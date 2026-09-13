@extends('layouts.app')
@section('title', 'Reconciliation')

@section('content')
<div class="space-y-4" x-data="reconDrawer()" @keydown.escape.window="closeDrawer()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h1 class="text-xl font-bold text-primary-900">Reconciliation</h1>
        <div class="flex flex-wrap gap-2">
            <span class="badge badge-yellow">Unreconciled: {{ $stats['unreconciled'] }}</span>
            <span class="badge badge-green">Reconciled: {{ $stats['reconciled'] }}</span>
            <span class="badge badge-red">Matched: {{ $stats['matched'] }}</span>
        </div>
    </div>

    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reference, phone..." class="flex-1 min-w-[160px] px-3 py-2 rounded-lg border border-primary-200 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
            <select name="status" class="px-3 py-2 rounded-lg border border-primary-200 text-sm bg-white">
                <option value="">All</option>
                <option value="UNRECONCILED" @selected(request('status')=='UNRECONCILED')>Unreconciled</option>
                <option value="MATCHED" @selected(request('status')=='MATCHED')>Matched</option>
                <option value="RECONCILED" @selected(request('status')=='RECONCILED')>Reconciled</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-bold hover:bg-primary-700">Filter</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Time</th><th>Provider</th><th>Amount</th><th>Ref</th><th>Status</th></tr></thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($reconciliations as $r)
                    <tr @click="openDrawer({{ $r->id }})" class="hover:bg-primary-50/70 cursor-pointer transition-colors" title="Click to open details drawer">
                        <td class="text-xs whitespace-nowrap">{{ $r->smsMessage->sms_timestamp->format('Y-m-d H:i') }}<br><span class="text-[10px] font-bold text-primary-400">{{ $r->smsMessage->device->device_code ?? '-' }}</span></td>
                        <td><span class="badge badge-green text-[10px]">{{ $r->smsTransaction->provider_code ?? '—' }}</span></td>
                        <td class="font-bold text-xs whitespace-nowrap">TZS {{ $r->smsTransaction->amount ? number_format($r->smsTransaction->amount,0) : '—' }}</td>
                        <td class="font-mono text-xs">{{ $r->smsTransaction->reference ?? '—' }}</td>
                        <td><span class="badge {{ $r->status==='RECONCILED' ? 'badge-green' : ($r->status==='UNRECONCILED' ? 'badge-yellow' : 'badge-red') }} text-[10px]">{{ $r->status }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-8 text-primary-400">No reconciliations.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $reconciliations->links() }}</div>
    </div>

    <!-- Right Drawer -->
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[480px] max-w-[100vw] h-full max-h-screen bg-white shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-5 pt-0 pb-4 border-b border-primary-100 bg-primary-50">
                <div>
                    <h3 class="text-sm font-bold text-primary-900 flex items-center gap-2"><i class="fa-solid fa-scale-balanced text-primary-600"></i> Reconciliation Details</h3>
                    <p class="text-[11px] text-primary-500" x-text="selected ? 'ID #' + selected.id : ''"></p>
                </div>
                <button @click="closeDrawer()" class="w-8 h-8 rounded-lg bg-white border border-primary-200 flex items-center justify-center hover:bg-primary-50"><i class="fa-solid fa-xmark text-primary-600"></i></button>
            </div>

            <div x-show="loading" class="p-8 text-center">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto"></div>
                <p class="text-xs text-primary-500 mt-3">Loading details...</p>
            </div>

            <div x-show="!loading && selected" class="flex-1 min-h-0 overflow-y-auto p-5 space-y-5" style="display:none;" x-cloak>
                <!-- Status -->
                <div class="p-4 rounded-xl border-2" :class="selected?.status === 'RECONCILED' ? 'bg-green-50 border-green-200' : (selected?.status === 'MATCHED' ? 'bg-blue-50 border-blue-200' : 'bg-amber-50 border-amber-200')">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold" :class="selected?.status === 'RECONCILED' ? 'text-green-800' : (selected?.status === 'MATCHED' ? 'text-blue-800' : 'text-amber-800')" x-text="selected?.status"></p>
                            <p class="text-[11px] text-primary-500" x-text="statusSub()"></p>
                        </div>
                        <template x-if="selected?.matched">
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold" :class="selected.matched.kind === 'transaction' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'" x-text="'Matched to ' + selected.matched.kind.toUpperCase()"></span>
                        </template>
                    </div>
                </div>

                <!-- SMS meta -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-primary-50 border border-primary-100">
                        <p class="text-[10px] font-bold tracking-widest text-primary-500">DEVICE</p>
                        <p class="font-bold text-primary-900" x-text="selected?.sms?.device ? selected.sms.device.code + ' — ' + selected.sms.device.name : '—'"></p>
                        <p class="text-primary-500" x-text="selected?.sms?.device?.location ?? ''"></p>
                    </div>
                    <div class="p-3 rounded-xl bg-primary-50 border border-primary-100">
                        <p class="text-[10px] font-bold tracking-widest text-primary-500">PROVIDER • SENDER</p>
                        <p class="font-bold text-primary-900" x-text="(selected?.sms?.provider?.code ?? '—') + ' • ' + (selected?.sms?.sender ?? '')"></p>
                        <p class="text-primary-500" x-text="selected?.sms?.sms_timestamp"></p>
                    </div>
                </div>

                <!-- SMS body -->
                <div>
                    <p class="text-[11px] font-bold tracking-widest text-primary-500 mb-2">SMS BODY</p>
                    <div class="p-4 rounded-xl bg-gray-900 text-green-300 text-[11px] leading-relaxed whitespace-pre-wrap break-words font-mono" x-text="selected?.sms?.body"></div>
                </div>

                <!-- Extracted transaction -->
                <div class="card p-4 !bg-primary-50/50">
                    <h4 class="text-xs font-bold text-primary-900 mb-3">Extracted Transaction</h4>
                    <template x-if="selected?.sms?.transaction">
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div><span class="text-primary-500">Amount</span><p class="font-bold text-sm" x-text="selected.sms.transaction.amount ? 'TZS ' + Number(selected.sms.transaction.amount).toLocaleString() + ' ' + (selected.sms.transaction.currency ?? '') : '—'"></p></div>
                            <div><span class="text-primary-500">Type</span><p><span class="badge badge-green text-[10px]" x-text="selected.sms.transaction.type"></span></p></div>
                            <div><span class="text-primary-500">Reference</span><p class="font-mono font-bold" x-text="selected.sms.transaction.reference ?? '—'"></p></div>
                            <div><span class="text-primary-500">Counterparty</span><p class="font-bold" x-text="selected.sms.transaction.counterparty ?? '—'"></p></div>
                            <div><span class="text-primary-500">Name</span><p class="font-bold" x-text="selected.sms.transaction.counterparty_name ?? '—'"></p></div>
                            <div><span class="text-primary-500">Balance</span><p class="font-bold" x-text="selected.sms.transaction.balance ? 'TZS ' + Number(selected.sms.transaction.balance).toLocaleString() : '—'"></p></div>
                        </div>
                    </template>
                    <template x-if="!selected?.sms?.transaction">
                        <p class="text-xs text-primary-400">No transaction extracted.</p>
                    </template>
                </div>

                <!-- Matched record -->
                <template x-if="selected?.matched">
                    <div class="p-4 rounded-xl" :class="selected.matched.kind === 'transaction' ? 'bg-blue-50 border border-blue-200' : 'bg-purple-50 border border-purple-200'">
                        <p class="text-[11px] font-bold tracking-widest mb-2" :class="selected.matched.kind === 'transaction' ? 'text-blue-700' : 'text-purple-700'" x-text="'MATCHED ' + selected.matched.kind.toUpperCase()"></p>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div><span class="text-primary-500">Reference</span><p class="font-mono font-bold" x-text="selected.matched.reference"></p></div>
                            <div><span class="text-primary-500">Status</span><p><span class="badge badge-green text-[10px]" x-text="selected.matched.status ?? '—'"></span></p></div>
                            <div><span class="text-primary-500">Amount</span><p class="font-bold" x-text="selected.matched.amount ? 'TZS ' + Number(selected.matched.amount).toLocaleString() : '—'"></p></div>
                            <div><span class="text-primary-500">Description</span><p class="leading-tight" x-text="selected.matched.description ?? '—'"></p></div>
                        </div>
                    </div>
                </template>

                @if(auth()->user()->is_admin)
                <!-- Match form -->
                <template x-if="selected?.matched == null">
                    <div class="space-y-3">
                        <p class="text-[11px] font-bold tracking-widest text-primary-500 mb-1">MATCH TRANSACTION OR PAYOUT</p>
                        <form method="POST" :action="'{{ url('sms-gateway/reconciliation') }}/' + selected?.id + '/match'" class="space-y-3">
                            @csrf
                            <div>
                                <label class="text-[11px] text-primary-500 font-bold">Transaction</label>
                                <select name="matched_transaction_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-primary-200 text-xs bg-white" :value="selected?.matchedTransactionId ?? ''">
                                    <option value="">— Select transaction —</option>
                                    @foreach($transactions as $t)
                                    <option value="{{ $t->id }}">{{ $t->order_reference ?? $t->transaction_id ?? $t->id }} — @if($t->amount)TZS {{ number_format($t->amount,0) }}@endif {{ $t->status ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] text-primary-500 font-bold">or Payout</label>
                                <select name="matched_payout_id" class="mt-1 w-full px-3 py-2 rounded-lg border border-primary-200 text-xs bg-white" :value="selected?.matchedPayoutId ?? ''">
                                    <option value="">— Select payout —</option>
                                    @foreach($payouts as $p)
                                    <option value="{{ $p->id }}">{{ $p->order_reference ?? $p->id }} — @if($p->amount)TZS {{ number_format($p->amount,0) }}@endif {{ $p->status ?? '' }}{{ $p->recipient_name ? ' • '.$p->recipient_name : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] text-primary-500 font-bold">Notes</label>
                                <textarea name="notes" rows="2" :value="selected?.notes ?? ''" placeholder="Optional note..." class="mt-1 w-full px-3 py-2 rounded-lg border border-primary-200 text-xs outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2 rounded-lg bg-primary-600 text-white text-xs font-bold hover:bg-primary-700">Match Now</button>
                        </form>
                    </div>
                </template>

                <!-- Unmatch -->
                <template x-if="selected?.matched">
                    <form method="POST" :action="'{{ url('sms-gateway/reconciliation') }}/' + selected?.id + '/unmatch'" onsubmit="return confirm('Remove this match?')" class="pt-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold hover:bg-amber-100"><i class="fa-solid fa-link-slash me-1"></i> Unmatch</button>
                    </form>
                </template>
                @endif

                <template x-if="selected?.sms">
                    <a :href="'{{ url('sms-gateway/sms') }}/' + selected.sms.id" class="flex items-center justify-center gap-2 px-3 py-2 rounded-lg border border-primary-200 text-xs font-bold hover:bg-primary-50">
                        <i class="fa-solid fa-message text-primary-600"></i> Open Full SMS
                    </a>
                </template>

                <button @click="closeDrawer()" class="w-full px-3 py-2 rounded-lg bg-gray-900 text-white text-xs font-bold">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function reconDrawer(){
    return {
        drawerOpen:false,
        selected:null,
        loading:false,
        openDrawer(id){
            this.drawerOpen=true;
            this.loading=true;
            this.selected=null;
            fetch('{{ url('sms-gateway/reconciliation') }}/' + id + '/details', {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
                .then(r=>r.json())
                .then(data=>{
                    this.selected=data;
                    this.loading=false;
                })
                .catch(()=>{this.loading=false; alert('Failed to load details');});
        },
        closeDrawer(){ this.drawerOpen=false; setTimeout(()=>{this.selected=null;},300); },
        statusSub(){
            if(!this.selected) return '';
            if(this.selected.status==='RECONCILED') return 'Reconciled by ' + (this.selected.reconciled_by ?? '—') + ' at ' + (this.selected.reconciled_at ?? '');
            if(this.selected.status==='MATCHED') return 'Matched — needs final reconcile';
            return 'Pending — awaiting match';
        }
    }
}
</script>
@endsection