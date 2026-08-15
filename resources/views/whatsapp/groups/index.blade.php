@extends('layouts.app')

@section('title', 'Manage Groups')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fab fa-whatsapp text-primary-500"></i> Manage Groups
            </h2>
            <p class="text-xs text-primary-500 mt-1">Live groups captured from the WhatsApp session API</p>
        </div>
        <a href="{{ url()->current() }}" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
            <i class="fas fa-sync-alt mr-1"></i> Refresh
        </a>
    </div>

    @if(session('success'))
        <div class="card p-4 border-l-4 border-l-green-500 bg-green-50/60 dark:bg-green-900/10">
            <p class="text-xs font-bold text-green-700 dark:text-green-300">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    @if(!$apiKeyConfigured)
        <div class="card p-4 border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle mr-1"></i> The WhatsApp Session API key is not configured.
                <a href="{{ route('settings.whatsapp') }}" class="underline">Configure it in WhatsApp settings</a> to load live groups.
            </p>
        </div>
    @endif

    <!-- Groups Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($groups as $group)
            <div class="card p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center overflow-hidden">
                        @if($group['img_url'])
                            <img src="{{ $group['img_url'] }}" alt="{{ $group['name'] }}" class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-users text-xl text-primary-600 dark:text-primary-400"></i>
                        @endif
                    </div>
                    <a href="{{ route('whatsapp.groups.details', $group['jid']) }}" class="px-3 py-1.5 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300 hover:bg-primary-600 hover:text-white transition-all">
                        <i class="fas fa-eye mr-1"></i> Details
                    </a>
                </div>
                <a href="{{ route('whatsapp.groups.details', $group['jid']) }}" class="hover:text-primary-500 transition-colors">
                    <h3 class="text-sm font-bold text-primary-900 dark:text-white">{{ $group['name'] }}</h3>
                </a>
                <p class="text-[11px] text-primary-500 mt-1 font-mono break-all">{{ $group['jid'] }}</p>
                <p class="text-[11px] text-primary-500 mt-1 line-clamp-2">{{ $group['description'] ?? 'No description' }}</p>
                <div class="mt-4 pt-4 border-t border-primary-100 dark:border-primary-800 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-primary-500">
                        <i class="fas fa-user mr-1"></i> {{ $group['participants_count'] }} members
                    </span>
                    @if($group['creation'])
                        <span class="text-[10px] text-primary-400">
                            <i class="far fa-calendar mr-1"></i> {{ $group['creation'] }}
                        </span>
                    @endif
                </div>
                @if($group['participants'])
                    <div class="mt-3 pt-3 border-t border-primary-100 dark:border-primary-800">
                        <p class="text-[10px] font-black text-primary-500 uppercase tracking-wider mb-2">Participants</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($group['participants'], 0, 6) as $participant)
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">{{ $participant['name'] ?? $participant['jid'] ?? 'Member' }}</span>
                            @endforeach
                            @if(count($group['participants']) > 6)
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-primary-500 dark:bg-gray-800">+{{ count($group['participants']) - 6 }} more</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="card p-16 text-center col-span-full">
                <i class="fab fa-whatsapp text-4xl text-primary-300 mb-3 block"></i>
                <p class="text-sm font-bold text-primary-500">No groups found</p>
                <p class="text-xs text-primary-400 mt-1">Groups will appear here from the connected WhatsApp session.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
