@extends('layouts.app')
@section('title', 'WhatsApp Outbox')

@section('content')
<div class="space-y-4" x-data="waDrawer()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h1 class="text-lg font-bold text-primary-900 dark:text-white flex items-center gap-2"><i class="fa-solid fa-inbox text-green-600"></i> WhatsApp — Outbox <span class="px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 text-[10px]">{{ $messages->total() }}</span></h1>
        <div class="flex gap-2">
            <a href="{{ route('whatsapp-app.create') }}" class="px-4 py-2 rounded-xl bg-green-600 text-white text-xs font-bold">+ New Message</a>
            <a href="{{ route('whatsapp-app.devices') }}" class="px-4 py-2 rounded-xl border border-primary-200 text-xs font-bold">Devices</a>
        </div>
    </div>

    <div class="card p-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search phone, message, uuid..." class="px-3 py-2 rounded-xl border border-primary-200 text-sm">
            <select name="device_id" class="px-3 py-2 rounded-xl border border-primary-200 text-sm">
                <option value="">All Devices</option>
                @foreach($devices as $d)<option value="{{ $d->id }}" @selected(request('device_id')==$d->id)>{{ $d->device_code }}</option>@endforeach
            </select>
            <select name="status" class="px-3 py-2 rounded-xl border border-primary-200 text-sm">
                <option value="">All Status</option>
                @foreach(['PENDING','QUEUED','DELIVERED_TO_DEVICE','OPENED','USER_ACTION_REQUIRED','SENT','FAILED','CANCELLED'] as $s)
                    <option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 rounded-xl bg-primary-600 text-white text-xs font-bold">Filter</button>
        </form>
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
            <a href="{{ route('whatsapp-app.outbox') }}" class="px-3 py-1 rounded-full border {{ !request('status') ? 'bg-primary-600 text-white' : 'hover:bg-primary-50' }}">All</a>
            <a href="{{ route('whatsapp-app.pending') }}" class="px-3 py-1 rounded-full border {{ request('status')==='PENDING' ? 'bg-amber-500 text-white' : 'hover:bg-primary-50' }}">Pending</a>
            <a href="{{ route('whatsapp-app.sent') }}" class="px-3 py-1 rounded-full border {{ request('status')==='SENT' ? 'bg-green-600 text-white' : 'hover:bg-primary-50' }}">Sent</a>
            <a href="{{ route('whatsapp-app.failed') }}" class="px-3 py-1 rounded-full border {{ request('status')==='FAILED' ? 'bg-red-600 text-white' : 'hover:bg-primary-50' }}">Failed</a>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="hidden md:block overflow-x-auto">
            <table class="data-table min-w-[720px]">
                <thead><tr><th>Date</th><th>Device</th><th>Recipient</th><th>Message</th><th>Attachment</th><th>Status</th></tr></thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($messages as $m)
                    <tr @click="openDrawer('{{ $m->uuid }}')" class="hover:bg-primary-50/70 cursor-pointer">
                        <td class="whitespace-nowrap text-xs">{{ $m->requested_at->format('M d, H:i') }}</td>
                        <td class="text-xs font-bold">{{ $m->device?->device_code ?? '—' }}</td>
                        <td class="font-mono text-xs">{{ $m->recipient_phone }}</td>
                        <td class="max-w-[240px] truncate text-xs" title="{{ $m->message }}">{{ Str::limit($m->message ?? '[attachment]', 36) }}</td>
                        <td class="text-xs">@if($m->attachment_name)<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 text-[10px]"><i class="fas fa-paperclip"></i> {{ Str::limit($m->attachment_name, 16) }}</span>@else — @endif</td>
                        <td><span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $m->status==='SENT'?'bg-green-100 text-green-700':($m->status==='FAILED'?'bg-red-100 text-red-700':'bg-amber-100 text-amber-700') }}">{{ $m->status }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-10 text-primary-400 text-xs">No messages. Create one for MOSHI-01.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="md:hidden divide-y divide-primary-50">
            @forelse($messages as $m)
                <div @click="openDrawer('{{ $m->uuid }}')" class="p-4 flex items-center gap-3 hover:bg-primary-50/50 cursor-pointer">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-xs {{ $m->status==='SENT'?'bg-green-500':($m->status==='FAILED'?'bg-red-500':'bg-amber-500') }}"><i class="fa-brands fa-whatsapp"></i></div>
                    <div class="min-w-0 flex-1">
                        <div class="font-mono text-xs font-bold truncate">{{ $m->recipient_phone }}</div>
                        <div class="text-xs truncate">{{ Str::limit($m->message ?? $m->attachment_name ?? '', 28) }}</div>
                        <div class="text-[10px] text-primary-400">{{ $m->requested_at->format('M d, H:i') }} • {{ $m->device?->device_code ?? '—' }} • {{ $m->status }}</div>
                    </div>
                    <i class="fas fa-chevron-right text-primary-300 text-xs"></i>
                </div>
            @empty
                <div class="p-8 text-center text-primary-400 text-xs">No messages</div>
            @endforelse
        </div>
        <div class="p-4">{{ $messages->links() }}</div>
    </div>

    <!-- Right Drawer for full details -->
    <div x-show="drawerOpen" x-cloak class="fixed inset-0 z-[60] flex justify-end overflow-hidden" @keydown.escape.window="closeDrawer()">
        <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
        <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[520px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <div class="shrink-0 flex items-start justify-between gap-4 px-5 pt-0 pb-4 border-b bg-primary-50/50">
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold flex items-center gap-2"><i class="fa-brands fa-whatsapp text-green-600"></i> WhatsApp Request</h3>
                    <p class="font-mono text-xs font-bold truncate" x-text="selected?.uuid"></p>
                    <p class="text-[10px] text-primary-500" x-text="selected?.recipient_phone"></p>
                </div>
                <button @click="closeDrawer()" class="w-8 h-8 rounded-lg bg-white border flex items-center justify-center"><i class="fas fa-times text-xs"></i></button>
            </div>
            <div class="flex-1 min-h-0 overflow-y-auto p-5 space-y-4" x-show="selected">
                <template x-if="selected">
                    <div class="space-y-4">
                        <div class="p-3 rounded-xl bg-primary-50 border flex items-center justify-between">
                            <div><p class="text-[11px] font-bold">Status</p><p class="font-bold text-sm" x-text="selected.status"></p><p class="text-[10px] text-primary-500" x-text="selected.requested_at"></p></div>
                            <span class="px-2 py-1 rounded-full text-xs font-bold" :class="selected.status==='SENT'?'bg-green-100 text-green-700':(selected.status==='FAILED'?'bg-red-100 text-red-700':'bg-amber-100 text-amber-700')" x-text="selected.status"></span>
                        </div>
                        <div><p class="text-[11px] font-bold tracking-widest text-primary-500">MESSAGE</p><p class="p-3 rounded-xl bg-gray-900 text-green-300 text-xs whitespace-pre-wrap" x-text="selected.message || '[no text]'"></p></div>
                        <template x-if="selected.attachment_name">
                            <div class="p-3 rounded-xl border bg-blue-50 flex items-center justify-between">
                                <div><p class="text-xs font-bold" x-text="selected.attachment_name"></p><p class="text-[10px] text-primary-500" x-text="selected.attachment_mime + ' • ' + (selected.attachment_size ? (selected.attachment_size/1024).toFixed(1)+' KB' : '')"></p></div>
                                <i class="fas fa-paperclip text-blue-600"></i>
                            </div>
                        </template>
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-primary-50 border"><p class="text-[10px] font-bold text-primary-500">DEVICE</p><p class="font-bold" x-text="selected.device_code"></p></div>
                            <div class="p-3 rounded-xl bg-primary-50 border"><p class="text-[10px] font-bold text-primary-500">RECIPIENT</p><p class="font-mono font-bold flex items-center gap-1"><span x-text="selected.recipient_phone"></span><button @click="copy(selected.recipient_phone)" class="w-6 h-6 rounded border bg-white flex items-center justify-center"><i class="fa-regular fa-copy text-[10px]"></i></button></p></div>
                        </div>
                        <div class="flex gap-2">
                            <a :href="'/whatsapp-app/messages/' + selected.uuid" class="flex-1 px-3 py-2 rounded-lg border text-center text-xs font-bold">Open Full Page</a>
                            <button @click="closeDrawer()" class="flex-1 px-3 py-2 rounded-lg bg-gray-900 text-white text-xs font-bold">Close</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div x-show="toast" x-transition class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-xl bg-gray-900 text-white text-xs font-bold flex items-center gap-2" style="display:none;"><i class="fa-solid fa-check text-green-400"></i><span x-text="toastMsg"></span></div>
</div>

<script>
function waDrawer(){
  return {
    drawerOpen:false, selected:null, toast:false, toastMsg:'Copied!',
    openDrawer(uuid){
      this.drawerOpen=true;
      fetch('/whatsapp-app/messages/'+uuid, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.json()).then(j=>{
          if(j.uuid){ this.selected=j; }
          else { // fallback: fetch via outbox json? use uuid search
            this.selected={uuid:uuid, recipient_phone:'...', message:'Loading...', status:'PENDING'};
            // try details endpoint
            fetch('/api/gateway/whatsapp/commands?limit=1', {headers:{'Accept':'application/json'}}).then(()=>{});
          }
        }).catch(()=>{
          // fallback to page fetch
          fetch('/whatsapp-app/messages/'+uuid, {headers:{'Accept':'text/html'}}).then(()=>{});
        });
      // simpler: use the row data already? For now fetch details via API using device? Instead fetch via outbox details endpoint we added at /whatsapp-app/messages/{uuid} with JSON if Accept json
      // To ensure it works, we will fetch the show endpoint with Accept json
      fetch('/whatsapp-app/messages/'+uuid, {headers:{'Accept':'application/json'}})
        .then(r=>r.json())
        .then(data=>{ if(data.uuid) this.selected=data; })
        .catch(()=>{});
    },
    closeDrawer(){ this.drawerOpen=false; setTimeout(()=>this.selected=null,300); },
    copy(t){ if(!t) return; navigator.clipboard.writeText(t).then(()=>{ this.toastMsg='Copied: '+t; this.toast=true; setTimeout(()=>this.toast=false,1500); }); }
  }
}
</script>
@endsection
