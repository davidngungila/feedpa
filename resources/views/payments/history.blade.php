@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0" style="font-size: 1.5rem;">
                    <i class="fas fa-history me-2"></i>
                    Payment History
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Payment History</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">{{ number_format($totalCount ?? 0) }}</h3>
                                <p class="mb-0">Total Transactions</p>
                            </div>
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle">
                                <i class="fas fa-receipt text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">{{ number_format($successCount ?? 0) }}</h3>
                                <p class="mb-0">Successful</p>
                            </div>
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle">
                                <i class="fas fa-check-circle text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">{{ number_format($pendingCount ?? 0) }}</h3>
                                <p class="mb-0">Pending</p>
                            </div>
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle">
                                <i class="fas fa-clock text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card bg-gradient-danger text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3 class="mb-0">{{ number_format($failedCount ?? 0) }}</h3>
                                <p class="mb-0">Failed</p>
                            </div>
                            <div class="avatar avatar-lg bg-white bg-opacity-25 rounded-circle">
                                <i class="fas fa-exclamation-triangle text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Advanced Filters & Search
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleFilters()">
                            <i class="fas fa-chevron-down" id="filterIcon"></i>
                        </button>
                    </div>
                    <div class="card-body" id="filtersSection" style="{{ request()->hasAny(['order_reference', 'status', 'currency', 'phone', 'payer_name', 'start_date', 'end_date']) ? '' : 'display: none;' }}">
                        <form method="GET" action="{{ route('payments.history') }}" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Order Reference</label>
                                    <input type="text" class="form-control" name="order_reference" value="{{ request('order_reference') }}" placeholder="Search reference...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Payer Name</label>
                                    <input type="text" class="form-control" name="payer_name" value="{{ request('payer_name') }}" placeholder="Search name...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control" name="phone" value="{{ request('phone') }}" placeholder="Search phone...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="SETTLED" {{ request('status', 'SETTLED') == 'SETTLED' ? 'selected' : '' }}>SETTLED</option>
                                        <option value="FAILED" {{ request('status') == 'FAILED' ? 'selected' : '' }}>FAILED</option>
                                        <option value="SUCCESS" {{ request('status') == 'SUCCESS' ? 'selected' : '' }}>SUCCESS</option>
                                        <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                                        <option value="PROCESSING" {{ request('status') == 'PROCESSING' ? 'selected' : '' }}>PROCESSING</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Currency</label>
                                    <select class="form-select" name="currency">
                                        <option value="">All Currencies</option>
                                        <option value="TZS" {{ request('currency') == 'TZS' ? 'selected' : '' }}>TZS</option>
                                        <option value="USD" {{ request('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search me-1"></i> Apply
                                        </button>
                                        <a href="{{ route('payments.history') }}" class="btn btn-outline-secondary w-100">
                                            Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Tabs -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white p-0">
                <ul class="nav nav-tabs nav-fill" id="statusTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link py-3 {{ request('status', 'SETTLED') === 'SETTLED' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }}" 
                           href="{{ request()->fullUrlWithQuery(['status' => 'SETTLED']) }}">
                            <i class="fas fa-check-circle me-2"></i> SETTLED
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-3 {{ request('status') === 'FAILED' ? 'active fw-bold border-bottom border-primary border-3' : 'text-muted' }}" 
                           href="{{ request()->fullUrlWithQuery(['status' => 'FAILED']) }}">
                            <i class="fas fa-times-circle me-2"></i> FAILED
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2 text-primary"></i>
                            Recent Transactions
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#exportModal" onclick="setExportType('pdf')">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#exportModal" onclick="setExportType('excel')">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <a href="{{ route('payments.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> New Payment
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Reference</th>
                                        <th>Customer</th>
                                        <th>Purpose / Description</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold">{{ $payment->order_reference }}</div>
                                                <small class="text-muted">{{ $payment->transaction_id ?? 'No ID' }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $payment->customer_name ?? $payment->payer_name ?? 'N/A' }}</div>
                                                @if($payment->customer_name && $payment->payer_name && strtolower($payment->customer_name) !== strtolower($payment->payer_name))
                                                    <div class="text-xs text-muted" style="font-size: 0.75rem;">Payer: {{ $payment->payer_name }}</div>
                                                @endif
                                                <small class="text-muted">{{ $payment->phone ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <div class="text-wrap" style="max-width: 200px;">
                                                    {{ $payment->description && $payment->description !== 'N/A' ? $payment->description : 'Malipo ya FEEDTAN' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ number_format($payment->amount, 2) }}</div>
                                                <small class="text-muted">{{ $payment->currency }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = match($payment->status) {
                                                        'SUCCESS', 'SETTLED' => 'bg-success',
                                                        'PENDING', 'PROCESSING' => 'bg-warning text-dark',
                                                        'FAILED' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $payment->status }}</span>
                                            </td>
                                            <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                                            <td>
                                                <div>{{ $payment->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $payment->created_at->format('H:i A') }}</small>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="{{ route('payments.status', ['reference' => $payment->order_reference]) }}" class="btn btn-sm btn-info text-white" title="View Status">
                                                        View
                                                    </a>
                                                    @if(in_array($payment->status, ['SUCCESS', 'SETTLED']))
                                                        <a href="{{ route('payments.receipt', $payment->order_reference) }}" class="btn btn-sm btn-success text-white" title="Download Receipt" target="_blank">
                                                            Receipt
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-muted mb-3">
                                                    <i class="fas fa-search fs-1"></i>
                                                </div>
                                                <h5>No transactions found</h5>
                                                <p>Try adjusting your filters or search terms.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($payments->hasPages())
                        <div class="card-footer bg-white py-3">
                            {{ $payments->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="exportForm" method="GET" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Export Options</h5>
                    <button type="button" class="btn-close" data-bs-toggle="modal" data-bs-target="#exportModal"></button>
                </div>
                <div class="modal-body">
                    <p>Select columns to include in the export:</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="order_reference" id="col_ref" checked>
                                <label class="form-check-label" for="col_ref">Order Reference</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="transaction_id" id="col_tid" checked>
                                <label class="form-check-label" for="col_tid">Transaction ID</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="status" id="col_status" checked>
                                <label class="form-check-label" for="col_status">Status</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="amount" id="col_amount" checked>
                                <label class="form-check-label" for="col_amount">Amount</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="currency" id="col_curr" checked>
                                <label class="form-check-label" for="col_curr">Currency</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="description" id="col_desc" checked>
                                <label class="form-check-label" for="col_desc">Purpose / Description</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="payer_name" id="col_name" checked>
                                <label class="form-check-label" for="col_name">Payer Name</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="phone" id="col_phone" checked>
                                <label class="form-check-label" for="col_phone">Phone</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="email" id="col_email" checked>
                                <label class="form-check-label" for="col_email">Email</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="payment_method" id="col_method" checked>
                                <label class="form-check-label" for="col_method">Payment Method</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="columns[]" value="created_at" id="col_date" checked>
                                <label class="form-check-label" for="col_date">Created At</label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <p class="text-muted small">The export will respect your current filters.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="confirmExportBtn">Export Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.bg-gradient-primary { background: linear-gradient(135deg, #0d6efd 0%, #0043a8 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #198754 0%, #0c5231 100%); }
.bg-gradient-warning { background: linear-gradient(135deg, #ffc107 0%, #ba8b00 100%); }
.bg-gradient-danger { background: linear-gradient(135deg, #dc3545 0%, #a11b27 100%); }
.card { border: none; }
.table > :not(caption) > * > * { padding: 1rem 0.5rem; }
.avatar { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
</style>
@endpush

@push('scripts')
<script>
function toggleFilters() {
    const section = document.getElementById('filtersSection');
    const icon = document.getElementById('filterIcon');
    if (section.style.display === 'none') {
        section.style.display = 'block';
        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
    } else {
        section.style.display = 'none';
        icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
    }
}

function setExportType(type) {
    const form = document.getElementById('exportForm');
    if (type === 'pdf') {
        form.action = '{{ route('payments.export.pdf') }}';
        document.getElementById('confirmExportBtn').className = 'btn btn-danger';
        document.getElementById('confirmExportBtn').innerText = 'Export PDF';
    } else {
        form.action = '{{ route('payments.export.excel') }}';
        document.getElementById('confirmExportBtn').className = 'btn btn-success';
        document.getElementById('confirmExportBtn').innerText = 'Export Excel';
    }

    // Add current filters to export form
    const filterForm = document.getElementById('filterForm');
    const formData = new FormData(filterForm);
    
    // Remove old hidden filter inputs
    form.querySelectorAll('.filter-input').forEach(el => el.remove());
    
    // Add current filters
    for (let [key, value] of formData.entries()) {
        if (value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            input.className = 'filter-input';
            form.appendChild(input);
        }
    }
}
</script>
@endpush

@endsection
