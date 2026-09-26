<x-app-layout>
    <div class="dashboard-hero">
        <h1 class="page-title dashboard-title">DASHBOARD</h1>
        <p class="dashboard-subtitle">Welcome back, {{ auth()->user()->name }}. Monitor your bookings and reservation activity.</p>
    </div>

    @php
        $userBookingsQuery = \App\Models\Booking::where('user_id', auth()->id());
        $totalBookings = (clone $userBookingsQuery)->count();
        $confirmedBookings = (clone $userBookingsQuery)->where('status', 'confirmed')->count();
        $pendingBookings = (clone $userBookingsQuery)->where('status', 'pending')->count();
        $latestBooking = \App\Models\Booking::with('concert.venue')
            ->where('user_id', auth()->id())
            ->orderByDesc('id')
            ->first();
    @endphp

    <div class="user-dashboard-metrics">
        <article class="dashboard-metric-card">
            <p class="dashboard-metric-label">Total Reservations</p>
            <p class="dashboard-metric-value">{{ $totalBookings }}</p>
            <p class="dashboard-metric-meta">All bookings linked to your account</p>
        </article>
        <article class="dashboard-metric-card">
            <p class="dashboard-metric-label">Confirmed</p>
            <p class="dashboard-metric-value dashboard-metric-value--pink">{{ $confirmedBookings }}</p>
            <p class="dashboard-metric-meta">Bookings successfully secured</p>
        </article>
        <article class="dashboard-metric-card">
            <p class="dashboard-metric-label">Pending</p>
            <p class="dashboard-metric-value dashboard-metric-value--blue">{{ $pendingBookings }}</p>
            <p class="dashboard-metric-meta">Reservations waiting for updates</p>
        </article>
    </div>

    <div class="user-dashboard-panels">
        <section class="card dashboard-panel">
            <div class="card-header">
                <h3 class="card-title dashboard-panel-title">QUICK ACTIONS</h3>
            </div>
            <div class="card-body">
                <p class="dashboard-panel-text">Access core tasks quickly without leaving the dashboard.</p>
                <div class="dashboard-actions">
                    <a href="{{ route('home') }}" class="btn btn-primary dashboard-action-btn">Browse Concerts</a>
                    <a href="{{ route('bookings.index') }}" class="btn btn-secondary dashboard-action-btn">Open My Bookings</a>
                </div>
            </div>
        </section>

        <section class="card dashboard-panel">
            <div class="card-header">
                <h3 class="card-title dashboard-panel-title">LATEST BOOKING</h3>
            </div>
            <div class="card-body">
                @if($latestBooking)
                    <div class="dashboard-booking-item">
                        <p class="dashboard-booking-title">{{ $latestBooking->concert->title }}</p>
                        <p class="dashboard-booking-meta">{{ $latestBooking->concert->date->format('M d, Y') }} • {{ $latestBooking->concert->venue->name }}</p>
                        <span class="badge {{ $latestBooking->status === 'confirmed' ? 'badge-success' : ($latestBooking->status === 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                            {{ strtoupper($latestBooking->status) }}
                        </span>
                    </div>
                    <a href="{{ route('bookings.index') }}" class="btn btn-outline btn-small">View Full Booking History</a>
                @else
                    <div class="dashboard-empty">
                        <p class="dashboard-panel-text">No bookings yet. Start by browsing available concerts.</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-app-layout>
