<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
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
            
            // System prompt
            $messages[] = [
                'role' => 'system',
                'content' => "You are a helpful AI assistant for Feedtan Digital Payment System. Help users with questions about payments, transactions, bills, and other system features."
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
                    'max_completion_tokens' => 1024,
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
