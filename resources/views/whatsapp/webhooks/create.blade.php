@extends('layouts.app')

@section('title', 'Configure Webhook')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-network-wired text-primary-500"></i> Configure Webhook
            </h2>
            <p class="text-xs text-primary-500 mt-1">Attach a webhook endpoint to a WhatsApp session</p>
        </div>
        <a href="{{ route('whatsapp.webhooks.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <div class="card p-6">
        @if(!$personalTokenConfigured)
            <div class="mb-5 p-4 rounded-xl border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
                <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                    <i class="fas fa-exclamation-triangle mr-1"></i> The WhatsApp Personal Access Token is not configured.
                    <a href="{{ route('settings.whatsapp') }}" class="underline">Add it in WhatsApp settings</a> to load the session list and save webhook changes.
                </p>
            </div>
        @elseif(empty($sessions))
            <div class="mb-5 p-4 rounded-xl border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
                <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                    <i class="fas fa-exclamation-triangle mr-1"></i> No WhatsApp sessions were found on your Wasender account. Create one in the Wasender dashboard first.
                </p>
            </div>
        @endif

        <form method="POST" action="{{ route('whatsapp.webhooks.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">WhatsApp Session *</label>
                    <select name="session_id" id="sessionSelect" {{ empty($sessions) ? 'disabled' : '' }} class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @if(empty($sessions))
                            <option value="">No sessions available</option>
                        @else
                            <option value="">Select a session</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session['id'] }}" {{ old('session_id') == $session['id'] ? 'selected' : '' }}>
                                    {{ $session['name'] ?? ('Session ' . $session['id']) }}
                                    @if(!empty($session['phone_number'])) ({{ $session['phone_number'] }}) @endif
                                    @if(!empty($session['status'])) — {{ ucfirst(strtolower($session['status'])) }} @endif
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('session_id') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Or enter Session ID manually</label>
                    <input type="number" name="session_id_manual" id="sessionIdManual" value="{{ old('session_id_manual') }}" min="1" placeholder="e.g. 106257" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <p class="text-[10px] text-primary-400 mt-1">Find the session ID in your Wasender dashboard URL (e.g. wasenderapi.com/whatsapp/106257/webhook).</p>
                    @error('session_id_manual') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Endpoint URL *</label>
                    <div class="flex gap-2">
                        <input type="url" name="webhook_url" id="webhookUrl" value="{{ old('webhook_url', $canonicalUrl) }}" required class="flex-1 w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="https://pay.feedtancmg.org/api/whatsapp/webhook">
                        <button type="button" id="resetUrlBtn" class="px-4 py-2 rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 hover:bg-primary-600 hover:text-white text-xs font-bold transition-all whitespace-nowrap">
                            <i class="fas fa-undo mr-1"></i> Default
                        </button>
                    </div>
                    <p class="text-[10px] text-primary-400 mt-1">Destination for POST requests sent by Wasender on events. Uses the configured webhook URL.</p>
                    @error('webhook_url') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Webhook Secret (optional)</label>
                    <div class="flex gap-2">
                        <input type="text" name="webhook_secret" id="webhookSecret" value="{{ old('webhook_secret') }}" class="flex-1 w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="whsec_...">
                        <button type="button" id="generateSecretBtn" class="px-4 py-2 rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 hover:bg-primary-600 hover:text-white text-xs font-bold transition-all whitespace-nowrap">
                            <i class="fas fa-shield-alt mr-1"></i> Rotate
                        </button>
                    </div>
                    <p class="text-[10px] text-primary-400 mt-1">Leave empty to keep the session's current secret. Wasender verifies the <span class="font-mono">X-Webhook-Signature</span> header against it.</p>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="webhook_enabled" value="0">
                    <input type="checkbox" name="webhook_enabled" id="webhook_enabled" value="1" {{ old('webhook_enabled', 1) ? 'checked' : '' }} class="w-4 h-4 rounded">
                    <label for="webhook_enabled" class="text-sm font-bold text-primary-700 dark:text-primary-300">Webhook enabled</label>
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Subscriptions / Trigger events</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($events as $event)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="events[]" value="{{ $event }}" class="w-4 h-4 rounded" {{ in_array($event, old('events', [])) ? 'checked' : '' }}>
                                <span class="text-xs font-bold text-primary-700 dark:text-primary-300 font-mono">{{ $event }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('events') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
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
        const sessionSelect = document.getElementById('sessionSelect');
        const sessionIdManual = document.getElementById('sessionIdManual');

        if (sessionSelect && sessionIdManual) {
            sessionSelect.addEventListener('change', function () {
                if (this.value) sessionIdManual.value = '';
            });
            sessionIdManual.addEventListener('input', function () {
                if (this.value) sessionSelect.value = '';
            });
        }

        const resetUrlBtn = document.getElementById('resetUrlBtn');
        if (resetUrlBtn) {
            resetUrlBtn.addEventListener('click', function () {
                document.getElementById('webhookUrl').value = '{{ $canonicalUrl }}';
            });
        }

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
