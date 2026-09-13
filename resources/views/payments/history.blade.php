@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="space-y-6" x-data="paymentHistoryDetails()" @keydown.escape.window="closeDetails()">
    <!-- Current Account Balances -->
    <div class="grid grid-cols-1 gap-4">
        <div class="card p-5 bg-gradient-to-br from-primary-500 to-primary-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase text-white/70 mb-1">API Live Balance</p>
                    <p class="text-3xl font-black text-white">
                        @if($apiLiveBalance !== null)
                            TZS {{ number_format($apiLiveBalance, 2) }}
                        @else
                            <span class="text-white/70">Loading...</span>
                        @endif
                    </p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-cloud text-2xl text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="card p-1">
        <div class="flex gap-1 overflow-x-auto scrollbar-hide">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'SETTLED', 'page' => 1]) }}"
               class="flex-1 min-w-[110px] flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ ($activeStatus ?? request('status')) === 'SETTLED' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/30' }}">
                <i class="fas fa-check-circle"></i>
                SETTLED
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeStatus ?? request('status')) === 'SETTLED' ? 'bg-white/20' : 'bg-primary-100 dark:bg-primary-900/40' }}">
                    {{ number_format($settledCount ?? 0) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'PROCESSING', 'page' => 1]) }}"
               class="flex-1 min-w-[110px] flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ ($activeStatus ?? request('status')) === 'PROCESSING' ? 'bg-amber-600 text-white shadow-lg shadow-amber-900/20' : 'text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30' }}">
                <i class="fas fa-clock"></i>
                PROCESSING
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeStatus ?? request('status')) === 'PROCESSING' ? 'bg-white/20' : 'bg-amber-100 dark:bg-amber-900/40' }}">
                    {{ number_format($processingCount ?? 0) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'FAILED', 'page' => 1]) }}"
               class="flex-1 min-w-[110px] flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ ($activeStatus ?? request('status')) === 'FAILED' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/30' }}">
                <i class="fas fa-times-circle"></i>
                FAILED
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeStatus ?? request('status')) === 'FAILED' ? 'bg-white/20' : 'bg-primary-100 dark:bg-primary-900/40' }}">
                    {{ number_format($failedCount ?? 0) }}
                </span>
            </a>
        </div>
    </div>

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
        
        <form x-show="showFilters" x-transition method="GET" action="{{ route('payments.history') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4" id="filterForm">
            <input type="hidden" name="status" value="{{ $activeStatus ?? request('status', 'SETTLED') }}">
            <div class="sm:col-span-2 lg:col-span-1">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Search</label>
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Reference, name, phone...">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Type</label>
                <select name="txn_type" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
                    <option value="all" {{ ($typeFilter ?? request('txn_type', 'all')) === 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="payment" {{ ($typeFilter ?? request('txn_type', 'all')) === 'payment' ? 'selected' : '' }}>Payments</option>
                    <option value="ecommerce" {{ ($typeFilter ?? request('txn_type', 'all')) === 'ecommerce' ? 'selected' : '' }}>E-commerce Payments</option>
                    <option value="payout" {{ ($typeFilter ?? request('txn_type', 'all')) === 'payout' ? 'selected' : '' }}>Payouts</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Start Date</label>
                <input type="date" name="start_date" id="startDate" value="{{ request('start_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">End Date</label>
                <input type="date" name="end_date" id="endDate" value="{{ request('end_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Per Page</label>
                <select name="per_page" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
                    <option value="10" {{ ($perPage ?? request('per_page', 20)) == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ ($perPage ?? request('per_page', 20)) == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ ($perPage ?? request('per_page', 20)) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ ($perPage ?? request('per_page', 20)) == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-500 text-white py-2 rounded-lg text-xs font-bold transition-all">
                    Apply Filter
                </button>
                <a href="{{ route('payments.history', ['status' => $activeStatus ?? 'SETTLED']) }}" class="px-3 py-2 bg-gray-100 dark:bg-dark-border rounded-lg text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-200 transition-all">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Export Card -->
    <div x-data="{ showExport: false }" class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-sm text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-file-export text-primary-500"></i> Export Report
            </h3>
            <button @click="showExport = !showExport" class="text-xs text-primary-600 font-bold hover:underline">
                <span x-text="showExport ? 'Hide Export Options' : 'Show Export Options'"></span>
            </button>
        </div>

        <form x-show="showExport" x-transition method="GET" action="{{ route('payments.export.pdf') }}" class="space-y-4">
            <input type="hidden" name="status" value="{{ $activeStatus ?? request('status', 'SETTLED') }}">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
            <input type="hidden" name="currency" value="{{ request('currency') }}">

            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Choose columns to include</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                    @foreach($availableColumns as $columnKey => $columnLabel)
                        <label class="flex items-center gap-2 text-xs bg-primary-50 dark:bg-dark-900 px-3 py-2 rounded-lg border border-primary-100 dark:border-dark-border">
                            <input type="checkbox" name="columns[]" value="{{ $columnKey }}"
                                   {{ in_array($columnKey, $selectedColumns ?? []) ? 'checked' : '' }}
                                   class="rounded border-primary-200 text-primary-600 focus:ring-primary-500">
                            <span class="text-primary-700 dark:text-primary-300">{{ $columnLabel }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-xs font-bold transition-all">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </button>
                <button type="submit" formaction="{{ route('payments.export.excel') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-xs font-bold transition-all">
                    <i class="fas fa-file-excel me-1"></i> Export Excel
                </button>
            </div>
        </form>
    </div>

    <!-- Reconciliation Card -->
    <div class="card p-5 bg-gradient-to-br from-primary-500/10 to-cyan-500/10 border border-primary-200 dark:border-dark-border">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center text-primary-600 dark:text-primary-300 shrink-0">
                    <i class="fas fa-scale-balanced"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-primary-900 dark:text-white">Payment Reconciliation</h3>
                    <p class="text-[11px] text-primary-500 mt-0.5">Compare live ClickPesa API payments against the records stored in this system, and spot missing or mismatched transactions.</p>
                </div>
            </div>
            <a href="{{ route('payments.reconcile') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-bold transition-all whitespace-nowrap">
                <i class="fas fa-rotate-right"></i> Run Reconciliation
            </a>
        </div>
    </div>

    <!-- Transactions Table - Full system, fully responsive -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <p class="text-[11px] font-semibold text-primary-700 dark:text-primary-300 flex items-center gap-1.5"><i class="fas fa-hand-pointer text-primary-500"></i> Click any row to open right drawer — full details, copy IDs, SMS/Email status</p>
            <span class="text-[10px] text-primary-500 hidden lg:inline">Tip: Horizontal scroll on desktop • Cards on mobile</span>
        </div>

        <!-- Desktop / Tablet Table — minimized to important columns only, rest in drawer -->
        <div class="hidden md:block overflow-x-auto">
            <table class="data-table min-w-[640px]">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Date & Time</th>
                        <th>Reference</th>
                        <th>Member Name</th>
                        <th class="whitespace-nowrap">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($displayItems as $item)
                        @if(in_array($item['type'], ['payment', 'billpay', 'ecommerce_payment']))
                            @php
                                $payment = $item['record'];
                                $callbackData = is_array($payment->callback_data ?? null) ? $payment->callback_data : [];
                                $callbackCustomer = is_array($callbackData['customer'] ?? null) ? $callbackData['customer'] : [];
                                $memberName = $payment->customer_name ?? $callbackCustomer['customerName'] ?? $callbackData['customerName'] ?? $payment->payer_name ?? 'N/A';
                                $actualPayer = $payment->payer_name ?? $callbackData['payer_name'] ?? $callbackCustomer['customerName'] ?? $memberName;
                                $displayPhone = $payment->phone ?? $callbackData['paymentPhoneNumber'] ?? $callbackCustomer['customerPhoneNumber'] ?? 'N/A';
                                $displayDescription = $payment->resolvedDescription();
                                $status = strtoupper($payment->status ?? 'UNKNOWN');
                                $isSettled = in_array($status, ['SETTLED', 'SUCCESS']);
                                $createdAt = $payment->created_at ? \Illuminate\Support\Carbon::parse($payment->created_at) : null;
                                $updatedAt = $payment->updated_at ? \Illuminate\Support\Carbon::parse($payment->updated_at) : null;
                                $smsSentAt = $payment->sms_sent_at ? \Illuminate\Support\Carbon::parse($payment->sms_sent_at) : null;
                                $emailSentAt = $payment->email_sent_at ? \Illuminate\Support\Carbon::parse($payment->email_sent_at) : null;
                                $detailPayload = [
                                    'reference' => $payment->order_reference,
                                    'transaction_id' => $payment->transaction_id ?? 'N/A',
                                    'status' => $status,
                                    'isSettled' => $isSettled,
                                    'amount' => (float) $payment->amount,
                                    'currency' => $payment->currency ?? 'TZS',
                                    'member_name' => $memberName,
                                    'payer_name' => $actualPayer,
                                    'phone' => $displayPhone,
                                    'email' => $payment->email,
                                    'payment_method' => $payment->payment_method ?? 'N/A',
                                    'description' => $displayDescription,
                                    'date' => $createdAt?->format('d M, Y'),
                                    'time' => $createdAt?->format('H:i:s'),
                                    'created_at' => $createdAt?->toIso8601String(),
                                    'updated_at' => $updatedAt?->toIso8601String(),
                                    'sms_sent' => (bool) $payment->sms_sent,
                                    'sms_sent_at' => $smsSentAt?->format('d M, Y H:i:s'),
                                    'sms_message' => $payment->sms_message,
                                    'sms_error' => $payment->sms_error,
                                    'email_sent' => (bool) $payment->email_sent,
                                    'email_sent_at' => $emailSentAt?->format('d M, Y H:i:s'),
                                    'email_error' => $payment->email_error,
                                    'status_url' => route('payments.status', ['reference' => $payment->order_reference]),
                                    'receipt_url' => route('payments.receipt', $payment->order_reference),
                                ];
                            @endphp
                            <tr @click="openDetails(@js($detailPayload))" class="hover:bg-primary-50/70 dark:hover:bg-primary-900/10 transition-colors cursor-pointer group">
                                <td class="whitespace-nowrap py-3.5 px-4">
                                    <div class="font-bold text-primary-900 dark:text-white text-xs">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div>
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[8px] font-bold" :class="statusBadgeClass('{{ $status }}') === 'badge-green' ? 'bg-green-100 text-green-700' : (statusBadgeClass('{{ $status }}') === 'badge-red' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700')">{{ $status }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="flex items-center gap-1.5 max-w-[180px]">
                                        <span class="font-mono text-[11px] bg-primary-50 dark:bg-dark-900 px-2 py-1 rounded border border-primary-100 dark:border-dark-border text-primary-700 dark:text-primary-300 truncate" title="{{ $payment->order_reference }}">{{ Str::limit($payment->order_reference, 18) }}</span>
                                        <button type="button" @click.stop="copyText(@js($payment->order_reference), 'ref-{{ $payment->id }}')" class="shrink-0 w-7 h-7 rounded-lg bg-white border border-primary-100 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all" title="Copy reference"><i class="fas text-[10px]" :class="copiedField === 'ref-{{ $payment->id }}' ? 'fa-check' : 'fa-copy'"></i></button>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="font-bold text-primary-900 dark:text-white text-xs truncate max-w-[160px]">{{ $memberName }}</div>
                                    <div class="text-[10px] font-mono text-primary-500 truncate max-w-[160px]">{{ $displayPhone }}</div>
                                </td>
                                <td class="whitespace-nowrap py-3.5 px-4">
                                    <div class="font-bold text-green-600 dark:text-green-400 text-sm">+ {{ number_format((float)$payment->amount, 2) }}</div>
                                    <div class="text-[10px] font-bold text-primary-500 uppercase">{{ $payment->currency ?? 'TZS' }}</div>
                                </td>
                            </tr>
                        @elseif($item['type'] === 'payout')
                            @php
                                $payout = $item['record'];
                                $status = strtoupper($payout->status ?? 'UNKNOWN');
                                $isSettled = in_array($status, ['SUCCESS', 'SETTLED', 'COMPLETED']);
                                $createdAt = $payout->created_at ? \Illuminate\Support\Carbon::parse($payout->created_at) : null;
                                $updatedAt = $payout->updated_at ? \Illuminate\Support\Carbon::parse($payout->updated_at) : null;
                                $detailPayload = [
                                    'reference' => $payout->order_reference,
                                    'transaction_id' => $payout->clickpesa_payout_id ?? 'N/A',
                                    'status' => $status,
                                    'isSettled' => $isSettled,
                                    'amount' => (float) $payout->amount,
                                    'currency' => $payout->currency ?? 'TZS',
                                    'member_name' => $payout->recipient_name ?? 'N/A',
                                    'payer_name' => $payout->recipient_name ?? 'N/A',
                                    'phone' => $payout->recipient_phone ?? $payout->beneficiary_mobile ?? 'N/A',
                                    'email' => $payout->beneficiary_email ?? null,
                                    'payment_method' => $payout->channel ?? 'N/A',
                                    'description' => $payout->resolvedDescription(),
                                    'date' => $createdAt?->format('d M, Y'),
                                    'time' => $createdAt?->format('H:i:s'),
                                    'created_at' => $createdAt?->toIso8601String(),
                                    'updated_at' => $updatedAt?->toIso8601String(),
                                    'status_url' => null,
                                    'receipt_url' => null,
                                ];
                            @endphp
                            <tr @click="openDetails(@js($detailPayload))" class="hover:bg-red-50/50 dark:hover:bg-red-900/10 transition-colors cursor-pointer">
                                <td class="whitespace-nowrap py-3.5 px-4"><div class="font-bold text-primary-900 dark:text-white text-xs">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div><div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div><span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[8px] font-bold bg-red-100 text-red-700">{{ $status }}</span></td>
                                <td class="py-3.5 px-3"><div class="flex items-center gap-1.5 max-w-[180px]"><span class="font-mono text-[11px] bg-red-50 dark:bg-dark-900 px-2 py-1 rounded border border-red-100 text-red-700 truncate" title="{{ $payout->order_reference }}">{{ Str::limit($payout->order_reference, 18) }}</span><button type="button" @click.stop="copyText(@js($payout->order_reference), 'ref-{{ $payout->id }}')" class="shrink-0 w-7 h-7 rounded-lg bg-white border border-red-100 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white" title="Copy"><i class="fas text-[10px]" :class="copiedField === 'ref-{{ $payout->id }}' ? 'fa-check' : 'fa-copy'"></i></button></div></td>
                                <td class="py-3.5 px-3"><div class="font-bold text-xs text-primary-900 dark:text-white truncate max-w-[160px]">{{ $payout->recipient_name ?? 'N/A' }}</div><div class="text-[10px] font-mono text-primary-500 truncate max-w-[160px]">{{ $payout->recipient_phone ?? $payout->beneficiary_mobile ?? 'N/A' }}</div></td>
                                <td class="whitespace-nowrap py-3.5 px-4"><div class="font-bold text-red-600 text-sm">- {{ number_format((float)$payout->amount, 2) }}</div><div class="text-[10px] font-bold uppercase text-primary-500">{{ $payout->currency ?? 'TZS' }}</div></td>
                            </tr>
                        @elseif($item['type'] === 'payout-fee')
                            @php $payout = $item['record']; $fee = $item['fee']; $status = strtoupper($payout->status ?? 'UNKNOWN'); $createdAt = $payout->created_at ? \Illuminate\Support\Carbon::parse($payout->created_at) : null; @endphp
                            <tr class="hover:bg-red-50/30 dark:hover:bg-red-900/10">
                                <td class="whitespace-nowrap py-3.5 px-4"><div class="font-bold text-xs">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div><div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div></td>
                                <td class="py-3.5 px-3"><span class="font-mono text-[11px] bg-red-50 dark:bg-dark-900 px-2 py-1 rounded border border-red-100 text-red-700">{{ Str::limit($payout->order_reference, 18) }}-FEE</span></td>
                                <td class="py-3.5 px-3"><span class="font-bold text-xs">Payout Fee</span><div class="text-[10px] text-primary-500">Fee</div></td>
                                <td class="whitespace-nowrap py-3.5 px-4"><div class="font-bold text-red-600 text-sm">- {{ number_format((float)$fee, 2) }}</div><div class="text-[10px] font-bold uppercase text-primary-500">TZS</div></td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="4" class="text-center py-20"><div class="flex flex-col items-center"><div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-dark-900 flex items-center justify-center mb-4"><i class="fas fa-folder-open text-2xl text-primary-200"></i></div><h4 class="font-bold text-primary-900 dark:text-white">No Transactions Found</h4><p class="text-xs text-primary-500">@if(($activeStatus ?? 'SETTLED') === 'FAILED') No failed payments/payouts match your filters. @else No settled payments/payouts match your filters. @endif</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards - fully responsive fallback -->
        <div class="md:hidden divide-y divide-primary-50 dark:divide-dark-border">
            @forelse($displayItems as $item)
                @php
                    if(in_array($item['type'], ['payment','billpay','ecommerce_payment'])) {
                        $r=$item['record'];
                        $cb=is_array($r->callback_data ?? null)?$r->callback_data:[];
                        $cbc=is_array($cb['customer'] ?? null)?$cb['customer']:[];
                        $mn=$r->customer_name ?? $cbc['customerName'] ?? $cb['customerName'] ?? $r->payer_name ?? 'N/A';
                        $st=strtoupper($r->status ?? 'UNKNOWN');
                        $ca=$r->created_at? \Illuminate\Support\Carbon::parse($r->created_at):null;
                        $amt=(float)$r->amount;
                        $cur=$r->currency ?? 'TZS';
                        $ref=$r->order_reference;
                        $desc=$r->resolvedDescription();
                        $payload=['reference'=>$ref,'transaction_id'=>$r->transaction_id??'N/A','status'=>$st,'isSettled'=>in_array($st,['SETTLED','SUCCESS']),'amount'=>$amt,'currency'=>$cur,'member_name'=>$mn,'payer_name'=>$r->payer_name??$mn,'phone'=>$r->phone??'N/A','email'=>$r->email,'payment_method'=>$r->payment_method??'N/A','description'=>$desc,'date'=>$ca?->format('d M, Y'),'time'=>$ca?->format('H:i:s'),'status_url'=>route('payments.status',['reference'=>$ref]),'receipt_url'=>route('payments.receipt',$ref),'sms_sent'=>(bool)$r->sms_sent,'sms_sent_at'=>$r->sms_sent_at? \Illuminate\Support\Carbon::parse($r->sms_sent_at)->format('d M, H:i') : null,'email_sent'=>(bool)$r->email_sent];
                    } elseif($item['type']==='payout'){
                        $r=$item['record']; $st=strtoupper($r->status??'UNKNOWN'); $ca=$r->created_at? \Illuminate\Support\Carbon::parse($r->created_at):null;
                        $payload=['reference'=>$r->order_reference,'transaction_id'=>$r->clickpesa_payout_id??'N/A','status'=>$st,'isSettled'=>in_array($st,['SUCCESS','SETTLED','COMPLETED']),'amount'=>(float)$r->amount,'currency'=>$r->currency??'TZS','member_name'=>$r->recipient_name??'N/A','payer_name'=>$r->recipient_name??'N/A','phone'=>$r->recipient_phone??'N/A','email'=>null,'payment_method'=>$r->channel??'N/A','description'=>$r->resolvedDescription(),'date'=>$ca?->format('d M, Y'),'time'=>$ca?->format('H:i:s'),'status_url'=>null,'receipt_url'=>null,'sms_sent'=>false,'email_sent'=>false];
                        $mn=$r->recipient_name; $ref=$r->order_reference; $amt=(float)$r->amount; $cur=$r->currency??'TZS'; $desc=$r->resolvedDescription();
                    } else { continue; }
                @endphp
                <div @click="openDetails(@js($payload))" class="p-4 flex items-center gap-3 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 cursor-pointer active:bg-primary-50">
                    <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-xs {{ str_starts_with($st,'FAILED')?'bg-red-500':(in_array($st,['SETTLED','SUCCESS'])?'bg-green-500':'bg-amber-500') }}">{{ substr($cur,0,1) }}</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-primary-900 dark:text-white truncate">{{ $ref }}</span>
                            <span class="shrink-0 px-1.5 py-0.5 rounded-full text-[8px] font-bold {{ in_array($st,['SETTLED','SUCCESS'])?'bg-green-100 text-green-700':(str_starts_with($st,'FAILED')?'bg-red-100 text-red-700':'bg-amber-100 text-amber-700') }}">{{ $st }}</span>
                        </div>
                        <div class="text-xs font-semibold text-primary-700 dark:text-primary-300 truncate">{{ $mn }}</div>
                        <div class="text-[11px] text-primary-500 truncate">{{ $desc }}</div>
                        <div class="text-[10px] text-primary-400">{{ $ca?->format('M d, H:i') ?? '' }} • {{ $payload['phone'] }}</div>
                    </div>
                    <div class="shrink-0 text-right">
                        <div class="font-bold text-xs {{ $item['type']==='payout'?'text-red-600':'text-green-600' }}">{{ $item['type']==='payout' ? '-' : '+' }}{{ number_format($amt,2) }} <span class="text-[9px]">{{ $cur }}</span></div>
                        <i class="fas fa-chevron-right text-[10px] text-primary-300 mt-1"></i>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center"><i class="fas fa-folder-open text-2xl text-primary-200"></i><p class="text-sm font-bold text-primary-900 dark:text-white mt-3">No Transactions</p></div>
            @endforelse
        </div>
        
        @if($displayItems->hasPages())
            <div class="p-4 bg-primary-50/30 dark:bg-dark-900/30 border-t border-primary-50 dark:border-dark-border">
                {{ $displayItems->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <!-- Right Drawer - fixed to viewport, fully scrollable, never cut at top -->
    <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex justify-end overflow-hidden" @keydown.escape.window="closeDetails()">
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeDetails()"></div>
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[520px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
            <!-- Drawer Header -->
            <div class="shrink-0 flex items-start justify-between gap-4 px-5 py-4 border-b border-primary-100 dark:border-dark-border bg-primary-50/50 dark:bg-dark-900/50">
                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-black text-primary-900 dark:text-white flex items-center gap-2"><i class="fas fa-receipt text-primary-600"></i> Payment Details</h3>
                    <p class="text-[10px] text-primary-500 uppercase tracking-widest">Full system view • Right drawer</p>
                    <p x-show="selected" class="font-mono text-xs font-bold text-primary-700 dark:text-primary-300 mt-1 truncate" x-text="selected?.reference"></p>
                </div>
                <button type="button" @click="closeDetails()" class="shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-dark-800 border border-primary-100 dark:border-dark-border text-primary-600 hover:bg-primary-50 flex items-center justify-center"><i class="fas fa-times text-xs"></i></button>
            </div>

            <!-- Drawer Body scroll - min-h-0 ensures flex child can shrink and scroll without cutting top -->
            <div class="flex-1 min-h-0 overflow-y-auto p-5 space-y-5 overscroll-contain" x-show="selected">
                <template x-if="selected">
                    <div class="space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl bg-primary-50/70 dark:bg-dark-800 border border-primary-100 dark:border-dark-border">
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-bold uppercase text-primary-500">Reference</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <p class="font-mono font-bold text-primary-900 dark:text-white break-all text-sm" x-text="selected.reference"></p>
                                    <button type="button" @click="copyText(selected.reference, 'reference')" class="shrink-0 w-7 h-7 rounded-lg bg-white dark:bg-dark-900 border border-primary-100 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white" title="Copy"><i class="fas text-[10px]" :class="copiedField === 'reference' ? 'fa-check' : 'fa-copy'"></i></button>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-[10px] font-bold uppercase text-primary-500">Amount</p>
                                <p class="text-lg font-black text-primary-600 dark:text-primary-400"><span x-text="selected.currency"></span> <span x-text="formatAmount(selected.amount)"></span></p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold" :class="statusBadgeClass(selected.status)" x-text="selected.status"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-3 p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-user-circle"></i> Member</h4>
                                <div><p class="text-[10px] font-bold uppercase text-primary-400">Member Name</p><p class="font-bold text-sm text-primary-900 dark:text-white" x-text="selected.member_name"></p></div>
                                <div><p class="text-[10px] font-bold uppercase text-primary-400">Payer</p><p class="font-semibold text-sm" x-text="selected.payer_name"></p></div>
                                <div><p class="text-[10px] font-bold uppercase text-primary-400">Phone</p><p class="font-mono text-sm flex items-center gap-2"><span x-text="selected.phone"></span><button type="button" @click="copyText(selected.phone,'phone')" class="w-6 h-6 rounded border border-primary-100 flex items-center justify-center hover:bg-primary-50"><i class="fas fa-copy text-[9px]"></i></button></p></div>
                                <template x-if="selected.email"><div><p class="text-[10px] font-bold uppercase text-primary-400">Email</p><p class="text-sm break-all" x-text="selected.email"></p></div></template>
                            </div>
                            <div class="space-y-3 p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-white dark:bg-dark-800">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-receipt"></i> Transaction</h4>
                                <div class="flex justify-between items-start gap-2 border-b border-primary-50 dark:border-dark-border pb-2"><span class="text-xs text-primary-500 shrink-0">Transaction ID</span><div class="flex items-center gap-1.5 min-w-0 justify-end"><span class="font-mono text-xs font-bold break-all text-right" x-text="selected.transaction_id"></span><button type="button" @click="copyText(selected.transaction_id,'transaction_id')" :disabled="!selected.transaction_id || selected.transaction_id==='N/A'" class="shrink-0 w-7 h-7 rounded-lg bg-primary-50 border border-primary-100 flex items-center justify-center hover:bg-primary-600 hover:text-white disabled:opacity-40"><i class="fas text-[10px]" :class="copiedField==='transaction_id'?'fa-check':'fa-copy'"></i></button></div></div>
                                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2"><span class="text-xs text-primary-500">Method</span><span class="text-xs font-bold" x-text="selected.payment_method"></span></div>
                                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2"><span class="text-xs text-primary-500">Date & Time</span><span class="text-xs font-bold"><span x-text="selected.date"></span> <span x-text="selected.time"></span></span></div>
                                <div class="flex justify-between"><span class="text-xs text-primary-500">Created</span><span class="text-[11px] font-mono" x-text="selected.created_at ? new Date(selected.created_at).toLocaleString() : ''"></span></div>
                            </div>
                        </div>

                        <div><p class="text-[10px] font-bold uppercase text-primary-500 mb-1">Purpose / Description</p><p class="text-sm bg-primary-50/70 dark:bg-dark-800 rounded-xl p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap" x-text="selected.description"></p></div>

                        <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-primary-50/30 dark:bg-dark-800/50 space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-sms"></i> SMS</h4>
                            <div class="flex items-center gap-2"><template x-if="selected.sms_sent"><span class="badge badge-green text-[10px]"><i class="fas fa-check me-1"></i> Sent</span></template><template x-if="!selected.sms_sent && selected.sms_error"><span class="badge badge-red text-[10px]">Failed</span></template><template x-if="!selected.sms_sent && !selected.sms_error"><span class="badge badge-yellow text-[10px]">Not Sent</span></template><template x-if="selected.sms_sent_at"><span class="text-[10px] text-primary-500" x-text="'at ' + selected.sms_sent_at"></span></template></div>
                            <template x-if="selected.sms_error"><p class="text-xs font-bold text-red-600" x-text="'Error: ' + selected.sms_error"></p></template>
                            <template x-if="selected.sms_message"><p class="text-xs bg-white dark:bg-dark-900 rounded-lg p-3 border whitespace-pre-wrap max-h-40 overflow-y-auto" x-text="selected.sms_message"></p></template>
                            <template x-if="!selected.sms_message && !selected.sms_error && !selected.sms_sent"><p class="text-xs text-primary-500 italic">No SMS sent yet.</p></template>
                        </div>

                        <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-primary-50/30 dark:bg-dark-800/50 space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2"><i class="fas fa-envelope"></i> Email</h4>
                            <div class="flex items-center gap-2"><template x-if="selected.email_sent"><span class="badge badge-green text-[10px]">Sent</span></template><template x-if="!selected.email_sent && selected.email_error"><span class="badge badge-red text-[10px]">Failed</span></template><template x-if="!selected.email_sent && !selected.email_error"><span class="badge badge-yellow text-[10px]">Not Sent</span></template><template x-if="selected.email_sent_at"><span class="text-[10px] text-primary-500" x-text="'at ' + selected.email_sent_at"></span></template></div>
                            <template x-if="selected.email_error"><p class="text-xs font-bold text-red-600" x-text="'Error: ' + selected.email_error"></p></template>
                            <template x-if="selected.email_message"><p class="text-xs bg-white dark:bg-dark-900 rounded-lg p-3 border" x-text="selected.email_message"></p></template>
                            <template x-if="!selected.email_message && !selected.email_error && !selected.email_sent"><p class="text-xs text-primary-500 italic">No email sent yet.</p></template>
                        </div>

                        <!-- Receipt Preview (inline, no download) -->
                        <template x-if="selected?.isSettled && selected?.receipt_url">
                            <div class="space-y-2">
                                <button type="button" @click="showReceipt = !showReceipt" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed transition-all text-xs font-bold" :class="showReceipt ? 'bg-primary-600 border-primary-600 text-white' : 'bg-white dark:bg-dark-800 border-primary-200 hover:bg-primary-50 text-primary-700'">
                                    <i class="fas" :class="showReceipt ? 'fa-eye-slash' : 'fa-file-pdf'"></i>
                                    <span x-text="showReceipt ? 'Hide Receipt Preview' : 'Preview Receipt (in-drawer)'"></span>
                                </button>
                                <div x-show="showReceipt" x-transition class="rounded-xl border border-primary-200 dark:border-dark-border overflow-hidden bg-white">
                                    <div class="px-3 py-2 bg-primary-50 dark:bg-dark-800 border-b border-primary-100 flex items-center justify-between">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-primary-600">Receipt Preview — no download</span>
                                        <span class="text-[10px] font-mono text-primary-500 truncate max-w-[160px]" x-text="selected.reference"></span>
                                    </div>
                                    <iframe :src="selected.receipt_url + (selected.receipt_url.includes('?') ? '&' : '?') + 'preview=1#toolbar=0&navpanes=0&scrollbar=0'" class="w-full h-[520px] bg-white" loading="lazy" title="Receipt preview"></iframe>
                                    <div class="p-2 bg-amber-50 border-t border-amber-100 text-[10px] text-amber-700 text-center">Preview only — printing allowed, download disabled by policy.</div>
                                </div>
                            </div>
                        </template>

                        <div class="flex flex-wrap gap-2 pt-2">
                            <a :href="selected.status_url" class="flex-1 min-w-[120px] px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold text-center transition-all"><i class="fas fa-external-link-alt me-1"></i> Full Page</a>
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
function paymentHistoryDetails() {
    let debounceTimer = null;
    return {
        open: false,
        selected: null,
        copiedField: null,
        copyTimeout: null,
        showReceipt: false,
        init() {
            const searchInput = document.getElementById('searchInput');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const filterForm = document.getElementById('filterForm');
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => { filterForm.submit(); }, 500);
                });
            }
            if (startDate) startDate.addEventListener('change', () => filterForm.submit());
            if (endDate) endDate.addEventListener('change', () => filterForm.submit());
            // ESC already handled via @keydown.escape
        },
        openDetails(payload) {
            this.selected = payload;
            this.showReceipt = false;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        closeDetails() {
            this.open = false;
            this.showReceipt = false;
            setTimeout(()=>{ this.selected=null; }, 300);
            document.body.style.overflow = '';
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
        formatAmount(value) {
            return new Intl.NumberFormat('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(value || 0));
        },
        statusBadgeClass(status) {
            const s = String(status || '').toUpperCase();
            if (['SETTLED', 'SUCCESS'].includes(s)) return 'badge-green';
            if (['FAILED', 'ERROR', 'CANCELLED'].includes(s)) return 'badge-red';
            return 'badge-yellow';
        }
    };
}
</script>
@endpush
