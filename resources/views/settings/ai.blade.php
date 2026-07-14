@extends('layouts.app')

@section('title', 'AI Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 animate-fade-in">
    <!-- Status Header Card -->
    <div class="card overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex items-center gap-6">
                <div class="p-3 bg-white rounded-2xl border border-primary-100 shadow-sm flex-shrink-0">
                    <i class="fas fa-robot text-4xl text-primary-600"></i>
                </div>
                <div>
                    <div class="text-[10px] text-primary-500 uppercase font-extrabold tracking-widest mb-1">System Configuration</div>
                    <div class="text-xl font-bold text-primary-900 dark:text-white">AI Settings</div>
                    <div class="mt-2">
                        <span class="badge badge-{{ $groqApiKey ? 'green' : 'yellow' }} px-4 py-1.5 text-xs">
                            <i class="fas fa-{{ $groqApiKey ? 'check' : 'clock' }} me-2"></i>
                            {{ $groqApiKey ? 'AI Assistant Enabled' : 'AI Assistant Disabled' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="card p-6 border-green-100 bg-green-50 dark:bg-green-900/10">
            <div class="flex items-center gap-4 text-green-600 dark:text-green-400">
                <i class="fas fa-check-circle text-2xl"></i>
                <div>
                    <h4 class="font-bold">Success</h4>
                    <p class="text-xs">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="card p-6 border-red-100 bg-red-50 dark:bg-red-900/10">
            <div class="flex items-center gap-4 text-red-600 dark:text-red-400">
                <i class="fas fa-exclamation-circle text-2xl"></i>
                <div>
                    <h4 class="font-bold">Error</h4>
                    <p class="text-xs">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
        <!-- AI Config Form -->
        <div class="card p-6 space-y-6">
            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500 flex items-center gap-2">
                <i class="fas fa-sliders-h"></i> Groq AI Configuration
            </h3>
            <form method="POST" action="{{ route('settings.ai.update') }}">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                        <div class="md:col-span-2">
                            <div class="text-[10px] text-gray-400 uppercase font-bold mb-1">Groq API Key</div>
                            <input type="password" name="groq_api_key" value="{{ old('groq_api_key', $groqApiKey) }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Enter your Groq API key">
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white font-bold rounded-xl hover:shadow-lg transition-all shadow-lg shadow-primary-900/20">
                        <i class="fas fa-save me-2"></i> Save AI Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
