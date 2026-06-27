<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Payout;
use App\Models\PayoutOtp;
use App\Models\User;
use App\Services\AppNotificationService;
use App\Services\ClickPesaAPIService;
use App\Services\MessagingServiceAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PayoutController extends Controller
{
    protected ClickPesaAPIService $api;
    protected MessagingServiceAPI $sms;
    protected AppNotificationService $notifications;

    public function __construct(ClickPesaAPIService $api, MessagingServiceAPI $sms, AppNotificationService $notifications)
    {
        $this->api = $api;
        $this->sms = $sms;
        $this->notifications = $notifications;
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
        if (!auth()->user()->can_create_payouts) {
            return redirect()->route('payouts.index')->with('error', 'You are not authorized to create payouts');
        }
        
        Audit::log('view_payout_create', 'Viewed payout create page');
        
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
        $beneficiaries = \App\Models\Beneficiary::where('user_id', auth()->id())
            ->where('is_active', true)
            ->latest()
            ->get();
        return view('payouts.create', compact('banks', 'balance', 'orderReference', 'beneficiaries'));
    }

    public function previewPayout(\Illuminate\Http\Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:100',
                'currency' => 'required|in:TZS,USD',
                'payout_type' => 'required|in:MOBILE_MONEY,BANK',
                'recipient_name' => 'nullable|string|max:255',
                'recipient_phone' => 'required_if:payout_type,MOBILE_MONEY|nullable|string',
                'bank_account_number' => 'required_if:payout_type,BANK|nullable|string',
                'bic' => 'required_if:payout_type,BANK|nullable|string',
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
                    $validated['recipient_name'] ?? 'Temp',
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

            $orderReference = $this->api->generateOrderReference('LOOKUP');
            $response = $this->api->previewBankPayout(
                100,
                $validated['currency'] ?? 'TZS',
                $validated['accountNumber'],
                'Temp',
                $validated['bic'],
                'ACH',
                $orderReference
            );

            Log::info('Account name lookup response', ['response' => $response]);

            // Check all possible keys for account name
            $accountName = $response['data']['receiver']['accountName'] 
                ?? $response['receiver']['accountName'] 
                ?? $response['data']['recipient']['accountName'] 
                ?? $response['recipient']['accountName'] 
                ?? $response['data']['accountName'] 
                ?? $response['accountName'] 
                ?? $response['data']['customerName'] 
                ?? $response['customerName'] 
                ?? null;

            if ($accountName) {
                return response()->json(['success' => true, 'accountName' => $accountName]);
            } else {
                // Also log all keys for debugging
                Log::info('All response keys for debugging:', array_keys($response));
                if (isset($response['data'])) {
                    Log::info('Data keys:', array_keys($response['data']));
                }
                return response()->json(['success' => false, 'message' => 'Unable to retrieve account name from response'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Account name lookup failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can_create_payouts) {
            return redirect()->route('payouts.index')->with('error', 'You are not authorized to create payouts');
        }
        
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
                'status' => 'PENDING_INITIATION_OTP',
                'workflow_stage' => 'INITIATION_OTP',
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
                'user_id' => auth()->id(),
                'initiated_by' => auth()->id(),
                'initiated_at' => now(),
            ]);

            Audit::log('initiate_payout', "Initiated payout {$orderReference}: {$validated['amount']} {$validated['currency']} to {$validated['recipient_name']} ({$validated['payout_type']})");

            $this->createAndSendOtp($payout, auth()->user(), 'initiation');
            $this->notifications->notifyPayoutOfficers(
                'payout_initiated',
                'Payout Initiated',
                "Payout {$orderReference} was initiated by " . auth()->user()->name . " and is waiting for initiation OTP verification.",
                route('payouts.status', $orderReference),
                ['payout_id' => $payout->id, 'order_reference' => $orderReference, 'status' => $payout->status],
                'payout:' . $payout->id . ':initiated'
            );

            return redirect()->route('payouts.verify-otp', $orderReference);

        } catch (\Exception $e) {
            Log::error('Payout initiation failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function showVerifyOtp(string $orderReference)
    {
        if (!auth()->user()->can_create_payouts) {
            return redirect()->route('payouts.index')->with('error', 'You are not authorized to manage payouts');
        }

        $payout = Payout::with(['initiator', 'approver'])->where('order_reference', $orderReference)->firstOrFail();
        $pendingOtp = $this->pendingOtpForPayout($payout);

        if (!$pendingOtp) {
            return redirect()->route('payouts.status', $orderReference)->with('warning', 'There is no pending OTP for this payout.');
        }

        if ($pendingOtp->purpose === 'initiation') {
            if ((int) ($payout->initiated_by ?? 0) !== (int) auth()->id()) {
                return redirect()->route('payouts.status', $orderReference)
                    ->with('warning', 'Only the initiating officer can verify the initiation OTP.');
            }

            $otpPurpose = 'Initiation Verification';
        } else {
            if ((int) ($payout->payment_otp_requested_by ?? 0) !== (int) auth()->id()) {
                return redirect()->route('payouts.status', $orderReference)
                    ->with('warning', 'Only the officer who requested the authorization OTP can verify it.');
            }

            $otpPurpose = 'Approval And Authorization';
        }

        return view('payouts.verify-otp', compact('payout', 'pendingOtp', 'otpPurpose'));
    }

    public function verifyOtp(Request $request, string $orderReference)
    {
        if (!auth()->user()->can_create_payouts) {
            return redirect()->route('payouts.index')->with('error', 'You are not authorized to manage payouts');
        }

        $validated = $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        try {
            $payout = Payout::where('order_reference', $orderReference)->firstOrFail();
            $otpRecord = $this->pendingOtpForPayout($payout);

            if (!$otpRecord) {
                return redirect()->route('payouts.status', $orderReference)->with('warning', 'There is no pending OTP for this payout.');
            }

            if ($otpRecord->otp !== $validated['otp']) {
                return back()->with('error', 'Invalid OTP');
            }

            if ($otpRecord->purpose === 'initiation') {
                if ((int) ($payout->initiated_by ?? 0) !== (int) auth()->id()) {
                    return redirect()->route('payouts.status', $orderReference)
                        ->with('warning', 'Only the initiating officer can verify the initiation OTP.');
                }
            } else {
                if ((int) ($payout->payment_otp_requested_by ?? 0) !== (int) auth()->id()) {
                    return redirect()->route('payouts.status', $orderReference)
                        ->with('warning', 'Only the officer who requested the authorization OTP can verify it.');
                }
            }

            $otpRecord->update(['is_verified' => true]);

            if ($otpRecord->purpose === 'payment_authorization') {
                $approvedAt = now();

                $payout->update([
                    'status' => 'PROCESSING',
                    'workflow_stage' => 'PROCESSING',
                    'approved_by' => auth()->id(),
                    'approved_at' => $approvedAt,
                    'payment_authorized_by' => auth()->id(),
                    'payment_authorized_at' => $approvedAt,
                ]);

                $apiResponse = $this->submitPayoutToProvider($payout);

                Audit::log('approve_payout', "Approved and authorized payout {$orderReference} using authorization OTP");

                $this->updatePayoutFromApi($payout->fresh(), $apiResponse);
                $payout->refresh();
                $this->broadcastAuthorizationAlert($payout, auth()->user());

                $this->notifications->notifyPayoutOfficers(
                    'payout_approved',
                    'Payout Approved And Authorized',
                    "Payout {$orderReference} was approved and authorized by " . auth()->user()->name . " after OTP verification and submitted for payment processing.",
                    route('payouts.status', $orderReference),
                    ['payout_id' => $payout->id, 'order_reference' => $orderReference, 'status' => $payout->status],
                    'payout:' . $payout->id . ':approved'
                );

                return redirect()->route('payouts.status', $orderReference)
                    ->with('success', 'Payout approved and authorized successfully.');
            }

            $payout->update([
                'status' => 'PENDING_APPROVAL',
                'workflow_stage' => 'APPROVAL_PENDING',
                'initiation_verified_by' => auth()->id(),
                'initiation_verified_at' => now(),
            ]);

            Audit::log('verify_payout_initiation_otp', "Verified initiation OTP for payout {$orderReference}");

            $this->notifications->notifyPayoutOfficers(
                'payout_pending_approval',
                'Payout Awaiting Approval And Authorization',
                "Payout {$orderReference} is ready for final approval and authorization after OTP verification by " . auth()->user()->name . '.',
                route('payouts.status', $orderReference),
                ['payout_id' => $payout->id, 'order_reference' => $orderReference, 'status' => 'PENDING_APPROVAL'],
                'payout:' . $payout->id . ':approval_pending'
            );

            return redirect()->route('payouts.status', $orderReference)
                ->with('success', 'Initiation OTP verified. The payout is now waiting for approval and authorization by another officer.');

        } catch (\Exception $e) {
            Log::error('OTP verification failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(string $orderReference)
    {
        if (!auth()->user()->can_create_payouts) {
            return redirect()->route('payouts.index')->with('error', 'You are not authorized to manage payouts');
        }

        try {
            $payout = Payout::where('order_reference', $orderReference)->firstOrFail();

            if ($payout->workflow_stage !== 'APPROVAL_PENDING') {
                return back()->with('warning', 'This payout is not waiting for final approval.');
            }

            if ((int) ($payout->initiated_by ?? 0) === (int) auth()->id()) {
                return back()->with('warning', 'The initiating officer cannot approve and authorize the same payout.');
            }

            $payout->otps()
                ->where('is_verified', false)
                ->where('expires_at', '>', now())
                ->update(['expires_at' => now()]);

            $payout->update([
                'status' => 'PENDING_PAYMENT_AUTHORIZATION',
                'workflow_stage' => 'PAYMENT_AUTHORIZATION_OTP',
                'payment_otp_requested_by' => auth()->id(),
                'payment_otp_requested_at' => now(),
            ]);

            Audit::log('approve_payout', "Requested approval and authorization OTP for payout {$orderReference}");
            $this->createAndSendOtp($payout, auth()->user(), 'payment_authorization');

            $this->notifications->notifyPayoutOfficers(
                'payout_approved',
                'Authorization OTP Requested',
                "Payout {$orderReference} is awaiting authorization OTP verification by " . auth()->user()->name . '.',
                route('payouts.status', $orderReference),
                ['payout_id' => $payout->id, 'order_reference' => $orderReference, 'status' => 'PENDING_PAYMENT_AUTHORIZATION'],
                'payout:' . $payout->id . ':approved'
            );

            return redirect()->route('payouts.verify-otp', $orderReference)
                ->with('success', 'Authorization OTP sent. Enter the OTP to approve and authorize this payout.');
        } catch (\Exception $e) {
            Log::error('Payout approval failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, string $orderReference)
    {
        if (!auth()->user()->can_create_payouts) {
            return redirect()->route('payouts.index')->with('error', 'You are not authorized to manage payouts');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        try {
            $payout = Payout::where('order_reference', $orderReference)->firstOrFail();
            $isPendingVerification = in_array($payout->workflow_stage, ['INITIATION_OTP', 'PENDING_VERIFICATION'], true)
                || $payout->status === 'PENDING_VERIFICATION';

            if (!$isPendingVerification) {
                return back()->with('warning', 'Only payouts waiting for verification can be cancelled from this page.');
            }

            $payout->otps()
                ->where('is_verified', false)
                ->where('expires_at', '>', now())
                ->update(['expires_at' => now()]);

            $payout->update([
                'status' => 'CANCELLED',
                'workflow_stage' => 'CANCELLED',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $validated['cancellation_reason'],
            ]);

            Audit::log('cancel_payout', "Cancelled payout {$orderReference}. Reason: {$validated['cancellation_reason']}");

            $this->notifications->notifyPayoutOfficers(
                'payout_cancelled',
                'Payout Cancelled',
                "Payout {$orderReference} was cancelled by " . auth()->user()->name . ". Reason: {$validated['cancellation_reason']}",
                route('payouts.status', $orderReference),
                [
                    'payout_id' => $payout->id,
                    'order_reference' => $orderReference,
                    'status' => 'CANCELLED',
                    'reason' => $validated['cancellation_reason'],
                ],
                'payout:' . $payout->id . ':cancelled'
            );

            return redirect()->route('payouts.index', ['status' => 'PENDING', 'page' => 1])
                ->with('success', 'Payout cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Payout cancellation failed', ['error' => $e->getMessage(), 'order_reference' => $orderReference]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, string $orderReference)
    {
        if (!auth()->user()->can_create_payouts) {
            return redirect()->route('payouts.index')->with('error', 'You are not authorized to manage payouts');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            $payout = Payout::where('order_reference', $orderReference)->firstOrFail();

            if (in_array($payout->workflow_stage, ['REJECTED', 'COMPLETED', 'FAILED'], true) || in_array($payout->status, ['SUCCESS', 'SETTLED'], true)) {
                return back()->with('warning', 'This payout can no longer be rejected.');
            }

            $payout->update([
                'status' => 'REJECTED',
                'workflow_stage' => 'REJECTED',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            Audit::log('reject_payout', "Rejected payout {$orderReference}. Reason: {$validated['rejection_reason']}");

            $this->notifications->notifyPayoutOfficers(
                'payout_rejected',
                'Payout Rejected',
                "Payout {$orderReference} was rejected by " . auth()->user()->name . ". Reason: {$validated['rejection_reason']}",
                route('payouts.status', $orderReference),
                ['payout_id' => $payout->id, 'order_reference' => $orderReference, 'status' => 'REJECTED'],
                'payout:' . $payout->id . ':rejected'
            );

            return redirect()->route('payouts.status', $orderReference)->with('success', 'Payout rejected successfully.');
        } catch (\Exception $e) {
            Log::error('Payout rejection failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function resendOtp(string $orderReference)
    {
        if (!auth()->user()->can_create_payouts) {
            return redirect()->route('payouts.index')->with('error', 'You are not authorized to manage payouts');
        }

        try {
            $payout = Payout::where('order_reference', $orderReference)->firstOrFail();
            if (($payout->workflow_stage ?? '') === 'PAYMENT_AUTHORIZATION_OTP') {
                if ((int) ($payout->payment_otp_requested_by ?? 0) !== (int) auth()->id()) {
                    return redirect()->route('payouts.status', $orderReference)
                        ->with('warning', 'Only the officer who requested the authorization OTP can resend it.');
                }

                $otpUser = User::find($payout->payment_otp_requested_by ?? auth()->id()) ?? auth()->user();
                $this->createAndSendOtp($payout, $otpUser, 'payment_authorization');
            } else {
                if ((int) ($payout->initiated_by ?? 0) !== (int) auth()->id()) {
                    return redirect()->route('payouts.status', $orderReference)
                        ->with('warning', 'Only the initiating officer can request a new initiation OTP.');
                }

                $otpUser = User::find($payout->initiated_by ?? auth()->id()) ?? auth()->user();
                $this->createAndSendOtp($payout, $otpUser, 'initiation');
            }

            return back()->with('success', 'OTP resent successfully');
        } catch (\Exception $e) {
            Log::error('OTP resend failed', ['error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(string $orderReference)
    {
        $payout = Payout::with([
            'notes.user',
            'initiator',
            'initiationVerifier',
            'approver',
            'rejector',
            'paymentAuthorizer',
        ])->where('order_reference', $orderReference)->firstOrFail();

        // Refresh status from API if not in final state
        if (!in_array($payout->status, ['SUCCESS', 'FAILED', 'SETTLED', 'REJECTED']) && !in_array($payout->workflow_stage, ['INITIATION_OTP', 'APPROVAL_PENDING', 'PAYMENT_AUTHORIZATION_OTP', 'REJECTED'], true)) {
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
        $payoutData['display_description'] = $payout->resolvedDescription();
        $payoutData['workflow_stage'] = $payout->workflow_stage;
        $payoutData['initiator_name'] = $payout->initiator?->name;
        $payoutData['initiation_verifier_name'] = $payout->initiationVerifier?->name;
        $payoutData['approver_name'] = $payout->approver?->name;
        $payoutData['rejector_name'] = $payout->rejector?->name;
        $payoutData['payment_authorizer_name'] = $payout->paymentAuthorizer?->name;
        $payoutData['initiated_at'] = $payout->initiated_at;
        $payoutData['initiation_verified_at'] = $payout->initiation_verified_at;
        $payoutData['approved_at'] = $payout->approved_at;
        $payoutData['rejected_at'] = $payout->rejected_at;
        $payoutData['rejection_reason'] = $payout->rejection_reason;
        $payoutData['payment_authorized_at'] = $payout->payment_authorized_at;
        unset($payoutData['notes']);

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

                        $existingPayout = Payout::where('order_reference', $orderRef)->first();
                        $previousStatus = $existingPayout?->status;
                        $beneficiary = $apiPayout['beneficiary'] ?? [];
                        $payoutType = ($apiPayout['channel'] ?? '') === 'BANK TRANSFER' ? 'BANK' : 'MOBILE MONEY';
                        $syncedStatus = $apiPayout['status'] ?? 'UNKNOWN';
                        $syncedWorkflowStage = in_array($syncedStatus, ['SUCCESS', 'SETTLED'], true)
                            ? 'COMPLETED'
                            : (in_array($syncedStatus, ['FAILED', 'ERROR', 'CANCELLED'], true) ? 'FAILED' : 'PROCESSING');

                        $payout = Payout::updateOrCreate(
                            ['order_reference' => $orderRef],
                            [
                                'clickpesa_payout_id' => $apiPayout['id'] ?? null,
                                'transaction_id' => $apiPayout['id'] ?? $apiPayout['transaction_id'] ?? null,
                                'status' => $syncedStatus,
                                'workflow_stage' => $syncedWorkflowStage,
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

                        if ($previousStatus !== $syncedStatus && in_array($syncedStatus, ['SUCCESS', 'SETTLED'], true)) {
                            $this->notifications->sendPayoutSuccessEmail($payout);
                        }

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

    protected function pendingOtpForPayout(Payout $payout): ?PayoutOtp
    {
        return PayoutOtp::where('payout_id', $payout->id)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    protected function createAndSendOtp(Payout $payout, User $user, string $purpose): PayoutOtp
    {
        $phone = $user->phone ?? auth()->user()?->phone ?? '255712345678';
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PayoutOtp::where('payout_id', $payout->id)
            ->where('purpose', $purpose)
            ->where('is_verified', false)
            ->update(['is_verified' => true]);

        $otpRecord = PayoutOtp::create([
            'payout_id' => $payout->id,
            'user_id' => $user->id,
            'otp' => $otp,
            'phone' => $phone,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
            'is_verified' => false,
        ]);

        $message = $this->buildOtpMessage($payout, $otp, $purpose, $user);

        try {
            $this->sms->sendSMS($phone, $message);
        } catch (\Exception $e) {
            Log::warning('Failed to send payout OTP SMS', [
                'payout_id' => $payout->id,
                'purpose' => $purpose,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }

        $this->notifications->sendPayoutOtpEmail($payout, $otp, $purpose, $user);

        return $otpRecord;
    }

    protected function submitPayoutToProvider(Payout $payout): array
    {
        if ($payout->payout_type === 'MOBILE_MONEY') {
            return $this->api->createMobileMoneyPayout(
                $payout->amount,
                $payout->recipient_phone,
                $payout->currency,
                $payout->order_reference
            );
        }

        return $this->api->createBankPayout(
            $payout->amount,
            $payout->currency,
            $payout->bank_account_number,
            $payout->recipient_name,
            $payout->bic,
            $payout->transfer_type ?? 'ACH',
            $payout->order_reference
        );
    }

    protected function buildOtpMessage(Payout $payout, string $otp, string $purpose, User $user): string
    {
        $heading = $purpose === 'payment_authorization'
            ? 'FEEDTAN PAYOUT AUTHORIZATION'
            : 'FEEDTAN PAYOUT INITIATION';

        $actionLine = $purpose === 'payment_authorization'
            ? 'Use this OTP to approve and authorize the payout for payment.'
            : 'Use this OTP to confirm payout initiation.';

        return "{$heading}\n"
            . "Reference: {$payout->order_reference}\n"
            . "Amount: {$payout->amount} {$payout->currency}\n"
            . "Recipient: {$payout->recipient_name}\n"
            . "Officer: {$user->name}\n"
            . "Reason: " . ($payout->resolvedDescription() ?: 'N/A') . "\n"
            . "OTP: {$otp}\n"
            . "{$actionLine}\n"
            . "Expires in 10 minutes.";
    }

    protected function broadcastAuthorizationAlert(Payout $payout, User $authorizer): void
    {
        $message = "Someone authorized the payment of {$payout->amount} {$payout->currency}. "
            . "It was made by " . ($payout->initiator?->name ?? 'Unknown officer')
            . " at " . now()->format('d M Y H:i')
            . " for reasons " . ($payout->resolvedDescription() ?: 'N/A') . '.';

        $this->notifications->notifyPayoutOfficers(
            'payout_authorized',
            'Payout Authorized',
            "Payout {$payout->order_reference} was authorized by {$authorizer->name}.",
            route('payouts.status', $payout->order_reference),
            [
                'payout_id' => $payout->id,
                'order_reference' => $payout->order_reference,
                'authorized_by' => $authorizer->name,
                'status' => $payout->status,
            ],
            'payout:' . $payout->id . ':authorized'
        );

        foreach ($this->notifications->payoutOfficers() as $officer) {
            if (!$officer->phone) {
                continue;
            }

            try {
                $this->sms->sendSMS($officer->phone, $message);
            } catch (\Exception $e) {
                Log::warning('Failed to send payout authorization alert SMS', [
                    'payout_id' => $payout->id,
                    'user_id' => $officer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function updatePayoutFromApi(Payout $payout, array $apiData)
    {
        $previousStatus = $payout->status;
        $beneficiary = $apiData['beneficiary'] ?? [];
        $payoutType = ($apiData['channel'] ?? '') === 'BANK TRANSFER' ? 'BANK' : 'MOBILE MONEY';
        $apiStatus = $apiData['status'] ?? $payout->status;
        $workflowStage = $payout->workflow_stage;

        if (in_array($apiStatus, ['SUCCESS', 'SETTLED'], true)) {
            $workflowStage = 'COMPLETED';
        } elseif (in_array($apiStatus, ['FAILED', 'ERROR', 'CANCELLED'], true)) {
            $workflowStage = 'FAILED';
        } elseif ($workflowStage === 'PROCESSING') {
            $workflowStage = 'PROCESSING';
        }

        $updateData = [
            'status' => $apiStatus,
            'workflow_stage' => $workflowStage,
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

        if ($previousStatus !== $apiStatus && in_array($apiStatus, ['SUCCESS', 'SETTLED', 'FAILED', 'ERROR', 'CANCELLED'], true)) {
            $title = in_array($apiStatus, ['SUCCESS', 'SETTLED'], true) ? 'Payout Completed' : 'Payout Update';
            $message = "Payout {$payout->order_reference} is now {$apiStatus}.";

            $this->notifications->notifyPayoutOfficers(
                'payout_status',
                $title,
                $message,
                route('payouts.status', $payout->order_reference),
                [
                    'payout_id' => $payout->id,
                    'order_reference' => $payout->order_reference,
                    'status' => $apiStatus,
                ],
                'payout:' . $payout->id . ':status:' . strtolower($apiStatus)
            );

            if (in_array($apiStatus, ['SUCCESS', 'SETTLED'], true)) {
                $this->notifications->sendPayoutSuccessEmail($payout);
            }
        }
    }
}
