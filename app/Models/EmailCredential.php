<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCredential extends Model
{
    protected $fillable = [
        'email_address',
        'password',
        'smtp_host',
        'smtp_port',
        'encryption',
        'from_name',
        'from_address',
        'mailer',
        'is_active'
    ];
}
