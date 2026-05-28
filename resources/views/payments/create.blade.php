@extends('layouts.app')

@section('title', 'Create Payment')

@section('content')
<div class="space-y-6 animate-fade-in">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-paper-plane text-primary-500"></i>
                Create Payment
            </h2>
            <p class="text-xs text-primary-500 mt-1">Initiate secure USSD push collection from a member.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard.index') }}" class="px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                <i class="fas fa-home me-1"></i> Dashboard
            </a>
            <a href="{{ route('payments.history') }}" class="px-4 py-2 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-xs font-bold text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-all">
                <i class="fas fa-history me-1"></i> Payment History
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
            <form action="{{ route('payments.store') }}" method="POST" id="paymentForm" class="card p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="payer_name" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Member Name</label>
                        <input type="text" id="payer_name" name="payer_name" value="{{ old('payer_name') }}" maxlength="255" required
                               class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('payer_name') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="Enter member full name">
                        @error('payer_name')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone_number" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Phone Number</label>
                        <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required
                               class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('phone_number') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="255712345678">
                        <p class="mt-1 text-[10px] text-primary-500">Use Tanzanian format: <span class="font-mono">2557XXXXXXXX</span></p>
                        @error('phone_number')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Amount (TZS)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-primary-500">TZS</span>
                            <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="500" max="1000000" step="0.01" required
                                   class="w-full pl-14 bg-primary-50 dark:bg-dark-900 border {{ $errors->has('amount') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="5000">
                        </div>
                        <p class="mt-1 text-[10px] text-primary-500">Minimum 500 • Maximum 1,000,000</p>
                        @error('amount')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="payment_method" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Preferred Network</label>
                        <select id="payment_method" name="payment_method"
                                class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="halopesa" {{ old('payment_method') === 'halopesa' ? 'selected' : '' }}>Halopesa</option>
                            <option value="tigopesa" {{ old('payment_method') === 'tigopesa' ? 'selected' : '' }}>Tigopesa</option>
                            <option value="airtelmoney" {{ old('payment_method') === 'airtelmoney' ? 'selected' : '' }}>Airtel Money</option>
                            <option value="mpesa" {{ old('payment_method') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                            <option value="ezypesa" {{ old('payment_method') === 'ezypesa' ? 'selected' : '' }}>Ezy Pesa</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Description</label>
                    <textarea id="description" name="description" rows="4" maxlength="500" required
                              class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('description') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                              placeholder="Example: Monthly contribution for May">{{ old('description') }}</textarea>
                    <div class="mt-1 flex items-center justify-between">
                        <p class="text-[10px] text-primary-500">Clear purpose improves reconciliation and reports.</p>
                        <p class="text-[10px] text-primary-500"><span id="charCount">{{ 500 - strlen(old('description') ?? '') }}</span> left</p>
                    </div>
                    @error('description')
                        <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" id="confirm" name="confirm" class="mt-0.5 rounded border-amber-300 text-primary-600 focus:ring-primary-500" required>
                        <span class="text-xs text-amber-700 dark:text-amber-300">
                            I confirm the details are correct and authorize sending a USSD prompt to this member.
                        </span>
                    </label>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" id="submitBtn" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-black transition-all">
                        <i class="fas fa-paper-plane me-1"></i> Initiate Payment
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
                        <span class="text-primary-500">Member</span>
                        <span id="previewName" class="font-bold text-primary-900 dark:text-white">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-primary-500">Phone</span>
                        <span id="previewPhone" class="font-mono text-primary-900 dark:text-white">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-primary-500">Amount</span>
                        <span id="previewAmount" class="font-black text-primary-600 dark:text-primary-400">TZS 0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-primary-500">Network</span>
                        <span id="previewMethod" class="font-bold text-primary-900 dark:text-white">Halopesa</span>
                    </div>
                </div>
            </div>

            <div class="card p-5">
                <h3 class="text-xs font-black text-primary-900 dark:text-white uppercase tracking-wider mb-4">Process</h3>
                <div class="space-y-3 text-[11px]">
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">1.</span> Enter member and payment details.</p>
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">2.</span> Submit to trigger a USSD push.</p>
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">3.</span> Member confirms on their phone.</p>
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">4.</span> Track status from history and receipt pages.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('paymentForm');
    const payerName = document.getElementById('payer_name');
    const phoneNumber = document.getElementById('phone_number');
    const amount = document.getElementById('amount');
    const paymentMethod = document.getElementById('payment_method');
    const description = document.getElementById('description');
    const charCount = document.getElementById('charCount');
    const submitBtn = document.getElementById('submitBtn');
    const resetBtn = document.getElementById('resetBtn');

    const previewName = document.getElementById('previewName');
    const previewPhone = document.getElementById('previewPhone');
    const previewAmount = document.getElementById('previewAmount');
    const previewMethod = document.getElementById('previewMethod');

    function formatCurrency(value) {
        const numeric = Number(value || 0);
        return new Intl.NumberFormat('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(numeric);
    }

    function syncPreview() {
        previewName.textContent = payerName.value.trim() || '-';
        previewPhone.textContent = phoneNumber.value.trim() || '-';
        previewAmount.textContent = 'TZS ' + formatCurrency(amount.value);
        previewMethod.textContent = paymentMethod.options[paymentMethod.selectedIndex]?.text || '-';
    }

    phoneNumber.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
        syncPreview();
    });

    phoneNumber.addEventListener('blur', function () {
        let value = this.value.trim();
        if (/^0[67]\d{8}$/.test(value)) {
            value = '255' + value.slice(1);
            this.value = value;
        }
        syncPreview();
    });

    description.addEventListener('input', function () {
        const remaining = 500 - this.value.length;
        charCount.textContent = remaining;
        charCount.classList.toggle('text-red-500', remaining < 30);
        charCount.classList.toggle('text-primary-500', remaining >= 30);
    });

    [payerName, amount, paymentMethod].forEach((el) => {
        el.addEventListener('input', syncPreview);
        el.addEventListener('change', syncPreview);
    });

    resetBtn.addEventListener('click', function () {
        form.reset();
        charCount.textContent = '500';
        charCount.classList.remove('text-red-500');
        charCount.classList.add('text-primary-500');
        syncPreview();
    });

    form.addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    });

    syncPreview();
});
</script>
@endpush
