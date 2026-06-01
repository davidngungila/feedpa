@extends('layouts.app')

@section('title', 'SMS Settings')

@section('content')
<div class="space-y-6 animate-fade-in">
    <h2 class="text-xl font-bold text-primary-900 dark:text-white">SMS Settings</h2>

    <div class="card p-6">
        <div class="mb-4">
            <p class="text-primary-600 dark:text-primary-300 text-sm">
                <i class="fas fa-info-circle mr-2"></i> SMS settings will be available soon.
            </p>
        </div>
        
        <div class="bg-primary-50 dark:bg-primary-900/20 p-4 rounded-lg">
            <h3 class="font-bold text-primary-700 dark:text-primary-300 mb-2">Coming Soon:</h3>
            <ul class="text-sm text-primary-600 dark:text-primary-300 space-y-1 list-disc pl-5">
                <li>SMS gateway configuration</li>
                <li>Custom SMS templates</li>
                <li>Transaction notification triggers</li>
            </ul>
        </div>
    </div>
</div>
@endsection
