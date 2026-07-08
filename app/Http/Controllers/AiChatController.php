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
}
