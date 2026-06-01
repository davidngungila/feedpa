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
                        <span class="badge badge-{{ $smsEnabled ? 'green' : 'yellow' }} px-4 py-1.5 text-xs">
                            <i class="fas fa-{{ $smsEnabled ? 'check' : 'clock' }} me-2"></i>
                            {{ $smsEnabled ? 'SMS Enabled' : 'SMS Disabled' }}
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

    @if(session('error'))
        <div class="card p-6 border-red-100 bg-red-50 dark:bg-red-900/10">
            <div class="flex items-center gap-4 text-red-600 dark:text-red-400">
                <i class="fas fa-exclamation-circle text-2xl"></i>
                <div>
                    <h4 class="font-bold">Error</h4>
                    <p class="text-xs">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
        <!-- SMS Config Form -->
        <div class="card p-6 space-y-6">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-sliders-h"></i> SMS Configuration
            </h3>
            <form method="POST" action="{{ route('settings.sms.update') }}">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">SMS Base URL</div>
                            <input type="url" name="sms_base_url" required value="{{ old('sms_base_url', $smsBaseUrl) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://messaging-service.co.tz">
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">API Token</div>
                            <input type="text" name="sms_token" required value="{{ old('sms_token', $smsToken) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Your API Token">
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">API Key</div>
                            <input type="text" name="sms_api_key" required value="{{ old('sms_api_key', $smsApiKey) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Your API Key">
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Sender ID</div>
                            <input type="text" name="sms_sender_id" required value="{{ old('sms_sender_id', $smsSenderId) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="FEEDTAN">
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">API Timeout (seconds)</div>
                            <input type="number" name="sms_timeout" required value="{{ old('sms_timeout', $smsTimeout) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="30" min="5" max="300">
                        </div>
                        <div class="flex flex-col justify-center space-y-2">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="sms_enabled" id="sms_enabled" {{ $smsEnabled ? 'checked' : '' }} class="w-4 h-4 rounded">
                                <label for="sms_enabled" class="text-sm font-bold text-primary-700 dark:text-primary-300">Enable SMS Notifications</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="sms_test_mode" id="sms_test_mode" {{ $smsTestMode ? 'checked' : '' }} class="w-4 h-4 rounded">
                                <label for="sms_test_mode" class="text-sm font-bold text-primary-700 dark:text-primary-300">Test Mode (No Real SMS)</label>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Payment SMS Template</div>
                        <textarea name="sms_template_payment" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" rows="3" placeholder="Hello {customer_name}, your payment of {amount} TZS has been received. Reference: {reference}">{{ old('sms_template_payment', $smsTemplatePayment) }}</textarea>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20">
                        <i class="fas fa-save me-2"></i> Save SMS Settings
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Test SMS Form -->
        <div class="card p-6 space-y-6 border-dashed border-2 border-primary-200">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-paper-plane"></i> Test SMS
            </h3>
            <form method="POST" action="{{ route('settings.sms.test') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Phone Number</div>
                        <input type="text" name="test_phone" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="255655123456">
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Test Message</div>
                        <textarea name="test_message" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" rows="3" placeholder="Hello! This is a test SMS from FEEDTAN.">Hello! This is a test SMS from FEEDTAN.</textarea>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-secondary-600 to-secondary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-secondary-900/20">
                        <i class="fas fa-paper-plane me-2"></i> Send Test SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
