<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        $messages = [];
        
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

        try {
            $response = Http::timeout(60)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$apiKey}", [
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
                    'error' => $response->body()
                ], $response->status());
            }

            $result = $response->json();
            
            $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No response received';

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
