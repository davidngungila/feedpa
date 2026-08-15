@extends('layouts.app')

@section('title', 'All Contacts')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-address-book text-primary-500"></i> All Contacts
            </h2>
            <p class="text-xs text-primary-500 mt-1">Contacts synced with the WhatsApp session</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ request()->fullUrlWithQuery([]) }}" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-primary-900/20 hover:bg-gray-200 text-primary-700 text-xs font-bold transition-all">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </a>
        </div>
    </div>

    @if($error)
        <div class="card p-4 border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ $error }}
            </p>
        </div>
    @endif

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    <!-- Contacts Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Phone / JID</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Display Name</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900 dark:to-green-800 flex items-center justify-center overflow-hidden">
                                        @if(!empty($contact['imgUrl']))
                                            <img src="{{ $contact['imgUrl'] }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <i class="fab fa-whatsapp text-green-600 dark:text-green-400"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-primary-900 dark:text-white truncate">{{ $contact['name'] ?? 'Unknown' }}</p>
                                        @if(!empty($contact['verifiedName']) && $contact['verifiedName'] !== ($contact['name'] ?? null))
                                            <p class="text-[10px] text-primary-400">
                                                <i class="fas fa-check-circle mr-0.5 text-blue-500"></i> {{ $contact['verifiedName'] }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 font-mono">{{ $contact['id'] ?? $contact['jid'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300">{{ $contact['notify'] ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs text-primary-700 dark:text-primary-300 italic max-w-xs line-clamp-1">{{ $contact['status'] ?? '—' }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <i class="fas fa-address-book text-4xl text-primary-300 mb-3 block"></i>
                                @if($error)
                                    <p class="text-sm font-bold text-primary-500">Could not load contacts</p>
                                    <p class="text-xs text-primary-400 mt-1">Check the error message above.</p>
                                @else
                                    <p class="text-sm font-bold text-primary-500">No contacts synced</p>
                                    <p class="text-xs text-primary-400 mt-1">Contacts synced with the WhatsApp session will appear here.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(!$error)
            <div class="px-6 py-4 border-t border-primary-100 dark:border-dark-border">
                <p class="text-[10px] text-primary-400 font-bold uppercase tracking-wider">{{ count($contacts) }} contact{{ count($contacts) === 1 ? '' : 's' }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
