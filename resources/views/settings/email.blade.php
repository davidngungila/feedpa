@extends('layouts.app')

@section('title', 'Email Settings')

@section('content')
<div class="space-y-6 animate-fade-in">
    <h2 class="text-xl font-bold text-primary-900 dark:text-white">Email Settings</h2>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="card p-6">
        <form method="POST" action="{{ route('settings.email.update') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">Email Address</label>
                    <input type="email" name="email_address" required value="{{ old('email_address', $emailSettings->email_address ?? 'feedtan15@gmail.com') }}" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">Password (App Password)</label>
                    <input type="password" name="password" required value="{{ old('password', $emailSettings->password ?? '') }}" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">SMTP Host</label>
                    <input type="text" name="smtp_host" required value="{{ old('smtp_host', $emailSettings->smtp_host ?? 'smtp.gmail.com') }}" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">SMTP Port</label>
                    <input type="number" name="smtp_port" required value="{{ old('smtp_port', $emailSettings->smtp_port ?? 587) }}" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">Encryption</label>
                    <select name="encryption" class="form-input">
                        <option value="tls" {{ (old('encryption', $emailSettings->encryption ?? 'tls') === 'tls') ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ (old('encryption', $emailSettings->encryption ?? 'tls') === 'ssl') ? 'selected' : '' }}>SSL</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">From Name</label>
                    <input type="text" name="from_name" required value="{{ old('from_name', $emailSettings->from_name ?? 'FeedTan Community Microfinance Group') }}" class="form-input">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">From Address</label>
                    <input type="email" name="from_address" required value="{{ old('from_address', $emailSettings->from_address ?? 'feedtan15@gmail.com') }}" class="form-input">
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
