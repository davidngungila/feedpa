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
                'key' => 'groq_api_key',
                'value' => '',
                'type' => 'string',
                'group' => 'ai',
                'label' => 'Groq API Key',
                'description' => 'API key for Groq AI',
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
