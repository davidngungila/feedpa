@extends('layouts.app')

@section('title', 'Add New Beneficiary')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <!-- Icon Section -->
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center">
                        <i class="fas fa-user-plus text-4xl text-primary-600 dark:text-primary-400"></i>
                    </div>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Create Beneficiary</div>
                    <div class="text-xl font-mono font-bold text-primary-900 dark:text-white">New Beneficiary</div>
                    <div class="mt-2">
                        <span class="px-4 py-1.5 text-xs font-bold rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                            <i class="fas fa-address-book me-2"></i>
                            Add New Beneficiary
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card p-6 sm:p-8 space-y-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
            <i class="fas fa-user-plus"></i> Beneficiary Information
        </h3>
        
        <form action="{{ route('beneficiaries.store') }}" method="POST">
            @csrf
            
            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Info -->
                <div class="space-y-4">
                    <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                        <i class="fas fa-user"></i> Basic Details
                    </h4>
                    <div>
                        <label for="name" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Beneficiary Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                               class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                        @error('name')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Beneficiary Type</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="mobile" {{ old('type') === 'mobile' ? 'checked' : '' }} required
                                       class="w-4 h-4 text-primary-600 focus:ring-primary-500">
                                <span class="text-xs text-primary-700 dark:text-primary-300">Mobile Money</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="type" value="bank" {{ old('type') === 'bank' ? 'checked' : '' }}
                                       class="w-4 h-4 text-primary-600 focus:ring-primary-500">
                                <span class="text-xs text-primary-700 dark:text-primary-300">Bank</span>
                            </label>
                        </div>
                        @error('type')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Mobile Money / Bank Fields -->
                <div class="space-y-4">
                    <div id="mobile_fields" style="display: none;">
                        <h4 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <i class="fas fa-mobile-alt"></i> Mobile Money Details
                        </h4>
                        <div>
                            <label for="phone" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Phone Number</label>
                            <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                                   class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="e.g. 0712345678">
                            @error('phone')
                                <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div id="bank_fields" style="display: none;">
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
                                            <option value="{{ $bankName }}" data-code="{{ $bankCode }}" {{ old('bank_name') === $bankName ? 'selected' : '' }}>
                                                {{ $bankName }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <input type="hidden" id="bic" name="bic" value="{{ old('bic') }}">
                                @error('bank_name')
                                    <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="account_number" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Account Number</label>
                                <input id="account_number" type="text" name="account_number" value="{{ old('account_number') }}"
                                       class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                                @error('account_number')
                                    <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Transfer Type</label>
                                <select name="transfer_type" class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="ACH" {{ old('transfer_type') === 'ACH' ? 'selected' : '' }}>ACH - Standard (1-2 days)</option>
                                    <option value="RTGS" {{ old('transfer_type') === 'RTGS' ? 'selected' : '' }}>RTGS - Express (Same day)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="email" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Email (Optional)</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
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
                    <i class="fas fa-plus"></i> Create Beneficiary
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
    
    // Update bic when bank is selected
    const bankSelect = document.getElementById('bank_name');
    const bicInput = document.getElementById('bic');
    bankSelect?.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        bicInput.value = selectedOption.dataset.code || '';
    });
});
</script>
@endpush
@endsection