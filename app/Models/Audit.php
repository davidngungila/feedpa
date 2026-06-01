<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'country',
        'city',
        'timezone',
        'device_type',
        'device_browser',
        'device_platform',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to parse user agent
    private static function parseUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return [
                'device_type' => null,
                'device_browser' => null,
                'device_platform' => null,
            ];
        }

        $deviceType = 'Desktop';
        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            $deviceType = 'Mobile';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            $deviceType = 'Tablet';
        }

        $browser = 'Unknown';
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        }

        $platform = 'Unknown';
        if (preg_match('/Windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            $platform = 'iOS';
        }

        return [
            'device_type' => $deviceType,
            'device_browser' => $browser,
            'device_platform' => $platform,
        ];
    }

    // Helper method to get location from IP
    private static function getLocationFromIP(?string $ip): array
    {
        $country = null;
        $city = null;
        $timezone = null;

        if ($ip && in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            // For local testing, default to Dar es Salaam
            return [
                'country' => 'Tanzania',
                'city' => 'Dar es Salaam',
                'timezone' => 'Africa/Dar_es_Salaam'
            ];
        }

        try {
            // Try to use free IP geolocation API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://ip-api.com/json/{$ip}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $data = json_decode($response, true);
                if ($data && isset($data['status']) && $data['status'] === 'success') {
                    $country = $data['country'] ?? null;
                    $city = $data['city'] ?? null;
                    $timezone = $data['timezone'] ?? null;
                }
            }
        } catch (\Exception $e) {
            // Ignore any errors
        }

        return [
            'country' => $country,
            'city' => $city,
            'timezone' => $timezone
        ];
    }

    // Helper method to log audit entries
    public static function log(string $action, ?string $details = null): self
    {
        $request = request();
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        
        $deviceInfo = self::parseUserAgent($userAgent);
        $locationInfo = self::getLocationFromIP($ip);

        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'country' => $locationInfo['country'],
            'city' => $locationInfo['city'],
            'timezone' => $locationInfo['timezone'],
            'device_type' => $deviceInfo['device_type'],
            'device_browser' => $deviceInfo['device_browser'],
            'device_platform' => $deviceInfo['device_platform'],
        ]);
    }
}
