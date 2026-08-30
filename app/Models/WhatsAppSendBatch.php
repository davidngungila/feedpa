<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppSendBatch extends Model
{
    protected $table = 'whatsapp_send_batches';

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'sent',
        'failed',
        'next_available_at',
    ];

    protected $casts = [
        'next_available_at' => 'datetime',
    ];

    public function jobs()
    {
        return $this->hasMany(WhatsAppSendJob::class, 'batch_id');
    }

    public function pendingJobs()
    {
        return $this->jobs()->where('status', 'pending');
    }
}