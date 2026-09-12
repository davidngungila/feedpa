<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsParsingRule extends Model
{
    protected $table = 'sms_parsing_rules';
    protected $fillable = ['provider_id', 'name', 'field', 'pattern', 'pattern_type', 'priority', 'is_active', 'meta'];
    protected $casts = ['is_active' => 'boolean', 'meta' => 'array'];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(SmsProvider::class, 'provider_id');
    }
}
