<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsTransaction extends Model
{
    protected $table = 'sms_transactions';
    protected $fillable = [
        'sms_message_id', 'device_id', 'provider_id', 'provider_code', 'transaction_type',
        'amount', 'currency', 'reference', 'counterparty', 'counterparty_name', 'balance',
        'transaction_at', 'raw_extracted'
    ];
    protected $casts = [
        'transaction_at' => 'datetime',
        'raw_extracted' => 'array',
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function smsMessage(): BelongsTo
    {
        return $this->belongsTo(SmsMessage::class, 'sms_message_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(SmsDevice::class, 'device_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(SmsProvider::class, 'provider_id');
    }
}
