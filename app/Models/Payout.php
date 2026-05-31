<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    protected $fillable = [
        'order_reference',
        'transaction_id',
        'status',
        'amount',
        'currency',
        'payout_type',
        'recipient_name',
        'recipient_phone',
        'bank_account_number',
        'bank_name',
        'bic',
        'description',
        'callback_data',
        'user_id',
        'channel',
        'channel_provider',
        'transfer_type',
        'fee',
        'beneficiary_account_number',
        'beneficiary_account_name',
        'beneficiary_mobile',
        'beneficiary_email',
        'notes',
        'clickpesa_payout_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'callback_data' => 'array',
        'amount' => 'decimal:2',
        'fee' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function otps()
    {
        return $this->hasMany(PayoutOtp::class);
    }
}
