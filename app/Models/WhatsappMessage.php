<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WhatsappMessage extends Model
{
    protected $table = 'whatsapp_messages';
    protected $fillable = [
        'uuid','device_id','created_by','recipient_phone','message',
        'attachment_path','attachment_name','attachment_mime','attachment_size',
        'status','requested_at','received_by_device_at','opened_whatsapp_at','sent_at','failed_at','failure_reason'
    ];
    protected $casts = [
        'requested_at' => 'datetime',
        'received_by_device_at' => 'datetime',
        'opened_whatsapp_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function getRouteKeyName(){ return 'uuid'; }

    protected static function booted()
    {
        static::creating(function ($m) {
            if (empty($m->uuid)) $m->uuid = (string) Str::uuid();
            if (empty($m->requested_at)) $m->requested_at = now();
        });
    }

    public function device(): BelongsTo { return $this->belongsTo(SmsDevice::class, 'device_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function logs(): HasMany { return $this->hasMany(WhatsappMessageLog::class, 'whatsapp_message_id')->latest(); }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['SENT','FAILED','CANCELLED','EXPIRED']);
    }

    // For file security: generate private path, ensure not public
    public static function attachmentDir(string $uuid): string
    {
        return 'whatsapp_attachments/'.$uuid;
    }
}
