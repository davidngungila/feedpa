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

    <!-- Create Payout -->
    @if(auth()->user()->can_create_payouts)
    <li class="menu-item {{ request()->routeIs('payouts.create') ? 'active' : '' }}">
        <a href="{{ route('payouts.create') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-money-withdraw"></i>
            <div data-i18n="Create Payout">Create Payout</div>
        </a>
    </li>
    @endif

    <!-- Beneficiaries -->
    <li class="menu-item {{ request()->routeIs('beneficiaries.*') ? 'active' : '' }}">
        <a href="{{ route('beneficiaries.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-book-open"></i>
            <div data-i18n="Beneficiaries">Beneficiaries</div>
        </a>
    </li>

    <!-- Payout History -->
    <li class="menu-item {{ request()->routeIs('payouts.index') ? 'active' : '' }}">
        <a href="{{ route('payouts.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-list-check"></i>
            <div data-i18n="Payout History">Payout History</div>
        </a>
    </li>

    <!-- Generate statement -->
    <li class="menu-item {{ request()->routeIs('account.statement') ? 'active' : '' }}">
      <a href="{{ route('account.statement') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-file"></i>
        <div data-i18n="Generate statement">Generate statement</div>
      </a>
    </li>

    <!-- Financial Reports -->
    <li class="menu-item {{ request()->routeIs('reports.*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
        <div data-i18n="Financial Reports">Financial Reports</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('reports.trial-balance') ? 'active' : '' }}">
          <a href="{{ route('reports.trial-balance') }}" class="menu-link">
            <div data-i18n="Trial Balance">Trial Balance</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}">
          <a href="{{ route('reports.balance-sheet') }}" class="menu-link">
            <div data-i18n="Balance Sheet">Balance Sheet</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
          <a href="{{ route('reports.profit-loss') }}" class="menu-link">
            <div data-i18n="Profit & Loss">Profit & Loss</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Bill Management -->
    <li class="menu-item {{ request()->routeIs('bills.*') ? 'active' : '' }}">
      <a href="{{ route('bills.index') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-file-invoice"></i>
        <div data-i18n="Bill Management">Bill Management</div>
      </a>
    </li>

    <!-- Financial Reports -->
    <li class="menu-item {{ request()->routeIs('reports.*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-bar-chart-alt"></i>
        <div data-i18n="Financial Reports">Financial Reports</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('reports.trial-balance') ? 'active' : '' }}">
          <a href="{{ route('reports.trial-balance') }}" class="menu-link">
            <div data-i18n="Trial Balance">Trial Balance</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}">
          <a href="{{ route('reports.balance-sheet') }}" class="menu-link">
            <div data-i18n="Balance Sheet">Balance Sheet</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
          <a href="{{ route('reports.profit-loss') }}" class="menu-link">
            <div data-i18n="Profit & Loss">Profit & Loss</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- System Settings (Admin Only) -->
    @if(auth()->user()->is_admin)
    <li class="menu-item {{ request()->routeIs('settings.*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-cog"></i>
        <div data-i18n="System Settings">System Settings</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('settings.sms') ? 'active' : '' }}">
          <a href="{{ route('settings.sms') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-message-rounded"></i>
            <div data-i18n="SMS Settings">SMS Settings</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('settings.email') ? 'active' : '' }}">
          <a href="{{ route('settings.email') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-envelope"></i>
            <div data-i18n="Email Settings">Email Settings</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('settings.general') ? 'active' : '' }}">
          <a href="{{ route('settings.general') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-slideshow"></i>
            <div data-i18n="General Settings">General Settings</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('settings.ai') ? 'active' : '' }}">
          <a href="{{ route('settings.ai') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-robot"></i>
            <div data-i18n="AI Settings">AI Settings</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- WhatsApp Operations (Admin Only) -->
    <li class="menu-item {{ request()->routeIs('whatsapp.*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bxl-whatsapp"></i>
        <div data-i18n="WhatsApp Operations">WhatsApp Operations</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->routeIs('whatsapp.messages.send') ? 'active' : '' }}">
          <a href="{{ route('whatsapp.messages.send') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-message-square-add"></i>
            <div data-i18n="Send Messages">Send Messages</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('whatsapp.contacts.index') ? 'active' : '' }}">
          <a href="{{ route('whatsapp.contacts.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-user-plus"></i>
            <div data-i18n="Manage Contacts">Manage Contacts</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('whatsapp.groups.index') ? 'active' : '' }}">
          <a href="{{ route('whatsapp.groups.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-group"></i>
            <div data-i18n="Manage Groups">Manage Groups</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('whatsapp.sessions.index') ? 'active' : '' }}">
          <a href="{{ route('whatsapp.sessions.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-cog"></i>
            <div data-i18n="Manage Sessions">Manage Sessions</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('whatsapp.webhooks.index') ? 'active' : '' }}">
          <a href="{{ route('whatsapp.webhooks.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-webhook"></i>
            <div data-i18n="Manage Webhooks">Manage Webhooks</div>
          </a>
        </li>
        <li class="menu-item {{ request()->routeIs('whatsapp.media.index') ? 'active' : '' }}">
          <a href="{{ route('whatsapp.media.index') }}" class="menu-link">
            <i class="menu-icon tf-icons bx bx-folder"></i>
            <div data-i18n="Media & Files">Media & Files</div>
          </a>
        </li>
      </ul>
    </li>
    @endif
  </ul>
</aside>
