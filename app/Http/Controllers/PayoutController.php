<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\PayoutOtp;
use App\Services\ClickPesaAPIService;
use App\Services\MessagingServiceAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayoutController extends Controller
{
    protected ClickPesaAPIService $api;
    protected MessagingServiceAPI $sms;

    public function __construct(ClickPesaAPIService $api, MessagingServiceAPI $sms)
    {
        $this->api = $api;
        $this->sms = $sms;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $query = Payout::orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $payouts = $query->paginate(20);

        return view('payouts.index', compact('payouts', 'status'));
    }

    public function create()
    {
        return view('payouts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
            'currency' => 'required|in:TZS,USD',
            'payout_type' => 'required|in:MOBILE_MONEY,BANK',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required_if:payout_type,MOBILE_MONEY|nullable|string',
            'bank_account_number' => 'required_if:payout_type,BANK|nullable|string',
            'bank_name' => 'required_if:payout_type,BANK|nullable|string',
            'bic' => 'required_if:payout_type,BANK|nullable|string',
            'description' => 'nullable|string|max:500'
        ]);

        try {
            $orderReference = $this->api->generateOrderReference('FEEDTANPAY');
            $payout = Payout::create([
                'order_reference' => $orderReference,
                'status' => 'PENDING_VERIFICATION',
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'payout_type' => $validated['payout_type'],
                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $validated['recipient_phone'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bic' => $validated['bic'] ?? null,
                'description' => $validated['description'] ?? null,
                'user_id' => auth()->id()
            ]);

            // Generate and send OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $adminPhone = auth()->user()->phone ?? '255712345678'; // Use authenticated user's phone or default
            
            $payoutOtp = PayoutOtp::create([
                'payout_id' => $payout->id,
                'user_id' => auth()->id(),
                'otp' => $otp,
                'phone' => $adminPhone,
                'expires_at' => now()->addMinutes(10),
                'is_verified' => false
            ]);

            // Send SMS with full details and OTP
            $smsMessage = "FEEDTAN PAYOUT REQUEST:\n";
            $smsMessage .= "Reference: {$orderReference}\n";
            $smsMessage .= "Amount: {$validated['amount']} {$validated['currency']}\n";
            $smsMessage .= "Recipient: {$validated['recipient_name']}\n";
            $smsMessage .= $validated['payout_type'] === 'MOBILE_MONEY' ? "Phone: {$validated['recipient_phone']}\n" : "Bank: {$validated['bank_name']}\nAcc: {$validated['bank_account_number']}\n";
            $smsMessage .= "Description: {$validated['description']}\n";
            $smsMessage .= "OTP: {$otp}\n";
            $smsMessage .= "Expires in 10 minutes.\nDo not share.";

            $this->sms->sendSMS($adminPhone, $smsMessage);

            return redirect()->route('payouts.verify-otp', $orderReference);

        } catch (\Exception $e) {
            Log::error('Payout initiation failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function showVerifyOtp(string $orderReference)
    {
        $payout = Payout::where('order_reference', $orderReference)->firstOrFail();
        return view('payouts.verify-otp', compact('payout'));
    }

    public function verifyOtp(Request $request, string $orderReference)
    {
        $validated = $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        try {
            $payout = Payout::where('order_reference', $orderReference)->firstOrFail();
            $otpRecord = PayoutOtp::where('payout_id', $payout->id)
                ->where('is_verified', false)
                ->where('expires_at', '>', now())
                ->latest()
                ->firstOrFail();

            if ($otpRecord->otp !== $validated['otp']) {
                return back()->with('error', 'Invalid OTP');
            }

            $otpRecord->update(['is_verified' => true]);

            // Initiate payout via ClickPesa
            if ($payout->payout_type === 'MOBILE_MONEY') {
                $apiResponse = $this->api->createMobileMoneyPayout(
                    $payout->amount,
                    $payout->recipient_phone,
                    $payout->currency,
                    $orderReference
                );
            } else {
                $apiResponse = $this->api->createBankPayout(
                    $payout->amount,
                    $payout->currency,
                    $payout->bank_account_number,
                    $payout->recipient_name,
                    $payout->bic,
                    'ACH',
                    $orderReference
                );
            }

            // Save complete API response and update payout
            $this->updatePayoutFromApi($payout, $apiResponse);

            return redirect()->route('payouts.status', $orderReference)
                            ->with('success', 'Payout initiated successfully!');

        } catch (\Exception $e) {
            Log::error('OTP verification failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function resendOtp(string $orderReference)
    {
        try {
            $payout = Payout::where('order_reference', $orderReference)->firstOrFail();
            
            // Generate new OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $adminPhone = auth()->user()->phone ?? '255712345678';
            
            $payoutOtp = PayoutOtp::create([
                'payout_id' => $payout->id,
                'user_id' => auth()->id(),
                'otp' => $otp,
                'phone' => $adminPhone,
                'expires_at' => now()->addMinutes(10),
                'is_verified' => false
            ]);

            // Send SMS
            $smsMessage = "FEEDTAN PAYOUT REQUEST:\n";
            $smsMessage .= "Reference: {$orderReference}\n";
            $smsMessage .= "New OTP: {$otp}\n";
            $smsMessage .= "Expires in 10 minutes.\nDo not share.";
            $this->sms->sendSMS($adminPhone, $smsMessage);

            return back()->with('success', 'OTP resent successfully');
        } catch (\Exception $e) {
            Log::error('OTP resend failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(string $orderReference)
    {
        $payout = Payout::where('order_reference', $orderReference)->firstOrFail();

        // Refresh status from API if not in final state
        if (!in_array($payout->status, ['SUCCESS', 'FAILED', 'SETTLED'])) {
            try {
                $apiResponse = $this->api->queryPayoutStatus($orderReference);
                $this->updatePayoutFromApi($payout, $apiResponse);
            } catch (\Exception $e) {
                Log::error('Failed to refresh payout status', ['error' => $e->getMessage()]);
            }
        }

        return view('payouts.show', compact('payout'));
    }

    public function refreshStatus(string $orderReference)
    {
        $payout = Payout::where('order_reference', $orderReference)->firstOrFail();

        try {
            $apiResponse = $this->api->queryPayoutStatus($orderReference);
            $this->updatePayoutFromApi($payout, $apiResponse);
            return back()->with('success', 'Payout status refreshed!');
        } catch (\Exception $e) {
            Log::error('Failed to refresh payout status', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to refresh status: ' . $e->getMessage());
        }
    }

    public function syncFromApi()
    {
        try {
            $apiPayouts = $this->api->queryAllPayouts();
            
            $payoutsArray = $apiPayouts['payouts'] ?? $apiPayouts;
            if (is_array($payoutsArray)) {
                foreach ($payoutsArray as $apiPayout) {
                    $orderRef = $apiPayout['order_reference'] ?? $apiPayout['orderReference'] ?? null;
                    if (!$orderRef) continue;

                    Payout::updateOrCreate(
                        ['order_reference' => $orderRef],
                        [
                            'transaction_id' => $apiPayout['id'] ?? $apiPayout['transaction_id'] ?? null,
                            'status' => $apiPayout['status'] ?? 'UNKNOWN',
                            'amount' => $apiPayout['amount'] ?? 0,
                            'currency' => $apiPayout['currency'] ?? 'TZS',
                            'recipient_name' => $apiPayout['recipient_name'] ?? $apiPayout['customerName'] ?? 'N/A',
                            'recipient_phone' => $apiPayout['recipient_phone'] ?? $apiPayout['phoneNumber'] ?? null,
                            'callback_data' => $apiPayout,
                            'user_id' => auth()->id()
                        ]
                    );
                }
            }

            return back()->with('success', 'Payouts synced successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to sync payouts', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to sync payouts: ' . $e->getMessage());
        }
    }

    protected function updatePayoutFromApi(Payout $payout, array $apiData)
    {
        $updateData = [
            'status' => $apiData['status'] ?? $payout->status,
            'transaction_id' => $apiData['id'] ?? $apiData['transaction_id'] ?? $payout->transaction_id,
            'callback_data' => $apiData
        ];
        $payout->update($updateData);
    }
}
