<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsDeviceLog extends Model
{
    protected $table = 'sms_device_logs';
    protected $fillable = ['device_id','level','message','context'];
    protected $casts = ['context'=>'array'];
    public function device(): BelongsTo { return $this->belongsTo(SmsDevice::class,'device_id'); }
}
