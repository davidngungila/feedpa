<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme advanced-sidebar">
  <div class="app-brand demo advanced-brand">
    <a href="{{ route('dashboard.index') }}" class="app-brand-link">
      <span class="app-brand-logo demo advanced-logo">
        <div class="logo-container">
          <div class="logo-circle">
            <span class="logo-text">FEED</span>
          </div>
          <div class="logo-badge">TAN</div>
        </div>
      </span>
      <span class="app-brand-text demo menu-text fw-bold advanced-brand-text">FEEDTAN CMG</span>
    </a>
    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none advanced-toggle">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow advanced-shadow"></div>

  <ul class="menu-inner py-1 advanced-menu">
    <!-- Dashboard -->
    <li class="menu-item {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
      <a href="{{ route('dashboard.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div data-i18n="Dashboard">Dashboard</div>
      </a>
    </li>

    <!-- Create Payment -->
    <li class="menu-item {{ request()->routeIs('payments.create') ? 'active' : '' }}">
      <a href="{{ route('payments.create') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-plus-circle"></i>
        <div data-i18n="Create Payment">Create Payment</div>
      </a>
    </li>

    <!-- Payment History -->
    <li class="menu-item {{ request()->routeIs('payments.history') ? 'active' : '' }}">
      <a href="{{ route('payments.history') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-history"></i>
        <div data-i18n="Payment History">Payment History</div>
      </a>
    </li>

    <!-- Generate statement -->
    <li class="menu-item {{ request()->routeIs('account.statement') ? 'active' : '' }}">
      <a href="{{ route('account.statement') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-file"></i>
        <div data-i18n="Generate statement">Generate statement</div>
      </a>
    </li>
  </ul>
</aside>
