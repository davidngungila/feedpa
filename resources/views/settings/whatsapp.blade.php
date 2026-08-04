@extends('layouts.app')

@section('title', 'WhatsApp Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Status Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-6">
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    <i class="fab fa-whatsapp text-4xl text-green-600"></i>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">System Configuration</div>
                    <div class="text-xl font-bold text-primary-900 dark:text-white">WhatsApp Settings</div>
                    <div class="mt-2">
                        <span class="badge badge-{{ $settings['whatsapp_enabled'] ? 'green' : 'yellow' }} px-4 py-1.5 text-xs">
                            <i class="fas fa-{{ $settings['whatsapp_enabled'] ? 'check' : 'clock' }} me-2"></i>
                            {{ $settings['whatsapp_enabled'] ? 'WhatsApp Enabled' : 'WhatsApp Disabled' }}
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

    <!-- Session Info Card -->
    @if($sessionInfo)
        <div class="card p-6 border-blue-100 bg-blue-50 dark:bg-blue-900/10">
            <div class="flex items-center gap-4 text-blue-600 dark:text-blue-400">
                <i class="fas fa-info-circle text-2xl"></i>
                <div>
                    <h4 class="font-bold">Session Connected</h4>
                    <p class="text-xs">WhatsApp session is active and connected.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
        <!-- WhatsApp Config Form -->
        <div class="card p-6 space-y-6">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-sliders-h"></i> WhatsApp Configuration
            </h3>
            <form method="POST" action="{{ route('settings.whatsapp.update') }}">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">WhatsApp Base URL</div>
                            <input type="url" name="whatsapp_base_url" value="{{ old('whatsapp_base_url', $settings['whatsapp_base_url']) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://www.wasenderapi.com/api">
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Session API Key</div>
                            <input type="text" name="whatsapp_session_api_key" value="{{ old('whatsapp_session_api_key', $settings['whatsapp_session_api_key']) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Your Session API Key from Wasender">
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Personal Access Token (Optional)</div>
                            <input type="text" name="whatsapp_personal_access_token" value="{{ old('whatsapp_personal_access_token', $settings['whatsapp_personal_access_token']) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Your Personal Access Token for session management">
                        </div>
                        <div class="flex flex-col justify-center space-y-2 md:col-span-2">
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="whatsapp_enabled" value="0">
                                <input type="checkbox" name="whatsapp_enabled" id="whatsapp_enabled" value="1" {{ $settings['whatsapp_enabled'] ? 'checked' : '' }} class="w-4 h-4 rounded">
                                <label for="whatsapp_enabled" class="text-sm font-bold text-primary-700 dark:text-primary-300">Enable WhatsApp Notifications</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20">
                        <i class="fas fa-save me-2"></i> Save WhatsApp Settings
                    </button>
                    <button type="button" id="testConnectionBtn" class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-green-900/20">
                        <i class="fas fa-plug me-2"></i> Test Connection
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Test WhatsApp Form -->
        <div class="card p-6 space-y-6 border-dashed border-2 border-green-200">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fab fa-whatsapp"></i> Test WhatsApp Message
            </h3>
            <form method="POST" action="{{ route('settings.whatsapp.test-message') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Phone Number (with country code)</div>
                        <input type="text" name="test_phone" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="255655123456">
                    </div>
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Test Message</div>
                        <textarea name="test_message" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" rows="3" placeholder="Hello! This is a test WhatsApp message from FEEDTAN.">Hello! This is a test WhatsApp message from FEEDTAN.</textarea>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-green-900/20">
                        <i class="fab fa-whatsapp me-2"></i> Send Test WhatsApp
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('testConnectionBtn').addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Testing...';
        
        fetch('{{ route('settings.whatsapp.test') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-plug me-2"></i> Test Connection';
            
            if (data.success) {
                alert('Connection successful! ' + data.message);
            } else {
                alert('Connection failed: ' + data.message);
            }
        })
        .catch(error => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-plug me-2"></i> Test Connection';
            alert('Error: ' + error.message);
        });
    });
</script>
@endpush
@endsection
