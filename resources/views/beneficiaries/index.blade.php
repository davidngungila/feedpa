@extends('layouts.app')

@section('title', 'Beneficiaries')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-address-book text-primary-500"></i> Beneficiaries
            </h2>
            <p class="text-xs text-primary-500 mt-1">Manage your beneficiaries for quick payouts</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('beneficiaries.create') }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                <i class="fas fa-plus mr-1"></i> Add Beneficiary
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Beneficiaries Table Card -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @foreach($beneficiaries as $beneficiary)
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-primary-900 dark:text-white">{{ $beneficiary->name }}</p>
                                <p class="text-[10px] text-primary-500">Added: {{ $beneficiary->created_at->format('M d, Y') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ $beneficiary->type === 'bank' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' }}">
                                    {{ $beneficiary->type === 'bank' ? 'Bank' : 'Mobile Money' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($beneficiary->type === 'bank')
                                    <p class="text-xs text-primary-700 dark:text-primary-300">{{ $beneficiary->bank_name }}</p>
                                    <p class="text-[10px] text-primary-500">{{ $beneficiary->account_number }}</p>
                                @else
                                    <p class="text-xs text-primary-700 dark:text-primary-300">{{ $beneficiary->phone }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-bold {{ $beneficiary->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' }}">
                                    {{ $beneficiary->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('beneficiaries.show', $beneficiary->id) }}" class="p-2 rounded-lg bg-primary-50 dark:bg-primary-900/20 text-primary-600 hover:bg-primary-600 hover:text-white transition-all" title="View">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="{{ route('beneficiaries.edit', $beneficiary->id) }}" class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 hover:bg-blue-600 hover:text-white transition-all" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </a>
                                    <form action="{{ route('beneficiaries.destroy', $beneficiary->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this beneficiary?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-600 hover:text-white transition-all" title="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($beneficiaries->hasPages())
            <div class="p-6 border-t border-primary-100 dark:border-dark-border">
                {{ $beneficiaries->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection