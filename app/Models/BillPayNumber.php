<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillPayNumber extends Model
{
    use HasFactory;

    protected $table = 'billpay_numbers';

    protected $fillable = [
        'bill_pay_number',
        'bill_description',
        'bill_amount',
        'bill_currency',
        'bill_payment_mode',
        'bill_status',
        'bill_type',
        'customer_name',
        'customer_email',
        'customer_phone',
        'bill_reference',
        'notes',
        'created_by',
        'last_payment_at',
        'total_paid',
    ];

    protected $casts = [
        'bill_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'last_payment_at' => 'datetime',
    ];
}
