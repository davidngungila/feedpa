@extends('layouts.app')

@section('title', 'Webhook Events')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-bolt text-primary-500"></i> Webhook Events
            </h2>
            <p class="text-xs text-primary-500 mt-1">Events received at <span class="font-mono">{{ url('api/whatsapp/webhook') }}</span></p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('whatsapp.webhooks.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-arrow-left mr-1"></i> Manage Webhooks
            </a>
            <a href="{{ request()->fullUrlWithQuery([]) }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </a>
        </div>
    </div>

    <!-- Events Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Source</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Payload</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Received At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer webhook-event-row" data-event-id="{{ $log->id }}" data-event-payload='@json($log->toArray())'>
                            <td class="px-6 py-4">
                                <p class="text-xs font-mono text-primary-700 dark:text-primary-300">#{{ $log->id }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                                    <i class="fas fa-bolt mr-1"></i>{{ $log->event }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono">{{ $log->source }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 max-w-md line-clamp-2 break-all">{{ \Illuminate\Support\Str::limit(json_encode($log->payload), 120) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ $log->created_at }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <i class="fas fa-bolt text-4xl text-primary-300 mb-3 block"></i>
                                <p class="text-sm font-bold text-primary-500">No webhook events received yet</p>
                                <p class="text-xs text-primary-400 mt-1">Events will appear here when Wasender delivers them to {{ url('api/whatsapp/webhook') }}.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="p-6 border-t border-primary-100 dark:border-dark-border">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Event Details Modal -->
<div id="eventModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="card w-full max-w-2xl p-6 max-h-[85vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-4">
            <h3 class="text-sm font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-bolt"></i> Event Details
            </h3>
            <button type="button" id="closeEventModal" class="p-2 rounded-lg bg-gray-100 dark:bg-primary-900/20 text-primary-500 hover:text-red-500 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="eventModalContent" class="space-y-3 text-xs">
            <div class="flex flex-wrap gap-2">
                <span id="eventModalBadge" class="px-2 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300"></span>
                <span id="eventModalSource" class="px-2 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-primary-500 dark:bg-gray-800"></span>
                <span id="eventModalTime" class="px-2 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-primary-500 dark:bg-gray-800"></span>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Payload (JSON)</p>
                <pre id="eventModalPayload" class="p-3 rounded-xl bg-gray-900 text-green-300 text-[10px] leading-relaxed overflow-x-auto max-h-[50vh]"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('eventModal');
        const closeBtn = document.getElementById('closeEventModal');
        const badge = document.getElementById('eventModalBadge');
        const sourceEl = document.getElementById('eventModalSource');
        const timeEl = document.getElementById('eventModalTime');
        const payloadEl = document.getElementById('eventModalPayload');

        function openEvent(payload) {
            badge.innerHTML = '<i class="fas fa-bolt mr-1"></i>' + (payload.event || 'unknown');
            sourceEl.innerHTML = '<i class="fas fa-tag mr-1"></i>' + (payload.source || '');
            timeEl.innerHTML = '<i class="far fa-clock mr-1"></i>' + (payload.created_at || '');
            payloadEl.textContent = JSON.stringify(payload.payload, null, 2);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        document.querySelectorAll('.webhook-event-row').forEach(function (row) {
            row.addEventListener('click', function () {
                openEvent(JSON.parse(row.dataset.eventPayload));
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });
</script>
@endpush
