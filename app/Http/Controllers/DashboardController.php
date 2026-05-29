<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BillPayNumber;
use App\Models\Transaction;
use App\Services\AccountBalanceService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(
        protected AccountBalanceService $accountBalanceService
    ) {}
    private function settledStatuses(): array
    {
        return ['SUCCESS', 'SETTLED', 'success', 'settled'];
    }

    private function applyDateFilter($query, string $dateFilter, ?string $startDate = null, ?string $endDate = null)
    {
        return match ($dateFilter) {
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]),
            'quarter' => $query->whereBetween('created_at', [now()->copy()->subMonths(3)->startOfDay(), now()->endOfDay()]),
            'year' => $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]),
            'custom' => ($startDate && $endDate)
                ? $query->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
                : $query->whereDate('created_at', now()->toDateString()),
            default => $query->whereDate('created_at', now()->toDateString()),
        };
    }

    private function periodLabel(string $dateFilter): string
    {
        return match ($dateFilter) {
            'week' => 'This Week',
            'month' => 'This Month',
            'quarter' => 'Last 3 Months',
            'year' => 'This Year',
            'custom' => 'Selected Period',
            default => 'Today',
        };
    }

    public function index(Request $request)
    {
        $dateFilter = $request->get('date_filter', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $settledStatuses = $this->settledStatuses();
        $periodLabel = $this->periodLabel($dateFilter);

        $baseQuery = Transaction::query();
        $this->applyDateFilter($baseQuery, $dateFilter, $startDate, $endDate);

        $settledQuery = (clone $baseQuery)->whereIn('status', $settledStatuses);

        $periodSettledAmount = (clone $settledQuery)->sum('amount');
        $periodSuccessfulCount = (clone $settledQuery)->count();
        $periodTotalCount = (clone $baseQuery)->count();
        $successRate = $periodTotalCount > 0
            ? round(($periodSuccessfulCount / $periodTotalCount) * 100, 2)
            : 0;

        $accountBalance = $this->accountBalanceService->getTzsBalance(refresh: true);

        $topCustomers = (clone $settledQuery)->whereNotNull('phone')
            ->selectRaw('phone, max(customer_name) as customer_name, max(description) as description, count(*) as count, sum(amount) as total_amount')
            ->groupBy('phone')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'name' => $row->customer_name ?: $row->description ?: $row->phone,
                    'phone' => $row->phone,
                    'count' => (int) $row->count,
                    'total_amount' => $row->total_amount,
                ];
            })
            ->toArray();

        $paymentMethods = (clone $settledQuery)->whereNotNull('payment_method')
            ->selectRaw('payment_method as name, count(*) as count')
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        $dailyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $daySettled = Transaction::whereDate('created_at', $date)
                ->whereIn('status', $settledStatuses);

            $dailyStats[] = [
                'date' => $date,
                'count' => $daySettled->count(),
                'amount' => $daySettled->sum('amount'),
            ];
        }

        $recentPayments = (clone $settledQuery)
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

        $recentBills = BillPayNumber::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        try {
            $apiStatus = cache()->get('api_status', 'checking');
            if ($apiStatus === 'checking') {
                $apiStatus = 'connected';
            }
        } catch (\Exception $e) {
            $apiStatus = 'disconnected';
        }

        return view('dashboard.index', [
            'dateFilter' => $dateFilter,
            'periodLabel' => $periodLabel,
            'accountBalance' => $accountBalance,
            'stats' => [
                'period_settled_amount' => $periodSettledAmount,
                'period_successful_count' => $periodSuccessfulCount,
                'success_rate' => $successRate,
                'top_customers' => $topCustomers,
                'payment_methods' => $paymentMethods,
                'daily_stats' => $dailyStats,
            ],
            'recentPayments' => $recentPayments,
            'recentBills' => $recentBills,
            'apiStatus' => $apiStatus,
        ]);
    }

    public function accountBalance()
    {
        $balance = $this->accountBalanceService->getTzsBalance(refresh: true);

        return response()->json([
            'success' => true,
            'data' => $balance,
        ]);
    }
}
