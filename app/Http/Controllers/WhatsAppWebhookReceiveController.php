<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppWebhook;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookReceiveController extends Controller
{
    public function receive(Request $request, ?string $token = null)
    {
        if ($token) {
            $webhook = WhatsAppWebhook::where('token', $token)->where('is_active', true)->first();

            if (!$webhook) {
                Log::warning('WhatsApp webhook received for unknown or inactive token', ['token' => $token]);
                return response()->json(['success' => false, 'message' => 'Invalid webhook token.'], 404);
            }

            $secret = $webhook->secret;
            $headerSecret = $request->header('X-Webhook-Signature', $request->header('X-Webhook-Secret', ''));
            $bodySecret = (string) ($request->input('secret') ?? '');

            if ($secret && ($headerSecret !== $secret) && ($bodySecret !== $secret)) {
                Log::warning('WhatsApp webhook secret mismatch', [
                    'webhook' => $webhook->name,
                    'event' => $request->input('event'),
                ]);
                return response()->json(['success' => false, 'message' => 'Invalid webhook secret.'], 401);
            }

            return $this->processPayload($request, $webhook->name);
        }

        return $this->receiveLive($request);
    }

    protected function receiveLive(Request $request)
    {
        $whatsapp = app(WhatsAppService::class);

        if (!$whatsapp->hasPersonalAccessToken()) {
            Log::warning('WhatsApp webhook received but no personal access token configured');
            return response()->json(['success' => false, 'message' => 'No session token configured.'], 401);
        }

        $signature = (string) $request->header('X-Webhook-Signature', '');
        $secret = $this->resolveLiveSecret($whatsapp);

        if (!$secret) {
            Log::warning('WhatsApp webhook received but no live webhook secret found', [
                'url' => $request->fullUrl(),
                'event' => $request->input('event'),
            ]);
            return response()->json(['success' => false, 'message' => 'No matching webhook secret.'], 401);
        }

        if ($signature === '' || !hash_equals($secret, $signature)) {
            Log::warning('WhatsApp webhook live secret mismatch', ['event' => $request->input('event')]);
            return response()->json(['success' => false, 'message' => 'Invalid webhook signature.'], 401);
        }

        return $this->processPayload($request, 'live-session');
    }

    protected function resolveLiveSecret(WhatsAppService $whatsapp): ?string
    {
        $key = 'whatsapp.live_webhook_secret';

        if (Cache::has($key)) {
            return (string) Cache::get($key);
        }

        $secret = null;
        foreach ($whatsapp->getSessions() as $session) {
            $details = $whatsapp->getSessionDetails((int) ($session['id'] ?? 0));
            if (!empty($details['webhook_secret'])) {
                $secret = (string) $details['webhook_secret'];
                break;
            }
        }

        if ($secret) {
            Cache::put($key, $secret, now()->addMinutes(5));
        }

        return $secret;
    }

    protected function processPayload(Request $request, string $source): \Illuminate\Http\JsonResponse
    {
        Log::info('WhatsApp webhook received', [
            'source' => $source,
            'event' => $request->input('event', 'unknown'),
            'payload' => $request->all(),
        ]);

        return response()->json(['success' => true, 'message' => 'Webhook received.']);
    }
}
