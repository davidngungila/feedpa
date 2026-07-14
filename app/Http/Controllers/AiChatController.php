<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    public function index()
    {
        return view('ai-chat.index');
    }
    
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
        ]);

        $apiKey = SystemSetting::get('groq_api_key') ?? env('GROQ_API_KEY');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Groq API key not configured. Please set it in system settings or .env file.',
            ], 400);
        }

        try {
            $messages = [];
            
            // System prompt
            $messages[] = [
                'role' => 'system',
                'content' => "You are a helpful AI assistant for Feedtan Digital Payment System. Help users with questions about payments, transactions, bills, and other system features."
            ];

            if ($request->has('history') && is_array($request->history)) {
                foreach ($request->history as $item) {
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

            $messages[] = [
                'role' => 'user',
                'content' => $request->message
            ];

            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 1024,
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
                \Illuminate\Support\Facades\Log::error('Groq API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI Error: Groq API failed (status ' . $response->status() . '): ' . $response->body(),
                ], 500);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Chat exception', [
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
