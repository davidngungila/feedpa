@extends('layouts.app')

@section('title', 'Bill Management')

@section('content')
<div class="space-y-6 animate-fade-in" x-data="billManagement()">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-file-invoice text-primary-500"></i>
                Bill Management
            </h2>
            <p class="text-xs text-primary-500 mt-1">Manage your BillPay control numbers and generate new bills.</p>
        </div>
        <div class="flex gap-2">
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
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left border-b border-primary-100 dark:border-dark-border">
                        <th class="pb-3 font-black text-primary-500 uppercase tracking-wider">Control Number</th>
                        <th class="pb-3 font-black text-primary-500 uppercase tracking-wider">Type</th>
                        <th class="pb-3 font-black text-primary-500 uppercase tracking-wider">Description</th>
                        <th class="pb-3 font-black text-primary-500 uppercase tracking-wider">Amount</th>
                        <th class="pb-3 font-black text-primary-500 uppercase tracking-wider">Status</th>
                        <th class="pb-3 font-black text-primary-500 uppercase tracking-wider">Date</th>
                        <th class="pb-3 font-black text-primary-500 uppercase tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50 dark:divide-dark-border">
                    @forelse($bills as $bill)
                        @php
                            $detailPayload = [
                                'id' => $bill->id,
                                'bill_pay_number' => $bill->bill_pay_number,
                                'bill_type' => $bill->bill_type,
                                'bill_description' => $bill->bill_description,
                                'bill_amount' => (float)$bill->bill_amount,
                                'bill_currency' => $bill->bill_currency,
                                'bill_payment_mode' => $bill->bill_payment_mode,
                                'bill_status' => $bill->bill_status,
                                'bill_reference' => $bill->bill_reference,
                                'customer_name' => $bill->customer_name,
                                'customer_email' => $bill->customer_email,
                                'customer_phone' => $bill->customer_phone,
                                'notes' => $bill->notes,
                                'total_paid' => (float)$bill->total_paid,
                                'last_payment_at' => $bill->last_payment_at?->format('d M, Y H:i:s'),
                                'created_at' => $bill->created_at->format('d M, Y'),
                                'created_time' => $bill->created_at->format('H:i:s'),
                                'show_url' => route('bills.show', $bill->id),
                                'pdf_url' => route('bills.pdf', $bill->id),
                            ];
                        @endphp
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="py-3">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-primary-900 dark:text-white">{{ $bill->bill_pay_number }}</span>
                                    <button type="button"
                                            @click.stop="copyText(@js($bill->bill_pay_number), 'cn-{{ $bill->id }}')"
                                            class="shrink-0 w-6 h-6 rounded-md bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                            title="Copy control number">
                                        <i class="fas text-[9px]" :class="copiedField === 'cn-{{ $bill->id }}' ? 'fa-check' : 'fa-copy'"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $bill->bill_type === 'order' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                    {{ ucfirst($bill->bill_type) }}
                                </span>
                            </td>
                            <td class="py-3 text-primary-700 dark:text-primary-300">{{ $bill->bill_description }}</td>
                            <td class="py-3">
                                <span class="font-bold text-primary-900 dark:text-white">
                                    {{ $bill->bill_currency }} {{ number_format($bill->bill_amount, 2) }}
                                </span>
                                @if($bill->total_paid > 0)
                                    <div class="text-[10px] text-green-600 font-bold mt-0.5">Paid: {{ number_format($bill->total_paid, 2) }}</div>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $bill->bill_status === 'ACTIVE' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                    {{ $bill->bill_status }}
                                </span>
                            </td>
                            <td class="py-3 text-primary-500">{{ $bill->created_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3">
                                <div class="flex gap-1.5 justify-center">
                                    <button type="button"
                                            @click="openDetails(@js($detailPayload))"
                                            class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                            title="Preview details">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </button>
                                    <a href="{{ route('bills.pdf', $bill->id) }}" target="_blank"
                                       class="w-7 h-7 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white transition-all"
                                       title="Download PDF">
                                        <i class="fas fa-file-pdf text-[10px]"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-20 text-center text-primary-500">
                                <i class="fas fa-folder-open text-2xl mb-2"></i>
                                <p class="text-xs">No bills found. Create your first bill above!</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bills->links() }}
        </div>
    </div>

    <!-- Bill detail modal -->
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="closeDetails()">
        <div class="absolute inset-0 bg-black/50" @click="closeDetails()"></div>
        <div class="relative w-full max-w-2xl card p-6 max-h-[90vh] overflow-y-auto animate-fade-in" @click.stop>
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-lg font-black text-primary-900 dark:text-white">Bill Details</h3>
                    <p class="text-[10px] text-primary-500 uppercase tracking-widest mt-1">BillPay Control Number Preview</p>
                </div>
                <button type="button" @click="closeDetails()" class="w-8 h-8 rounded-lg bg-primary-50 dark:bg-dark-900 text-primary-600 hover:bg-primary-100 transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <template x-if="selected">
                <div class="space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl bg-primary-50/50 dark:bg-dark-900/50 border border-primary-100 dark:border-dark-border">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold uppercase text-primary-500">Control Number</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <p class="font-mono font-bold text-primary-900 dark:text-white break-all" x-text="selected.bill_pay_number"></p>
                                <button type="button"
                                        @click="copyText(selected.bill_pay_number, 'modal-cn')"
                                        class="shrink-0 w-8 h-8 rounded-lg bg-white dark:bg-dark-900 text-primary-600 flex items-center justify-center hover:bg-primary-600 hover:text-white border border-primary-100 dark:border-dark-border transition-all"
                                        title="Copy control number">
                                    <i class="fas text-xs" :class="copiedField === 'modal-cn' ? 'fa-check' : 'fa-copy'"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold uppercase text-primary-500">Amount</p>
                            <p class="text-xl font-black text-primary-600 dark:text-primary-400">
                                <span x-text="selected.bill_currency"></span>
                                <span x-text="formatAmount(selected.bill_amount)"></span>
                            </p>
                            <span class="badge text-[10px] mt-1" :class="statusBadgeClass(selected.bill_status)" x-text="selected.bill_status"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                <i class="fas fa-info-circle"></i> Bill Information
                            </h4>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Type</span>
                                <span class="text-xs font-bold capitalize" x-text="selected.bill_type"></span>
                            </div>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Payment Mode</span>
                                <span class="text-xs font-bold" x-text="selected.bill_payment_mode"></span>
                            </div>
                            <template x-if="selected.bill_reference">
                                <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                    <span class="text-xs text-primary-500">Reference</span>
                                    <span class="text-xs font-mono font-bold" x-text="selected.bill_reference"></span>
                                </div>
                            </template>
                            <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                                <span class="text-xs text-primary-500">Date Created</span>
                                <span class="text-xs font-bold"><span x-text="selected.created_at"></span> <span x-text="selected.created_time"></span></span>
                            </div>
                        </div>

                        <template x-if="selected.bill_type === 'customer' && (selected.customer_name || selected.customer_email || selected.customer_phone)">
                            <div class="space-y-3">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                                    <i class="fas fa-user-circle"></i> Customer Information
                                </h4>
                                <template x-if="selected.customer_name">
                                    <div>
                                        <p class="text-[10px] text-primary-500 uppercase font-bold">Customer Name</p>
                                        <p class="font-bold text-primary-900 dark:text-white" x-text="selected.customer_name"></p>
                                    </div>
                                </template>
                                <template x-if="selected.customer_phone">
                                    <div>
                                        <p class="text-[10px] text-primary-500 uppercase font-bold">Phone</p>
                                        <p class="font-mono text-sm" x-text="selected.customer_phone"></p>
                                    </div>
                                </template>
                                <template x-if="selected.customer_email">
                                    <div>
                                        <p class="text-[10px] text-primary-500 uppercase font-bold">Email</p>
                                        <p class="text-sm" x-text="selected.customer_email"></p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div>
                        <p class="text-[10px] text-primary-500 uppercase font-bold mb-1">Description</p>
                        <p class="text-sm text-primary-800 dark:text-primary-200 bg-primary-50/50 dark:bg-dark-900/50 rounded-xl p-3 border border-primary-100 dark:border-dark-border" x-text="selected.bill_description"></p>
                    </div>

                    <template x-if="selected.total_paid > 0">
                        <div class="p-4 rounded-xl border border-green-200 bg-green-50/50 dark:bg-green-900/10 dark:border-green-800 space-y-1">
                            <div class="flex justify-between">
                                <span class="text-xs text-green-700 dark:text-green-400 font-bold">Total Paid</span>
                                <span class="font-black text-green-700 dark:text-green-400" x-text="selected.bill_currency + ' ' + formatAmount(selected.total_paid)"></span>
                            </div>
                            <template x-if="selected.last_payment_at">
                                <p class="text-[10px] text-green-600 dark:text-green-500" x-text="'Last payment: ' + selected.last_payment_at"></p>
                            </template>
                        </div>
                    </template>

                    <template x-if="selected.notes">
                        <div>
                            <p class="text-[10px] text-primary-500 uppercase font-bold mb-1">Notes</p>
                            <p class="text-sm text-primary-700 dark:text-primary-300 bg-white dark:bg-dark-900 rounded-lg p-3 border border-primary-100 dark:border-dark-border" x-text="selected.notes"></p>
                        </div>
                    </template>

                    <div class="flex flex-wrap gap-2 pt-2">
                        <a :href="selected.show_url" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                            <i class="fas fa-external-link-alt me-1"></i> Full Bill Page
                        </a>
                        <a :href="selected.pdf_url" target="_blank" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition-all">
                            <i class="fas fa-file-pdf me-1"></i> Download PDF
                        </a>
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
function billManagement() {
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
            this.selected = null;
            document.body.style.overflow = '';
        },
        async copyText(text, field) {
            const value = String(text ?? '').trim();
            if (!value) return;
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
            if (s === 'ACTIVE') return 'badge-green';
            if (s === 'INACTIVE') return 'badge-red';
            return 'badge-yellow';
        }
    };
}
</script>
@endpush
