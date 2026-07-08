<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bad Request - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { font-family: 'Manrope', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4 text-center">
        <div class="mb-8">
            <div class="w-32 h-32 mx-auto bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <h1 class="text-6xl font-bold text-gray-800 mb-4">400</h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Bad Request</h2>
        <p class="text-gray-600 mb-4">The request could not be understood by the server due to malformed syntax.</p>
        <div class="mb-8">
            <p class="text-gray-600 mb-2">If the problem persists, please contact our system administrator:</p>
            <div class="flex flex-col items-center gap-2 text-emerald-700">
                <a href="tel:+255622239304" class="flex items-center gap-2 hover:text-emerald-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    +255 622 239 304
                </a>
                <a href="mailto:feedtan15@gmail.com" class="flex items-center gap-2 hover:text-emerald-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    feedtan15@gmail.com
                </a>
            </div>
        </div>
        <a href="{{ url('/') }}" class="inline-block bg-emerald-700 hover:bg-emerald-800 text-white font-semibold py-3 px-8 rounded-lg transition-colors duration-200">
            Go Home
        </a>
    </div>
</body>
</html>
