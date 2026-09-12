@extends('layouts.app')
@section('title', 'SMS Inbox')

@section('content')
<div class="space-y-4" x-data="smsDrawer()" @keydown.escape.window="closeDrawer()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h1 class="text-xl font-bold text-primary-900">SMS — Live Inbox <span class="ml-2 px-2 py-1 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">SMS ONLY</span></h1>
        <div class="flex flex-wrap gap-2">
            <span class="badge badge-green">Today: {{ $stats['today'] }}</span>
            <span class="badge badge-green">Recorded: {{ $stats['recorded'] }}</span>
            <span class="badge badge-yellow">Not Recorded: {{ $stats['not_recorded'] }}</span>
            <span class="badge badge-red">Pending: {{ $stats['pending'] }}</span>
        </div>
    </div>

    <div class="card p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search MPX827362, phone, amount, comment..." class="sm:col-span-2 px-3 py-2 rounded-lg border border-primary-200 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
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
            <select name="recorded" class="px-3 py-2 rounded-lg border border-primary-200 text-sm">
                <option value="">All • Recorded</option>
                <option value="1" @selected(request('recorded')=='1')>Recorded ✓</option>
                <option value="0" @selected(request('recorded')=='0')>Not Recorded</option>
            </select>
            <button class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-bold hover:bg-primary-700">Filter</button>
        </form>
        <div class="mt-3 flex flex-wrap gap-2 text-xs items-center">
            <a href="{{ route('sms-gateway.sms', ['filter'=>'today']) }}" class="px-3 py-1.5 rounded-full border border-primary-200 hover:bg-primary-50">Today</a>
            <a href="{{ route('sms-gateway.sms', ['filter'=>'recorded']) }}" class="px-3 py-1.5 rounded-full border border-green-200 bg-green-50 text-green-700">Recorded</a>
            <a href="{{ route('sms-gateway.sms', ['filter'=>'not_recorded']) }}" class="px-3 py-1.5 rounded-full border border-amber-200 bg-amber-50 text-amber-700">Not Recorded</a>
            <a href="{{ route('sms-gateway.sms') }}" class="px-3 py-1.5 rounded-full border border-primary-200 hover:bg-primary-50">Clear</a>
            <span class="ml-auto text-primary-400 hidden sm:inline">Click any row → right drawer with full details • Add comment • Toggle recorded</span>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Time</th><th>Device</th><th>Provider</th><th>Sender</th><th>Message</th><th>Amount</th><th>Ref</th><th>Recorded</th><th>Comment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($messages as $sms)
                    <tr @click="openDrawer({{ $sms->id }})" class="hover:bg-primary-50/70 cursor-pointer transition-colors {{ $sms->is_recorded ? 'bg-green-50/30' : '' }}" title="Click to open details drawer">
                        <td class="whitespace-nowrap text-xs">{{ $sms->sms_timestamp->format('Y-m-d H:i') }}</td>
                        <td class="text-xs font-bold">{{ $sms->device->device_code ?? '-' }}</td>
                        <td><span class="badge badge-green text-[10px]">{{ $sms->provider->code ?? ($sms->parsed_data['provider_code'] ?? '—') }}</span></td>
                        <td class="font-mono text-xs">{{ $sms->sender }}</td>
                        <td class="max-w-[260px] truncate text-xs" title="{{ $sms->body }}">{{ Str::limit($sms->body, 64) }}</td>
                        <td class="whitespace-nowrap text-xs font-bold">{{ optional($sms->smsTransaction)->amount ? 'TZS '.number_format($sms->smsTransaction->amount,0) : '—' }}</td>
                        <td class="font-mono text-xs">{{ optional($sms->smsTransaction)->reference ?? '—' }}</td>
                        <td>
                            @if($sms->is_recorded)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-bold"><i class="fa-solid fa-check text-[9px]"></i> Recorded</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold"><i class="fa-solid fa-clock text-[9px]"></i> Not recorded</span>
                            @endif
                        </td>
                        <td class="max-w-[140px] truncate text-xs text-primary-600">
                            @if($sms->admin_comment)
                                <span title="{{ $sms->admin_comment }}"><i class="fa-solid fa-comment-dots text-primary-400"></i> {{ Str::limit($sms->admin_comment, 28) }}</span>
                            @else
                                <span class="text-primary-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-10 text-primary-400">No SMS found. Flutter gateways will populate this inbox.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $messages->links() }}</div>
    </div>

    <!-- Right Drawer -->
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40" style="display:none;">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="absolute inset-y-0 right-0 w-full sm:w-[480px] bg-white shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-primary-100 bg-primary-50">
                <div>
                    <h3 class="text-sm font-bold text-primary-900 flex items-center gap-2"><i class="fa-solid fa-inbox text-primary-600"></i> SMS Details</h3>
                    <p class="text-[11px] text-primary-500" x-text="selected ? 'ID #' + selected.id + ' • ' + selected.uuid.substring(0,8) + '…' : ''"></p>
                </div>
                <button @click="closeDrawer()" class="w-8 h-8 rounded-lg bg-white border border-primary-200 flex items-center justify-center hover:bg-primary-50"><i class="fa-solid fa-xmark text-primary-600"></i></button>
            </div>

            <div x-show="loading" class="p-8 text-center">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto"></div>
                <p class="text-xs text-primary-500 mt-3">Loading details...</p>
            </div>

            <div x-show="!loading && selected" class="flex-1 overflow-y-auto p-5 space-y-5" style="display:none;" x-cloak>
                <!-- Meta -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-primary-50 border border-primary-100">
                        <p class="text-[10px] font-bold tracking-widest text-primary-500">DEVICE</p>
                        <p class="font-bold text-primary-900" x-text="selected?.device ? selected.device.code + ' — ' + selected.device.name : '—'"></p>
                        <p class="text-primary-500" x-text="selected?.device?.location ?? ''"></p>
                    </div>
                    <div class="p-3 rounded-xl bg-primary-50 border border-primary-100">
                        <p class="text-[10px] font-bold tracking-widest text-primary-500">PROVIDER • SENDER</p>
                        <p class="font-bold text-primary-900" x-text="(selected?.provider?.code ?? '—') + ' • ' + (selected?.sender ?? '')"></p>
                        <p class="text-primary-500" x-text="selected?.sms_timestamp + ' • ' + selected?.received_at"></p>
                    </div>
                </div>

                <!-- Body -->
                <div>
                    <p class="text-[11px] font-bold tracking-widest text-primary-500 mb-2">MESSAGE BODY</p>
                    <div class="p-4 rounded-xl bg-gray-900 text-green-300 text-xs leading-relaxed whitespace-pre-wrap break-words" x-text="selected?.body"></div>
                    <p class="mt-2 text-[10px] font-mono text-primary-400 break-all" x-text="'Hash: ' + (selected?.hash ?? '')"></p>
                </div>

                <!-- Parsed Transaction -->
                <div class="card p-4 !bg-primary-50/50">
                    <h4 class="text-xs font-bold text-primary-900 mb-3">Extracted Transaction</h4>
                    <template x-if="selected?.transaction">
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div><span class="text-primary-500">Amount</span><p class="font-bold text-sm" x-text="selected.transaction.amount ? 'TZS ' + Number(selected.transaction.amount).toLocaleString() + ' ' + selected.transaction.currency : '—'"></p></div>
                            <div><span class="text-primary-500">Type</span><p><span class="badge badge-green text-[10px]" x-text="selected.transaction.type"></span></p></div>
                            <div><span class="text-primary-500">Reference</span><p class="font-mono font-bold" x-text="selected.transaction.reference ?? '—'"></p></div>
                            <div><span class="text-primary-500">Counterparty</span><p class="font-bold" x-text="selected.transaction.counterparty ?? '—'"></p></div>
                            <div><span class="text-primary-500">Balance</span><p class="font-bold" x-text="selected.transaction.balance ? 'TZS ' + Number(selected.transaction.balance).toLocaleString() : '—'"></p></div>
                            <div><span class="text-primary-500">Sync</span><p class="font-bold" x-text="selected.sync_status + ' / ' + selected.processing_status"></p></div>
                        </div>
                    </template>
                    <template x-if="!selected?.transaction">
                        <p class="text-xs text-primary-400">No transaction parsed (generic SMS).</p>
                    </template>
                </div>

                <!-- Recorded Toggle -->
                <div class="p-4 rounded-xl border-2" :class="selected?.is_recorded ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200'">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold" :class="selected?.is_recorded ? 'text-green-800' : 'text-amber-800'" x-text="selected?.is_recorded ? '✓ Recorded in books' : 'Not yet recorded'"></p>
                            <p class="text-[11px]" :class="selected?.is_recorded ? 'text-green-600' : 'text-amber-600'" x-text="selected?.is_recorded ? ('By ' + (selected.recorded_by ?? '—') + ' at ' + (selected.recorded_at ?? '')) : 'Mark when you have entered this SMS into accounting.'"></p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" :checked="selected?.is_recorded" @change="toggleRecorded($event.target.checked)" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Comment -->
                <div>
                    <label class="text-[11px] font-bold tracking-widest text-primary-500">COMMENT (visible to team)</label>
                    <textarea x-model="commentText" rows="3" placeholder="Add note: e.g. Recorded in ledger INV-00127, or reason for pending..." class="mt-2 w-full px-3 py-2.5 rounded-xl border border-primary-200 text-xs outline-none focus:ring-2 focus:ring-primary-500 bg-white"></textarea>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-[11px] text-primary-400" x-text="selected?.comment_by ? 'Last comment by ' + selected.comment_by + ' at ' + selected.commented_at : ''"></p>
                        <button @click="saveComment()" :disabled="savingComment" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-xs font-bold hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2">
                            <span x-show="!savingComment">Save Comment</span><span x-show="savingComment">Saving…</span>
                        </button>
                    </div>
                    <template x-if="selected?.admin_comment">
                        <div class="mt-3 p-3 rounded-lg bg-primary-50 border border-primary-100">
                            <p class="text-xs text-primary-700 whitespace-pre-wrap" x-text="selected.admin_comment"></p>
                        </div>
                    </template>
                </div>

                <div class="pt-2 flex gap-2">
                    <a :href="'{{ url('sms-gateway/sms') }}/' + selected?.id" class="flex-1 px-3 py-2 rounded-lg border border-primary-200 text-center text-xs font-bold hover:bg-primary-50">Open Full Page</a>
                    <button @click="closeDrawer()" class="flex-1 px-3 py-2 rounded-lg bg-gray-900 text-white text-xs font-bold">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function smsDrawer(){
    return {
        drawerOpen:false,
        selected:null,
        loading:false,
        commentText:'',
        savingComment:false,
        openDrawer(id){
            this.drawerOpen=true;
            this.loading=true;
            this.selected=null;
            fetch('{{ url('sms-gateway/sms') }}/' + id + '/details', {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
                .then(r=>r.json())
                .then(data=>{
                    this.selected=data;
                    this.commentText=data.admin_comment || '';
                    this.loading=false;
                })
                .catch(()=>{this.loading=false; alert('Failed to load details');});
        },
        closeDrawer(){ this.drawerOpen=false; setTimeout(()=>{this.selected=null;},300); },
        toggleRecorded(checked){
            if(!this.selected) return;
            fetch('{{ url('sms-gateway/sms') }}/' + this.selected.id + '/recorded', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content,'Accept':'application/json'},
                body: JSON.stringify({is_recorded: checked ? 1 : 0})
            }).then(r=>r.json()).then(j=>{
                if(j.success){ this.selected.is_recorded = j.is_recorded; this.selected.recorded_at = new Date().toLocaleString(); }
                // also update row badge without reload? simple reload for now
                // location.reload();
            });
        },
        saveComment(){
            if(!this.selected) return;
            this.savingComment=true;
            fetch('{{ url('sms-gateway/sms') }}/' + this.selected.id + '/comment', {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content,'Accept':'application/json'},
                body: JSON.stringify({admin_comment: this.commentText})
            }).then(r=>r.json()).then(j=>{
                this.savingComment=false;
                if(j.success){
                    this.selected.admin_comment=j.comment;
                    this.selected.commented_at=new Date().toLocaleString();
                }
            }).catch(()=>{this.savingComment=false;});
        }
    }
}
</script>
@endsection
