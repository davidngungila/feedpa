@if(session('success'))
    <div class="mb-4 p-4 rounded-lg bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-800 dark:text-green-300 flex items-center gap-3 animate-fade-in">
        <i class="fas fa-check-circle text-xl"></i>
        <div class="flex-1">
            <p class="font-bold text-sm">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-4 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-800 dark:text-red-300 flex items-center gap-3 animate-fade-in">
        <i class="fas fa-exclamation-circle text-xl"></i>
        <div class="flex-1">
            <p class="font-bold text-sm">{{ session('error') }}</p>
        </div>
    </div>
@endif

@if(session('info'))
    <div class="mb-4 p-4 rounded-lg bg-blue-100 dark:bg-blue-900/30 border border-blue-400 dark:border-blue-700 text-blue-800 dark:text-blue-300 flex items-center gap-3 animate-fade-in">
        <i class="fas fa-info-circle text-xl"></i>
        <div class="flex-1">
            <p class="font-bold text-sm">{{ session('info') }}</p>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="mb-4 p-4 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-800 dark:text-red-300 animate-fade-in">
        <div class="flex items-center gap-3 mb-2">
            <i class="fas fa-exclamation-triangle text-xl"></i>
            <p class="font-bold text-sm">Whoops! There were some problems with your input.</p>
        </div>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li class="text-xs">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
