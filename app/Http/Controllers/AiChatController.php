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

        // Try multiple models in fallback order
        $apiVersions = ['v1', 'v1beta'];
        $models = ['gemini-1.5-flash', 'gemini-1.0-pro', 'gemini-pro'];
        
        $lastError = null;
        
        foreach ($apiVersions as $apiVersion) {
            foreach ($models as $model) {
                try {
                    $response = Http::timeout(60)
                        ->post("https://generativelanguage.googleapis.com/{$apiVersion}/models/{$model}:generateContent?key={$apiKey}", [
                            'contents' => $messages,
                            'generationConfig' => [
                                'temperature' => 0.7,
                                'topK' => 40,
                                'topP' => 0.95,
                                'maxOutputTokens' => 2048,
                            ],
                        ]);

                    if ($response->successful()) {
                        $result = $response->json();
                        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                            return response()->json([
                                'success' => true,
                                'response' => $result['candidates'][0]['content']['parts'][0]['text'],
                            ]);
                        }
                    } else {
                        $lastError = $response->body();
                    }
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                }
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Error calling Gemini API',
            'error' => $lastError
        ], 500);
    }
}
