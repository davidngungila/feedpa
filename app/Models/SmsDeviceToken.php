<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsDeviceToken extends Model
{
    protected $table = 'sms_device_tokens';
    protected $fillable = ['device_id', 'token', 'plain_hint', 'last_used_at', 'expires_at', 'is_revoked'];
    protected $casts = ['last_used_at' => 'datetime', 'expires_at' => 'datetime', 'is_revoked' => 'boolean'];
    public $plain_token = null; // transient

    public function device(): BelongsTo
    {
        return $this->belongsTo(SmsDevice::class, 'device_id');
    }
}
