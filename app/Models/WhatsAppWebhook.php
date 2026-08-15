<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppWebhook extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_webhooks';

    protected $fillable = [
        'name',
        'url',
        'token',
        'events',
        'secret',
        'is_active',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];
}