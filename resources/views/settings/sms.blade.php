@extends('layouts.app')

@section('title', 'SMS Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Status Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-6">
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    <i class="fas fa-sms text-4xl text-primary-600"></i>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">System Configuration</div>
                    <div class="text-xl font-bold text-primary-900 dark:text-white">SMS Settings</div>
                    <div class="mt-2">
                        <span class="badge badge-yellow px-4 py-1.5 text-xs">
                            <i class="fas fa-clock me-2"></i>
                            Coming Soon
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
        <!-- SMS Config Form -->
        <div class="card p-6 space-y-4">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-sliders-h"></i> SMS Configuration
            </h3>
            <form method="POST" action="{{ route('settings.sms.update') }}">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">SMS Provider</div>
                            <input type="text" name="sms_provider" value="{{ old('sms_provider', $settings['sms_provider']->value ?? '') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="e.g., Twilio, Nexmo">
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">API Key</div>
                            <input type="text" name="sms_api_key" value="{{ old('sms_api_key', $settings['sms_api_key']->value ?? '') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Your API Key">
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">API Secret</div>
                            <input type="password" name="sms_api_secret" value="{{ old('sms_api_secret', $settings['sms_api_secret']->value ?? '') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Your API Secret">
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Sender ID</div>
                            <input type="text" name="sms_sender_id" value="{{ old('sms_sender_id', $settings['sms_sender_id']->value ?? '') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="FEEDTANCMG">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="sms_enabled" id="sms_enabled" {{ (isset($settings['sms_enabled']) && SystemSetting::get('sms_enabled', false)) ? 'checked' : '' }} class="w-4 h-4 rounded">
                            <label for="sms_enabled" class="text-sm font-bold text-primary-700 dark:text-primary-300">Enable SMS Notifications</label>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Payment SMS Template</div>
                        <textarea name="sms_template_payment" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" rows="3" placeholder="Hello {customer_name}, your payment of {amount} TZS has been received. Reference: {reference}">{{ old('sms_template_payment', $settings['sms_template_payment']->value ?? '') }}</textarea>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20">
                        <i class="fas fa-save me-2"></i> Save SMS Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
