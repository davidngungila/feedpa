<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payout extends Model
{
    protected $fillable = [
        'order_reference',
        'transaction_id',
        'status',
        'workflow_stage',
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
        'initiated_by',
        'initiated_at',
        'initiation_verified_by',
        'initiation_verified_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'payment_otp_requested_by',
        'payment_otp_requested_at',
        'payment_authorized_by',
        'payment_authorized_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'callback_data' => 'array',
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'initiated_at' => 'datetime',
        'initiation_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'payment_otp_requested_at' => 'datetime',
        'payment_authorized_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(PayoutOtp::class);
    }
    
    public function notes()
    {
        return $this->hasMany(PayoutNote::class)->latest();
    }
    
    public function resolvedDescription()
    {
        $callbackData = $this->callback_data ?? [];
        return $this->description 
            ?? $callbackData['notes'] 
            ?? $callbackData['description']
            ?? 'N/A';
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function initiationVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiation_verified_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function paymentOtpRequester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_otp_requested_by');
    }

    public function paymentAuthorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_authorized_by');
    }
}
