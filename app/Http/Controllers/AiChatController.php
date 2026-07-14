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

        $apiKey = SystemSetting::get('gemini_api_key') ?? env('GEMINI_API_KEY');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API key not configured. Please set it in system settings or .env file.',
            ], 400);
        }

        try {
            $messages = [];
            
            // System prompt
            $messages[] = [
                'role' => 'user',
                'parts' => [['text' => "You are a helpful AI assistant for Feedtan Digital Payment System. Help users with questions about payments, bills, account statements, and other system features."]]
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

            // Try multiple models in order
            $modelsToTry = [
                ['model' => 'gemini-1.5-flash', 'version' => 'v1'],
                ['model' => 'gemini-2.0-flash', 'version' => 'v1beta'],
                ['model' => 'gemini-1.5-flash-002', 'version' => 'v1beta'],
                ['model' => 'gemini-1.0-pro', 'version' => 'v1'],
            ];
            
            $aiResponse = null;
            $attempts = [];
            $lastError = null;
            
            foreach ($modelsToTry as $modelConfig) {
                $attempt = [
                    'model' => $modelConfig['model'],
                    'version' => $modelConfig['version'],
                    'success' => false,
                ];
                
                try {
                    $response = Http::timeout(60)
                        ->post("https://generativelanguage.googleapis.com/{$modelConfig['version']}/models/{$modelConfig['model']}:generateContent?key={$apiKey}", [
                            'contents' => $messages,
                            'generationConfig' => [
                                'temperature' => 0.7,
                                'maxOutputTokens' => 1024,
                            ],
                        ]);

                    $attempt['status'] = $response->status();
                    
                    if ($response->successful()) {
                        $result = $response->json();
                        $attempt['response'] = $result;
                        $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                        if ($aiResponse) {
                            $attempt['success'] = true;
                            $attempts[] = $attempt;
                            break;
                        } else {
                            $lastError = "Model {$modelConfig['model']} returned no text response: " . json_encode($result);
                        }
                    } else {
                        $attempt['body'] = $response->body();
                        $lastError = "Model {$modelConfig['model']} failed (status {$response->status()}): " . $response->body();
                        \Illuminate\Support\Facades\Log::error("Gemini model {$modelConfig['model']} ({$modelConfig['version']}) failed", $attempt);
                    }
                } catch (\Exception $e) {
                    $attempt['exception'] = $e->getMessage();
                    $attempt['trace'] = $e->getTraceAsString();
                    $lastError = "Model {$modelConfig['model']} exception: " . $e->getMessage();
                    \Illuminate\Support\Facades\Log::error("Gemini model {$modelConfig['model']} exception", $attempt);
                }
                
                $attempts[] = $attempt;
            }

            if (!$aiResponse) {
                \Illuminate\Support\Facades\Log::error('AI Chat failed: All models tried without success', [
                    'attempts' => $attempts,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'AI Error: ' . ($lastError ?? 'Sorry, AI service temporarily unavailable. Please try again later.'),
                ], 500);
            }

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
            ]);
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
