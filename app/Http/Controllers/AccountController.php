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
        $search = $request->get('search');
        
        $error = null;
        $displayTransactions = collect();
        $displayBills = collect();
        $dbTransactions = collect();
        $apiTransactions = collect();
        $totalPaidFilter = false;
        $dbCount = 0;
        $apiCount = 0;

        if ($statementType === 'payments') {
            // 1. Get PAYMENTS from Database
            $query = \App\Models\Transaction::query()->where('type', 'payment');
            if ($startDate) $query->whereDate('created_at', '>=', $startDate);
            if ($endDate) $query->whereDate('created_at', '<=', $endDate);
            if ($currency) $query->where('currency', $currency);
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_reference', 'like', "%$search%")
                      ->orWhere('transaction_id', 'like', "%$search%")
                      ->orWhere('customer_name', 'like', "%$search%")
                      ->orWhere('payer_name', 'like', "%$search%")
                      ->orWhere('phone', 'like', "%$search%");
                });
            }
            
            $dbPayments = $query->orderBy('created_at', 'desc')->get()->map(function ($t) {
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
                    'type' => 'payment',
                    'sms_sent' => (bool)$t->sms_sent,
                    'sms_sent_at' => $t->sms_sent_at?->toIso8601String(),
                    'sms_message' => $t->sms_message,
                    'sms_error' => $t->sms_error,
                    'email_sent' => (bool)$t->email_sent,
                    'email_sent_at' => $t->email_sent_at?->toIso8601String(),
                    'email_error' => $t->email_error,
                ];
            });

            // 2. Get PAYOUTS (Withdrawals) from Database
            $payoutQuery = \App\Models\Payout::query();
            if ($startDate) $payoutQuery->whereDate('created_at', '>=', $startDate);
            if ($endDate) $payoutQuery->whereDate('created_at', '<=', $endDate);
            if ($currency) $payoutQuery->where('currency', $currency);
            if ($search) {
                $payoutQuery->where(function ($q) use ($search) {
                    $q->where('order_reference', 'like', "%$search%")
                      ->orWhere('clickpesa_payout_id', 'like', "%$search%")
                      ->orWhere('recipient_name', 'like', "%$search%")
                      ->orWhere('recipient_phone', 'like', "%$search%");
                });
            }
            $dbPayouts = $payoutQuery->orderBy('created_at', 'desc')->get()->map(function ($p) {
                return [
                    'date' => $p->created_at->toIso8601String(),
                    'created_at' => $p->created_at->toIso8601String(),
                    'updated_at' => $p->updated_at?->toIso8601String(),
                    'description' => $p->notes ?? "Payout to {$p->recipient_name}",
                    'amount' => (float)$p->amount,
                    'currency' => $p->currency ?? 'TZS',
                    'entry' => 'DEBIT',
                    'status' => strtoupper($p->status ?? 'UNKNOWN'),
                    'reference' => $p->order_reference,
                    'order_reference' => $p->order_reference,
                    'transaction_id' => $p->clickpesa_payout_id,
                    'source' => 'DATABASE',
                    'customer_name' => $p->recipient_name,
                    'payer_name' => $p->recipient_name,
                    'phone' => $p->recipient_phone,
                    'email' => $p->beneficiary_email ?? null,
                    'payment_method' => $p->channel ?? null,
                    'type' => 'payout',
                    'sms_sent' => false,
                    'sms_sent_at' => null,
                    'sms_message' => null,
                    'sms_error' => null,
                    'email_sent' => false,
                    'email_sent_at' => null,
                    'email_error' => null,
                ];
            });

            // Get ALL DB transactions (regardless of date range/search) to calculate correct starting balance
            $allDbPayments = \App\Models\Transaction::query()
                ->where('type', 'payment')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($t) {
                    return [
                        'date' => $t->created_at->toIso8601String(),
                        'amount' => (float)$t->amount,
                        'currency' => $t->currency ?? 'TZS',
                        'entry' => 'CREDIT',
                        'status' => strtoupper($t->status),
                    ];
                });

            $allDbPayouts = \App\Models\Payout::query()
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($p) {
                    return [
                        'date' => $p->created_at->toIso8601String(),
                        'amount' => (float)$p->amount,
                        'currency' => $p->currency ?? 'TZS',
                        'entry' => 'DEBIT',
                        'status' => strtoupper($p->status ?? 'UNKNOWN'),
                    ];
                });

            // Combine and calculate total balance up to NOW
            $allDbCombined = $allDbPayments->merge($allDbPayouts)->sortBy('date')->values();
            $startingBalance = 0;
            foreach ($allDbCombined as $t) {
                if (in_array($t['status'], ['SUCCESS', 'SETTLED', 'COMPLETED'])) {
                    if ($t['entry'] === 'CREDIT') {
                        $startingBalance += $t['amount'];
                    } else {
                        $startingBalance -= $t['amount'];
                    }
                }
            }

            // Combine filtered payments and payouts, sort by date ascending
            $dbTransactions = $dbPayments->merge($dbPayouts)->sortBy('date')->values();
            $dbCount = $dbTransactions->count();

            // Calculate running balance for filtered list, starting from total balance and working backwards
            $runningBalance = $startingBalance;
            $dbTransactions = $dbTransactions->reverse()->map(function ($t) use (&$runningBalance) {
                // We need to go backwards, so subtract credits and add debits
                if (in_array($t['status'], ['SUCCESS', 'SETTLED', 'COMPLETED'])) {
                    if ($t['entry'] === 'CREDIT') {
                        $runningBalance -= $t['amount'];
                    } else {
                        $runningBalance += $t['amount'];
                    }
                }
                $t['running_balance'] = $runningBalance;
                return $t;
            })->reverse();
            
            // Now re-add the first transaction's amount to get the correct starting balance for the first row
            if ($dbTransactions->isNotEmpty()) {
                $firstTransaction = $dbTransactions->first();
                if (in_array($firstTransaction['status'], ['SUCCESS', 'SETTLED', 'COMPLETED'])) {
                    if ($firstTransaction['entry'] === 'CREDIT') {
                        $firstTransaction['running_balance'] += $firstTransaction['amount'];
                    } else {
                        $firstTransaction['running_balance'] -= $firstTransaction['amount'];
                    }
                    $dbTransactions[0] = $firstTransaction;
                }
            }

            // 3. Fetch API data
            try {
                $apiResponse = $this->api->getAccountStatement($currency, $startDate, $endDate);
                if (isset($apiResponse['transactions'])) {
                    $apiTransactions = collect($apiResponse['transactions'])->map(function ($t) use ($currency) {
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
                        
                        // Determine if it's a payout (debit) or payment (credit)
                        $apiType = strtolower($t['type'] ?? 'payment');
                        $t['type'] = $apiType;
                        $t['entry'] = $apiType === 'payout' || $apiType === 'debit' || (isset($t['is_withdrawal']) && $t['is_withdrawal']) ? 'DEBIT' : 'CREDIT';
                        
                        return $t;
                    });
                    
                    if ($search) {
                        $apiTransactions = $apiTransactions->filter(function ($t) use ($search) {
                            return stripos($t['reference'], $search) !== false
                                || stripos($t['transaction_id'], $search) !== false
                                || stripos($t['customer_name'], $search) !== false
                                || stripos($t['payer_name'], $search) !== false
                                || stripos($t['phone'], $search) !== false;
                        });
                    }
                    $apiCount = $apiTransactions->count();
                }
            } catch (Exception $e) {
                $error = 'API Fetch Error: ' . $e->getMessage();
            }

            // 4. Identification and Cross-Referencing
            $dbRefs = $dbTransactions->pluck('reference')->filter()->unique()->toArray();
            $dbTids = $dbTransactions->pluck('transaction_id')->filter()->unique()->toArray();

            // 5. Apply Status Sub-Tab Filtering
            $filterByStatus = function ($collection) use ($statusFilter) {
                if ($statusFilter === 'settled') {
                    return $collection->filter(fn($t) => in_array($t['status'], ['SUCCESS', 'SETTLED', 'COMPLETED']));
                } elseif ($statusFilter === 'failed') {
                    return $collection->filter(fn($t) => in_array($t['status'], ['FAILED', 'ERROR', 'CANCELLED']));
                }
                return $collection;
            };

            if ($activeTab === 'database') {
                // Sort DB transactions by date ascending (earliest first) and recalc running balance
                $sortedDbTransactions = $dbTransactions->sortBy(function ($t) {
                    return $t['date'] ?? $t['created_at'] ?? null;
                })->values();
                
                $dbRunningBalance = 0;
                $sortedDbTransactions = $sortedDbTransactions->map(function ($t) use (&$dbRunningBalance) {
                    if ($t['entry'] === 'CREDIT') {
                        $dbRunningBalance += $t['amount'];
                    } else {
                        $dbRunningBalance -= $t['amount'];
                    }
                    $t['running_balance'] = $dbRunningBalance;
                    return $t;
                });
                
                $displayTransactions = $filterByStatus($sortedDbTransactions);
            } else {
                // Sort API transactions by date ascending (earliest first) and calculate running balance
                $sortedApiTransactions = $apiTransactions->sortBy(function ($t) {
                    return $t['date'] ?? $t['created_at'] ?? $t['createdAt'] ?? null;
                })->values();
                
                $apiRunningBalance = 0;
                $sortedApiTransactions = $sortedApiTransactions->map(function ($t) use (&$apiRunningBalance, $dbRefs, $dbTids) {
                    $t['is_synced'] = in_array($t['reference'], $dbRefs) || in_array($t['transaction_id'], $dbTids);
                    if ($t['entry'] === 'CREDIT') {
                        $apiRunningBalance += $t['amount'];
                    } else {
                        $apiRunningBalance -= $t['amount'];
                    }
                    $t['running_balance'] = $apiRunningBalance;
                    return $t;
                });
                
                $displayTransactions = $filterByStatus($sortedApiTransactions);
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
                $displayBills = $query->orderBy('created_at', 'asc')->get();
            } else {
                $displayBills = $query->orderBy('created_at', 'asc')->paginate(20);
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
            'displayTransactions' => $statementType === 'payments' ? $displayTransactions : collect(),
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

    /**
     * Fetch single payout from ClickPesa API
     */
    public function fetchSinglePayout(Request $request)
    {
        $validated = $request->validate([
            'order_reference' => 'required|string',
        ]);

        try {
            $payout = $this->api->queryPayoutStatus($validated['order_reference']);
            return response()->json([
                'success' => true,
                'data' => $payout
            ]);
        } catch (Exception $e) {
            Log::error('Failed to fetch single payout: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync single payout from API to database
     */
    public function syncSinglePayout(Request $request)
    {
        $validated = $request->validate([
            'order_reference' => 'required|string',
        ]);

        try {
            $orderReference = $validated['order_reference'];
            
            // Check if payout already exists
            $existing = \App\Models\Payout::where('order_reference', $orderReference)->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payout already exists in database'
                ]);
            }

            // Fetch from API
            $apiPayout = $this->api->queryPayoutStatus($orderReference);
            $beneficiary = $apiPayout['beneficiary'] ?? [];
            $payoutType = ($apiPayout['channel'] ?? '') === 'BANK TRANSFER' ? 'BANK' : 'MOBILE MONEY';

            // Create payout in DB
            \App\Models\Payout::create([
                'order_reference' => $orderReference,
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
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payout synced successfully!'
            ]);
        } catch (Exception $e) {
            Log::error('Failed to sync single payout: ' . $e->getMessage(), [
                'order_reference' => $request->order_reference,
                'error' => $e
            ]);
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
