@extends('layouts.app')

@section('title', 'SMS Gateway Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-primary-900">SMS &amp; Accounting System</h1>
            <p class="text-xs text-primary-500">Centralized SMS Gateway — Devices: Moshi / Tabora / Arusha</p>
        </div>
        <a href="{{ route('sms-gateway.devices.create') }}" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-xs font-bold hover:bg-primary-700">+ Register Device</a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-[11px] font-bold tracking-widest text-primary-500">DEVICES ONLINE</p>
            <p class="text-2xl font-bold text-green-600">{{ $devicesOnline }}</p>
        </div>
        <div class="card p-4">
            <p class="text-[11px] font-bold tracking-widest text-primary-500">DEVICES OFFLINE</p>
            <p class="text-2xl font-bold text-red-600">{{ $devicesOffline }}</p>
        </div>
        <div class="card p-4">
            <p class="text-[11px] font-bold tracking-widest text-primary-500">SMS TODAY</p>
            <p class="text-2xl font-bold text-primary-900">{{ $smsToday }}</p>
            <p class="text-[11px] text-primary-500">Synced: {{ $syncedToday }} · Pending: {{ $pending }} · Failed: {{ $failed }}</p>
        </div>
        <div class="card p-4">
            <p class="text-[11px] font-bold tracking-widest text-primary-500">PAYMENTS TODAY</p>
            <p class="text-2xl font-bold text-primary-900">TZS {{ number_format($paymentsToday,0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 card overflow-hidden">
            <div class="px-4 py-3 border-b border-primary-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-primary-900">Live SMS Inbox (latest 10)</h3>
                <a href="{{ route('sms-gateway.sms') }}" class="text-xs font-bold text-primary-600 hover:underline">View SMS →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr><th>Time</th><th>Device</th><th>Provider</th><th>Sender</th><th>Message</th><th>Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-primary-50">
                        @forelse($recentSms as $sms)
                        <tr class="hover:bg-primary-50/50">
                            <td class="whitespace-nowrap">{{ $sms->sms_timestamp->format('H:i') }}</td>
                            <td>{{ $sms->device->device_code ?? '-' }}</td>
                            <td><span class="badge badge-green">{{ $sms->provider->code ?? $sms->parsed_data['provider_code'] ?? '—' }}</span></td>
                            <td class="font-mono text-xs">{{ $sms->sender }}</td>
                            <td class="max-w-[260px] truncate text-xs">{{ Str::limit($sms->body, 60) }}</td>
                            <td><span class="badge {{ $sms->reconciliation_status==='RECONCILED' ? 'badge-green' : ($sms->reconciliation_status==='UNRECONCILED' ? 'badge-yellow' : 'badge-red') }}">{{ $sms->reconciliation_status }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-primary-400 py-8">No SMS yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-4 space-y-3">
            <h3 class="text-sm font-bold text-primary-900">Device Health</h3>
            @foreach($deviceHealth as $d)
            <div class="flex items-center justify-between p-3 rounded-lg border border-primary-100 {{ $d['online'] ? 'bg-green-50' : 'bg-red-50' }}">
                <div>
                    <p class="text-xs font-bold text-primary-900">{{ $d['code'] }} <span class="text-primary-500 font-normal">{{ $d['name'] }}</span></p>
                    <p class="text-[11px] text-primary-500">Battery {{ $d['battery'] ?? '—' }}% · {{ $d['network'] ?? '—' }}</p>
                    <p class="text-[11px] text-primary-400">Last: {{ $d['last_heartbeat']?->diffForHumans() ?? 'never' }}</p>
                </div>
                <span class="w-3 h-3 rounded-full {{ $d['online'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
            </div>
            @endforeach
            @if(empty($deviceHealth) || $deviceHealth->isEmpty())
                <p class="text-xs text-primary-400">No devices registered.</p>
            @endif

            <div class="pt-3 border-t border-primary-100">
                <h4 class="text-xs font-bold text-primary-700 mb-2">By Provider Today</h4>
                @foreach($byProvider as $row)
                    <div class="flex justify-between text-xs"><span>{{ $row->provider->name ?? 'Unknown' }}</span><span class="font-bold">{{ $row->cnt }}</span></div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
