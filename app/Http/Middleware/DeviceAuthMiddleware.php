<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SmsDevice;
use Symfony\Component\HttpFoundation\Response;

class DeviceAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            $token = $request->header('X-Device-Token') ?? $request->input('device_token');
        }

        $deviceId = $request->header('X-Device-ID') ?? $request->input('device_id') ?? $request->input('device_code');

        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Device token required. Use Authorization: Bearer <token> or X-Device-Token header.'], 401);
        }

        $device = SmsDevice::findByToken($token);
        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired device token.'], 401);
        }

        // Optional: if device_id/code provided, verify it matches token device
        if ($deviceId && $device->device_code !== $deviceId && (string)$device->id !== (string)$deviceId) {
            return response()->json(['success' => false, 'message' => 'Device ID does not match token.'], 401);
        }

        if ($device->status !== 'ACTIVE') {
            return response()->json(['success' => false, 'message' => 'Device not active. Status: '.$device->status], 403);
        }

        $request->merge(['__device' => $device]);
        $request->attributes->set('device', $device);

        return $next($request);
    }
}
