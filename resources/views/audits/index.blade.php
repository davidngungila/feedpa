@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 animate-fade-in" x-data="auditLogDetails()">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-history text-primary-500"></i> Audit Logs
            </h2>
            <p class="text-xs text-primary-500 mt-1">Track all user activity and system events</p>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30">
            <p class="text-[10px] text-primary-500">Click <i class="fas fa-eye"></i> on any row to view full details</p>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($audits as $audit)
                        @php
                            $createdAt = $audit->created_at ? \Illuminate\Support\Carbon::parse($audit->created_at) : null;
                            $detailPayload = [
                                'id' => $audit->id,
                                'action' => $audit->action,
                                'details' => $audit->details,
                                'ip_address' => $audit->ip_address ?? 'N/A',
                                'user_agent' => $audit->user_agent,
                                'url' => $audit->url,
                                'method' => $audit->method,
                                'created_at' => $createdAt?->toIso8601String(),
                                'date' => $createdAt?->format('d M, Y'),
                                'time' => $createdAt?->format('H:i:s'),
                                'user_name' => $audit->user?->name ?? 'Guest / System',
                                'user_email' => $audit->user?->email,
                                'country' => $audit->country,
                                'city' => $audit->city,
                                'timezone' => $audit->timezone,
                                'device_type' => $audit->device_type,
                                'device_browser' => $audit->device_browser,
                                'device_platform' => $audit->device_platform
                            ];
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="whitespace-nowrap">
                                <div class="font-bold text-primary-900 dark:text-white">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div>
                            </td>
                            <td>
                                @if($audit->user)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center overflow-hidden">
                                            <i class="fas fa-user text-primary-600 dark:text-primary-400 text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-primary-900 dark:text-white truncate">{{ $audit->user->name }}</p>
                                            <p class="text-[10px] text-primary-500">{{ $audit->user->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-primary-500 dark:text-primary-400 italic">Guest / System</span>
                                @endif
                            </td>
                            <td>
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ 
                                    $audit->action === 'login' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 
                                    ($audit->action === 'logout' ? 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300' : 
                                    ($audit->action === 'login_failed' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300')) }}">
                                    {{ ucwords(str_replace('_', ' ', $audit->action)) }}
                                </span>
                            </td>
                            <td>
                                <div class="text-xs text-primary-700 dark:text-primary-400 max-w-[220px] truncate" title="{{ $audit->details }}">
                                    {{ $audit->details }}
                                </div>
                                @if($audit->url)
                                    <p class="text-[9px] text-primary-500 mt-1 font-mono truncate" title="{{ $audit->url }}">
                                        {{ Str::limit($audit->url, 60) }}
                                    </p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                <p class="text-sm font-mono text-primary-900 dark:text-white">{{ $audit->ip_address ?? 'N/A' }}</p>
                            </td>
                            <td>
                                <div class="flex gap-2 justify-center">
                                    <button type="button"
                                            @click="openDetails(@js($detailPayload))"
                                            class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                            title="View full details">
                                        <i class="fas fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-20">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-dark-900 flex items-center justify-center mb-4">
                                        <i class="fas fa-folder-open text-2xl text-primary-200"></i>
                                    </div>
                                    <h4 class="font-bold text-primary-900 dark:text-white">No Audit Logs Found</h4>
                                    <p class="text-xs text-primary-500">No activity recorded yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($audits->hasPages())
            <div class="p-4 bg-primary-50/30 dark:bg-dark-900/30 border-t border-primary-50 dark:border-dark-border">
                {{ $audits->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Audit details modal -->
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeDetails()">
        <div class="absolute inset-0 bg-black/50" @click="closeDetails()"></div>
        <div class="relative w-full max-w-2xl card p-6 max-h-[90vh] overflow-y-auto animate-fade-in" @click.stop>
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-black text-primary-900 dark:text-white">Audit Log Details</h3>
                    <p class="text-[10px] text-primary-500 uppercase tracking-widest mt-1">Event ID: <span x-text="selected.id"></span></p>
                </div>
                <button type="button" @click="closeDetails()" class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-dark-900 text-primary-600 hover:bg-primary-100 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <template x-if="selected">
                <div class="space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl bg-primary-50/50 dark:bg-dark-900/50 border border-primary-100 dark:border-dark-border">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold uppercase text-primary-500">Action</p>
                            <p class="font-bold text-primary-900 dark:text-white" x-text="formatAction(selected.action)"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase text-primary-500">Date & Time</p>
                            <p class="text-sm font-semibold text-primary-800 dark:text-primary-200">
                                <span x-text="selected.date"></span> <span x-text="selected.time"></span>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                <i class="fas fa-user-circle"></i> User Information
                            </h4>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">User Name</p>
                                <p class="font-bold text-primary-900 dark:text-white" x-text="selected.user_name"></p>
                            </div>
                            <template x-if="selected.user_email">
                                <div>
                                    <p class="text-[10px] text-primary-500 uppercase font-bold">Email</p>
                                    <p class="text-sm" x-text="selected.user_email"></p>
                                </div>
                            </template>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">IP Address</p>
                                <p class="font-mono text-sm" x-text="selected.ip_address"></p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                <i class="fas fa-map-marker-alt"></i> Location
                            </h4>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Country</p>
                                <p class="text-sm" x-text="selected.country || 'N/A'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">City</p>
                                <p class="text-sm" x-text="selected.city || 'N/A'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Timezone</p>
                                <p class="text-sm" x-text="selected.timezone || 'N/A'"></p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                <i class="fas fa-laptop"></i> Device Information
                            </h4>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Device Type</p>
                                <p class="text-sm" x-text="selected.device_type || 'N/A'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Browser</p>
                                <p class="text-sm" x-text="selected.device_browser || 'N/A'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Platform</p>
                                <p class="text-sm" x-text="selected.device_platform || 'N/A'"></p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                <i class="fas fa-info-circle"></i> Request Details
                            </h4>
                            <template x-if="selected.method">
                                <div class="flex justify-between items-start gap-2 border-b border-primary-50 dark:border-dark-border pb-2">
                                    <span class="text-xs text-primary-500 shrink-0">Method</span>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded"
                                          :class="methodBadgeClass(selected.method)"
                                          x-text="selected.method"></span>
                                </div>
                            </template>
                            <template x-if="selected.url">
                                <div class="border-b border-primary-50 dark:border-dark-border pb-2">
                                    <p class="text-[10px] text-primary-500 uppercase font-bold mb-1">URL</p>
                                    <p class="font-mono text-xs text-primary-800 dark:text-primary-200 break-all" x-text="selected.url"></p>
                                </div>
                            </template>
                            <template x-if="selected.user_agent">
                                <div class="border-b border-primary-50 dark:border-dark-border pb-2">
                                    <p class="text-[10px] text-primary-500 uppercase font-bold mb-1">User Agent</p>
                                    <p class="font-mono text-[10px] text-primary-800 dark:text-primary-200 break-all" x-text="selected.user_agent"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] text-primary-500 uppercase font-bold mb-1">Event Details</p>
                        <p class="text-sm text-primary-800 dark:text-primary-200 bg-primary-50/50 dark:bg-dark-900/50 rounded-xl p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap" x-text="selected.details || 'No details available'"></p>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <button type="button" @click="closeDetails()" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-dark-border text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-200 transition-all">
                            Close
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
function auditLogDetails() {
    return {
        open: false,
        selected: null,
        openDetails(payload) {
            this.selected = payload;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        closeDetails() {
            this.open = false;
            this.selected = null;
            document.body.style.overflow = '';
        },
        formatAction(action) {
            return String(action || '').split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },
        methodBadgeClass(method) {
            const m = String(method || '').toUpperCase();
            if (['GET'].includes(m)) return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300';
            if (['POST', 'PUT', 'PATCH'].includes(m)) return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
            if (['DELETE'].includes(m)) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
            return 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300';
        }
    };
}
</script>
@endpush
