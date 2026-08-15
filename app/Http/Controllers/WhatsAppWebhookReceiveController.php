<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookReceiveController extends Controller
{
    public function receive(Request $request, string $token)
    {
        $webhook = WhatsAppWebhook::where('token', $token)->where('is_active', true)->first();

        if (!$webhook) {
            Log::warning('WhatsApp webhook received for unknown or inactive token', ['token' => $token]);
            return response()->json(['success' => false, 'message' => 'Invalid webhook token.'], 404);
        }

        $secret = $webhook->secret;
        $headerSecret = $request->header('X-Webhook-Secret', '');
        $bodySecret = (string) ($request->input('secret') ?? '');

        if ($secret && ($headerSecret !== $secret) && ($bodySecret !== $secret)) {
            Log::warning('WhatsApp webhook secret mismatch', [
                'webhook' => $webhook->name,
                'event' => $request->input('event'),
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid webhook secret.'], 401);
        }

        Log::info('WhatsApp webhook received', [
            'webhook' => $webhook->name,
            'event' => $request->input('event', 'unknown'),
            'payload' => $request->all(),
        ]);

        return response()->json(['success' => true, 'message' => 'Webhook received.']);
    }
}
