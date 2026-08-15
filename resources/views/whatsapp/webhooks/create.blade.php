@extends('layouts.app')

@section('title', 'Add Webhook')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-network-wired text-primary-500"></i> Add Webhook
            </h2>
            <p class="text-xs text-primary-500 mt-1">Register a new webhook endpoint</p>
        </div>
        <a href="{{ route('whatsapp.webhooks.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('whatsapp.webhooks.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Webhook Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Message Status Updates">
                    @error('name') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Endpoint URL *</label>
                    <div class="flex gap-2">
                        <input type="url" name="url" id="webhookUrl" value="{{ old('url') }}" required class="flex-1 w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://your-domain.com/api/whatsapp/webhook/token">
                        <button type="button" id="generateUrlBtn" class="px-4 py-2 rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 hover:bg-primary-600 hover:text-white text-xs font-bold transition-all whitespace-nowrap">
                            <i class="fas fa-magic mr-1"></i> Generate URL
                        </button>
                    </div>
                    <input type="hidden" name="token" id="webhookToken" value="{{ old('token') }}">
                    <p class="text-[10px] text-primary-400 mt-1">Generate a unique callback URL or enter your own endpoint.</p>
                    @error('url') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Events *</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach(['message_received', 'message_sent', 'message_delivered', 'message_read', 'session_status', 'contact_update', 'group_update'] as $event)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="events[]" value="{{ $event }}" class="w-4 h-4 rounded" {{ in_array($event, old('events', [])) ? 'checked' : '' }}>
                                <span class="text-xs font-bold text-primary-700 dark:text-primary-300">{{ ucwords(str_replace('_', ' ', $event)) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('events') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Secret (optional)</label>
                    <div class="flex gap-2">
                        <input type="text" name="secret" id="webhookSecret" value="{{ old('secret') }}" class="flex-1 w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="whsec_...">
                        <button type="button" id="generateSecretBtn" class="px-4 py-2 rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 hover:bg-primary-600 hover:text-white text-xs font-bold transition-all whitespace-nowrap">
                            <i class="fas fa-shield-alt mr-1"></i> Generate
                        </button>
                    </div>
                    <p class="text-[10px] text-primary-400 mt-1">Used to verify webhook payloads sent to your endpoint. Leave empty to auto-generate.</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }} class="w-4 h-4 rounded">
                    <label for="is_active" class="text-sm font-bold text-primary-700 dark:text-primary-300">Webhook active</label>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20">
                    <i class="fas fa-save me-2"></i> Save Webhook
                </button>
                <a href="{{ route('whatsapp.webhooks.index') }}" class="px-6 py-3 bg-gray-100 dark:bg-primary-900/20 text-primary-700 font-bold rounded-xl hover:bg-gray-200 transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function ajaxPost(url, btn, loadingText, onSuccess) {
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>' + loadingText;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = original;
                onSuccess(data);
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = original;
                alert('Error: ' + error.message);
            });
        }

        const generateUrlBtn = document.getElementById('generateUrlBtn');
        if (generateUrlBtn) {
            generateUrlBtn.addEventListener('click', function () {
                ajaxPost('{{ route('whatsapp.webhooks.generate-url') }}', this, 'Generating...', function (data) {
                    if (data.success) {
                        document.getElementById('webhookUrl').value = data.url;
                        document.getElementById('webhookToken').value = data.token;
                    } else {
                        alert(data.message || 'Failed to generate URL.');
                    }
                });
            });
        }

        const generateSecretBtn = document.getElementById('generateSecretBtn');
        if (generateSecretBtn) {
            generateSecretBtn.addEventListener('click', function () {
                ajaxPost('{{ route('whatsapp.webhooks.generate-secret') }}', this, 'Generating...', function (data) {
                    if (data.success) {
                        document.getElementById('webhookSecret').value = data.secret;
                    } else {
                        alert(data.message || 'Failed to generate secret.');
                    }
                });
            });
        }
    });
</script>
@endpush
