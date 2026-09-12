@extends('layouts.app')
@section('title', 'Device '.$device->device_code)

@section('content')
<div class="space-y-4">
    <a href="{{ route('sms-gateway.devices.index') }}" class="text-xs font-bold text-primary-600 hover:underline">← Back to Devices</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 card p-6 space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-xl font-bold text-primary-900">{{ $device->device_code }} — {{ $device->name }}</h1>
                    <p class="text-xs text-primary-500">{{ $device->location->name ?? 'No location' }} • {{ $device->phone_number ?? 'No SIM' }} • {{ $device->sim_operator ?? '' }}</p>
                </div>
                <span class="badge {{ $device->status==='ACTIVE' ? 'badge-green' : 'badge-yellow' }} text-xs">{{ $device->status }}</span>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="p-3 rounded-lg border border-primary-100"><p class="text-[11px] text-primary-500">Battery</p><p class="font-bold">{{ $device->battery_level !== null ? $device->battery_level.'%' : '—' }}</p></div>
                <div class="p-3 rounded-lg border border-primary-100"><p class="text-[11px] text-primary-500">Network</p><p class="font-bold">{{ $device->network_type ?? '—' }}</p></div>
                <div class="p-3 rounded-lg border border-primary-100"><p class="text-[11px] text-primary-500">Online</p><p class="font-bold {{ $isOnline ? 'text-green-600' : 'text-red-600' }}">{{ $isOnline ? '🟢 ONLINE' : '🔴 OFFLINE' }}</p></div>
                <div class="p-3 rounded-lg border border-primary-100"><p class="text-[11px] text-primary-500">App</p><p class="font-bold text-xs">{{ $device->app_version ?? '—' }} / And {{ $device->android_version ?? '—' }}</p></div>
            </div>

            <div class="grid grid-cols-3 gap-3 text-xs">
                <div><span class="text-primary-500">Last Heartbeat</span><p class="font-bold">{{ $device->last_heartbeat_at ?? 'never' }}</p><p class="text-primary-400">{{ $device->last_heartbeat_at?->diffForHumans() ?? '' }}</p></div>
                <div><span class="text-primary-500">Last Sync</span><p class="font-bold">{{ $device->last_sync_at ?? 'never' }}</p></div>
                <div><span class="text-primary-500">Last SMS</span><p class="font-bold">{{ $device->last_sms_at ?? 'never' }}</p></div>
            </div>

            <div class="flex flex-wrap gap-2 pt-2">
                <form method="POST" action="{{ route('sms-gateway.devices.generate-code', $device) }}">@csrf<button class="px-4 py-2 rounded-lg bg-amber-500 text-white text-xs font-bold">Generate Activation Code</button></form>
                @if($device->status !== 'ACTIVE')
                <form method="POST" action="{{ route('sms-gateway.devices.activate', $device) }}">@csrf<button class="px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-bold">Mark Active</button></form>
                @else
                <form method="POST" action="{{ route('sms-gateway.devices.suspend', $device) }}">@csrf<button class="px-4 py-2 rounded-lg bg-yellow-500 text-white text-xs font-bold">Suspend</button></form>
                @endif
                <form method="POST" action="{{ route('sms-gateway.devices.revoke', $device) }}" onsubmit="return confirm('Revoke device and all tokens?')">@csrf<button class="px-4 py-2 rounded-lg bg-red-600 text-white text-xs font-bold">Revoke</button></form>
            </div>

            @if($device->activation_code)
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-300">
                <p class="text-xs font-bold text-amber-900">Activation Code (valid until {{ $device->activation_expires_at }}):</p>
                <p class="text-3xl font-mono font-bold tracking-widest text-amber-800 mt-1">{{ $device->activation_code }}</p>
                <p class="text-xs text-amber-700 mt-1">Enter this on the Flutter gateway → Activate</p>
            </div>
            @endif

            <div>
                <h3 class="text-sm font-bold text-primary-900">Recent SMS (10)</h3>
                <div class="mt-2 overflow-x-auto border border-primary-100 rounded-lg">
                    <table class="data-table">
                        <thead><tr><th>Time</th><th>Sender</th><th>Body</th><th>Status</th></tr></thead>
                        <tbody class="divide-y divide-primary-50">
                            @forelse($recentSms as $sms)
                            <tr><td class="text-xs whitespace-nowrap">{{ $sms->sms_timestamp }}</td><td class="font-mono text-xs">{{ $sms->sender }}</td><td class="text-xs max-w-[240px] truncate">{{ Str::limit($sms->body,60) }}</td><td><span class="badge badge-green text-[10px]">{{ $sms->reconciliation_status }}</span></td></tr>
                            @empty<tr><td colspan="4" class="text-center py-4 text-primary-400 text-xs">No SMS yet</td></tr>@endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card p-4">
                <h3 class="text-sm font-bold text-primary-900">Auth Tokens</h3>
                @forelse($device->tokens()->latest()->limit(5)->get() as $t)
                <div class="mt-2 p-2 rounded-lg border border-primary-100">
                    <p class="font-mono text-xs">{{ substr($t->token,0,16) }}... hint: {{ $t->plain_hint ?? '—' }}</p>
                    <p class="text-[11px] text-primary-500">Revoked: {{ $t->is_revoked ? 'YES' : 'NO' }} • Expires: {{ $t->expires_at ?? 'never' }} • Last used: {{ $t->last_used_at ?? 'never' }}</p>
                </div>
                @empty<p class="text-xs text-primary-400">No tokens.</p>@endforelse
            </div>

            <div class="card p-4">
                <h3 class="text-sm font-bold text-primary-900">Heartbeats (last 20)</h3>
                <div class="mt-2 space-y-1 max-h-[320px] overflow-y-auto">
                    @forelse($device->heartbeats as $hb)
                    <div class="text-xs p-2 rounded border border-primary-100">
                        <p>{{ $hb->created_at }} — Bat {{ $hb->battery_level ?? '?' }}% • {{ $hb->network_type ?? '?' }} • pending {{ $hb->pending_sms }}</p>
                    </div>
                    @empty<p class="text-xs text-primary-400">No heartbeats.</p>@endforelse
                </div>
            </div>

            <form method="POST" action="{{ route('sms-gateway.devices.destroy', $device) }}" onsubmit="return confirm('Delete device permanently?')">@csrf @method('DELETE')<button class="w-full px-3 py-2 rounded-lg border border-red-200 text-red-600 text-xs font-bold">Delete Device</button></form>
        </div>
    </div>
</div>
@endsection
