<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\User;

class SmsMessage extends Model
{
    protected $table = 'sms_messages';
    protected $fillable = [
        'uuid', 'device_message_id', 'device_id', 'provider_id', 'sender', 'body', 'hash',
        'sms_timestamp', 'received_at', 'sync_status', 'processing_status', 'reconciliation_status',
        'parsed_data', 'failure_reason',
        'is_recorded','recorded_at','recorded_by','admin_comment','comment_by','commented_at'
    ];
    protected $casts = [
        'sms_timestamp' => 'datetime',
        'received_at' => 'datetime',
        'parsed_data' => 'array',
        'is_recorded' => 'boolean',
        'recorded_at' => 'datetime',
        'commented_at' => 'datetime',
    ];

    public function recordedBy(): BelongsTo { return $this->belongsTo(User::class,'recorded_by'); }
    public function commentBy(): BelongsTo { return $this->belongsTo(User::class,'comment_by'); }

    public function device(): BelongsTo
    {
        return $this->belongsTo(SmsDevice::class, 'device_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(SmsProvider::class, 'provider_id');
    }

    public function smsTransaction(): HasOne
    {
        return $this->hasOne(SmsTransaction::class, 'sms_message_id');
    }

    public function reconciliation(): HasOne
    {
        return $this->hasOne(SmsReconciliation::class, 'sms_message_id');
    }

    public static function computeHash(string $deviceCode, string $sender, string $timestamp, string $body): string
    {
        $normalized = trim($deviceCode) . '|' . trim($sender) . '|' . trim($timestamp) . '|' . trim($body);
        return hash('sha256', $normalized);
    }
}
