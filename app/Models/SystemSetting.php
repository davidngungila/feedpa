<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key', 'value', 'type', 'group', 'label', 'description'
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::find($key);
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'number' => (float)$setting->value,
            'integer' => (int)$setting->value,
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general', ?string $label = null, ?string $description = null): self
    {
        $value = match ($type) {
            'boolean' => $value ? '1' : '0',
            default => (string)$value,
        };

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'label' => $label,
                'description' => $description
            ]
        );
    }
}
