<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AiChatController extends Controller
{
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

    public function chat(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:5000',
                'history' => 'nullable|array',
            ]);

            $apiKey = SystemSetting::get('gemini_api_key');
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gemini API key not configured',
                ], 400);
            }

            // --- Step 1: Load all relevant business data ---
            $dateFilter = 'today';
            $settledStatuses = $this->settledStatuses();

            $baseQuery = Transaction::query();
            $this->applyDateFilter($baseQuery, $dateFilter);

            $settledQuery = (clone $baseQuery)->whereIn('status', $settledStatuses);

            $todaySettledAmount = (clone $settledQuery)->sum('amount');
            $todaySuccessfulCount = (clone $settledQuery)->count();
            $todayTotalCount = (clone $baseQuery)->count();
            $successRate = $todayTotalCount > 0
                ? round(($todaySuccessfulCount / $todayTotalCount) * 100, 2)
                : 0;

            // Get account balance safely
            try {
                $accountBalanceService = app(\App\Services\AccountBalanceService::class);
                $accountBalance = $accountBalanceService->getTzsBalance(refresh: true);
            } catch (\Exception $e) {
                $accountBalance = 'Error retrieving balance';
            }

            $topCustomersToday = (clone $settledQuery)->whereNotNull('phone')
                ->selectRaw('phone, max(customer_name) as customer_name, max(description) as description, count(*) as count, sum(amount) as total_amount')
                ->groupBy('phone')
                ->orderByDesc('total_amount')
                ->limit(10)
                ->get()
                ->map(function ($row) {
                    return [
                        'name' => $row->customer_name ?: $row->description ?: $row->phone,
                        'phone' => $row->phone,
                        'count' => (int)$row->count,
                        'total_amount' => $row->total_amount,
                    ];
                })->toArray();

            $paymentMethodsToday = (clone $settledQuery)->whereNotNull('payment_method')
                ->selectRaw('payment_method as name, count(*) as count, sum(amount) as total_amount')
                ->groupBy('payment_method')
                ->orderByDesc('count')
                ->get()
                ->toArray();

            $topPurposesToday = (clone $settledQuery)->whereNotNull('description')
                ->selectRaw('description, count(*) as count, sum(amount) as total_amount')
                ->groupBy('description')
                ->orderByDesc('total_amount')
                ->limit(10)
                ->get()
                ->toArray();

            $statusStatsToday = (clone $baseQuery)
                ->selectRaw('status, count(*) as count, sum(amount) as amount')
                ->groupBy('status')
                ->orderByDesc('count')
                ->get()
                ->toArray();

            $recentTransactions = (clone $settledQuery)->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($txn) {
                    return [
                        'order_reference' => $txn->order_reference,
                        'customer_name' => $txn->customer_name ?? $txn->description ?? 'Payment',
                        'phone' => $txn->phone,
                        'amount' => $txn->amount,
                        'status' => $txn->status,
                        'payment_method' => $txn->payment_method,
                        'created_at' => $txn->created_at ? $txn->created_at->toDateTimeString() : 'N/A',
                    ];
                })->toArray();

            // --- Step 2: Prepare system prompt with data ---
            $systemPrompt = <<<SYSTEM_PROMPT
You are a helpful financial assistant for Feedtan Digital staff. You have access to real-time business data from their payment system.

CURRENT BUSINESS DATA AS OF {now}:

**Today's Stats:
- Total Transactions Today: {$todayTotalCount}
- Successful Transactions Today: {$todaySuccessfulCount}
- Success Rate Today: {$successRate}%
- Total Amount Collected Today: TZS {$todaySettledAmount}
- Current Account Balance: TZS {$accountBalance['balance']}

**Top Customers Today:
{top_customers}

**Payment Methods Today:
{payment_methods}

**Top Payment Purposes Today:
{top_purposes}

**Transaction Status Breakdown Today:
{status_stats}

**Recent Successful Transactions Today (last 20):
{recent_transactions}

