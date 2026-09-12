@extends('layouts.app')
@section('title', 'SMS Devices')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-primary-900">Devices</h1>
        <a href="{{ route('sms-gateway.devices.create') }}" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-xs font-bold">+ Register Device</a>
    </div>

    <div class="grid grid-cols-4 gap-3">
        <div class="card p-3 text-center"><p class="text-[11px] text-primary-500">TOTAL</p><p class="text-xl font-bold">{{ $stats['total'] }}</p></div>
        <div class="card p-3 text-center"><p class="text-[11px] text-primary-500">ACTIVE</p><p class="text-xl font-bold text-green-600">{{ $stats['active'] }}</p></div>
        <div class="card p-3 text-center"><p class="text-[11px] text-primary-500">ONLINE</p><p class="text-xl font-bold text-green-600">{{ $stats['online'] }}</p></div>
        <div class="card p-3 text-center"><p class="text-[11px] text-primary-500">PENDING</p><p class="text-xl font-bold text-amber-600">{{ $stats['pending'] }}</p></div>
    </div>

    <div class="card p-4">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search MOSHI-01, phone..." class="flex-1 px-3 py-2 rounded-lg border border-primary-200 text-sm">
            <select name="status" class="px-3 py-2 rounded-lg border border-primary-200 text-sm">
                <option value="">All Status</option>
                <option value="ACTIVE" @selected(request('status')=='ACTIVE')>ACTIVE</option>
                <option value="PENDING" @selected(request('status')=='PENDING')>PENDING</option>
                <option value="SUSPENDED" @selected(request('status')=='SUSPENDED')>SUSPENDED</option>
                <option value="REVOKED" @selected(request('status')=='REVOKED')>REVOKED</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-bold">Filter</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead><tr><th>Device</th><th>Location</th><th>Phone</th><th>Status</th><th>Battery</th><th>Network</th><th>Last Heartbeat</th><th>Last Sync</th><th></th></tr></thead>
            <tbody class="divide-y divide-primary-50">
                @forelse($devices as $d)
                <tr class="hover:bg-primary-50/50">
                    <td><a href="{{ route('sms-gateway.devices.show', $d) }}" class="font-bold text-primary-700 hover:underline">{{ $d->device_code }}</a><br><span class="text-xs text-primary-500">{{ $d->name }}</span></td>
                    <td class="text-xs">{{ $d->location->name ?? '—' }}</td>
                    <td class="font-mono text-xs">{{ $d->phone_number ?? '—' }}</td>
                    <td><span class="badge {{ $d->status==='ACTIVE' ? 'badge-green' : ($d->status==='PENDING' ? 'badge-yellow' : 'badge-red') }}">{{ $d->status }}</span> @if($d->isOnline()) <span class="ml-1 w-2 h-2 inline-block rounded-full bg-green-500"></span> @endif</td>
                    <td class="text-xs">{{ $d->battery_level !== null ? $d->battery_level.'%' : '—' }}</td>
                    <td class="text-xs">{{ $d->network_type ?? '—' }}</td>
                    <td class="text-xs whitespace-nowrap">{{ $d->last_heartbeat_at?->diffForHumans() ?? 'never' }}</td>
                    <td class="text-xs whitespace-nowrap">{{ $d->last_sync_at?->diffForHumans() ?? 'never' }}</td>
                    <td><a href="{{ route('sms-gateway.devices.show', $d) }}" class="text-xs font-bold text-primary-600 hover:underline">Manage</a></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center py-8 text-primary-400">No devices. Register MOSHI-01, TABORA-01...</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $devices->links() }}</div>
    </div>
</div>
@endsection
