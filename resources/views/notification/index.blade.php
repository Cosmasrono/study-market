@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">My Notifications</h3>
                    @if($unreadCount > 0)
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                Mark All as Read
                            </button>
                        </form>
                    @endif
                </div>

                <div class="card-body p-0">
                    @if($notifications->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-muted">No notifications found.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($notifications as $notification)
                                <li class="list-group-item {{ $notification->unread() ? 'bg-light' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            @switch($notification->data['type'])
                                                @case('payment_success')
                                                    <strong>Payment Confirmation</strong>
                                                    <p class="mb-1 text-muted">
                                                        {{ $notification->data['message'] }}
                                                    </p>
                                                    <small class="text-muted">
                                                        Transaction ID: {{ $notification->data['transaction_id'] }}
                                                    </small>
                                                    @break
                                                @default
                                                    <p>{{ json_encode($notification->data) }}</p>
                                            @endswitch
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <small class="text-muted mr-3">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </small>
                                            @if($notification->unread())
                                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        Mark Read
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if($notifications->hasPages())
                    <div class="card-footer">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateUnreadCount() {
            fetch('{{ route('notifications.unread-count') }}')
                .then(response => response.json())
                .then(data => {
                    const unreadBadge = document.getElementById('unread-notifications-badge');
                    if (unreadBadge) {
                        unreadBadge.textContent = data.unread_count;
                        unreadBadge.style.display = data.unread_count > 0 ? 'inline-block' : 'none';
                    }
                });
        }

        // Update unread count periodically
        updateUnreadCount();
        setInterval(updateUnreadCount, 60000); // Update every minute
    });
</script>
@endpush