<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutOtp extends Model
{
    protected $fillable = [
        'payout_id',
        'user_id',
        'otp',
        'phone',
        'is_verified',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
