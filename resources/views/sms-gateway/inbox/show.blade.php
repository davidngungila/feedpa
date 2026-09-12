@extends('layouts.app')
@section('title', 'SMS Detail')

@section('content')
<div class="space-y-4 max-w-5xl mx-auto">
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
                    @if($sms->is_recorded)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold"><i class="fa-solid fa-check"></i> Recorded</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold"><i class="fa-solid fa-clock"></i> Not recorded</span>
                    @endif
                </div>
                <div class="mt-4 p-4 rounded-xl bg-primary-50 border border-primary-100">
                    <p class="text-sm leading-relaxed whitespace-pre-wrap break-words">{{ $sms->body }}</p>
                </div>
                @php
                    $kumb=null; $namba=null; $risiti=null;
                    if(preg_match('/Kumbukumbu\s*(?:no\.?|namba)?\s*[:\.\s]*([A-Za-z0-9\-]{5,30})/iu', $sms->body, $m)) $kumb=trim($m[1], " .-");
                    if(preg_match('/Namba ya muamala\s*[:\s]*([A-Za-z0-9\-]{5,30})/iu', $sms->body, $m)) $namba=trim($m[1], " .-");
                    if(preg_match('/Risiti\s*[:\s]*([A-Za-z0-9\-\.]{5,30})/iu', $sms->body, $m)) $risiti=trim($m[1], " .-");
                    if(!$kumb && $sms->smsTransaction && $sms->smsTransaction->reference) $kumb=$sms->smsTransaction->reference;
                @endphp
                @if($kumb)
                    <div class="mt-3 inline-flex items-center gap-2 px-3 py-2 rounded-full bg-amber-100 border border-amber-200">
                        <span class="text-[11px] font-bold text-amber-800">Kumbukumbu: <span class="font-mono">{{ $kumb }}</span></span>
                        <button onclick="copyRefShow('{{ $kumb }}')" title="Copy Kumbukumbu" class="w-6 h-6 rounded-full bg-white border border-amber-300 flex items-center justify-center text-amber-700 hover:bg-amber-50">
                            <i class="fa-regular fa-copy text-[10px]"></i>
                        </button>
                    </div>
                @endif
                @if($namba)
                    <div class="mt-2 inline-flex items-center gap-2 px-3 py-2 rounded-full bg-blue-50 border border-blue-200">
                        <span class="text-[11px] font-bold text-blue-800">Namba ya muamala: <span class="font-mono">{{ $namba }}</span></span>
                        <button onclick="copyRefShow('{{ $namba }}')" title="Copy Namba ya muamala" class="w-6 h-6 rounded-full bg-white border border-blue-300 flex items-center justify-center text-blue-700 hover:bg-blue-50">
                            <i class="fa-regular fa-copy text-[10px]"></i>
                        </button>
                    </div>
                @endif
                @if($risiti)
                    <div class="mt-2 inline-flex items-center gap-2 px-3 py-2 rounded-full bg-emerald-50 border border-emerald-200">
                        <span class="text-[11px] font-bold text-emerald-800">Risiti: <span class="font-mono">{{ $risiti }}</span></span>
                        <button onclick="copyRefShow('{{ $risiti }}')" title="Copy Risiti" class="w-6 h-6 rounded-full bg-white border border-emerald-300 flex items-center justify-center text-emerald-700 hover:bg-emerald-50">
                            <i class="fa-regular fa-copy text-[10px]"></i>
                        </button>
                    </div>
                @endif
                <div class="mt-3 grid grid-cols-3 gap-3 text-xs">
                    <div><span class="text-primary-500">Provider</span><p class="font-bold">{{ $sms->provider->name ?? $sms->parsed_data['provider_code'] ?? '—' }}</p></div>
                    <div><span class="text-primary-500">Hash</span><p class="font-mono text-[10px] break-all">{{ $sms->hash }}</p></div>
                    <div><span class="text-primary-500">Sync</span><p class="font-bold">{{ $sms->sync_status }} / {{ $sms->processing_status }}</p></div>
                </div>
                @if($sms->is_recorded)
                    <p class="mt-2 text-[11px] text-green-600">Recorded by {{ $sms->recordedBy?->name ?? '—' }} at {{ $sms->recorded_at ?? '—' }}</p>
                @endif
                @if(auth()->user()->is_admin)
                    <form method="POST" action="{{ route('sms-gateway.sms.reparse', $sms) }}" class="mt-3">
                        @csrf
                        <button class="px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-[11px] font-bold hover:bg-amber-100"><i class="fa-solid fa-rotate me-1"></i> Re-parse (fix Swahili amount/ref)</button>
                    </form>
                @endif
            </div>

            @if($sms->smsTransaction)
            <div class="card p-5">
                <h3 class="text-sm font-bold text-primary-900">Extracted Transaction</h3>
                <div class="mt-3 grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-xs text-primary-500">Amount</span><p class="font-bold text-lg">TZS {{ $sms->smsTransaction->amount ? number_format($sms->smsTransaction->amount,0) : '—' }} <span class="text-xs font-normal">{{ $sms->smsTransaction->currency }}</span></p></div>
                    <div><span class="text-xs text-primary-500">Type</span><p class="font-bold"><span class="badge badge-green">{{ $sms->smsTransaction->transaction_type }}</span></p></div>
                    <div>
                        <span class="text-xs text-primary-500">Reference / Txn ID</span>
                        <p class="font-mono font-bold flex items-center gap-1.5">
                            <span id="refText">{{ $sms->smsTransaction->reference ?? '—' }}</span>
                            @if($sms->smsTransaction && $sms->smsTransaction->reference)
                                <button onclick="copyRefShow('{{ $sms->smsTransaction->reference }}')" title="Copy reference" class="w-6 h-6 rounded-md bg-white border border-primary-200 hover:bg-primary-50 flex items-center justify-center text-primary-600">
                                    <i class="fa-regular fa-copy text-[10px]"></i>
                                </button>
                            @endif
                        </p>
                    </div>
                    <div><span class="text-xs text-primary-500">Counterparty</span><p class="font-bold">{{ $sms->smsTransaction->counterparty ?? '—' }}</p></div>
                    <div><span class="text-xs text-primary-500">Recipient Name</span><p class="font-bold">{{ $sms->smsTransaction->counterparty_name ?? '—' }}</p></div>
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
            <!-- Recorded / Comment Card (replaces reconciliation) -->
            <div class="card p-4 space-y-4">
                <h4 class="text-sm font-bold text-primary-900 flex items-center gap-2"><i class="fa-solid fa-clipboard-check text-primary-600"></i> Recorded & Comment</h4>

                <form method="POST" action="{{ route('sms-gateway.sms.recorded', $sms) }}" class="p-3 rounded-xl border-2 {{ $sms->is_recorded ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200' }}">
                    @csrf
                    <input type="hidden" name="is_recorded" value="{{ $sms->is_recorded ? 0 : 1 }}">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold {{ $sms->is_recorded ? 'text-green-800' : 'text-amber-800' }}">{{ $sms->is_recorded ? '✓ Recorded' : 'Not recorded' }}</p>
                            <p class="text-[11px] {{ $sms->is_recorded ? 'text-green-600' : 'text-amber-600' }}">{{ $sms->is_recorded ? 'By '.($sms->recordedBy?->name ?? '—').' at '.($sms->recorded_at ?? '') : 'Mark when entered into books' }}</p>
                        </div>
                        <button class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $sms->is_recorded ? 'bg-amber-500 text-white' : 'bg-green-600 text-white' }}">{{ $sms->is_recorded ? 'Mark Not Recorded' : 'Mark Recorded' }}</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('sms-gateway.sms.comment', $sms) }}" class="space-y-2">
                    @csrf
                    <label class="text-[11px] font-bold tracking-widest text-primary-500">ADD / EDIT COMMENT</label>
                    <textarea name="admin_comment" rows="4" placeholder="e.g. Recorded in ledger INV-00127, pending verification..." class="w-full px-3 py-2.5 rounded-xl border border-primary-200 text-xs outline-none focus:ring-2 focus:ring-primary-500">{{ old('admin_comment', $sms->admin_comment) }}</textarea>
                    @error('admin_comment')<p class="text-[11px] text-red-500 font-bold">{{ $message }}</p>@enderror
                    <button class="w-full px-3 py-2 rounded-lg bg-primary-600 text-white text-xs font-bold hover:bg-primary-700">Save Comment</button>
                    @if($sms->admin_comment)
                        <div class="p-3 rounded-lg bg-primary-50 border border-primary-100">
                            <p class="text-xs text-primary-700 whitespace-pre-wrap">{{ $sms->admin_comment }}</p>
                            <p class="text-[10px] text-primary-400 mt-1">By {{ $sms->commentBy?->name ?? '—' }} at {{ $sms->commented_at ?? '' }}</p>
                        </div>
                    @endif
                </form>
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
    <!-- Copy toast for show page -->
    <div id="copyToastShow" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 rounded-xl bg-gray-900 text-white text-xs font-bold shadow-xl hidden items-center gap-2">
        <i class="fa-solid fa-check text-green-400"></i><span id="copyToastShowMsg">Copied!</span>
    </div>
</div>

@push('scripts')
<script>
function copyRefShow(text){
    if(!text || text==='—') return;
    const showToast=(msg)=>{
        const t=document.getElementById('copyToastShow'); const m=document.getElementById('copyToastShowMsg');
        if(!t||!m) return; m.textContent=msg; t.classList.remove('hidden'); t.classList.add('flex');
        setTimeout(()=>{ t.classList.add('hidden'); t.classList.remove('flex'); },1800);
    };
    if(navigator.clipboard && navigator.clipboard.writeText){
        navigator.clipboard.writeText(text).then(()=> showToast('Copied: ' + text));
    } else {
        const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
        showToast('Copied: ' + text);
    }
}
</script>
@endpush
@endsection
