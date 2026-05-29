<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    protected $keyType = 'string'; // since id is uuid
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'order_reference',
        'transaction_id',
        'status',
        'amount',
        'collected_amount',
        'currency',
        'phone',
        'email',
        'description',
        'type',
        'payment_method',
        'payer_name',
        'customer_name',
        'sms_sent',
        'sms_message',
        'sms_sent_at',
        'sms_error',
        'callback_data',
        'callback_received_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_data' => 'array',
        'callback_received_at' => 'datetime'
    ];

    public function resolvedDescription($fallbackDescription = null)
    {
        return \App\Support\TransactionFieldResolver::resolveForTransaction($this, $fallbackDescription);
    }

    public function getResolvedDescriptionAttribute()
    {
        return $this->resolvedDescription();
    }
}
