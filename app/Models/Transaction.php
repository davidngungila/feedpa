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
        'sms_sent',
        'sms_message',
        'sms_sent_at',
        'sms_error',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_data' => 'array',
        'callback_received_at' => 'datetime',
        'sms_sent' => 'boolean',
        'sms_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

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

    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', ['SUCCESS', 'SETTLED']);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['PROCESSING', 'PENDING']);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }

    public function scopePayments($query)
    {
        return $query->where('type', 'payment');
    }
}
