@extends('layouts.app')
@section('title', 'SMS Inbox')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-primary-900">SMS — Live Inbox <span class="ml-2 px-2 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">SMS ONLY</span></h1>
        <div class="flex gap-2">
            <span class="badge badge-green">Today: {{ $stats['today'] }}</span>
            <span class="badge badge-yellow">Unreconciled: {{ $stats['unreconciled'] }}</span>
            <span class="badge badge-red">Pending: {{ $stats['pending'] }}</span>
        </div>
    </div>

    <div class="card p-4">
        <form method="GET" class="grid grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search MPX827362, phone, amount..." class="col-span-2 px-3 py-2 rounded-lg border border-primary-200 text-sm">
            <select name="device_id" class="px-3 py-2 rounded-lg border border-primary-200 text-sm">
                <option value="">All Devices</option>
                @foreach($devices as $d)
                <option value="{{ $d->id }}" @selected(request('device_id')==$d->id)>{{ $d->device_code }} — {{ $d->name }}</option>
                @endforeach
            </select>
            <select name="provider_id" class="px-3 py-2 rounded-lg border border-primary-200 text-sm">
                <option value="">All Providers</option>
                @foreach($providers as $p)
                <option value="{{ $p->id }}" @selected(request('provider_id')==$p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
            <select name="reconciliation_status" class="px-3 py-2 rounded-lg border border-primary-200 text-sm">
                <option value="">All Statuses</option>
                <option value="RECONCILED" @selected(request('reconciliation_status')=='RECONCILED')>Reconciled</option>
                <option value="UNRECONCILED" @selected(request('reconciliation_status')=='UNRECONCILED')>Unreconciled</option>
                <option value="MATCHED" @selected(request('reconciliation_status')=='MATCHED')>Matched</option>
                <option value="DUPLICATE" @selected(request('reconciliation_status')=='DUPLICATE')>Duplicate</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-bold">Filter</button>
        </form>
        <div class="mt-3 flex gap-2 text-xs">
            <a href="{{ route('sms-gateway.sms', ['filter'=>'today']) }}" class="px-3 py-1 rounded-full border border-primary-200 hover:bg-primary-50">Today</a>
            <a href="{{ route('sms-gateway.sms', ['filter'=>'unreconciled']) }}" class="px-3 py-1 rounded-full border border-amber-200 bg-amber-50">Unreconciled</a>
            <a href="{{ route('sms-gateway.sms') }}" class="px-3 py-1 rounded-full border border-primary-200 hover:bg-primary-50">Clear</a>
            <span class="ml-auto text-primary-400">Live updates via polling / WebSocket ready</span>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th><th>Device</th><th>Provider</th><th>Sender</th><th>Message</th><th>Amount</th><th>Ref</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($messages as $sms)
                    <tr class="hover:bg-primary-50/50">
                        <td class="whitespace-nowrap text-xs">{{ $sms->sms_timestamp->format('Y-m-d H:i') }}</td>
                        <td class="text-xs font-bold">{{ $sms->device->device_code ?? '-' }}</td>
                        <td><span class="badge badge-green text-[10px]">{{ $sms->provider->code ?? ($sms->parsed_data['provider_code'] ?? '—') }}</span></td>
                        <td class="font-mono text-xs">{{ $sms->sender }}</td>
                        <td class="max-w-[320px] truncate text-xs" title="{{ $sms->body }}">{{ Str::limit($sms->body, 80) }}</td>
                        <td class="whitespace-nowrap text-xs font-bold">{{ $sms->smsTransaction->amount ? 'TZS '.number_format($sms->smsTransaction->amount,0) : '—' }}</td>
                        <td class="font-mono text-xs">{{ $sms->smsTransaction->reference ?? '—' }}</td>
                        <td><span class="badge {{ $sms->reconciliation_status==='RECONCILED' ? 'badge-green' : ($sms->reconciliation_status==='UNRECONCILED' ? 'badge-yellow' : 'badge-red') }} text-[10px]">{{ $sms->reconciliation_status }}</span></td>
                        <td><a href="{{ route('sms-gateway.sms.show', $sms) }}" class="text-xs font-bold text-primary-600 hover:underline">View</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-10 text-primary-400">No SMS found. Flutter gateways will populate this inbox.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $messages->links() }}</div>
    </div>
</div>
@endsection
