<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppSettingsController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function index()
    {
        $settings = [
            'whatsapp_session_api_key' => SystemSetting::get('whatsapp_session_api_key'),
            'whatsapp_personal_access_token' => SystemSetting::get('whatsapp_personal_access_token'),
            'whatsapp_base_url' => SystemSetting::get('whatsapp_base_url', 'https://www.wasenderapi.com/api'),
            'whatsapp_enabled' => SystemSetting::get('whatsapp_enabled', false),
        ];

        $sessionInfo = null;
        if ($settings['whatsapp_session_api_key']) {
            try {
                $sessionInfo = $this->whatsapp->getSessionInfo();
            } catch (\Exception $e) {
                Log::error('Failed to get WhatsApp session info: ' . $e->getMessage());
            }
        }

        return view('settings.whatsapp', compact('settings', 'sessionInfo'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_session_api_key' => 'nullable|string|max:500',
            'whatsapp_personal_access_token' => 'nullable|string|max:500',
            'whatsapp_base_url' => 'nullable|string|max:500',
            'whatsapp_enabled' => 'nullable|boolean',
        ]);

        SystemSetting::set('whatsapp_session_api_key', $validated['whatsapp_session_api_key'] ?? null);
        SystemSetting::set('whatsapp_personal_access_token', $validated['whatsapp_personal_access_token'] ?? null);
        SystemSetting::set('whatsapp_base_url', $validated['whatsapp_base_url'] ?? 'https://www.wasenderapi.com/api');
        SystemSetting::set('whatsapp_enabled', filter_var($request->input('whatsapp_enabled', false), FILTER_VALIDATE_BOOLEAN));

        return back()->with('success', 'WhatsApp settings updated successfully!');
    }

    public function testConnection()
    {
        try {
            $sessionInfo = $this->whatsapp->getSessionInfo();

            if ($sessionInfo) {
                return response()->json([
                    'success' => true,
                    'message' => 'WhatsApp connection successful!',
                    'data' => $sessionInfo
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve session information. Please check your API key.'
            ], 400);
        } catch (\Exception $e) {
            Log::error('WhatsApp connection test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testWhatsApp(Request $request)
    {
        $validated = $request->validate([
            'test_phone' => 'required|string',
            'test_message' => 'required|string',
        ]);

        try {
            $result = $this->whatsapp->sendText($validated['test_phone'], $validated['test_message']);

            if ($result['success'] ?? false) {
                return back()->with('success', 'Test WhatsApp sent successfully!');
            }

            return back()->with('error', 'Failed to send test WhatsApp: ' . ($result['message'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('WhatsApp test failed: ' . $e->getMessage());
            return back()->with('error', 'Test failed: ' . $e->getMessage());
        }
    }
}
