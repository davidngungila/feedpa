<?php

namespace App\Http\Controllers;

use App\Services\ClickPesaAPIService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected ClickPesaAPIService $api;

    public function __construct(ClickPesaAPIService $api)
    {
        $this->api = $api;
    }

    /**
     * Advanced Dashboard
     */
    public function index(Request $request)
    {
        $recentPayments = [];
        $successfulPayments = [];
        $failedPayments = [];
        $stats = [
            'total_transactions' => 0,
            'successful' => 0,
            'pending' => 0,
            'failed' => 0,
            'total_amount' => 0,
            'today_revenue' => 0,
            'average_transaction' => 0,
            'success_rate' => 0,
            'failure_rate' => 0,
                        'daily_stats' => [],
            'weekly_stats' => [],
            'monthly_stats' => [],
            'yearly_stats' => [],
            'top_customers' => [],
            'payment_methods' => [],
            'currency_breakdown' => []
        ];
        $error = null;
        
        // Get date filter from request
        $dateFilter = $request->get('date_filter', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        try {
            // Build API parameters with date filtering
            $params = ['limit' => 50, 'orderBy' => 'DESC'];
            
            // Apply date filters
            if ($dateFilter !== 'all') {
                switch ($dateFilter) {
                    case 'today':
                        $params['startDate'] = now()->format('Y-m-d');
                        $params['endDate'] = now()->format('Y-m-d');
                        break;
                    case 'week':
                        $params['startDate'] = now()->startOfWeek()->format('Y-m-d');
                        $params['endDate'] = now()->endOfWeek()->format('Y-m-d');
                        break;
                    case 'month':
                        $params['startDate'] = now()->startOfMonth()->format('Y-m-d');
                        $params['endDate'] = now()->endOfMonth()->format('Y-m-d');
                        break;
                    case 'year':
                        $params['startDate'] = now()->startOfYear()->format('Y-m-d');
                        $params['endDate'] = now()->endOfYear()->format('Y-m-d');
                        break;
                    case 'custom':
                        if ($startDate) $params['startDate'] = $startDate;
                        if ($endDate) $params['endDate'] = $endDate;
                        break;
                }
            }
            
            // Get payments data for statistics (fetch all for accurate stats)
            $statsParams = $params;
            unset($statsParams['limit']); // Remove limit for statistics
            $statsResponse = $this->api->queryAllPayments($statsParams);
            
            // Get recent payments for display (limited to 50)
            $recentParams = $params;
            $recentParams['limit'] = 50;
            $response = $this->api->queryAllPayments($recentParams);
            
            if (isset($statsResponse['data'])) {
                $allPayments = $statsResponse['data'];
                $stats['total_transactions'] = $statsResponse['totalCount'] ?? 0;
                $stats['successful'] = collect($allPayments)->filter(fn($p) => 
                    in_array($p['status'] ?? '', ['SUCCESS', 'SETTLED']))->count();
                $stats['pending'] = collect($allPayments)->filter(fn($p) => 
                    in_array($p['status'] ?? '', ['PROCESSING', 'PENDING']))->count();
                $stats['failed'] = collect($allPayments)->filter(fn($p) => 
                    in_array($p['status'] ?? '', ['FAILED', 'ERROR']))->count();
                // Calculate revenue only from settled transactions
                $settledPayments = collect($allPayments)->filter(fn($p) => 
                    in_array($p['status'] ?? '', ['SETTLED']));
                
                $stats['total_amount'] = $settledPayments->sum(fn($p) => $p['amount'] ?? $p['collectedAmount'] ?? 0);
                $stats['settled_count'] = $settledPayments->count();
                
                // Use limited response for recent payments display
                if (isset($response['data'])) {
                    $allRecentPayments = array_slice($response['data'], 0, 50); // Get more for filtering
                    
                    // Separate successful and failed transactions
                    $successfulTransactions = array_filter($allRecentPayments, function($payment) {
                        return in_array($payment['status'] ?? '', ['SUCCESS', 'SETTLED']);
                    });
                    
                    $failedTransactions = array_filter($allRecentPayments, function($payment) {
                        return in_array($payment['status'] ?? '', ['FAILED', 'ERROR']);
                    });
                    
                    // Enhance customer data extraction for better display
                    $enhancePaymentData = function($payment) {
                        // Extract customer name from various possible fields
                        $payment['customer_name'] = $payment['customer']['customerName'] ?? 
                                                   $payment['payer_name'] ?? 
                                                   $payment['customerName'] ?? 
                                                   'Customer';
                        
                        // Extract customer phone number
                        $payment['customer_phone'] = $payment['customer']['customerPhoneNumber'] ?? 
                                                    $payment['paymentPhoneNumber'] ?? 
                                                    $payment['phone'] ?? 
                                                    'N/A';
                        
                        return $payment;
                    };
                    
                    $recentSettledTransactions = array_values(array_filter($allRecentPayments, function ($payment) {
                        return ($payment['status'] ?? '') === 'SETTLED';
                    }));
                    $recentPayments = array_map($enhancePaymentData, array_slice($recentSettledTransactions, 0, 10));
                    $successfulPayments = array_map($enhancePaymentData, array_slice($successfulTransactions, 0, 10));
                    $failedPayments = array_map($enhancePaymentData, array_slice($failedTransactions, 0, 10));
                } else {
                    $recentPayments = [];
                    $successfulPayments = [];
                    $failedPayments = [];
                }
                
                // Calculate today's revenue and statistics (settled only)
                $todayPayments = collect($allPayments)->filter(function($payment) {
                    $paymentDate = isset($payment['createdAt']) ? 
                        \Carbon\Carbon::parse($payment['createdAt'])->format('Y-m-d') : null;
                    return $paymentDate === now()->format('Y-m-d') && 
                           in_array($payment['status'] ?? '', ['SETTLED']);
                });
                
                $stats['today_revenue'] = $todayPayments->sum(fn($p) => $p['amount'] ?? $p['collectedAmount'] ?? 0);
                $stats['average_transaction'] = $stats['settled_count'] > 0 ? 
                    $stats['total_amount'] / $stats['settled_count'] : 0;
                
                // Calculate success and failure rates
                $stats['success_rate'] = $stats['total_transactions'] > 0 ? 
                    round(($stats['successful'] / $stats['total_transactions']) * 100, 1) : 0;
                $stats['failure_rate'] = $stats['total_transactions'] > 0 ? 
                    round(($stats['failed'] / $stats['total_transactions']) * 100, 1) : 0;

                // Calculate daily stats for last 7 days
                $stats['daily_stats'] = $this->calculateDailyStats($allPayments);
                
                // Calculate monthly stats for last 6 months
                $stats['monthly_stats'] = $this->calculateMonthlyStats($allPayments);
                
                // Calculate weekly stats for last 4 weeks
                $stats['weekly_stats'] = $this->calculateWeeklyStats($allPayments);
                
                // Calculate yearly stats for current year
                $stats['yearly_stats'] = $this->calculateYearlyStats($allPayments);
                
                // Payment methods breakdown
                $stats['payment_methods'] = $this->calculatePaymentMethods($allPayments);
                
                // Currency breakdown
                $stats['currency_breakdown'] = $this->calculateCurrencyBreakdown($allPayments);
                
                // Calculate top customers by transaction count and amount
                $stats['top_customers'] = $this->calculateTopCustomers($allPayments);
            }

            
        } catch (Exception $e) {
            $error = 'Failed to load dashboard data: ' . $e->getMessage();
            Log::error('Dashboard data loading failed: ' . $error);
        }

        return view('dashboard.index', compact('stats', 'recentPayments', 'successfulPayments', 'failedPayments', 'error'));
    }
    public function liveStatus()
    {
        $activePayments = [];
        $recentPayments = [];
        $error = null;

        try {
            $response = $this->api->queryAllPayments(['limit' => 20, 'orderBy' => 'DESC']);
            
            if (isset($response['data'])) {
                $allPayments = $response['data'];
                $activePayments = collect($allPayments)->filter(fn($p) => 
                    in_array($p['status'], ['PROCESSING', 'PENDING']))->toArray();
                $recentPayments = array_slice($allPayments, 0, 10);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            Log::error('Live status error: ' . $error);
        }

        return view('dashboard.live-status', compact('activePayments', 'recentPayments', 'error'));
    }

    /**
     * Calculate daily statistics for the last 7 days
     */
    private function calculateDailyStats($payments)
    {
        $dailyStats = [];
        $today = now();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            
            $dayPayments = collect($payments)->filter(function($payment) use ($dateStr) {
                $paymentDate = isset($payment['createdAt']) ? 
                    \Carbon\Carbon::parse($payment['createdAt'])->format('Y-m-d') : 
                    null;
                return $paymentDate === $dateStr;
            });
            
            $dailyStats[] = [
                'date' => $date->format('M d'),
                'count' => $dayPayments->count(),
                'amount' => $dayPayments->sum(function($payment) {
                    return $payment['collectedAmount'] ?? $payment['amount'] ?? 0;
                }),
                'success' => $dayPayments->filter(fn($p) => $p['status'] === 'SUCCESS')->count(),
                'failed' => $dayPayments->filter(fn($p) => in_array($p['status'] ?? '', ['FAILED', 'ERROR']))->count()
            ];
        }
        
        return $dailyStats;
    }
    
    /**
     * Calculate weekly statistics for last 4 weeks
     */
    private function calculateWeeklyStats($payments)
    {
        $weeklyStats = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = now()->copy()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->copy()->subWeeks($i)->endOfWeek();
            
            $weekPayments = collect($payments)->filter(function($payment) use ($weekStart, $weekEnd) {
                $paymentDate = isset($payment['createdAt']) ? 
                    \Carbon\Carbon::parse($payment['createdAt']) : null;
                return $paymentDate && $paymentDate->between($weekStart, $weekEnd);
            });
            
            $weeklyStats[] = [
                'week' => 'Week ' . (4 - $i),
                'start_date' => $weekStart->format('M d'),
                'end_date' => $weekEnd->format('M d'),
                'count' => $weekPayments->count(),
                'amount' => $weekPayments->sum(function($payment) {
                    return $payment['collectedAmount'] ?? $payment['amount'] ?? 0;
                }),
                'success' => $weekPayments->filter(fn($p) => $p['status'] === 'SUCCESS')->count(),
                'failed' => $weekPayments->filter(fn($p) => in_array($p['status'] ?? '', ['FAILED', 'ERROR']))->count()
            ];
        }
        
        return array_reverse($weeklyStats);
    }
    
    /**
     * Calculate monthly statistics for last 6 months
     */
    private function calculateMonthlyStats($payments)
    {
        $monthlyStats = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->copy()->subMonths($i);
            $monthName = $month->format('F');
            $monthYear = $month->format('Y');
            
            $monthPayments = collect($payments)->filter(function($payment) use ($month) {
                $paymentDate = isset($payment['createdAt']) ? 
                    \Carbon\Carbon::parse($payment['createdAt']) : null;
                return $paymentDate && $paymentDate->month == $month->month && $paymentDate->year == $month->year;
            });
            
            $monthlyStats[] = [
                'month' => $monthName,
                'year' => $monthYear,
                'count' => $monthPayments->count(),
                'amount' => $monthPayments->sum(function($payment) {
                    return $payment['collectedAmount'] ?? $payment['amount'] ?? 0;
                }),
                'success' => $monthPayments->filter(fn($p) => $p['status'] === 'SUCCESS')->count(),
                'failed' => $monthPayments->filter(fn($p) => in_array($p['status'] ?? '', ['FAILED', 'ERROR']))->count()
            ];
        }
        
        return array_reverse($monthlyStats);
    }
    
    /**
     * Calculate yearly statistics for current year
     */
    private function calculateYearlyStats($payments)
    {
        $currentYear = now()->year;
        
        $yearPayments = collect($payments)->filter(function($payment) use ($currentYear) {
            $paymentDate = isset($payment['createdAt']) ? 
                \Carbon\Carbon::parse($payment['createdAt']) : null;
            return $paymentDate && $paymentDate->year == $currentYear;
        });
        
        return [
            'year' => $currentYear,
            'count' => $yearPayments->count(),
            'amount' => $yearPayments->sum(function($payment) {
                return $payment['collectedAmount'] ?? $payment['amount'] ?? 0;
            }),
            'success' => $yearPayments->filter(fn($p) => $p['status'] === 'SUCCESS')->count(),
            'failed' => $yearPayments->filter(fn($p) => in_array($p['status'] ?? '', ['FAILED', 'ERROR']))->count()
        ];
    }

    /**
                $paymentMonth = isset($payment['createdAt']) ? 
                    \Carbon\Carbon::parse($payment['createdAt'])->format('Y-m') : 
                    null;
                return $paymentMonth === $monthStr;
            });
            
            $monthlyStats[] = [
                'month' => $month->format('M Y'),
                'count' => $monthPayments->count(),
                'amount' => $monthPayments->sum(function($payment) {
                    return $payment['collectedAmount'] ?? $payment['amount'] ?? 0;
                }),
                'success' => $monthPayments->filter(fn($p) => $p['status'] === 'SUCCESS')->count(),
                'failed' => $monthPayments->filter(fn($p) => in_array($p['status'] ?? '', ['FAILED', 'ERROR']))->count()
            ];
        }
        
        return $monthlyStats;
    }

    /**
     * Calculate payment methods breakdown
     */
    private function calculatePaymentMethods($payments)
    {
        $methods = [];
        
        foreach ($payments as $payment) {
            $method = $payment['channel'] ?? $payment['paymentMethod'] ?? 'Unknown';
            $amount = $payment['collectedAmount'] ?? $payment['amount'] ?? 0;
            
            if (!isset($methods[$method])) {
                $methods[$method] = [
                    'name' => $method,
                    'count' => 0,
                    'amount' => 0,
                    'success' => 0,
                    'failed' => 0
                ];
            }
            
            $methods[$method]['count']++;
            $methods[$method]['amount'] += $amount;
            
            if ($payment['status'] === 'SUCCESS') {
                $methods[$method]['success']++;
            } elseif (in_array($payment['status'] ?? '', ['FAILED', 'ERROR'])) {
                $methods[$method]['failed']++;
            }
        }
        
        return array_values($methods);
    }

    /**
     * Calculate currency breakdown
     */
    private function calculateCurrencyBreakdown($payments)
    {
        $currencies = [];
        
        foreach ($payments as $payment) {
            $currency = $payment['collectedCurrency'] ?? $payment['currency'] ?? 'TZS';
            $amount = $payment['collectedAmount'] ?? $payment['amount'] ?? 0;
            
            if (!isset($currencies[$currency])) {
                $currencies[$currency] = [
                    'currency' => $currency,
                    'count' => 0,
                    'amount' => 0,
                    'success' => 0,
                    'failed' => 0
                ];
            }
            
            $currencies[$currency]['count']++;
            $currencies[$currency]['amount'] += $amount;
            
            if ($payment['status'] === 'SUCCESS') {
                $currencies[$currency]['success']++;
            } elseif (in_array($payment['status'] ?? '', ['FAILED', 'ERROR'])) {
                $currencies[$currency]['failed']++;
            }
        }
        
        return array_values($currencies);
    }

    /**
     * Calculate top customers by transaction count and total amount
     */
    private function calculateTopCustomers($payments)
    {
        $customers = [];
        
        foreach ($payments as $payment) {
            // Extract customer name from various possible fields
            $customerName = $payment['customer']['customerName'] ?? 
                           $payment['payer_name'] ?? 
                           $payment['customerName'] ?? 
                           'Customer';
            
            $amount = $payment['collectedAmount'] ?? $payment['amount'] ?? 0;
            
            if (!isset($customers[$customerName])) {
                $customers[$customerName] = [
                    'name' => $customerName,
                    'count' => 0,
                    'total_amount' => 0,
                    'phone' => $payment['customer']['customerPhoneNumber'] ?? 
                               $payment['paymentPhoneNumber'] ?? 
                               $payment['phone'] ?? 'N/A'
                ];
            }
            
            $customers[$customerName]['count']++;
            $customers[$customerName]['total_amount'] += $amount;
        }
        
        // Sort by total amount descending and take top 5
        uasort($customers, function($a, $b) {
            return $b['total_amount'] <=> $a['total_amount'];
        });
        
        return array_slice($customers, 0, 5);
    }
    
    /**
     * Send manual SMS for payment confirmation
     */
    public function sendManualSMS(Request $request)
    {
        try {
            $reference = $request->input('reference');
            $phoneNumber = $request->input('phone_number');
            $customerName = $request->input('customer_name');
            $amount = $request->input('amount');
            
            if (!$reference || !$phoneNumber || !$customerName || !$amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required information for SMS'
                ], 400);
            }
            
            // Find transaction
            $transaction = \App\Models\Transaction::where('order_reference', $reference)->first();
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }
            
            // Use messaging service to send SMS
            $messaging = app(\App\Services\MessagingServiceAPI::class);
            
            // Prepare payment data for SMS
            $paymentData = [
                'orderReference' => $reference,
                'id' => $transaction->transaction_id,
                'status' => $transaction->status,
                'collectedAmount' => $transaction->amount,
                'collectedCurrency' => $transaction->currency,
                'paymentPhoneNumber' => $phoneNumber,
                'channel' => $transaction->payment_method,
                'customer' => [
                    'customerName' => $customerName,
                    'customerPhoneNumber' => $phoneNumber
                ],
                'createdAt' => $transaction->created_at,
                'updatedAt' => $transaction->updated_at
            ];
            
            // Generate SMS message
            $smsMessage = "FeedTan: Malipo yako ya TZS " . number_format($amount, 0) . " kwa reference {$reference} imethibitishwa. Ahsante kwa kutumia!";
            
            // Send SMS
            $result = $messaging->sendPaymentConfirmation($phoneNumber, $paymentData);
            
            // Update transaction with SMS details
            if ($result) {
                $transaction->update([
                    'sms_sent' => true,
                    'sms_message' => $smsMessage,
                    'sms_sent_at' => now(),
                    'sms_error' => null
                ]);
                
                Log::info('Manual SMS sent successfully', [
                    'reference' => $reference,
                    'phone_number' => $phoneNumber,
                    'customer_name' => $customerName,
                    'amount' => $amount
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'SMS sent successfully to ' . $customerName
                ]);
            } else {
                $transaction->update([
                    'sms_sent' => false,
                    'sms_message' => $smsMessage,
                    'sms_sent_at' => null,
                    'sms_error' => 'Failed to send manual SMS'
                ]);
                
                Log::error('Failed to send manual SMS', [
                    'reference' => $reference,
                    'phone_number' => $phoneNumber,
                    'customer_name' => $customerName,
                    'amount' => $amount
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send SMS. Please try again.'
                ], 500);
            }
            
        } catch (Exception $e) {
            Log::error('Error in sendManualSMS', [
                'error' => $e->getMessage(),
                'reference' => $reference,
                'phone_number' => $phoneNumber,
                'customer_name' => $customerName,
                'amount' => $amount
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending SMS. Please try again.'
            ], 500);
        }
    }
}
