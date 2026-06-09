@extends('layouts.app')

@section('title', 'Edit Bill - ' . $bill->bill_pay_number)

@section('content')
<div class="space-y-6 animate-fade-in">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-edit text-primary-500"></i>
                Edit Bill
            </h2>
            <p class="text-xs text-primary-500 mt-1">Control Number: {{ $bill->bill_pay_number }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('bills.show', $bill->id) }}" class="px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                <i class="fas fa-arrow-left me-1"></i> View Bill
            </a>
            <a href="{{ route('bills.index') }}" class="px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                <i class="fas fa-list me-1"></i> All Bills
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

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2">
            <form action="{{ route('bills.update', $bill->id) }}" method="POST" id="editForm" class="card p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="bill_description" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Bill Description *</label>
                    <input type="text" id="bill_description" name="bill_description" value="{{ old('bill_description', $bill->bill_description) }}" required
                           class="w-full bg-primary-50 dark:bg-dark-900 border {{ $errors->has('bill_description') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="Enter bill description">
                    @error('bill_description')
                        <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="bill_amount" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Amount ({{ $bill->bill_currency }})</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-primary-500">{{ $bill->bill_currency }}</span>
                            <input type="number" id="bill_amount" name="bill_amount" value="{{ old('bill_amount', $bill->bill_amount) }}" step="0.01"
                                   class="w-full pl-14 bg-primary-50 dark:bg-dark-900 border {{ $errors->has('bill_amount') ? 'border-red-400' : 'border-primary-100 dark:border-dark-border' }} rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="10000">
                        </div>
                        @error('bill_amount')
                            <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bill_payment_mode" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Payment Mode *</label>
                        <select id="bill_payment_mode" name="bill_payment_mode"
                                class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="ALLOW_PARTIAL_AND_OVER_PAYMENT" {{ old('bill_payment_mode', $bill->bill_payment_mode) === 'ALLOW_PARTIAL_AND_OVER_PAYMENT' ? 'selected' : '' }}>Allow Partial & Over Payment</option>
                            <option value="EXACT" {{ old('bill_payment_mode', $bill->bill_payment_mode) === 'EXACT' ? 'selected' : '' }}>Exact Amount Only</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="bill_status" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Status *</label>
                    <select id="bill_status" name="bill_status"
                            class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="ACTIVE" {{ old('bill_status', $bill->bill_status) === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="INACTIVE" {{ old('bill_status', $bill->bill_status) === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="notes" class="block text-[10px] font-bold uppercase tracking-wider text-primary-500 mb-2">Notes</label>
                    <textarea id="notes" name="notes" rows="4"
                              class="w-full bg-primary-50 dark:bg-dark-900 border border-primary-100 dark:border-dark-border rounded-xl px-3 py-2.5 text-xs text-primary-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"
                              placeholder="Add any notes about this bill">{{ old('notes', $bill->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-[10px] text-red-500 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" id="submitBtn" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-black transition-all">
                        <i class="fas fa-save me-1"></i> Update Bill
                    </button>
                    <a href="{{ route('bills.show', $bill->id) }}" class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-dark-border dark:hover:bg-dark-700 text-xs font-bold text-gray-700 dark:text-gray-200 transition-all">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="card p-5">
                <h3 class="text-xs font-black text-primary-900 dark:text-white uppercase tracking-wider mb-4">Bill Details</h3>
                <div class="space-y-3 text-[11px]">
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-primary-500">Control Number</span>
                        <span class="font-mono font-bold text-primary-900 dark:text-white">{{ $bill->bill_pay_number }}</span>
                    </div>
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-primary-500">Type</span>
                        <span class="font-bold text-primary-900 dark:text-white">{{ ucfirst($bill->bill_type) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-primary-500">Created On</span>
                        <span class="font-bold text-primary-900 dark:text-white">{{ $bill->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    @if($bill->customer_name)
                    <div class="flex justify-between border-b border-primary-50 dark:border-dark-border pb-2">
                        <span class="text-primary-500">Customer</span>
                        <span class="font-bold text-primary-900 dark:text-white">{{ $bill->customer_name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card p-5">
                <h3 class="text-xs font-black text-primary-900 dark:text-white uppercase tracking-wider mb-4">Payment Modes</h3>
                <div class="space-y-2 text-[11px]">
                    <div class="flex items-start gap-2">
                        <span class="text-primary-600 font-black">1.</span>
                        <span class="text-primary-700 dark:text-primary-300"><strong>Allow Partial & Over Payment:</strong> Customer can pay any amount, even more or less than the bill amount.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-primary-600 font-black">2.</span>
                        <span class="text-primary-700 dark:text-primary-300"><strong>Exact Amount Only:</strong> Customer must pay exactly the specified amount.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection