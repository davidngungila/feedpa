@extends('layouts.app')

@section('title', 'Edit Beneficiary')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <!-- Icon Section -->
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center">
                        <i class="fas fa-user-edit text-4xl text-primary-600 dark:text-primary-400"></i>
                    </div>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Edit Beneficiary</div>
                    <div class="text-xl font-mono font-bold text-primary-900 dark:text-white">Edit Details</div>
                    <div class="mt-2">
                        <span class="px-4 py-1.5 text-xs font-bold rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                            <i class="fas fa-address-book me-2"></i>
                            Update Beneficiary
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card p-6 sm:p-8 space-y-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
            <i class="fas fa-user-edit"></i> Beneficiary Information
        </h3>
        
        <form action="{{ route('beneficiaries.update', $beneficiary->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Info -->
                <div class="space-y-4">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-user"></i> Basic Details
                    </h4>
                    <div>
                        <label for="name" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Beneficiary Name</label>
                        <div class="relative">
                            <div id="nameLoader" class="hidden absolute left-3 top-1/2 -translate-y-1/2">
                                <svg class="w-4 h-4 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <input id="name" type="text" name="name" value="{{ old('name', $beneficiary->name) }}" required
                                   class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl pl-10 pr-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <p id="nameError" class="hidden mt-1 text-[10px] text-red-500 font-bold"></p>
                        @error('name')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Beneficiary Type</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="mobile" {{ old('type', $beneficiary->type) === 'mobile' ? 'checked' : '' }} required
                                       class="w-4 h-4 text-primary-600 focus:ring-primary-500">
                                <span class="text-xs text-primary-700 dark:text-primary-300">Mobile Money</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="bank" {{ old('type', $beneficiary->type) === 'bank' ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary-600 focus:ring-primary-500">
                                <span class="text-xs text-primary-700 dark:text-primary-300">Bank</span>
                            </label>
                        </div>
                        @error('type')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $beneficiary->is_active) ? 'checked' : '' }}
                                   class="rounded border-primary-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-[10px] font-bold text-primary-700 dark:text-primary-300 uppercase tracking-wider">Active</span>
                        </label>
                    </div>
                </div>

                <!-- Mobile Money / Bank Fields -->
                <div class="space-y-4">
                    <div id="mobile_fields">
                        <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-mobile-alt"></i> Mobile Money Details
                        </h4>
                        <div>
                            <label for="phone" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Phone Number</label>
                            <input id="phone" type="text" name="phone" value="{{ old('phone', $beneficiary->phone) }}"
                                   class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="e.g. 0712345678">
                            @error('phone')
                                <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div id="bank_fields">
                        <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-university"></i> Bank Details
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label for="bank_name" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Select Bank</label>
                                <select id="bank_name" name="bank_name"
                                        class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="">-- Select Bank --</option>
                                    @foreach($banks as $bank)
                                        @php
                                            $bankName = $bank['name'] ?? $bank['bank_name'] ?? null;
                                            $bankCode = $bank['code'] ?? $bank['bank_code'] ?? $bank['id'] ?? null;
                                        @endphp
                                        @if($bankName)
                                            <option value="{{ $bankName }}" data-code="{{ $bankCode }}" {{ old('bank_name', $beneficiary->bank_name) === $bankName ? 'selected' : '' }}>
                                                {{ $bankName }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <input type="hidden" id="bic" name="bic" value="{{ old('bic', $beneficiary->bic) }}">
                                @error('bank_name')
                                    <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="account_number" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Account Number</label>
                                <div class="flex gap-2">
                                    <input id="account_number" type="text" name="account_number" value="{{ old('account_number', $beneficiary->account_number) }}"
                                           class="flex-1 bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                                    <button type="button" id="verify_account_btn"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition-all disabled:opacity-50"
                                            disabled>
                                        Verify
                                    </button>
                                </div>
                                <p id="verification_status" class="mt-1 text-[10px] font-bold hidden"></p>
                                @error('account_number')
                                    <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Transfer Type</label>
                                <select name="transfer_type" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="ACH" {{ old('transfer_type', $beneficiary->transfer_type) === 'ACH' ? 'selected' : '' }}>ACH - Standard (1-2 days)</option>
                                    <option value="RTGS" {{ old('transfer_type', $beneficiary->transfer_type) === 'RTGS' ? 'selected' : '' }}>RTGS - Express (Same day)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Email (Optional)</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $beneficiary->email) }}"
                               class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                        @error('email')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-6 flex flex-wrap gap-3 justify-end">
                <a href="{{ route('beneficiaries.index') }}" 
                   class="px-5 py-2.5 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-900/20 transition-all">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeInputs = document.querySelectorAll('input[name="type"]');
    const mobileFields = document.getElementById('mobile_fields');
    const bankFields = document.getElementById('bank_fields');
    const nameInput = document.getElementById('name');
    const phoneInput = document.getElementById('phone');
    const bankSelect = document.getElementById('bank_name');
    const accountNumberInput = document.getElementById('account_number');
    const bicInput = document.getElementById('bic');
    const nameLoader = document.getElementById('nameLoader');
    const nameError = document.getElementById('nameError');
    const verifyAccountBtn = document.getElementById('verify_account_btn');
    const verificationStatus = document.getElementById('verification_status');
    
    let phoneTimeout;
    let accountTimeout;
    
    function updateFields() {
        const selectedType = document.querySelector('input[name="type"]:checked')?.value;
        
        if (selectedType === 'mobile') {
            mobileFields.style.display = 'block';
            bankFields.style.display = 'none';
        } else if (selectedType === 'bank') {
            mobileFields.style.display = 'none';
            bankFields.style.display = 'block';
        }
    }
    
    typeInputs.forEach(input => input.addEventListener('change', updateFields));
    updateFields();
    
    // Enable/disable verify button
    function checkEnableVerify() {
        const hasBank = bankSelect?.value;
        const hasAccount = accountNumberInput?.value?.trim();
        if (verifyAccountBtn) {
            verifyAccountBtn.disabled = !(hasBank && hasAccount);
        }
    }
    
    bankSelect?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        bicInput.value = selectedOption.dataset.code || '';
        checkEnableVerify();
        
        // If account number is already entered, load the name
        const currentAccountNumber = accountNumberInput?.value;
        if (currentAccountNumber && bicInput.value) {
            loadBankAccountName(currentAccountNumber, bicInput.value);
        }
    });
    
    accountNumberInput?.addEventListener('input', function() {
        checkEnableVerify();
        clearTimeout(accountTimeout);
        if (this.value && bicInput.value) {
            accountTimeout = setTimeout(() => {
                loadBankAccountName(this.value, bicInput.value);
            }, 500);
        }
    });
    
    // Verify button click handler
    verifyAccountBtn?.addEventListener('click', function() {
        if (bicInput.value && accountNumberInput.value) {
            loadBankAccountName(accountNumberInput.value, bicInput.value);
        }
    });
    
    // Phone number formatting and verification
    function formatPhoneNumberForInput(phone) {
        let cleaned = phone.replace(/[^0-9]/g, '');
        if (cleaned.startsWith('0')) {
            cleaned = cleaned.slice(1);
        }
        if (cleaned.startsWith('255')) {
            cleaned = cleaned.slice(3);
        }
        return cleaned.slice(0, 9);
    }
    
    function formatPhoneNumberForApi(phone) {
        let cleaned = phone.replace(/[^0-9]/g, '');
        if (cleaned.startsWith('0')) {
            cleaned = '255' + cleaned.slice(1);
        } else if (cleaned.length === 9) {
            cleaned = '255' + cleaned;
        } else if (!cleaned.startsWith('255')) {
            cleaned = '255' + cleaned;
        }
        return cleaned.slice(0, 12);
    }
    
    function validatePhoneNumber(phone) {
        const cleaned = phone.replace(/[^0-9]/g, '');
        return /^255[67]\d{8}$/.test(cleaned) || /^[67]\d{8}$/.test(cleaned);
    }
    
    async function loadMobileMoneyRecipientName(phone) {
        try {
            nameInput.value = '';
            nameLoader.classList.remove('hidden');
            nameError.classList.add('hidden');
            
            const previewPayload = {
                amount: 100,
                currency: 'TZS',
                payout_type: 'MOBILE_MONEY',
                recipient_phone: phone,
                _token: document.querySelector('input[name="_token"]')?.value
            };
            
            const previewResponse = await fetch('{{ route('payouts.preview') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(previewPayload)
            });
            
            const result = await previewResponse.json();
            if (result.success && result.data && result.data.receiver && result.data.receiver.accountName) {
                nameInput.value = result.data.receiver.accountName;
            }
        } catch (error) {
            console.error('Error retrieving recipient name:', error);
        } finally {
            nameLoader.classList.add('hidden');
        }
    }
    
    async function loadBankAccountName(accountNumber, bic) {
        try {
            nameInput.value = '';
            nameLoader.classList.remove('hidden');
            nameError.classList.add('hidden');
            if (verificationStatus) {
                verificationStatus.classList.remove('hidden', 'text-green-600', 'text-red-500');
                verificationStatus.classList.add('text-blue-600');
                verificationStatus.textContent = 'Verifying...';
            }
            if (verifyAccountBtn) {
                verifyAccountBtn.disabled = true;
                verifyAccountBtn.textContent = 'Verifying...';
            }
            
            const response = await fetch('{{ route('payouts.lookup-account-name') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value
                },
                body: JSON.stringify({ bic: bic, accountNumber: accountNumber, currency: 'TZS' })
            });
            
            const data = await response.json();
            if (data.success && data.accountName) {
                nameInput.value = data.accountName;
                if (verificationStatus) {
                    verificationStatus.classList.remove('hidden', 'text-blue-600', 'text-red-500');
                    verificationStatus.classList.add('text-green-600');
                    verificationStatus.textContent = '✓ Verified successfully!';
                }
            } else {
                if (verificationStatus) {
                    verificationStatus.classList.remove('hidden', 'text-blue-600', 'text-green-600');
                    verificationStatus.classList.add('text-red-500');
                    verificationStatus.textContent = 'Verification failed. Please check the account number.';
                }
            }
        } catch (error) {
            console.error('Error in loadBankAccountName:', error);
            if (verificationStatus) {
                verificationStatus.classList.remove('hidden', 'text-blue-600', 'text-green-600');
                verificationStatus.classList.add('text-red-500');
                verificationStatus.textContent = 'Verification failed. Please try again.';
            }
        } finally {
            nameLoader.classList.add('hidden');
            if (verifyAccountBtn) {
                verifyAccountBtn.textContent = 'Verify';
                checkEnableVerify();
            }
        }
    }
    
    // Phone input listener
    phoneInput?.addEventListener('input', function() {
        this.value = formatPhoneNumberForInput(this.value);
        clearTimeout(phoneTimeout);
        if (validatePhoneNumber(this.value)) {
            phoneTimeout = setTimeout(() => {
                const formattedPhone = formatPhoneNumberForApi(this.value);
                loadMobileMoneyRecipientName(formattedPhone);
            }, 500);
        }
    });
    
    checkEnableVerify();
});
</script>
@endpush
@endsection