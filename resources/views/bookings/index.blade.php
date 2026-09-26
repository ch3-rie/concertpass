<x-app-layout>
    <div class="bookings-hero">
        <div>
            <h1 class="page-title bookings-title">MY BOOKINGS</h1>
            <p class="bookings-subtitle">Review all your ticket reservations, statuses, and payment totals.</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-secondary">Browse Concerts</a>
    </div>

    <div class="bookings-grid">
        @forelse($bookings as $booking)
            <a href="{{ route('bookings.show', $booking) }}" class="bookings-card-link">
                <article class="card bookings-card">
                    <div class="bookings-card-banner"></div>

                    <div class="bookings-card-content">
                        <div class="bookings-card-head">
                            <div>
                                <p class="bookings-reference">Booking #{{ $booking->id }}</p>
                                <h3 class="bookings-card-title">{{ $booking->concert->title }}</h3>
                                <p class="bookings-card-artist">{{ $booking->concert->artist }}</p>
                            </div>
                            <span class="badge {{ $booking->status === 'confirmed' ? 'badge-success' : ($booking->status === 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                                {{ strtoupper($booking->status) }}
                            </span>
                        </div>

                        <div class="bookings-detail-grid">
                            <p><span>Date</span>{{ $booking->concert->date->format('M d, Y') }}</p>
                            <p><span>Venue</span>{{ $booking->concert->venue->name }}</p>
                            <p><span>Location</span>{{ $booking->concert->venue->location }}</p>
                            <p><span>Tickets</span>{{ $booking->tickets->count() }} Ticket{{ $booking->tickets->count() !== 1 ? 's' : '' }}</p>
                        </div>
                    </div>

                    <div class="bookings-card-footer">
                        <div>
                            <p class="bookings-total-label">Total Price</p>
                            <p class="bookings-total-value">₱{{ number_format($booking->total_price, 2) }}</p>
                        </div>
                        <span class="btn btn-primary btn-small">View Details</span>
                    </div>
                </article>
            </a>
        @empty
            <div class="card bookings-empty-state">
                <div class="bookings-empty-icon">📋</div>
                <h3 class="bookings-empty-title">No Bookings Yet</h3>
                <p class="bookings-empty-text">Your reservation history will appear here once you book a concert.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">Browse Concerts</a>
            </div>
        @endforelse
    </div>
</x-app-layout>
