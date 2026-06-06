<?php

namespace App\Http\Controllers;

use App\Services\ClickPesaAPIService;
use App\Services\AccountBalanceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    public function __construct(
        protected ClickPesaAPIService $api,
        protected AccountBalanceService $accountBalanceService
    ) {}

    /**
     * Show account management page
     */
    public function index(Request $request)
    {
        $balanceData = null;
        $statementData = null;
        $error = null;
        $success = null;

        try {
            $tzsBalance = $this->accountBalanceService->getTzsBalance(refresh: true);
            $balanceData = [['currency' => 'TZS', 'balance' => $tzsBalance['balance']]];
            $success = 'Account balance retrieved successfully!';
        } catch (Exception $e) {
            $error = 'Failed to retrieve account balance: ' . $e->getMessage();
            Log::error($error);
        }

        // Get account statement if requested
        if ($request->hasAny(['get_statement', 'currency', 'start_date', 'end_date'])) {
            try {
                $currency = $request->get('currency', 'TZS');
                $startDate = $request->get('start_date');
                $endDate = $request->get('end_date');
                
                // Debug: Log the currency value
                Log::info('Account statement request', [
                    'currency' => $currency,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]);
                
                $statementData = $this->api->getAccountStatement($currency, $startDate, $endDate);
                
                // Process transaction statistics
                $transactions = $statementData['transactions'] ?? [];
                $stats = $this->calculateTransactionStats($transactions);
                
                $success = 'Account statement retrieved successfully!';
            } catch (Exception $e) {
                $error = 'Failed to retrieve account statement: ' . $e->getMessage();
                Log::error($error);
                $transactions = [];
                $stats = [];
            }
        } else {
            $transactions = [];
            $stats = [];
        }

        return view('account.index', compact('balanceData', 'statementData', 'transactions', 'stats', 'error', 'success'));
    }

    /**
     * Get account balance page
     */
    public function balance()
    {
        $balanceData = null;
        $error = null;

        try {
            $tzsBalance = $this->accountBalanceService->getTzsBalance(refresh: true);
            $balanceData = [['currency' => 'TZS', 'balance' => $tzsBalance['balance']]];
        } catch (Exception $e) {
            $error = 'Failed to retrieve account balance: ' . $e->getMessage();
            Log::error($error);
        }

        return view('account.balance', compact('balanceData', 'error'));
    }

    /**
     * Calculate transaction statistics
     */
    private function calculateTransactionStats(array $transactions): array
    {
        $stats = [
            'total' => count($transactions),
            'successful' => 0,
            'pending' => 0,
            'failed' => 0,
            'total_credits' => 0,
            'total_debits' => 0
        ];

        foreach ($transactions as $transaction) {
            $status = strtolower($transaction['status'] ?? 'unknown');
            $entry = strtoupper($transaction['entry'] ?? 'CREDIT');
            $amount = floatval($transaction['amount'] ?? 0);

            if ($status === 'success' || $status === 'settled') {
                $stats['successful']++;
            } elseif ($status === 'pending' || $status === 'processing') {
                $stats['pending']++;
            } elseif ($status === 'failed') {
                $stats['failed']++;
            }

            if ($entry === 'CREDIT') {
                $stats['total_credits'] += $amount;
            } else {
                $stats['total_debits'] += $amount;
            }
        }

        return $stats;
    }

    /**
     * Get account balance (API endpoint)
     */
    public function balanceApi()
    {
        try {
            $balance = $this->accountBalanceService->getTzsBalance(refresh: true);

            return response()->json([
                'success' => true,
                'data' => [
                    ['currency' => $balance['currency'], 'balance' => $balance['balance']],
                ],
                'synced_at' => $balance['synced_at'],
                'live' => $balance['live'],
            ]);
        } catch (Exception $e) {
            Log::error('Balance API error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get account statement page
     */
    public function statement(Request $request)
    {
        $currency = $request->get('currency', 'TZS');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $activeTab = $request->get('tab', 'database'); 
        $statusFilter = $request->get('status', 'all');
        $statementType = $request->get('type', 'payments');
        
        $error = null;
        $displayTransactions = collect();
        $displayBills = collect();
        $dbTransactions = collect();
        $apiTransactions = collect();
        $totalPaidFilter = false;

        if ($statementType === 'payments') {
            // 1. Get from Database
            $query = \App\Models\Transaction::query()->where('type', 'payment');
            if ($startDate) $query->whereDate('created_at', '>=', $startDate);
            if ($endDate) $query->whereDate('created_at', '<=', $endDate);
            if ($currency) $query->where('currency', $currency);
            
            $dbTransactions = $query->orderBy('created_at', 'desc')->get()->map(function ($t) {
                return [
                    'date' => $t->created_at->toIso8601String(),
                    'created_at' => $t->created_at->toIso8601String(),
                    'updated_at' => $t->updated_at?->toIso8601String(),
                    'description' => $t->resolvedDescription(),
                    'amount' => (float)$t->amount,
                    'currency' => $t->currency ?? 'TZS',
                    'entry' => 'CREDIT',
                    'status' => strtoupper($t->status),
                    'reference' => $t->order_reference,
                    'order_reference' => $t->order_reference,
                    'transaction_id' => $t->transaction_id,
                    'source' => 'DATABASE',
                    'customer_name' => $t->customer_name,
                    'payer_name' => $t->payer_name,
                    'phone' => $t->phone,
                    'email' => $t->email,
                    'payment_method' => $t->payment_method,
                    'type' => $t->type,
                ];
            });

            // 2. Fetch API data
            try {
                $apiResponse = $this->api->getAccountStatement($currency, $startDate, $endDate);
                if (isset($apiResponse['transactions'])) {
                    $apiTransactions = collect($apiResponse['transactions'])->map(function ($t) {
                        $t['source'] = 'API';
                        $t['amount'] = (float) ($t['amount'] ?? $t['collectedAmount'] ?? 0);
                        $t['status'] = strtoupper($t['status'] ?? 'UNKNOWN');
                        $t['reference'] = $t['orderReference'] ?? $t['reference'] ?? null;
                        $t['order_reference'] = $t['orderReference'] ?? $t['reference'] ?? null;
                        $t['transaction_id'] = $t['id'] ?? $t['transaction_id'] ?? null;
                        $t['currency'] = $t['currency'] ?? $t['collectedCurrency'] ?? $currency;
                        $t['customer_name'] = $t['customer_name'] ?? (isset($t['customer']) ? $t['customer']['customerName'] : null);
                        $t['payer_name'] = $t['payer_name'] ?? (isset($t['customer']) ? $t['customer']['customerName'] : $t['customer_name']);
                        $t['phone'] = $t['phone'] ?? $t['paymentPhoneNumber'] ?? (isset($t['customer']) ? $t['customer']['customerPhoneNumber'] : null);
                        $t['payment_method'] = $t['payment_method'] ?? $t['channel'] ?? $t['paymentMethod'] ?? null;
                        $t['description'] = $t['description'] ?? $t['narrative'] ?? null;
                        $t['created_at'] = $t['created_at'] ?? $t['createdAt'] ?? $t['date'] ?? null;
                        $t['updated_at'] = $t['updated_at'] ?? $t['updatedAt'] ?? null;
                        return $t;
                    });
                }
            } catch (Exception $e) {
                $error = 'API Fetch Error: ' . $e->getMessage();
            }

            // 3. Identification and Cross-Referencing
            $dbRefs = $dbTransactions->pluck('reference')->filter()->unique()->toArray();
            $dbTids = $dbTransactions->pluck('transaction_id')->filter()->unique()->toArray();

            // 4. Apply Status Sub-Tab Filtering
            $filterByStatus = function ($collection) use ($statusFilter) {
                if ($statusFilter === 'settled') {
                    return $collection->filter(fn($t) => in_array($t['status'], ['SUCCESS', 'SETTLED']));
                } elseif ($statusFilter === 'failed') {
                    return $collection->filter(fn($t) => $t['status'] === 'FAILED');
                }
                return $collection;
            };

            if ($activeTab === 'database') {
                $displayTransactions = $filterByStatus($dbTransactions);
            } else {
                $displayTransactions = $apiTransactions->map(function ($t) use ($dbRefs, $dbTids) {
                    $t['is_synced'] = in_array($t['reference'], $dbRefs) || in_array($t['transaction_id'], $dbTids);
                    return $t;
                });
            }
        } else {
            // Handle Billing Statement
            $query = \App\Models\BillPayNumber::query();
            if ($startDate) $query->whereDate('created_at', '>=', $startDate);
            if ($endDate) $query->whereDate('created_at', '<=', $endDate);
            if ($currency) $query->where('bill_currency', $currency);
            
            if ($statusFilter === 'settled') {
                $query->where('total_paid', '>', 0);
                $totalPaidFilter = true;
            } elseif ($statusFilter === 'failed') {
                $query->where('bill_status', '!=', 'ACTIVE');
            }
            
            if ($request->has('export')) {
                $displayBills = $query->orderBy('created_at', 'desc')->get();
            } else {
                $displayBills = $query->orderBy('created_at', 'desc')->paginate(20);
            }
        }

        // Statistics
        $stats = [];
        $billingStats = [];
        if ($statementType === 'payments') {
            $mergedTransactions = $this->mergeAndDeduplicate($dbTransactions, $apiTransactions);
            $stats = $this->calculateTransactionStats($mergedTransactions->toArray());
        } else {
            $billsForStats = $request->has('export') ? $displayBills : $displayBills->items();
            $billingStats = [
                'totalBills' => $request->has('export') ? $displayBills->count() : $displayBills->total(),
                'totalAmount' => collect($billsForStats)->sum('bill_amount'),
                'totalPaid' => collect($billsForStats)->sum('total_paid'),
                'showPaid' => $totalPaidFilter || collect($billsForStats)->sum('total_paid') > 0
            ];
        }

        // Handle Export
        if ($request->has('export')) {
            if ($statementType === 'payments') {
                return $this->exportStatement($displayTransactions, $stats, $request->get('export'), $activeTab, $currency);
            } else {
                return $this->exportBillingStatement($displayBills, $billingStats, $request->get('export'), $currency);
            }
        }

        return view('account.statement', [
            'displayTransactions' => $statementType === 'payments' ? $displayTransactions->sortByDesc(function ($transaction) {
                return $transaction['date'] ?? $transaction['created_at'] ?? $transaction['createdAt'] ?? null;
            }) : collect(),
            'displayBills' => $displayBills,
            'stats' => $stats,
            'error' => $error,
            'currency' => $currency,
            'currencyFilter' => $currency,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activeTab' => $activeTab,
            'statusFilter' => $statusFilter,
            'statementType' => $statementType,
            'totalPaidFilter' => $totalPaidFilter,
            'dbCount' => $dbTransactions->count(),
            'apiCount' => $apiTransactions->count(),
            'settledCount' => $statementType === 'payments' ? ($activeTab === 'database' ? $dbTransactions : $apiTransactions)->filter(fn($t) => in_array($t['status'], ['SUCCESS', 'SETTLED']))->count() : 0,
            'failedCount' => $statementType === 'payments' ? ($activeTab === 'database' ? $dbTransactions : $apiTransactions)->filter(fn($t) => $t['status'] === 'FAILED')->count() : 0
        ]);
    }

    /**
     * Merge DB and API transactions without duplicates
     */
    private function mergeAndDeduplicate($db, $api)
    {
        $merged = collect($db);
        
        $dbRefs = $db->pluck('reference')->filter()->unique()->toArray();
        $dbTids = $db->pluck('transaction_id')->filter()->unique()->toArray();

        foreach ($api as $t) {
            $ref = $t['reference'] ?? null;
            $tid = $t['transaction_id'] ?? null;
            
            if (!in_array($ref, $dbRefs) && !in_array($tid, $dbTids)) {
                $merged->push($t);
            }
        }

        return $merged;
    }

    /**
     * Export Statement to PDF or Excel
     */
    private function exportStatement($transactions, $stats, $format, $tab, $currency)
    {
        $data = [
            'transactions' => $transactions->sortByDesc('date'),
            'stats' => $stats,
            'tab' => $tab,
            'currency' => $currency,
            'date' => date('Y-m-d H:i:s')
        ];

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('account.exports.statement_pdf', $data)
                ->setPaper('a4', 'landscape');
            return $pdf->download('account-statement-' . $tab . '-' . date('Y-m-d') . '.pdf');
        } else {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\PaymentHistoryExport($transactions->toArray()), 
                'account-statement-' . $tab . '-' . date('Y-m-d') . '.xlsx'
            );
        }
    }

    /**
     * Export Billing Statement to PDF or Excel
     */
    private function exportBillingStatement($bills, $stats, $format, $currency)
    {
        $data = [
            'bills' => $bills,
            'totalBills' => $stats['totalBills'],
            'totalAmount' => $stats['totalAmount'],
            'totalPaid' => $stats['totalPaid'],
            'showPaid' => $stats['showPaid'],
            'currency' => $currency,
            'date' => date('Y-m-d H:i:s')
        ];

        if ($format === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('account.exports.billing_statement_pdf', $data)
                ->setPaper('a4', 'landscape');
            return $pdf->download('billing-statement-' . date('Y-m-d') . '.pdf');
        } else {
            // For Excel, we can create a simple export array
            $exportData = $bills->map(function ($bill) {
                return [
                    'Date' => $bill->created_at->format('Y-m-d H:i'),
                    'Control Number' => $bill->bill_pay_number,
                    'Description' => $bill->bill_description,
                    'Type' => strtoupper($bill->bill_type),
                    'Status' => strtoupper($bill->bill_status),
                    'Amount' => $bill->bill_currency . ' ' . number_format($bill->bill_amount, 2),
                    'Paid' => $bill->bill_currency . ' ' . number_format($bill->total_paid, 2),
                ];
            })->toArray();
            
            // Create a simple Excel export (or use a dedicated Export class if needed)
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\PaymentHistoryExport($exportData), 
                'billing-statement-' . date('Y-m-d') . '.xlsx'
            );
        }
    }

    /**
     * Fetch single transaction from ClickPesa API
     */
    public function fetchSingleTransaction(Request $request)
    {
        $validated = $request->validate([
            'order_reference' => 'required|string',
        ]);

        try {
            $transaction = $this->api->queryPaymentStatus($validated['order_reference']);
            return response()->json([
                'success' => true,
                'data' => $transaction
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch single transaction: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync single transaction from API to database only if successful/settled
     */
    public function syncSingleTransaction(Request $request)
    {
        $validated = $request->validate([
            'order_reference' => 'required|string',
        ]);

        try {
            $orderReference = $validated['order_reference'];
            
            // Check if transaction already exists
            $existing = \App\Models\Transaction::where('order_reference', $orderReference)->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction already exists in database'
                ]);
            }

            // Fetch from API
            $apiTransaction = $this->api->queryPaymentStatus($orderReference);
            
            // Check status is SUCCESS or SETTLED
            $status = strtoupper($apiTransaction['status'] ?? $apiTransaction['status'] ?? 'UNKNOWN');
            if (!in_array($status, ['SUCCESS', 'SETTLED'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction is not successful/settled, skipping sync'
                ]);
            }

            // Extract transaction data
            $transactionId = $apiTransaction['transaction_id'] ?? $apiTransaction['id'] ?? null;
            $amount = $apiTransaction['amount'] ?? $apiTransaction['collectedAmount'] ?? 0;
            $currency = $apiTransaction['currency'] ?? $apiTransaction['collectedCurrency'] ?? 'TZS';
            $phone = $apiTransaction['phone'] ?? $apiTransaction['paymentPhoneNumber'] ?? ($apiTransaction['customer']['customerPhoneNumber'] ?? null);
            $customerName = $apiTransaction['customer_name'] ?? ($apiTransaction['customer']['customerName'] ?? null);
            $payerName = $apiTransaction['payer_name'] ?? ($apiTransaction['customer']['customerName'] ?? $customerName);
            $paymentMethod = $apiTransaction['payment_method'] ?? $apiTransaction['channel'] ?? $apiTransaction['paymentMethod'] ?? null;
            $description = $apiTransaction['description'] ?? $apiTransaction['narrative'] ?? 'Synced from API';

            // Create transaction in DB
            $transaction = \App\Models\Transaction::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'order_reference' => $orderReference,
                'transaction_id' => $transactionId,
                'status' => $status,
                'amount' => (float) $amount,
                'currency' => $currency,
                'phone' => $phone,
                'customer_name' => $customerName,
                'payer_name' => $payerName,
                'description' => $description,
                'payment_method' => $paymentMethod,
                'callback_data' => $apiTransaction,
                'callback_received_at' => now(),
                'type' => $this->determineTransactionType($apiTransaction),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaction synced successfully!',
                'data' => $transaction
            ]);
        } catch (Exception $e) {
            Log::error('Failed to sync single transaction: ' . $e->getMessage(), [
                'order_reference' => $request->order_reference,
                'error' => $e
            ]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine transaction type from data
     */
    private function determineTransactionType(array $data): string
    {
        return match (strtolower($data['type'] ?? 'payment')) {
            'payment' => 'payment',
            'payout' => 'payout',
            'refund' => 'refund',
            default => 'payment'
        };
    }
}
