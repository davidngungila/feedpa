<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model
{
    protected $fillable = [
        'currency',
        'balance',
        'synced_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'synced_at' => 'datetime',
    ];
}
