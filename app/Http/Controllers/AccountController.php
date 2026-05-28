<?php

namespace App\Http\Controllers;

use App\Services\ClickPesaAPIService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountController extends Controller
{
    protected ClickPesaAPIService $api;

    public function __construct(ClickPesaAPIService $api)
    {
        $this->api = $api;
    }

    /**
     * Show account management page
     */
    public function index(Request $request)
    {
        $balanceData = null;
        $statementData = null;
        $error = null;
        $success = null;

        // Get account balance
        try {
            $balanceData = $this->api->getAccountBalance();
            if (isset($balanceData[0])) {
                $success = 'Account balance retrieved successfully!';
            }
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
            $balanceData = $this->api->getAccountBalance();
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
            $balanceData = $this->api->getAccountBalance();
            
            return response()->json([
                'success' => true,
                'data' => $balanceData
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
        $statusFilter = $request->get('status', 'all'); // Sub-tab status filter
        
        $error = null;
        $dbTransactions = collect();
        $apiTransactions = collect();

        // 1. Get from Database
        $query = \App\Models\Transaction::query()->where('type', 'payment');
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        if ($currency) $query->where('currency', $currency);
        
        $dbTransactions = $query->orderBy('created_at', 'desc')->get()->map(function($t) {
            return [
                'date' => $t->created_at->toIso8601String(),
                'created_at' => $t->created_at->toIso8601String(),
                'updated_at' => $t->updated_at?->toIso8601String(),
                'description' => $t->description ?? 'Payment Transaction',
                'amount' => (float) $t->amount,
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
                $apiTransactions = collect($apiResponse['transactions'])->map(function($t) {
                    $t['source'] = 'API';
                    $t['amount'] = (float)($t['amount'] ?? 0);
                    $t['status'] = strtoupper($t['status'] ?? 'UNKNOWN');
                    $t['reference'] = $t['orderReference'] ?? $t['reference'] ?? null;
                    $t['transaction_id'] = $t['id'] ?? $t['transaction_id'] ?? null;
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
        $filterByStatus = function($collection) use ($statusFilter) {
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
            // API tab shows all transactions regardless of status filter
            $displayTransactions = $apiTransactions->map(function($t) use ($dbRefs, $dbTids) {
                $t['is_synced'] = in_array($t['reference'], $dbRefs) || in_array($t['transaction_id'], $dbTids);
                return $t;
            });
        }

        // Statistics
        $mergedTransactions = $this->mergeAndDeduplicate($dbTransactions, $apiTransactions);
        $stats = $this->calculateTransactionStats($mergedTransactions->toArray());

        // Handle Export
        if ($request->has('export')) {
            return $this->exportStatement($displayTransactions, $stats, $request->get('export'), $activeTab, $currency);
        }

        return view('account.statement', [
            'displayTransactions' => $displayTransactions->sortByDesc(function ($transaction) {
                return $transaction['date'] ?? $transaction['created_at'] ?? $transaction['createdAt'] ?? null;
            }),
            'stats' => $stats,
            'error' => $error,
            'currency' => $currency,
            'currencyFilter' => $currency,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activeTab' => $activeTab,
            'statusFilter' => $statusFilter,
            'dbCount' => $dbTransactions->count(),
            'apiCount' => $apiTransactions->count(),
            // Counts for sub-tabs
            'settledCount' => ($activeTab === 'database' ? $dbTransactions : $apiTransactions)->filter(fn($t) => in_array($t['status'], ['SUCCESS', 'SETTLED']))->count(),
            'failedCount' => ($activeTab === 'database' ? $dbTransactions : $apiTransactions)->filter(fn($t) => $t['status'] === 'FAILED')->count()
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
}
