@extends('layouts.app')

@section('title', 'Manage Webhooks')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-network-wired text-primary-500"></i> Manage Webhooks
            </h2>
            <p class="text-xs text-primary-500 mt-1">Configure webhook endpoints for WhatsApp events</p>
        </div>
        <a href="{{ route('whatsapp.webhooks.create') }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
            <i class="fas fa-plus mr-1"></i> Add Webhook
        </a>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Webhooks Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Endpoint URL</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Events</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($webhooks as $webhook)
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $webhook->name }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2 max-w-xs">
                                    <p class="text-xs text-primary-700 dark:text-primary-300 font-mono break-all">{{ $webhook->url }}</p>
                                    <button type="button" class="copy-url p-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-primary-500 hover:bg-primary-600 hover:text-white transition-all flex-shrink-0" title="Copy URL" data-url="{{ $webhook->url }}">
                                        <i class="fas fa-copy text-[10px]"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach($webhook->events as $event)
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">{{ $event }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ $webhook->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                                    {{ $webhook->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" class="webhook-test p-2 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600 hover:bg-green-600 hover:text-white transition-all" title="Test" data-url="{{ route('whatsapp.webhooks.test', $webhook->id) }}">
                                        <i class="fas fa-paper-plane text-xs"></i>
                                    </button>
                                    <a href="{{ route('whatsapp.webhooks.edit', $webhook->id) }}" class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('whatsapp.webhooks.destroy', $webhook->id) }}" method="POST" data-ajax-delete onsubmit="return confirm('Are you sure you want to delete this webhook?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <i class="fas fa-network-wired text-4xl text-primary-300 mb-3 block"></i>
                                <p class="text-sm font-bold text-primary-500">No webhooks configured</p>
                                <p class="text-xs text-primary-400 mt-1">Add your first webhook to receive WhatsApp event notifications.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($webhooks->hasPages())
            <div class="p-6 border-t border-primary-100 dark:border-dark-border">
                {{ $webhooks->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[data-ajax-delete]').forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this webhook?')) return;

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
                        form.closest('tr').remove();
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to delete webhook.');
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
