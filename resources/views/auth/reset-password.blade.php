<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', showPassword: false, showConfirmPassword: false }" :class="{'dark': darkMode}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | FEEDTAN</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b',950:'#022c22' },
                        dark: { 800:'#0f1a14',850:'#111f17',900:'#0a140e',card:'#0d1f16',border:'#1a3328' }
                    },
                    fontFamily: { sans:['Plus Jakarta Sans','sans-serif'] },
                    animation: { 'fade-in':'fadeIn 0.4s ease' },
                    keyframes: { fadeIn:{from:{opacity:0,transform:'translateY(10px)'},to:{opacity:1,transform:'translateY(0)'}} }
                }
            }
        }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif !important; }
        body { margin: 0; }
    </style>
</head>
<body class="h-full" :class="darkMode ? 'dark bg-[#0a140e]' : 'bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700'">

    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Background decorations -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full opacity-10" :class="darkMode ? 'bg-primary-400' : 'bg-white'"></div>
            <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full opacity-10" :class="darkMode ? 'bg-primary-500' : 'bg-white'"></div>
            <div class="absolute top-1/3 right-1/4 w-64 h-64 rounded-full opacity-5" :class="darkMode ? 'bg-primary-300' : 'bg-white'"></div>
        </div>

        <div class="relative w-full max-w-md mx-4">
            <div class="card rounded-2xl p-8 shadow-2xl" :class="darkMode ? 'bg-[#0d1f16] border border-[#1a3328]' : 'bg-white'">
                <!-- Logo/Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-600 mb-4 shadow-lg">
                        <i class="fas fa-lock text-white text-2xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold" :class="darkMode?'text-white':'text-primary-900'">Reset Password</h1>
                    <p class="text-sm mt-1" :class="darkMode?'text-primary-300':'text-primary-600'">Create your new password</p>
                </div>

                <!-- Session Status -->
                @if(session('status'))
                    <div class="mb-4 p-4 rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-800 dark:text-green-300 flex items-center gap-2 animate-fade-in">
                        <i class="fas fa-check-circle text-xl"></i>
                        <div class="flex-1">
                            <p class="font-bold text-sm">{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Error Toast -->
                <div id="error-toast" class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-400 dark:border-red-700 text-red-800 dark:text-red-300 flex items-center gap-2 animate-fade-in" style="display: none;">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    <div class="flex-1">
                        <p class="font-bold text-sm" id="error-toast-text"></p>
                    </div>
                </div>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2" :class="darkMode?'text-primary-300':'text-primary-700'">New Password</label>
                        <div class="relative">
                            <i class="fas fa-key absolute left-3 top-1/2 -translate-y-1/2 text-sm" :class="darkMode?'text-primary-400':'text-primary-500'"></i>
                            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required placeholder="••••••••" minlength="6"
                                   class="w-full pl-10 pr-10 py-3 rounded-xl border focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all"
                                   :class="darkMode?'bg-[#0a140e] border-[#1a3328] text-white placeholder-gray-500':'bg-gray-50 border-primary-100 text-primary-900 placeholder-gray-400'">
                            <button type="button" @click="showPassword=!showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm" :class="darkMode?'text-primary-400':'text-primary-500'">
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2" :class="darkMode?'text-primary-300':'text-primary-700'">Confirm New Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-sm" :class="darkMode?'text-primary-400':'text-primary-500'"></i>
                            <input id="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required placeholder="••••••••" minlength="6"
                                   class="w-full pl-10 pr-10 py-3 rounded-xl border focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all"
                                   :class="darkMode?'bg-[#0a140e] border-[#1a3328] text-white placeholder-gray-500':'bg-gray-50 border-primary-100 text-primary-900 placeholder-gray-400'">
                            <button type="button" @click="showConfirmPassword=!showConfirmPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm" :class="darkMode?'text-primary-400':'text-primary-500'">
                                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-primary-600 hover:bg-primary-500 text-white font-bold text-sm transition-all shadow-lg hover:shadow-primary-900/30 active:scale-95">
                        <i class="fas fa-check mr-2"></i> Reset Password
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Login
                    </a>
                </div>

                <!-- Dark mode toggle -->
                <div class="mt-4 flex justify-center">
                    <button @click="darkMode=!darkMode; localStorage.setItem('darkMode', darkMode);" class="text-xs flex items-center gap-2 transition-colors" :class="darkMode?'text-primary-300':'text-primary-600'">
                        <i :class="darkMode?'fa-solid fa-sun':'fa-solid fa-moon'"></i>
                        <span x-text="darkMode?'Switch to Light Mode':'Switch to Dark Mode'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                const toast = document.getElementById('error-toast');
                const textEl = document.getElementById('error-toast-text');
                textEl.textContent = '{{ $errors->first() }}';
                toast.style.display = 'flex';
                setTimeout(() => toast.style.display = 'none', 5000);
            @endif
        });
    </script>
</body>
</html>
