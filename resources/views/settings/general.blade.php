@extends('layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Status Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-6">
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    <i class="fas fa-cog text-4xl text-primary-600"></i>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">System Configuration</div>
                    <div class="text-xl font-bold text-primary-900 dark:text-white">General Settings</div>
                    <div class="mt-2">
                        <span class="badge badge-green px-4 py-1.5 text-xs">
                            <i class="fas fa-check-circle me-2"></i>
                            System Active
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="card p-6 border-green-100 bg-green-50 dark:bg-green-900/10">
            <div class="flex items-center gap-4 text-green-600 dark:text-green-400">
                <i class="fas fa-check-circle text-2xl"></i>
                <div>
                    <h4 class="font-bold">Success</h4>
                    <p class="text-xs">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- System Health -->
        <div class="card p-6 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-heartbeat"></i> System Health
            </h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-database text-xl {{ $systemHealth['database']['status'] === 'healthy' ? 'text-green-500' : 'text-red-500' }}"></i>
                        <div>
                            <div class="text-sm font-bold text-primary-900 dark:text-white">Database Connection</div>
                            <p class="text-xs text-primary-500">{{ $systemHealth['database']['message'] }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $systemHealth['database']['status'] === 'healthy' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($systemHealth['database']['status']) }}
                    </span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-hdd text-xl {{ $systemHealth['cache']['status'] === 'healthy' ? 'text-green-500' : 'text-yellow-500' }}"></i>
                        <div>
                            <div class="text-sm font-bold text-primary-900 dark:text-white">Cache</div>
                            <p class="text-xs text-primary-500">{{ $systemHealth['cache']['message'] }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $systemHealth['cache']['status'] === 'healthy' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($systemHealth['cache']['status']) }}
                    </span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-server text-xl {{ $systemHealth['disk_space']['status'] === 'healthy' ? 'text-green-500' : ($systemHealth['disk_space']['status'] === 'warning' ? 'text-yellow-500' : 'text-red-500') }}"></i>
                        <div>
                            <div class="text-sm font-bold text-primary-900 dark:text-white">Disk Space</div>
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
        <div class="card p-6 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-users"></i> Quick Stats
            </h3>
            
            <div class="text-center p-6 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                <div class="text-4xl font-black text-primary-600 dark:text-primary-400">{{ $activeUsers }}</div>
                <div class="text-xs text-primary-500 mt-1">Registered System Users</div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('users.index') }}" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1">
                    <i class="fas fa-arrow-right"></i> Manage All Users
                </a>
            </div>
        </div>
    </div>

    <!-- System Configuration -->
    <div class="card p-6 space-y-4">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
            <i class="fas fa-sliders-h"></i> System Configuration
        </h3>
        
        <form method="POST" action="{{ route('settings.general.update') }}">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Site Name</div>
                        <input type="text" name="site_name" value="{{ old('site_name', SystemSetting::get('site_name', 'FEEDTAN DIGITAL')) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="FEEDTAN DIGITAL">
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Session Timeout (minutes)</div>
                        <input type="number" name="session_timeout" value="{{ old('session_timeout', SystemSetting::get('session_timeout', 120)) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="120" min="5" max="1440">
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">API Timeout (seconds)</div>
                        <input type="number" name="api_timeout" value="{{ old('api_timeout', SystemSetting::get('api_timeout', 30)) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="30" min="5" max="300">
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Site Description</div>
                        <textarea name="site_description" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" rows="2" placeholder="Short description of your site">{{ old('site_description', SystemSetting::get('site_description', '')) }}</textarea>
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
            </div>
            
            <div class="mt-6">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20">
                    <i class="fas fa-save me-2"></i> Save General Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
