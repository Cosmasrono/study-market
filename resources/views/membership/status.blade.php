@extends('layouts.app')

@section('title', 'Membership Status')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Membership Overview</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <h4>
                            <span class="badge bg-{{ $user->membership_status === 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($user->membership_status) }}
                            </span>
                        </h4>
                    </div>

                    @if($user->membership_expires_at)
                    <div class="mb-3">
                        <label class="text-muted small">Expires On</label>
                        <p class="mb-0">{{ $user->membership_expires_at->format('F d, Y') }}</p>
                        @if($user->membership_status === 'active')
                            <small class="text-muted">
                                ({{ $user->membership_expires_at->diffForHumans() }})
                            </small>
                        @endif
                    </div>
                    @endif

                    <div class="d-grid gap-2 mt-4">
                        @if($user->membership_status === 'expired' || !$user->hasMembership())
                            <a href="{{ route('membership.payment') }}" class="btn btn-primary">
                                <i class="fas fa-crown me-2"></i>Subscribe Now
                            </a>
                        @else
                            <a href="{{ route('membership.renew') }}" class="btn btn-warning text-dark">
                                <i class="fas fa-sync-alt me-2"></i>Renew Membership
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">Payment History</h5>
                </div>
                <div class="card-body">
                    @if($membershipPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($membershipPayments as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at->format('M d, Y') }}</td>
                                        <td><code>{{ $payment->reference }}</code></td>
                                        <td>KSh {{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($payment->status === 'completed')
                                                <a href="{{ route('membership.payments.receipt', $payment->id) }}" 
                                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> Receipt
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $membershipPayments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No payment history yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection