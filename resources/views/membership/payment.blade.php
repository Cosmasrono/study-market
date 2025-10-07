@extends('layouts.app')

@section('title', 'Membership Payment')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-crown me-2"></i>Annual Membership Payment</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="membership-benefits mb-4">
                        <h5 class="mb-3">Membership Benefits:</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check-circle text-success me-2"></i>Access to all premium content</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Unlimited exam attempts</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Download study materials</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Priority support</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Valid for 12 months</li>
                        </ul>
                    </div>

                    <div class="payment-details bg-light p-4 rounded mb-4">
                        <h5 class="mb-3">Payment Details</h5>
                        <div class="row">
                            <div class="col-6">
                                <strong>Membership Type:</strong>
                            </div>
                            <div class="col-6 text-end">
                                Annual Membership
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <strong>Duration:</strong>
                            </div>
                            <div class="col-6 text-end">
                                12 Months
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <strong>Amount:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <h4 class="text-primary mb-0">KSh {{ number_format($membershipPrice, 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('membership.pay') }}" method="POST" id="paymentForm">
                        @csrf
                        <input type="hidden" name="amount" value="{{ $membershipPrice }}">

                        <div class="mb-4">
                            <label for="phone" class="form-label">M-Pesa Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       placeholder="254712345678" 
                                       value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                       required>
                            </div>
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: 254XXXXXXXXX (e.g., 254712345678)</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-lock me-2"></i>Pay KSh {{ number_format($membershipPrice, 2) }}
                            </button>
                            <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </form>

                    <div class="payment-info mt-4">
                        <h6>Payment Instructions:</h6>
                        <ol class="small text-muted">
                            <li>Enter your M-Pesa registered phone number</li>
                            <li>Click "Pay" button</li>
                            <li>You will receive an M-Pesa prompt on your phone</li>
                            <li>Enter your M-Pesa PIN to complete payment</li>
                            <li>Wait for confirmation message</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('paymentForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
});
</script>
@endpush
@endsection