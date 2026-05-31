@extends('layouts.app')

@section('title', 'Create Payout')

@section('content')
<div class="space-y-6 animate-fade-in">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-wallet text-primary-500"></i>
                Create Payout
            </h2>
            <p class="text-xs text-primary-500 mt-1">Initiate a secure payout via Mobile Money or Bank transfer.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard.index') }}" class="px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
            <a href="{{ route('payouts.index') }}" class="px-4 py-2 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-xs font-bold text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-all">
                <i class="fas fa-history me-1"></i> Payout History
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

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2">
            <form action="{{ route('payouts.store') }}" method="POST" id="payoutForm" class="card p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="payout_type" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Payout Type</label>
                        <select id="payout_type" name="payout_type"
                                class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                onchange="togglePayoutFields()">
                            <option value="MOBILE_MONEY" {{ old('payout_type') === 'MOBILE_MONEY' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="BANK" {{ old('payout_type') === 'BANK' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>

                    <div>
                        <label for="currency" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Currency</label>
                        <select id="currency" name="currency"
                                class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="TZS" {{ old('currency') === 'TZS' ? 'selected' : '' }}>Tanzanian Shilling (TZS)</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>US Dollar (USD)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="recipient_name" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Recipient Name</label>
                        <input type="text" id="recipient_name" name="recipient_name" value="{{ old('recipient_name') }}" maxlength="255" required
                               class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('recipient_name') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="Enter recipient full name">
                        @error('recipient_name')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="amount" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Amount</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-primary-500" id="currencyLabel">TZS</span>
                            <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="100" step="0.01" required
                                   class="w-full pl-14 bg-primary-50 dark:bg-dark-900 border {{ $errors->has('amount') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="5000">
                        </div>
                        <p class="mt-1 text-[10px] text-primary-500">Minimum 100</p>
                        @error('amount')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Mobile Money Fields -->
                <div id="mobileMoneyFields">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="recipient_phone" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Recipient Phone Number</label>
                            <input type="tel" id="recipient_phone" name="recipient_phone" value="{{ old('recipient_phone') }}"
                                   class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('recipient_phone') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="255712345678">
                            <p class="mt-1 text-[10px] text-primary-500">Use Tanzanian format: <span class="font-mono">2557XXXXXXXX</span></p>
                            @error('recipient_phone')
                                <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Bank Transfer Fields -->
                <div id="bankFields" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="bank_name" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}"
                                   class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('bank_name') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="Enter bank name">
                            @error('bank_name')
                                <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="bank_account_number" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Account Number</label>
                            <input type="text" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number') }}"
                                   class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('bank_account_number') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="Enter account number">
                            @error('bank_account_number')
                                <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="bic" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">BIC/SWIFT Code</label>
                            <input type="text" id="bic" name="bic" value="{{ old('bic') }}"
                                   class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('bic') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="Enter BIC/SWIFT code">
                            @error('bic')
                                <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3" maxlength="500"
                              class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                              placeholder="Example: Monthly salary">{{ old('description') }}</textarea>
                    <div class="mt-1 flex items-center justify-between">
                        <p class="text-[10px] text-primary-500">Optional description for your records.</p>
                        <p class="text-[10px] text-primary-500"><span id="descCharCount">{{ 500 - strlen(old('description') ?? '') }}</span> left</p>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" id="confirm" name="confirm" class="mt-0.5 rounded border-amber-300 text-primary-600 focus:ring-primary-500" required>
                        <span class="text-xs text-amber-700 dark:text-amber-300">
                            I confirm all details are correct and authorize this payout.
                        </span>
                    </label>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" id="submitBtn" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-black transition-all">
                        <i class="fas fa-paper-plane me-1"></i> Continue to Verify
                    </button>
                    <button type="button" id="resetBtn" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-dark-border dark:hover:bg-dark-700 text-xs font-bold text-gray-700 dark:text-gray-200 transition-all">
                        <i class="fas fa-rotate-left me-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="text-xs font-black text-primary-900 dark:text-white uppercase tracking-wider mb-4">Live Preview</h3>
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between">
                        <span class="text-primary-500">Recipient</span>
                        <span id="previewName" class="font-bold text-primary-900 dark:text-white">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-primary-500">Type</span>
                        <span id="previewType" class="font-bold text-primary-900 dark:text-white">Mobile Money</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-primary-500">Amount</span>
                        <span id="previewAmount" class="font-black text-primary-600 dark:text-primary-400">TZS 0.00</span>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <h3 class="text-xs font-black text-primary-900 dark:text-white uppercase tracking-wider mb-4">Process</h3>
                <div class="space-y-3 text-[11px]">
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">1.</span> Enter payout details</p>
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">2.</span> Verify OTP sent to your phone</p>
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">3.</span> Payout is initiated securely</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('payoutForm');
    const payoutType = document.getElementById('payout_type');
    const currency = document.getElementById('currency');
    const recipientName = document.getElementById('recipient_name');
    const amount = document.getElementById('amount');
    const description = document.getElementById('description');
    const descCharCount = document.getElementById('descCharCount');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn = document.getElementById('resetBtn');
    const previewName = document.getElementById('previewName');
    const previewType = document.getElementById('previewType');
    const previewAmount = document.getElementById('previewAmount');
    const currencyLabel = document.getElementById('currencyLabel');
    const mobileMoneyFields = document.getElementById('mobileMoneyFields');
    const bankFields = document.getElementById('bankFields');

    function formatCurrency(value, currencyCode) {
        const numeric = Number(value || 0);
        return new Intl.NumberFormat('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(numeric);
    }

    function syncPreview() {
        previewName.textContent = recipientName.value.trim() || '-';
        previewType.textContent = payoutType.options[payoutType.selectedIndex]?.text === 'Mobile Money' ? 'Mobile Money' : 'Bank Transfer';
        previewAmount.textContent = currency.value + ' ' + formatCurrency(amount.value);
        currencyLabel.textContent = currency.value;
    }

    function togglePayoutFields() {
        if (payoutType.value === 'MOBILE_MONEY') {
            mobileMoneyFields.classList.remove('hidden');
            bankFields.classList.add('hidden');
        } else {
            mobileMoneyFields.classList.add('hidden');
            bankFields.classList.remove('hidden');
        }
        syncPreview();
    }

    description.addEventListener('input', function () {
        const remaining = 500 - this.value.length;
        descCharCount.textContent = remaining;
        descCharCount.classList.toggle('text-red-500', remaining < 30);
        descCharCount.classList.toggle('text-primary-500', remaining >= 30);
    });

    [recipientName, amount, currency, payoutType].forEach((el) => {
        el.addEventListener('input', syncPreview);
        el.addEventListener('change', syncPreview);
    });

    resetBtn.addEventListener('click', function () {
        form.reset();
        descCharCount.textContent = '500';
        descCharCount.classList.remove('text-red-500');
        descCharCount.classList.add('text-primary-500');
        togglePayoutFields();
    });

    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    });

    togglePayoutFields();
    syncPreview();
});
</script>
@endpush
