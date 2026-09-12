@extends('layouts.app')
@section('title', 'SMS Detail')

@section('content')
<div class="space-y-4 max-w-4xl">
    <a href="{{ route('sms-gateway.sms') }}" class="text-xs font-bold text-primary-600 hover:underline">← Back to SMS Inbox</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold tracking-widest text-primary-500">SMS #{{ $sms->id }} • {{ $sms->uuid }}</p>
                        <p class="text-sm font-bold text-primary-900 mt-1">{{ $sms->sender }} → {{ $sms->device->device_code ?? '-' }} ({{ $sms->device->name ?? '' }})</p>
                        <p class="text-xs text-primary-500">{{ $sms->sms_timestamp }} • Received: {{ $sms->received_at }}</p>
                    </div>
                    <span class="badge {{ $sms->reconciliation_status==='RECONCILED' ? 'badge-green' : 'badge-yellow' }}">{{ $sms->reconciliation_status }}</span>
                </div>
                <div class="mt-4 p-4 rounded-xl bg-primary-50 border border-primary-100">
                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ $sms->body }}</p>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-3 text-xs">
                    <div><span class="text-primary-500">Provider</span><p class="font-bold">{{ $sms->provider->name ?? $sms->parsed_data['provider_code'] ?? '—' }}</p></div>
                    <div><span class="text-primary-500">Hash</span><p class="font-mono text-[10px] break-all">{{ $sms->hash }}</p></div>
                    <div><span class="text-primary-500">Sync</span><p class="font-bold">{{ $sms->sync_status }} / {{ $sms->processing_status }}</p></div>
                </div>
            </div>

            @if($sms->smsTransaction)
            <div class="card p-5">
                <h3 class="text-sm font-bold text-primary-900">Extracted Transaction</h3>
                <div class="mt-3 grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-xs text-primary-500">Amount</span><p class="font-bold text-lg">TZS {{ $sms->smsTransaction->amount ? number_format($sms->smsTransaction->amount,0) : '—' }} <span class="text-xs font-normal">{{ $sms->smsTransaction->currency }}</span></p></div>
                    <div><span class="text-xs text-primary-500">Type</span><p class="font-bold"><span class="badge badge-green">{{ $sms->smsTransaction->transaction_type }}</span></p></div>
                    <div><span class="text-xs text-primary-500">Reference</span><p class="font-mono font-bold">{{ $sms->smsTransaction->reference ?? '—' }}</p></div>
                    <div><span class="text-xs text-primary-500">Counterparty</span><p class="font-bold">{{ $sms->smsTransaction->counterparty ?? '—' }}</p></div>
                    <div><span class="text-xs text-primary-500">Balance</span><p class="font-bold">{{ $sms->smsTransaction->balance ? 'TZS '.number_format($sms->smsTransaction->balance,0) : '—' }}</p></div>
                    <div><span class="text-xs text-primary-500">At</span><p class="font-bold">{{ $sms->smsTransaction->transaction_at }}</p></div>
                </div>
                <details class="mt-4">
                    <summary class="text-xs font-bold text-primary-600 cursor-pointer">Raw extracted JSON</summary>
                    <pre class="mt-2 p-3 rounded-lg bg-gray-900 text-green-400 text-[11px] overflow-x-auto">{{ json_encode($sms->parsed_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
                </details>
            </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="card p-4">
                <h4 class="text-sm font-bold text-primary-900">Reconciliation</h4>
                @if($sms->reconciliation)
                    <p class="text-xs mt-2">Status: <span class="badge {{ $sms->reconciliation->status==='RECONCILED' ? 'badge-green' : 'badge-yellow' }}">{{ $sms->reconciliation->status }}</span></p>
                    @if($sms->reconciliation->status !== 'RECONCILED')
                    <form method="POST" action="{{ route('sms-gateway.reconciliation.match', $sms->reconciliation) }}" class="mt-3 space-y-2">
                        @csrf
                        <input type="number" name="matched_transaction_id" placeholder="Transaction ID (optional)" class="w-full px-3 py-2 rounded-lg border border-primary-200 text-xs">
                        <input type="number" name="matched_payout_id" placeholder="Payout ID (optional)" class="w-full px-3 py-2 rounded-lg border border-primary-200 text-xs">
                        <textarea name="notes" placeholder="Notes" class="w-full px-3 py-2 rounded-lg border border-primary-200 text-xs"></textarea>
                        <button class="w-full px-3 py-2 rounded-lg bg-primary-600 text-white text-xs font-bold">Mark Reconciled</button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('sms-gateway.reconciliation.unmatch', $sms->reconciliation) }}" class="mt-3">
                        @csrf
                        <button class="w-full px-3 py-2 rounded-lg border border-amber-300 bg-amber-50 text-amber-800 text-xs font-bold">Unmatch</button>
                    </form>
                    @endif
                @else
                    <p class="text-xs text-primary-400 mt-2">No reconciliation record.</p>
                @endif
            </div>

            <div class="card p-4">
                <h4 class="text-xs font-bold text-primary-900">Device</h4>
                <p class="text-sm font-bold mt-1">{{ $sms->device->device_code }} — {{ $sms->device->name }}</p>
                <p class="text-xs text-primary-500">{{ $sms->device->location->name ?? 'No location' }} • {{ $sms->device->phone_number ?? '' }}</p>
                <p class="text-xs text-primary-500 mt-1">Status: {{ $sms->device->status }} • Online: {{ $sms->device->isOnline() ? 'YES' : 'NO' }}</p>
                <a href="{{ route('sms-gateway.devices.show', $sms->device) }}" class="inline-block mt-2 text-xs font-bold text-primary-600 hover:underline">View Device →</a>
            </div>

            <form method="POST" action="{{ route('sms-gateway.inbox.retry', $sms) }}">
                @csrf
                <button class="w-full px-3 py-2 rounded-lg border border-primary-200 text-xs font-bold hover:bg-primary-50">Retry Processing</button>
            </form>
        </div>
    </div>
</div>
@endsection
