<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'openrouter_api_key',
                'value' => '',
                'type' => 'string',
                'group' => 'ai',
                'label' => 'OpenRouter API Key',
                'description' => 'API key for OpenRouter AI',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::set(
                $setting['key'],
                $setting['value'],
                $setting['type'],
                $setting['group'],
                $setting['label'],
                $setting['description']
            );
        }
    }
}
