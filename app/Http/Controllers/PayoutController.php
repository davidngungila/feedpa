<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\PayoutOtp;
use App\Services\ClickPesaAPIService;
use App\Services\MessagingServiceAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

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
        $activeStatus = $request->get('status', 'SUCCESS');
        $selectedColumns = $request->get('columns', ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'recipient_name', 'recipient_phone', 'payout_type', 'created_at']);
        $availableColumns = [
            'order_reference' => 'Reference',
            'transaction_id' => 'Transaction ID',
            'status' => 'Status',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'fee' => 'Fee',
            'recipient_name' => 'Recipient Name',
            'recipient_phone' => 'Recipient Phone',
            'beneficiary_account_name' => 'Beneficiary Account Name',
            'beneficiary_account_number' => 'Beneficiary Account Number',
            'beneficiary_mobile' => 'Beneficiary Mobile',
            'beneficiary_email' => 'Beneficiary Email',
            'payout_type' => 'Payout Type',
            'channel' => 'Channel',
            'channel_provider' => 'Channel Provider',
            'transfer_type' => 'Transfer Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];

        $query = Payout::query();

        $this->applyHistoryTabFilter($query, $activeStatus);
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('order_reference', 'like', '%' . $search . '%')
                    ->orWhere('transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('recipient_name', 'like', '%' . $search . '%')
                    ->orWhere('beneficiary_account_name', 'like', '%' . $search . '%')
                    ->orWhere('recipient_phone', 'like', '%' . $search . '%')
                    ->orWhere('beneficiary_mobile', 'like', '%' . $search . '%')
                    ->orWhere('beneficiary_account_number', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $payouts = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get count stats
        $successCount = Payout::whereIn('status', ['SUCCESS', 'SETTLED'])->count();
        $failedCount = Payout::whereIn('status', ['FAILED', 'ERROR', 'CANCELLED'])->count();
        $pendingCount = Payout::whereNotIn('status', ['SUCCESS', 'SETTLED', 'FAILED', 'ERROR', 'CANCELLED'])->count();

        return view('payouts.index', compact(
            'payouts',
            'activeStatus',
            'selectedColumns',
            'availableColumns',
            'successCount',
            'failedCount',
            'pendingCount'
        ));
    }
    
    protected function applyHistoryTabFilter($query, $status)
    {
        switch (strtoupper($status)) {
            case 'SUCCESS':
            case 'SETTLED':
                $query->whereIn('status', ['SUCCESS', 'SETTLED']);
                break;
            case 'FAILED':
            case 'ERROR':
            case 'CANCELLED':
                $query->whereIn('status', ['FAILED', 'ERROR', 'CANCELLED']);
                break;
            case 'PENDING':
                $query->whereNotIn('status', ['SUCCESS', 'SETTLED', 'FAILED', 'ERROR', 'CANCELLED']);
                break;
        }
    }
    
    /**
     * Export payout history to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            // Check if this is a single payout receipt request
            if ($request->filled('order_reference') && !$request->filled('bulk')) {
                return $this->receipt($request->order_reference);
            }

            // Get filtered payouts from database
            $query = Payout::query();

            $this->applyHistoryTabFilter($query, $request->get('status', 'SUCCESS'));
            if ($request->filled('currency')) {
                $query->where('currency', $request->currency);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('order_reference', 'like', '%' . $search . '%')
                        ->orWhere('transaction_id', 'like', '%' . $search . '%')
                        ->orWhere('recipient_name', 'like', '%' . $search . '%')
                        ->orWhere('beneficiary_account_name', 'like', '%' . $search . '%')
                        ->orWhere('recipient_phone', 'like', '%' . $search . '%');
                });
            }
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $payouts = $query->orderBy('created_at', 'asc')->get()->toArray();
            
            // Selected columns
            $allowedColumns = ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'fee', 'recipient_name', 'recipient_phone', 'beneficiary_account_name', 'beneficiary_account_number', 'beneficiary_mobile', 'beneficiary_email', 'payout_type', 'channel', 'channel_provider', 'transfer_type', 'created_at', 'updated_at'];
            $columns = array_values(array_intersect($request->get('columns', ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'recipient_name', 'payout_type', 'created_at']), $allowedColumns));
            if (empty($columns)) {
                $columns = ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'recipient_name', 'payout_type', 'created_at'];
            }

            $pdf = Pdf::loadView('payouts.exports.pdf', [
                'payouts' => $payouts,
                'columns' => $columns
            ])
                ->setPaper('a4', 'landscape')
                ->setOption('margin-bottom', 10);

            return $pdf->download('payout-history-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('PDF export failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export payout history to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            // Get filtered payouts from database
            $query = Payout::query();

            $this->applyHistoryTabFilter($query, $request->get('status', 'SUCCESS'));
            if ($request->filled('currency')) {
                $query->where('currency', $request->currency);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('order_reference', 'like', '%' . $search . '%')
                        ->orWhere('transaction_id', 'like', '%' . $search . '%')
                        ->orWhere('recipient_name', 'like', '%' . $search . '%')
                        ->orWhere('beneficiary_account_name', 'like', '%' . $search . '%')
                        ->orWhere('recipient_phone', 'like', '%' . $search . '%');
                });
            }
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            $payouts = $query->orderBy('created_at', 'asc')->get()->toArray();
            
            // Selected columns
            $allowedColumns = ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'fee', 'recipient_name', 'recipient_phone', 'beneficiary_account_name', 'beneficiary_account_number', 'beneficiary_mobile', 'beneficiary_email', 'payout_type', 'channel', 'channel_provider', 'transfer_type', 'created_at', 'updated_at'];
            $columns = array_values(array_intersect($request->get('columns', ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'recipient_name', 'payout_type', 'created_at', 'updated_at']), $allowedColumns));
            if (empty($columns)) {
                $columns = ['order_reference', 'transaction_id', 'status', 'amount', 'currency', 'recipient_name', 'payout_type', 'created_at', 'updated_at'];
            }

            return Excel::download(new \App\Exports\PaymentHistoryExport($payouts, $columns), 'payout-history-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Excel export failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to export Excel: ' . $e->getMessage());
        }
    }

    /**
     * Generate payout receipt PDF
     */
    public function receipt($orderReference)
    {
        try {
            $payout = Payout::where('order_reference', $orderReference)->firstOrFail();
            
            $payoutData = [
                'orderReference' => $payout->order_reference,
                'transaction_id' => $payout->transaction_id ?? $payout->clickpesa_payout_id,
                'status' => $payout->status,
                'amount' => $payout->amount,
                'currency' => $payout->currency,
                'fee' => $payout->fee,
                'payout_type' => $payout->payout_type,
                'channel' => $payout->channel,
                'channel_provider' => $payout->channel_provider,
                'transfer_type' => $payout->transfer_type,
                'recipient_name' => $payout->recipient_name ?? $payout->beneficiary_account_name,
                'recipient_phone' => $payout->recipient_phone ?? $payout->beneficiary_mobile,
                'beneficiary' => [
                    'accountName' => $payout->beneficiary_account_name ?? $payout->recipient_name,
                    'accountNumber' => $payout->beneficiary_account_number ?? $payout->bank_account_number,
                    'beneficiaryMobileNumber' => $payout->beneficiary_mobile ?? $payout->recipient_phone,
                    'beneficiaryEmail' => $payout->beneficiary_email
                ],
                'description' => $payout->resolvedDescription(),
                'createdAt' => $payout->created_at,
                'id' => $payout->clickpesa_payout_id ?? $payout->transaction_id
            ];
            
            // Generate QR code
            $qrContent = "FEEDTAN PAYOUT RECEIPT:\n" .
                        "Reference: {$orderReference}\n" .
                        "Amount: {$payoutData['amount']} {$payoutData['currency']}\n" .
                        "Status: {$payoutData['status']}\n" .
                        "Date: " . (isset($payoutData['createdAt']) ? \Carbon\Carbon::parse($payoutData['createdAt'])->format('Y-m-d H:i:s') : 'N/A');
            
            $qrCodeSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->encoding('UTF-8')->errorCorrection('H')->generate($qrContent);
            $qrCodeImage = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);

            $pdf = Pdf::loadView('payouts.receipt', ['payoutData' => $payoutData, 'qrCodeImage' => $qrCodeImage])
                ->setPaper('a4', 'portrait')
                ->setOption('margin-bottom', 20);

            return $pdf->download('payout-receipt-' . $orderReference . '.pdf');
        } catch (\Exception $e) {
            Log::error('Receipt generation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate receipt: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $banks = [];
        $balance = null;
        try {
            $banksResponse = $this->api->getBanksList();
            Log::info('Banks response', ['response' => $banksResponse]);
            // Check all possible keys where banks might be
            if (isset($banksResponse['data']) && is_array($banksResponse['data'])) {
                $banks = $banksResponse['data'];
            } elseif (isset($banksResponse['banks']) && is_array($banksResponse['banks'])) {
                $banks = $banksResponse['banks'];
            } elseif (is_array($banksResponse)) {
                $banks = $banksResponse;
            }
            Log::info('Processed banks', ['banks' => $banks]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch banks list', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }
        
        try {
            $balanceResponse = $this->api->getAccountBalance();
            if (isset($balanceResponse['data'])) {
                $balance = $balanceResponse['data'];
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch account balance', ['error' => $e->getMessage()]);
            $balance = null;
        }
        
        $orderReference = $this->api->generateOrderReference('FEEDTANPAY');
        return view('payouts.create', compact('banks', 'balance', 'orderReference'));
    }

    public function previewPayout(\Illuminate\Http\Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:100',
                'currency' => 'required|in:TZS,USD',
                'payout_type' => 'required|in:MOBILE_MONEY,BANK',
                'recipient_phone' => 'required_if:payout_type,MOBILE_MONEY|nullable|string',
                'bank_account_number' => 'required_if:payout_type,BANK|nullable|string',
                'bic' => 'required_if:payout_type,BANK|nullable|string',
                'account_name' => 'required_if:payout_type,BANK|nullable|string',
                'transfer_type' => 'required_if:payout_type,BANK|in:ACH,RTGS',
            ]);

            $orderReference = $this->api->generateOrderReference('PREVIEW');

            if ($validated['payout_type'] === 'MOBILE_MONEY') {
                $response = $this->api->previewMobileMoneyPayout(
                    $validated['amount'],
                    $validated['recipient_phone'],
                    $validated['currency'],
                    $orderReference
                );
            } else {
                $response = $this->api->previewBankPayout(
                    $validated['amount'],
                    $validated['currency'],
                    $validated['bank_account_number'],
                    $validated['account_name'],
                    $validated['bic'],
                    $validated['transfer_type'],
                    $orderReference
                );
            }

            return response()->json(['success' => true, 'data' => $response, 'order_reference' => $orderReference]);
        } catch (\Exception $e) {
            Log::error('Payout preview failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function detectProvider(\Illuminate\Http\Request $request)
    {
        try {
            $validated = $request->validate([
                'phoneNumber' => 'required|string'
            ]);
            // For now, simulate provider detection based on prefix
            $phone = preg_replace('/[^0-9]/', '', $validated['phoneNumber']);
            $provider = null;
            if (preg_match('/^25565/', $phone)) {
                $provider = 'TIGO PESA';
            } elseif (preg_match('/^2557[14]/', $phone)) {
                $provider = 'M-PESA';
            } elseif (preg_match('/^2557[56]/', $phone)) {
                $provider = 'AIRTEL MONEY';
            } elseif (preg_match('/^25577/', $phone)) {
                $provider = 'HALOPESA';
            }
            return response()->json(['success' => true, 'provider' => $provider]);
        } catch (\Exception $e) {
            Log::error('Provider detection failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function lookupAccountName(\Illuminate\Http\Request $request)
    {
        try {
            $validated = $request->validate([
                'bic' => 'required|string',
                'accountNumber' => 'required|string',
                'currency' => 'sometimes|in:TZS,USD'
            ]);

            $response = $this->api->lookupBankAccountName(
                $validated['bic'],
                $validated['accountNumber'],
                $validated['currency'] ?? 'TZS'
            );

            Log::info('Account name lookup response', ['response' => $response]);

            // Extract account name from preview response
            $accountName = $response['data']['receiver']['accountName'] 
                ?? $response['receiver']['accountName'] 
                ?? $response['data']['accountName'] 
                ?? $response['accountName'] 
                ?? null;

            if ($accountName) {
                return response()->json(['success' => true, 'accountName' => $accountName]);
            } else {
                return response()->json(['success' => false, 'message' => 'Unable to retrieve account name from response'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Account name lookup failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_reference' => 'required|string|max:255',
            'amount' => 'required|numeric|min:100',
            'currency' => 'required|in:TZS,USD',
            'payout_type' => 'required|in:MOBILE_MONEY,BANK',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required_if:payout_type,MOBILE_MONEY|nullable|string',
            'bank_account_number' => 'required_if:payout_type,BANK|nullable|string',
            'bank_name' => 'required_if:payout_type,BANK|nullable|string',
            'bic' => 'required_if:payout_type,BANK|nullable|string',
            'transfer_type' => 'required_if:payout_type,BANK|in:ACH,RTGS',
            'beneficiary_email' => 'nullable|email|max:255',
            'beneficiary_mobile' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500'
        ]);

        // Format phone number for mobile money payouts
        $recipientPhone = $validated['recipient_phone'] ?? null;
        if ($validated['payout_type'] === 'MOBILE_MONEY' && $recipientPhone) {
            $cleaned = preg_replace('/[^0-9]/', '', $recipientPhone);
            if (strlen($cleaned) === 9) {
                $recipientPhone = '255' . $cleaned;
            } elseif (strlen($cleaned) === 10 && str_starts_with($cleaned, '0')) {
                $recipientPhone = '255' . substr($cleaned, 1);
            } elseif (strlen($cleaned) === 12 && str_starts_with($cleaned, '255')) {
                $recipientPhone = $cleaned;
            }
        }

        try {
            $orderReference = $validated['order_reference'];
            $payout = Payout::create([
                'order_reference' => $orderReference,
                'status' => 'PENDING_VERIFICATION',
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'payout_type' => $validated['payout_type'],
                'recipient_name' => $validated['recipient_name'],
                'recipient_phone' => $recipientPhone ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'bic' => $validated['bic'] ?? null,
                'transfer_type' => $validated['transfer_type'] ?? null,
                'beneficiary_email' => $validated['beneficiary_email'] ?? null,
                'beneficiary_mobile' => $validated['beneficiary_mobile'] ?? null,
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
            $smsMessage .= $validated['payout_type'] === 'MOBILE_MONEY' ? "Phone: {$recipientPhone}\n" : "Bank: {$validated['bank_name']}\nAcc: {$validated['bank_account_number']}\n";
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
                    $payout->transfer_type ?? 'ACH',
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

        // Prepare payout data array
        $payoutData = $payout->toArray();
        $payoutData['beneficiary'] = [
            'accountName' => $payout->beneficiary_account_name ?? $payout->recipient_name,
            'accountNumber' => $payout->beneficiary_account_number ?? $payout->bank_account_number,
            'beneficiaryMobileNumber' => $payout->beneficiary_mobile ?? $payout->recipient_phone,
            'beneficiaryEmail' => $payout->beneficiary_email
        ];

        // Get notes
        $notes = $payout->notes;

        return view('payouts.show', compact('payout', 'payoutData', 'orderReference', 'notes'));
    }

    public function addNote(Request $request, string $orderReference)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $payout = Payout::where('order_reference', $orderReference)->firstOrFail();

        $payout->notes()->create([
            'user_id' => auth()->id(),
            'content' => $validated['content']
        ]);

        return back()->with('success', 'Note added successfully!');
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
            $apiPayouts = $this->api->queryAllPayouts(['limit' => 20, 'orderBy' => 'DESC']);
            $payoutsData = $apiPayouts['data'] ?? $apiPayouts['payouts'] ?? [];
            $syncedCount = 0;
            
            if (is_array($payoutsData)) {
                foreach ($payoutsData as $apiPayout) {
                    try {
                        $orderRef = $apiPayout['order_reference'] ?? $apiPayout['orderReference'] ?? $apiPayout['id'] ?? null;
                        if (!$orderRef) continue;

                        $beneficiary = $apiPayout['beneficiary'] ?? [];
                        $payoutType = ($apiPayout['channel'] ?? '') === 'BANK TRANSFER' ? 'BANK' : 'MOBILE MONEY';

                        Payout::updateOrCreate(
                            ['order_reference' => $orderRef],
                            [
                                'clickpesa_payout_id' => $apiPayout['id'] ?? null,
                                'transaction_id' => $apiPayout['id'] ?? $apiPayout['transaction_id'] ?? null,
                                'status' => $apiPayout['status'] ?? 'UNKNOWN',
                                'amount' => $apiPayout['amount'] ?? 0,
                                'currency' => $apiPayout['currency'] ?? 'TZS',
                                'fee' => $apiPayout['fee'] ?? 0,
                                'payout_type' => $payoutType,
                                'recipient_name' => $beneficiary['accountName'] ?? $apiPayout['recipient_name'] ?? $apiPayout['customerName'] ?? 'N/A',
                                'recipient_phone' => $beneficiary['beneficiaryMobileNumber'] ?? $apiPayout['recipient_phone'] ?? $apiPayout['phoneNumber'] ?? null,
                                'bank_name' => $apiPayout['bank_name'] ?? null,
                                'bank_account_number' => $beneficiary['accountNumber'] ?? $apiPayout['bank_account_number'] ?? null,
                                'bic' => $beneficiary['bic'] ?? $apiPayout['bic'] ?? null,
                                'channel' => $apiPayout['channel'] ?? null,
                                'channel_provider' => $apiPayout['channelProvider'] ?? null,
                                'transfer_type' => $apiPayout['transferType'] ?? null,
                                'beneficiary_account_number' => $beneficiary['accountNumber'] ?? null,
                                'beneficiary_account_name' => $beneficiary['accountName'] ?? null,
                                'beneficiary_mobile' => $beneficiary['beneficiaryMobileNumber'] ?? null,
                                'beneficiary_email' => $beneficiary['beneficiaryEmail'] ?? null,
                                'notes' => $apiPayout['notes'] ?? null,
                                'created_at' => isset($apiPayout['createdAt']) ? \Carbon\Carbon::parse($apiPayout['createdAt'])->toDateTimeString() : now(),
                                'updated_at' => isset($apiPayout['updatedAt']) ? \Carbon\Carbon::parse($apiPayout['updatedAt'])->toDateTimeString() : now(),
                                'callback_data' => $apiPayout,
                                'user_id' => auth()->check() ? auth()->id() : null
                            ]
                        );
                        $syncedCount++;
                    } catch (\Exception $e) {
                        Log::warning('Failed to sync individual payout', ['error' => $e->getMessage(), 'payout' => $apiPayout]);
                        continue;
                    }
                }
            }

            return back()->with('success', 'Payouts synced successfully! Total synced: ' . $syncedCount);
        } catch (\Exception $e) {
            Log::error('Failed to sync payouts', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Failed to sync payouts: ' . $e->getMessage());
        }
    }

    protected function updatePayoutFromApi(Payout $payout, array $apiData)
    {
        $beneficiary = $apiData['beneficiary'] ?? [];
        $payoutType = ($apiData['channel'] ?? '') === 'BANK TRANSFER' ? 'BANK' : 'MOBILE MONEY';

        $updateData = [
            'status' => $apiData['status'] ?? $payout->status,
            'transaction_id' => $apiData['id'] ?? $apiData['transaction_id'] ?? $payout->transaction_id,
            'clickpesa_payout_id' => $apiData['id'] ?? $payout->clickpesa_payout_id,
            'amount' => $apiData['amount'] ?? $payout->amount,
            'currency' => $apiData['currency'] ?? $payout->currency,
            'fee' => $apiData['fee'] ?? $payout->fee,
            'payout_type' => $payoutType,
            'recipient_name' => $beneficiary['accountName'] ?? $apiData['recipient_name'] ?? $apiData['customerName'] ?? $payout->recipient_name,
            'recipient_phone' => $beneficiary['beneficiaryMobileNumber'] ?? $apiData['recipient_phone'] ?? $apiData['phoneNumber'] ?? $payout->recipient_phone,
            'bank_name' => $apiData['bank_name'] ?? $payout->bank_name,
            'bank_account_number' => $beneficiary['accountNumber'] ?? $apiData['bank_account_number'] ?? $payout->bank_account_number,
            'bic' => $beneficiary['bic'] ?? $apiData['bic'] ?? $payout->bic,
            'channel' => $apiData['channel'] ?? $payout->channel,
            'channel_provider' => $apiData['channelProvider'] ?? $payout->channel_provider,
            'transfer_type' => $apiData['transferType'] ?? $payout->transfer_type,
            'beneficiary_account_number' => $beneficiary['accountNumber'] ?? $payout->beneficiary_account_number,
            'beneficiary_account_name' => $beneficiary['accountName'] ?? $payout->beneficiary_account_name,
            'beneficiary_mobile' => $beneficiary['beneficiaryMobileNumber'] ?? $payout->beneficiary_mobile,
            'beneficiary_email' => $beneficiary['beneficiaryEmail'] ?? $payout->beneficiary_email,
            'notes' => $apiData['notes'] ?? $payout->notes,
            'updated_at' => isset($apiData['updatedAt']) ? \Carbon\Carbon::parse($apiData['updatedAt'])->toDateTimeString() : now(),
            'callback_data' => $apiData
        ];
        $payout->update($updateData);
    }
}
