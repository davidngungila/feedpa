<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsDeviceHeartbeat extends Model
{
    protected $table = 'sms_device_heartbeats';
    public $timestamps = false;
    protected $fillable = ['device_id', 'battery_level', 'network_type', 'pending_sms', 'app_version', 'android_version', 'ip_address', 'meta', 'created_at'];
    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public function device(): BelongsTo
    {
        return $this->belongsTo(SmsDevice::class, 'device_id');
    }
}
