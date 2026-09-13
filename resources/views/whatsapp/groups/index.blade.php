@extends('layouts.app')

@section('title', 'Manage Groups')

@section('content')
<div x-data="groupDrawer()" @keydown.escape.window="closeDrawer()" class="max-w-6xl mx-auto space-y-6 animate-fade-in">
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

    @if($rateLimited)
        <div class="card p-4 border-l-4 border-l-amber-500 bg-amber-50/60 dark:bg-amber-900/10">
            <p class="text-xs font-bold text-amber-700 dark:text-amber-300">
                <i class="fas fa-exclamation-triangle mr-1"></i> The WhatsApp API rate limit was reached while loading group details. Some groups show basic info only - refresh after a minute to load the rest.
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
                    <a href="#" @click.prevent="openDrawer(@js($group))" class="px-3 py-1.5 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300 hover:bg-primary-600 hover:text-white transition-all">
                        <i class="fas fa-eye mr-1"></i> Details
                    </a>
                </div>
                <a href="#" @click.prevent="openDrawer(@js($group))" class="hover:text-primary-500 transition-colors">
                    <h3 class="text-sm font-bold text-primary-900 dark:text-white">{{ $group['name'] }}</h3>
                </a>
                <p class="text-[11px] text-primary-500 mt-1 font-mono break-all">{{ $group['jid'] }}</p>
                <p class="text-[11px] text-primary-500 mt-1 line-clamp-2">{{ $group['description'] ?? 'No description' }}</p>
                <div class="mt-4 pt-4 border-t border-primary-100 dark:border-primary-800 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-primary-500">
                        <i class="fas fa-user mr-1"></i> {{ $group['participants_count'] ? $group['participants_count'] . ' members' : 'Members —' }}
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

<!-- Group Details Drawer -->
<div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] flex justify-end overflow-hidden" style="display:none;">
    <div @click="closeDrawer()" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div x-show="drawerOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative w-full sm:w-[480px] max-w-[100vw] h-full max-h-screen bg-white dark:bg-dark-900 shadow-2xl flex flex-col overflow-hidden">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between p-5 border-b border-primary-100 dark:border-primary-800">
            <div>
                <h3 class="text-sm font-black text-primary-900 dark:text-white flex items-center gap-2">
                    <i class="fas fa-users text-primary-500"></i> Group Details
                </h3>
                <p x-show="selected" x-text="selected?.name" class="text-[11px] text-primary-500 mt-0.5 truncate max-w-[280px]"></p>
            </div>
            <button @click="closeDrawer()" class="w-9 h-9 rounded-xl bg-primary-100 dark:bg-primary-800 text-primary-600 dark:text-primary-300 hover:bg-primary-600 hover:text-white transition-all flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Drawer Body -->
        <div class="flex-1 overflow-y-auto p-5 space-y-5">
            <!-- Loading Spinner -->
            <div x-show="loading" x-cloak class="flex flex-col items-center justify-center py-16">
                <div class="w-10 h-10 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
                <p class="text-xs text-primary-500 mt-3 font-bold">Loading group details...</p>
            </div>

            <!-- Group Info -->
            <div x-show="selected && !loading">
                <div class="flex items-start gap-4">
                    <div x-show="selected.img_url" class="w-16 h-16 rounded-2xl overflow-hidden shrink-0">
                        <img :src="selected.img_url" :alt="selected.name" class="w-full h-full object-cover">
                    </div>
                    <div x-show="!selected.img_url" class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center shrink-0">
                        <i class="fas fa-users text-2xl text-primary-600 dark:text-primary-400"></i>
                    </div>
                    <div class="min-w-0">
                        <p x-text="selected.name" class="text-base font-black text-primary-900 dark:text-white break-words"></p>
                        <p class="text-[11px] font-mono text-primary-500 mt-1 break-all" x-text="selected.jid"></p>
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-5 p-4 rounded-2xl bg-primary-50/60 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-800">
                    <p class="text-[10px] font-black text-primary-500 uppercase tracking-wider mb-1">Description</p>
                    <p x-text="selected.description || 'No description'" class="text-[12px] text-primary-700 dark:text-primary-300 leading-relaxed"></p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="p-4 rounded-2xl bg-primary-50/60 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-800">
                        <p class="text-[10px] font-black text-primary-500 uppercase tracking-wider mb-1">Participants</p>
                        <p class="text-lg font-black text-primary-900 dark:text-white" x-text="selected.participants_count ?? selected.participants?.length ?? '—'"></p>
                    </div>
                    <div class="p-4 rounded-2xl bg-primary-50/60 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-800">
                        <p class="text-[10px] font-black text-primary-500 uppercase tracking-wider mb-1">Created</p>
                        <p class="text-lg font-black text-primary-900 dark:text-white" x-text="selected.creation || '—'"></p>
                    </div>
                </div>

                <!-- Participants List -->
                <div class="mt-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-black text-primary-500 uppercase tracking-wider">Participants</p>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                            <i class="fas fa-user mr-1"></i> <span x-text="selected.participants?.length ?? 0"></span> members
                        </span>
                    </div>
                    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        <template x-for="(participant, index) in selected.participants" :key="index">
                            <div class="flex items-start gap-3 p-3 rounded-xl bg-primary-50/60 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-800">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center shrink-0">
                                    <i class="fas fa-user text-sm text-primary-600 dark:text-primary-400"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-primary-900 dark:text-white truncate" x-text="participant.name || 'Member'"></p>
                                    <p class="text-[11px] font-mono text-primary-500 truncate" x-text="participant.jid"></p>
                                </div>
                            </div>
                        </template>
                        <div x-show="!selected.participants?.length" class="p-4 rounded-xl bg-primary-50/60 dark:bg-primary-900/10 text-center">
                            <p class="text-xs text-primary-500">No participants available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Drawer Footer -->
        <div class="p-5 border-t border-primary-100 dark:border-primary-800">
            <button @click="closeDrawer()" class="w-full px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                <i class="fas fa-times mr-1"></i> Close
            </button>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>
@endsection

@push('scripts')
<script>
    function groupDrawer() {
        return {
            drawerOpen: false,
            selected: null,
            loading: false,
            openDrawer(group) {
                this.selected = group;
                this.loading = true;
                this.drawerOpen = true;

                this.$nextTick(() => {
                    setTimeout(() => {
                        this.loading = false;
                    }, 500);
                });
            },
            closeDrawer() {
                this.drawerOpen = false;
                this.loading = false;
            }
        }
    }
</script>
@endpush