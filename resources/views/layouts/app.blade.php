<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ClickPesa Payment System') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}">
    
    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/vendor/css/core.css') }}" rel="stylesheet">
    
    <!-- Main CSS -->
    <link href="{{ asset('assets/css/demo.css') }}" rel="stylesheet">
    
    <!-- Page CSS -->
    
    @stack('styles')
</head>
<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('layouts.navigation')
            
            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('layouts.navbar')
                
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @include('layouts.flash')
                        @yield('content')
                    </div>
                    
                    <!-- Footer -->
                    @include('layouts.footer')
                    
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        
        <!-- Drag Target Area -->
        <div class="drag-target"></div>
    </div>
    
    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/js/menu.js') }}"></script>
    
    <!-- Fixed Sidebar and Header CSS -->
    <style>
        /* Fix sidebar to prevent scrolling */
        .layout-menu {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            z-index: 1030 !important;
            transform: translateX(0) !important;
            transition: transform 0.3s ease !important;
        }
        
        /* Fix header to prevent scrolling */
        .layout-navbar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1020 !important;
            background: white !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        /* Adjust content wrapper to account for fixed header */
        .content-wrapper {
            margin-top: 70px !important;
        }
        
        /* Ensure dropdowns appear above all content */
        .dropdown-menu {
            z-index: 99999 !important;
        }
        
        /* Profile dropdown specific styling */
        .navbar-nav .dropdown-menu {
            z-index: 99999 !important;
            position: absolute !important;
        }
        
        /* User dropdown container */
        .dropdown-user .dropdown-menu {
            z-index: 99999 !important;
            position: absolute !important;
        }
        
        /* Fix navbar overflow to allow dropdown to escape */
        .layout-navbar {
            overflow: visible !important;
        }
        
        .navbar-nav-right {
            overflow: visible !important;
        }
        
        .navbar-nav {
            overflow: visible !important;
        }
        
        /* Ensure dropdown can break out of container */
        .dropdown {
            overflow: visible !important;
        }
        
        .dropdown-menu {
            overflow: visible !important;
            position: absolute !important;
            transform: translateY(0) !important;
        }
        
        /* Adjust layout page to account for fixed sidebar - reduced space */
        .layout-page {
            margin-left: 250px !important;
            transition: margin-left 0.3s ease !important;
            padding-left: 0 !important;
        }
        
        /* When sidebar is collapsed - reduced space */
        .layout-menu-collapsed .layout-page {
            margin-left: 70px !important;
            padding-left: 0 !important;
        }
        
        /* When sidebar is hidden on mobile */
        @media (max-width: 1200px) {
            .layout-page {
                margin-left: 0 !important;
                padding-left: 0 !important;
            }
            
            .layout-menu {
                transform: translateX(-100%) !important;
            }
            
            .layout-menu-open .layout-menu {
                transform: translateX(0) !important;
            }
        }
        
        /* Ensure sidebar stays fixed during scroll */
        .layout-menu.menu-vertical {
            position: fixed !important;
            height: 100vh !important;
            overflow-y: auto !important;
            right: auto !important;
            margin-right: 0 !important;
        }
        
        /* Remove gap between sidebar and content */
        .layout-container {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .layout-wrapper {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        /* Prevent content from going under fixed sidebar on desktop - reduced space */
        @media (min-width: 1201px) {
            .content-wrapper {
                margin-left: 250px !important;
                padding-left: 1rem !important;
            }
            
            .layout-menu-collapsed .content-wrapper {
                margin-left: 70px !important;
                padding-left: 1rem !important;
            }
        }
        
        /* Remove any gaps or spaces */
        .layout-page > .content-wrapper {
            margin-left: 0 !important;
            padding-left: 0 !important;
        }
        
        /* Ensure no horizontal gaps */
        .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        .col {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        
        /* Remove container padding that creates gaps */
        .container-xxl,
        .container-fluid,
        .container {
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        
        /* Advanced Sidebar Design - Dark Green Theme */
        .advanced-sidebar {
            background: linear-gradient(135deg, #0f4c2f 0%, #1a5c3a 25%, #2e7d32 50%, #43a047 75%, #66bb6a 100%) !important;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2) !important;
            border-right: 3px solid #2e7d32 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            z-index: 1030 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }
        
        .advanced-brand {
            background: rgba(255, 255, 255, 0.15) !important;
            backdrop-filter: blur(10px) !important;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3) !important;
            padding: 1.5rem 1rem !important;
            margin: 0 !important;
        }
        
        .advanced-logo {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .logo-container {
            position: relative !important;
            width: 40px !important;
            height: 40px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .logo-circle {
            width: 35px !important;
            height: 35px !important;
            background: linear-gradient(45deg, #2e7d32, #66bb6a) !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.4) !important;
            animation: logoPulse 2s infinite !important;
        }
        
        .logo-text {
            color: white !important;
            font-weight: bold !important;
            font-size: 10px !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
        }
        
        .logo-badge {
            position: absolute !important;
            top: -5px !important;
            right: -5px !important;
            background: linear-gradient(45deg, #ffc107, #ffeb3b) !important;
            color: #333 !important;
            font-size: 8px !important;
            font-weight: bold !important;
            padding: 2px 4px !important;
            border-radius: 10px !important;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.5) !important;
            animation: badgeFloat 3s ease-in-out infinite !important;
        }
        
        .advanced-brand-text {
            color: white !important;
            font-size: 1.2rem !important;
            font-weight: 800 !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
            letter-spacing: 1px !important;
            background: linear-gradient(45deg, #ffffff, #81c784) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            background-clip: text !important;
        }
        
        .advanced-toggle {
            color: white !important;
            background: rgba(255, 255, 255, 0.15) !important;
            border-radius: 50% !important;
            width: 30px !important;
            height: 30px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.3s ease !important;
        }
        
        .advanced-toggle:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            transform: scale(1.1) !important;
        }
        
        .advanced-shadow {
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.4) 0%, transparent 100%) !important;
            height: 20px !important;
        }
        
        .advanced-menu {
            padding: 1rem 0 !important;
        }
        
        .advanced-menu .menu-item {
            margin: 0.25rem 0.5rem !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            transition: all 0.3s ease !important;
            position: relative !important;
        }
        
        .advanced-menu .menu-item:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            transform: translateX(5px) !important;
        }
        
        .advanced-menu .menu-item.active {
            background: linear-gradient(135deg, rgba(46, 125, 50, 0.9), rgba(102, 187, 106, 0.9)) !important;
            box-shadow: 0 4px 15px rgba(46, 125, 50, 0.5) !important;
            transform: translateX(5px) !important;
            border-left: 4px solid #ffeb3b !important;
        }
        
        .advanced-menu .menu-item.active::before {
            content: '' !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            bottom: 0 !important;
            width: 4px !important;
            background: linear-gradient(180deg, #ffc107, #ffeb3b) !important;
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.8) !important;
        }
        
        .advanced-menu .menu-link {
            color: rgba(255, 255, 255, 0.9) !important;
            padding: 0.75rem 1rem !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            font-weight: 500 !important;
        }
        
        .advanced-menu .menu-item:hover .menu-link,
        .advanced-menu .menu-item.active .menu-link {
            color: white !important;
            font-weight: 600 !important;
        }
        
        .advanced-menu .menu-icon {
            font-size: 1.25rem !important;
            margin-right: 0.75rem !important;
            width: 24px !important;
            text-align: center !important;
            transition: all 0.3s ease !important;
        }
        
        .advanced-menu .menu-item.active .menu-icon {
            color: #ffeb3b !important;
            text-shadow: 0 0 8px rgba(255, 235, 59, 0.8) !important;
            transform: scale(1.1) !important;
        }
        
        .advanced-menu .menu-item:hover .menu-icon {
            transform: scale(1.05) !important;
        }
        
        /* Animations */
        @keyframes logoPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes badgeFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }
        
        /* Comprehensive Responsive Design */
        /* Extra Large Screens (1400px and up) */
        @media (min-width: 1400px) {
            .advanced-sidebar {
                width: 280px !important;
            }
            
            .layout-page {
                margin-left: 280px !important;
            }
            
            .content-wrapper {
                margin-left: 280px !important;
            }
            
            .layout-menu-collapsed .layout-page {
                margin-left: 80px !important;
            }
            
            .layout-menu-collapsed .content-wrapper {
                margin-left: 80px !important;
            }
        }
        
        /* Large Screens (1200px - 1399px) */
        @media (min-width: 1201px) and (max-width: 1399px) {
            .advanced-sidebar {
                width: 250px !important;
            }
            
            .layout-page {
                margin-left: 250px !important;
            }
            
            .content-wrapper {
                margin-left: 250px !important;
            }
            
            .layout-menu-collapsed .layout-page {
                margin-left: 70px !important;
            }
            
            .layout-menu-collapsed .content-wrapper {
                margin-left: 70px !important;
            }
        }
        
        /* Medium Screens (992px - 1199px) */
        @media (min-width: 992px) and (max-width: 1200px) {
            .advanced-sidebar {
                width: 240px !important;
            }
            
            .layout-page {
                margin-left: 240px !important;
            }
            
            .content-wrapper {
                margin-left: 240px !important;
            }
            
            .layout-menu-collapsed .layout-page {
                margin-left: 65px !important;
            }
            
            .layout-menu-collapsed .content-wrapper {
                margin-left: 65px !important;
            }
            
            .advanced-brand-text {
                font-size: 1.1rem !important;
            }
            
            .logo-container {
                width: 35px !important;
                height: 35px !important;
            }
            
            .logo-circle {
                width: 30px !important;
                height: 30px !important;
            }
            
            .logo-text {
                font-size: 9px !important;
            }
        }
        
        /* Small Screens (768px - 991px) */
        @media (min-width: 768px) and (max-width: 991px) {
            .advanced-sidebar {
                width: 220px !important;
                transform: translateX(-100%) !important;
                transition: transform 0.3s ease !important;
            }
            
            .layout-menu-open .advanced-sidebar {
                transform: translateX(0) !important;
            }
            
            .layout-page {
                margin-left: 0 !important;
                transition: margin-left 0.3s ease !important;
            }
            
            .content-wrapper {
                margin-left: 0 !important;
            }
            
            .layout-menu-open .layout-page {
                margin-left: 220px !important;
            }
            
            .layout-menu-open .content-wrapper {
                margin-left: 220px !important;
            }
            
            .advanced-brand-text {
                font-size: 1rem !important;
            }
            
            .advanced-menu .menu-link {
                padding: 0.6rem 0.8rem !important;
                font-size: 0.9rem !important;
            }
            
            .advanced-menu .menu-icon {
                font-size: 1.1rem !important;
                margin-right: 0.6rem !important;
            }
        }
        
        /* Extra Small Screens (480px - 767px) */
        @media (min-width: 480px) and (max-width: 767px) {
            .advanced-sidebar {
                width: 260px !important;
                transform: translateX(-100%) !important;
                transition: transform 0.3s ease !important;
            }
            
            .layout-menu-open .advanced-sidebar {
                transform: translateX(0) !important;
            }
            
            .layout-page {
                margin-left: 0 !important;
                transition: margin-left 0.3s ease !important;
            }
            
            .content-wrapper {
                margin-left: 0 !important;
            }
            
            .layout-menu-open .layout-page {
                margin-left: 260px !important;
            }
            
            .layout-menu-open .content-wrapper {
                margin-left: 260px !important;
            }
            
            .advanced-brand {
                padding: 1rem 0.8rem !important;
            }
            
            .advanced-brand-text {
                font-size: 0.95rem !important;
            }
            
            .logo-container {
                width: 32px !important;
                height: 32px !important;
            }
            
            .logo-circle {
                width: 28px !important;
                height: 28px !important;
            }
            
            .logo-text {
                font-size: 8px !important;
            }
            
            .logo-badge {
                font-size: 7px !important;
                padding: 1px 3px !important;
            }
            
            .advanced-menu .menu-link {
                padding: 0.8rem 1rem !important;
                font-size: 0.95rem !important;
            }
            
            .advanced-menu .menu-icon {
                font-size: 1.2rem !important;
                margin-right: 0.8rem !important;
            }
        }
        
        /* Ultra Small Screens (320px - 479px) */
        @media (max-width: 479px) {
            .advanced-sidebar {
                width: 100% !important;
                max-width: 280px !important;
                transform: translateX(-100%) !important;
                transition: transform 0.3s ease !important;
            }
            
            .layout-menu-open .advanced-sidebar {
                transform: translateX(0) !important;
            }
            
            .layout-page {
                margin-left: 0 !important;
                transition: margin-left 0.3s ease !important;
            }
            
            .content-wrapper {
                margin-left: 0 !important;
            }
            
            .layout-menu-open .layout-page {
                margin-left: 0 !important;
            }
            
            .layout-menu-open .content-wrapper {
                margin-left: 0 !important;
            }
            
            .advanced-brand {
                padding: 0.8rem 0.6rem !important;
            }
            
            .advanced-brand-text {
                font-size: 0.9rem !important;
            }
            
            .logo-container {
                width: 30px !important;
                height: 30px !important;
            }
            
            .logo-circle {
                width: 25px !important;
                height: 25px !important;
            }
            
            .logo-text {
                font-size: 7px !important;
            }
            
            .logo-badge {
                font-size: 6px !important;
                padding: 1px 2px !important;
            }
            
            .advanced-menu .menu-link {
                padding: 0.8rem 0.8rem !important;
                font-size: 0.9rem !important;
            }
            
            .advanced-menu .menu-icon {
                font-size: 1.1rem !important;
                margin-right: 0.6rem !important;
            }
            
            .advanced-menu .menu-item {
                margin: 0.2rem 0.3rem !important;
            }
        }
        
        /* Landscape Orientation */
        @media (max-height: 600px) and (orientation: landscape) {
            .advanced-sidebar {
                height: 100vh !important;
                overflow-y: auto !important;
            }
            
            .advanced-brand {
                padding: 1rem 0.8rem !important;
            }
            
            .advanced-menu {
                padding: 0.5rem 0 !important;
            }
            
            .advanced-menu .menu-link {
                padding: 0.6rem 0.8rem !important;
            }
        }
        
        /* High DPI Displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .logo-text {
                font-weight: 900 !important;
            }
            
            .advanced-brand-text {
                font-weight: 900 !important;
            }
            
            .advanced-menu .menu-link {
                font-weight: 600 !important;
            }
        }
        
        /* Print Styles */
        @media print {
            .advanced-sidebar {
                display: none !important;
            }
            
            .layout-page {
                margin-left: 0 !important;
            }
            
            .content-wrapper {
                margin-left: 0 !important;
            }
        }
        
        /* Hide scrollbar on sidebar but keep functionality */
        .layout-menu::-webkit-scrollbar {
            width: 6px !important;
        }
        
        .layout-menu::-webkit-scrollbar-track {
            background: #f1f1f1 !important;
        }
        
        .layout-menu::-webkit-scrollbar-thumb {
            background: #c1c1c1 !important;
            border-radius: 3px !important;
        }
        
        .layout-menu::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8 !important;
        }
        
        /* Remove bottom scroll from all pages */
        html, body {
            overflow-x: hidden !important;
            overflow-y: auto !important;
            height: 100% !important;
            max-height: 100vh !important;
        }
        
        /* Remove bottom padding that causes unwanted scroll */
        .content-wrapper {
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
            min-height: calc(100vh - 70px) !important;
        }
        
        /* Remove footer bottom scroll */
        .content-footer {
            position: relative !important;
            bottom: auto !important;
            margin-top: 0 !important;
            padding: 1rem 0 !important;
        }
        
        /* Remove bottom scroll from all containers */
        .container-xxl,
        .container-fluid,
        .container {
            overflow-x: hidden !important;
            overflow-y: visible !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        
        /* Remove bottom scroll from layout containers */
        .layout-wrapper,
        .layout-container,
        .layout-page {
            overflow-x: hidden !important;
            overflow-y: visible !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        
        /* Remove bottom scroll from content sections */
        .content,
        .content-header,
        .card {
            overflow-x: hidden !important;
            overflow-y: visible !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        
        /* Remove bottom scroll from all elements */
        * {
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        
        *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        
        /* Ensure no bottom scroll on any element */
        html {
            scroll-behavior: smooth !important;
        }
        
        body {
            overscroll-behavior: none !important;
            overscroll-behavior-y: none !important;
        }
        
        /* Remove bottom margin from last elements */
        .card:last-child,
        .row:last-child,
        .col:last-child,
        .container:last-child,
        .content-wrapper:last-child {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
    </style>
    
    <!-- Menu Class Definition (must be loaded before main.js) -->
    <script>
        // Define Menu class globally
        window.Menu = class Menu {
            constructor(element, options = {}) {
                this.element = element;
                this.options = options;
                this.orientation = options.orientation || 'vertical';
                this.closeChildren = options.closeChildren || false;
                this.init();
            }
            
            init() {
                console.log('Menu initialized for element:', this.element);
                // Basic menu initialization
                if (this.element) {
                    this.element.classList.add('menu-initialized');
                }
            }
            
            toggle() {
                if (this.element) {
                    this.element.classList.toggle('menu-open');
                }
            }
            
            close() {
                if (this.element) {
                    this.element.classList.remove('menu-open');
                }
            }
            
            open() {
                if (this.element) {
                    this.element.classList.add('menu-open');
                }
            }
        };
        
        // Define Helpers object globally
        window.Helpers = {
            scrollToActive: function(animate = true) {
                console.log('Scroll to active called');
            },
            toggleCollapsed: function() {
                console.log('Toggle collapsed called');
                const body = document.body;
                body.classList.toggle('layout-menu-collapsed');
            },
            isSmallScreen: function() {
                return window.innerWidth < 768;
            },
            mainMenu: null
        };
        
        console.log('Menu class and Helpers defined globally');
    </script>
    
    <!-- Main JS -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @stack('scripts')
    
    <!-- SweetAlert Notifications -->
    @include('partials/sweetalert-notifications')
</body>
</html>