IMPORTANT: When answering questions, use this data directly. Be friendly and professional.
SYSTEM_PROMPT;

            // Format data for system prompt
            $topCustomersStr = empty($topCustomersToday) ? 'None yet today' : json_encode($topCustomersToday, JSON_PRETTY_PRINT);
            $paymentMethodsStr = empty($paymentMethodsToday) ? 'None yet today' : json_encode($paymentMethodsToday, JSON_PRETTY_PRINT);
            $topPurposesStr = empty($topPurposesToday) ? 'None yet today' : json_encode($topPurposesToday, JSON_PRETTY_PRINT);
            $statusStatsStr = empty($statusStatsToday) ? 'None yet today' : json_encode($statusStatsToday, JSON_PRETTY_PRINT);
            $recentTransactionsStr = empty($recentTransactions) ? 'None yet today' : json_encode($recentTransactions, JSON_PRETTY_PRINT);

            $systemPrompt = str_replace(
                ['{now}', '{top_customers}', '{payment_methods}', '{top_purposes}', '{status_stats}', '{recent_transactions}'],
                [now()->toDateTimeString(), $topCustomersStr, $paymentMethodsStr, $topPurposesStr, $statusStatsStr, $recentTransactionsStr],
                $systemPrompt
            );

            $messages = [
                [
                    'role' => 'user',
                    'parts' => [['text' => $systemPrompt]]
                ],
                [
                    'role' => 'model',
                    'parts' => [['text' => 'Okay, I understand. I have access to this business data and will use it to answer staff questions.']]
                ]
            ];
            
            if ($request->has('history') && is_array($request->history)) {
                foreach ($request->history as $item) {
                    $messages[] = [
                        'role' => $item['role'] ?? 'user',
                        'parts' => [['text' => $item['text'] ?? '']]
                    ];
                }
            }
            
            $messages[] = [
                'role' => 'user',
                'parts' => [['text' => $request->message]]
            ];

            // First, get list of available models
            $availableModel = null;
            $apiVersionToUse = null;
            
            foreach (['v1', 'v1beta'] as $apiVersion) {
                try {
                    $listResponse = Http::timeout(30)
                        ->get("https://generativelanguage.googleapis.com/{$apiVersion}/models?key={$apiKey}");
                    
                    if ($listResponse->successful()) {
                        $modelsList = $listResponse->json();
                        if (isset($modelsList['models']) && is_array($modelsList['models'])) {
                            foreach ($modelsList['models'] as $model) {
                                if (
                                    str_contains($model['name'], 'gemini') &&
                                    isset($model['supportedGenerationMethods']) &&
                                    in_array('generateContent', $model['supportedGenerationMethods'])
                                ) {
                                    $availableModel = str_replace('models/', '', $model['name']);
                                    $apiVersionToUse = $apiVersion;
                                    break 2;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Continue
                }
            }

            if (!$availableModel) {
                $fallbackModels = [
                    ['version' => 'v1', 'model' => 'gemini-1.5-flash'],
                    ['version' => 'v1', 'model' => 'gemini-1.0-pro'],
                    ['version' => 'v1beta', 'model' => 'gemini-1.5-flash'],
                    ['version' => 'v1beta', 'model' => 'gemini-1.0-pro'],
                ];
                
                foreach ($fallbackModels as $item) {
                    $apiVersionToUse = $item['version'];
                    $availableModel = $item['model'];
                    break;
                }
            }

            $response = Http::timeout(90)
                ->post("https://generativelanguage.googleapis.com/{$apiVersionToUse}/models/{$availableModel}:generateContent?key={$apiKey}", [
                    'contents' => $messages,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 2048,
                    ],
                ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error calling Gemini API',
                    'error' => $response->body(),
                    'usingModel' => $availableModel,
                    'usingVersion' => $apiVersionToUse
                ], $response->status());
            }

            $result = $response->json();
            
            $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No response received';

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
                'model' => $availableModel,
                'version' => $apiVersionToUse
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage() . ' (File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ')',
            ], 500);
        }
    }
}
