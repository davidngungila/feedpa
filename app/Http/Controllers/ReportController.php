<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function trialBalance(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = Transaction::where('type', 'payment');
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        $transactions = $query->get();
        
        $trialBalance = [
            'accounts' => [
                [
                    'name' => 'Cash',
                    'debit' => $transactions->sum('amount'),
                    'credit' => 0,
                    'balance' => $transactions->sum('amount'),
                ],
                [
                    'name' => 'Revenue',
                    'debit' => 0,
                    'credit' => $transactions->sum('amount'),
                    'balance' => -$transactions->sum('amount'),
                ],
            ],
            'totals' => [
                'debit' => $transactions->sum('amount'),
                'credit' => $transactions->sum('amount'),
            ]
        ];
        
        return view('reports.trial-balance', compact('trialBalance', 'startDate', 'endDate'));
    }
    
    public function balanceSheet(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        
        $transactions = Transaction::where('type', 'payment')
            ->whereDate('created_at', '<=', $asOfDate)
            ->get();
        
        $totalAssets = $transactions->sum('amount');
        $totalEquity = $totalAssets;
        
        $balanceSheet = [
            'as_of_date' => $asOfDate,
            'assets' => [
                [
                    'name' => 'Cash',
                    'value' => $totalAssets,
                ],
            ],
            'liabilities' => [],
            'equity' => [
                [
                    'name' => 'Retained Earnings',
                    'value' => $totalEquity,
                ],
            ],
            'totals' => [
                'assets' => $totalAssets,
                'liabilities' => 0,
                'equity' => $totalEquity,
                'total_liabilities_equity' => $totalEquity,
            ],
        ];
        
        return view('reports.balance-sheet', compact('balanceSheet'));
    }
    
    public function profitLoss(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth()->format('Y-m-01'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        $query = Transaction::where('type', 'payment');
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        $revenue = $query->sum('amount');
        
        $profitLoss = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenue' => [
                [
                    'name' => 'Payment Revenue',
                    'amount' => $revenue,
                ],
            ],
            'expenses' => [],
            'net_profit' => $revenue,
            'totals' => [
                'total_revenue' => $revenue,
                'total_expenses' => 0,
                'net_profit' => $revenue,
            ],
        ];
        
        return view('reports.profit-loss', compact('profitLoss'));
    }
    
    public function exportTrialBalance(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = Transaction::where('type', 'payment');
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        $transactions = $query->get();
        
        $trialBalance = [
            'accounts' => [
                [
                    'name' => 'Cash',
                    'debit' => $transactions->sum('amount'),
                    'credit' => 0,
                    'balance' => $transactions->sum('amount'),
                ],
                [
                    'name' => 'Revenue',
                    'debit' => 0,
                    'credit' => $transactions->sum('amount'),
                    'balance' => -$transactions->sum('amount'),
                ],
            ],
            'totals' => [
                'debit' => $transactions->sum('amount'),
                'credit' => $transactions->sum('amount'),
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
        
        $transactions = Transaction::where('type', 'payment')
            ->whereDate('created_at', '<=', $asOfDate)
            ->get();
        
        $totalAssets = $transactions->sum('amount');
        $totalEquity = $totalAssets;
        
        $balanceSheet = [
            'as_of_date' => $asOfDate,
            'assets' => [
                [
                    'name' => 'Cash',
                    'value' => $totalAssets,
                ],
            ],
            'liabilities' => [],
            'equity' => [
                [
                    'name' => 'Retained Earnings',
                    'value' => $totalEquity,
                ],
            ],
            'totals' => [
                'assets' => $totalAssets,
                'liabilities' => 0,
                'equity' => $totalEquity,
                'total_liabilities_equity' => $totalEquity,
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
        
        $query = Transaction::where('type', 'payment');
        
        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        
        $revenue = $query->sum('amount');
        
        $profitLoss = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'revenue' => [
                [
                    'name' => 'Payment Revenue',
                    'amount' => $revenue,
                ],
            ],
            'expenses' => [],
            'net_profit' => $revenue,
            'totals' => [
                'total_revenue' => $revenue,
                'total_expenses' => 0,
                'net_profit' => $revenue,
            ],
        ];
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.exports.profit-loss-pdf', [
            'profitLoss' => $profitLoss,
        ])->setPaper('a4', 'portrait');
        
        return $pdf->download('profit-and-loss-' . date('Y-m-d') . '.pdf');
    }
}
