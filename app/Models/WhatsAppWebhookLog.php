<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppWebhookLog extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_webhook_logs';

    protected $fillable = [
        'source',
        'event',
        'payload',
        'headers',
    ];

    protected $casts = [
        'payload' => 'array',
        'headers' => 'array',
    ];
}
