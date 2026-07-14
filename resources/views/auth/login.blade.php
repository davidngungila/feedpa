<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FEEDTAN DIGITAL PAYMENT SYSTEM</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
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
                    animation: { 'fade-in':'fadeIn 0.4s ease','slide-in':'slideIn 0.3s ease','count-up':'countUp 1.5s ease','pulse-slow':'pulse 3s infinite' },
                    keyframes: { fadeIn:{from:{opacity:0,transform:'translateY(10px)'},to:{opacity:1,transform:'translateY(0)'}}, slideIn:{from:{opacity:0,transform:'translateX(-20px)'},to:{opacity:1,transform:'translateX(0)'}} }
                }
            }
        }
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        .main-bg { background: #f0fdf4; }
        .card { background: #ffffff; border: 1px solid #d1fae5; box-shadow: 0 2px 12px rgba(6,78,59,0.08); border-radius: 1rem; }
        .form-input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .form-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
        @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        @keyframes countUp { from { opacity:0; transform: translateY(10px); } to { opacity:1; transform: translateY(0); } }
        @keyframes moveRightLeft { 0%, 100% { transform: translateX(0); } 50% { transform: translateX(-20px); } }
        .hidden-element { display: none !important; }
    </style>
</head>
<body class="h-full main-bg">

    <!-- Auto Logout Toast Notification -->
    <div id="auto-logout-toast" class="fixed top-4 right-4 z-[9999] hidden-element flex items-center gap-4 px-6 py-4 rounded-xl shadow-2xl bg-yellow-100 border border-yellow-400" style="animation: moveRightLeft 4s ease-in-out infinite;">
        <div class="flex-shrink-0">
            <i class="fa-solid fa-clock text-2xl text-yellow-600"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold text-base text-yellow-800">Session Expired</p>
            <p class="text-sm text-yellow-700">You were logged out automatically due to inactivity. Please login to continue.</p>
        </div>
    </div>

    <!-- Error Toast Notification -->
    <div id="error-toast" class="fixed top-4 right-4 z-[9999] hidden-element flex items-center gap-4 px-6 py-4 rounded-xl shadow-2xl bg-red-100 border border-red-400">
        <div class="flex-shrink-0">
            <i class="fa-solid fa-exclamation-circle text-2xl text-red-600"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold text-base text-red-800" id="error-toast-title">Error</p>
            <p class="text-sm text-red-700" id="error-toast-message"></p>
        </div>
    </div>
    
    <!-- Success Toast Notification -->
    <div id="success-toast" class="fixed top-4 right-4 z-[9999] hidden-element flex items-center gap-4 px-6 py-4 rounded-xl shadow-2xl bg-green-100 border border-green-400">
        <div class="flex-shrink-0">
            <i class="fa-solid fa-check-circle text-2xl text-green-600"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold text-base text-green-800">Success!</p>
            <p class="text-sm text-green-700" id="success-toast-message"></p>
        </div>
    </div>

    <!-- ============================================================
         LOGIN SCREEN
         ============================================================ -->
    <div id="loginScreen" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Background decorations -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full opacity-10 bg-primary-600"></div>
            <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full opacity-10 bg-primary-600"></div>
            <div class="absolute top-1/3 right-1/4 w-64 h-64 rounded-full opacity-5 bg-primary-600"></div>
        </div>

        <div class="relative w-full max-w-md mx-4">
            <div class="card rounded-2xl p-8 shadow-2xl">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-600 mb-4 shadow-lg">
                        <i class="fa-solid fa-leaf text-white text-2xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-primary-900">FEEDTAN DIGITAL</h1>
                    <p class="text-sm mt-1 text-primary-600">Payment System</p>
                </div>

                <form id="loginForm" method="POST" action="{{ route('login.attempt', ['entryToken' => request()->route('entryToken')]) }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-2 text-primary-700">Email Address</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-sm text-primary-500"></i>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="you@example.com"
                                   class="form-input pl-9 bg-gray-50 border-primary-200 text-primary-900">
                        </div>
                        @error('email')
                            <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold mb-2 text-primary-700">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-sm text-primary-500"></i>
                            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                                   class="form-input pl-9 bg-gray-50 border-primary-200 text-primary-900">
                        </div>
                        @error('password')
                            <p class="mt-2 text-xs font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2">
                            <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="w-4 h-4 rounded border-primary-200 bg-primary-50 text-primary-600 focus:ring-primary-500">
                            <label for="remember" class="text-xs text-primary-600 font-medium cursor-pointer">Remember Me</label>
                        </div>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" id="submitBtn" class="w-full py-3 rounded-xl bg-primary-600 hover:bg-primary-500 text-white font-semibold text-sm transition-all duration-200 hover:shadow-lg hover:shadow-primary-900/20 active:scale-95">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i> Sign In
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================
         LOADING SCREEN
         ============================================================ -->
    <div id="loadingScreen" class="fixed inset-0 z-[60] flex items-center justify-center hidden-element">
        <div class="text-center w-full max-w-md px-4">
            <!-- Spinner -->
            <div class="mb-6">
                <div class="w-16 h-16 border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin mx-auto"></div>
            </div>
            <!-- Step messages -->
            <div class="space-y-1 mb-6">
                <p class="text-lg font-bold text-primary-900" id="currentStep">Validating...</p>
                <p class="text-sm text-primary-600" id="currentStepDescription">Checking your credentials</p>
            </div>
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                <div id="progressBar" class="bg-primary-600 h-2.5 rounded-full transition-all duration-75" style="width: 0%"></div>
            </div>
            <p class="text-xs text-primary-500 mt-2" id="progressText">0%</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginScreen = document.getElementById('loginScreen');
            const loadingScreen = document.getElementById('loadingScreen');
            const currentStepEl = document.getElementById('currentStep');
            const currentStepDescriptionEl = document.getElementById('currentStepDescription');
            const submitBtn = document.getElementById('submitBtn');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            
            const steps = [
                { title: 'Authenticating', description: "We're validating your identity, please wait...", delay: 400 },
                { title: 'Authorizing...', description: 'Verifying your account', delay: 400 }
            ];

            // Auto-logout cookie check
            const cookies = document.cookie.split(';');
            let autoLogout = false;
            
            cookies.forEach(cookie => {
                const [name, value] = cookie.trim().split('=');
                if (name === 'auto_logout' && value === 'true') {
                    autoLogout = true;
                }
            });
            
            if (autoLogout) {
                const toast = document.getElementById('auto-logout-toast');
                toast.classList.remove('hidden-element');
            }
            
            @if($errors->any())
                const errorToast = document.getElementById('error-toast');
                const errorTitle = document.getElementById('error-toast-title');
                const errorMessage = document.getElementById('error-toast-message');
                
                errorTitle.textContent = 'Login Failed';
                @php
                    $firstError = $errors->first();
                @endphp
                errorMessage.textContent = '{{ addslashes($firstError) }}';
                errorToast.classList.remove('hidden-element');
                
                setTimeout(() => {
                    errorToast.classList.add('hidden-element');
                }, 5000);
            @endif
            
            @if(session('success'))
                const successToast = document.getElementById('success-toast');
                const successMessage = document.getElementById('success-toast-message');
                
                successMessage.textContent = '{{ addslashes(session('success')) }}';
                successToast.classList.remove('hidden-element');
                
                setTimeout(() => {
                    successToast.classList.add('hidden-element');
                }, 5000);
            @endif

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                
                document.cookie = "auto_logout=; path=/; max-age=-1";
                document.getElementById('auto-logout-toast').classList.add('hidden-element');
                document.getElementById('error-toast').classList.add('hidden-element');
                
                if (!emailInput.value.trim() || !passwordInput.value.trim()) {
                    const errorToast = document.getElementById('error-toast');
                    const errorTitle = document.getElementById('error-toast-title');
                    const errorMessage = document.getElementById('error-toast-message');
                    
                    errorTitle.textContent = 'Validation Error';
                    errorMessage.textContent = 'Please enter both email and password.';
                    errorToast.classList.remove('hidden-element');
                    
                    setTimeout(() => {
                        errorToast.classList.add('hidden-element');
                    }, 5000);
                    
                    return;
                }
                
                submitBtn.disabled = true;
                loginScreen.classList.add('hidden-element');
                loadingScreen.classList.remove('hidden-element');
                
                // Animate progress bar from 0-100%
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += 2;
                    if (progress > 100) progress = 100;
                    progressBar.style.width = progress + '%';
                    progressText.textContent = progress + '%';
                    if (progress >= 100) clearInterval(progressInterval);
                }, 8);
                
                for (let i = 0; i < steps.length; i++) {
                    currentStepEl.textContent = steps[i].title;
                    currentStepDescriptionEl.textContent = steps[i].description;
                    await new Promise(resolve => setTimeout(resolve, steps[i].delay));
                }
                
                form.submit();
            });
        });
    </script>
</body>
</html>
