@extends('layouts.app')

@section('title', 'Manage Webhooks')

@section('content')
<div x-data="webhookDrawer()" @keydown.escape.window="closeDrawer()" class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-network-wired text-primary-500"></i> Manage Webhooks
            </h2>
            <p class="text-xs text-primary-500 mt-1">Webhook configuration captured live from WhatsApp sessions</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('whatsapp.webhooks.events') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-bolt mr-1"></i> Webhook Events
            </a>
            <a href="{{ route('whatsapp.webhooks.create') }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                <i class="fas fa-plus mr-1"></i> Configure Webhook
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    @if(session('error'))
        <div class="card p-4 border-l-4 border-l-red-500 bg-red-50/60 dark:bg-red-900/10">
            <p class="text-xs font-bold text-red-700 dark:text-red-300">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            </p>
        </div>
    @endif

    @if(!$personalTokenConfigured)
        <div class="card p-4 border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle mr-1"></i> The WhatsApp Personal Access Token is not configured.
                <a href="{{ route('settings.whatsapp') }}" class="underline">Configure it in WhatsApp settings</a> to load live webhook configuration.
            </p>
        </div>
    @endif

    <!-- Webhooks Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Session</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Endpoint URL</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Events</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($webhooks as $webhook)
                        <tr @click="openDrawer(@js($webhook))" class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $webhook['name'] ?? ('Session ' . $webhook['id']) }}</p>
                                @if(!empty($webhook['status']))
                                    <p class="text-[10px] text-primary-500 mt-0.5">
                                        <span class="px-2 py-0.5 rounded-full font-bold {{ $webhook['status'] === 'open' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-primary-500 dark:bg-gray-800' }}">
                                            {{ ucfirst($webhook['status']) }}
                                        </span>
                                        @if(!empty($webhook['phone_number']))
                                            <span class="ml-1">{{ $webhook['phone_number'] }}</span>
                                        @endif
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($webhook['webhook_url'])
                                    <div class="flex items-center gap-2 max-w-xs">
                                        <p class="text-xs text-primary-700 dark:text-primary-300 font-mono break-all">{{ $webhook['webhook_url'] }}</p>
                                        <button type="button" @click.stop class="copy-url p-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-primary-500 hover:bg-primary-600 hover:text-white transition-all flex-shrink-0" title="Copy URL" data-url="{{ $webhook['webhook_url'] }}">
                                            <i class="fas fa-copy text-[10px]"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-primary-400">Not configured</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @forelse($webhook['webhook_events'] ?? [] as $event)
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">{{ $event }}</span>
                                    @empty
                                        <span class="text-xs text-primary-400">None</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ $webhook['webhook_enabled'] ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                                    {{ $webhook['webhook_enabled'] ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if($webhook['webhook_url'])
                                        <button type="button" @click.stop class="webhook-test p-2 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 hover:bg-green-600 hover:text-white transition-all" title="Test" data-url="{{ route('whatsapp.webhooks.test', $webhook['id']) }}">
                                            <i class="fas fa-paper-plane text-xs"></i>
                                        </button>
                                        <a href="{{ route('whatsapp.webhooks.edit', $webhook['id']) }}" @click.stop class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Edit">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <form action="{{ route('whatsapp.webhooks.destroy', $webhook['id']) }}" method="POST" @click.stop data-ajax-delete onsubmit="return confirm('Are you sure you want to disable the webhook for this session?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Disable">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('whatsapp.webhooks.edit', $webhook['id']) }}" @click.stop class="p-2 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 hover:bg-primary-600 hover:text-white transition-all" title="Configure">
                                            <i class="fas fa-cog text-xs"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <i class="fas fa-network-wired text-4xl text-primary-300 mb-3 block"></i>
                                <p class="text-sm font-bold text-primary-500">No sessions with webhooks found</p>
                                <p class="text-xs text-primary-400 mt-1">Configure a webhook on a WhatsApp session to receive event notifications.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Drawer -->
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[480px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="text-sm font-black text-primary-900 dark:text-white">Webhook Details</h3>
                    <p x-text="drawerWebhook?.name || ''" class="text-xs text-primary-500 mt-0.5"></p>
                </div>
                <button @click="closeDrawer()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-primary-400 hover:text-primary-600 transition-all">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">
                <!-- Loading -->
                <div x-show="drawerLoading" class="flex items-center justify-center py-12">
                    <i class="fas fa-spinner fa-spin text-2xl text-primary-400"></i>
                </div>

                <div x-show="!drawerLoading && drawerWebhook" x-cloak>
                    <!-- Session Info -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-primary-500 text-sm"></i>
                            </div>
                            <div>
                                <p x-text="drawerWebhook?.name || ''" class="text-sm font-bold text-primary-900 dark:text-white"></p>
                                <p class="text-[11px] text-primary-400">ID: <span x-text="drawerWebhook?.id || ''" class="font-mono"></span></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <template x-if="drawerWebhook?.status">
                                <span :class="drawerWebhook.status === 'open' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-primary-500 dark:bg-gray-800'" class="px-2.5 py-1 rounded-full text-[10px] font-bold" x-text="drawerWebhook.status.charAt(0).toUpperCase() + drawerWebhook.status.slice(1)"></span>
                            </template>
                            <template x-if="drawerWebhook?.phone_number">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300" x-text="drawerWebhook.phone_number"></span>
                            </template>
                        </div>
                    </div>

                    <!-- Endpoint URL -->
                    <div class="mt-5">
                        <p class="text-[10px] font-bold text-primary-500 uppercase tracking-wider mb-2">Endpoint URL</p>
                        <template x-if="drawerWebhook?.webhook_url">
                            <div class="flex items-center gap-2 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono break-all flex-1" x-text="drawerWebhook.webhook_url"></p>
                                <button @click="copyDrawerUrl()" class="p-1.5 rounded-lg bg-white dark:bg-gray-800 text-primary-500 hover:bg-primary-600 hover:text-white transition-all flex-shrink-0" title="Copy URL">
                                    <i :class="drawerCopied ? 'fas fa-check text-[10px]' : 'fas fa-copy text-[10px]'"></i>
                                </button>
                            </div>
                        </template>
                        <template x-if="!drawerWebhook?.webhook_url">
                            <p class="text-xs text-primary-400 italic">Not configured</p>
                        </template>
                    </div>

                    <!-- Webhook Enabled Status -->
                    <div class="mt-5">
                        <p class="text-[10px] font-bold text-primary-500 uppercase tracking-wider mb-2">Webhook Status</p>
                        <template x-if="drawerWebhook?.webhook_enabled">
                            <span class="px-3 py-1.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                <i class="fas fa-check-circle mr-1"></i> Enabled
                            </span>
                        </template>
                        <template x-if="!drawerWebhook?.webhook_enabled">
                            <span class="px-3 py-1.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">
                                <i class="fas fa-times-circle mr-1"></i> Disabled
                            </span>
                        </template>
                    </div>

                    <!-- Events -->
                    <div class="mt-5">
                        <p class="text-[10px] font-bold text-primary-500 uppercase tracking-wider mb-2">Subscribed Events</p>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="event in (drawerWebhook?.webhook_events || [])" :key="event">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300" x-text="event"></span>
                            </template>
                            <template x-if="!drawerWebhook?.webhook_events || drawerWebhook.webhook_events.length === 0">
                                <p class="text-xs text-primary-400 italic">No events subscribed</p>
                            </template>
                        </div>
                    </div>

                    <!-- Webhook Secret -->
                    <div class="mt-5" x-show="drawerWebhook?.webhook_secret" x-cloak>
                        <p class="text-[10px] font-bold text-primary-500 uppercase tracking-wider mb-2">Webhook Secret</p>
                        <div class="flex items-center gap-2 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-800">
                            <p class="text-xs text-primary-700 dark:text-primary-300 font-mono break-all flex-1" x-text="drawerWebhook?.webhook_secret || ''"></p>
                            <button @click="copyDrawerSecret()" class="p-1.5 rounded-lg bg-white dark:bg-gray-800 text-primary-500 hover:bg-primary-600 hover:text-white transition-all flex-shrink-0" title="Copy Secret">
                                <i :class="drawerSecretCopied ? 'fas fa-check text-[10px]' : 'fas fa-copy text-[10px]'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[10px] font-bold text-primary-500 uppercase tracking-wider mb-1">Created</p>
                            <p class="text-xs text-primary-700 dark:text-primary-300" x-text="drawerWebhook?.created_at ? new Date(drawerWebhook.created_at).toLocaleDateString() : 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-primary-500 uppercase tracking-wider mb-1">Updated</p>
                            <p class="text-xs text-primary-700 dark:text-primary-300" x-text="drawerWebhook?.updated_at ? new Date(drawerWebhook.updated_at).toLocaleDateString() : 'N/A'"></p>
                        </div>
                    </div>

                    <!-- Drawer Actions -->
                    <div class="mt-6 space-y-2">
                        <template x-if="drawerWebhook?.webhook_url">
                            <div class="flex flex-wrap gap-2">
                                <button @click="testDrawerWebhook()" class="px-4 py-2 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-600 hover:bg-green-600 hover:text-white text-xs font-bold transition-all flex items-center gap-1.5">
                                    <i :class="drawerTesting ? 'fas fa-spinner fa-spin' : 'fas fa-paper-plane'"></i>
                                    <span x-text="drawerTesting ? 'Sending...' : 'Test (POST)'"></span>
                                </button>
                                <a :href="'{{ url('/whatsapp/webhooks') }}/' + drawerWebhook?.id + '/edit'" class="px-4 py-2 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-600 hover:text-white text-xs font-bold transition-all flex items-center gap-1.5">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form :action="'{{ url('/whatsapp/webhooks') }}/' + drawerWebhook?.id" method="POST" @click.stop data-ajax-delete>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-600 hover:text-white text-xs font-bold transition-all flex items-center gap-1.5">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </template>
                        <template x-if="!drawerWebhook?.webhook_url">
                            <a :href="'{{ url('/whatsapp/webhooks') }}/' + drawerWebhook?.id + '/edit'" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all inline-flex items-center gap-1.5">
                                <i class="fas fa-cog"></i> Configure Webhook
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Drawer Footer -->
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                <button @click="closeDrawer()" class="w-full px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-primary-700 dark:text-primary-300 text-xs font-bold transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
    function webhookDrawer() {
        return {
            drawerOpen: false,
            drawerWebhook: null,
            drawerLoading: false,
            drawerTesting: false,
            drawerCopied: false,
            drawerSecretCopied: false,

            openDrawer(webhook) {
                this.drawerWebhook = webhook;
                this.drawerOpen = true;
                this.drawerLoading = false;
            },

            closeDrawer() {
                this.drawerOpen = false;
                this.drawerWebhook = null;
                this.drawerTesting = false;
                this.drawerCopied = false;
                this.drawerSecretCopied = false;
            },

            copyDrawerUrl() {
                const url = this.drawerWebhook?.webhook_url;
                if (!url) return;
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(() => {
                        this.drawerCopied = true;
                        setTimeout(() => { this.drawerCopied = false; }, 1500);
                    });
                }
            },

            copyDrawerSecret() {
                const secret = this.drawerWebhook?.webhook_secret;
                if (!secret) return;
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(secret).then(() => {
                        this.drawerSecretCopied = true;
                        setTimeout(() => { this.drawerSecretCopied = false; }, 1500);
                    });
                }
            },

            testDrawerWebhook() {
                if (!this.drawerWebhook?.id) return;
                this.drawerTesting = true;
                const url = '{{ route("whatsapp.webhooks.test", ":id") }}'.replace(':id', this.drawerWebhook.id);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    this.drawerTesting = false;
                    alert(data.message || 'Test completed.');
                })
                .catch(error => {
                    this.drawerTesting = false;
                    alert('Error: ' + error.message);
                });
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-ajax-delete]').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to disable this webhook?')) return;

                fetch(form.action, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to disable webhook.');
                    }
                })
                .catch(error => alert('Error: ' + error.message));
            });
        });

        document.querySelectorAll('.copy-url').forEach(btn => {
            btn.addEventListener('click', function () {
                const url = this.dataset.url;
                const icon = this.querySelector('i');

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(() => {
                        icon.className = 'fas fa-check text-[10px]';
                        setTimeout(() => { icon.className = 'fas fa-copy text-[10px]'; }, 1500);
                    });
                } else {
                    const textarea = document.createElement('textarea');
                    textarea.value = url;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    icon.className = 'fas fa-check text-[10px]';
                    setTimeout(() => { icon.className = 'fas fa-copy text-[10px]'; }, 1500);
                }
            });
        });

        document.querySelectorAll('.webhook-test').forEach(btn => {
            btn.addEventListener('click', function () {
                const url = this.dataset.url;
                const original = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    this.disabled = false;
                    this.innerHTML = original;
                    alert(data.message || 'Test completed.');
                })
                .catch(error => {
                    this.disabled = false;
                    this.innerHTML = original;
                    alert('Error: ' + error.message);
                });
            });
        });
    });
</script>
@endpush
