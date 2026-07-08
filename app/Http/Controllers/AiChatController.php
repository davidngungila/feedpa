<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\Payout;
use App\Models\AccountBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
        ]);

        $apiKey = SystemSetting::get('gemini_api_key');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API key not configured',
            ], 400);
        }

        // Gather system data for the AI
        $systemData = $this->gatherSystemData();

        $messages = [];

        // Add system prompt with data access
        $messages[] = [
            'role' => 'user',
            'parts' => [['text' => "You are a helpful AI assistant for Feedtan ClickPesa staff. You have access to real data from the system. Use this data to answer questions about transactions, payments, payouts, customers, and business operations. If the data doesn't have the answer, say so clearly but still be helpful.\n\nCURRENT SYSTEM DATA:\n" . json_encode($systemData, JSON_PRETTY_PRINT) . "\n\nAlways use the latest data provided when answering questions. Format your answers clearly and professionally."]]
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
                            // Check if model supports generateContent
                            if (
                                str_contains($model['name'], 'gemini') &&
                                isset($model['supportedGenerationMethods']) &&
                                in_array('generateContent', $model['supportedGenerationMethods'])
                            ) {
                                // Found a supported model
                                $availableModel = str_replace('models/', '', $model['name']);
                                $apiVersionToUse = $apiVersion;
                                break 2;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue to next API version
            }
        }

        // If no model found, try fallback models
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

        // Now try to use the model
        try {
            $response = Http::timeout(60)
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
                'message' => 'An error occurred: ' . $e->getMessage(),
                'model' => $availableModel,
                'version' => $apiVersionToUse
            ], 500);
        }
    }

    private function gatherSystemData(): array
    {
        $now = Carbon::now();
        $settledStatuses = ['SUCCESS', 'SETTLED', 'success', 'settled'];

        // Today's data
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $todayTransactions = Transaction::whereBetween('created_at', [$todayStart, $todayEnd])->get();
        $todaySettled = $todayTransactions->whereIn('status', $settledStatuses);
        $todaySettledCount = $todaySettled->count();
        $todaySettledAmount = $todaySettled->sum('amount');

        // This week's data
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $weekTransactions = Transaction::whereBetween('created_at', [$weekStart, $weekEnd])->get();
        $weekSettled = $weekTransactions->whereIn('status', $settledStatuses);
        $weekSettledCount = $weekSettled->count();
        $weekSettledAmount = $weekSettled->sum('amount');

        // This month's data
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $monthTransactions = Transaction::whereBetween('created_at', [$monthStart, $monthEnd])->get();
        $monthSettled = $monthTransactions->whereIn('status', $settledStatuses);
        $monthSettledCount = $monthSettled->count();
        $monthSettledAmount = $monthSettled->sum('amount');

        // Payout data
        $todayPayouts = Payout::whereBetween('created_at', [$todayStart, $todayEnd])->get();
        $monthPayouts = Payout::whereBetween('created_at', [$monthStart, $monthEnd])->get();

        // Account balance
        $accountBalance = AccountBalance::where('currency', 'TZS')->first();

        // Top customers
        $topCustomers = $monthSettled
            ->whereNotNull('phone')
            ->groupBy('phone')
            ->map(function ($group) {
                return [
                    'phone' => $group->first()->phone,
                    'name' => $group->first()->customer_name ?? $group->first()->description ?? 'Unknown',
                    'count' => $group->count(),
                    'total_amount' => $group->sum('amount')
                ];
            })
            ->sortByDesc('total_amount')
            ->take(10)
            ->values()
            ->toArray();

        // Recent transactions
        $recentTransactions = Transaction::orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($txn) {
                return [
                    'id' => $txn->id,
                    'order_reference' => $txn->order_reference,
                    'status' => $txn->status,
                    'amount' => $txn->amount,
                    'customer_name' => $txn->customer_name ?? $txn->description ?? 'Unknown',
                    'phone' => $txn->phone,
                    'payment_method' => $txn->payment_method,
                    'created_at' => $txn->created_at->toISOString()
                ];
            })
            ->toArray();

        return [
            'current_datetime' => $now->toISOString(),
            'today' => [
                'settled_transactions_count' => $todaySettledCount,
                'settled_amount_tzs' => $todaySettledAmount,
                'payouts_count' => $todayPayouts->count(),
                'payouts_amount_tzs' => $todayPayouts->sum('amount')
            ],
            'this_week' => [
                'settled_transactions_count' => $weekSettledCount,
                'settled_amount_tzs' => $weekSettledAmount
            ],
            'this_month' => [
                'settled_transactions_count' => $monthSettledCount,
                'settled_amount_tzs' => $monthSettledAmount,
                'payouts_count' => $monthPayouts->count(),
                'payouts_amount_tzs' => $monthPayouts->sum('amount')
            ],
            'account_balance_tzs' => $accountBalance?->balance ?? 0,
            'top_customers' => $topCustomers,
            'recent_transactions' => $recentTransactions
        ];
    }
}
