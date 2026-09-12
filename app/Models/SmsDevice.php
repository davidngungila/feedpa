<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SmsDevice extends Model
{
    protected $table = 'sms_devices';
    protected $fillable = [
        'device_code', 'name', 'location_id', 'phone_number', 'sim_slot', 'sim_operator',
        'activation_code', 'activation_expires_at', 'status', 'android_version', 'app_version',
        'battery_level', 'network_type', 'signal_strength', 'last_heartbeat_at', 'last_sync_at',
        'last_sms_at', 'config', 'created_by'
    ];
    protected $casts = [
        'activation_expires_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'last_sms_at' => 'datetime',
        'config' => 'array',
        'battery_level' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(SmsLocation::class, 'location_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(SmsDeviceToken::class, 'device_id');
    }

    public function activeToken(): ?SmsDeviceToken
    {
        return $this->tokens()->where('is_revoked', false)->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })->latest()->first();
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(SmsDeviceHeartbeat::class, 'device_id');
    }

    public function smsMessages(): HasMany
    {
        return $this->hasMany(SmsMessage::class, 'device_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOnline(int $thresholdSeconds = 180): bool
    {
        if (!$this->last_heartbeat_at) return false;
        return $this->last_heartbeat_at->gt(now()->subSeconds($thresholdSeconds));
    }

    public function generateActivationCode(int $ttlMinutes = 60): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        // ensure unique
        while (self::where('activation_code', $code)->exists()) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        }
        $this->update([
            'activation_code' => $code,
            'activation_expires_at' => now()->addMinutes($ttlMinutes),
        ]);
        return $code;
    }

    public function createAuthToken(?int $ttlDays = null): SmsDeviceToken
    {
        $plain = Str::random(60);
        $hash = hash('sha256', $plain);
        $token = $this->tokens()->create([
            'token' => $hash,
            'plain_hint' => substr($plain, -4),
            'expires_at' => $ttlDays ? now()->addDays($ttlDays) : null,
        ]);
        // attach plain for one-time return
        $token->plain_token = $plain;
        return $token;
    }

    public static function findByToken(string $plainToken): ?self
    {
        $hash = hash('sha256', $plainToken);
        $token = SmsDeviceToken::where('token', $hash)->where('is_revoked', false)->first();
        if (!$token) return null;
        if ($token->expires_at && $token->expires_at->isPast()) return null;
        $device = $token->device;
        if (!$device || $device->status !== 'ACTIVE') return null;
        $token->update(['last_used_at' => now()]);
        return $device;
    }
}
