@extends('layouts.app')

@section('title', 'Renew Membership')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="fas fa-sync-alt me-2"></i>Renew Membership</h4>
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

                    <div class="current-status mb-4">
                        <h5>Current Membership Status</h5>
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-6">
                                    <strong>Status:</strong>
                                </div>
                                <div class="col-6 text-end">
                                    <span class="badge bg-{{ $user->membership_status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($user->membership_status) }}
                                    </span>
                                </div>
                            </div>
                            @if($user->membership_expires_at)
                            <div class="row mt-2">
                                <div class="col-6">
                                    <strong>Expires On:</strong>
                                </div>
                                <div class="col-6 text-end">
                                    {{ $user->membership_expires_at->format('F d, Y') }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="renewal-details bg-light p-4 rounded mb-4">
                        <h5 class="mb-3">Renewal Details</h5>
                        <p>Renew your membership for another 12 months and continue enjoying all premium benefits.</p>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <strong>Renewal Amount:</strong>
                            </div>
                            <div class="col-6 text-end">
                                <h4 class="text-primary mb-0">KSh {{ number_format($membershipPrice, 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('membership.renew.process') }}" method="POST" id="renewalForm">
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
                                       value="{{ old('phone', $user->phone ?? '') }}"
                                       required>
                            </div>
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: 254XXXXXXXXX</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg text-dark" id="submitBtn">
                                <i class="fas fa-sync-alt me-2"></i>Renew for KSh {{ number_format($membershipPrice, 2) }}
                            </button>
                            <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('renewalForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
});
</script>
@endpush
@endsection