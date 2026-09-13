<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessageLog extends Model
{
    protected $table = 'whatsapp_message_logs';
    protected $fillable = ['whatsapp_message_id','device_id','action','details','meta'];
    protected $casts = ['meta' => 'array'];
    public $timestamps = true;

    public function message(): BelongsTo { return $this->belongsTo(WhatsappMessage::class, 'whatsapp_message_id'); }
    public function device(): BelongsTo { return $this->belongsTo(SmsDevice::class, 'device_id'); }
}
