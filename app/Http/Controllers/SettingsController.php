<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailCredential;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{


    private function checkAdmin()
    {
        if (!auth()->check()) {
            return redirect('/');
        }
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
    }

    public function sms()
    {
        $this->checkAdmin();
        $settings = SystemSetting::where('group', 'sms')->get()->keyBy('key');
        $smsBaseUrl = SystemSetting::get('sms_base_url', 'https://messaging-service.co.tz');
        $smsToken = SystemSetting::get('sms_token', 'f9a89f439206e27169ead766463ca92c');
        $smsApiKey = SystemSetting::get('sms_api_key', 'f9a89f439206e27169ead766463ca92c');
        $smsSenderId = SystemSetting::get('sms_sender_id', 'FEEDTAN');
        $smsEnabled = SystemSetting::get('sms_enabled', true);
        $smsTemplatePayment = SystemSetting::get('sms_template_payment', '');
        $smsTimeout = SystemSetting::get('sms_timeout', 30);
        $smsTestMode = SystemSetting::get('sms_test_mode', false);
        
        return view('settings.sms', compact(
            'settings',
            'smsBaseUrl',
            'smsToken',
            'smsApiKey',
            'smsSenderId',
            'smsEnabled',
            'smsTemplatePayment',
            'smsTimeout',
            'smsTestMode'
        ));
    }

    public function updateSms(Request $request)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'sms_base_url' => 'required|url',
            'sms_token' => 'required|string',
            'sms_api_key' => 'required|string',
            'sms_sender_id' => 'required|string',
            'sms_enabled' => 'nullable',
            'sms_timeout' => 'required|integer|min:5|max:300',
            'sms_test_mode' => 'nullable',
            'sms_template_payment' => 'nullable|string',
        ]);

        SystemSetting::set('sms_base_url', $validated['sms_base_url'], 'string', 'sms', 'SMS Base URL', 'Base URL for SMS API');
        SystemSetting::set('sms_token', $validated['sms_token'], 'string', 'sms', 'API Token', 'API token for SMS provider');
        SystemSetting::set('sms_api_key', $validated['sms_api_key'], 'string', 'sms', 'API Key', 'API key for SMS provider');
        SystemSetting::set('sms_sender_id', $validated['sms_sender_id'], 'string', 'sms', 'Sender ID', 'Sender ID to use for SMS');
        SystemSetting::set('sms_enabled', $request->has('sms_enabled'), 'boolean', 'sms', 'Enable SMS Notifications', 'Whether to send SMS notifications');
        SystemSetting::set('sms_timeout', $validated['sms_timeout'], 'integer', 'sms', 'API Timeout (seconds)', 'API request timeout');
        SystemSetting::set('sms_test_mode', $request->has('sms_test_mode'), 'boolean', 'sms', 'Test Mode', 'Enable test mode (no real SMS sent)');
        SystemSetting::set('sms_template_payment', $validated['sms_template_payment'] ?? '', 'string', 'sms', 'Payment SMS Template', 'Template for payment confirmation SMS');

        return back()->with('success', 'SMS Settings updated successfully!');
    }
    
    public function testSms(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'test_phone' => 'required|string',
            'test_message' => 'required|string|max:1600',
        ]);
        
        try {
            $messagingService = new \App\Services\MessagingServiceAPI();
            $result = $messagingService->testSMS($request->test_phone, $request->test_message);
            
            return back()->with('success', 'Test SMS sent successfully! Response: ' . json_encode($result));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test SMS: ' . $e->getMessage());
        }
    }

    public function email()
    {
        $this->checkAdmin();
        $emailSettings = EmailCredential::where('is_active', true)->first();
        return view('settings.email', compact('emailSettings'));
    }

    public function updateEmail(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'email_address' => 'required|email',
            'password' => 'required|string',
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'encryption' => 'required|string',
            'from_name' => 'required|string',
            'from_address' => 'required|email',
        ]);

        // Deactivate existing
        EmailCredential::where('is_active', true)->update(['is_active' => false]);

        // Create new
        EmailCredential::create([
            'email_address' => $request->email_address,
            'password' => $request->password,
            'smtp_host' => $request->smtp_host,
            'smtp_port' => $request->smtp_port,
            'encryption' => $request->encryption,
            'from_name' => $request->from_name,
            'from_address' => $request->from_address,
            'mailer' => 'smtp',
            'is_active' => true,
        ]);

        return back()->with('success', 'Email Settings updated successfully!');
    }

    public function general()
    {
        $this->checkAdmin();
        $activeUsers = User::whereNotNull('email')->count();
        $systemHealth = $this->checkSystemHealth();
        $settings = SystemSetting::where('group', 'general')->get()->keyBy('key');
        
        $siteName = SystemSetting::get('site_name', 'FEEDTAN DIGITAL');
        $sessionTimeout = SystemSetting::get('session_timeout', 120);
        $apiTimeout = SystemSetting::get('api_timeout', 30);
        $siteDescription = SystemSetting::get('site_description', '');
        $paymentNotificationsEnabled = SystemSetting::get('payment_notifications_enabled', true);
        $payoutNotificationsEnabled = SystemSetting::get('payout_notifications_enabled', true);
        
        $users = User::all();
        
        return view('settings.general', compact(
            'activeUsers',
            'systemHealth',
            'settings',
            'siteName',
            'sessionTimeout',
            'apiTimeout',
            'siteDescription',
            'paymentNotificationsEnabled',
            'payoutNotificationsEnabled',
            'users'
        ));
    }
    
    public function toggleUserLock(User $user)
    {
        $this->checkAdmin();
        $user->update(['is_locked' => !$user->is_locked]);
        return back()->with('success', 'User ' . ($user->is_locked ? 'locked' : 'unlocked') . ' successfully!');
    }
    
    public function deleteUser(User $user)
    {
        $this->checkAdmin();
        $user->delete();
        return back()->with('success', 'User deleted successfully!');
    }

    public function updateGeneral(Request $request)
    {
        $this->checkAdmin();
        $validated = $request->validate([
            'session_timeout' => 'nullable|numeric|min:0.1|max:1440',
            'api_timeout' => 'nullable|integer|min:1|max:300',
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'payment_notifications_enabled' => 'nullable',
            'payout_notifications_enabled' => 'nullable',
        ]);

        SystemSetting::set('session_timeout', $validated['session_timeout'] ?? 120, 'number', 'general', 'Session Timeout (minutes)', 'How long until session expires');
        SystemSetting::set('api_timeout', $validated['api_timeout'] ?? 30, 'integer', 'general', 'API Timeout (seconds)', 'API request timeout');
        SystemSetting::set('site_name', $validated['site_name'] ?? 'FEEDTAN CMG', 'string', 'general', 'Site Name', 'Name of the website');
        SystemSetting::set('site_description', $validated['site_description'] ?? '', 'string', 'general', 'Site Description', 'Short description of the site');
        SystemSetting::set('payment_notifications_enabled', $request->has('payment_notifications_enabled'), 'boolean', 'general', 'Payment Notifications', 'Email officers when payment is made');
        SystemSetting::set('payout_notifications_enabled', $request->has('payout_notifications_enabled'), 'boolean', 'general', 'Payout Notifications', 'Email officers when payout is made');

        return back()->with('success', 'General Settings updated successfully!');
    }

    private function checkSystemHealth(): array
    {
        return [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'disk_space' => $this->checkDiskSpace(),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            Cache::put('health_check', 'ok', 10);
            if (Cache::get('health_check') === 'ok') {
                return ['status' => 'healthy', 'message' => 'Cache is working'];
            }
            return ['status' => 'unhealthy', 'message' => 'Cache not working'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    private function checkDiskSpace(): array
    {
        try {
            $freeSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usedPercent = round((1 - ($freeSpace / $totalSpace)) * 100, 2);
            return [
                'status' => $usedPercent < 90 ? 'healthy' : ($usedPercent < 95 ? 'warning' : 'critical'),
                'message' => "Used: {$usedPercent}%",
                'free_space' => $this->formatBytes($freeSpace),
                'total_space' => $this->formatBytes($totalSpace),
            ];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    private function formatBytes($size, $precision = 2): string
    {
        if ($size === 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($size, 1024);
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }
}
