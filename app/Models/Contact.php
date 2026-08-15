<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'company',
        'notes',
    ];

    protected $casts = [
        'phone' => 'string',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(WhatsAppGroup::class, 'contact_group', 'contact_id', 'group_id');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('company', 'like', "%{$term}%");
    }
}