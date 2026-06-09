<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | FEEDTAN DIGITAL PAYMENT SYSTEM</title>
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
      theme: {
        extend: {
          colors: {
            primary: { 50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b',950:'#022c22' }
          },
          fontFamily: { sans:['Plus Jakarta Sans','sans-serif'] },
          animation: { 'fade-in':'fadeIn 0.4s ease','slide-in':'slideIn 0.3s ease','count-up':'countUp 1.5s ease','pulse-slow':'pulse 3s infinite' },
          keyframes: { fadeIn:{from:{opacity:0,transform:'translateY(10px)'},to:{opacity:1,transform:'translateY(0)'}}, slideIn:{from:{opacity:0,transform:'translateX(-20px)'},to:{opacity:1,transform:'translateX(0)'}} }
        }
      }
    }
  </script>

  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }

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

    @keyframes countUp {
      from { opacity:0; transform: translateY(10px); }
      to { opacity:1; transform: translateY(0); }
    }
    
    @keyframes moveRightLeft {
      0%, 100% { transform: translateX(0); }
      50% { transform: translateX(-20px); }
    }
  </style>
</head>
<body class="h-full bg-[#f0fdf4]" x-data="loginApp()">

  <!-- Auto Logout Toast Notification -->
  <div id="auto-logout-toast" class="fixed top-4 right-4 z-[9999] hidden flex items-center gap-4 px-6 py-4 rounded-xl shadow-2xl bg-yellow-100 border border-yellow-400" style="animation: moveRightLeft 4s ease-in-out infinite;">
    <div class="flex-shrink-0">
      <i class="fa-solid fa-clock text-2xl text-yellow-600"></i>
    </div>
    <div class="flex-1">
      <p class="font-bold text-base text-yellow-800">Session Expired</p>
      <p class="text-sm text-yellow-700">You were logged out automatically due to inactivity. Please login to continue.</p>
    </div>
  </div>

  <!-- Error Toast Notification -->
  <div id="error-toast" class="fixed top-4 right-4 z-[9999] hidden flex items-center gap-4 px-6 py-4 rounded-xl shadow-2xl bg-red-100 border border-red-400">
    <div class="flex-shrink-0">
      <i class="fa-solid fa-exclamation-circle text-2xl text-red-600"></i>
    </div>
    <div class="flex-1">
      <p class="font-bold text-base text-red-800" id="error-toast-title">Error</p>
      <p class="text-sm text-red-700" id="error-toast-message"></p>
    </div>
  </div>
  
  <!-- Success Toast Notification -->
  <div id="success-toast" class="fixed top-4 right-4 z-[9999] hidden flex items-center gap-4 px-6 py-4 rounded-xl shadow-2xl bg-green-100 border border-green-400">
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
  <div x-show="!showSplash" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700">
    <!-- Background decorations -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full opacity-10 bg-white"></div>
      <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full opacity-10 bg-white"></div>
      <div class="absolute top-1/3 right-1/4 w-64 h-64 rounded-full opacity-5 bg-white"></div>
    </div>

    <div class="relative w-full max-w-md mx-4">
      <div class="card rounded-2xl p-8 shadow-2xl bg-white">
        <!-- Logo -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-600 mb-4 shadow-lg">
            <i class="fa-solid fa-leaf text-white text-2xl"></i>
          </div>
          <h1 class="text-2xl font-bold text-primary-900">FEEDTAN DIGITAL</h1>
          <p class="text-sm mt-1 text-primary-600">Payment System</p>
        </div>

        <form id="loginForm" method="POST" action="{{ route('login') }}" @submit.prevent="submitLogin()">
          @csrf

          <!-- Email -->
          <div class="mb-4">
            <label class="block text-sm font-semibold mb-2 text-primary-700">Email Address</label>
            <div class="relative">
              <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-sm text-primary-500"></i>
              <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="you@example.com"
                     class="form-input pl-9 bg-gray-50 border-primary-200 text-primary-900"/>
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
                     class="form-input pl-9 bg-gray-50 border-primary-200 text-primary-900"/>
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
                        <div>
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-500 transition-colors">
                                Forgot Password?
                            </a>
                        </div>
                    </div>

          <!-- Login Button -->
          <button type="submit" class="w-full py-3 rounded-xl bg-primary-600 hover:bg-primary-500 text-white font-semibold text-sm transition-all duration-200 hover:shadow-lg hover:shadow-primary-900/30 active:scale-95">
            <i class="fa-solid fa-right-to-bracket mr-2"></i> Sign In
          </button>
        </form>

      </div>
    </div>
  </div>

  <!-- ============================================================
       LOADING SCREEN
       ============================================================ -->
  <div x-show="showSplash" x-transition class="fixed inset-0 z-[60] flex items-center justify-center bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700">
    <div class="text-center">
      <!-- Spinner -->
      <div class="mb-6">
        <div class="w-16 h-16 border-4 border-white/30 border-t-white rounded-full animate-spin mx-auto"></div>
      </div>
      <!-- Step messages -->
      <div class="space-y-1">
        <p class="text-lg font-bold text-white" x-text="currentStep"></p>
        <p class="text-sm text-primary-200" x-text="currentStepDescription"></p>
      </div>
    </div>
  </div>

  <script>
    function loginApp() {
      return {
        showSplash: false,
        currentStep: 'Validating...',
        currentStepDescription: 'Checking your credentials',
        steps: [
          { title: 'Validating...', description: 'Checking your credentials', delay: 400 },
          { title: 'Authorizing...', description: 'Verifying your account', delay: 400 }
        ],
        async submitLogin() {
          const form = document.getElementById('loginForm');
          const emailInput = document.getElementById('email');
          const passwordInput = document.getElementById('password');
          
          document.cookie = "auto_logout=; path=/; max-age=-1";
          document.getElementById('auto-logout-toast').classList.add('hidden');
          document.getElementById('error-toast').classList.add('hidden');
          
          if (!emailInput.value.trim() || !passwordInput.value.trim()) {
            const errorToast = document.getElementById('error-toast');
            const errorTitle = document.getElementById('error-toast-title');
            const errorMessage = document.getElementById('error-toast-message');
            
            errorTitle.textContent = 'Validation Error';
            errorMessage.textContent = 'Please enter both email and password.';
            errorToast.classList.remove('hidden');
            
            setTimeout(() => {
              errorToast.classList.add('hidden');
            }, 5000);
            
            return;
          }
          
          this.showSplash = true;
          
          for (let i = 0; i < this.steps.length; i++) {
            this.currentStep = this.steps[i].title;
            this.currentStepDescription = this.steps[i].description;
            await new Promise(resolve => setTimeout(resolve, this.steps[i].delay));
          }
          
          form.submit();
        }
      }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
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
        toast.classList.remove('hidden');
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
        errorToast.classList.remove('hidden');
        
        setTimeout(() => {
          errorToast.classList.add('hidden');
        }, 5000);
      @endif
      
      @if(session('success'))
        const successToast = document.getElementById('success-toast');
        const successMessage = document.getElementById('success-toast-message');
        
        successMessage.textContent = '{{ addslashes(session('success')) }}';
        successToast.classList.remove('hidden');
        
        setTimeout(() => {
          successToast.classList.add('hidden');
        }, 5000);
      @endif
    });
  </script>
</body>
</html>
