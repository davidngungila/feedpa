@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">SMS Testing</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">SMS Testing</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Custom SMS Test -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Send Custom SMS</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('sms-test.send') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" 
                                       value="0622239304" placeholder="255712345678" required>
                                <small class="form-text text-muted">Enter phone number (format: 0622239304 or 255622239304)</small>
                            </div>
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="3" 
                                          placeholder="Enter your message here..." required maxlength="160">Test message from FEEDTAN system</textarea>
                                <small class="form-text text-muted">Maximum 160 characters</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-2"></i> Send SMS
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Pre-defined Tests -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pre-defined Tests</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('sms-test.test-bill') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="bill_phone">Phone Number</label>
                                <input type="text" class="form-control" id="bill_phone" name="phone_number" 
                                       value="0622239304" required>
                            </div>
                            <button type="submit" class="btn btn-info mb-2">
                                <i class="fas fa-file-invoice mr-2"></i> Test Bill Notification
                            </button>
                        </form>

                        <form action="{{ route('sms-test.test-payment') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="payment_phone">Phone Number</label>
                                <input type="text" class="form-control" id="payment_phone" name="phone_number" 
                                       value="0622239304" required>
                            </div>
                            <button type="submit" class="btn btn-success mb-2">
                                <i class="fas fa-check-circle mr-2"></i> Test Payment Confirmation
                            </button>
                        </form>

                        <form action="{{ route('sms-test.test-insufficient') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="insufficient_phone">Phone Number</label>
                                <input type="text" class="form-control" id="insufficient_phone" name="phone_number" 
                                       value="0622239304" required>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Test Insufficient Funds
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Configuration Info -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>API Configuration</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-server"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Base URL</span>
                                        <span class="info-box-number">messaging-service.co.tz</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Token Status</span>
                                        <span class="info-box-number">Configured</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Sender ID</span>
                                        <span class="info-box-number">FEEDTAN</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary">
                                        <i class="fas fa-toggle-on"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Service Status</span>
                                        <span class="info-box-number">@php echo config('messaging.enabled') ? 'Enabled' : 'Disabled'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh results
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sending...';
            }
        });
    });
});
</script>
@endpush
@endsection
