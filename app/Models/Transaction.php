<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

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
        'akiba_type',
        'uwekezaji_type',
        'type',
        'payment_method',
        'payer_name',
        'customer_name',
        'sms_sent',
        'sms_message',
        'sms_sent_at',
        'sms_error',
        'email_sent',
        'email_message',
        'email_sent_at',
        'email_error',
        'callback_data',
        'callback_received_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->id)) {
                $transaction->id = (string) Str::uuid();
            }
        });
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'callback_data' => 'array',
        'callback_received_at' => 'datetime',
        'sms_sent' => 'boolean',
        'sms_sent_at' => 'datetime',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
    ];

    public function resolvedDescription($fallbackDescription = null)
    {
        return \App\Support\TransactionFieldResolver::resolveForTransaction($this, $fallbackDescription);
    }

    public function getResolvedDescriptionAttribute()
    {
        return $this->resolvedDescription();
    }

    public function notes()
    {
        return $this->hasMany(TransactionNote::class)->latest();
    }
}
