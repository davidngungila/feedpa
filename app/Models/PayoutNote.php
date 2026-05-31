<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutNote extends Model
{
    protected $fillable = ['payout_id', 'user_id', 'content'];

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
