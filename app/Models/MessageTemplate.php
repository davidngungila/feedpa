<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $table = 'message_templates';

    protected $fillable = [
        'name',
        'content',
        'variables',
        'category',
    ];

    protected $casts = [
        'variables' => 'array',
    ];
}