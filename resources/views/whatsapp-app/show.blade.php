@extends('layouts.app')
@section('title', 'WhatsApp Request')

@section('content')
<div class="max-w-4xl mx-auto space-y-4">
    <a href="{{ route('whatsapp-app.outbox') }}" class="text-xs font-bold text-primary-600 hover:underline">← Back to Outbox</a>
    <div class="card p-6 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold tracking-widest text-primary-500">UUID {{ $msg->uuid }}</p>
                <p class="text-sm font-bold mt-1">To: <span class="font-mono">{{ $msg->recipient_phone }}</span> via <span class="font-bold">{{ $msg->device?->device_code ?? '—' }}</span></p>
                <p class="text-xs text-primary-500">{{ $msg->requested_at }} • by {{ $msg->creator?->name ?? '—' }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $msg->status==='SENT'?'bg-green-100 text-green-700':($msg->status==='FAILED'?'bg-red-100 text-red-700':'bg-amber-100 text-amber-700') }}">{{ $msg->status }}</span>
        </div>
        <div class="p-4 rounded-xl bg-gray-900 text-green-300 text-sm whitespace-pre-wrap">{{ $msg->message ?? '[no text]' }}</div>
        @if($msg->attachment_path)
            <div class="p-4 rounded-xl border bg-blue-50 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold">Attachment: {{ $msg->attachment_name }}</p>
                    <p class="text-[11px] text-primary-500">{{ $msg->attachment_mime }} • {{ $msg->attachment_size ? round($msg->attachment_size/1024,1).' KB' : '' }}</p>
                </div>
                <span class="text-xs font-mono bg-white px-2 py-1 rounded border">{{ $msg->attachment_path }}</span>
            </div>
        @endif
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-primary-50 border"><p class="text-[10px] font-bold text-primary-500">RECEIVED BY DEVICE</p><p class="font-bold">{{ $msg->received_by_device_at ?? '—' }}</p></div>
            <div class="p-3 rounded-xl bg-primary-50 border"><p class="text-[10px] font-bold text-primary-500">OPENED WHATSAPP</p><p class="font-bold">{{ $msg->opened_whatsapp_at ?? '—' }}</p></div>
            <div class="p-3 rounded-xl bg-green-50 border border-green-200"><p class="text-[10px] font-bold text-green-700">SENT AT</p><p class="font-bold">{{ $msg->sent_at ?? '—' }}</p></div>
            <div class="p-3 rounded-xl bg-red-50 border border-red-200"><p class="text-[10px] font-bold text-red-700">FAILED</p><p class="font-bold">{{ $msg->failed_at ?? '—' }} {{ $msg->failure_reason }}</p></div>
        </div>
        @if(!in_array($msg->status, ['SENT','FAILED','CANCELLED','EXPIRED']))
            <form method="POST" action="{{ route('whatsapp-app.cancel', $msg) }}" onsubmit="return confirm('Cancel this request?')">@csrf<button class="w-full py-2 rounded-xl border border-red-200 text-red-600 text-xs font-bold">Cancel Request</button></form>
        @endif
        <div>
            <h4 class="text-xs font-bold mb-2">Logs</h4>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @forelse($msg->logs as $log)
                    <div class="p-2 rounded-lg border bg-primary-50/50 text-xs"><p class="font-bold">{{ $log->action }} • {{ $log->created_at->format('H:i:s') }} @if($log->device) via {{ $log->device->device_code }} @endif</p><p class="text-primary-600">{{ $log->details }}</p></div>
                @empty
                    <p class="text-xs text-primary-400">No logs</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
