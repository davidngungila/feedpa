@extends('layouts.app')

@section('title', 'Message Logs - Session ' . $id)

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-scroll text-primary-500"></i> Message Logs
            </h2>
            <p class="text-xs text-primary-500 mt-1">
                Session {{ $session['name'] ?? ('#' . $id) }}
                @if(!empty($session['phone_number'])) ({{ $session['phone_number'] }}) @endif
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('whatsapp.sessions.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-arrow-left mr-1"></i> Back to Sessions
            </a>
        </div>
    </div>

    @if(!$personalTokenConfigured)
        <div class="card p-4 border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle mr-1"></i> The WhatsApp Personal Access Token is not configured.
                <a href="{{ route('settings.whatsapp') }}" class="underline">Configure it in WhatsApp settings</a> to load message logs.
            </p>
        </div>
    @endif

    @if($error)
        <div class="card p-4 border-l-4 border-l-red-500 bg-red-50/60 dark:bg-red-900/10">
            <p class="text-xs font-bold text-red-700 dark:text-red-300">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ $error }}
                <span class="font-normal mt-0.5 block text-red-600 dark:text-red-400">Message logging must be enabled for the session in Wasender settings.</span>
            </p>
        </div>
    @endif

    <!-- Logs Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-primary-100 dark:border-dark-border">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-list"></i> Messages sent via API
                @if($paginator)
                    <span class="ml-auto text-[10px] font-bold text-primary-400">{{ $paginator->total() }} total</span>
                @endif
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">To</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Content</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Sent At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($items as $log)
                        @php
                            $decoded = json_decode((string) ($log['content'] ?? ''), true);
                            if (is_array($decoded)) {
                                $text = $decoded['text'] ?? trim(implode(', ', array_filter([
                                    $decoded['imageUrl'] ?? null,
                                    $decoded['videoUrl'] ?? null,
                                    $decoded['audioUrl'] ?? null,
                                    $decoded['documentUrl'] ?? null,
                                    $decoded['stickerUrl'] ?? null,
                                ])));
                                if ($text === '' && $decoded) {
                                    $text = 'Media message (' . implode(', ', array_keys($decoded)) . ')';
                                }
                            } else {
                                $text = (string) ($log['content'] ?? '');
                            }
                            $status = strtolower((string) ($log['status'] ?? 'unknown'));
                            $statusClass = match($status) {
                                'sent' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                'failed' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                'in_progress', 'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                                default => 'bg-gray-100 text-primary-500 dark:bg-gray-800',
                            };
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono">{{ $log['id'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono break-all">{{ $log['to'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 max-w-md line-clamp-2">{{ $text ?: '—' }}</p>
                                @if(!empty($log['failed_reason']))
                                    <p class="text-[10px] text-red-600 dark:text-red-400 mt-0.5">Reason: {{ $log['failed_reason'] }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ \Illuminate\Support\Str::limit($log['created_at'] ?? '—', 19) }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <i class="fas fa-scroll text-4xl text-primary-300 mb-3 block"></i>
                                <p class="text-sm font-bold text-primary-500">No message logs found</p>
                                <p class="text-xs text-primary-400 mt-1">Enable message logging for the session in Wasender settings to capture logs.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($paginator && $paginator->hasPages())
            <div class="p-6 border-t border-primary-100 dark:border-dark-border">
                {{ $paginator->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
