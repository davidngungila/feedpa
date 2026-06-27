@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 animate-fade-in">
    <div class="card p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.3em] font-black text-primary-500">Activity Center</p>
            <h1 class="text-2xl font-black text-primary-900 dark:text-white mt-2">Notifications</h1>
            <p class="text-sm text-primary-500 mt-1">Track new payments, payout approvals, rejections, and authorization events.</p>
        </div>

        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                <i class="fas fa-check-double"></i>
                <span>Mark All Read</span>
            </button>
        </form>
    </div>

    <div class="card overflow-hidden">
        @forelse($notifications as $notification)
            <a href="{{ route('notifications.open', $notification) }}"
               class="block px-6 py-5 border-b border-primary-50 dark:border-dark-border hover:bg-primary-50/60 dark:hover:bg-primary-900/10 transition-all {{ $notification->is_read ? '' : 'bg-primary-50/40 dark:bg-primary-900/10' }}">
                <div class="flex items-start gap-4">
                    <div class="mt-1 w-3 h-3 rounded-full {{ $notification->is_read ? 'bg-primary-200' : 'bg-primary-500' }}"></div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-bold text-primary-900 dark:text-white">{{ $notification->title }}</h3>
                                <p class="text-[11px] uppercase tracking-widest text-primary-400 mt-1">{{ str_replace('_', ' ', strtoupper($notification->type)) }}</p>
                            </div>
                            <span class="text-xs text-primary-400 whitespace-nowrap">{{ $notification->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <p class="mt-3 text-sm text-primary-600 dark:text-primary-300">{{ $notification->message }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-primary-50 dark:bg-dark-900 flex items-center justify-center">
                    <i class="fas fa-bell-slash text-2xl text-primary-300"></i>
                </div>
                <h3 class="mt-4 text-lg font-black text-primary-900 dark:text-white">No notifications yet</h3>
                <p class="mt-2 text-sm text-primary-500">Payment and payout workflow updates will show here.</p>
            </div>
        @endforelse

        @if($notifications->hasPages())
            <div class="p-4 bg-primary-50/30 dark:bg-dark-900/30">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
