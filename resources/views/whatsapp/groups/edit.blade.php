@extends('layouts.app')

@section('title', 'Edit Group')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-users text-primary-500"></i> Edit Group
            </h2>
            <p class="text-xs text-primary-500 mt-1">Update group details and members</p>
        </div>
        <a href="{{ route('whatsapp.groups.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('whatsapp.groups.update', $group->id) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Group Name *</label>
                    <input type="text" name="name" value="{{ old('name', $group->name) }}" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Sales Team">
                    @error('name') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">WhatsApp Group ID (optional)</label>
                    <input type="text" name="group_id" value="{{ old('group_id', $group->group_id) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="e.g. 255700000000-123456789@g.us">
                    <p class="text-[10px] text-primary-400 mt-1">Link this group to an existing WhatsApp group to send messages directly to it.</p>
                    @error('group_id') <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-1 block">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Optional description">{{ old('description', $group->description) }}</textarea>
                </div>
                <div>
                    <label class="text-[10px] text-gray-400 uppercase font-bold mb-2 block">Members</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-60 overflow-y-auto p-3 rounded-xl border border-primary-100 dark:border-primary-800">
                        @forelse($contacts as $contact)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="contacts[]" value="{{ $contact->id }}" class="w-4 h-4 rounded" {{ $group->contacts->contains($contact->id) ? 'checked' : '' }}>
                                <span class="text-xs font-bold text-primary-700 dark:text-primary-300">{{ $contact->name }}</span>
                            </label>
                        @empty
                            <p class="text-[10px] text-primary-400 col-span-full">No contacts available.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20">
                    <i class="fas fa-save me-2"></i> Update Group
                </button>
                <a href="{{ route('whatsapp.groups.index') }}" class="px-6 py-3 bg-gray-100 dark:bg-primary-900/20 text-primary-700 font-bold rounded-xl hover:bg-gray-200 transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
