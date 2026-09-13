@extends('layouts.app')
@section('title', 'WhatsApp App Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-primary-900 dark:text-white flex items-center gap-2"><i class="fa-brands fa-whatsapp text-green-500 text-2xl"></i> WhatsApp App <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-bold">Phone-Assisted</span></h1>
            <p class="text-xs text-primary-500 mt-1">Web creates request → Android gateway (Flutter) → Intent → WhatsApp → User taps Send</p>
        </div>
        <a href="{{ route('whatsapp-app.create') }}" class="px-4 py-2.5 rounded-xl bg-green-600 hover:bg-green-500 text-white text-xs font-bold shadow-lg flex items-center gap-2"><i class="fas fa-paper-plane"></i> New Message</a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4"><p class="text-[11px] font-bold tracking-widest text-primary-500">PENDING</p><p class="text-2xl font-black text-amber-600">{{ $stats['pending'] }}</p><p class="text-[10px] text-primary-400">QUEUED + PENDING</p></div>
        <div class="card p-4"><p class="text-[11px] font-bold tracking-widest text-primary-500">DELIVERED</p><p class="text-2xl font-black text-blue-600">{{ $stats['delivered'] }}</p><p class="text-[10px] text-primary-400">To device</p></div>
        <div class="card p-4"><p class="text-[11px] font-bold tracking-widest text-primary-500">SENT</p><p class="text-2xl font-black text-green-600">{{ $stats['sent'] }}</p><p class="text-[10px] text-primary-400">User marked sent</p></div>
        <div class="card p-4"><p class="text-[11px] font-bold tracking-widest text-primary-500">TODAY</p><p class="text-2xl font-black text-primary-900 dark:text-white">{{ $stats['today'] }}</p><p class="text-[10px] text-primary-400">Failed: {{ $stats['failed'] }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card overflow-hidden">
            <div class="p-4 border-b border-primary-50 flex items-center justify-between"><h3 class="text-sm font-bold text-primary-900 dark:text-white">Recent Requests (10)</h3><a href="{{ route('whatsapp-app.outbox') }}" class="text-xs font-bold text-primary-600 hover:underline">View Outbox →</a></div>
            <div class="overflow-x-auto">
                <table class="data-table min-w-[640px]">
                    <thead><tr><th>Time</th><th>Device</th><th>Recipient</th><th>Message</th><th>Status</th></tr></thead>
                    <tbody class="divide-y divide-primary-50">
                        @forelse($recent as $m)
                        <tr class="hover:bg-primary-50/50">
                            <td class="whitespace-nowrap text-xs">{{ $m->requested_at->format('M d, H:i') }}</td>
                            <td class="text-xs font-bold">{{ $m->device?->device_code ?? '—' }}</td>
                            <td class="font-mono text-xs">{{ $m->recipient_phone }}</td>
                            <td class="max-w-[220px] truncate text-xs" title="{{ $m->message }}">{{ Str::limit($m->message ?? '[attachment]', 40) }}</td>
                            <td><span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $m->status==='SENT' ? 'bg-green-100 text-green-700' : ($m->status==='FAILED' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $m->status }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-8 text-primary-400 text-xs">No requests yet. Create your first WhatsApp message.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-5 space-y-4">
            <h3 class="text-sm font-bold text-primary-900 dark:text-white">Gateway Devices</h3>
            @forelse($devices as $d)
                <div class="p-3 rounded-xl border flex items-center justify-between {{ $d['online'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                    <div>
                        <p class="text-xs font-bold text-primary-900">{{ $d['code'] }} <span class="font-normal text-primary-500">{{ $d['name'] }}</span></p>
                        <p class="text-[10px] text-primary-500">{{ $d['location'] ?? 'No location' }} • {{ $d['pending'] }} pending</p>
                        <p class="text-[10px] text-primary-400">{{ $d['last_seen'] ? \Carbon\Carbon::parse($d['last_seen'])->diffForHumans() : 'never' }} • {{ $d['battery'] ?? '—' }}% • {{ $d['network'] ?? '—' }}</p>
                    </div>
                    <span class="w-2.5 h-2.5 rounded-full {{ $d['online'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                </div>
            @empty
                <p class="text-xs text-primary-400">No gateway devices. Register MOSHI-01 etc. via Devices.</p>
            @endforelse
            <a href="{{ route('whatsapp-app.devices') }}" class="block text-center text-xs font-bold text-primary-600 hover:underline">Manage Devices →</a>
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800">
                <p class="font-bold">How it works</p>
                <ol class="list-decimal ml-4 mt-1 space-y-0.5">
                    <li>Web creates request with phone + message + file</li>
                    <li>Phone polls <code class="bg-white px-1 rounded">/api/gateway/whatsapp/commands</code></li>
                    <li>Phone downloads attachment via <code class="bg-white px-1 rounded">FileProvider</code></li>
                    <li>App launches <code class="bg-white px-1 rounded">ACTION_SEND</code> to WhatsApp</li>
                    <li>User taps <b>Send</b> → Phone marks <code class="bg-white px-1 rounded">SENT</code></li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
