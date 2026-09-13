@extends('layouts.app')

@section('title', 'Bill Management')

@section('content')
<div class="space-y-6" x-data="billHistoryDetails()" @keydown.escape.window="closeDetails()">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-file-invoice text-primary-500"></i>
                Bill Management
            </h2>
            <p class="text-xs text-primary-500 mt-1">Manage your BillPay control numbers and generate new bills.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('dashboard.index') }}" class="px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
            <a href="{{ route('bills.create-order') }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-black transition-all">
                <i class="fas fa-plus-circle me-1"></i> Create Order Control Number
            </a>
            <a href="{{ route('bills.create-customer') }}" class="px-4 py-2 rounded-xl bg-green-600 hover:bg-green-500 text-white text-xs font-black transition-all">
                <i class="fas fa-user-plus me-1"></i> Create Customer Control Number
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="card p-4 border-l-4 border-l-red-500 bg-red-50/60 dark:bg-red-900/10">
            <p class="text-xs font-bold text-red-700 dark:text-red-300">
                <i class="fas fa-circle-exclamation me-1"></i> {{ session('error') }}
            </p>
        </div>
    @endif

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-circle-check me-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <div class="card p-6">
        <!-- Filters -->
        <form method="GET" action="{{ route('bills.index') }}" class="mb-0">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="text-[10px] font-black uppercase tracking-wider text-primary-500 mb-1 block">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by control number, description, customer, etc."
                               class="w-full px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-900 text-primary-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                        <i class="fas fa-search absolute right-3 top-1/2 -translate-y-1/2 text-primary-400 text-xs"></i>
                    </div>
                </div>
                <!-- Status Filter -->
                <div>
                    <label class="text-[10px] font-black uppercase tracking-wider text-primary-500 mb-1 block">Status</label>
                    <select name="status" class="w-full px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-900 text-primary-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                        <option value="ALL" {{ $status === 'ALL' ? 'selected' : '' }}>All Status</option>
                        <option value="ACTIVE" {{ $status === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="INACTIVE" {{ $status === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <!-- Type Filter -->
                <div>
                    <label class="text-[10px] font-black uppercase tracking-wider text-primary-500 mb-1 block">Type</label>
                    <select name="type" class="w-full px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-900 text-primary-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                        <option value="ALL" {{ $type === 'ALL' ? 'selected' : '' }}>All Types</option>
                        <option value="order" {{ $type === 'order' ? 'selected' : '' }}>Order</option>
                        <option value="customer" {{ $type === 'customer' ? 'selected' : '' }}>Customer</option>
                    </select>
                </div>
                <!-- Actions -->
                <div class="flex gap-2 items-end">
                    <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('bills.index') }}" class="shrink-0 px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 hover:bg-primary-50 transition-all">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
            <!-- Date Range -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="text-[10px] font-black uppercase tracking-wider text-primary-500 mb-1 block">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                           class="w-full px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-900 text-primary-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                </div>
                <div>
                    <label class="text-[10px] font-black uppercase tracking-wider text-primary-500 mb-1 block">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="w-full px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-900 text-primary-900 dark:text-white text-xs focus:outline-none focus:ring-2 focus:ring-primary-500/30">
                </div>
            </div>
        </form>
    </div>

    @php
        $billsUserIds = $bills->pluck('created_by')->filter()->unique()->values();
        $billsUsersById = $billsUserIds->isNotEmpty() ? \App\Models\User::whereIn('id', $billsUserIds)->pluck('name','id') : collect();
    @endphp

    <!-- Bills Table Card - Fully responsive: minimal columns, rest in drawer -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <p class="text-[11px] font-semibold text-primary-700 dark:text-primary-300 flex items-center gap-1.5"><i class="fas fa-hand-pointer text-primary-500"></i> Click any row to open right drawer — full details, copy IDs, customer, status, audit & expiry</p>
            <span class="text-[10px] text-primary-500 hidden lg:inline">Tip: Horizontal scroll on desktop • Cards on mobile</span>
        </div>

        <!-- Desktop / Tablet Table — minimized to important columns only, rest in drawer -->
        <div class="hidden md:block overflow-x-auto">
            <table class="data-table min-w-[640px]">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Date & Time</th>
                        <th>Control No / Bill</th>
                        <th>Customer</th>
                        <th class="whitespace-nowrap">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($bills as $bill)
                        @php
                            $createdAt = $bill->created_at ? \Illuminate\Support\Carbon::parse($bill->created_at) : null;
                            $updatedAt = $bill->updated_at ? \Illuminate\Support\Carbon::parse($bill->updated_at) : null;
                            $lastPaymentAt = $bill->last_payment_at ? \Illuminate\Support\Carbon::parse($bill->last_payment_at) : null;
                            $creatorName = $bill->created_by ? ($billsUsersById[$bill->created_by] ?? 'User #'.$bill->created_by) : 'System';
                            $customerDisplay = $bill->customer_name ?? null;
                            $customerSub = $bill->customer_phone ?? $bill->customer_email ?? null;
                            if(!$customerDisplay) {
                                $customerDisplay = $bill->bill_type === 'customer' ? '—' : 'Order bill';
                                if(!$customerSub) {
                                    $customerSub = \Illuminate\Support\Str::limit($bill->bill_description ?? '', 28);
                                }
                            }
                            $detailPayload = [
                                'id' => $bill->id,
                                'bill_pay_number' => $bill->bill_pay_number,
                                'bill_type' => $bill->bill_type,
                                'bill_description' => $bill->bill_description,
                                'bill_amount' => $bill->bill_amount !== null ? (float)$bill->bill_amount : null,
                                'bill_currency' => $bill->bill_currency ?? 'TZS',
                                'bill_payment_mode' => $bill->bill_payment_mode,
                                'bill_status' => $bill->bill_status,
                                'bill_reference' => $bill->bill_reference,
                                'customer_name' => $bill->customer_name,
                                'customer_email' => $bill->customer_email,
                                'customer_phone' => $bill->customer_phone,
                                'customer_display' => $customerDisplay,
                                'customer_sub' => $customerSub,
                                'notes' => $bill->notes,
                                'total_paid' => (float)($bill->total_paid ?? 0),
                                'last_payment_at' => $lastPaymentAt?->format('d M, Y H:i:s'),
                                'last_payment_iso' => $lastPaymentAt?->toIso8601String(),
                                'created_at' => $createdAt?->format('d M, Y'),
                                'created_time' => $createdAt?->format('H:i:s'),
                                'created_at_full' => $createdAt?->format('Y-m-d H:i:s'),
                                'created_iso' => $createdAt?->toIso8601String(),
                                'updated_at_full' => $updatedAt?->format('Y-m-d H:i:s'),
                                'updated_iso' => $updatedAt?->toIso8601String(),
                                'created_by' => $bill->created_by,
                                'created_by_name' => $creatorName,
                                'show_url' => route('bills.show', $bill->id),
                                'edit_url' => route('bills.edit', $bill->id),
                                'pdf_url' => route('bills.pdf', $bill->id),
                            ];
                        @endphp
                        <tr @click="openDetails(@js($detailPayload))" class="hover:bg-primary-50/70 dark:hover:bg-primary-900/10 transition-colors cursor-pointer group">
                            <td class="whitespace-nowrap py-3.5 px-4">
                                <div class="font-bold text-primary-900 dark:text-white text-xs">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div>
                                <div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div>
                                <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[8px] font-bold {{ $bill->bill_status === 'ACTIVE' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">{{ $bill->bill_status }}</span>
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="flex items-center gap-1.5 max-w-[200px]">
                                    <span class="font-mono text-[11px] bg-primary-50 dark:bg-dark-900 px-2 py-1 rounded border border-primary-100 dark:border-dark-border text-primary-700 dark:text-primary-300 truncate" title="{{ $bill->bill_pay_number }}">{{ \Illuminate\Support\Str::limit($bill->bill_pay_number, 20) }}</span>
                                    <button type="button" @click.stop="copyText(@js($bill->bill_pay_number), 'cn-{{ $bill->id }}')" class="shrink-0 w-7 h-7 rounded-lg bg-white dark:bg-dark-800 border border-primary-100 dark:border-dark-border text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all" title="Copy control number"><i class="fas text-[10px]" :class="copiedField === 'cn-{{ $bill->id }}' ? 'fa-check' : 'fa-copy'"></i></button>
                                </div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold {{ $bill->bill_type === 'order' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">{{ ucfirst($bill->bill_type) }}</span>
                                    @if($bill->bill_reference)
                                        <span class="font-mono text-[10px] text-primary-500 truncate max-w-[110px]" title="{{ $bill->bill_reference }}">{{ \Illuminate\Support\Str::limit($bill->bill_reference, 14) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-3">
                                <div class="font-bold text-primary-900 dark:text-white text-xs truncate max-w-[180px]">{{ $customerDisplay }}</div>
                                <div class="text-[10px] font-mono text-primary-500 truncate max-w-[180px]">{{ $customerSub ?? '—' }}</div>
                            </td>
                            <td class="whitespace-nowrap py-3.5 px-4">
                                <div class="font-bold text-primary-900 dark:text-white text-sm">{{ $bill->bill_currency ?? 'TZS' }} {{ $bill->bill_amount !== null ? number_format((float)$bill->bill_amount, 2) : '—' }}</div>
                                @if((float)($bill->total_paid ?? 0) > 0)
                                    <div class="text-[10px] font-bold text-green-600">Paid: {{ number_format((float)$bill->total_paid, 2) }}</div>
                                @else
                                    <div class="text-[10px] font-bold uppercase text-primary-400">{{ $bill->bill_payment_mode === 'EXACT' ? 'EXACT' : 'Partial allowed' }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-20"><div class="flex flex-col items-center"><div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-dark-900 flex items-center justify-center mb-4"><i class="fas fa-folder-open text-2xl text-primary-200"></i></div><h4 class="font-bold text-primary-900 dark:text-white">No Bills Found</h4><p class="text-xs text-primary-500 mt-1">No bills match your filters. Create your first bill above!</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards - fully responsive fallback -->
        <div class="md:hidden divide-y divide-primary-50 dark:divide-dark-border">
            @forelse($bills as $bill)
                @php
                    $createdAtM = $bill->created_at ? \Illuminate\Support\Carbon::parse($bill->created_at) : null;
                    $lastPaymentAtM = $bill->last_payment_at ? \Illuminate\Support\Carbon::parse($bill->last_payment_at) : null;
                    $creatorNameM = $bill->created_by ? ($billsUsersById[$bill->created_by] ?? 'User #'.$bill->created_by) : 'System';
                    $customerDisplayM = $bill->customer_name ?? ($bill->bill_type === 'customer' ? '—' : 'Order bill');
                    $customerSubM = $bill->customer_phone ?? $bill->customer_email ?? \Illuminate\Support\Str::limit($bill->bill_description ?? '', 28);
                    $amountM = $bill->bill_amount !== null ? (float)$bill->bill_amount : null;
                    $payloadM = [
                        'id' => $bill->id,
                        'bill_pay_number' => $bill->bill_pay_number,
                        'bill_type' => $bill->bill_type,
                        'bill_description' => $bill->bill_description,
                        'bill_amount' => $amountM,
                        'bill_currency' => $bill->bill_currency ?? 'TZS',
                        'bill_payment_mode' => $bill->bill_payment_mode,
                        'bill_status' => $bill->bill_status,
                        'bill_reference' => $bill->bill_reference,
                        'customer_name' => $bill->customer_name,
                        'customer_email' => $bill->customer_email,
                        'customer_phone' => $bill->customer_phone,
                        'customer_display' => $customerDisplayM,
                        'customer_sub' => $customerSubM,
                        'notes' => $bill->notes,
                        'total_paid' => (float)($bill->total_paid ?? 0),
                        'last_payment_at' => $lastPaymentAtM?->format('d M, Y H:i:s'),
                        'created_at' => $createdAtM?->format('d M, Y'),
                        'created_time' => $createdAtM?->format('H:i:s'),
                        'created_at_full' => $createdAtM?->format('Y-m-d H:i:s'),
                        'created_iso' => $createdAtM?->toIso8601String(),
                        'updated_at_full' => $bill->updated_at?->format('Y-m-d H:i:s'),
                        'created_by_name' => $creatorNameM,
                        'show_url' => route('bills.show', $bill->id),
                        'edit_url' => route('bills.edit', $bill->id),
                        'pdf_url' => route('bills.pdf', $bill->id),
                    ];
                @endphp
                <div @click="openDetails(@js($payloadM))" class="p-4 flex items-center gap-3 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 cursor-pointer active:bg-primary-50">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-xs {{ $bill->bill_status === 'ACTIVE' ? 'bg-green-500' : 'bg-gray-400' }}">{{ substr($bill->bill_currency ?? 'TZS',0,1) }}</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-primary-900 dark:text-white truncate">{{ \Illuminate\Support\Str::limit($bill->bill_pay_number, 18) }}</span>
                            <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[8px] font-bold {{ $bill->bill_type === 'order' ? 'bg-primary-100 text-primary-700' : 'bg-blue-100 text-blue-700' }}">{{ strtoupper($bill->bill_type) }}</span>
                            <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[8px] font-bold {{ $bill->bill_status === 'ACTIVE' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $bill->bill_status }}</span>
                        </div>
                        <div class="text-xs font-semibold text-primary-700 dark:text-primary-300 truncate">{{ $customerDisplayM }}</div>
                        <div class="text-[11px] text-primary-500 truncate">{{ $customerSubM }}</div>
                        <div class="text-[10px] text-primary-400">{{ $createdAtM?->format('M d, H:i') ?? '' }} • {{ $bill->bill_reference ? \Illuminate\Support\Str::limit($bill->bill_reference, 14) : 'No ref' }}</div>
                    </div>
                    <div class="shrink-0 text-right">
                        <div class="font-bold text-xs text-primary-900 dark:text-white">{{ $bill->bill_currency ?? 'TZS' }} {{ $amountM !== null ? number_format($amountM,2) : '—' }}</div>
                        @if((float)($bill->total_paid ?? 0) > 0)
                            <div class="text-[10px] font-bold text-green-600">Paid {{ number_format((float)$bill->total_paid,2) }}</div>
                        @endif
                        <i class="fas fa-chevron-right text-[10px] text-primary-300 mt-1"></i>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center"><i class="fas fa-folder-open text-2xl text-primary-200"></i><p class="text-sm font-bold text-primary-900 dark:text-white mt-3">No Bills</p><p class="text-xs text-primary-500">No bills found.</p></div>
            @endforelse
        </div>

        @if($bills->hasPages())
            <div class="p-4 bg-primary-50/30 dark:bg-dark-900/30 border-t border-primary-50 dark:border-dark-border">
                {{ $bills->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Right Drawer - replaces center modal, fully responsive -->
    <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex justify-end overflow-hidden" @keydown.escape.window="closeDetails()">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeDetails()"></div>
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[520px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="shrink-0 flex items-start justify-between gap-4 px-5 pt-0 pb-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/50 dark:bg-dark-900/50">
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-black text-primary-900 dark:text-white flex items-center gap-2"><i class="fas fa-file-invoice text-primary-600"></i> Bill Details</h3>
                    <p class="text-[10px] text-primary-500 uppercase tracking-widest">Full BillPay view • Right drawer</p>
                    <p x-show="selected" class="font-mono text-xs font-bold text-primary-700 dark:text-primary-300 mt-1 truncate" x-text="selected?.bill_pay_number"></p>
                </div>
                <button type="button" @click="closeDetails()" class="shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-dark-800 border border-primary-100 dark:border-dark-border text-primary-600 hover:bg-primary-50 flex items-center justify-center"><i class="fas fa-times text-xs"></i></button>
            </div>

            <!-- Drawer Body scroll -->
            <div class="flex-1 min-h-0 overflow-y-auto p-5 space-y-5 overscroll-contain" x-show="selected">
                <template x-if="selected">
                    <div class="space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl bg-primary-50/70 dark:bg-dark-800 border border-primary-100 dark:border-dark-border">
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold uppercase text-primary-500">Control Number</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <p class="font-mono font-bold text-primary-900 dark:text-white break-all text-sm" x-text="selected.bill_pay_number"></p>
                                    <button type="button" @click="copyText(selected.bill_pay_number, 'drawer-cn')" class="shrink-0 w-7 h-7 rounded-lg bg-white dark:bg-dark-900 border border-primary-100 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white" title="Copy"><i class="fas text-[10px]" :class="copiedField === 'drawer-cn' ? 'fa-check' : 'fa-copy'"></i></button>
                                </div>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold capitalize" :class="selected.bill_type === 'order' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'" x-text="selected.bill_type"></span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="statusBadgeClass(selected.bill_status)" x-text="selected.bill_status"></span>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[10px] font-bold uppercase text-primary-500">Amount</p>
                                <p class="text-lg font-black text-primary-600 dark:text-primary-400"><span x-text="selected.bill_currency"></span> <span x-text="selected.bill_amount !== null ? formatAmount(selected.bill_amount) : '—'"></span></p>
                                <p class="text-[10px] font-bold uppercase" :class="selected.bill_payment_mode === 'EXACT' ? 'text-amber-600' : 'text-green-600'" x-text="selected.bill_payment_mode === 'EXACT' ? 'EXACT' : 'Partial allowed'"></p>
                                <template x-if="selected.total_paid > 0">
                                    <p class="text-[11px] font-bold text-green-600 dark:text-green-400 mt-1" x-text="'Paid: ' + selected.bill_currency + ' ' + formatAmount(selected.total_paid)"></p>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-3 p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-info-circle"></i> Bill Information</h4>
                                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                    <span class="text-xs text-primary-500">Type</span>
                                    <span class="text-xs font-bold capitalize" x-text="selected.bill_type"></span>
                                </div>
                                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                    <span class="text-xs text-primary-500">Payment Mode</span>
                                    <span class="text-xs font-bold text-right max-w-[140px] break-words" x-text="selected.bill_payment_mode"></span>
                                </div>
                                <div class="flex justify-between items-start gap-2 border-b border-primary-50 dark:border-dark-border pb-2">
                                    <span class="text-xs text-primary-500 shrink-0">Reference</span>
                                    <div class="flex items-center gap-1.5 min-w-0 justify-end">
                                        <span class="font-mono text-xs font-bold break-all text-right" x-text="selected.bill_reference || '—'"></span>
                                        <button type="button" @click="copyText(selected.bill_reference, 'drawer-ref')" x-show="selected.bill_reference" class="shrink-0 w-6 h-6 rounded border border-primary-100 flex items-center justify-center hover:bg-primary-50 text-primary-600"><i class="fas fa-copy text-[9px]"></i></button>
                                    </div>
                                </div>
                                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                    <span class="text-xs text-primary-500">Currency</span>
                                    <span class="text-xs font-bold" x-text="selected.bill_currency"></span>
                                </div>
                                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                    <span class="text-xs text-primary-500">Date Created</span>
                                    <span class="text-xs font-bold"><span x-text="selected.created_at"></span> <span x-text="selected.created_time"></span></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-xs text-primary-500">Updated</span>
                                    <span class="text-[11px] font-mono" x-text="selected.updated_at_full"></span>
                                </div>
                            </div>
                            <div class="space-y-3 p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-user-circle"></i> Customer</h4>
                                <template x-if="selected.customer_name || selected.customer_phone || selected.customer_email">
                                    <div class="space-y-3">
                                        <template x-if="selected.customer_name">
                                            <div>
                                                <p class="text-[10px] font-bold uppercase text-primary-400">Customer Name</p>
                                                <p class="font-bold text-sm text-primary-900 dark:text-white" x-text="selected.customer_name"></p>
                                            </div>
                                        </template>
                                        <template x-if="selected.customer_phone">
                                            <div>
                                                <p class="text-[10px] font-bold uppercase text-primary-400">Phone</p>
                                                <p class="font-mono text-sm flex items-center gap-2"><span x-text="selected.customer_phone"></span><button type="button" @click="copyText(selected.customer_phone,'phone')" class="w-6 h-6 rounded border border-primary-100 flex items-center justify-center hover:bg-primary-50 text-primary-600"><i class="fas fa-copy text-[9px]"></i></button></p>
                                            </div>
                                        </template>
                                        <template x-if="selected.customer_email">
                                            <div>
                                                <p class="text-[10px] font-bold uppercase text-primary-400">Email</p>
                                                <p class="text-sm break-all flex items-center gap-2"><span class="min-w-0 flex-1" x-text="selected.customer_email"></span><button type="button" @click="copyText(selected.customer_email,'email')" class="shrink-0 w-6 h-6 rounded border border-primary-100 flex items-center justify-center hover:bg-primary-50 text-primary-600"><i class="fas fa-copy text-[9px]"></i></button></p>
                                            </div>
                                        </template>
                                        <template x-if="!selected.customer_name && !selected.customer_phone && !selected.customer_email">
                                            <p class="text-xs text-primary-500 italic">No customer attached.</p>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!selected.customer_name && !selected.customer_phone && !selected.customer_email">
                                    <div>
                                        <p class="text-xs text-primary-500 italic">Order bill — no customer attached. Payer provides details at payment time.</p>
                                        <div class="mt-3 p-3 rounded-lg bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border">
                                            <p class="text-[10px] font-bold uppercase text-primary-500">Customer Display</p>
                                            <p class="text-sm font-bold" x-text="selected.customer_display"></p>
                                            <p class="text-xs font-mono text-primary-600" x-text="selected.customer_sub"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold uppercase text-primary-500 mb-1">Description</p>
                            <p class="text-sm bg-primary-50/70 dark:bg-dark-800 rounded-xl p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap break-words" x-text="selected.bill_description || '—'"></p>
                        </div>

                        <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800 space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-coins"></i> Payment Tracking</h4>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-primary-500">Total Paid</span>
                                <span class="font-black text-sm" :class="selected.total_paid > 0 ? 'text-green-600 dark:text-green-400' : 'text-primary-900 dark:text-white'" x-text="selected.bill_currency + ' ' + formatAmount(selected.total_paid)"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-primary-500">Last Payment</span>
                                <span class="text-xs font-bold" x-text="selected.last_payment_at || 'No payments yet'"></span>
                            </div>
                            <template x-if="selected.bill_amount !== null && selected.total_paid !== null">
                                <div class="flex justify-between items-center pt-2 border-t border-primary-50 dark:border-dark-border">
                                    <span class="text-xs text-primary-500">Remaining / Over</span>
                                    <span class="text-xs font-bold" :class="(selected.bill_amount - selected.total_paid) <= 0 ? 'text-green-600' : 'text-amber-600'" x-text="selected.bill_currency + ' ' + formatAmount(selected.bill_amount - selected.total_paid) + (selected.bill_amount - selected.total_paid < 0 ? ' (overpaid)' : '')"></span>
                                </div>
                            </template>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-primary-50/30 dark:bg-dark-800/50 space-y-3">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-user-shield"></i> Audit</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between border-b border-primary-100/50 dark:border-dark-border pb-2">
                                        <span class="text-[11px] text-primary-500">Created By</span>
                                        <span class="text-xs font-bold text-right max-w-[150px] truncate" x-text="selected.created_by_name"></span>
                                    </div>
                                    <div class="flex justify-between border-b border-primary-100/50 dark:border-dark-border pb-2">
                                        <span class="text-[11px] text-primary-500">Created At</span>
                                        <span class="text-xs font-mono font-bold" x-text="selected.created_at_full"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-[11px] text-primary-500">Updated At</span>
                                        <span class="text-xs font-mono" x-text="selected.updated_at_full"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-900/10 space-y-3">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-amber-700 dark:text-amber-400 flex items-center gap-2"><i class="fas fa-hourglass-half"></i> Expiry</h4>
                                <p class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed">No expiry date configured — control numbers remain <span class="font-bold" x-text="selected.bill_status"></span> until manually deactivated.</p>
                                <div class="flex justify-between border-t border-amber-100 dark:border-amber-900/30 pt-2">
                                    <span class="text-[11px] text-amber-700 dark:text-amber-400">Valid Since</span>
                                    <span class="text-xs font-mono font-bold text-amber-900 dark:text-amber-200" x-text="selected.created_at"></span>
                                </div>
                                <p class="text-[10px] text-amber-700/70 dark:text-amber-400/70">Valid indefinitely while ACTIVE. Set to INACTIVE to expire.</p>
                            </div>
                        </div>

                        <template x-if="selected.notes">
                            <div>
                                <p class="text-[10px] font-bold uppercase text-primary-500 mb-1">Notes</p>
                                <p class="text-sm bg-white dark:bg-dark-800 rounded-xl p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap break-words" x-text="selected.notes"></p>
                            </div>
                        </template>

                        <div class="flex flex-wrap gap-2 pt-2">
                            <a :href="selected.show_url" class="flex-1 min-w-[120px] px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold text-center transition-all"><i class="fas fa-external-link-alt me-1"></i> Full Bill Page</a>
                            <a :href="selected.edit_url" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold text-center transition-all"><i class="fas fa-edit me-1"></i> Edit</a>
                            <a :href="selected.pdf_url" target="_blank" class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold text-center transition-all"><i class="fas fa-file-pdf me-1"></i> PDF</a>
                            <button type="button" @click="closeDetails()" class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-dark-border text-xs font-bold hover:bg-gray-200">Close</button>
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
function billHistoryDetails() {
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
            setTimeout(() => { this.selected = null; }, 300);
            document.body.style.overflow = '';
        },
        async copyText(text, field) {
            const value = String(text ?? '').trim();
            if (!value || value === 'N/A' || value === '—') return;
            try {
                await navigator.clipboard.writeText(value);
            } catch {
                const ta = document.createElement('textarea');
                ta.value = value;
                ta.style.position = 'fixed';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            this.copiedField = field;
            clearTimeout(this.copyTimeout);
            this.copyTimeout = setTimeout(() => { this.copiedField = null; }, 1800);
        },
        formatAmount(value) {
            return new Intl.NumberFormat('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value || 0));
        },
        statusBadgeClass(status) {
            const s = String(status || '').toUpperCase();
            if (s === 'ACTIVE') return 'badge-green';
            if (s === 'INACTIVE') return 'badge-red';
            return 'badge-yellow';
        }
    };
}
</script>
@endpush
