<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmailCredential;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->is_admin) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    public function sms()
    {
        return view('settings.sms');
    }

    public function email()
    {
        $emailSettings = EmailCredential::where('is_active', true)->first();
        return view('settings.email', compact('emailSettings'));
    }

    public function general()
    {
        $activeUsers = User::whereNotNull('email')->count();
        $systemHealth = $this->checkSystemHealth();
        return view('settings.general', compact('activeUsers', 'systemHealth'));
    }

    public function updateEmail(Request $request)
    {
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

        return back()->with('success', 'Email settings updated successfully!');
    }

    private function checkSystemHealth()
    {
        return [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'disk_space' => $this->checkDiskSpace(),
        ];
    }

    private function checkDatabase()
    {
        try {
            \DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Connection successful'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    private function checkCache()
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

    private function checkDiskSpace()
    {
        try {
            $freeSpace = disk_free_space('/');
            $totalSpace = disk_total_space('/');
            $usedPercent = round((1 - ($freeSpace / $totalSpace)) * 100, 2);
            return [
                'status' => $usedPercent < 90 ? 'healthy' : 'warning',
                'message' => "Used: {$usedPercent}%",
                'free_space' => $this->formatBytes($freeSpace),
                'total_space' => $this->formatBytes($totalSpace),
            ];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }

    private function formatBytes($size, $precision = 2)
    {
        if ($size === 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($size, 1024);
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }
}
