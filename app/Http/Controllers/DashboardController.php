<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClickPesaAPIService;
use App\Models\BillPayNumber;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateFilter = $request->get('date_filter', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Build base query for transactions
        $baseQuery = Transaction::query();
        
        // Apply date filters to base query
        if ($dateFilter !== 'all') {
            if ($dateFilter === 'today') {
                $baseQuery->whereDate('created_at', now()->toDateString());
            } elseif ($dateFilter === 'week') {
                $baseQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($dateFilter === 'month') {
                $baseQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            } elseif ($dateFilter === 'year') {
                $baseQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
            } elseif ($dateFilter === 'custom' && $startDate && $endDate) {
                $baseQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // Get transaction counts and amounts (each uses clone)
        $totalTransactions = (clone $baseQuery)->count();
        $successfulTransactions = (clone $baseQuery)->whereIn('status', ['SUCCESS', 'SETTLED', 'success', 'settled'])->count();
        $pendingTransactions = (clone $baseQuery)->whereIn('status', ['PENDING', 'pending'])->count();
        $failedTransactions = (clone $baseQuery)->whereIn('status', ['FAILED', 'ERROR', 'failed', 'error'])->count();
        $totalAmount = (clone $baseQuery)->whereIn('status', ['SUCCESS', 'SETTLED', 'success', 'settled'])->sum('amount');
        $todayRevenue = Transaction::whereDate('created_at', now()->toDateString())->whereIn('status', ['SUCCESS', 'SETTLED', 'success', 'settled'])->sum('amount');
        $successRate = $totalTransactions > 0 ? round(($successfulTransactions / $totalTransactions) * 100, 2) : 0;

        // Get top customers
        $topCustomers = (clone $baseQuery)->whereNotNull('phone')
            ->selectRaw('phone, description, count(*) as count, sum(amount) as total_amount')
            ->groupBy('phone', 'description')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get()
            ->toArray();

        // Get payment methods
        $paymentMethods = (clone $baseQuery)->whereNotNull('payment_method')
            ->selectRaw('payment_method as name, count(*) as count')
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        // Daily stats (last 7 days)
        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dayTransactions = Transaction::whereDate('created_at', $date);
            $dailyStats[] = [
                'date' => $date,
                'count' => $dayTransactions->count(),
                'amount' => $dayTransactions->sum('amount'),
            ];
        }

        // Get recent transactions for display (only successful)
        $recentPayments = (clone $baseQuery)->whereIn('status', ['SUCCESS', 'SETTLED', 'success', 'settled'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($txn) {
                return [
                    'orderReference' => $txn->order_reference,
                    'customer_name' => $txn->customer_name ?? $txn->description ?? 'Payment',
                    'customer_phone' => $txn->phone,
                    'amount' => $txn->amount,
                    'status' => $txn->status,
                    'createdAt' => $txn->created_at,
                ];
            });

        // Get bill stats
        $billQuery = BillPayNumber::query();
        $totalBills = $billQuery->count();
        $activeBills = $billQuery->where('bill_status', 'ACTIVE')->count();
        $settledBills = $billQuery->where('total_paid', '>', 0)->count();
        $totalBillAmount = $billQuery->sum('bill_amount');
        $todayBills = $billQuery->whereDate('created_at', now()->toDateString())->count();

        // Get recent bills
        $recentBills = BillPayNumber::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Check API connection status
        try {
            $apiStatus = cache()->get('api_status', 'checking');
            if ($apiStatus === 'checking') {
                // Quick check if needed
                $apiStatus = 'connected'; // assume connected if cache is not set
            }
        } catch (\Exception $e) {
            $apiStatus = 'disconnected';
        }

        return view('dashboard.index', [
            'stats' => [
                'total_transactions' => $totalTransactions,
                'successful' => $successfulTransactions,
                'pending' => $pendingTransactions,
                'failed' => $failedTransactions,
                'total_amount' => $totalAmount,
                'today_revenue' => $todayRevenue,
                'success_rate' => $successRate,
                'top_customers' => $topCustomers,
                'payment_methods' => $paymentMethods,
                'daily_stats' => $dailyStats,
                'total_bills' => $totalBills,
                'active_bills' => $activeBills,
                'settled_bills' => $settledBills,
                'total_bill_amount' => $totalBillAmount,
                'today_bills' => $todayBills,
            ],
            'recentPayments' => $recentPayments,
            'recentBills' => $recentBills,
            'apiStatus' => $apiStatus,
        ]);
    }
}
