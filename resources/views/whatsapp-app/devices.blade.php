@extends('layouts.app')
@section('title', 'WhatsApp Devices')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-lg font-bold">WhatsApp Gateway Devices</h1>
        <a href="{{ route('sms-gateway.devices.index') }}" class="text-xs font-bold text-primary-600 hover:underline">Manage SMS Gateway Devices →</a>
    </div>
    <p class="text-xs text-primary-500">Same physical devices as SMS Gateway (MOSHI-01 etc). Reuse activation, heartbeat, battery, network. WhatsApp availability if detectable.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($devices as $d)
            <div class="card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-bold text-primary-900">{{ $d->device_code }} — {{ $d->name }}</p>
                        <p class="text-xs text-primary-500">{{ $d->location?->name ?? 'No location' }} • {{ $d->phone_number ?? '' }}</p>
                        <p class="text-[11px] mt-1">Status: <span class="font-bold {{ $d->status==='ACTIVE'?'text-green-600':'text-amber-600' }}">{{ $d->status }}</span> • {{ $d->isOnline() ? '🟢 ONLINE' : '🔴 OFFLINE' }} • Last: {{ $d->last_heartbeat_at?->diffForHumans() ?? 'never' }}</p>
                        <p class="text-[11px]">Battery {{ $d->battery_level ?? '—' }}% • {{ $d->network_type ?? '—' }} • Pending WhatsApp: {{ $d->pending_whatsapp ?? 0 }}</p>
                    </div>
                    <span class="w-3 h-3 rounded-full {{ $d->isOnline() ? 'bg-green-500' : 'bg-red-500' }}"></span>
                </div>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('sms-gateway.devices.show', $d) }}" class="text-xs font-bold text-primary-600 hover:underline">View Device</a>
                    @if($d->isOnline())
                        <span class="text-xs text-green-600">WhatsApp ready — device will poll /api/gateway/whatsapp/commands</span>
                    @else
                        <span class="text-xs text-amber-600">Offline — requests will queue</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
