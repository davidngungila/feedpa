<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $averageTransactionValue = $periodSuccessfulCount > 0
            ? round($periodSettledAmount / $periodSuccessfulCount, 2)
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
            ->selectRaw('payment_method as name, count(*) as count, sum(amount) as total_amount')
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        // Get date range for period stats
        $dateRange = $this->getDateRange($dateFilter, $startDate, $endDate);
        $dailyStats = $this->generateDailyStats($dateRange['start'], $dateRange['end'], $settledStatuses);
        $monthlyStats = $this->generateMonthlyStats($dateRange['start'], $dateRange['end'], $settledStatuses);
        $statusStats = (clone $baseQuery)
            ->selectRaw('status, count(*) as count, sum(amount) as amount')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        // Get top payment purposes
        $topPurposes = (clone $settledQuery)->whereNotNull('description')
            ->selectRaw('description, count(*) as count, sum(amount) as total_amount')
            ->groupBy('description')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get()
            ->toArray();

        // Get previous period stats for comparison
        $previousDateRange = $this->getPreviousPeriodDateRange($dateRange, $dateFilter);
        $previousBaseQuery = Transaction::query();
        $previousBaseQuery->whereBetween('created_at', [$previousDateRange['start'], $previousDateRange['end']]);
        $previousSettledQuery = (clone $previousBaseQuery)->whereIn('status', $settledStatuses);
        $previousPeriodSettledAmount = (clone $previousSettledQuery)->sum('amount');
        $previousPeriodSuccessfulCount = (clone $previousSettledQuery)->count();

        // Calculate growth rates
        $amountGrowthRate = $previousPeriodSettledAmount > 0
            ? round((($periodSettledAmount - $previousPeriodSettledAmount) / $previousPeriodSettledAmount) * 100, 2)
            : 0;
        $countGrowthRate = $previousPeriodSuccessfulCount > 0
            ? round((($periodSuccessfulCount - $previousPeriodSuccessfulCount) / $previousPeriodSuccessfulCount) * 100, 2)
            : 0;

        // Generate AI insights
        $aiInsights = $this->generateAiInsights([
            'period_settled_amount' => $periodSettledAmount,
            'period_successful_count' => $periodSuccessfulCount,
            'success_rate' => $successRate,
            'average_transaction_value' => $averageTransactionValue,
            'amount_growth_rate' => $amountGrowthRate,
            'count_growth_rate' => $countGrowthRate,
            'top_purposes' => $topPurposes,
            'top_customers' => $topCustomers,
            'payment_methods' => $paymentMethods,
        ]);

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
                'average_transaction_value' => $averageTransactionValue,
                'top_customers' => $topCustomers,
                'payment_methods' => $paymentMethods,
                'daily_stats' => $dailyStats,
                'monthly_stats' => $monthlyStats,
                'status_stats' => $statusStats,
                'top_purposes' => $topPurposes,
                'amount_growth_rate' => $amountGrowthRate,
                'count_growth_rate' => $countGrowthRate,
                'previous_period_settled_amount' => $previousPeriodSettledAmount,
                'previous_period_successful_count' => $previousPeriodSuccessfulCount,
            ],
            'aiInsights' => $aiInsights,
            'recentPayments' => $recentPayments,
            'apiStatus' => $apiStatus,
        ]);
    }

    private function getDateRange(string $dateFilter, ?string $startDate, ?string $endDate): array
    {
        return match ($dateFilter) {
            'week' => ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()],
            'month' => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
            'quarter' => ['start' => now()->copy()->subMonths(3)->startOfDay(), 'end' => now()->endOfDay()],
            'year' => ['start' => now()->startOfYear(), 'end' => now()->endOfYear()],
            'custom' => ($startDate && $endDate)
                ? ['start' => Carbon::parse($startDate)->startOfDay(), 'end' => Carbon::parse($endDate)->endOfDay()]
                : ['start' => now()->startOfDay(), 'end' => now()->endOfDay()],
            default => ['start' => now()->startOfDay(), 'end' => now()->endOfDay()],
        };
    }

    private function generateDailyStats(Carbon $start, Carbon $end, array $settledStatuses): array
    {
        $dailyStats = [];
        $currentDate = $start->copy();
        while ($currentDate->lte($end)) {
            $dateStr = $currentDate->toDateString();
            $dayQuery = Transaction::whereDate('created_at', $dateStr)->whereIn('status', $settledStatuses);
            $dailyStats[] = [
                'date' => $currentDate->format('M j'),
                'full_date' => $dateStr,
                'count' => $dayQuery->count(),
                'amount' => $dayQuery->sum('amount'),
            ];
            $currentDate->addDay();
        }
        return $dailyStats;
    }

    private function generateMonthlyStats(Carbon $start, Carbon $end, array $settledStatuses): array
    {
        $monthlyStats = [];
        $currentDate = $start->copy()->startOfMonth();
        $endDate = $end->copy()->startOfMonth();
        
        while ($currentDate->lte($endDate)) {
            $monthQuery = Transaction::whereYear('created_at', $currentDate->year)
                ->whereMonth('created_at', $currentDate->month)
                ->whereIn('status', $settledStatuses);
            $monthlyStats[] = [
                'month' => $currentDate->format('M Y'),
                'count' => $monthQuery->count(),
                'amount' => $monthQuery->sum('amount'),
            ];
            $currentDate->addMonth();
        }
        return $monthlyStats;
    }

    private function getPreviousPeriodDateRange(array $currentDateRange, string $dateFilter): array
    {
        $currentStart = $currentDateRange['start'];
        $currentEnd = $currentDateRange['end'];
        $diffDays = $currentStart->diffInDays($currentEnd) + 1;

        $previousStart = $currentStart->copy()->subDays($diffDays);
        $previousEnd = $currentStart->copy()->subDay();

        return [
            'start' => $previousStart->startOfDay(),
            'end' => $previousEnd->endOfDay(),
        ];
    }

    private function generateAiInsights(array $stats): array
    {
        $insights = [];
        $recommendations = [];

        // Growth insights
        if ($stats['amount_growth_rate'] > 10) {
            $insights[] = [
                'icon' => 'fa-arrow-trend-up',
                'color' => 'text-green-600',
                'bg' => 'bg-green-50',
                'title' => 'Excellent Growth!',
                'message' => "Revenue has increased by {$stats['amount_growth_rate']}% compared to the previous period.",
            ];
        } elseif ($stats['amount_growth_rate'] > 0) {
            $insights[] = [
                'icon' => 'fa-arrow-up',
                'color' => 'text-green-500',
                'bg' => 'bg-green-50',
                'title' => 'Positive Growth',
                'message' => "Revenue is up by {$stats['amount_growth_rate']}% from last period.",
            ];
        } elseif ($stats['amount_growth_rate'] < -10) {
            $insights[] = [
                'icon' => 'fa-arrow-trend-down',
                'color' => 'text-red-600',
                'bg' => 'bg-red-50',
                'title' => 'Revenue Decline',
                'message' => "Revenue has dropped by " . abs($stats['amount_growth_rate']) . "% compared to last period.",
            ];
            $recommendations[] = "Focus on re-engaging top members and promoting high-value payment purposes.";
        } elseif ($stats['amount_growth_rate'] < 0) {
            $insights[] = [
                'icon' => 'fa-arrow-down',
                'color' => 'text-amber-600',
                'bg' => 'bg-amber-50',
                'title' => 'Slight Dip',
                'message' => "Revenue is down by " . abs($stats['amount_growth_rate']) . "%. Monitor trends closely.",
            ];
        } else {
            $insights[] = [
                'icon' => 'fa-minus',
                'color' => 'text-blue-600',
                'bg' => 'bg-blue-50',
                'title' => 'Stable Performance',
                'message' => "Revenue is consistent with the previous period.",
            ];
        }

        // Success rate insights
        if ($stats['success_rate'] < 80) {
            $insights[] = [
                'icon' => 'fa-exclamation-triangle',
                'color' => 'text-amber-600',
                'bg' => 'bg-amber-50',
                'title' => 'Success Rate Needs Attention',
                'message' => "Current success rate is {$stats['success_rate']}%. Consider investigating failed transactions.",
            ];
            $recommendations[] = "Check failed transactions for patterns (e.g., specific payment methods, times of day).";
        }

        // Top purpose insight
        if (!empty($stats['top_purposes'])) {
            $topPurpose = $stats['top_purposes'][0];
            $insights[] = [
                'icon' => 'fa-star',
                'color' => 'text-yellow-600',
                'bg' => 'bg-yellow-50',
                'title' => 'Top Payment Purpose',
                'message' => "\"{$topPurpose['description']}\" is the most popular, generating TZS " . number_format($topPurpose['total_amount'], 0) . ".",
            ];
            $recommendations[] = "Consider promoting \"{$topPurpose['description']}\" to drive more revenue.";
        }

        // Average transaction value insight
        if ($stats['average_transaction_value'] > 0) {
            $insights[] = [
                'icon' => 'fa-coins',
                'color' => 'text-primary-600',
                'bg' => 'bg-primary-50',
                'title' => 'Average Transaction Value',
                'message' => "Each successful transaction averages TZS " . number_format($stats['average_transaction_value'], 0) . ".",
            ];
        }

        // Default recommendations if none
        if (empty($recommendations)) {
            $recommendations[] = "Continue current strategies - performance is good!";
            $recommendations[] = "Look for opportunities to upsell or cross-sell to top members.";
        }

        return [
            'insights' => $insights,
            'recommendations' => $recommendations,
        ];
    }

    public function accountBalance()
    {
        $balance = $this->accountBalanceService->getTzsBalance(refresh: true);

        return response()->json([
            'success' => true,
            'data' => $balance,
        ]);
    }

    public function syncTransactions()
    {
        \Artisan::call('payments:sync', ['--days' => 1]);
        
        return response()->json([
            'success' => true,
            'message' => 'Transactions synced',
        ]);
    }

    public function syncBills()
    {
        \Artisan::call('app:sync-bills-from-api');
        
        return response()->json([
            'success' => true,
            'message' => 'Bills synced',
        ]);
    }

    public function clearCache()
    {
        \Artisan::call('route:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        \Artisan::call('optimize:clear');
        
        return response()->json([
            'success' => true,
            'message' => 'All caches cleared successfully!',
        ]);
    }

    public function exportPdf(Request $request)
    {
        // Reuse index method logic to get stats
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
        $averageTransactionValue = $periodSuccessfulCount > 0
            ? round($periodSettledAmount / $periodSuccessfulCount, 2)
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
            ->selectRaw('payment_method as name, count(*) as count, sum(amount) as total_amount')
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        $dateRange = $this->getDateRange($dateFilter, $startDate, $endDate);
        $dailyStats = $this->generateDailyStats($dateRange['start'], $dateRange['end'], $settledStatuses);
        $monthlyStats = $this->generateMonthlyStats($dateRange['start'], $dateRange['end'], $settledStatuses);
        $statusStats = (clone $baseQuery)
            ->selectRaw('status, count(*) as count, sum(amount) as amount')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        $topPurposes = (clone $settledQuery)->whereNotNull('description')
            ->selectRaw('description, count(*) as count, sum(amount) as total_amount')
            ->groupBy('description')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get()
            ->toArray();

        $previousDateRange = $this->getPreviousPeriodDateRange($dateRange, $dateFilter);
        $previousBaseQuery = Transaction::query();
        $previousBaseQuery->whereBetween('created_at', [$previousDateRange['start'], $previousDateRange['end']]);
        $previousSettledQuery = (clone $previousBaseQuery)->whereIn('status', $settledStatuses);
        $previousPeriodSettledAmount = (clone $previousSettledQuery)->sum('amount');
        $previousPeriodSuccessfulCount = (clone $previousSettledQuery)->count();

        $amountGrowthRate = $previousPeriodSettledAmount > 0
            ? round((($periodSettledAmount - $previousPeriodSettledAmount) / $previousPeriodSettledAmount) * 100, 2)
            : 0;
        $countGrowthRate = $previousPeriodSuccessfulCount > 0
            ? round((($periodSuccessfulCount - $previousPeriodSuccessfulCount) / $previousPeriodSuccessfulCount) * 100, 2)
            : 0;

        $aiInsights = $this->generateAiInsights([
            'period_settled_amount' => $periodSettledAmount,
            'period_successful_count' => $periodSuccessfulCount,
            'success_rate' => $successRate,
            'average_transaction_value' => $averageTransactionValue,
            'amount_growth_rate' => $amountGrowthRate,
            'count_growth_rate' => $countGrowthRate,
            'top_purposes' => $topPurposes,
            'top_customers' => $topCustomers,
            'payment_methods' => $paymentMethods,
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.export-pdf', [
            'dateFilter' => $dateFilter,
            'periodLabel' => $periodLabel,
            'accountBalance' => $accountBalance,
            'stats' => [
                'period_settled_amount' => $periodSettledAmount,
                'period_successful_count' => $periodSuccessfulCount,
                'success_rate' => $successRate,
                'average_transaction_value' => $averageTransactionValue,
                'top_customers' => $topCustomers,
                'payment_methods' => $paymentMethods,
                'daily_stats' => $dailyStats,
                'monthly_stats' => $monthlyStats,
                'status_stats' => $statusStats,
                'top_purposes' => $topPurposes,
                'amount_growth_rate' => $amountGrowthRate,
                'count_growth_rate' => $countGrowthRate,
            ],
            'aiInsights' => $aiInsights,
            'exportDate' => now()->format('Y-m-d H:i:s'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("dashboard-report-{$dateFilter}-" . now()->format('Y-m-d') . ".pdf");
    }
}
