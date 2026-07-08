<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Expired - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4 text-center">
        <div class="mb-8">
            <div class="w-32 h-32 mx-auto bg-amber-100 rounded-full flex items-center justify-center">
                <svg class="w-16 h-16 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <h1 class="text-6xl font-bold text-gray-800 mb-4">419</h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Page Expired</h2>
        <p class="text-gray-600 mb-8">Your session has expired. Please refresh and try again.</p>
        <a href="{{ url()->current() }}" class="inline-block bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-3 px-8 rounded-lg transition-colors duration-200">
            Refresh
        </a>
    </div>
</body>
</html>
