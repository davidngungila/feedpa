@extends('layouts.app')

@section('title', 'Payment Reconciliation')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'summary', search: '' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-scale-balanced text-primary-500"></i> Payment Reconciliation
            </h1>
            <p class="text-xs text-primary-500 mt-1">
                Compare successful (SETTLED/SUCCESS) live ClickPesa API payments against records stored in the system. Failed & processing payments are excluded.
                Last checked: {{ $fetchedAt->format('d M Y, H:i:s') }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('payments.reconcile') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 transition-all">
                <i class="fas fa-rotate-right"></i> Re-run Reconciliation
            </a>
            <a href="{{ route('payments.history') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold text-primary-700 dark:text-primary-300 bg-white dark:bg-dark-card border border-primary-200 dark:border-dark-border hover:bg-primary-50 transition-all">
                <i class="fas fa-arrow-left"></i> Back to History
            </a>
        </div>
    </div>

    @if($error)
        <div class="rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/40 p-4 flex items-start gap-3">
            <i class="fas fa-circle-exclamation text-red-500 mt-0.5"></i>
            <div>
                <p class="text-sm font-bold text-red-800 dark:text-red-200">API Fetch Failed</p>
                <p class="text-xs text-red-600 dark:text-red-300 mt-0.5">{{ $error }}</p>
                <p class="text-xs text-red-400 mt-1">Showing database-only reconciliation data below. Click "Re-run Reconciliation" to retry.</p>
            </div>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-5 border-l-4 border-l-primary-500 bg-gradient-to-br from-primary-500 to-primary-700">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/70">Live API Payments</p>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/20 text-white text-[9px] font-black uppercase tracking-wider">
                    <i class="fas fa-circle text-[6px] animate-pulse"></i> Live from API
                </span>
            </div>
            <p class="text-2xl font-black text-white mt-1">{{ number_format($summary['api_count']) }}</p>
            <p class="text-[11px] text-white/80 mt-1">TZS {{ number_format($summary['api_total'], 2) }}</p>
            <div class="mt-2 pt-2 border-t border-white/20 flex items-center justify-between">
                <span class="text-[10px] font-bold text-white/70"><i class="fas fa-bolt mr-1"></i>Live Balance</span>
                <span class="text-xs font-black text-white">
                    @if($apiLiveBalance !== null)
                        TZS {{ number_format($apiLiveBalance, 2) }}
                    @else
                        <span class="text-white/70">Unavailable</span>
                    @endif
                </span>
            </div>
        </div>
        <div class="card p-5 border-l-4 border-l-cyan-500">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-cyan-600">System Records</p>
            <p class="text-2xl font-black text-primary-900 dark:text-white mt-1">{{ number_format($summary['db_count']) }}</p>
            <p class="text-[11px] text-primary-500 mt-1">TZS {{ number_format($summary['db_total'], 2) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-green-600">Matched</p>
            <p class="text-2xl font-black text-green-600 mt-1">{{ number_format($summary['matched_count']) }}</p>
            <p class="text-[11px] text-primary-500 mt-1">Status & amount agree</p>
        </div>
        <div class="card p-5 border-l-4 border-l-red-500">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-red-600">Discrepancies</p>
            <p class="text-2xl font-black text-red-600 mt-1">{{ number_format($summary['status_mismatch_count'] + $summary['amount_mismatch_count'] + $summary['only_in_api_count'] + $summary['only_in_db_count']) }}</p>
            <p class="text-[11px] text-primary-500 mt-1">Needs attention</p>
        </div>
    </div>

    <!-- Detail Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-red-600">In API only</p>
                    <p class="text-lg font-black text-primary-900 dark:text-white">{{ number_format($summary['only_in_api_count']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-500">
                    <i class="fas fa-cloud-arrow-down"></i>
                </div>
            </div>
            <p class="text-[11px] text-primary-500 mt-2">Payments on ClickPesa not in the system — TZS {{ number_format($summary['api_only_total'], 2) }}</p>
        </div>
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-amber-600">In system only</p>
                    <p class="text-lg font-black text-primary-900 dark:text-white">{{ number_format($summary['only_in_db_count']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-500">
                    <i class="fas fa-database"></i>
                </div>
            </div>
            <p class="text-[11px] text-primary-500 mt-2">System records not on ClickPesa — TZS {{ number_format($summary['db_only_total'], 2) }}</p>
        </div>
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-orange-600">Status / Amount mismatches</p>
                    <p class="text-lg font-black text-primary-900 dark:text-white">{{ number_format($summary['status_mismatch_count'] + $summary['amount_mismatch_count']) }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-500">
                    <i class="fas fa-code-compare"></i>
                </div>
            </div>
            <p class="text-[11px] text-primary-500 mt-2">Status mismatch: {{ number_format($summary['status_mismatch_count']) }} &middot; Amount mismatch: {{ number_format($summary['amount_mismatch_count']) }}</p>
        </div>
    </div>

    <!-- Search -->
    <div class="card p-4">
        <div class="flex items-center gap-3">
            <i class="fas fa-search text-primary-400"></i>
            <input type="text" x-model="search" placeholder="Filter by reference, payer, phone..." class="flex-1 bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 outline-none">
            <button type="button"
                    @click="activeTab = 'summary'"
                    class="px-3 py-2 rounded-lg text-xs font-bold transition-all"
                    :class="activeTab === 'summary' ? 'bg-primary-600 text-white' : 'bg-primary-50 dark:bg-dark-900 text-primary-600 dark:text-primary-300'">
                Matched ({{ $summary['matched_count'] }})
            </button>
            <button type="button"
                    @click="activeTab = 'onlyApi'"
                    class="px-3 py-2 rounded-lg text-xs font-bold transition-all"
                    :class="activeTab === 'onlyApi' ? 'bg-red-600 text-white' : 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-300'">
                API Only ({{ $summary['only_in_api_count'] }})
            </button>
            <button type="button"
                    @click="activeTab = 'onlyDb'"
                    class="px-3 py-2 rounded-lg text-xs font-bold transition-all"
                    :class="activeTab === 'onlyDb' ? 'bg-amber-600 text-white' : 'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-300'">
                System Only ({{ $summary['only_in_db_count'] }})
            </button>
            <button type="button"
                    @click="activeTab = 'mismatches'"
                    class="px-3 py-2 rounded-lg text-xs font-bold transition-all"
                    :class="activeTab === 'mismatches' ? 'bg-orange-600 text-white' : 'bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-300'">
                Mismatches ({{ $summary['status_mismatch_count'] + $summary['amount_mismatch_count'] }})
            </button>
        </div>
    </div>

    <!-- Matched Table -->
    <div x-show="activeTab === 'summary'" x-cloak class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-primary-900 dark:text-white"><i class="fas fa-check-circle text-green-500 mr-2"></i>Matched Payments</p>
                <p class="text-[10px] text-primary-500">Records that exist in both the API and the system with matching status & amount</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Reference</th>
                        <th>Payer</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Method</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($matched as $row)
                        @php $api = $row['api']; @endphp
                        <tr x-show="!search || '{{ strtolower($row['reference'] . ' ' . ($api['payer'] ?? '') . ' ' . ($api['phone'] ?? '')) }}'.includes(search.toLowerCase())" class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">
                                    <i class="fas fa-check text-[8px]"></i> Matched
                                </span>
                            </td>
                            <td class="font-mono text-[11px] text-primary-700 dark:text-primary-300">{{ $row['reference'] }}</td>
                            <td class="text-xs font-bold text-primary-900 dark:text-white">{{ $api['payer'] ?? 'N/A' }}</td>
                            <td class="text-xs text-primary-600 dark:text-primary-300">{{ $api['phone'] ?? 'N/A' }}</td>
                            <td class="text-xs font-bold text-primary-900 dark:text-white">TZS {{ number_format($api['amount'], 2) }}</td>
                            <td>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ in_array($api['status'], ['SUCCESS', 'SETTLED']) ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($api['status'] === 'FAILED' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300') }}">
                                    <i class="fas fa-circle text-[5px]"></i> {{ $api['status'] }}
                                </span>
                            </td>
                            <td class="text-xs text-primary-600 dark:text-primary-300">{{ $api['method'] ?? 'N/A' }}</td>
                            <td class="text-xs text-primary-500">{{ isset($api['created_at']) ? \Illuminate\Support\Carbon::parse($api['created_at'])->format('d M Y H:i') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-8 text-xs text-primary-500 italic">No matched payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- API Only Table -->
    <div x-show="activeTab === 'onlyApi'" x-cloak class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-red-50/30 dark:bg-red-900/10 flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-red-700 dark:text-red-200"><i class="fas fa-cloud-arrow-down text-red-500 mr-2"></i>On ClickPesa API but Missing in System</p>
                <p class="text-[10px] text-red-500">These payments exist live on ClickPesa but have no matching record in the system. Run a payment sync to import them.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Reference</th>
                        <th>Payer</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Method</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($onlyInApi as $row)
                        @php $api = $row['api']; @endphp
                        <tr x-show="!search || '{{ strtolower($row['reference'] . ' ' . ($api['payer'] ?? '') . ' ' . ($api['phone'] ?? '')) }}'.includes(search.toLowerCase())" class="hover:bg-red-50/30 dark:hover:bg-red-900/10 transition-colors">
                            <td>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                    <i class="fas fa-xmark text-[8px]"></i> Not Matched
                                </span>
                            </td>
                            <td class="font-mono text-[11px] text-red-700 dark:text-red-300">{{ $row['reference'] }}</td>
                            <td class="text-xs font-bold text-primary-900 dark:text-white">{{ $api['payer'] ?? 'N/A' }}</td>
                            <td class="text-xs text-primary-600 dark:text-primary-300">{{ $api['phone'] ?? 'N/A' }}</td>
                            <td class="text-xs font-bold text-red-600 dark:text-red-300">TZS {{ number_format($api['amount'], 2) }}</td>
                            <td>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ in_array($api['status'], ['SUCCESS', 'SETTLED']) ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($api['status'] === 'FAILED' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300') }}">
                                    <i class="fas fa-circle text-[5px]"></i> {{ $api['status'] }}
                                </span>
                            </td>
                            <td class="text-xs text-primary-600 dark:text-primary-300">{{ $api['method'] ?? 'N/A' }}</td>
                            <td class="text-xs text-primary-500">{{ isset($api['created_at']) ? \Illuminate\Support\Carbon::parse($api['created_at'])->format('d M Y H:i') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-8 text-xs text-primary-500 italic">No API-only payments found — the system is up to date.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- System Only Table -->
    <div x-show="activeTab === 'onlyDb'" x-cloak class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-amber-50/30 dark:bg-amber-900/10 flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-amber-700 dark:text-amber-200"><i class="fas fa-database text-amber-500 mr-2"></i>In System but Missing on ClickPesa API</p>
                <p class="text-[10px] text-amber-500">Each record was checked live against the ClickPesa API (by reference). Records found live on the API were moved to Matched/Mismatches; the rest are confirmed absent from the API — locally created or test data.</p>
                @if(($summary['api_query_errors'] ?? 0) > 0)
                    <p class="text-[10px] text-amber-600 mt-1"><i class="fas fa-triangle-exclamation mr-1"></i>{{ $summary['api_query_errors'] }} record(s) could not be verified live (API lookup failed for those references).</p>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Reference</th>
                        <th>Payer</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Method</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($onlyInDb as $row)
                        @php $db = $row['db']; @endphp
                        <tr x-show="!search || '{{ strtolower($row['reference'] . ' ' . ($db['payer'] ?? '') . ' ' . ($db['phone'] ?? '')) }}'.includes(search.toLowerCase())" class="hover:bg-amber-50/30 dark:hover:bg-amber-900/10 transition-colors">
                            <td>
                                @if(($row['verified_absent'] ?? false))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                        <i class="fas fa-xmark text-[8px]"></i> Not Matched
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        <i class="fas fa-question text-[8px]"></i> Unverified
                                    </span>
                                @endif
                            </td>
                            <td class="font-mono text-[11px] text-amber-700 dark:text-amber-300">{{ $row['reference'] }}</td>
                            <td class="text-xs font-bold text-primary-900 dark:text-white">{{ $db['payer'] ?? 'N/A' }}</td>
                            <td class="text-xs text-primary-600 dark:text-primary-300">{{ $db['phone'] ?? 'N/A' }}</td>
                            <td class="text-xs font-bold text-amber-600 dark:text-amber-300">TZS {{ number_format($db['amount'], 2) }}</td>
                            <td>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ in_array($db['status'], ['SUCCESS', 'SETTLED']) ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($db['status'] === 'FAILED' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300') }}">
                                    <i class="fas fa-circle text-[5px]"></i> {{ $db['status'] }}
                                </span>
                            </td>
                            <td class="text-xs text-primary-600 dark:text-primary-300">{{ $db['method'] ?? 'N/A' }}</td>
                            <td class="text-xs text-primary-500">{{ isset($db['created_at']) ? \Illuminate\Support\Carbon::parse($db['created_at'])->format('d M Y H:i') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-8 text-xs text-primary-500 italic">No system-only records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mismatches Table -->
    <div x-show="activeTab === 'mismatches'" x-cloak class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-orange-50/30 dark:bg-orange-900/10">
            <p class="text-sm font-bold text-orange-700 dark:text-orange-200"><i class="fas fa-code-compare text-orange-500 mr-2"></i>Status / Amount Mismatches</p>
            <p class="text-[10px] text-orange-500">Records found in both the API and the system, but with conflicting status or amount.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Reference</th>
                        <th>Field</th>
                        <th>API Value</th>
                        <th>System Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($statusMismatch->merge($amountMismatch)->unique('reference') as $row)
                        @php $api = $row['api']; $db = $row['db']; @endphp
                        <tr x-show="!search || '{{ strtolower($row['reference'] . ' ' . ($api['payer'] ?? '') . ' ' . ($db['payer'] ?? '') . ' ' . ($api['phone'] ?? '') . ' ' . ($db['phone'] ?? '')) }}'.includes(search.toLowerCase())" class="hover:bg-orange-50/30 dark:hover:bg-orange-900/10 transition-colors align-top">
                            <td>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">
                                    <i class="fas fa-xmark text-[8px]"></i> Not Matched
                                </span>
                            </td>
                            <td class="font-mono text-[11px] text-orange-700 dark:text-orange-300 whitespace-nowrap">{{ $row['reference'] }}</td>
                            <td class="text-xs">
                                @unless($row['status_match'] ?? true)
                                    <div class="mb-1"><span class="px-2 py-0.5 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 text-[10px] font-bold">STATUS</span></div>
                                @endunless
                                @unless($row['amount_match'] ?? true)
                                    <div><span class="px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-[10px] font-bold">AMOUNT</span></div>
                                @endunless
                            </td>
                            <td class="text-xs text-primary-900 dark:text-white whitespace-nowrap">
                                @unless($row['status_match'] ?? true)
                                    <div class="mb-1">
                                        <span class="px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 text-[10px] font-bold">{{ $api['status'] }}</span>
                                    </div>
                                @endunless
                                @unless($row['amount_match'] ?? true)
                                    <div class="font-bold">TZS {{ number_format($api['amount'], 2) }}</div>
                                @endunless
                            </td>
                            <td class="text-xs text-primary-900 dark:text-white whitespace-nowrap">
                                @unless($row['status_match'] ?? true)
                                    <div class="mb-1">
                                        <span class="px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 text-[10px] font-bold">{{ $db['status'] }}</span>
                                    </div>
                                @endunless
                                @unless($row['amount_match'] ?? true)
                                    <div class="font-bold">TZS {{ number_format($db['amount'], 2) }}</div>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-8 text-xs text-primary-500 italic">No mismatches found — API and system data agree.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-[10px] text-gray-400 text-center">
        <i class="fas fa-info-circle mr-1"></i>Reconciliation compares successful (SETTLED/SUCCESS) payments returned by the ClickPesa API against the local database. Status & amount are matched by order reference; failed, cancelled and processing payments are excluded.
    </p>
</div>
@endsection
