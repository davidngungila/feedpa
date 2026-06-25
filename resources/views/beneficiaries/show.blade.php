@extends('layouts.app')

@section('title', 'View Beneficiary')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <!-- Icon Section -->
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center">
                        <i class="fas fa-user text-4xl text-primary-600 dark:text-primary-400"></i>
                    </div>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">Beneficiary</div>
                    <div class="text-xl font-mono font-bold text-primary-900 dark:text-white">{{ $beneficiary->name }}</div>
                    <div class="mt-2">
                        <span class="px-4 py-1.5 text-xs font-bold rounded-full {{ $beneficiary->type === 'bank' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' }}">
                            <i class="{{ $beneficiary->type === 'bank' ? 'fas fa-university' : 'fas fa-mobile-alt' }} me-2"></i>
                            {{ $beneficiary->type === 'bank' ? 'Bank' : 'Mobile Money' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('beneficiaries.edit', $beneficiary->id) }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <a href="{{ route('beneficiaries.index') }}" class="px-4 py-2 rounded-xl border border-primary-100 dark:border-dark-border text-xs font-bold text-primary-600 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <!-- Details Card -->
    <div class="card p-6 sm:p-8 space-y-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
            <i class="fas fa-info-circle"></i> Beneficiary Details
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-[10px] text-primary-500 uppercase font-bold">Name</p>
                <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $beneficiary->name }}</p>
            </div>
            <div>
                <p class="text-[10px] text-primary-500 uppercase font-bold">Type</p>
                <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $beneficiary->type === 'bank' ? 'Bank' : 'Mobile Money' }}</p>
            </div>
            @if($beneficiary->type === 'bank')
                <div>
                    <p class="text-[10px] text-primary-500 uppercase font-bold">Bank Name</p>
                    <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $beneficiary->bank_name }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-primary-500 uppercase font-bold">Account Number</p>
                    <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $beneficiary->account_number }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-primary-500 uppercase font-bold">Transfer Type</p>
                    <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $beneficiary->transfer_type }}</p>
                </div>
            @else
                <div>
                    <p class="text-[10px] text-primary-500 uppercase font-bold">Phone Number</p>
                    <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $beneficiary->phone }}</p>
                </div>
            @endif
            @if($beneficiary->email)
                <div>
                    <p class="text-[10px] text-primary-500 uppercase font-bold">Email</p>
                    <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $beneficiary->email }}</p>
                </div>
            @endif
            <div>
                <p class="text-[10px] text-primary-500 uppercase font-bold">Status</p>
                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ $beneficiary->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                    {{ $beneficiary->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>
</div>
@endsection