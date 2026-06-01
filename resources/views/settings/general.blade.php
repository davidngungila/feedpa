@extends('layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="space-y-6 animate-fade-in">
    <h2 class="text-xl font-bold text-primary-900 dark:text-white">General Settings</h2>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Health -->
        <div class="card p-6">
            <h3 class="font-bold text-lg text-primary-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-heartbeat text-red-500"></i> System Health
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-database text-xl {{ $systemHealth['database']['status'] === 'healthy' ? 'text-green-500' : 'text-red-500' }}"></i>
                        <div>
                            <span class="text-sm font-medium text-primary-700 dark:text-primary-300">Database Connection</span>
                            <p class="text-xs text-primary-500">{{ $systemHealth['database']['message'] }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $systemHealth['database']['status'] === 'healthy' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($systemHealth['database']['status']) }}
                    </span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-hdd text-xl {{ $systemHealth['cache']['status'] === 'healthy' ? 'text-green-500' : 'text-yellow-500' }}"></i>
                        <div>
                            <span class="text-sm font-medium text-primary-700 dark:text-primary-300">Cache</span>
                            <p class="text-xs text-primary-500">{{ $systemHealth['cache']['message'] }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $systemHealth['cache']['status'] === 'healthy' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($systemHealth['cache']['status']) }}
                    </span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-server text-xl {{ $systemHealth['disk_space']['status'] === 'healthy' ? 'text-green-500' : ($systemHealth['disk_space']['status'] === 'warning' ? 'text-yellow-500' : 'text-red-500') }}"></i>
                        <div>
                            <span class="text-sm font-medium text-primary-700 dark:text-primary-300">Disk Space</span>
                            <p class="text-xs text-primary-500">
                                {{ $systemHealth['disk_space']['free_space'] ?? 'N/A' }} free of {{ $systemHealth['disk_space']['total_space'] ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $systemHealth['disk_space']['status'] === 'healthy' ? 'bg-green-100 text-green-700' : ($systemHealth['disk_space']['status'] === 'warning' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                        {{ ucfirst($systemHealth['disk_space']['status']) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Active Users & Quick Stats -->
        <div class="card p-6">
            <h3 class="font-bold text-lg text-primary-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i> Quick Stats
            </h3>
            
            <div class="text-center p-6 bg-primary-50 dark:bg-primary-900/20 rounded-lg mb-4">
                <p class="text-4xl font-black text-primary-600 dark:text-primary-400">{{ $activeUsers }}</p>
                <p class="text-sm text-primary-500 mt-1">Registered System Users</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('users.index') }}" class="text-sm font-bold text-primary-600 hover:underline flex items-center gap-1">
                    <i class="fas fa-arrow-right"></i> Manage All Users
                </a>
            </div>
        </div>
    </div>

    <!-- System Configuration -->
    <div class="card p-6">
        <h3 class="font-bold text-lg text-primary-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-sliders text-purple-500"></i> System Configuration
        </h3>
        
        <form method="POST" action="{{ route('settings.general.update') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">Site Name</label>
                    <input type="text" name="site_name" value="{{ old('site_name', SystemSetting::get('site_name', 'FEEDTAN CMG')) }}" class="form-input" placeholder="FEEDTAN CMG">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">Session Timeout (minutes)</label>
                    <input type="number" name="session_timeout" value="{{ old('session_timeout', SystemSetting::get('session_timeout', 120)) }}" class="form-input" placeholder="120" min="5" max="1440">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">API Timeout (seconds)</label>
                    <input type="number" name="api_timeout" value="{{ old('api_timeout', SystemSetting::get('api_timeout', 30)) }}" class="form-input" placeholder="30" min="5" max="300">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">Site Description</label>
                    <textarea name="site_description" class="form-input" rows="2" placeholder="Short description of your site">{{ old('site_description', SystemSetting::get('site_description', '')) }}</textarea>
                </div>
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="payment_notifications_enabled" id="payment_notifications_enabled" {{ SystemSetting::get('payment_notifications_enabled', true) ? 'checked' : '' }} class="w-4 h-4 rounded">
                    <label for="payment_notifications_enabled" class="text-sm font-bold text-primary-700 dark:text-primary-300">Enable Payment Notifications</label>
                </div>
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="payout_notifications_enabled" id="payout_notifications_enabled" {{ SystemSetting::get('payout_notifications_enabled', true) ? 'checked' : '' }} class="w-4 h-4 rounded">
                    <label for="payout_notifications_enabled" class="text-sm font-bold text-primary-700 dark:text-primary-300">Enable Payout Notifications</label>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                    Save General Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
