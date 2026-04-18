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

    <!-- Payments -->
    <li class="menu-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-credit-card"></i>
        <div data-i18n="Payments">Payments</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="{{ route('payments.create') }}" class="menu-link">
            <div data-i18n="Create Payment">Create Payment</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="{{ route('payments.history') }}" class="menu-link">
            <div data-i18n="Payment History">Payment History</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Payouts -->
    <li class="menu-item {{ request()->routeIs('payouts.*') ? 'active' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-money-withdraw"></i>
        <div data-i18n="Payouts">Payouts</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="{{ route('payouts.create') }}" class="menu-link">
            <div data-i18n="Create Payout">Create Payout</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="{{ route('payouts.history') }}" class="menu-link">
            <div data-i18n="Payout History">Payout History</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- BillPay -->
    <li class="menu-item {{ request()->routeIs('billpay.*') ? 'active' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-receipt"></i>
        <div data-i18n="BillPay">BillPay</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="{{ route('billpay.index') }}" class="menu-link">
            <div data-i18n="All Bills">All Bills</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="{{ route('billpay.create') }}" class="menu-link">
            <div data-i18n="Create Bill">Create Bill</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Account -->
    <li class="menu-item {{ request()->routeIs('account.*') ? 'active' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-user"></i>
        <div data-i18n="Account">Account</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="{{ route('account.index') }}" class="menu-link">
            <div data-i18n="Overview">Overview</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="{{ route('account.balance') }}" class="menu-link">
            <div data-i18n="Balance">Balance</div>
          </a>
        </li>
        <li class="menu-item">
          <a href="{{ route('account.statement') }}" class="menu-link">
            <div data-i18n="Statement">Statement</div>
          </a>
        </li>
      </ul>
    </li>
  </ul>
</aside>
