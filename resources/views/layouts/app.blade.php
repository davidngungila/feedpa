<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'FEEDTAN DIGITAL') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    
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
            animation: { 'fade-in':'fadeIn 0.4s ease','slide-in':'slideIn 0.3s ease','spin-slow':'spin 1.5s linear infinite','bounce-slow':'bounce 1.5s infinite' },
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
    .page-item.active .page-link { background: #10b981; color: white; border-color: #10b981; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
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
    
    /* Dropdown menus in sidebar */
    .sidebar-dropdown { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
    .sidebar-dropdown.open { max-height: 500px; }
    
    /* AI Floating Button */
    .ai-button {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 50;
        width: 3.5rem;
        height: 3.5rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 50%;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #065f46;
    }
    .ai-button:hover {
        transform: scale(1.1);
        box-shadow: 0 12px 30px rgba(16, 185, 129, 0.6);
    }
    
    .ai-face {
        position: relative;
        width: 2.25rem;
        height: 2.25rem;
    }
    
    .ai-eye {
        position: absolute;
        top: 0.625rem;
        width: 0.5625rem;
        height: 0.5625rem;
        background: white;
        border-radius: 50%;
        overflow: hidden;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.2);
    }
    .ai-eye.left {
        left: 0.25rem;
    }
    .ai-eye.right {
        right: 0.25rem;
    }
    
    .ai-pupil {
        position: absolute;
        top: 0.125rem;
        left: 0.125rem;
        width: 0.3125rem;
        height: 0.3125rem;
        background: #064e3b;
        border-radius: 50%;
        transition: transform 0.1s ease;
    }
    
    .ai-mouth {
        position: absolute;
        bottom: 0.375rem;
        left: 50%;
        transform: translateX(-50%);
        width: 0.9375rem;
        height: 0.46875rem;
        border: 2px solid white;
        border-top: none;
        border-radius: 0 0 999px 999px;
    }
    </style>
    
    @stack('styles')
</head>
<body class="h-full main-bg" x-data="{ sidebarOpen: false, openDropdowns: [], profileDropdownOpen: false, notificationDropdownOpen: false, isLoading: true }" @click="($el.tagName === 'A' && $el.href && !$el.href.includes('#')) || ($el.tagName === 'BUTTON' && ($el.closest('form') || $el.getAttribute('type') === 'submit')) ? (isLoading = true) : null" x-init="setTimeout(() => { isLoading = false }, 300);">
    
    <!-- Loading Overlay -->
    <div x-show="isLoading"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[9999] bg-primary-50/80 backdrop-blur-sm flex items-center justify-center">
        <div class="text-center space-y-4">
            <div class="flex items-center justify-center gap-2">
                <div class="w-4 h-4 rounded-full bg-primary-500 animate-bounce-slow" style="animation-delay: 0s;"></div>
                <div class="w-4 h-4 rounded-full bg-primary-500 animate-bounce-slow" style="animation-delay: 0.1s;"></div>
                <div class="w-4 h-4 rounded-full bg-primary-500 animate-bounce-slow" style="animation-delay: 0.2s;"></div>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 rounded-full border-4 border-primary-200 border-t-primary-500 animate-spin-slow"></div>
            </div>
            <p class="text-sm font-semibold text-primary-700">Loading...</p>
        </div>
    </div>
    
    <div class="flex h-screen overflow-hidden">
        <!-- Overlay to close sidebar -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>
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
                        <p class="text-primary-300 text-[10px]">DIGITAL PAYMENT SYSTEM</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <!-- Dashboard -->
                <a href="{{ route('dashboard.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('dashboard.*') ? 'bg-primary-600 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                    <i class="fa-solid fa-gauge-high w-4 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <!-- AI Chat -->
                <a href="{{ route('dashboard.ai-chat.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('dashboard.ai-chat.*') ? 'bg-primary-600 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                    <i class="fa-solid fa-robot w-4 text-center"></i>
                    <span>AI Chat</span>
                </a>

                <!-- Payments Dropdown -->
                <div class="space-y-0.5">
                    <button @click="openDropdowns.includes('payments') ? openDropdowns = openDropdowns.filter(d => d !== 'payments') : openDropdowns.push('payments')"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('payments.*') ? 'bg-primary-800/60 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-credit-card w-4 text-center"></i>
                            <span>Payments</span>
                        </div>
                        <i :class="openDropdowns.includes('payments') ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-[10px] text-primary-400"></i>
                    </button>
                    <div :class="openDropdowns.includes('payments') ? 'sidebar-dropdown open' : 'sidebar-dropdown'" class="ml-3 space-y-0.5">
                        <a href="{{ route('payments.create') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('payments.create') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>New Payment</span>
                        </a>
                        <a href="{{ route('payments.history') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('payments.history') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Payment History</span>
                        </a>
                    </div>
                </div>

                <!-- Payouts Dropdown -->
                <div class="space-y-0.5">
                    <button @click="openDropdowns.includes('payouts') ? openDropdowns = openDropdowns.filter(d => d !== 'payouts') : openDropdowns.push('payouts')"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('payouts.*') ? 'bg-primary-800/60 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-wallet w-4 text-center"></i>
                            <span>Payouts</span>
                        </div>
                        <i :class="openDropdowns.includes('payouts') ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-[10px] text-primary-400"></i>
                    </button>
                    <div :class="openDropdowns.includes('payouts') ? 'sidebar-dropdown open' : 'sidebar-dropdown'" class="ml-3 space-y-0.5">
                        @if(auth()->check() && auth()->user()->can_create_payouts)
                        <a href="{{ route('payouts.create') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('payouts.create') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>New Payout</span>
                        </a>
                        @endif
                        <a href="{{ route('payouts.index') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('payouts.index') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Payout History</span>
                        </a>
                    </div>
                </div>

                <!-- Beneficiaries -->
                <a href="{{ route('beneficiaries.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('beneficiaries.*') ? 'bg-primary-600 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                    <i class="fa-solid fa-address-book w-4 text-center"></i>
                    <span>Beneficiaries</span>
                </a>

                <!-- Bill Management Dropdown -->
                <div class="space-y-0.5">
                    <button @click="openDropdowns.includes('bills') ? openDropdowns = openDropdowns.filter(d => d !== 'bills') : openDropdowns.push('bills')"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('bills.*') ? 'bg-primary-800/60 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-file-invoice-dollar w-4 text-center"></i>
                            <span>Bill Management</span>
                        </div>
                        <i :class="openDropdowns.includes('bills') ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-[10px] text-primary-400"></i>
                    </button>
                    <div :class="openDropdowns.includes('bills') ? 'sidebar-dropdown open' : 'sidebar-dropdown'" class="ml-3 space-y-0.5">
                        <a href="{{ route('bills.index') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('bills.index') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>All Bills</span>
                        </a>
                        <a href="{{ route('bills.create-order') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('bills.create-order') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Create Order Control No</span>
                        </a>
                        <a href="{{ route('bills.create-customer') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('bills.create-customer') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Create Customer Control No</span>
                        </a>
                    </div>
                </div>

                <!-- Account Dropdown -->
                <div class="space-y-0.5">
                    <button @click="openDropdowns.includes('account') ? openDropdowns = openDropdowns.filter(d => d !== 'account') : openDropdowns.push('account')"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('account.*') ? 'bg-primary-800/60 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-file-invoice w-4 text-center"></i>
                            <span>Account</span>
                        </div>
                        <i :class="openDropdowns.includes('account') ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-[10px] text-primary-400"></i>
                    </button>
                    <div :class="openDropdowns.includes('account') ? 'sidebar-dropdown open' : 'sidebar-dropdown'" class="ml-3 space-y-0.5">
                        <a href="{{ route('account.statement') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('account.statement') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Account Statement</span>
                        </a>
                    </div>
                </div>

                <!-- Financial Reports Dropdown -->
                <div class="space-y-0.5">
                    <button @click="openDropdowns.includes('reports') ? openDropdowns = openDropdowns.filter(d => d !== 'reports') : openDropdowns.push('reports')"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('reports.*') ? 'bg-primary-800/60 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-chart-bar w-4 text-center"></i>
                            <span>Financial Reports</span>
                        </div>
                        <i :class="openDropdowns.includes('reports') ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-[10px] text-primary-400"></i>
                    </button>
                    <div :class="openDropdowns.includes('reports') ? 'sidebar-dropdown open' : 'sidebar-dropdown'" class="ml-3 space-y-0.5">
                        <a href="{{ route('reports.trial-balance') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('reports.trial-balance') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Trial Balance</span>
                        </a>
                        <a href="{{ route('reports.balance-sheet') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('reports.balance-sheet') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Balance Sheet</span>
                        </a>
                        <a href="{{ route('reports.profit-loss') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('reports.profit-loss') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Profit & Loss</span>
                        </a>
                        <a href="{{ route('reports.customer-report') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('reports.customer-report') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Customer Report</span>
                        </a>
                    </div>
                </div>

                @if(auth()->check() && auth()->user()->is_admin)
                <!-- Users Management -->
                <div class="space-y-0.5">
                    <button @click="openDropdowns.includes('users') ? openDropdowns = openDropdowns.filter(d => d !== 'users') : openDropdowns.push('users')"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('users.*') ? 'bg-primary-800/60 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-users w-4 text-center"></i>
                            <span>Users</span>
                        </div>
                        <i :class="openDropdowns.includes('users') ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-[10px] text-primary-400"></i>
                    </button>
                    <div :class="openDropdowns.includes('users') ? 'sidebar-dropdown open' : 'sidebar-dropdown'" class="ml-3 space-y-0.5">
                        <a href="{{ route('users.index') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('users.index') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>All Users</span>
                        </a>
                        <a href="{{ route('users.create') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('users.create') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Add User</span>
                        </a>
                        <a href="{{ route('audits.index') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('audits.index') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Audit Logs</span>
                        </a>
                    </div>
                </div>

                <!-- System Settings -->
                <div class="space-y-0.5">
                    <button @click="openDropdowns.includes('settings') ? openDropdowns = openDropdowns.filter(d => d !== 'settings') : openDropdowns.push('settings')"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('settings.*') ? 'bg-primary-800/60 text-white' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-cog w-4 text-center"></i>
                            <span>System Settings</span>
                        </div>
                        <i :class="openDropdowns.includes('settings') ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="text-[10px] text-primary-400"></i>
                    </button>
                    <div :class="openDropdowns.includes('settings') ? 'sidebar-dropdown open' : 'sidebar-dropdown'" class="ml-3 space-y-0.5">
                        <a href="{{ route('settings.sms') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('settings.sms') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>SMS Settings</span>
                        </a>
                        <a href="{{ route('settings.email') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('settings.email') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>Email Settings</span>
                        </a>
                        <a href="{{ route('settings.general') }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-all {{ request()->routeIs('settings.general') ? 'bg-primary-600 text-white' : 'text-primary-300 hover:bg-primary-800/30 hover:text-white' }}">
                            <i class="fa-solid fa-circle text-[6px] ml-1"></i>
                            <span>General Settings</span>
                        </a>
                    </div>
                </div>
                @endif
            </nav>

            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-primary-800/50">
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="navbar-bg flex items-center justify-between px-6 h-16 flex-shrink-0">
                @php
                    $headerNotifications = auth()->user()
                        ->appNotifications()
                        ->take(8)
                        ->get();
                    $unreadNotificationCount = auth()->user()
                        ->appNotifications()
                        ->where('is_read', false)
                        ->count();
                @endphp
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-primary-600 hover:bg-primary-50">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h2 class="text-lg font-bold text-primary-900">
                        @yield('title', 'Dashboard')
                    </h2>
                </div>
                
                <!-- Connection Status -->
                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-50">
                        <div class="relative">
                            @if(cache()->get('api_status', 'connected') === 'connected')
                                <div class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></div>
                                <div class="w-3 h-3 rounded-full bg-green-400 absolute top-0 left-0 animate-ping"></div>
                            @else
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            @endif
                        </div>
                        <span class="text-xs font-bold text-primary-600">
                            {{ cache()->get('api_status', 'connected') === 'connected' ? 'API Connected' : 'API Disconnected' }}
                        </span>
                    </div>

                <div class="relative">
                    <button @click="notificationDropdownOpen = !notificationDropdownOpen; if (notificationDropdownOpen) profileDropdownOpen = false"
                            class="relative flex items-center justify-center w-11 h-11 rounded-xl border border-primary-100 bg-white hover:bg-primary-50 transition-all text-primary-600">
                        <i class="fas fa-bell text-sm"></i>
                        @if($unreadNotificationCount > 0)
                            <span class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                            </span>
                        @endif
                    </button>

                    <div x-show="notificationDropdownOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         @click.outside="notificationDropdownOpen = false"
                         class="absolute right-0 mt-2 w-[360px] max-w-[90vw] z-50 rounded-xl shadow-xl bg-white border border-primary-100 overflow-hidden">
                        <div class="p-4 border-b border-primary-100 bg-primary-50 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-primary-900">Notifications</p>
                                <p class="text-[10px] text-primary-500">{{ $unreadNotificationCount }} unread</p>
                            </div>
                            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                @csrf
                                <button type="submit" class="text-[10px] font-bold text-primary-600 hover:underline">
                                    Mark all read
                                </button>
                            </form>
                        </div>

                        <div class="max-h-[420px] overflow-y-auto divide-y divide-primary-50">
                            @forelse($headerNotifications as $notification)
                                <a href="{{ route('notifications.open', $notification) }}"
                                   class="block px-4 py-3 hover:bg-primary-50 transition-all {{ $notification->is_read ? 'bg-white' : 'bg-primary-50/50' }}">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 w-2.5 h-2.5 rounded-full {{ $notification->is_read ? 'bg-primary-200' : 'bg-primary-500' }}"></div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-xs font-bold text-primary-900 truncate">{{ $notification->title }}</p>
                                                <span class="text-[10px] text-primary-400 whitespace-nowrap">{{ $notification->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-xs text-primary-600 mt-1 line-clamp-3">{{ $notification->message }}</p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <i class="fas fa-bell-slash text-2xl text-primary-200"></i>
                                    <p class="mt-3 text-sm font-bold text-primary-700">No notifications yet</p>
                                    <p class="text-xs text-primary-500">New payments and payout actions will appear here.</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="p-3 border-t border-primary-100 bg-white">
                            <a href="{{ route('notifications.index') }}"
                               class="flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition-all">
                                <i class="fas fa-list"></i>
                                <span>View All Notifications</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <button @click="profileDropdownOpen = !profileDropdownOpen; if (profileDropdownOpen) notificationDropdownOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-primary-50 transition-all">
                        <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center overflow-hidden border-2 border-green-500">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-primary-600 font-bold">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="hidden sm:flex flex-col text-left">
                            <p class="text-xs font-bold text-primary-900">{{ auth()->user()->name ?? 'Administrator' }}</p>
                            <p class="text-[10px] text-primary-500">{{ auth()->user()->position ?? 'Member' }}</p>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-primary-400"></i>
                    </button>

                    <!-- Profile Dropdown -->
                    <div x-show="profileDropdownOpen"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         @click.outside="profileDropdownOpen = false"
                         class="absolute right-0 mt-2 w-64 z-50 rounded-xl shadow-xl bg-white border border-primary-100 overflow-hidden">
                        
                        <div class="p-4 bg-primary-50 border-b border-primary-100">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center overflow-hidden border-2 border-green-500">
                                    @if(auth()->user()->avatar)
                                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-primary-600 font-bold text-lg">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-primary-900 truncate">{{ auth()->user()->name ?? 'Administrator' }}</p>
                                    <p class="text-xs text-primary-500 truncate">{{ auth()->user()->email ?? 'admin@feedtancmg.org' }}</p>
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700">
                                        {{ auth()->user()->position ?? 'Member' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="py-2">
                            <a href="{{ route('profile.index') }}" 
                               class="flex items-center gap-3 px-4 py-2.5 text-xs text-primary-700 hover:bg-primary-50 transition-all">
                                <i class="fas fa-id-card w-4"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" 
                               class="flex items-center gap-3 px-4 py-2.5 text-xs text-primary-700 hover:bg-primary-50 transition-all">
                                <i class="fas fa-cog w-4"></i>
                                <span>Settings</span>
                            </a>
                        </div>

                        <div class="border-t border-primary-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-3 px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 transition-all">
                                    <i class="fas fa-sign-out-alt w-4"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
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

    <!-- Session Timeout Script -->
    <script>
        @if(auth()->check())
            let sessionTimeout = {{ \App\Models\SystemSetting::get('session_timeout', 120) * 60 * 1000 }}; // convert to ms
            let warningTime = sessionTimeout / 3; // 1/3 of time
            let lastActivity = Date.now();

            function resetTimer() {
                lastActivity = Date.now();
            }

            function autoLogout() {
                // Show loading screen immediately
                const bodyEl = document.body;
                if (bodyEl.__x) {
                    bodyEl.__x.$data.isLoading = true;
                }
                
                // Set a cookie to remember we logged out due to inactivity
                document.cookie = "auto_logout=true; path=/; max-age=" + (60 * 5) + "; SameSite=Lax";
                
                // Create form and submit POST request for logout immediately
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('logout') }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }

            // Listen for user activity to reset timer
            document.addEventListener('mousemove', resetTimer);
            document.addEventListener('keypress', resetTimer);
            document.addEventListener('click', resetTimer);
            document.addEventListener('scroll', resetTimer);

            // Check every second
            setInterval(() => {
                const now = Date.now();
                const timeSinceActivity = now - lastActivity;
                
                if (timeSinceActivity >= sessionTimeout) {
                    // Auto logout
                    autoLogout();
                } else if (timeSinceActivity >= warningTime && timeSinceActivity < warningTime + 1000) {
                    // Show warning
                    const warning = document.createElement('div');
                    warning.className = 'fixed top-4 right-4 z-50 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded-xl shadow-lg animate-pulse';
                    warning.innerHTML = `
                        <div class="flex items-center gap-3">
                            <i class="fas fa-exclamation-triangle text-2xl"></i>
                            <div>
                                <p class="font-bold">Session Warning!</p>
                                <p class="text-sm">Your session will expire soon. Move your mouse or press any key to stay logged in.</p>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(warning);
                    
                    // Remove warning after 5 seconds or on activity
                    setTimeout(() => warning.remove(), 5000);
                    document.addEventListener('mousemove', () => warning.remove(), { once: true });
                    document.addEventListener('keypress', () => warning.remove(), { once: true });
                }
            }, 1000);
        @endif
    </script>

    <!-- AI Chat Functionality -->
    <script>
        let chatHistory = [];
        
        function openChat() {
            document.getElementById('chatModal').classList.remove('hidden');
            document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
        }
        
        function closeChat() {
            document.getElementById('chatModal').classList.add('hidden');
        }
        
        async function sendMessage() {
            const messageInput = document.getElementById('chatInput');
            const message = messageInput.value.trim();
            if (!message) return;
            
            messageInput.value = '';
            
            // Add user message to UI
            addMessageToChat('user', message);
            chatHistory.push({role: 'user', text: message});
            
            // Show loading indicator
            const loadingMessage = addLoadingMessage();
            
            try {
                const response = await fetch('{{ route('dashboard.ai-chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        message: message,
                        history: chatHistory
                    })
                });
                
                const data = await response.json();
                
                // Remove loading indicator
                loadingMessage.remove();
                
                if (data.success) {
                    addMessageToChat('model', data.response);
                    chatHistory.push({role: 'model', text: data.response});
                } else {
                    let errorMsg = 'Error: ' + (data.message || 'Something went wrong');
                    if (data.error) {
                        try {
                            const errorObj = JSON.parse(data.error);
                            if (errorObj.error && errorObj.error.message) {
                                errorMsg = 'Error: ' + errorObj.error.message;
                            }
                        } catch (e) {
                            errorMsg = 'Error: ' + data.message;
                        }
                    }
                    addMessageToChat('model', errorMsg);
                }
            } catch (error) {
                loadingMessage.remove();
                addMessageToChat('model', 'Error: ' + error.message);
            }
        }
        
        function addMessageToChat(role, text) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'p-4 rounded-lg mb-3 ' + 
                (role === 'user' 
                    ? 'bg-green-100 text-gray-800 ml-auto max-w-[80%]' 
                    : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white max-w-[80%]');
            
            const formattedText = text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
            
            messageDiv.innerHTML = formattedText;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            return messageDiv;
        }
        
        function addLoadingMessage() {
            const chatMessages = document.getElementById('chatMessages');
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'p-4 rounded-lg mb-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white max-w-[80%]';
            loadingDiv.innerHTML = '<div class="flex space-x-1"><span class="w-2 h-2 bg-green-600 rounded-full animate-bounce" style="animation-delay: 0s;"></span><span class="w-2 h-2 bg-green-600 rounded-full animate-bounce" style="animation-delay: 0.2s;"></span><span class="w-2 h-2 bg-green-600 rounded-full animate-bounce" style="animation-delay: 0.4s;"></span></div>';
            chatMessages.appendChild(loadingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            return loadingDiv;
        }
        
        // Handle enter key on chat input
        document.addEventListener('DOMContentLoaded', function() {
            const chatInput = document.getElementById('chatInput');
            if (chatInput) {
                chatInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        sendMessage();
                    }
                });
            }
        });
    </script>

    <!-- Floating AI Chat Button with Eyes -->
    <button 
        onclick="openChat()"
        class="ai-button"
        id="aiButton">
        <div class="ai-face">
            <div class="ai-eye left">
                <div class="ai-pupil" id="leftPupil"></div>
            </div>
            <div class="ai-eye right">
                <div class="ai-pupil" id="rightPupil"></div>
            </div>
            <div class="ai-mouth"></div>
        </div>
    </button>
    
    <!-- Eye Tracking Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const leftPupil = document.getElementById('leftPupil');
            const rightPupil = document.getElementById('rightPupil');
            const button = document.getElementById('aiButton');
            
            if (!leftPupil || !rightPupil || !button) return;
            
            document.addEventListener('mousemove', function(e) {
                const rect = button.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                
                const angle = Math.atan2(e.clientY - centerY, e.clientX - centerX);
                const distance = Math.min(2, Math.hypot(e.clientX - centerX, e.clientY - centerY) / 60);
                
                const offsetX = Math.cos(angle) * distance;
                const offsetY = Math.sin(angle) * distance;
                
                leftPupil.style.transform = `translate(${offsetX}px, ${offsetY}px)`;
                rightPupil.style.transform = `translate(${offsetX}px, ${offsetY}px)`;
            });
        });
    </script>

    <!-- AI Chat Modal -->
    <div id="chatModal" class="fixed bottom-24 right-8 z-40 w-96 max-w-[90vw] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-500 text-white p-5 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-lg">AI Assistant</h3>
                        <p class="text-sm text-green-100">Powered by Google Gemini</p>
                    </div>
                </div>
                <button 
                    onclick="closeChat()"
                    class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Messages -->
        <div 
            id="chatMessages" 
            class="p-5 h-80 overflow-y-auto bg-gray-50 dark:bg-gray-900">
            <div class="p-4 rounded-lg mb-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-white max-w-[80%]">
                <p>Hi there! How can I help you with your payments and transactions today?</p>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-5 border-t border-gray-200 dark:border-gray-700">
            <div class="flex gap-3">
                <input 
                    id="chatInput" 
                    type="text" 
                    placeholder="Ask me anything..."
                    class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                <button 
                    onclick="sendMessage()"
                    class="px-5 py-3 bg-gradient-to-r from-green-600 to-green-500 text-white rounded-xl hover:from-green-500 hover:to-green-400 transition-all font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Show loading when page starts loading (from cache or navigation)
        window.addEventListener('beforeunload', function() {
            const bodyEl = document.body;
            if (bodyEl.__x) {
                bodyEl.__x.$data.isLoading = true;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Handle all navigation links
            document.querySelectorAll('a:not([href^="#"]):not([target="_blank"])').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Skip if it's a download link
                    if (this.hasAttribute('download')) return;
                    
                    // Show loading
                    const bodyEl = document.body;
                    if (bodyEl.__x) {
                        bodyEl.__x.$data.isLoading = true;
                    }
                });
            });
            
            // Handle all form submissions
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const bodyEl = document.body;
                    if (bodyEl.__x) {
                        bodyEl.__x.$data.isLoading = true;
                    }
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
