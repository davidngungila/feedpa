<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FEEDTAN DIGITAL') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet"/>

    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              primary: { 50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b',950:'#022c22' },
              dark: { 800:'#0f1a14',850:'#111f17',900:'#0a140e',card:'#0d1f16',border:'#1a3328' }
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

    /* Fix Pagination Styling */
    .pagination { display: flex; list-style: none; padding: 0; gap: 0.5rem; align-items: center; }
    .page-item .page-link { 
        display: flex; align-items: center; justify-content: center;
        min-width: 2.5rem; height: 2.5rem; padding: 0.5rem;
        border-radius: 0.75rem; border: 1px solid #d1fae5;
        background: white; color: #065f46; font-weight: 600; font-size: 0.875rem;
        transition: all 0.2s;
    }
    .dark .page-item .page-link { background: #0d1f16; border-color: #1a3328; color: #6ee7b7; }
    .page-item.active .page-link { background: #10b981; color: white; border-color: #10b981; shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
    .page-item.disabled .page-link { opacity: 0.5; cursor: not-allowed; }
    .page-item:hover:not(.active):not(.disabled) .page-link { background: #f0fdf4; border-color: #10b981; }
    .dark .page-item:hover:not(.active):not(.disabled) .page-link { background: #052e16; }
    .pagination svg { width: 1.25rem; height: 1.25rem; }

    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #10b981; border-radius: 10px; }

    .sidebar-bg { background: #064e3b; }
    .navbar-bg { background: #ffffff; border-bottom: 1px solid #d1fae5; }
    .main-bg { background: #f0fdf4; }

    .dark .main-bg { background: #0a140e; }
    .dark .navbar-bg { background: #0d1f16; border-bottom: 1px solid #1a3328; }
    .dark .sidebar-bg { background: #022c22; }

    .card { background: #ffffff; border: 1px solid #d1fae5; box-shadow: 0 2px 12px rgba(6,78,59,0.08); border-radius: 1rem; }
    .dark .card { background: #0d1f16; border: 1px solid #1a3328; box-shadow: 0 2px 12px rgba(0,0,0,0.4); }

    .badge { display:inline-flex; align-items:center; padding:2px 10px; border-radius:999px; font-size:11px; font-weight:600; }
    .badge-green { background:#d1fae5; color:#065f46; }
    .badge-red { background:#fee2e2; color:#991b1b; }
    .badge-yellow { background:#fef9c3; color:#854d0e; }

    .dark .badge-green { background:#052e16; color:#6ee7b7; }
    .dark .badge-red { background:#450a0a; color:#fca5a5; }
    .dark .badge-yellow { background:#422006; color:#fde68a; }

    .data-table { width:100%; border-collapse:collapse; }
    .data-table th { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; padding:12px 14px; text-align: left; }
    .data-table td { padding:12px 14px; font-size:13px; }
    .light-mode .data-table th { color:#065f46; background:#ecfdf5; }
    .dark .data-table th { color:#6ee7b7; background:#052e16; }
    
    .sidebar { transition: width 0.3s ease; }
    @media(max-width:1024px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.mobile-open { transform: translateX(0); }
    }
    </style>
    
    @stack('styles')
</head>
<body class="h-full main-bg" x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{'dark': darkMode}">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" 
               class="sidebar sidebar-bg fixed lg:relative w-[260px] h-screen z-50 flex flex-col transition-transform duration-300">
            
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between p-4 border-b border-primary-800/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-primary-400 flex items-center justify-center">
                        <i class="fa-solid fa-leaf text-primary-900 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-white font-bold text-sm leading-tight">FEEDTAN</p>
                        <p class="text-primary-300 text-[10px]">DIGITAL</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <a href="{{ route('payments.history') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('payments.history') ? 'bg-primary-600 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                    <i class="fa-solid fa-history w-4 text-center"></i>
                    <span>Payment History</span>
                </a>
                <a href="{{ route('account.statement') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('account.statement') ? 'bg-primary-600 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                    <i class="fa-solid fa-file-invoice w-4 text-center"></i>
                    <span>Account Statement</span>
                </a>
                <a href="{{ route('payments.create') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('payments.create') ? 'bg-primary-600 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                    <i class="fa-solid fa-plus-circle w-4 text-center"></i>
                    <span>New Payment</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-primary-800/50">
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                        class="w-full flex items-center gap-3 px-3 py-2 text-primary-300 hover:text-white transition-colors">
                    <i :class="darkMode ? 'fa-solid fa-sun' : 'fa-solid fa-moon'" class="w-4 text-center"></i>
                    <span x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="navbar-bg flex items-center justify-between px-6 h-16 flex-shrink-0">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/30">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h2 class="text-lg font-bold text-primary-900 dark:text-white">
                        @yield('title', 'Dashboard')
                    </h2>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex flex-col text-right">
                        <p class="text-xs font-bold text-primary-900 dark:text-white">{{ auth()->user()->name ?? 'Administrator' }}</p>
                        <p class="text-[10px] text-primary-500">{{ auth()->user()->email ?? 'admin@feedtan.co.tz' }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-primary-600 flex items-center justify-center text-white font-bold">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto p-6">
                @include('layouts.flash')
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>