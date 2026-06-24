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
            <a href="{{ route('payouts.index') }}" class="px-4 py-2 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-xs font-black text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-all">
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
                <input type="hidden" id="orderReferenceInput" name="order_reference" value="{{ $orderReference }}">

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
                        <div class="mt-1 flex justify-between">
                            <p class="text-[10px] text-primary-500">Minimum: <span id="minAmount">100 TZS</span></p>
                            @if($balance)
                                <p class="text-[10px] text-green-600 font-bold">Balance: {{ number_format($balance['available'] ?? 0, 2) }} {{ $balance['currency'] ?? 'TZS' }}</p>
                            @endif
                        </div>
                        @error('amount')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Mobile Money Fields -->
                <div id="mobileMoneyFields">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="recipient_phone" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Recipient Phone Number</label>
                            <input type="tel" id="recipient_phone" name="recipient_phone" value="{{ old('recipient_phone') }}"
                                   class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('recipient_phone') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="255712345678">
                            <div class="mt-1 flex justify-between items-center">
                                <p class="text-[10px] text-primary-500">Use Tanzanian format: <span class="font-mono">2557XXXXXXXX</span></p>
                                <div id="providerBadge" class="hidden px-2 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-[10px] font-bold text-green-700 dark:text-green-300">
                                    <i class="fas fa-check-circle me-1"></i> <span id="providerName"></span>
                                </div>
                            </div>
                            @error('recipient_phone')
                                <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="beneficiary_email" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Beneficiary Email (Optional)</label>
                            <input type="email" id="beneficiary_email" name="beneficiary_email" value="{{ old('beneficiary_email') }}"
                                   class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="recipient@example.com">
                        </div>
                    </div>
                </div>

                <!-- Bank Transfer Fields -->
                <div id="bankFields" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="bank_id" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Select Bank</label>
                            <select id="bank_id" name="bank_id" onchange="updateBankDetails()"
                                    class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('bank_id') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">-- Select Bank --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank['bic'] }}" data-bank-name="{{ $bank['name'] }}" {{ old('bic') === $bank['bic'] ? 'selected' : '' }}>
                                        {{ $bank['name'] }} ({{ $bank['bic'] }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" id="bic" name="bic" value="{{ old('bic') }}">
                            <input type="hidden" id="bank_name" name="bank_name" value="{{ old('bank_name') }}">
                            @error('bic')
                                <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="transfer_type" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Transfer Type</label>
                            <select id="transfer_type" name="transfer_type" onchange="updateMinAmount()"
                                    class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="ACH" {{ old('transfer_type') === 'ACH' ? 'selected' : '' }}>ACH (Standard - 1-2 days)</option>
                                <option value="RTGS" {{ old('transfer_type') === 'RTGS' ? 'selected' : '' }}>RTGS (Express - Same day)</option>
                            </select>
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
                        <div>
                            <label for="beneficiary_mobile" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Beneficiary Mobile (Optional)</label>
                            <input type="tel" id="beneficiary_mobile" name="beneficiary_mobile" value="{{ old('beneficiary_mobile') }}"
                                   class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="255712345678">
                        </div>
                        <div class="md:col-span-2">
                            <label for="beneficiary_email" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Beneficiary Email (Optional)</label>
                            <input type="email" id="beneficiary_email" name="beneficiary_email" value="{{ old('beneficiary_email') }}"
                                   class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="recipient@example.com">
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

                <div class="flex flex-wrap gap-2">
                    <button type="button" id="previewBtn" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-black transition-all">
                        <i class="fas fa-eye me-1"></i> Preview Payout
                    </button>
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
                <div class="space-y-3 text-xs" id="previewSection">
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
                    <div id="previewFeeSection" class="hidden pt-3 border-t border-primary-100 dark:border-dark-border space-y-2">
                        <div class="flex justify-between">
                            <span class="text-primary-500">Fee</span>
                            <span id="previewFee" class="font-bold text-primary-900 dark:text-white">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-primary-500">Total</span>
                            <span id="previewTotal" class="font-black text-green-600 dark:text-green-400">-</span>
                        </div>
                        @if($balance)
                            <div class="flex justify-between">
                                <span class="text-primary-500">Balance After</span>
                                <span id="previewBalanceAfter" class="font-bold text-primary-900 dark:text-white">-</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div id="previewError" class="hidden mt-3 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-xs text-red-700 dark:text-red-300">
                </div>
            </div>

            <div class="card p-5">
                <h3 class="text-xs font-black text-primary-900 dark:text-white uppercase tracking-wider mb-4">Process</h3>
                <div class="space-y-3 text-[11px]">
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">1.</span> Enter payout details</p>
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">2.</span> Preview payout (optional)</p>
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">3.</span> Verify OTP sent to your phone</p>
                    <p class="text-primary-700 dark:text-primary-300"><span class="font-black">4.</span> Payout is initiated securely</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="card max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-black text-primary-900 dark:text-white mb-4">
            <i class="fas fa-calculator me-2 text-primary-500"></i> Payout Preview
        </h3>
        <div id="previewModalContent" class="space-y-4"></div>
        <div class="mt-6 flex gap-2">
            <button type="button" id="editPreviewBtn" class="px-4 py-2 rounded-xl border border-primary-200 dark:border-dark-border text-primary-700 dark:text-primary-300 text-xs font-bold">
                <i class="fas fa-edit me-1"></i> Edit
            </button>
            <button type="button" id="confirmPreviewBtn" class="px-4 py-2 rounded-xl bg-primary-600 text-white text-xs font-black">
                <i class="fas fa-check me-1"></i> Continue to Verify
            </button>
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
    const previewBtn = document.getElementById('previewBtn');
    const resetBtn = document.getElementById('resetBtn');
    const previewName = document.getElementById('previewName');
    const previewType = document.getElementById('previewType');
    const previewAmount = document.getElementById('previewAmount');
    const previewFeeSection = document.getElementById('previewFeeSection');
    const previewFee = document.getElementById('previewFee');
    const previewTotal = document.getElementById('previewTotal');
    const previewBalanceAfter = document.getElementById('previewBalanceAfter');
    const previewError = document.getElementById('previewError');
    const currencyLabel = document.getElementById('currencyLabel');
    const mobileMoneyFields = document.getElementById('mobileMoneyFields');
    const bankFields = document.getElementById('bankFields');
    const bankSelect = document.getElementById('bank_id');
    const bicInput = document.getElementById('bic');
    const bankNameInput = document.getElementById('bank_name');
    const recipientPhone = document.getElementById('recipient_phone');
    const providerBadge = document.getElementById('providerBadge');
    const providerName = document.getElementById('providerName');
    const transferType = document.getElementById('transfer_type');
    const minAmountSpan = document.getElementById('minAmount');
    const previewModal = document.getElementById('previewModal');
    const previewModalContent = document.getElementById('previewModalContent');
    const editPreviewBtn = document.getElementById('editPreviewBtn');
    const confirmPreviewBtn = document.getElementById('confirmPreviewBtn');

    let previewData = null;

    function formatCurrency(value, currencyCode) {
        const numeric = Number(value || 0);
        return new Intl.NumberFormat('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(numeric);
    }

    function updateMinAmount() {
        const type = payoutType.value;
        const transType = transferType ? transferType.value : 'ACH';
        let minAmount = 100;
        if (type === 'BANK') {
            minAmount = transType === 'RTGS' ? 10000 : 1000;
        }
        amount.min = minAmount;
        minAmountSpan.textContent = minAmount + ' ' + currency.value;
    }

    function syncPreview() {
        previewName.textContent = recipientName.value.trim() || '-';
        previewType.textContent = payoutType.options[payoutType.selectedIndex]?.text === 'Mobile Money' ? 'Mobile Money' : 'Bank Transfer';
        previewAmount.textContent = currency.value + ' ' + formatCurrency(amount.value);
        currencyLabel.textContent = currency.value;
        previewFeeSection.classList.add('hidden');
        previewError.classList.add('hidden');
    }

    function togglePayoutFields() {
        if (payoutType.value === 'MOBILE_MONEY') {
            mobileMoneyFields.classList.remove('hidden');
            bankFields.classList.add('hidden');
        } else {
            mobileMoneyFields.classList.add('hidden');
            bankFields.classList.remove('hidden');
        }
        updateMinAmount();
        syncPreview();
    }

    function updateBankDetails() {
        const selectedOption = bankSelect.options[bankSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            bicInput.value = selectedOption.value;
            bankNameInput.value = selectedOption.getAttribute('data-bank-name');
        } else {
            bicInput.value = '';
            bankNameInput.value = '';
        }
    }

    function formatPhoneNumber(phone) {
        let cleaned = phone.replace(/[^0-9]/g, '');
        if (cleaned.startsWith('0')) {
            cleaned = '255' + cleaned.slice(1);
        }
        if (!cleaned.startsWith('255') && cleaned.length === 9) {
            cleaned = '255' + cleaned;
        }
        return cleaned;
    }

    function validatePhoneNumber(phone) {
        const cleaned = phone.replace(/[^0-9]/g, '');
        return /^255[67]\d{8}$/.test(cleaned);
    }

    async function detectProvider(phone) {
        try {
            const response = await fetch('{{ route('payouts.detect-provider') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ phoneNumber: phone })
            });
            const data = await response.json();
            if (data.success && data.provider) {
                providerName.textContent = data.provider;
                providerBadge.classList.remove('hidden');
            } else {
                providerBadge.classList.add('hidden');
            }
        } catch (error) {
            providerBadge.classList.add('hidden');
        }
    }

    let phoneTimeout;
    recipientPhone.addEventListener('input', function () {
        this.value = formatPhoneNumber(this.value);
        clearTimeout(phoneTimeout);
        if (validatePhoneNumber(this.value)) {
            phoneTimeout = setTimeout(() => {
                detectProvider(this.value);
            }, 500);
        } else {
            providerBadge.classList.add('hidden');
        }
    });

    recipientPhone.addEventListener('blur', function () {
        if (validatePhoneNumber(this.value)) {
            detectProvider(this.value);
        }
    });

    async function previewPayout() {
        try {
            previewBtn.disabled = true;
            previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Previewing...';
            previewError.classList.add('hidden');
            previewFeeSection.classList.add('hidden');

            const data = {
                amount: parseFloat(amount.value),
                currency: currency.value,
                payout_type: payoutType.value,
                recipient_name: recipientName.value,
                _token: document.querySelector('input[name="_token"]').value
            };

            if (payoutType.value === 'MOBILE_MONEY') {
                data.recipient_phone = document.getElementById('recipient_phone').value;
            } else {
                data.bank_account_number = document.getElementById('bank_account_number').value;
                data.bic = bicInput.value;
                data.account_name = recipientName.value;
                data.transfer_type = transferType.value;
            }

            const response = await fetch('{{ route('payouts.preview') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                previewData = result;
                const fee = result.data.fee || 0;
                const total = parseFloat(data.amount) + parseFloat(fee);
                
                previewFee.textContent = data.currency + ' ' + formatCurrency(fee);
                previewTotal.textContent = data.currency + ' ' + formatCurrency(total);
                
                @if($balance)
                    const balanceAfter = parseFloat({{ $balance['available'] ?? 0 }}) - total;
                    previewBalanceAfter.textContent = data.currency + ' ' + formatCurrency(balanceAfter);
                @endif
                
                previewFeeSection.classList.remove('hidden');

                // Show modal
                showPreviewModal(result.data, data);
            } else {
                previewError.textContent = result.message || 'Preview failed';
                previewError.classList.remove('hidden');
            }
        } catch (error) {
            previewError.textContent = 'Error previewing payout: ' + error.message;
            previewError.classList.remove('hidden');
        } finally {
            previewBtn.disabled = false;
            previewBtn.innerHTML = '<i class="fas fa-eye me-1"></i> Preview Payout';
        }
    }

    function showPreviewModal(data, formData) {
        previewModalContent.innerHTML = `
            <div class="space-y-3">
                <div class="p-3 rounded-lg bg-primary-50 dark:bg-dark-900">
                    <div class="text-xs font-bold text-primary-900 dark:text-white mb-2">
                        ${formData.payout_type === 'MOBILE_MONEY' ? '📱 Mobile Money Payout' : '🏦 Bank Transfer'}
                    </div>
                    ${formData.payout_type === 'MOBILE_MONEY' ? `
                        <div class="text-xs text-primary-500">Provider: <span class="font-bold text-primary-700">${data.channelProvider || '-'}</span></div>
                        <div class="text-xs text-primary-500">Phone: <span class="font-bold text-primary-700">${formData.recipient_phone}</span></div>
                    ` : `
                        <div class="text-xs text-primary-500">Bank: <span class="font-bold text-primary-700">${bankNameInput.value}</span></div>
                        <div class="text-xs text-primary-500">Account: <span class="font-bold text-primary-700">${formData.bank_account_number}</span></div>
                        <div class="text-xs text-primary-500">Transfer Type: <span class="font-bold text-primary-700">${formData.transfer_type}</span></div>
                    `}
                </div>

                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                    <h4 class="text-xs font-bold text-primary-900 dark:text-white mb-2">Amount Details</h4>
                    <div class="flex justify-between text-xs text-primary-500 mb-1">
                        <span>Payout Amount</span>
                        <span class="font-bold text-primary-900">${formData.currency} ${formatCurrency(formData.amount)}</span>
                    </div>
                    <div class="flex justify-between text-xs text-primary-500 mb-1">
                        <span>Transaction Fee</span>
                        <span class="font-bold text-primary-900">${formData.currency} ${formatCurrency(data.fee || 0)}</span>
                    </div>
                    <div class="pt-2 mt-2 border-t border-gray-200 dark:border-dark-border flex justify-between text-xs">
                        <span class="font-bold text-primary-900">Total</span>
                        <span class="font-black text-green-600">${formData.currency} ${formatCurrency(parseFloat(formData.amount) + parseFloat(data.fee || 0))}</span>
                    </div>
                </div>

                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                    <h4 class="text-xs font-bold text-primary-900 dark:text-white mb-2">Recipient Details</h4>
                    <div class="text-xs text-primary-500 mb-1">Name: <span class="font-bold text-primary-700">${formData.recipient_name}</span></div>
                    <div class="text-xs text-primary-500">Order Reference: <span class="font-mono font-bold text-primary-700">${previewData.order_reference}</span></div>
                </div>
            </div>
        `;
        previewModal.classList.remove('hidden');
        previewModal.classList.add('flex');
    }

    editPreviewBtn.addEventListener('click', function () {
        previewModal.classList.add('hidden');
        previewModal.classList.remove('flex');
    });

    confirmPreviewBtn.addEventListener('click', function () {
        previewModal.classList.add('hidden');
        previewModal.classList.remove('flex');
        // Submit form
        form.submit();
    });

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

    previewBtn.addEventListener('click', previewPayout);

    resetBtn.addEventListener('click', function () {
        form.reset();
        descCharCount.textContent = '500';
        descCharCount.classList.remove('text-red-500');
        descCharCount.classList.add('text-primary-500');
        previewFeeSection.classList.add('hidden');
        previewError.classList.add('hidden');
        providerBadge.classList.add('hidden');
        togglePayoutFields();
        // Regenerate order reference
        const now = new Date();
        const prefix = 'FEEDTANPAY';
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let randomPart = '';
        for (let i = 0; i < 7; i++) {
            randomPart += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('orderReferenceInput').value = prefix + randomPart;
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
