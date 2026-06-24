@extends('layouts.app')

@section('title', 'Create Payout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="p-2 bg-primary-600 rounded-lg">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        Create Payout
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Initiate secure payouts to mobile money or bank accounts
                    </p>
                </div>
                
                <!-- Balance Card -->
                @if($balance)
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-2xl p-5 shadow-lg text-white">
                        <p class="text-xs font-semibold uppercase tracking-wider opacity-90 mb-1">Available Balance</p>
                        <p class="text-2xl font-extrabold">
                            {{ number_format($balance['available'] ?? 0, 2) }} {{ $balance['currency'] ?? 'TZS' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        @if(session('error'))
            <div class="mb-6 rounded-xl border-l-4 border-red-500 bg-red-50 dark:bg-red-900/20 p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm font-semibold text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 rounded-xl border-l-4 border-green-500 bg-green-50 dark:bg-green-900/20 p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm font-semibold text-green-700 dark:text-green-300">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Section -->
            <div class="lg:col-span-2">
                <form action="{{ route('payouts.store') }}" method="POST" id="payoutForm" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    @csrf
                    <input type="hidden" id="orderReferenceInput" name="order_reference" value="{{ $orderReference }}">
                    
                    <!-- Form Header -->
                    <div class="bg-gradient-to-r from-primary-600 to-indigo-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-white">Payout Details</h2>
                                <p class="text-xs text-primary-100 mt-0.5">Order Ref: <span class="font-mono">{{ $orderReference }}</span></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 bg-green-400 rounded-full animate-pulse"></span>
                                <span class="text-xs text-primary-100">Ready to Send</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Payout Type & Currency Row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Payout Type
                                    </span>
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="payout_type" value="MOBILE_MONEY" id="type_mm" class="peer sr-only" {{ old('payout_type') !== 'BANK' ? 'checked' : '' }} onchange="togglePayoutFields()">
                                        <div class="flex items-center gap-3 p-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-900/20 transition-all">
                                            <div class="p-2 bg-primary-100 dark:bg-primary-900/30 rounded-lg peer-checked:bg-primary-500 transition-colors">
                                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-bold text-gray-900 dark:text-white">Mobile Money</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Fast & Simple</p>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="payout_type" value="BANK" id="type_bank" class="peer sr-only" {{ old('payout_type') === 'BANK' ? 'checked' : '' }} onchange="togglePayoutFields()">
                                        <div class="flex items-center gap-3 p-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-900/20 transition-all">
                                            <div class="p-2 bg-primary-100 dark:bg-primary-900/30 rounded-lg peer-checked:bg-primary-500 transition-colors">
                                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 peer-checked:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-bold text-gray-900 dark:text-white">Bank Transfer</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Secure & Reliable</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label for="currency" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                    Currency
                                </label>
                                <select id="currency" name="currency" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                    <option value="TZS" {{ old('currency') !== 'USD' ? 'selected' : '' }}>TZS - Tanzanian Shilling</option>
                                    <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                </select>
                            </div>
                        </div>

                        <!-- Recipient Name & Amount -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="recipient_name" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                    Recipient Name
                                </label>
                                <input type="text" id="recipient_name" name="recipient_name" value="{{ old('recipient_name') }}" maxlength="255" required
                                       class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border {{ $errors->has('recipient_name') ? 'border-red-400' : 'border-gray-200 dark:border-gray-600' }} rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                       placeholder="John Doe">
                                @error('recipient_name')
                                    <p class="mt-1.5 text-xs font-semibold text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="amount" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                    Amount
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-500 dark:text-gray-400" id="currencyLabel">TZS</span>
                                    <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="100" step="0.01" required
                                           class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border {{ $errors->has('amount') ? 'border-red-400' : 'border-gray-200 dark:border-gray-600' }} rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                           placeholder="5000">
                                </div>
                                <div class="mt-1.5 flex justify-between items-center">
                                    <p class="text-xs text-gray-500">Min: <span class="font-semibold" id="minAmountLabel">100 TZS</span></p>
                                </div>
                                @error('amount')
                                    <p class="mt-1.5 text-xs font-semibold text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Mobile Money Fields -->
                        <div id="mobileMoneyFields" class="space-y-5 {{ old('payout_type') === 'BANK' ? 'hidden' : '' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label for="recipient_phone" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                        Phone Number
                                    </label>
                                    <div class="relative">
                                        <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-500 dark:text-gray-400">+255</span>
                                        </div>
                                        <input type="tel" id="recipient_phone" name="recipient_phone" value="{{ old('recipient_phone') }}"
                                               class="w-full pl-16 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border {{ $errors->has('recipient_phone') ? 'border-red-400' : 'border-gray-200 dark:border-gray-600' }} rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                               placeholder="712345678">
                                    </div>
                                    <div class="mt-2 flex items-center justify-between">
                                        <p class="text-xs text-gray-500">Format: 7XXXXXXXX</p>
                                        <div id="providerBadge" class="hidden items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-xs font-bold text-green-700 dark:text-green-300">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span id="providerName"></span>
                                        </div>
                                    </div>
                                    @error('recipient_phone')
                                        <p class="mt-1.5 text-xs font-semibold text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="beneficiary_email" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                        Email (Optional)
                                    </label>
                                    <input type="email" id="beneficiary_email" name="beneficiary_email" value="{{ old('beneficiary_email') }}"
                                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                           placeholder="recipient@example.com">
                                </div>
                            </div>
                        </div>

                        <!-- Bank Transfer Fields -->
                        <div id="bankFields" class="space-y-5 {{ old('payout_type') === 'BANK' ? '' : 'hidden' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label for="bank_id" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                        Select Bank
                                    </label>
                                    <select id="bank_id" name="bank_id" onchange="updateBankDetails()"
                                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border {{ $errors->has('bank_id') ? 'border-red-400' : 'border-gray-200 dark:border-gray-600' }} rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                        <option value="">Choose a bank...</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank['bic'] }}" data-bank-name="{{ $bank['name'] }}" {{ old('bic') === $bank['bic'] ? 'selected' : '' }}>
                                                {{ $bank['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="bic" name="bic" value="{{ old('bic') }}">
                                    <input type="hidden" id="bank_name" name="bank_name" value="{{ old('bank_name') }}">
                                    @error('bic')
                                        <p class="mt-1.5 text-xs font-semibold text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="transfer_type" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                        Transfer Type
                                    </label>
                                    <select id="transfer_type" name="transfer_type" onchange="updateMinAmount()"
                                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                        <option value="ACH" {{ old('transfer_type') !== 'RTGS' ? 'selected' : '' }}>ACH - Standard (1-2 days)</option>
                                        <option value="RTGS" {{ old('transfer_type') === 'RTGS' ? 'selected' : '' }}>RTGS - Express (Same day)</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="bank_account_number" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                        Account Number
                                    </label>
                                    <input type="text" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number') }}"
                                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border {{ $errors->has('bank_account_number') ? 'border-red-400' : 'border-gray-200 dark:border-gray-600' }} rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                           placeholder="0123456789">
                                    @error('bank_account_number')
                                        <p class="mt-1.5 text-xs font-semibold text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="beneficiary_mobile" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                        Mobile (Optional)
                                    </label>
                                    <input type="tel" id="beneficiary_mobile" name="beneficiary_mobile" value="{{ old('beneficiary_mobile') }}"
                                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                           placeholder="255712345678">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="beneficiary_email" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                        Email (Optional)
                                    </label>
                                    <input type="email" id="beneficiary_email" name="beneficiary_email" value="{{ old('beneficiary_email') }}"
                                           class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                           placeholder="recipient@example.com">
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-2">
                                Description (Optional)
                            </label>
                            <textarea id="description" name="description" rows="3" maxlength="500"
                                      class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"
                                      placeholder="e.g., Monthly salary payment">{{ old('description') }}</textarea>
                            <div class="mt-1.5 flex justify-end">
                                <p class="text-xs text-gray-400"><span id="descCharCount">{{ 500 - strlen(old('description') ?? '') }}</span> characters left</p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap gap-3">
                            <button type="button" id="previewBtn" class="flex-1 px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-500/25 transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Preview Payout
                            </button>
                            <button type="submit" id="submitBtn" class="flex-1 px-5 py-3 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white text-sm font-bold rounded-xl shadow-lg shadow-primary-500/25 transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Continue to Verify
                            </button>
                            <button type="button" id="resetBtn" class="px-5 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Live Preview -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-700 px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Live Preview
                        </h3>
                    </div>
                    <div class="p-6 space-y-4" id="previewSection">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Recipient</span>
                            <span id="previewName" class="text-sm font-semibold text-gray-900 dark:text-white">-</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Type</span>
                            <span id="previewType" class="text-sm font-semibold text-gray-900 dark:text-white">Mobile Money</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Amount</span>
                            <span id="previewAmount" class="text-xl font-extrabold text-primary-600 dark:text-primary-400">TZS 0.00</span>
                        </div>

                        <div id="previewFeeSection" class="hidden pt-4 border-t border-gray-100 dark:border-gray-700 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Transaction Fee</span>
                                <span id="previewFee" class="text-sm font-semibold text-gray-900 dark:text-white">-</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Total Amount</span>
                                <span id="previewTotal" class="text-lg font-extrabold text-green-600 dark:text-green-400">-</span>
                            </div>
                            @if($balance)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Balance After</span>
                                    <span id="previewBalanceAfter" class="text-sm font-semibold text-gray-900 dark:text-white">-</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div id="previewError" class="hidden mx-6 mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 text-xs font-semibold text-red-700 dark:text-red-300"></div>
                </div>

                <!-- Info Cards -->
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-amber-900 dark:text-amber-300 mb-1">Important Info</h4>
                                <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                                    Double-check recipient details before proceeding. Payouts are processed securely and cannot be reversed.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-5">
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white mb-3 uppercase tracking-wide">How it works</h4>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-green-600">1</span>
                                </div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">Fill in payout details</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-primary-600">2</span>
                                </div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">Preview & confirm</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-gray-600">3</span>
                                </div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">Verify OTP</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                    <span class="text-xs font-bold text-gray-600">4</span>
                                </div>
                                <span class="text-xs text-gray-600 dark:text-gray-400">Payout processed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
        <div class="bg-gradient-to-r from-primary-600 to-indigo-600 px-6 py-5">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Payout Preview
            </h3>
        </div>
        <div id="previewModalContent" class="p-6 space-y-4"></div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 flex gap-3">
            <button type="button" id="editPreviewBtn" class="flex-1 px-4 py-2.5 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-500 transition-all">
                Edit Details
            </button>
            <button type="button" id="confirmPreviewBtn" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white text-sm font-bold rounded-xl transition-all">
                Continue to Verify
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('payoutForm');
    const payoutTypeMobile = document.getElementById('type_mm');
    const payoutTypeBank = document.getElementById('type_bank');
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
    const minAmountLabel = document.getElementById('minAmountLabel');
    const previewModal = document.getElementById('previewModal');
    const previewModalContent = document.getElementById('previewModalContent');
    const editPreviewBtn = document.getElementById('editPreviewBtn');
    const confirmPreviewBtn = document.getElementById('confirmPreviewBtn');

    let previewData = null;

    function formatCurrency(value, currencyCode) {
        const numeric = Number(value || 0);
        return new Intl.NumberFormat('en-TZ', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(numeric);
    }

    function getCurrentPayoutType() {
        if (payoutTypeMobile && payoutTypeMobile.checked) return 'MOBILE_MONEY';
        if (payoutTypeBank && payoutTypeBank.checked) return 'BANK';
        return 'MOBILE_MONEY';
    }

    function updateMinAmount() {
        const type = getCurrentPayoutType();
        const transType = transferType ? transferType.value : 'ACH';
        let minAmount = 100;
        if (type === 'BANK') {
            minAmount = transType === 'RTGS' ? 10000 : 1000;
        }
        if (amount) amount.min = minAmount;
        if (minAmountLabel) minAmountLabel.textContent = minAmount + ' ' + (currency ? currency.value : 'TZS');
    }

    function syncPreview() {
        if (previewName) previewName.textContent = recipientName ? recipientName.value.trim() || '-' : '-';
        if (previewType) previewType.textContent = getCurrentPayoutType() === 'MOBILE_MONEY' ? 'Mobile Money' : 'Bank Transfer';
        if (previewAmount && currency && amount) previewAmount.textContent = currency.value + ' ' + formatCurrency(amount.value);
        if (currencyLabel && currency) currencyLabel.textContent = currency.value;
        if (previewFeeSection) previewFeeSection.classList.add('hidden');
        if (previewError) previewError.classList.add('hidden');
    }

    function togglePayoutFields() {
        const type = getCurrentPayoutType();
        if (mobileMoneyFields) mobileMoneyFields.classList.toggle('hidden', type !== 'MOBILE_MONEY');
        if (bankFields) bankFields.classList.toggle('hidden', type !== 'BANK');
        updateMinAmount();
        syncPreview();
    }

    function updateBankDetails() {
        if (!bankSelect) return;
        const selectedOption = bankSelect.options[bankSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            if (bicInput) bicInput.value = selectedOption.value;
            if (bankNameInput) bankNameInput.value = selectedOption.getAttribute('data-bank-name');
        } else {
            if (bicInput) bicInput.value = '';
            if (bankNameInput) bankNameInput.value = '';
        }
    }

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

    async function detectProvider(phone) {
        try {
            const formattedPhone = formatPhoneNumberForApi(phone);
            const response = await fetch('{{ route('payouts.detect-provider') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value
                },
                body: JSON.stringify({ phoneNumber: formattedPhone })
            });
            const data = await response.json();
            if (providerName && providerBadge && data.success && data.provider) {
                providerName.textContent = data.provider;
                providerBadge.classList.remove('hidden');
                providerBadge.classList.add('flex');
            } else if (providerBadge) {
                providerBadge.classList.add('hidden');
                providerBadge.classList.remove('flex');
            }
        } catch (error) {
            if (providerBadge) {
                providerBadge.classList.add('hidden');
                providerBadge.classList.remove('flex');
            }
        }
    }

    let phoneTimeout;
    if (recipientPhone) {
        recipientPhone.addEventListener('input', function () {
            this.value = formatPhoneNumberForInput(this.value);
            clearTimeout(phoneTimeout);
            if (validatePhoneNumber(this.value)) {
                phoneTimeout = setTimeout(() => {
                    detectProvider(this.value);
                }, 500);
            } else if (providerBadge) {
                providerBadge.classList.add('hidden');
                providerBadge.classList.remove('flex');
            }
        });
        recipientPhone.addEventListener('blur', function () {
            if (validatePhoneNumber(this.value)) {
                detectProvider(this.value);
            }
        });
    }

    async function previewPayout() {
        try {
            if (previewBtn) {
                previewBtn.disabled = true;
                previewBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25"></circle><path d="M4 12a8 8 0 018-8" stroke-width="4" class="opacity-75"></path></svg> Previewing...';
            }
            if (previewError) previewError.classList.add('hidden');
            if (previewFeeSection) previewFeeSection.classList.add('hidden');

            const type = getCurrentPayoutType();
            const data = {
                amount: parseFloat(amount.value),
                currency: currency.value,
                payout_type: type,
                recipient_name: recipientName.value,
                _token: document.querySelector('input[name="_token"]')?.value
            };

            if (type === 'MOBILE_MONEY') {
                data.recipient_phone = formatPhoneNumberForApi(recipientPhone?.value || '');
            } else {
                data.bank_account_number = document.getElementById('bank_account_number')?.value;
                data.bic = bicInput?.value;
                data.account_name = recipientName.value;
                data.transfer_type = transferType?.value;
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
                
                if (previewFee) previewFee.textContent = data.currency + ' ' + formatCurrency(fee);
                if (previewTotal) previewTotal.textContent = data.currency + ' ' + formatCurrency(total);
                
                @if($balance)
                    const balanceAfter = parseFloat({{ $balance['available'] ?? 0 }}) - total;
                    if (previewBalanceAfter) previewBalanceAfter.textContent = data.currency + ' ' + formatCurrency(balanceAfter);
                @endif
                
                if (previewFeeSection) previewFeeSection.classList.remove('hidden');

                showPreviewModal(result.data, data);
            } else if (previewError) {
                previewError.textContent = result.message || 'Preview failed';
                previewError.classList.remove('hidden');
            }
        } catch (error) {
            if (previewError) {
                previewError.textContent = 'Error previewing payout: ' + error.message;
                previewError.classList.remove('hidden');
            }
        } finally {
            if (previewBtn) {
                previewBtn.disabled = false;
                previewBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg> Preview Payout';
            }
        }
    }

    function showPreviewModal(apiData, formData) {
        if (!previewModalContent) return;
        previewModalContent.innerHTML = `
            <div class="space-y-5">
                <div class="p-4 rounded-xl bg-gradient-to-r from-primary-50 to-indigo-50 dark:from-primary-900/20 dark:to-indigo-900/20 border border-primary-100 dark:border-primary-800">
                    <div class="text-sm font-bold text-primary-900 dark:text-white mb-2">
                        ${formData.payout_type === 'MOBILE_MONEY' ? '📱 Mobile Money Payout' : '🏦 Bank Transfer'}
                    </div>
                    ${formData.payout_type === 'MOBILE_MONEY' ? `
                        <div class="text-xs text-primary-600 dark:text-primary-300">Provider: <span class="font-bold">${apiData.channelProvider || 'Detected automatically'}</span></div>
                        <div class="text-xs text-primary-600 dark:text-primary-300 mt-1">Phone: <span class="font-mono font-bold">${formData.recipient_phone}</span></div>
                    ` : `
                        <div class="text-xs text-primary-600 dark:text-primary-300">Bank: <span class="font-bold">${bankNameInput?.value || 'Selected Bank'}</span></div>
                        <div class="text-xs text-primary-600 dark:text-primary-300 mt-1">Account: <span class="font-mono font-bold">${formData.bank_account_number}</span></div>
                        <div class="text-xs text-primary-600 dark:text-primary-300 mt-1">Transfer Type: <span class="font-bold">${formData.transfer_type}</span></div>
                    `}
                </div>

                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600">
                    <h4 class="text-xs font-bold text-gray-900 dark:text-white mb-3 uppercase tracking-wide">Amount Breakdown</h4>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-gray-500">Payout Amount</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">${formData.currency} ${formatCurrency(formData.amount)}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Transaction Fee</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">${formData.currency} ${formatCurrency(apiData.fee || 0)}</span>
                    </div>
                    <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-500 flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Total</span>
                        <span class="text-xl font-extrabold text-green-600">${formData.currency} ${formatCurrency(parseFloat(formData.amount) + parseFloat(apiData.fee || 0))}</span>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-600">
                    <h4 class="text-xs font-bold text-gray-900 dark:text-white mb-3 uppercase tracking-wide">Recipient Details</h4>
                    <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Name: <span class="font-semibold text-gray-900 dark:text-white">${formData.recipient_name}</span></div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Order Reference: <span class="font-mono font-semibold text-primary-600 dark:text-primary-400">${previewData?.order_reference || 'PREVIEW'}</span></div>
                </div>
            </div>
        `;
        if (previewModal) {
            previewModal.classList.remove('hidden');
            previewModal.classList.add('flex');
        }
    }

    if (editPreviewBtn) {
        editPreviewBtn.addEventListener('click', function () {
            if (previewModal) {
                previewModal.classList.add('hidden');
                previewModal.classList.remove('flex');
            }
        });
    }

    if (confirmPreviewBtn) {
        confirmPreviewBtn.addEventListener('click', function () {
            if (previewModal) {
                previewModal.classList.add('hidden');
                previewModal.classList.remove('flex');
            }
            if (form) form.submit();
        });
    }

    if (previewModal) {
        previewModal.addEventListener('click', function (e) {
            if (e.target === previewModal && editPreviewBtn) {
                previewModal.classList.add('hidden');
                previewModal.classList.remove('flex');
            }
        });
    }

    if (description && descCharCount) {
        description.addEventListener('input', function () {
            const remaining = 500 - this.value.length;
            descCharCount.textContent = remaining;
            descCharCount.classList.toggle('text-red-500', remaining < 30);
            descCharCount.classList.toggle('text-gray-400', remaining >= 30);
        });
    }

    if (recipientName && amount && currency) {
        [recipientName, amount, currency].forEach((el) => {
            el?.addEventListener('input', syncPreview);
            el?.addEventListener('change', syncPreview);
        });
    }

    if (payoutTypeMobile) payoutTypeMobile.addEventListener('change', togglePayoutFields);
    if (payoutTypeBank) payoutTypeBank.addEventListener('change', togglePayoutFields);
    if (previewBtn) previewBtn.addEventListener('click', previewPayout);

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            if (form) form.reset();
            if (descCharCount) {
                descCharCount.textContent = '500';
                descCharCount.classList.remove('text-red-500');
                descCharCount.classList.add('text-gray-400');
            }
            if (previewFeeSection) previewFeeSection.classList.add('hidden');
            if (previewError) previewError.classList.add('hidden');
            if (providerBadge) {
                providerBadge.classList.add('hidden');
                providerBadge.classList.remove('flex');
            }
            togglePayoutFields();
            const prefix = 'FEEDTANPAY';
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let randomPart = '';
            for (let i = 0; i < 7; i++) {
                randomPart += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            const orderInput = document.getElementById('orderReferenceInput');
            if (orderInput) orderInput.value = prefix + randomPart;
        });
    }

    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25"></circle><path d="M4 12a8 8 0 018-8" stroke-width="4" class="opacity-75"></path></svg> Processing...';
            }
        });
    }

    togglePayoutFields();
    syncPreview();
});
</script>
@endpush
