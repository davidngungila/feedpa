<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'order_reference',
        'transaction_id',
        'status',
        'amount',
        'currency',
        'phone',
        'payer_name',
        'email',
        'description',
        'type',
        'payment_method',
        'callback_data',
        'callback_received_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_data' => 'array',
        'callback_received_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the human-readable status description
     */
    public function getStatusDescriptionAttribute(): string
    {
        return match($this->status) {
            'SUCCESS' => 'Payment completed successfully',
            'SETTLED' => 'Payment has been settled',
            'PROCESSING' => 'Payment is being processed',
            'PENDING' => 'Payment is pending',
            'FAILED' => 'Payment failed',
            default => 'Unknown status'
        };
    }

    /**
     * Scope to get successful transactions
     */
    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['SUCCESS', 'SETTLED']);
    }

    /**
     * Scope to get pending transactions
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['PROCESSING', 'PENDING']);
    }

    /**
     * Scope to get failed transactions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }

    /**
     * Scope to get payments
     */
    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }

    /**
     * Scope to get payouts
     */
    public function scopePayouts($query)
    {
        return $query->where('type', 'payout');
    }

    /**
     * Scope to get billpay transactions
     */
    public function scopeBillpay($query)
    {
        return $query->where('type', 'billpay');
    }
}
