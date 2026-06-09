<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b',950:'#022c22' }
                    },
                    fontFamily: { sans:['Plus Jakarta Sans','sans-serif'], mono:['JetBrains Mono','monospace'] },
                    animation: { 'fade-in':'fadeIn 0.4s ease','slide-in':'slideIn 0.3s ease' },
                    keyframes: { fadeIn:{from:{opacity:0,transform:'translateY(10px)'},to:{opacity:1,transform:'translateY(0)'}}, slideIn:{from:{opacity:0,transform:'translateX(-20px)'},to:{opacity:1,transform:'translateX(0)'}} }
                }
            }
        }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        .card { background: #ffffff; border: 1px solid #d1fae5; box-shadow: 0 2px 12px rgba(6,78,59,0.08); border-radius: 1rem; }
        .badge { display:inline-flex; align-items:center; padding:2px 10px; border-radius:999px; font-size:11px; font-weight:600; }
        .badge-green { background:#d1fae5; color:#065f46; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="card rounded-2xl p-8 shadow-2xl animate-fade-in">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-600 mb-4 shadow-lg">
                        <i class="fa-solid fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-primary-900">Two-Factor Authentication</h1>
                    <p class="text-sm mt-1 text-primary-600">
                        Enter the verification code from your authenticator app or use a recovery code.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200">
                        <p class="text-xs font-bold text-red-700">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $errors->first() }}
                        </p>
                    </div>
                @endif

                <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="code" class="block text-xs font-bold uppercase tracking-widest text-primary-500 mb-2">Verification Code</label>
                        <input type="text" id="code" name="code" 
                               class="w-full bg-primary-50 border border-primary-100 rounded-xl px-4 py-3 text-sm text-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="Enter 6-digit code or recovery code" 
                               inputmode="text"
                               autocomplete="one-time-code">
                    </div>

                    <button type="submit" 
                            class="w-full py-3 rounded-xl bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold transition-all duration-200 hover:shadow-lg hover:shadow-primary-900/20 active:scale-[0.98]">
                        <i class="fa-solid fa-check mr-2"></i> Verify & Login
                    </button>
                </form>

                <div class="mt-4 pt-4 border-t border-primary-100 text-center">
                    <a href="{{ route('login') }}" 
                       class="text-xs font-semibold text-primary-600 hover:text-primary-500 transition-colors">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
