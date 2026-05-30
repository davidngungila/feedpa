@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-primary-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-history text-primary-500"></i> Audit Logs
            </h2>
            <p class="text-xs text-primary-500 mt-1">Track all user activity and system events</p>
        </div>
    </div>

    <!-- Audit Logs Table Card -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-primary-50 dark:bg-primary-900/20">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">Details</th>
                        <th class="px-6 py-4 text-[10px] font-black text-primary-700 dark:text-primary-300 uppercase tracking-wider">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-100 dark:divide-primary-800">
                    @foreach($audits as $audit)
                    <tr class="hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-primary-900 dark:text-white">{{ $audit->created_at->format('M d, Y H:i:s') }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($audit->user)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900 dark:to-primary-800 flex items-center justify-center overflow-hidden">
                                    <i class="fas fa-user text-primary-600 dark:text-primary-400 text-xs"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-primary-900 dark:text-white truncate">{{ $audit->user->name }}</p>
                                    <p class="text-[10px] text-primary-500">{{ $audit->user->email }}</p>
                                </div>
                            </div>
                            @else
                            <span class="text-sm text-primary-500 dark:text-primary-400 italic">Guest / System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1.5 rounded-full text-[10px] font-bold 
                                {{ $audit->action === 'login' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 
                                   ($audit->action === 'logout' ? 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300' : 
                                   ($audit->action === 'login_failed' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' : 
                                   'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300')) }}">
                                {{ ucwords(str_replace('_', ' ', $audit->action)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-primary-900 dark:text-white">{{ $audit->details }}</p>
                            @if($audit->url)
                            <p class="text-[10px] text-primary-500 mt-1 font-mono truncate max-w-md">{{ $audit->url }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-mono text-primary-900 dark:text-white">{{ $audit->ip_address ?? 'N/A' }}</p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($audits->hasPages())
        <div class="p-6 border-t border-primary-100 dark:border-primary-800">
            {{ $audits->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
