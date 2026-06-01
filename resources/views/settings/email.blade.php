@extends('layouts.app')

@section('title', 'Email Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Status Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-6">
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    <i class="fas fa-envelope text-4xl text-primary-600"></i>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">System Configuration</div>
                    <div class="text-xl font-bold text-primary-900 dark:text-white">Email Settings</div>
                    <div class="mt-2">
                        <span class="badge badge-green px-4 py-1.5 text-xs">
                            <i class="fas fa-check-circle me-2"></i>
                            Settings Active
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
    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
        <!-- Email Config Form -->
        <div class="card p-6 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-sliders-h"></i> Email Configuration
            </h3>
            <form method="POST" action="{{ route('settings.email.update') }}">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Email Address</div>
                            <input type="email" name="email_address" required value="{{ old('email_address', $emailSettings->email_address ?? 'feedtan15@gmail.com') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Password (App Password)</div>
                            <input type="password" name="password" required value="{{ old('password', $emailSettings->password ?? '') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">SMTP Host</div>
                            <input type="text" name="smtp_host" required value="{{ old('smtp_host', $emailSettings->smtp_host ?? 'smtp.gmail.com') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">SMTP Port</div>
                            <input type="number" name="smtp_port" required value="{{ old('smtp_port', $emailSettings->smtp_port ?? 587) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Encryption</div>
                            <select name="encryption" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="tls" {{ (old('encryption', $emailSettings->encryption ?? 'tls') === 'tls') ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ (old('encryption', $emailSettings->encryption ?? 'tls') === 'ssl') ? 'selected' : '' }}>SSL</option>
                            </select>
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">From Name</div>
                            <input type="text" name="from_name" required value="{{ old('from_name', $emailSettings->from_name ?? 'FEEDTAN DIGITAL') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">From Address</div>
                        <input type="email" name="from_address" required value="{{ old('from_address', $emailSettings->from_address ?? 'feedtan15@gmail.com') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20">
                        <i class="fas fa-save me-2"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
