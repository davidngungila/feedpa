<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsReconciliation extends Model
{
    protected $table = 'sms_reconciliations';
    protected $fillable = [
        'sms_transaction_id', 'sms_message_id', 'matched_transaction_id', 'matched_payout_id',
        'status', 'reconciled_by', 'reconciled_at', 'notes', 'match_meta'
    ];
    protected $casts = ['reconciled_at' => 'datetime', 'match_meta' => 'array'];

    public function smsTransaction(): BelongsTo
    {
        return $this->belongsTo(SmsTransaction::class, 'sms_transaction_id');
    }

    public function smsMessage(): BelongsTo
    {
        return $this->belongsTo(SmsMessage::class, 'sms_message_id');
    }
}
