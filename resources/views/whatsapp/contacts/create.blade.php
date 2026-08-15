@extends('layouts.app')

@section('title', 'Add Contact')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-user-plus text-primary-500"></i> Add Contact
            </h2>
            <p class="text-xs text-primary-500 mt-1">Create a new WhatsApp contact</p>
        </div>
        <a href="{{ route('whatsapp.contacts.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('whatsapp.contacts.store') }}">
            @csrf
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="John Doe">
                        @error('name') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Phone *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="255655123456">
                        @error('phone') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="john@example.com">
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Company</label>
                        <input type="text" name="company" value="{{ old('company') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Company name">
                    </div>
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Optional notes">{{ old('notes') }}</textarea>
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Groups</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($groups as $group)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="groups[]" value="{{ $group->id }}" class="w-4 h-4 rounded">
                                <span class="text-xs font-bold text-primary-700 dark:text-primary-300">{{ $group->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($groups->isEmpty())
                        <p class="text-[10px] text-primary-400">No groups yet. <a href="{{ route('whatsapp.groups.create') }}" class="text-primary-600 underline">Create one</a></p>
                    @endif
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20">
                    <i class="fas fa-save me-2"></i> Save Contact
                </button>
                <a href="{{ route('whatsapp.contacts.index') }}" class="px-6 py-3 bg-gray-100 dark:bg-primary-900/20 text-primary-700 font-bold rounded-xl hover:bg-gray-200 transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
