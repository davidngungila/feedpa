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
            [
                'key' => 'sms_base_url',
                'value' => 'https://messaging-service.co.tz',
                'type' => 'string',
                'group' => 'sms',
                'label' => 'SMS Base URL',
                'description' => 'Base URL for SMS API',
            ],
            [
                'key' => 'sms_token',
                'value' => 'f9a89f439206e27169ead766463ca92c',
                'type' => 'string',
                'group' => 'sms',
                'label' => 'API Token',
                'description' => 'API token for SMS provider',
            ],
            [
                'key' => 'sms_api_key',
                'value' => 'f9a89f439206e27169ead766463ca92c',
                'type' => 'string',
                'group' => 'sms',
                'label' => 'API Key',
                'description' => 'API key for SMS provider',
            ],
            [
                'key' => 'sms_sender_id',
                'value' => 'FEEDTAFDFDFDN CMG',
                'type' => 'string',
                'group' => 'sms',
                'label' => 'Sender ID',
                'description' => 'Sender ID to use for SMS',
            ],
            [
                'key' => 'sms_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'sms',
                'label' => 'Enable SMS Notifications',
                'description' => 'Whether to send SMS notifications',
            ],
            [
                'key' => 'sms_timeout',
                'value' => '30',
                'type' => 'integer',
                'group' => 'sms',
                'label' => 'API Timeout (seconds)',
                'description' => 'API request timeout',
            ],
            [
                'key' => 'sms_test_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'sms',
                'label' => 'Test Mode',
                'description' => 'Enable test mode (no real SMS sent)',
            ],
            [
                'key' => 'sms_template_payment',
                'value' => '',
                'type' => 'string',
                'group' => 'sms',
                'label' => 'Payment SMS Template',
                'description' => 'Template for payment confirmation SMS',
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
