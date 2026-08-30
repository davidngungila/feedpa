<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppSendJob extends Model
{
    protected $table = 'whatsapp_send_jobs';

    protected $fillable = [
        'batch_id',
        'recipient_type',
        'recipient',
        'recipient_name',
        'payload',
        'status',
        'message',
        'result',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'processed_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(WhatsAppSendBatch::class, 'batch_id');
    }
}