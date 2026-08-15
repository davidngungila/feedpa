<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppMedia extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_media';

    protected $fillable = [
        'name',
        'type',
        'mime_type',
        'size',
        'url',
        'wasender_id',
    ];

    protected $casts = [
        'size' => 'integer',
    ];
}