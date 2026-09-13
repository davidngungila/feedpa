@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="space-y-6" x-data="auditLogDetails()" @keydown.escape.window="closeDetails()">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-history text-primary-500"></i> Audit Logs
            </h2>
            <p class="text-xs text-primary-500 mt-1">Track all user activity and system events</p>
        </div>
        <form id="bulk-delete-form" action="{{ route('audits.bulk-destroy') }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="ids" id="bulk-delete-ids">
        </form>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-circle-check me-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Filters Card -->
    <div x-data="{ showFilters: false }" class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-sm text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-filter text-primary-500"></i> Advanced Filters
            </h3>
            <button @click="showFilters = !showFilters" class="text-xs text-primary-600 font-bold hover:underline">
                <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
            </button>
        </div>
        
        <form x-show="showFilters" x-transition method="GET" action="{{ route('audits.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-primary-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Search by user, action, IP...">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-primary-500 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-primary-500 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-500 text-white py-2 rounded-lg text-xs font-bold transition-all">
                    Apply Filter
                </button>
                <a href="{{ route('audits.index') }}" class="px-3 py-2 bg-gray-100 dark:bg-dark-border rounded-lg text-xs text-gray-600 dark:text-gray-300 hover:bg-gray-200 transition-all">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Export & Bulk Actions Card -->
    <div x-data="{ showExport: false }" class="card p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-4">
                <h3 class="font-bold text-sm text-primary-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-file-export text-primary-500"></i> Actions
                </h3>
                
                <button type="button" id="bulk-delete-btn" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-xs font-bold transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled @click="confirmBulkDelete()">
                    <i class="fas fa-trash-alt me-1"></i> Delete Selected
                </button>
            </div>
            
            <button @click="showExport = !showExport" class="text-xs text-primary-600 font-bold hover:underline">
                <span x-text="showExport ? 'Hide Export Options' : 'Show Export Options'"></span>
            </button>
        </div>

        <form x-show="showExport" x-transition method="GET" action="{{ route('audits.export.pdf') }}" class="mt-4 space-y-4">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-xs font-bold transition-all">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </button>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table - minimal columns, fully responsive -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <p class="text-[11px] font-semibold text-primary-700 dark:text-primary-300 flex items-center gap-1.5"><i class="fas fa-hand-pointer text-primary-500"></i> Click any row to open right drawer — full details, copy IDs, device & location</p>
            <span class="text-[10px] text-primary-500 hidden lg:inline">Tip: Horizontal scroll on desktop • Cards on mobile</span>
        </div>

        <!-- Desktop / Tablet Table — minimized to important columns only, rest in drawer -->
        <div class="hidden md:block overflow-x-auto">
            <table class="data-table min-w-[640px]">
                <thead>
                    <tr>
                        <th class="w-10">
                            <input type="checkbox" id="select-all" class="rounded border-primary-200 text-primary-600 focus:ring-primary-500" @click="toggleSelectAll()">
                        </th>
                        <th class="whitespace-nowrap">Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Target</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($audits as $audit)
                        @php
                            $createdAt = $audit->created_at ? \Illuminate\Support\Carbon::parse($audit->created_at) : null;
                            // Derive model / target label from action + details (audit table has no auditable_type/auditable_id columns)
                            $actionLower = strtolower($audit->action ?? '');
                            $derivedModel = 'N/A';
                            if (str_contains($actionLower, 'beneficiary')) $derivedModel = 'Beneficiary';
                            elseif (str_contains($actionLower, 'payout')) $derivedModel = 'Payout';
                            elseif (str_contains($actionLower, 'bill')) $derivedModel = 'Bill';
                            elseif (str_contains($actionLower, 'payment') || str_contains($actionLower, 'transaction')) $derivedModel = 'Transaction';
                            elseif (str_contains($actionLower, 'user')) $derivedModel = 'User';
                            elseif (str_contains($actionLower, 'login') || str_contains($actionLower, 'logout') || str_contains($actionLower, '2fa')) $derivedModel = 'Auth';
                            elseif (str_contains($actionLower, 'audit')) $derivedModel = 'Audit';
                            // Try to extract numeric target id from details if present (fallback N/A)
                            $derivedTargetId = null;
                            if (preg_match('/#\s?(\d+)/', $audit->details ?? '', $m)) $derivedTargetId = $m[1];
                            elseif (preg_match('/\bID\s*[:#]?\s*(\d+)/i', $audit->details ?? '', $m)) $derivedTargetId = $m[1];
                            // Attempt to decode details as JSON for old/new values (most logs are plain strings)
                            $detailsDecoded = null;
                            $oldValues = null;
                            $newValues = null;
                            $detailsTrim = trim($audit->details ?? '');
                            if ($detailsTrim !== '' && ($detailsTrim[0] === '{' || $detailsTrim[0] === '[')) {
                                $tmp = json_decode($detailsTrim, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
                                    $detailsDecoded = $tmp;
                                    $oldValues = $tmp['old'] ?? $tmp['old_values'] ?? null;
                                    $newValues = $tmp['new'] ?? $tmp['new_values'] ?? $tmp['attributes'] ?? null;
                                }
                            }
                            $targetLabel = $audit->details ? \Illuminate\Support\Str::limit($audit->details, 72) : '—';
                            $detailPayload = [
                                'id' => $audit->id,
                                'action' => $audit->action,
                                'action_label' => ucwords(str_replace('_', ' ', $audit->action ?? '')),
                                'details' => $audit->details,
                                'details_decoded' => $detailsDecoded,
                                'ip_address' => $audit->ip_address ?? 'N/A',
                                'user_agent' => $audit->user_agent,
                                'url' => $audit->url,
                                'method' => $audit->method,
                                'created_at' => $createdAt?->toIso8601String(),
                                'date' => $createdAt?->format('d M, Y'),
                                'time' => $createdAt?->format('H:i:s'),
                                'date_short' => $createdAt?->format('M d, Y'),
                                'user_name' => $audit->user?->name ?? 'Guest / System',
                                'user_email' => $audit->user?->email,
                                'country' => $audit->country,
                                'city' => $audit->city,
                                'timezone' => $audit->timezone,
                                'device_type' => $audit->device_type,
                                'device_browser' => $audit->device_browser,
                                'device_platform' => $audit->device_platform,
                                'model' => $derivedModel,
                                'target_id' => $derivedTargetId ?? 'N/A',
                                'target_label' => $targetLabel,
                                'old_values' => $oldValues,
                                'new_values' => $newValues,
                            ];
                        @endphp
                        <tr @click="openDetails(@js($detailPayload))" class="hover:bg-primary-50/70 dark:hover:bg-primary-900/10 transition-colors cursor-pointer group">
                            <td @click.stop>
                                <input type="checkbox" class="audit-checkbox rounded border-primary-200 text-primary-600 focus:ring-primary-500" data-id="{{ $audit->id }}" @click.stop="updateBulkDeleteBtn()">
                            </td>
                            <td class="whitespace-nowrap py-3.5 px-4">
                                <div class="font-bold text-primary-900 dark:text-white text-xs">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-3">
                                @if($audit->user)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center overflow-hidden shrink-0">
                                            <i class="fas fa-user text-primary-600 dark:text-primary-400 text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-primary-900 dark:text-white truncate max-w-[160px]">{{ $audit->user->name }}</p>
                                            <p class="text-[10px] text-primary-500 truncate max-w-[160px]">{{ $audit->user->email }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-primary-500 dark:text-primary-400 italic">Guest / System</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border" :class="actionBadgeClass('{{ $audit->action }}')">
                                    {{ ucwords(str_replace('_', ' ', $audit->action)) }}
                                </span>
                                <div class="text-[10px] font-mono text-primary-500 mt-1">#{{ $audit->id }}</div>
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="text-xs text-primary-700 dark:text-primary-300 max-w-[260px] truncate font-medium" title="{{ $audit->details }}">{{ $targetLabel }}</div>
                                <div class="text-[10px] text-primary-500 truncate max-w-[260px] flex items-center gap-1"><span class="font-bold uppercase tracking-widest">{{ $derivedModel }}</span><span class="opacity-50">•</span><span class="font-mono">{{ $derivedTargetId ? '#'.$derivedTargetId : '—' }}</span></div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-20">
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

        <!-- Mobile Cards - fully responsive fallback -->
        <div class="md:hidden divide-y divide-primary-50 dark:divide-dark-border">
            @forelse($audits as $audit)
                @php
                    $createdAt = $audit->created_at ? \Illuminate\Support\Carbon::parse($audit->created_at) : null;
                    $actionLower = strtolower($audit->action ?? '');
                    $derivedModel = 'N/A';
                    if (str_contains($actionLower, 'beneficiary')) $derivedModel = 'Beneficiary';
                    elseif (str_contains($actionLower, 'payout')) $derivedModel = 'Payout';
                    elseif (str_contains($actionLower, 'bill')) $derivedModel = 'Bill';
                    elseif (str_contains($actionLower, 'payment') || str_contains($actionLower, 'transaction')) $derivedModel = 'Transaction';
                    elseif (str_contains($actionLower, 'user')) $derivedModel = 'User';
                    elseif (str_contains($actionLower, 'login') || str_contains($actionLower, 'logout') || str_contains($actionLower, '2fa')) $derivedModel = 'Auth';
                    $derivedTargetId = null;
                    if (preg_match('/#\s?(\d+)/', $audit->details ?? '', $m)) $derivedTargetId = $m[1];
                    elseif (preg_match('/\bID\s*[:#]?\s*(\d+)/i', $audit->details ?? '', $m)) $derivedTargetId = $m[1];
                    $detailsDecoded = null; $oldValues = null; $newValues = null;
                    $detailsTrim = trim($audit->details ?? '');
                    if ($detailsTrim !== '' && ($detailsTrim[0] === '{' || $detailsTrim[0] === '[')) {
                        $tmp = json_decode($detailsTrim, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
                            $detailsDecoded = $tmp;
                            $oldValues = $tmp['old'] ?? $tmp['old_values'] ?? null;
                            $newValues = $tmp['new'] ?? $tmp['new_values'] ?? $tmp['attributes'] ?? null;
                        }
                    }
                    $targetLabel = $audit->details ? \Illuminate\Support\Str::limit($audit->details, 72) : '—';
                    $detailPayload = [
                        'id' => $audit->id,
                        'action' => $audit->action,
                        'action_label' => ucwords(str_replace('_', ' ', $audit->action ?? '')),
                        'details' => $audit->details,
                        'details_decoded' => $detailsDecoded,
                        'ip_address' => $audit->ip_address ?? 'N/A',
                        'user_agent' => $audit->user_agent,
                        'url' => $audit->url,
                        'method' => $audit->method,
                        'created_at' => $createdAt?->toIso8601String(),
                        'date' => $createdAt?->format('d M, Y'),
                        'time' => $createdAt?->format('H:i:s'),
                        'date_short' => $createdAt?->format('M d, Y'),
                        'user_name' => $audit->user?->name ?? 'Guest / System',
                        'user_email' => $audit->user?->email,
                        'country' => $audit->country,
                        'city' => $audit->city,
                        'timezone' => $audit->timezone,
                        'device_type' => $audit->device_type,
                        'device_browser' => $audit->device_browser,
                        'device_platform' => $audit->device_platform,
                        'model' => $derivedModel,
                        'target_id' => $derivedTargetId ?? 'N/A',
                        'target_label' => $targetLabel,
                        'old_values' => $oldValues,
                        'new_values' => $newValues,
                    ];
                    $isLogin = $audit->action === 'login';
                    $isFailed = $audit->action === 'login_failed' || str_contains($audit->action, 'failed');
                @endphp
                <div @click="openDetails(@js($detailPayload))" class="p-4 flex items-center gap-3 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 cursor-pointer active:bg-primary-50">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-xs {{ $isFailed ? 'bg-red-500' : ($isLogin ? 'bg-green-600' : 'bg-primary-600') }}">
                        <i class="fas {{ $isFailed ? 'fa-exclamation' : ($isLogin ? 'fa-sign-in-alt' : 'fa-history') }} text-[12px]"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-black text-primary-900 dark:text-white truncate">{{ ucwords(str_replace('_',' ', $audit->action)) }}</span>
                            <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[8px] font-bold border" :class="actionBadgeClass('{{ $audit->action }}')">{{ ucwords(str_replace('_',' ', $audit->action)) }}</span>
                        </div>
                        <div class="text-xs font-semibold text-primary-700 dark:text-primary-300 truncate">{{ $audit->user?->name ?? 'Guest / System' }}</div>
                        <div class="text-[11px] text-primary-500 truncate">{{ $targetLabel }}</div>
                        <div class="text-[10px] text-primary-400 flex items-center gap-1.5 mt-0.5"><i class="fas fa-clock text-[9px]"></i>{{ $createdAt?->format('M d, H:i') ?? 'N/A' }} <span class="opacity-50">•</span> <span class="font-mono">{{ $audit->ip_address ?? 'N/A' }}</span></div>
                    </div>
                    <div class="shrink-0 flex flex-col items-end gap-1">
                        <span class="text-[9px] font-mono bg-primary-50 dark:bg-dark-900 px-2 py-1 rounded border border-primary-100 dark:border-dark-border">#{{ $audit->id }}</span>
                        <i class="fas fa-chevron-right text-[10px] text-primary-300"></i>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center"><div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-dark-900 flex items-center justify-center mx-auto mb-4"><i class="fas fa-folder-open text-2xl text-primary-200"></i></div><p class="text-sm font-bold text-primary-900 dark:text-white">No Audit Logs Found</p><p class="text-xs text-primary-500">No activity recorded yet.</p></div>
            @endforelse
        </div>
        
        @if($audits->hasPages())
            <div class="p-4 bg-primary-50/30 dark:bg-dark-900/30 border-t border-primary-50 dark:border-dark-border">
                {{ $audits->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Right Drawer - fixed inset-0 overlay + right-0 w-full sm:w-[520px] slide-over -->
    <div x-show="open" x-cloak class="fixed inset-0 z-[60] overflow-hidden" @keydown.escape.window="closeDetails()">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeDetails()"></div>
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="absolute inset-y-0 right-0 w-full sm:w-[520px] max-w-[100vw] h-screen max-h-[100dvh] bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="shrink-0 flex items-start justify-between gap-4 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/50 dark:bg-dark-900/50">
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-black text-primary-900 dark:text-white flex items-center gap-2"><i class="fas fa-history text-primary-600"></i> Audit Details</h3>
                    <p class="text-[10px] text-primary-500 uppercase tracking-widest">Full system view • Right drawer</p>
                    <p x-show="selected" class="font-mono text-xs font-bold text-primary-700 dark:text-primary-300 mt-1 truncate"><span x-text="'#'+selected?.id"></span> <span class="opacity-50">•</span> <span x-text="selected?.action_label"></span></p>
                </div>
                <button type="button" @click="closeDetails()" class="shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-dark-800 border border-primary-100 dark:border-dark-border text-primary-600 hover:bg-primary-50 flex items-center justify-center"><i class="fas fa-times text-xs"></i></button>
            </div>

            <!-- Drawer Body scroll -->
            <div class="flex-1 min-h-0 overflow-y-auto p-5 space-y-5 overscroll-contain" x-show="selected">
                <template x-if="selected">
                    <div class="space-y-5">
                        <!-- Summary -->
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl bg-primary-50/70 dark:bg-dark-800 border border-primary-100 dark:border-dark-border">
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold uppercase text-primary-500">Action</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border" :class="actionBadgeClass(selected.action)" x-text="selected.action_label"></span>
                                    <button type="button" @click="copyText(selected.action, 'action')" class="shrink-0 w-7 h-7 rounded-lg bg-white dark:bg-dark-900 border border-primary-100 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white" title="Copy action"><i class="fas text-[10px]" :class="copiedField === 'action' ? 'fa-check' : 'fa-copy'"></i></button>
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-[10px] font-bold uppercase text-primary-500">Model</span>
                                    <span class="text-xs font-bold text-primary-900 dark:text-white" x-text="selected.model"></span>
                                    <span class="text-[10px] text-primary-400">•</span>
                                    <span class="text-[10px] font-bold uppercase text-primary-500">Target ID</span>
                                    <span class="font-mono text-xs font-bold flex items-center gap-1"><span x-text="selected.target_id"></span><button type="button" @click="copyText(selected.target_id, 'target_id')" :disabled="!selected.target_id || selected.target_id==='N/A'" class="w-6 h-6 rounded border border-primary-100 flex items-center justify-center hover:bg-primary-50 disabled:opacity-40"><i class="fas text-[9px]" :class="copiedField==='target_id'?'fa-check':'fa-copy'"></i></button></span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[10px] font-bold uppercase text-primary-500">Date & Time</p>
                                <p class="text-sm font-semibold text-primary-800 dark:text-primary-200"><span x-text="selected.date"></span> <span x-text="selected.time"></span></p>
                                <span class="inline-block mt-1 font-mono text-[10px] bg-white dark:bg-dark-900 px-2 py-0.5 rounded border border-primary-100" x-text="'ID #'+selected.id"></span>
                                <button type="button" @click="copyText(String(selected.id), 'id')" class="ml-1 w-6 h-6 rounded border border-primary-100 bg-white dark:bg-dark-900 inline-flex items-center justify-center hover:bg-primary-50"><i class="fas text-[9px]" :class="copiedField==='id'?'fa-check':'fa-copy'"></i></button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-3 p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-user-circle"></i> User</h4>
                                <div><p class="text-[10px] font-bold uppercase text-primary-400">User Name</p><p class="font-bold text-sm text-primary-900 dark:text-white" x-text="selected.user_name"></p></div>
                                <template x-if="selected.user_email"><div><p class="text-[10px] font-bold uppercase text-primary-400">Email</p><p class="text-sm break-all flex items-center gap-2"><span x-text="selected.user_email"></span><button type="button" @click="copyText(selected.user_email,'user_email')" class="w-6 h-6 rounded border border-primary-100 flex items-center justify-center hover:bg-primary-50"><i class="fas fa-copy text-[9px]"></i></button></p></div></template>
                                <template x-if="!selected.user_email"><div><p class="text-[10px] font-bold uppercase text-primary-400">Email</p><p class="text-sm text-primary-500 italic">Guest / System — no email</p></div></template>
                                <div><p class="text-[10px] font-bold uppercase text-primary-400">Audit ID</p><p class="font-mono text-sm flex items-center gap-2"><span x-text="'#'+selected.id"></span><button type="button" @click="copyText(String(selected.id),'id2')" class="w-6 h-6 rounded border border-primary-100 flex items-center justify-center hover:bg-primary-50"><i class="fas text-[9px]" :class="copiedField==='id2'?'fa-check':'fa-copy'"></i></button></p></div>
                            </div>
                            <div class="space-y-3 p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-bullseye"></i> Target</h4>
                                <div><p class="text-[10px] font-bold uppercase text-primary-400">Model</p><p class="font-bold text-sm" x-text="selected.model"></p></div>
                                <div><p class="text-[10px] font-bold uppercase text-primary-400">Target ID</p><p class="font-mono text-sm flex items-center gap-2"><span x-text="selected.target_id"></span><button type="button" @click="copyText(selected.target_id,'target_id2')" :disabled="!selected.target_id || selected.target_id==='N/A'" class="w-6 h-6 rounded border border-primary-100 flex items-center justify-center hover:bg-primary-50 disabled:opacity-40"><i class="fas text-[9px]" :class="copiedField==='target_id2'?'fa-check':'fa-copy'"></i></button></p></div>
                                <div><p class="text-[10px] font-bold uppercase text-primary-400">Target / Details excerpt</p><p class="text-xs bg-primary-50/70 dark:bg-dark-900 rounded-lg p-2 border border-primary-100 dark:border-dark-border whitespace-pre-wrap break-words" x-text="selected.target_label || selected.details || '—'"></p></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-3 p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-network-wired"></i> Request</h4>
                                <div class="flex justify-between items-start gap-2 border-b border-primary-50 dark:border-dark-border pb-2"><span class="text-xs text-primary-500 shrink-0">Method</span><span class="text-xs font-bold px-2 py-0.5 rounded" :class="methodBadgeClass(selected.method)" x-text="selected.method || 'N/A'"></span></div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase text-primary-400">IP Address</p>
                                    <p class="font-mono text-sm flex items-center gap-2"><span x-text="selected.ip_address"></span><button type="button" @click="copyText(selected.ip_address,'ip_address')" class="w-7 h-7 rounded-lg bg-primary-50 border border-primary-100 flex items-center justify-center hover:bg-primary-600 hover:text-white"><i class="fas text-[10px]" :class="copiedField==='ip_address'?'fa-check':'fa-copy'"></i></button></p>
                                </div>
                                <template x-if="selected.url">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase text-primary-400 mb-1">URL</p>
                                        <div class="flex items-start gap-2">
                                            <p class="font-mono text-xs text-primary-800 dark:text-primary-200 break-all flex-1 bg-primary-50/50 dark:bg-dark-900/50 rounded-lg p-2 border border-primary-100 dark:border-dark-border" x-text="selected.url"></p>
                                            <button type="button" @click="copyText(selected.url,'url')" class="shrink-0 w-7 h-7 rounded-lg bg-white border border-primary-100 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white" title="Copy URL"><i class="fas text-[10px]" :class="copiedField==='url'?'fa-check':'fa-copy'"></i></button>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="selected.user_agent">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase text-primary-400 mb-1">User Agent</p>
                                        <div class="flex items-start gap-2">
                                            <p class="font-mono text-[10px] text-primary-800 dark:text-primary-200 break-all flex-1 bg-primary-50/50 dark:bg-dark-900/50 rounded-lg p-2 border border-primary-100 dark:border-dark-border" x-text="selected.user_agent"></p>
                                            <button type="button" @click="copyText(selected.user_agent,'user_agent')" class="shrink-0 w-7 h-7 rounded-lg bg-white border border-primary-100 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white" title="Copy user agent"><i class="fas text-[10px]" :class="copiedField==='user_agent'?'fa-check':'fa-copy'"></i></button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="space-y-3">
                                <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800 space-y-3">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-laptop"></i> Device</h4>
                                    <div><p class="text-[10px] font-bold uppercase text-primary-400">Device Type</p><p class="text-sm" x-text="selected.device_type || 'N/A'"></p></div>
                                    <div><p class="text-[10px] font-bold uppercase text-primary-400">Browser</p><p class="text-sm" x-text="selected.device_browser || 'N/A'"></p></div>
                                    <div><p class="text-[10px] font-bold uppercase text-primary-400">Platform</p><p class="text-sm" x-text="selected.device_platform || 'N/A'"></p></div>
                                </div>
                                <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800 space-y-3">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-map-marker-alt"></i> Location</h4>
                                    <div><p class="text-[10px] font-bold uppercase text-primary-400">Country</p><p class="text-sm" x-text="selected.country || 'N/A'"></p></div>
                                    <div><p class="text-[10px] font-bold uppercase text-primary-400">City</p><p class="text-sm" x-text="selected.city || 'N/A'"></p></div>
                                    <div><p class="text-[10px] font-bold uppercase text-primary-400">Timezone</p><p class="text-sm" x-text="selected.timezone || 'N/A'"></p></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-[10px] font-bold uppercase text-primary-500">Event Details</p>
                                <button type="button" @click="copyText(selected.details || '', 'details')" :disabled="!selected.details" class="text-[10px] font-bold text-primary-600 hover:underline disabled:opacity-40 flex items-center gap-1"><i class="fas" :class="copiedField==='details'?'fa-check':'fa-copy'"></i> <span x-text="copiedField==='details'?'Copied':'Copy'"></span></button>
                            </div>
                            <p class="text-sm text-primary-800 dark:text-primary-200 bg-primary-50/50 dark:bg-dark-800 rounded-xl p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap break-words" x-text="selected.details || 'No details available'"></p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-primary-50/30 dark:bg-dark-800/50 space-y-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-history"></i> Old Values</h4>
                                    <button type="button" @click="copyText(formatJson(selected.old_values), 'old_values')" :disabled="!selected.old_values" class="text-[10px] font-bold text-primary-600 hover:underline disabled:opacity-40"><i class="fas" :class="copiedField==='old_values'?'fa-check':'fa-copy'"></i> Copy</button>
                                </div>
                                <template x-if="selected.old_values">
                                    <pre class="text-[11px] font-mono bg-white dark:bg-dark-900 rounded-lg p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap break-words max-h-40 overflow-y-auto" x-text="formatJson(selected.old_values)"></pre>
                                </template>
                                <template x-if="!selected.old_values"><p class="text-xs text-primary-500 italic bg-white dark:bg-dark-900 rounded-lg p-3 border border-dashed">No old values stored for this event.</p></template>
                            </div>
                            <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-primary-50/30 dark:bg-dark-800/50 space-y-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-plus-circle"></i> New Values</h4>
                                    <button type="button" @click="copyText(formatJson(selected.new_values), 'new_values')" :disabled="!selected.new_values" class="text-[10px] font-bold text-primary-600 hover:underline disabled:opacity-40"><i class="fas" :class="copiedField==='new_values'?'fa-check':'fa-copy'"></i> Copy</button>
                                </div>
                                <template x-if="selected.new_values">
                                    <pre class="text-[11px] font-mono bg-white dark:bg-dark-900 rounded-lg p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap break-words max-h-40 overflow-y-auto" x-text="formatJson(selected.new_values)"></pre>
                                </template>
                                <template x-if="!selected.new_values"><p class="text-xs text-primary-500 italic bg-white dark:bg-dark-900 rounded-lg p-3 border border-dashed">No new values stored for this event.</p></template>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-code"></i> Audit Payload</h4>
                                <button type="button" @click="copyText(formatJson(selected), 'payload')" class="px-3 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-[10px] font-bold transition-all flex items-center gap-1"><i class="fas" :class="copiedField==='payload'?'fa-check':'fa-copy'"></i> <span x-text="copiedField==='payload'?'Copied':'Copy JSON'"></span></button>
                            </div>
                            <p class="text-[10px] text-primary-500">Full audit record as JSON — includes user, action, model, target, IP, user agent, device, location, timestamps.</p>
                            <pre class="text-[11px] font-mono bg-primary-50/70 dark:bg-dark-900 rounded-xl p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap break-words max-h-56 overflow-y-auto" x-text="formatJson(selected)"></pre>
                        </div>

                        <div class="flex flex-wrap gap-2 pt-2">
                            <button type="button" @click="closeDetails()" class="flex-1 min-w-[120px] px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold text-center transition-all">Close Drawer</button>
                            <button type="button" @click="copyText(formatJson(selected), 'payload2')" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-dark-border text-xs font-bold hover:bg-gray-200 flex items-center gap-2"><i class="fas" :class="copiedField==='payload2'?'fa-check':'fa-copy'"></i> Copy Payload</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; } .scrollbar-hide::-webkit-scrollbar{display:none} .scrollbar-hide{-ms-overflow-style:none;scrollbar-width:none}</style>
@endsection

@push('scripts')
<script>
function auditLogDetails() {
    return {
        open: false,
        selected: null,
        copiedField: null,
        copyTimeout: null,
        openDetails(payload) {
            this.selected = payload;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        closeDetails() {
            this.open = false;
            setTimeout(()=>{ this.selected=null; }, 300);
            document.body.style.overflow = '';
        },
        formatAction(action) {
            return String(action || '').split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },
        formatJson(value) {
            try {
                if (value === null || value === undefined) return '';
                if (typeof value === 'string') {
                    try { return JSON.stringify(JSON.parse(value), null, 2); } catch { return value; }
                }
                return JSON.stringify(value, null, 2);
            } catch { return String(value); }
        },
        async copyText(text, field) {
            const value = String(text ?? '').trim();
            if (!value || value === 'N/A') return;
            try { await navigator.clipboard.writeText(value); } catch {
                const ta = document.createElement('textarea');
                ta.value = value; ta.style.position='fixed'; ta.style.left='-9999px';
                document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
            }
            this.copiedField = field;
            clearTimeout(this.copyTimeout);
            this.copyTimeout = setTimeout(() => { this.copiedField = null; }, 1800);
        },
        methodBadgeClass(method) {
            const m = String(method || '').toUpperCase();
            if (['GET'].includes(m)) return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300';
            if (['POST', 'PUT', 'PATCH'].includes(m)) return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300';
            if (['DELETE'].includes(m)) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
            return 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300';
        },
        actionBadgeClass(action) {
            const a = String(action || '').toLowerCase();
            if (a === 'login' || a === 'login_2fa' || a === 'login_2fa_recovery') return 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800';
            if (a === 'logout') return 'bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-800';
            if (a.includes('failed') || a.includes('login_failed')) return 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800';
            if (a.includes('create') || a.includes('initiate')) return 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800';
            if (a.includes('delete') || a.includes('cancel') || a.includes('reject')) return 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/20 dark:text-red-300';
            if (a.includes('update') || a.includes('approve') || a.includes('verify')) return 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800';
            return 'bg-primary-50 text-primary-700 border-primary-200 dark:bg-primary-900/20 dark:text-primary-300 dark:border-primary-800';
        },
        toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.audit-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            this.updateBulkDeleteBtn();
        },
        updateBulkDeleteBtn() {
            const checkboxes = document.querySelectorAll('.audit-checkbox:checked');
            const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
            const bulkDeleteIdsInput = document.getElementById('bulk-delete-ids');
            
            const ids = Array.from(checkboxes).map(cb => cb.getAttribute('data-id'));
            bulkDeleteIdsInput.value = JSON.stringify(ids);
            
            bulkDeleteBtn.disabled = ids.length === 0;
        },
        confirmBulkDelete() {
            if (confirm('Are you sure you want to delete the selected audit logs?')) {
                const form = document.getElementById('bulk-delete-form');
                form.submit();
            }
        }
    };
}
</script>
@endpush
