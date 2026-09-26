<x-app-layout>
    <!-- PAGE HEADER -->
    <div style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
        <div>
            <p style="color: var(--accent-primary); font-weight: 700; text-transform: uppercase; font-size: 0.875rem; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Booking Details</p>
            <h1 class="page-title" style="font-size: 3.5rem;">{{ $booking->concert->title }}</h1>
            <p style="color: var(--accent-secondary); font-size: 1.1rem; font-weight: 600; margin-top: 1rem;">by {{ $booking->concert->artist }}</p>
            <a href="{{ route('bookings.index') }}" class="btn btn-primary btn-small" style="margin-top: 1rem;">Back</a>
        </div>
    </div>

    <div class="grid-2 gap-8">
        <!-- MAIN: BOOKING INFO -->
        <div class="card">
            <div class="card-header" style="justify-content: space-between;">
                <h3 class="card-title">EVENT & TICKETS</h3>
                <span class="badge {{ $booking->status === 'confirmed' ? 'badge-success' : ($booking->status === 'cancelled' ? 'badge-danger' : 'badge-warning') }}" style="padding: 0.75rem 1.25rem;">{{ strtoupper($booking->status) }}</span>
            </div>

            <div class="card-body">
                <!-- Event Summary -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 2px solid rgba(255, 102, 0, 0.3);">
                    <div>
                        <p style="color: var(--text-tertiary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Venue</p>
                        <p style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $booking->concert->venue->name }}</p>
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">{{ $booking->concert->venue->location }}</p>
                    </div>
                    <div>
                        <p style="color: var(--text-tertiary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Date & Time</p>
                        <p style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $booking->concert->date->format('M d, Y') }}</p>
                        <p style="color: var(--text-secondary); font-size: 0.95rem;">{{ $booking->concert->time->format('g:i A') }}</p>
                    </div>
                </div>

                <!-- Tickets List -->
                <h4 style="font-size: 1rem; font-weight: 700; text-transform: uppercase; margin-bottom: 1.5rem; color: var(--accent-primary);">Your Tickets ({{ $tickets->total() }})</h4>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($tickets as $ticket)
                        <div style="padding: 1.25rem; border-left: 3px solid var(--accent-primary); background-color: rgba(255, 102, 0, 0.05);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <div>
                                    @if($ticket->seat)
                                        <p style="color: var(--text-tertiary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 0.25rem;">Seat Number</p>
                                        <p style="font-size: 1.5rem; font-weight: 800;">{{ $ticket->seat->seat_number }}</p>
                                        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-top: 0.25rem;">Section: {{ $ticket->seat->section }}</p>
                                    @else
                                        <p style="color: var(--text-tertiary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 0.25rem;">Ticket Type</p>
                                        <p style="font-size: 1.5rem; font-weight: 800;">{{ $ticket->ticket_type }}</p>
                                        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-top: 0.25rem;">No seat assigned</p>
                                    @endif
                                </div>
                                <div style="text-align: right;">
                                    <p style="color: var(--text-tertiary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 0.25rem;">Price</p>
                                    <p style="font-size: 1.5rem; font-weight: 800; color: var(--accent-primary);">₱{{ number_format($ticket->price_at_purchase, 2) }}</p>
                                </div>
                            </div>
                            @if($ticket->qr_code)
                                <div style="padding: 1rem; background-color: rgba(255, 255, 255, 0.05); border-radius: 0; display: grid; gap: 0.75rem; justify-items: center;">
                                    <p style="color: var(--text-tertiary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 0.5rem;">QR Code</p>
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($ticket->qr_code) }}" alt="QR Code" style="max-width: 200px; width: 100%; height: auto; background: white; padding: 0.5rem;" />
                                    <p style="color: var(--text-secondary); font-family: monospace; font-size: 0.75rem; word-break: break-all; margin-top: 0.25rem;">{{ $ticket->qr_code }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="ticket-pagination" style="margin-top: 1rem;">
                    {{ $tickets->links('vendor.pagination.booking') }}
                </div>
            </div>
        </div>

        <!-- SIDEBAR: PAYMENT INFO -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">PAYMENT</h3>
            </div>

            <div class="card-body">
                @if($booking->payment)
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div style="padding: 1.5rem; border-left: 3px solid var(--accent-primary); background-color: rgba(255, 102, 0, 0.05);">
                            <p style="color: var(--text-tertiary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Total Amount</p>
                            <p style="font-size: 2.5rem; font-weight: 800; color: var(--accent-primary);">₱{{ number_format($booking->payment->amount, 2) }}</p>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div style="padding: 1rem; background-color: rgba(255, 102, 0, 0.05); border-left: 2px solid var(--accent-primary);">
                                <p style="color: var(--text-tertiary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 0.5rem;">Card Number</p>
                                <p style="font-weight: 700;">{{ $booking->payment->masked_payment_method }}</p>
                            </div>
                            <div style="padding: 1rem; background-color: rgba(255, 102, 0, 0.05); border-left: 2px solid var(--accent-primary);">
                                <p style="color: var(--text-tertiary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 0.5rem;">Status</p>
                                <span class="badge {{ $booking->payment->status === 'paid' ? 'badge-success' : 'badge-danger' }}" style="padding: 0.5rem 0.75rem;">{{ strtoupper($booking->payment->status) }}</span>
                            </div>
                        </div>

                        @if($booking->payment->paid_at)
                            <div style="padding: 1rem; background-color: rgba(255, 102, 0, 0.05); border-left: 2px solid var(--accent-primary);">
                                <p style="color: var(--text-tertiary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 0.5rem;">Paid On</p>
                                <p style="font-weight: 600;">{{ $booking->payment->paid_at->format('F j, Y') }}</p>
                                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem;">{{ $booking->payment->paid_at->format('g:i A') }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div style="padding: 2rem; text-align: center; background-color: rgba(255, 102, 0, 0.05); border-left: 2px solid var(--accent-primary);">
                        <p style="color: var(--text-secondary);">Payment information not available yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
