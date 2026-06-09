@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="space-y-6" x-data="paymentHistoryDetails()">
    <!-- Current Account Balances -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
        
        <div class="card p-5 bg-gradient-to-br from-green-500 to-green-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase text-white/70 mb-1">Internal Database Balance</p>
                    <p class="text-3xl font-black text-white">
                        TZS {{ number_format($internalDbBalance, 2) }}
                    </p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-database text-2xl text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="card p-1">
        <div class="flex gap-1">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'SETTLED', 'page' => 1]) }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ ($activeStatus ?? request('status')) === 'SETTLED' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/30' }}">
                <i class="fas fa-check-circle"></i>
                SETTLED
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeStatus ?? request('status')) === 'SETTLED' ? 'bg-white/20' : 'bg-primary-100 dark:bg-primary-900/40' }}">
                    {{ number_format($settledCount ?? 0) }}
                </span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'FAILED', 'page' => 1]) }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all {{ ($activeStatus ?? request('status')) === 'FAILED' ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/20' : 'text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/30' }}">
                <i class="fas fa-times-circle"></i>
                FAILED
                <span class="text-[10px] px-2 py-0.5 rounded-full {{ ($activeStatus ?? request('status')) === 'FAILED' ? 'bg-white/20' : 'bg-primary-100 dark:bg-primary-900/40' }}">
                    {{ number_format($failedCount ?? 0) }}
                </span>
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div x-data="{ showFilters: true }" class="card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-sm text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-filter text-primary-500"></i> Advanced Filters
            </h3>
            <button @click="showFilters = !showFilters" class="text-xs text-primary-600 font-bold hover:underline">
                <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
            </button>
        </div>
        
        <form x-show="showFilters" x-transition method="GET" action="{{ route('payments.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4" id="filterForm">
            <input type="hidden" name="status" value="{{ $activeStatus ?? request('status', 'SETTLED') }}">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Search</label>
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 outline-none" placeholder="Reference, name, phone...">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">Start Date</label>
                <input type="date" name="start_date" id="startDate" value="{{ request('start_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-1">End Date</label>
                <input type="date" name="end_date" id="endDate" value="{{ request('end_date') }}" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-lg px-3 py-2 text-xs outline-none">
            </div>
            <div class="flex items-end gap-2">
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
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
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

    <!-- Transactions Table -->
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-primary-50 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30">
            <p class="text-[10px] text-primary-500">Click <i class="fas fa-eye"></i> on any row to preview full details</p>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Reference</th>
                        <th>Member Name</th>
                        <th>Purpose / Description</th>
                        <th>Amount</th>
                        <th>SMS Status</th>
                        <th>Email Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($combinedWithBalance as $item)
                        @if($item['type'] === 'payment')
                            @php
                                $payment = $item['record'];
                                $callbackData = is_array($payment->callback_data ?? null) ? $payment->callback_data : [];
                                $callbackCustomer = is_array($callbackData['customer'] ?? null) ? $callbackData['customer'] : [];

                                $memberName = $payment->customer_name
                                    ?? $callbackCustomer['customerName']
                                    ?? $callbackData['customerName']
                                    ?? $payment->payer_name
                                    ?? 'N/A';

                                $actualPayer = $payment->payer_name
                                    ?? $callbackData['payer_name']
                                    ?? $callbackCustomer['customerName']
                                    ?? $memberName;

                                $displayPhone = $payment->phone
                                    ?? $callbackData['paymentPhoneNumber']
                                    ?? $callbackCustomer['customerPhoneNumber']
                                    ?? 'N/A';

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
                            <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                                <td class="whitespace-nowrap">
                                    <div class="font-bold text-primary-900 dark:text-white">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5 max-w-[200px]">
                                        <span class="font-mono text-[11px] bg-primary-50 dark:bg-dark-900 px-2 py-1 rounded border border-primary-100 dark:border-dark-border text-primary-700 dark:text-primary-300 truncate" title="{{ $payment->order_reference }}">
                                            {{ $payment->order_reference }}
                                        </span>
                                        <button type="button"
                                                @click.stop="copyText(@js($payment->order_reference), 'ref-{{ $payment->id }}')"
                                                class="shrink-0 w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                                title="Copy reference">
                                            <i class="fas text-[10px]" :class="copiedField === 'ref-{{ $payment->id }}' ? 'fa-check' : 'fa-copy'"></i>
                                        </button>
                                    </div>
                                    @if($payment->transaction_id)
                                        <div class="flex items-center gap-1.5 mt-1 max-w-[200px]">
                                            <span class="font-mono text-[9px] text-primary-500 truncate" title="{{ $payment->transaction_id }}">TX: {{ $payment->transaction_id }}</span>
                                            <button type="button"
                                                    @click.stop="copyText(@js($payment->transaction_id), 'tx-{{ $payment->id }}')"
                                                    class="shrink-0 w-6 h-6 rounded-md bg-primary-50 dark:bg-primary-900/20 text-primary-500 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                                    title="Copy transaction ID">
                                                <i class="fas text-[9px]" :class="copiedField === 'tx-{{ $payment->id }}' ? 'fa-check' : 'fa-copy'"></i>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-bold text-primary-900 dark:text-white">{{ $memberName }}</div>
                                    <div class="text-[10px] text-primary-500">Payer: {{ $actualPayer }}</div>
                                    <div class="text-[10px] text-primary-500 font-mono">{{ $displayPhone }}</div>
                                </td>
                                <td>
                                    <div class="text-xs text-primary-700 dark:text-primary-400 max-w-[220px] truncate" title="{{ $displayDescription }}">
                                        {{ $displayDescription }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">
                                    <div class="font-bold text-green-600 dark:text-green-400">
                                        + {{ number_format((float)$payment->amount, 2) }}
                                    </div>
                                    <div class="text-[10px] text-primary-500 uppercase font-bold">{{ $payment->currency ?? 'TZS' }}</div>
                                </td>
                                <td class="whitespace-nowrap">
                                    @if($payment->sms_sent)
                                        <span class="badge badge-green text-[10px]">
                                            <i class="fas fa-check me-1"></i> Sent
                                        </span>
                                        @if($payment->sms_sent_at)
                                            <div class="text-[9px] text-primary-500 mt-1">{{ \Illuminate\Support\Carbon::parse($payment->sms_sent_at)->format('d M, H:i') }}</div>
                                        @endif
                                    @elseif($payment->sms_error)
                                        <span class="badge badge-red text-[10px]">
                                            <i class="fas fa-times me-1"></i> Failed
                                        </span>
                                    @elseif($isSettled)
                                        <span class="badge badge-yellow text-[10px]">Not Sent</span>
                                    @else
                                        <span class="text-[10px] text-primary-400">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">
                                    @if($payment->email_sent)
                                        <span class="badge badge-green text-[10px]">
                                            <i class="fas fa-check me-1"></i> Sent
                                        </span>
                                        @if($payment->email_sent_at)
                                            <div class="text-[9px] text-primary-500 mt-1">{{ \Illuminate\Support\Carbon::parse($payment->email_sent_at)->format('d M, H:i') }}</div>
                                        @endif
                                    @elseif($payment->email_error)
                                        <span class="badge badge-red text-[10px]">
                                            <i class="fas fa-times me-1"></i> Failed
                                        </span>
                                    @elseif($isSettled)
                                        <span class="badge badge-yellow text-[10px]">Not Sent</span>
                                    @else
                                        <span class="text-[10px] text-primary-400">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2 justify-center">
                                        <button type="button"
                                                @click="openDetails(@js($detailPayload))"
                                                class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                                title="Preview details">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                        <a href="{{ route('payments.status', ['reference' => $payment->order_reference]) }}"
                                           class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                           title="Full payment page">
                                            <i class="fas fa-external-link-alt text-xs"></i>
                                        </a>
                                        <a href="{{ route('payments.receipt', $payment->order_reference) }}"
                                           class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                           title="Download receipt">
                                            <i class="fas fa-file-download text-xs"></i>
                                        </a>
                                    </div>
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
                        @elseif($item['type'] === 'payout-fee')
                            @php
                                $payout = $item['record'];
                                $fee = $item['fee'];
                                $status = strtoupper($payout->status ?? 'UNKNOWN');
                                $isSettled = in_array($status, ['SUCCESS', 'SETTLED', 'COMPLETED']);
                                $createdAt = $payout->created_at ? \Illuminate\Support\Carbon::parse($payout->created_at) : null;
                            @endphp
                            <tr class="hover:bg-red-50/50 dark:hover:bg-red-900/10 transition-colors">
                                <td class="whitespace-nowrap">
                                    <div class="font-bold text-primary-900 dark:text-white">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5 max-w-[200px]">
                                        <span class="font-mono text-[11px] bg-red-50 dark:bg-dark-900 px-2 py-1 rounded border border-red-100 dark:border-red-900/30 text-red-700 dark:text-red-300 truncate" title="{{ $payout->order_reference }}-FEE">
                                            {{ $payout->order_reference }}-FEE
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-bold text-primary-900 dark:text-white">Payout Fee</div>
                                </td>
                                <td>
                                    <div class="text-xs text-primary-700 dark:text-primary-400 max-w-[220px] truncate">
                                        Fee for payout {{ $payout->order_reference }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">
                                    <div class="font-bold text-red-600 dark:text-red-400">
                                        - {{ number_format((float)$fee, 2) }}
                                    </div>
                                    <div class="text-[10px] text-primary-500 uppercase font-bold">{{ $payout->currency ?? 'TZS' }}</div>
                                </td>
                                <td class="whitespace-nowrap text-center">
                                    <span class="text-[10px] text-primary-400">—</span>
                                </td>
                                <td class="whitespace-nowrap text-center">
                                    <span class="text-[10px] text-primary-400">—</span>
                                </td>
                                <td>
                                    <div class="flex gap-2 justify-center">
                                        <a href="{{ route('payouts.status', $payout->order_reference) }}"
                                           class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all"
                                           title="View payout">
                                            <i class="fas fa-external-link-alt text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @else
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
                            <tr class="hover:bg-red-50/50 dark:hover:bg-red-900/10 transition-colors">
                                <td class="whitespace-nowrap">
                                    <div class="font-bold text-primary-900 dark:text-white">{{ $createdAt?->format('M d, Y') ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-primary-500">{{ $createdAt?->format('H:i:s') ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1.5 max-w-[200px]">
                                        <span class="font-mono text-[11px] bg-red-50 dark:bg-dark-900 px-2 py-1 rounded border border-red-100 dark:border-red-900/30 text-red-700 dark:text-red-300 truncate" title="{{ $payout->order_reference }}">
                                            {{ $payout->order_reference }}
                                        </span>
                                        <button type="button"
                                                @click.stop="copyText(@js($payout->order_reference), 'ref-{{ $payout->id }}')"
                                                class="shrink-0 w-7 h-7 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all"
                                                title="Copy reference">
                                            <i class="fas text-[10px]" :class="copiedField === 'ref-{{ $payout->id }}' ? 'fa-check' : 'fa-copy'"></i>
                                        </button>
                                    </div>
                                    @if($payout->clickpesa_payout_id)
                                        <div class="flex items-center gap-1.5 mt-1 max-w-[200px]">
                                            <span class="font-mono text-[9px] text-red-500 truncate" title="{{ $payout->clickpesa_payout_id }}">PAYOUT: {{ $payout->clickpesa_payout_id }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-bold text-primary-900 dark:text-white">{{ $payout->recipient_name ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-primary-500">Recipient: {{ $payout->recipient_name ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-primary-500 font-mono">{{ $payout->recipient_phone ?? $payout->beneficiary_mobile ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="text-xs text-primary-700 dark:text-primary-400 max-w-[220px] truncate" title="{{ $payout->resolvedDescription() }}">
                                        {{ $payout->resolvedDescription() }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">
                                    <div class="font-bold text-red-600 dark:text-red-400">
                                        - {{ number_format((float)$payout->amount, 2) }}
                                    </div>
                                    <div class="text-[10px] text-primary-500 uppercase font-bold">{{ $payout->currency ?? 'TZS' }}</div>
                                </td>
                                <td class="whitespace-nowrap text-center">
                                    <span class="text-[10px] text-primary-400">—</span>
                                </td>
                                <td class="whitespace-nowrap text-center">
                                    <span class="text-[10px] text-primary-400">—</span>
                                </td>
                                <td>
                                    <div class="flex gap-2 justify-center">
                                        <button type="button"
                                                @click="openDetails(@js($detailPayload))"
                                                class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition-all"
                                                title="Preview details">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-20">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-dark-900 flex items-center justify-center mb-4">
                                        <i class="fas fa-folder-open text-2xl text-primary-200"></i>
                                    </div>
                                    <h4 class="font-bold text-primary-900 dark:text-white">No Transactions Found</h4>
                                    <p class="text-xs text-primary-500">
                                        @if(($activeStatus ?? 'SETTLED') === 'FAILED')
                                            No failed payments/payouts match your filters.
                                        @else
                                            No settled payments/payouts match your filters.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payment detail modal -->
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeDetails()">
        <div class="absolute inset-0 bg-black/50" @click="closeDetails()"></div>
        <div class="relative w-full max-w-2xl card p-6 max-h-[90vh] overflow-y-auto animate-fade-in" @click.stop>
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-black text-primary-900 dark:text-white">Payment Details</h3>
                    <p class="text-[10px] text-primary-500 uppercase tracking-widest mt-1">Payment History Preview</p>
                </div>
                <button type="button" @click="closeDetails()" class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-dark-900 text-primary-600 hover:bg-primary-100 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <template x-if="selected">
                <div class="space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl bg-primary-50/50 dark:bg-dark-900/50 border border-primary-100 dark:border-dark-border">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold uppercase text-primary-500">Reference</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <p class="font-mono font-bold text-primary-900 dark:text-white break-all" x-text="selected.reference"></p>
                                <button type="button"
                                        @click="copyText(selected.reference, 'reference')"
                                        class="shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-dark-900 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white border border-primary-100 dark:border-dark-border transition-all"
                                        title="Copy reference">
                                    <i class="fas text-xs" :class="copiedField === 'reference' ? 'fa-check' : 'fa-copy'"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase text-primary-500">Amount</p>
                            <p class="text-xl font-black text-primary-600 dark:text-primary-400">
                                <span x-text="selected.currency"></span>
                                <span x-text="formatAmount(selected.amount)"></span>
                            </p>
                            <span class="badge text-[10px] mt-1" :class="statusBadgeClass(selected.status)" x-text="selected.status"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                <i class="fas fa-user-circle"></i> Member Information
                            </h4>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Member Name</p>
                                <p class="font-bold text-primary-900 dark:text-white" x-text="selected.member_name"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Actual Payer</p>
                                <p class="font-semibold text-sm text-primary-800 dark:text-primary-200" x-text="selected.payer_name"></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold">Phone</p>
                                <p class="font-mono text-sm" x-text="selected.phone"></p>
                            </div>
                            <template x-if="selected.email">
                                <div>
                                    <p class="text-[10px] text-primary-500 uppercase font-bold">Email</p>
                                    <p class="text-sm" x-text="selected.email"></p>
                                </div>
                            </template>
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                <i class="fas fa-receipt"></i> Transaction Details
                            </h4>
                            <div class="flex justify-between items-start gap-2 border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500 shrink-0">Transaction ID</span>
                                <div class="flex items-center gap-2 min-w-0 justify-end">
                                    <span class="font-mono text-xs font-bold break-all text-right" x-text="selected.transaction_id"></span>
                                    <button type="button"
                                            @click="copyText(selected.transaction_id, 'transaction_id')"
                                            :disabled="!selected.transaction_id || selected.transaction_id === 'N/A'"
                                            class="shrink-0 w-7 h-7 rounded-lg bg-primary-50 dark:bg-dark-900 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all disabled:opacity-40 disabled:pointer-events-none"
                                            title="Copy transaction ID">
                                        <i class="fas text-[10px]" :class="copiedField === 'transaction_id' ? 'fa-check' : 'fa-copy'"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Payment Method</span>
                                <span class="text-xs font-bold" x-text="selected.payment_method"></span>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Date & Time</span>
                                <span class="text-xs font-bold"><span x-text="selected.date"></span> <span x-text="selected.time"></span></span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] text-primary-500 uppercase font-bold mb-1">Purpose / Description</p>
                        <p class="text-sm text-primary-800 dark:text-primary-200 bg-primary-50/50 dark:bg-dark-900/50 rounded-xl p-3 border border-primary-100 dark:border-dark-border" x-text="selected.description"></p>
                    </div>

                    <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 space-y-3">
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                            <i class="fas fa-sms"></i> SMS Notification
                        </h4>
                        <div class="flex items-center gap-2">
                            <template x-if="selected.sms_sent">
                                <span class="badge badge-green text-[10px]"><i class="fas fa-check me-1"></i> SMS Sent</span>
                            </template>
                            <template x-if="!selected.sms_sent && selected.sms_error">
                                <span class="badge badge-red text-[10px]"><i class="fas fa-times me-1"></i> SMS Failed</span>
                            </template>
                            <template x-if="!selected.sms_sent && !selected.sms_error">
                                <span class="badge badge-yellow text-[10px]">Not Sent</span>
                            </template>
                            <template x-if="selected.sms_sent_at">
                                <span class="text-[10px] text-primary-500" x-text="'at ' + selected.sms_sent_at"></span>
                            </template>
                        </div>
                        <template x-if="selected.sms_error">
                            <p class="text-xs text-red-600 dark:text-red-400 font-bold" x-text="'Error: ' + selected.sms_error"></p>
                        </template>
                        <template x-if="selected.sms_message">
                            <div>
                                <p class="text-[10px] text-primary-500 uppercase font-bold mb-1">Message Sent</p>
                                <p class="text-xs text-primary-800 dark:text-primary-200 bg-white dark:bg-dark-900 rounded-lg p-3 border border-primary-100 dark:border-dark-border whitespace-pre-wrap max-h-40 overflow-y-auto" x-text="selected.sms_message"></p>
                            </div>
                        </template>
                        <template x-if="!selected.sms_message && !selected.sms_error && !selected.sms_sent">
                            <p class="text-xs text-primary-500 italic">No SMS has been sent for this payment yet.</p>
                        </template>
                    </div>

                    <div class="p-4 rounded-xl border border-primary-100 dark:border-dark-border bg-primary-50/30 dark:bg-dark-900/30 space-y-3">
                        <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                            <i class="fas fa-envelope"></i> Email Notification
                        </h4>
                        <div class="flex items-center gap-2">
                            <template x-if="selected.email_sent">
                                <span class="badge badge-green text-[10px]"><i class="fas fa-check me-1"></i> Email Sent</span>
                            </template>
                            <template x-if="!selected.email_sent && selected.email_error">
                                <span class="badge badge-red text-[10px]"><i class="fas fa-times me-1"></i> Email Failed</span>
                            </template>
                            <template x-if="!selected.email_sent && !selected.email_error">
                                <span class="badge badge-yellow text-[10px]">Not Sent</span>
                            </template>
                            <template x-if="selected.email_sent_at">
                                <span class="text-[10px] text-primary-500" x-text="'at ' + selected.email_sent_at"></span>
                            </template>
                        </div>
                        <template x-if="selected.email_error">
                            <p class="text-xs text-red-600 dark:text-red-400 font-bold" x-text="'Error: ' + selected.email_error"></p>
                        </template>
                        <template x-if="selected.email_message">
                            <p class="text-xs text-primary-800 dark:text-primary-300 bg-white dark:bg-dark-900 rounded-lg p-3 border border-primary-100 dark:border-dark-border">
                                <strong>Recipients:</strong> <span x-text="selected.email_message"></span>
                            </p>
                        </template>
                        <template x-if="!selected.email_message && !selected.email_error && !selected.email_sent">
                            <p class="text-xs text-primary-500 italic">No email has been sent for this payment yet.</p>
                        </template>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <a :href="selected.status_url" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                            <i class="fas fa-external-link-alt me-1"></i> Full Payment Page
                        </a>
                        <template x-if="selected.isSettled">
                            <a :href="selected.receipt_url" target="_blank" class="px-4 py-2 rounded-xl bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold border border-primary-100 dark:border-dark-border hover:bg-primary-100 transition-all">
                                <i class="fas fa-file-pdf me-1"></i> Receipt PDF
                            </a>
                        </template>
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
function paymentHistoryDetails() {
    let debounceTimer = null;
    return {
        open: false,
        selected: null,
        copiedField: null,
        copyTimeout: null,
        init() {
            // Add real-time search
            const searchInput = document.getElementById('searchInput');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');
            const filterForm = document.getElementById('filterForm');

            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        filterForm.submit();
                    }, 500);
                });
            }

            if (startDate) {
                startDate.addEventListener('change', () => filterForm.submit());
            }

            if (endDate) {
                endDate.addEventListener('change', () => filterForm.submit());
            }
        },
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
        async copyText(text, field) {
            const value = String(text ?? '').trim();
            if (!value || value === 'N/A') return;
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
            this.copyTimeout = setTimeout(() => { this.copiedField = null; }, 2000);
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
