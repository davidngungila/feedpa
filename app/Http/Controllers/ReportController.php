<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function trialBalance(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $paymentsQuery = Transaction::where('type', 'payment')->whereIn('status', ['SETTLED', 'SUCCESS']);
        
        if ($startDate) $paymentsQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate) $paymentsQuery->whereDate('created_at', '<=', $endDate);
        
        $payments = $paymentsQuery->get();
        
        $payoutsQuery = Payout::whereIn('status', ['SUCCESS', 'SETTLED']);
        if ($startDate) $payoutsQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate) $payoutsQuery->whereDate('created_at', '<=', $endDate);
        $payouts = $payoutsQuery->get();
        
        $totalRevenue = $payments->sum('amount');
        $totalExpenses = $payouts->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        
        $trialBalance = [
            'accounts' => [
                [
                    'name' => 'Cash',
                    'debit' => $totalRevenue,
                    'credit' => $totalExpenses,
                    'balance' => $netProfit,
                ],
                [
                    'name' => 'Revenue',
                    'debit' => 0,
                    'credit' => $totalRevenue,
                    'balance' => -$totalRevenue,
                ],
                [
                    'name' => 'Payout Expenses',
                    'debit' => $totalExpenses,
                    'credit' => 0,
                    'balance' => $totalExpenses,
                ],
            ],
            'totals' => [
                'debit' => $totalRevenue + $totalExpenses,
                'credit' => $totalRevenue + $totalExpenses,
            ]
        ];
        
        return view('reports.trial-balance', compact('trialBalance', 'startDate', 'endDate'));
    }
    
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        
        $payments = Transaction::where('type', 'payment')
            ->whereIn('status', ['SETTLED', 'SUCCESS'])
            ->whereDate('created_at', '<=', $asOfDate)
            ->get();
        
        $payouts = Payout::whereIn('status', ['SUCCESS', 'SETTLED'])
            ->whereDate('created_at', '<=', $asOfDate)
            ->get();
            
        $totalRevenue = $payments->sum('amount');
        $totalExpenses = $payouts->sum('amount');
        $netAssets = $totalRevenue - $totalExpenses;
        
        $balanceSheet = [
            'as_of_date' => $asOfDate,
            'assets' => [
                [
                    'name' => 'Cash',
                    'value' => $netAssets,
                ],
            ],
            'liabilities' => [],
            'equity' => [
                [
                    'name' => 'Retained Earnings',
                    'value' => $netAssets,
                ],
            ],
            'totals' => [
                'assets' => $netAssets,
                'liabilities' => 0,
                'equity' => $netAssets,
                'total_liabilities_equity' => $netAssets,
            ],
        ];
        
        return view('reports.balance-sheet', compact('balanceSheet'));
    }
    
    public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-01'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $paymentsQuery = Transaction::where('type', 'payment')->whereIn('status', ['SETTLED', 'SUCCESS']);
        $payoutsQuery = Payout::whereIn('status', ['SUCCESS', 'SETTLED']);
        
        if ($startDate) {
            $paymentsQuery->whereDate('created_at', '>=', $startDate);
            $payoutsQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $paymentsQuery->whereDate('created_at', '<=', $endDate);
            $payoutsQuery->whereDate('created_at', '<=', $endDate);
        }
        
        $revenue = $paymentsQuery->sum('amount');
        $expenses = $payoutsQuery->sum('amount');
        $netProfit = $revenue - $expenses;
        
        $profitLoss = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenue' => [
                [
                    'name' => 'Payment Revenue',
                    'amount' => $revenue,
                ],
            ],
            'expenses' => [
                [
                    'name' => 'Payouts',
                    'amount' => $expenses,
                ],
            ],
            'net_profit' => $netProfit,
            'totals' => [
                'total_revenue' => $revenue,
                'total_expenses' => $expenses,
                'net_profit' => $netProfit,
            ],
        ];
        
        return view('reports.profit-loss', compact('profitLoss'));
    }
    
    public function exportTrialBalance(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $paymentsQuery = Transaction::where('type', 'payment')->whereIn('status', ['SETTLED', 'SUCCESS']);
        $payoutsQuery = Payout::whereIn('status', ['SUCCESS', 'SETTLED']);
        
        if ($startDate) {
            $paymentsQuery->whereDate('created_at', '>=', $startDate);
            $payoutsQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $paymentsQuery->whereDate('created_at', '<=', $endDate);
            $payoutsQuery->whereDate('created_at', '<=', $endDate);
        }
        
        $payments = $paymentsQuery->get();
        $payouts = $payoutsQuery->get();
        
        $totalRevenue = $payments->sum('amount');
        $totalExpenses = $payouts->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        
        $trialBalance = [
            'accounts' => [
                [
                    'name' => 'Cash',
                    'debit' => $totalRevenue,
                    'credit' => $totalExpenses,
                    'balance' => $netProfit,
                ],
                [
                    'name' => 'Revenue',
                    'debit' => 0,
                    'credit' => $totalRevenue,
                    'balance' => -$totalRevenue,
                ],
                [
                    'name' => 'Payout Expenses',
                    'debit' => $totalExpenses,
                    'credit' => 0,
                    'balance' => $totalExpenses,
                ],
            ],
            'totals' => [
                'debit' => $totalRevenue + $totalExpenses,
                'credit' => $totalRevenue + $totalExpenses,
            ]
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.trial-balance-pdf', [
            'trialBalance' => $trialBalance,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('trial-balance-' . date('Y-m-d') . '.pdf');
    }
    
    public function exportBalanceSheet(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        
        $payments = Transaction::where('type', 'payment')
            ->whereIn('status', ['SETTLED', 'SUCCESS'])
            ->whereDate('created_at', '<=', $asOfDate)
            ->get();
        
        $payouts = Payout::whereIn('status', ['SUCCESS', 'SETTLED'])
            ->whereDate('created_at', '<=', $asOfDate)
            ->get();
            
        $totalRevenue = $payments->sum('amount');
        $totalExpenses = $payouts->sum('amount');
        $netAssets = $totalRevenue - $totalExpenses;
        
        $balanceSheet = [
            'as_of_date' => $asOfDate,
            'assets' => [
                [
                    'name' => 'Cash',
                    'value' => $netAssets,
                ],
            ],
            'liabilities' => [],
            'equity' => [
                [
                    'name' => 'Retained Earnings',
                    'value' => $netAssets,
                ],
            ],
            'totals' => [
                'assets' => $netAssets,
                'liabilities' => 0,
                'equity' => $netAssets,
                'total_liabilities_equity' => $netAssets,
            ],
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.balance-sheet-pdf', [
            'balanceSheet' => $balanceSheet,
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download('balance-sheet-' . date('Y-m-d') . '.pdf');
    }
    
    public function exportProfitLoss(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-01'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $paymentsQuery = Transaction::where('type', 'payment')->whereIn('status', ['SETTLED', 'SUCCESS']);
        $payoutsQuery = Payout::whereIn('status', ['SUCCESS', 'SETTLED']);
        
        if ($startDate) {
            $paymentsQuery->whereDate('created_at', '>=', $startDate);
            $payoutsQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $paymentsQuery->whereDate('created_at', '<=', $endDate);
            $payoutsQuery->whereDate('created_at', '<=', $endDate);
        }
        
        $revenue = $paymentsQuery->sum('amount');
        $expenses = $payoutsQuery->sum('amount');
        $netProfit = $revenue - $expenses;
        
        $profitLoss = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenue' => [
                [
                    'name' => 'Payment Revenue',
                    'amount' => $revenue,
                ],
            ],
            'expenses' => [
                [
                    'name' => 'Payouts',
                    'amount' => $expenses,
                ],
            ],
            'net_profit' => $netProfit,
            'totals' => [
                'total_revenue' => $revenue,
                'total_expenses' => $expenses,
                'net_profit' => $netProfit,
            ],
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.profit-loss-pdf', [
            'profitLoss' => $profitLoss,
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download('profit-and-loss-' . date('Y-m-d') . '.pdf');
    }
}
