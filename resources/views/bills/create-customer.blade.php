@extends('layouts.app')

@section('title', 'Create Customer Control Number')

@section('content')
<div class="space-y-6 animate-fade-in">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-user-tie text-primary-500"></i>
                Create Customer Control Number
            </h2>
            <p class="text-xs text-primary-500 mt-1">Generate a new BillPay control number for a specific customer.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('bills.index') }}" class="px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                <i class="fas fa-arrow-left me-1"></i> Back to Bills
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-circle-check me-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    @if(session('error'))
        <div class="card p-4 border-l-4 border-l-red-500 bg-red-50/60 dark:bg-red-900/10">
            <p class="text-xs font-bold text-red-700 dark:text-red-300">
                <i class="fas fa-circle-exclamation me-1"></i> {{ session('error') }}
            </p>
        </div>
    @endif

    @if($errors->any())
        <div class="card p-4 border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-800 dark:text-amber-200 mb-2">
                <i class="fas fa-triangle-exclamation me-1"></i> Please fix the following:
            </p>
            <ul class="list-disc list-inside text-[11px] text-amber-700 dark:text-amber-300 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2">
            <form action="{{ route('bills.store-customer') }}" method="POST" id="customerForm" class="card p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_name" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Customer Name *</label>
                        <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required
                               class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('customer_name') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="Enter customer name">
                        @error('customer_name')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Customer Phone *</label>
                        <input type="tel" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}"
                               class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('customer_phone') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="255712345678" inputmode="numeric" pattern="255[0-9]{9}">
                        <p class="mt-1 text-[10px] text-primary-500">Required if email is empty. Format: 255712345678 (no + sign)</p>
                        @error('customer_phone')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="customer_email" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Customer Email</label>
                    <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email') }}"
                           class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('customer_email') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="customer@example.com">
                    <p class="mt-1 text-[10px] text-primary-500">Required if phone is empty</p>
                    @error('customer_email')
                        <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bill_description" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Bill Description</label>
                    <input type="text" id="bill_description" name="bill_description" value="{{ old('bill_description') }}"
                           class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('bill_description') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="Enter bill description">
                    @error('bill_description')
                        <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="bill_amount" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Amount (TZS)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-primary-500">TZS</span>
                            <input type="number" id="bill_amount" name="bill_amount" value="{{ old('bill_amount') }}" step="0.01"
                                   class="w-full pl-14 bg-primary-50 dark:bg-dark-900 border {{ $errors->has('bill_amount') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="10000">
                        </div>
                        <p class="mt-1 text-[10px] text-primary-500">Leave empty for open amount</p>
                        @error('bill_amount')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bill_payment_mode" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Payment Mode *</label>
                        <select id="bill_payment_mode" name="bill_payment_mode"
                                class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="ALLOW_PARTIAL_AND_OVER_PAYMENT" {{ old('bill_payment_mode', 'ALLOW_PARTIAL_AND_OVER_PAYMENT') === 'ALLOW_PARTIAL_AND_OVER_PAYMENT' ? 'selected' : '' }}>Allow Partial & Over Payment</option>
                            <option value="EXACT" {{ old('bill_payment_mode') === 'EXACT' ? 'selected' : '' }}>Exact Amount Only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="bill_reference" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Bill Reference</label>
                    <div class="flex gap-2">
                        <input type="text" id="bill_reference" name="bill_reference" value="{{ old('bill_reference') }}"
                               class="flex-1 bg-primary-50 dark:bg-dark-900 border {{ $errors->has('bill_reference') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500 font-mono"
                               placeholder="FEEDTAN1234">
                        <button type="button" onclick="generateReference()" class="px-3 py-2.5 rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-300 text-xs font-bold hover:bg-primary-200 dark:hover:bg-primary-900/50 transition-all">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-[10px] text-primary-500">Click generate to auto-create reference in FEEDTANXXXX format</p>
                    @error('bill_reference')
                        <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" id="submitBtn" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-black transition-all">
                        <i class="fas fa-plus-circle me-1"></i> Create Control Number
                    </button>
                    <a href="{{ route('bills.index') }}" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-dark-border dark:hover:bg-dark-700 text-xs font-bold text-gray-700 dark:text-gray-200 transition-all">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="text-xs font-black text-primary-900 dark:text-white uppercase tracking-wider mb-4">About Customer Control Numbers</h3>
                <div class="space-y-3 text-[11px]">
                    <p class="text-primary-700 dark:text-primary-300">Customer control numbers are linked to specific customer information (name, email, phone) for better tracking and management.</p>
                    <p class="text-primary-600 dark:text-primary-400 font-bold">ClickPesa requires at least a phone number <em>or</em> email address for each customer.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Splash Screen Modal -->
<div id="splashModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-dark-800 rounded-2xl p-8 text-center shadow-2xl max-w-sm w-full mx-4">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
            <i class="fas fa-sparkles text-3xl text-primary-500"></i>
        </div>
        <h3 class="text-xl font-black text-primary-900 dark:text-white mb-2">Generating Bill</h3>
        <p class="text-sm text-primary-500 mb-6">Please wait while we create your control number...</p>
        <div class="mb-6">
            <div class="text-6xl font-black text-primary-600 dark:text-primary-400 mb-2" id="counterDisplay">0</div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                <div id="progressBar" class="bg-primary-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
        <p class="text-xs text-gray-500">Processing payment details...</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('customerForm');
    const submitBtn = document.getElementById('submitBtn');
    const splashModal = document.getElementById('splashModal');
    const counterDisplay = document.getElementById('counterDisplay');
    const progressBar = document.getElementById('progressBar');

    // Generate random reference
    window.generateReference = function() {
        const randomNum = Math.floor(Math.random() * 9000) + 1000;
        document.getElementById('bill_reference').value = 'FEEDTAN' + randomNum;
    };

    // Auto-generate reference on page load if empty
    if (!document.getElementById('bill_reference').value) {
        window.generateReference();
    }

    form.addEventListener('submit', function (e) {
        const name = document.getElementById('customer_name').value.trim();
        const phone = document.getElementById('customer_phone').value.trim();
        const email = document.getElementById('customer_email').value.trim();
        const amount = document.getElementById('bill_amount').value.trim();
        const paymentMode = document.getElementById('bill_payment_mode').value;

        if (!name) {
            e.preventDefault();
            alert('Customer name is required.');
            return;
        }
        if (!phone && !email) {
            e.preventDefault();
            alert('Provide a customer phone number or email address (at least one is required).');
            return;
        }
        if (phone && !/^255[67]\d{8}$/.test(phone.replace(/\D/g, ''))) {
            e.preventDefault();
            alert('Phone must be in format 255712345678 (country code, no + sign).');
            return;
        }
        if (paymentMode === 'EXACT' && !amount) {
            e.preventDefault();
            alert('Amount is required when payment mode is Exact Amount Only.');
            return;
        }

        e.preventDefault();
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
        
        // Show splash screen
        splashModal.classList.remove('hidden');
        splashModal.classList.add('flex');
        
        // Start counter
        let counter = 0;
        const interval = setInterval(() => {
            counter++;
            counterDisplay.textContent = counter;
            progressBar.style.width = counter + '%';
            
            if (counter >= 100) {
                clearInterval(interval);
                // Submit form after counter completes
                submitBtn.disabled = false;
                form.submit();
            }
        }, 30);
    });
});
</script>
@endpush
