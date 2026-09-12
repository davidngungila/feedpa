<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsProvider extends Model
{
    protected $table = 'sms_providers';
    protected $fillable = ['name', 'code', 'sender_ids', 'detection_keywords', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'detection_keywords' => 'array'];

    public function smsMessages(): HasMany
    {
        return $this->hasMany(SmsMessage::class, 'provider_id');
    }

    public function parsingRules(): HasMany
    {
        return $this->hasMany(SmsParsingRule::class, 'provider_id');
    }

    public static function detectProvider(string $sender, string $body): ?self
    {
        $providers = self::where('is_active', true)->get();
        $bodyLower = strtolower($body);
        $senderLower = strtolower(trim($sender));

        foreach ($providers as $provider) {
            // Check sender_ids match
            if ($provider->sender_ids) {
                $senders = array_map(fn($s) => strtolower(trim($s)), explode(',', $provider->sender_ids));
                foreach ($senders as $s) {
                    if ($s !== '' && (str_contains($senderLower, $s) || str_contains($bodyLower, $s))) {
                        return $provider;
                    }
                }
            }
            // Check detection keywords
            if ($provider->detection_keywords) {
                $keywords = is_array($provider->detection_keywords)
                    ? $provider->detection_keywords
                    : json_decode($provider->detection_keywords, true);
                if (is_array($keywords)) {
                    foreach ($keywords as $kw) {
                        if (str_contains($bodyLower, strtolower(trim($kw)))) {
                            return $provider;
                        }
                    }
                }
            }
        }

        // Fallback simple mapping by sender name
        $map = [
            'mpesa' => 'MPESA',
            'm-pesa' => 'MPESA',
            'tigo' => 'MIXX',
            'mixx' => 'MIXX',
            'yas' => 'MIXX',
            'airtel' => 'AIRTEL',
            'halopesa' => 'HALOPESA',
            'halotel' => 'HALOPESA',
        ];
        foreach ($map as $key => $code) {
            if (str_contains($senderLower, $key) || str_contains($bodyLower, $key)) {
                $found = self::where('code', $code)->first();
                if ($found) return $found;
            }
        }

        return null;
    }
}
