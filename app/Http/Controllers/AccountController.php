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
     * Get account statement (API endpoint)
     */
    public function statement(Request $request)
    {
        $request->validate([
            'currency' => 'sometimes|string|in:TZS,USD',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        try {
            $currency = $request->get('currency', 'TZS');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            
            $statementData = $this->api->getAccountStatement($currency, $startDate, $endDate);
            
            return response()->json([
                'success' => true,
                'data' => $statementData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
