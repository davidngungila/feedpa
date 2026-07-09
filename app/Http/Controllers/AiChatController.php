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

        $apiKey = SystemSetting::get('gemini_api_key');
        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API key not configured. Please set it in system settings.',
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

            // Use gemini-1.5-flash which is reliable
            $response = Http::timeout(60)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => $messages,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 1024,
                    ],
                ]);

            if (!$response->successful()) {
                \Illuminate\Support\Facades\Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . ($response->json('error.message') ?? 'Could not reach AI service'),
                ], $response->status());
            }

            $result = $response->json();
            $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No response received from AI';

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
                'message' => 'Sorry, something went wrong. Please try again later.',
            ], 500);
        }
    }
}
