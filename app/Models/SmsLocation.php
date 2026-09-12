<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsLocation extends Model
{
    protected $table = 'sms_locations';
    protected $fillable = ['name', 'code', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function devices(): HasMany
    {
        return $this->hasMany(SmsDevice::class, 'location_id');
    }
}
