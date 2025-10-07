@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-history me-2"></i>Membership Payment History</h4>
        </div>
        <div class="card-body">
            @if($payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Phone Number</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Paid At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td>{{ $payment->created_at->format('M d, Y H:i') }}</td>
                                <td><code>{{ $payment->reference }}</code></td>
                                <td>{{ $payment->phone_number }}</td>
                                <td><strong>KSh {{ number_format($payment->amount, 2) }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($payment->paid_at)
                                        {{ $payment->paid_at->format('M d, Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->status === 'completed')
                                        <a href="{{ route('membership.payments.receipt', $payment->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           target="_blank"
                                           title="Download Receipt">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @elseif($payment->status === 'pending')
                                        <button class="btn btn-sm btn-outline-info check-status" 
                                                data-reference="{{ $payment->reference }}"
                                                title="Check Status">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $payments->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No payment history available</p>
                    <a href="{{ route('membership.payment') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-crown me-2"></i>Get Membership
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.check-status').forEach(btn => {
    btn.addEventListener('click', function() {
        const reference = this.dataset.reference;
        window.location.href = `/membership/payment-status/${reference}`;
    });
});
</script>
@endpush
@endsection