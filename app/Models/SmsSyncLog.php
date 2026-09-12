<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsSyncLog extends Model
{
    protected $table = 'sms_sync_logs';
    protected $fillable = ['sms_message_id','device_id','action','request_payload','response_payload','status'];
    protected $casts = ['request_payload'=>'array','response_payload'=>'array'];
}
