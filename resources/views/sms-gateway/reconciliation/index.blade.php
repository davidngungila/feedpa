@extends('layouts.app')
@section('title', 'Reconciliation')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-primary-900">Reconciliation</h1>
        <div class="flex gap-2">
            <span class="badge badge-yellow">Unreconciled: {{ $stats['unreconciled'] }}</span>
            <span class="badge badge-green">Reconciled: {{ $stats['reconciled'] }}</span>
            <span class="badge badge-green">Matched: {{ $stats['matched'] }}</span>
        </div>
    </div>

    <div class="card p-4">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reference, phone..." class="flex-1 px-3 py-2 rounded-lg border border-primary-200 text-sm">
            <select name="status" class="px-3 py-2 rounded-lg border border-primary-200 text-sm">
                <option value="">All</option>
                <option value="UNRECONCILED" @selected(request('status')=='UNRECONCILED')>Unreconciled</option>
                <option value="MATCHED" @selected(request('status')=='MATCHED')>Matched</option>
                <option value="RECONCILED" @selected(request('status')=='RECONCILED')>Reconciled</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-bold">Filter</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead><tr><th>SMS</th><th>Provider</th><th>Amount</th><th>Ref</th><th>Type</th><th>Status</th><th>Matched</th><th></th></tr></thead>
            <tbody class="divide-y divide-primary-50">
                @forelse($reconciliations as $r)
                <tr class="hover:bg-primary-50/50">
                    <td class="text-xs whitespace-nowrap">{{ $r->smsMessage->sms_timestamp->format('Y-m-d H:i') }}<br><span class="text-primary-500">{{ $r->smsMessage->device->device_code ?? '-' }}</span></td>
                    <td><span class="badge badge-green text-[10px]">{{ $r->smsTransaction->provider_code ?? '—' }}</span></td>
                    <td class="font-bold text-xs">TZS {{ $r->smsTransaction->amount ? number_format($r->smsTransaction->amount,0) : '—' }}</td>
                    <td class="font-mono text-xs">{{ $r->smsTransaction->reference ?? '—' }}</td>
                    <td class="text-xs">{{ $r->smsTransaction->transaction_type }}</td>
                    <td><span class="badge {{ $r->status==='RECONCILED' ? 'badge-green' : ($r->status==='UNRECONCILED' ? 'badge-yellow' : 'badge-red') }} text-[10px]">{{ $r->status }}</span></td>
                    <td class="text-xs">{{ $r->matched_transaction_id ? 'Txn #'.$r->matched_transaction_id : ($r->matched_payout_id ? 'Payout #'.$r->matched_payout_id : '—') }}</td>
                    <td><a href="{{ route('sms-gateway.sms.show', $r->smsMessage) }}" class="text-xs font-bold text-primary-600 hover:underline">View</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-8 text-primary-400">No reconciliations.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $reconciliations->links() }}</div>
    </div>
</div>
@endsection
