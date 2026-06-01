@extends('layouts.app')

@section('title', 'SMS Settings')

@section('content')
<div class="space-y-6 animate-fade-in">
    <h2 class="text-xl font-bold text-primary-900 dark:text-white">SMS Settings</h2>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="card p-6">
        <form method="POST" action="{{ route('settings.sms.update') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">SMS Provider</label>
                    <input type="text" name="sms_provider" value="{{ old('sms_provider', $settings['sms_provider']->value ?? '') }}" class="form-input" placeholder="e.g. Twilio, Nexmo">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">API Key</label>
                    <input type="text" name="sms_api_key" value="{{ old('sms_api_key', $settings['sms_api_key']->value ?? '') }}" class="form-input" placeholder="Your SMS API key">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">API Secret</label>
                    <input type="password" name="sms_api_secret" value="{{ old('sms_api_secret', $settings['sms_api_secret']->value ?? '') }}" class="form-input" placeholder="Your SMS API secret">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">Sender ID</label>
                    <input type="text" name="sms_sender_id" value="{{ old('sms_sender_id', $settings['sms_sender_id']->value ?? '') }}" class="form-input" placeholder="FEEDTANCMG">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="sms_enabled" id="sms_enabled" {{ (isset($settings['sms_enabled']) && SystemSetting::get('sms_enabled', false)) ? 'checked' : '' }} class="w-4 h-4 rounded">
                    <label for="sms_enabled" class="text-sm font-bold text-primary-700 dark:text-primary-300">Enable SMS Notifications</label>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold mb-1 text-primary-700 dark:text-primary-300">Payment SMS Template</label>
                    <textarea name="sms_template_payment" class="form-input" rows="3" placeholder="Hello {customer_name}, your payment of {amount} TZS has been received. Reference: {reference}">{{ old('sms_template_payment', $settings['sms_template_payment']->value ?? '') }}"></textarea>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all">
                    Save SMS Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
