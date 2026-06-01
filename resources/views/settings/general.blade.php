@extends('layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="space-y-6 animate-fade-in">
    <h2 class="text-xl font-bold text-primary-900 dark:text-white">General Settings</h2>

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
                        <span class="text-sm font-medium text-primary-700 dark:text-primary-300">Database Connection</span>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $systemHealth['database']['status'] === 'healthy' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ucfirst($systemHealth['database']['status']) }}
                    </span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-hdd text-xl {{ $systemHealth['cache']['status'] === 'healthy' ? 'text-green-500' : 'text-yellow-500' }}"></i>
                        <span class="text-sm font-medium text-primary-700 dark:text-primary-300">Cache</span>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $systemHealth['cache']['status'] === 'healthy' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($systemHealth['cache']['status']) }}
                    </span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-server text-xl {{ $systemHealth['disk_space']['status'] === 'healthy' ? 'text-green-500' : 'text-yellow-500' }}"></i>
                        <div>
                            <span class="text-sm font-medium text-primary-700 dark:text-primary-300">Disk Space</span>
                            <p class="text-xs text-primary-500">{{ $systemHealth['disk_space']['free_space'] ?? 'N/A' }} free of {{ $systemHealth['disk_space']['total_space'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded text-xs font-bold {{ $systemHealth['disk_space']['status'] === 'healthy' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($systemHealth['disk_space']['status']) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="card p-6">
            <h3 class="font-bold text-lg text-primary-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i> Active Users
            </h3>
            
            <div class="text-center p-6 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                <p class="text-4xl font-black text-primary-600 dark:text-primary-400">{{ $activeUsers }}</p>
                <p class="text-sm text-primary-500 mt-1">Registered system users</p>
            </div>
            
            <div class="mt-4">
                <a href="{{ route('users.index') }}" class="text-sm font-bold text-primary-600 hover:underline flex items-center gap-1">
                    <i class="fas fa-arrow-right"></i> Manage all users
                </a>
            </div>
        </div>
    </div>

    <!-- System Timeout Settings -->
    <div class="card p-6">
        <h3 class="font-bold text-lg text-primary-900 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-clock text-purple-500"></i> Timeout Settings
        </h3>
        
        <p class="text-sm text-primary-600 dark:text-primary-300 mb-4">
            <i class="fas fa-info-circle mr-2"></i> Timeout configuration will be available in a future update.
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-gray-50 dark:bg-gray-900/30 rounded-lg">
                <label class="text-xs font-bold uppercase text-gray-500 mb-1 block">Session Timeout</label>
                <p class="text-sm text-gray-700 dark:text-gray-300">120 minutes (default)</p>
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-900/30 rounded-lg">
                <label class="text-xs font-bold uppercase text-gray-500 mb-1 block">API Timeout</label>
                <p class="text-sm text-gray-700 dark:text-gray-300">30 seconds (default)</p>
            </div>
        </div>
    </div>
</div>
@endsection
