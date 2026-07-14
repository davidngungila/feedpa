<?php

namespace App\Http\Controllers;

use App\Models\Beneficiary;
use App\Models\Payout;
use App\Models\SystemSetting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    public function index()
    {
        return view('ai-chat.index');
    }
    
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        $apiKey = SystemSetting::get('groq_api_key') ?? env('GROQ_API_KEY');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Groq API key not configured. Please set it in system settings or .env file.',
            ], 400);
        }

        try {
            $history = $validated['history'] ?? [];
            if (is_string($history)) {
                $decodedHistory = json_decode($history, true);
                $history = is_array($decodedHistory) ? $decodedHistory : [];
            }

            $imageFile = $request->file('image');
            $messages = [];
            
            // Build user's system data context
            $user = auth()->user();
            $systemContext = "";
            if ($user) {
                $payouts = Payout::where('user_id', $user->id)
                    ->latest()
                    ->take(20)
                    ->get(['id', 'order_reference', 'status', 'amount', 'currency', 'payout_type', 'recipient_name', 'created_at']);
                
                $transactions = Transaction::latest()
                    ->take(20)
                    ->get(['id', 'order_reference', 'status', 'amount', 'currency', 'type', 'payer_name', 'created_at']);
                
                $beneficiaries = Beneficiary::where('user_id', $user->id)
                    ->where('is_active', true)
                    ->get(['id', 'name', 'type', 'phone', 'bank_name', 'account_number']);
                
                $systemContext .= "## Current User's System Data\n";
                $systemContext .= "User: {$user->name} (ID: {$user->id}, Email: {$user->email})\n\n";
                
                if ($payouts->count() > 0) {
                    $systemContext .= "### Recent Payouts (Last 20)\n";
                    foreach ($payouts as $payout) {
                        $systemContext .= "- ID: {$payout->id}, Ref: {$payout->order_reference}, Status: {$payout->status}, Amount: {$payout->amount} {$payout->currency}, Type: {$payout->payout_type}, Recipient: {$payout->recipient_name}, Date: {$payout->created_at}\n";
                    }
                }
                
                if ($transactions->count() > 0) {
                    $systemContext .= "\n### Recent Transactions (Last 20)\n";
                    foreach ($transactions as $transaction) {
                        $systemContext .= "- ID: {$transaction->id}, Ref: {$transaction->order_reference}, Status: {$transaction->status}, Amount: {$transaction->amount} {$transaction->currency}, Type: {$transaction->type}, Payer: {$transaction->payer_name}, Date: {$transaction->created_at}\n";
                    }
                }
                
                if ($beneficiaries->count() > 0) {
                    $systemContext .= "\n### Active Beneficiaries\n";
                    foreach ($beneficiaries as $beneficiary) {
                        $systemContext .= "- ID: {$beneficiary->id}, Name: {$beneficiary->name}, Type: {$beneficiary->type}, Phone: {$beneficiary->phone}, Bank: {$beneficiary->bank_name}, Account: {$beneficiary->account_number}\n";
                    }
                }
            }
            
            // System prompt
            $messages[] = [
                'role' => 'system',
                'content' => "You are a helpful AI assistant for Feedtan Digital Payment System. Help users with questions about payments, transactions, bills, and other system features. You have access to the user's recent system data. Use this data to answer questions about their payouts, transactions, and beneficiaries. " . $systemContext
            ];

            if (is_array($history)) {
                foreach ($history as $item) {
                    $role = $item['role'] ?? 'user';
                    // Map any invalid roles to valid ones
                    if (!in_array($role, ['system', 'user', 'assistant'])) {
                        $role = $role === 'model' ? 'assistant' : 'user';
                    }
                    $messages[] = [
                        'role' => $role,
                        'content' => $item['text'] ?? ''
                    ];
                }
            }

            $userMessage = [
                'role' => 'user',
                'content' => $request->message,
            ];

            $model = 'llama-3.3-70b-versatile';
            if ($imageFile) {
                $mimeType = $imageFile->getMimeType() ?: 'image/jpeg';
                $base64Image = base64_encode(file_get_contents($imageFile->getRealPath()));

                $userMessage['content'] = [
                    [
                        'type' => 'text',
                        'text' => $request->message,
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => "data:{$mimeType};base64,{$base64Image}",
                        ],
                    ],
                ];

                $model = 'meta-llama/llama-4-scout-17b-16e-instruct';
            }

            $messages[] = $userMessage;

            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_completion_tokens' => 2048,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                $aiResponse = $result['choices'][0]['message']['content'] ?? null;
                
                if ($aiResponse) {
                    return response()->json([
                        'success' => true,
                        'response' => $aiResponse,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'AI Error: No response text received from Groq.',
                    ], 500);
                }
            } else {
                Log::error('Groq API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI Error: Groq API failed (status ' . $response->status() . '): ' . $response->body(),
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('AI Chat exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'AI Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
