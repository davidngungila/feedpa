@extends('layouts.app')
@section('title', 'WhatsApp Logs')

@section('content')
<div class="max-w-4xl mx-auto space-y-4">
    <a href="{{ route('whatsapp-app.show', $whatsappMessage) }}" class="text-xs font-bold text-primary-600 hover:underline">← Back to Request</a>
    <h1 class="text-lg font-bold">Logs for {{ $whatsappMessage->uuid }}</h1>
    <div class="card p-4">
        <p class="text-xs text-primary-500">Recipient: <span class="font-mono font-bold">{{ $whatsappMessage->recipient_phone }}</span> • Device: {{ $whatsappMessage->device?->device_code ?? '—' }}</p>
    </div>
    <div class="card overflow-hidden">
        <div class="divide-y">
            @forelse($logs as $log)
                <div class="p-3 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold">{{ $log->action }} @if($log->device) <span class="text-primary-500">via {{ $log->device->device_code }}</span> @endif</p>
                        <p class="text-xs text-primary-600">{{ $log->details }}</p>
                        @if($log->meta)<pre class="mt-1 p-2 rounded bg-gray-900 text-green-300 text-[10px] overflow-x-auto">{{ json_encode($log->meta, JSON_PRETTY_PRINT) }}</pre>@endif
                    </div>
                    <span class="text-[10px] text-primary-400 whitespace-nowrap">{{ $log->created_at->format('H:i:s') }}<br>{{ $log->created_at->format('Y-m-d') }}</span>
                </div>
            @empty
                <p class="p-8 text-center text-xs text-primary-400">No logs</p>
            @endforelse
        </div>
        <div class="p-4">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
