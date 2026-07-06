<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function customerReport(Request $request)
    {
        $customerName = $request->get('customer_name');
        $phone = $request->get('phone');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = $request->get('status', 'all');
        
        // Get distinct customers for filter dropdown
        $customers = Transaction::select('customer_name')
            ->whereNotNull('customer_name')
            ->distinct()
            ->orderBy('customer_name')
            ->pluck('customer_name');
        
        $paymentQuery = Transaction::whereIn('type', ['payment', 'billpay', 'ecommerce_payment']);
        
        if ($customerName) {
            $paymentQuery->where('customer_name', 'like', '%' . $customerName . '%');
        }
        
        if ($phone) {
            $paymentQuery->where('phone', 'like', '%' . $phone . '%');
        }
        
        if ($startDate) {
            $paymentQuery->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $paymentQuery->whereDate('created_at', '<=', $endDate);
        }
        
        if ($status !== 'all') {
            if ($status === 'settled') {
                $paymentQuery->whereIn('status', ['SETTLED', 'SUCCESS']);
            } else if ($status === 'failed') {
                $paymentQuery->whereIn('status', ['FAILED', 'ERROR']);
            }
        }
        
        $payments = $paymentQuery->orderBy('created_at', 'desc')->paginate(20);
        
        // Calculate totals
        $totalAmount = $payments->whereIn('status', ['SETTLED', 'SUCCESS'])->sum('amount');
        
        return view('reports.customer-report', compact(
            'payments',
            'customers',
            'customerName',
            'phone',
            'startDate',
            'endDate',
            'status',
            'totalAmount'
        ));
    }
    
    public function exportCustomerReportPdf(Request $request)
    {
        $customerName = $request->get('customer_name');
        $phone = $request->get('phone');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = $request->get('status', 'all');
        
        $paymentQuery = Transaction::whereIn('type', ['payment', 'billpay', 'ecommerce_payment']);
        
        if ($customerName) {
            $paymentQuery->where('customer_name', 'like', '%' . $customerName . '%');
        }
        
        if ($phone) {
            $paymentQuery->where('phone', 'like', '%' . $phone . '%');
        }
        
        if ($startDate) {
            $paymentQuery->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $paymentQuery->whereDate('created_at', '<=', $endDate);
        }
        
        if ($status !== 'all') {
            if ($status === 'settled') {
                $paymentQuery->whereIn('status', ['SETTLED', 'SUCCESS']);
            } else if ($status === 'failed') {
                $paymentQuery->whereIn('status', ['FAILED', 'ERROR']);
            }
        }
        
        $payments = $paymentQuery->orderBy('created_at', 'asc')->get();
        
        $totalAmount = $payments->whereIn('status', ['SETTLED', 'SUCCESS'])->sum('amount');
        
        $pdf = Pdf::loadView('reports.exports.customer-report-pdf', [
            'payments' => $payments,
            'customerName' => $customerName,
            'phone' => $phone,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalAmount' => $totalAmount,
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('customer-report-' . date('Y-m-d') . '.pdf');
    }
    
    public function exportCustomerReportExcel(Request $request)
    {
        $customerName = $request->get('customer_name');
        $phone = $request->get('phone');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = $request->get('status', 'all');
        
        $paymentQuery = Transaction::whereIn('type', ['payment', 'billpay', 'ecommerce_payment']);
        
        if ($customerName) {
            $paymentQuery->where('customer_name', 'like', '%' . $customerName . '%');
        }
        
        if ($phone) {
            $paymentQuery->where('phone', 'like', '%' . $phone . '%');
        }
        
        if ($startDate) {
            $paymentQuery->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $paymentQuery->whereDate('created_at', '<=', $endDate);
        }
        
        if ($status !== 'all') {
            if ($status === 'settled') {
                $paymentQuery->whereIn('status', ['SETTLED', 'SUCCESS']);
            } else if ($status === 'failed') {
                $paymentQuery->whereIn('status', ['FAILED', 'ERROR']);
            }
        }
        
        $payments = $paymentQuery->orderBy('created_at', 'asc')->get();
        
        // Create a simple export class inline
        $export = new class($payments) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            protected $payments;
            
            public function __construct($payments) {
                $this->payments = $payments;
            }
            
            public function collection() {
                return $this->payments->map(function($payment) {
                    return [
                        'Date' => $payment->created_at->format('Y-m-d H:i:s'),
                        'Reference' => $payment->order_reference,
                        'Customer Name' => $payment->customer_name ?? $payment->payer_name,
                        'Payer Name' => $payment->payer_name,
                        'Phone' => $payment->phone,
                        'Description' => $payment->description,
                        'Amount' => $payment->amount,
                        'Currency' => $payment->currency ?? 'TZS',
                        'Status' => $payment->status,
                        'Payment Method' => $payment->payment_method,
                    ];
                });
            }
            
            public function headings(): array {
                return [
                    'Date',
                    'Reference',
                    'Customer Name',
                    'Payer Name',
                    'Phone',
                    'Description',
                    'Amount',
                    'Currency',
                    'Status',
                    'Payment Method',
                ];
            }
        };
        
        return Excel::download($export, 'customer-report-' . date('Y-m-d') . '.xlsx');
    }
    public function trialBalance(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $paymentsQuery = Transaction::whereIn('type', ['payment', 'billpay'])->whereIn('status', ['SETTLED', 'SUCCESS']);
        
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
        
        $payments = Transaction::whereIn('type', ['payment', 'billpay'])
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
        
        $paymentsQuery = Transaction::whereIn('type', ['payment', 'billpay'])->whereIn('status', ['SETTLED', 'SUCCESS']);
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
        
        $paymentsQuery = Transaction::whereIn('type', ['payment', 'billpay'])->whereIn('status', ['SETTLED', 'SUCCESS']);
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
        
        $payments = Transaction::whereIn('type', ['payment', 'billpay'])
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
        
        $paymentsQuery = Transaction::whereIn('type', ['payment', 'billpay'])->whereIn('status', ['SETTLED', 'SUCCESS']);
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
