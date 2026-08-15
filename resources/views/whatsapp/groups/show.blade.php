@extends('layouts.app')

@section('title', $group['name'] . ' - Group Details')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fab fa-whatsapp text-primary-500"></i> {{ $group['name'] }}
            </h2>
            <p class="text-xs text-primary-500 mt-1 font-mono break-all">{{ $group['jid'] }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('whatsapp.groups.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-arrow-left mr-1"></i> Back to Groups
            </a>
        </div>
    </div>

    <!-- Group Info -->
    @if(!empty($metaError))
        <div class="p-4 rounded-xl border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle mr-1"></i> Could not load group metadata from the WhatsApp API: {{ $metaError }}
            </p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="card p-6 lg:col-span-1">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center overflow-hidden">
                    @if($group['img_url'])
                        <img src="{{ $group['img_url'] }}" alt="{{ $group['name'] }}" class="w-full h-full object-cover">
                    @else
                        <i class="fas fa-users text-2xl text-primary-600 dark:text-primary-400"></i>
                    @endif
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-primary-900 dark:text-white break-all">{{ $group['name'] }}</h3>
                    <p class="text-[10px] text-primary-500 font-mono break-all">{{ $group['jid'] }}</p>
                </div>
            </div>
            <h4 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
                <i class="fas fa-info-circle"></i> Group Information
            </h4>
            <div class="space-y-3 text-xs">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Description</p>
                    <p class="text-primary-700 dark:text-primary-300">{{ $group['description'] ?: 'No description' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Owner</p>
                    <p class="text-primary-700 dark:text-primary-300 font-mono break-all">{{ $group['owner'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Created</p>
                    <p class="text-primary-700 dark:text-primary-300">{{ $group['creation'] ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Members</p>
                    <p class="text-primary-700 dark:text-primary-300">{{ count($group['participants']) }}</p>
                </div>
            </div>
        </div>

        <!-- Participants -->
        <div class="card overflow-hidden lg:col-span-2">
            <div class="p-6 border-b border-primary-100 dark:border-dark-border">
                <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                    <i class="fas fa-users"></i> Participants ({{ count($group['participants']) }})
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-primary-50 dark:bg-primary-900/20">
                        <tr>
                            <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Name / JID</th>
                            <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Phone</th>
                            <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                        @forelse($group['participants'] as $participant)
                            <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                                <td class="px-6 py-3">
                                    <p class="text-xs font-bold text-primary-900 dark:text-white">{{ $participant['name'] ?? $participant['pn'] ?? 'Unknown' }}</p>
                                    <p class="text-[10px] text-primary-500 font-mono break-all">{{ $participant['jid'] ?? $participant['id'] ?? '' }}</p>
                                </td>
                                <td class="px-6 py-3">
                                    <p class="text-xs text-primary-700 dark:text-primary-300 font-mono">{{ $participant['pn'] ?? '—' }}</p>
                                </td>
                                <td class="px-6 py-3">
                                    @if(!empty($participant['isSuperAdmin']))
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300">
                                            <i class="fas fa-crown mr-1"></i> Super Admin
                                        </span>
                                    @elseif(!empty($participant['isAdmin']))
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                            <i class="fas fa-shield-alt mr-1"></i> Admin
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-primary-500 dark:bg-gray-800">Member</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center">
                                    <p class="text-xs text-primary-500">No participants synced. Reconnect the session if the list is empty.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Participants -->
    <div class="card p-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2 mb-4">
            <i class="fas fa-user-plus"></i> Add Group Participants
        </h3>
        <p class="text-[11px] text-primary-500 mb-4">Add participants by international phone number (E.164, e.g. <span class="font-mono">255784510488</span>). Admin privileges are required in the group.</p>
        <form id="addParticipantsForm" action="{{ route('whatsapp.groups.add-participants', $group['jid']) }}" method="POST">
            @csrf
            <div class="flex flex-col md:flex-row gap-3">
                <input type="text" name="participants" id="participantsInput" placeholder="255712345678, 255712345679" required
                    class="flex-1 w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
                <button type="submit" id="addParticipantsBtn" class="px-6 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                    <i class="fas fa-user-plus mr-1"></i> Add Participants
                </button>
            </div>
        </form>
        <div id="addParticipantsResult" class="mt-4 hidden"></div>
    </div>

    <!-- Message Logs for this group -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-primary-100 dark:border-dark-border flex items-center justify-between">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-scroll"></i> Message Logs for this Group
            </h3>
            @if($personalTokenConfigured && $session)
                <a href="{{ route('whatsapp.sessions.message-logs', $session['id']) }}" class="text-[10px] font-bold text-primary-600 dark:text-primary-300 hover:underline">
                    View all session logs <i class="fas fa-arrow-right ml-1"></i>
                </a>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">To</th>
                        <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Content</th>
                        <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Sent At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($messageLogs as $log)
                        @php
                            $decoded = json_decode((string) ($log['content'] ?? ''), true);
                            $text = is_array($decoded) ? ($decoded['text'] ?? json_encode($decoded)) : (string) ($log['content'] ?? '');
                            $status = strtolower((string) ($log['status'] ?? 'unknown'));
                            $statusClass = match($status) {
                                'sent' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                'failed' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                'in_progress', 'pending' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                                default => 'bg-gray-100 text-primary-500 dark:bg-gray-800',
                            };
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="px-6 py-3">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono break-all">{{ $log['to'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-xs text-primary-700 dark:text-primary-300 max-w-md line-clamp-2">{{ $text }}</p>
                                @if($log['failed_reason'])
                                    <p class="text-[10px] text-red-600 dark:text-red-400 mt-0.5">Reason: {{ $log['failed_reason'] }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ \Illuminate\Support\Str::limit($log['created_at'] ?? '—', 19) }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <i class="fas fa-scroll text-3xl text-primary-300 mb-3 block"></i>
                                @if(!$personalTokenConfigured)
                                    <p class="text-sm font-bold text-primary-500">Message logs unavailable</p>
                                    <p class="text-xs text-primary-400 mt-1">Configure the Personal Access Token in WhatsApp settings and enable message logging for the session.</p>
                                @elseif($logsError)
                                    <p class="text-sm font-bold text-primary-500">Could not load message logs</p>
                                    <p class="text-xs text-primary-400 mt-1">{{ $logsError }}</p>
                                @else
                                    <p class="text-sm font-bold text-primary-500">No messages sent to this group</p>
                                    <p class="text-xs text-primary-400 mt-1">Messages sent via the API to this group will appear here.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('addParticipantsForm');
        const resultBox = document.getElementById('addParticipantsResult');
        const btn = document.getElementById('addParticipantsBtn');

        if (form && resultBox && btn) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Adding...';

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: new FormData(form),
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function (result) {
                    const data = result.data;
                    resultBox.classList.remove('hidden');

                    if (data.success && Array.isArray(data.data)) {
                        const rows = data.data.map(function (item) {
                            const ok = item.status === 200;
                            const cls = ok
                                ? 'border-green-200 dark:border-green-800 bg-green-50/60 dark:bg-green-900/10'
                                : 'border-amber-200 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/10';
                            const badgeCls = ok ? 'badge-green' : 'badge-amber';
                            return '<div class="flex items-center justify-between gap-3 p-3 rounded-xl border ' + cls + '">' +
                                '<div class="min-w-0">' +
                                    '<p class="text-xs font-bold text-primary-900 dark:text-white font-mono">' + (item.jid || '') + '</p>' +
                                    '<p class="text-[10px] text-primary-500 mt-0.5">' + (item.message || '') + '</p>' +
                                '</div>' +
                                '<span class="badge ' + badgeCls + ' whitespace-nowrap">' + (ok ? 'Added' : 'Not added') + '</span>' +
                            '</div>';
                        }).join('');

                        resultBox.innerHTML = '<div class="space-y-2">' + rows + '</div>';
                    } else {
                        resultBox.innerHTML = '<div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-900/10 border border-red-200 dark:border-red-800 text-xs font-bold text-red-700 dark:text-red-300">' +
                            '<i class="fas fa-exclamation-circle mr-1"></i>' + (data.message || 'Failed to add participants.') + '</div>';
                    }
                })
                .catch(function () {
                    resultBox.classList.remove('hidden');
                    resultBox.innerHTML = '<div class="p-3 rounded-xl bg-red-50/60 dark:bg-red-900/10 border border-red-200 dark:border-red-800 text-xs font-bold text-red-700 dark:text-red-300">' +
                        '<i class="fas fa-exclamation-circle mr-1"></i> Network error. Please try again.</div>';
                })
                .finally(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                });
            });
        }
    });
</script>
@endpush
