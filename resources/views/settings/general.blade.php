@extends('layouts.app')

@section('title', 'General Settings')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
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
                        <input type="text" name="site_name" value="{{ old('site_name', $siteName) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="FEEDTAN DIGITAL">
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Session Timeout (minutes)</div>
                        <input type="number" name="session_timeout" value="{{ old('session_timeout', $sessionTimeout) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="120" step="any" min="0.1" max="1440">
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">API Timeout (seconds)</div>
                        <input type="number" name="api_timeout" value="{{ old('api_timeout', $apiTimeout) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="30" min="5" max="300">
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Site Description</div>
                        <textarea name="site_description" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" rows="2" placeholder="Short description of your site">{{ old('site_description', $siteDescription) }}</textarea>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="payment_notifications_enabled" id="payment_notifications_enabled" {{ $paymentNotificationsEnabled ? 'checked' : '' }} class="w-4 h-4 rounded">
                        <label for="payment_notifications_enabled" class="text-sm font-bold text-primary-700 dark:text-primary-300">Enable Payment Notifications</label>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="payout_notifications_enabled" id="payout_notifications_enabled" {{ $payoutNotificationsEnabled ? 'checked' : '' }} class="w-4 h-4 rounded">
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

    <!-- Users Management -->
    <div class="card p-6 space-y-4">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
            <i class="fas fa-users-cog"></i> System Users
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Position</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($users as $user)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-primary-900 dark:text-white">{{ $user->name }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->email }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->phone ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->position ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="px-2 py-1 rounded text-xs font-bold {{ $user->is_locked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $user->is_locked ? 'Locked' : 'Active' }}
                                </span>
                                @if($user->is_admin)
                                    <span class="ml-1 px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-700">
                                        Admin
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <form method="POST" action="{{ route('settings.users.toggle-lock', $user) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-lg text-xs font-bold {{ $user->is_locked ? 'bg-green-500 hover:bg-green-600 text-white' : 'bg-yellow-500 hover:bg-yellow-600 text-white' }}">
                                        {{ $user->is_locked ? 'Unlock' : 'Lock' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('settings.users.delete', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 rounded-lg text-xs font-bold bg-red-500 hover:bg-red-600 text-white">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Session Timeout Notification Script -->
<script>
    let sessionTimeout = {{ $sessionTimeout * 60 * 1000 }}; // convert to ms
    let warningTime = sessionTimeout / 3; // 1/3 of time
    let lastActivity = Date.now();

    function resetTimer() {
        lastActivity = Date.now();
    }

    // Listen for user activity to reset timer
    document.addEventListener('mousemove', resetTimer);
    document.addEventListener('keypress', resetTimer);
    document.addEventListener('click', resetTimer);
    document.addEventListener('scroll', resetTimer);

    // Check every second
    setInterval(() => {
        const now = Date.now();
        const timeSinceActivity = now - lastActivity;
        
        if (timeSinceActivity >= sessionTimeout) {
            // Auto logout
            window.location.href = '{{ route('logout') }}';
        } else if (timeSinceActivity >= warningTime && timeSinceActivity < warningTime + 1000) {
            // Show warning
            const warning = document.createElement('div');
            warning.className = 'fixed top-4 right-4 z-50 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-xl shadow-lg animate-pulse';
            warning.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                    <div>
                        <p class="font-bold">Session Warning!</p>
                        <p class="text-sm">Your session will expire soon. Move your mouse or press any key to stay logged in.</p>
                    </div>
                </div>
            `;
            document.body.appendChild(warning);
            
            // Remove warning after 5 seconds or on activity
            setTimeout(() => warning.remove(), 5000);
            document.addEventListener('mousemove', () => warning.remove(), { once: true });
            document.addEventListener('keypress', () => warning.remove(), { once: true });
        }
    }, 1000);
</script>
@endsection
