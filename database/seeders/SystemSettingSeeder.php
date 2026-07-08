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
                'key' => 'gemini_api_key',
                'value' => '',
                'type' => 'string',
                'group' => 'ai',
                'label' => 'Gemini API Key',
                'description' => 'API key for Google Gemini AI',
            ],
            [
                'key' => 'gemini_project_name',
                'value' => '',
                'type' => 'string',
                'group' => 'ai',
                'label' => 'Gemini Project Name',
                'description' => 'Project name for Google Gemini API',
            ],
            [
                'key' => 'gemini_project_number',
                'value' => '',
                'type' => 'number',
                'group' => 'ai',
                'label' => 'Gemini Project Number',
                'description' => 'Project number for Google Gemini API',
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
