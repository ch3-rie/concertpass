<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; align-items: center;">
            <div style="text-align: center; padding: 1rem; background: rgba(255, 102, 0, 0.08); border-left: 4px solid var(--accent-primary);">
                <p style="font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.5rem; color: var(--text-tertiary);">Step 1</p>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 700;">Choose Tickets</h3>
            </div>
            <div style="text-align: center; padding: 1rem; background: rgba(255, 102, 0, 0.08); border-left: 4px solid var(--accent-primary);">
                <p style="font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.5rem; color: var(--text-tertiary);">Step 2</p>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 700;">Review Order</h3>
            </div>
            <div style="text-align: center; padding: 1rem; background: rgba(0, 0, 0, 0.03); border-left: 4px solid transparent;">
                <p style="font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.5rem; color: var(--text-tertiary);">Step 3</p>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 700;">Checkout</h3>
            </div>
            <div style="text-align: center; padding: 1rem; background: rgba(0, 0, 0, 0.03); border-left: 4px solid transparent;">
                <p style="font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.5rem; color: var(--text-tertiary);">Step 4</p>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 700;">Get Tickets</h3>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 3rem;">
        <p style="color: var(--accent-primary); font-weight: 700; text-transform: uppercase; font-size: 0.875rem; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Review Your Order</p>
        <h1 class="page-title" style="font-size: 3.5rem;">REVIEW ORDER</h1>
        <p style="color: var(--text-secondary); font-size: 1.1rem; margin-top: 1rem;">{{ $concert->title }} • {{ $concert->date->format('M d, Y') }} • {{ $concert->venue->name }}</p>
    </div>

    <div class="grid-2 gap-8">
        <!-- MAIN: ORDER SUMMARY -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ORDER SUMMARY</h3>
            </div>

            <div class="card-body">
                <!-- Event Details -->
                <div style="margin-bottom: 2rem; padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border-radius: 0.5rem;">
                    <h4 style="font-weight: 700; margin-bottom: 1rem; color: var(--text-primary);">Event Details</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; color: var(--text-secondary);">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Event</span>
                            <span style="font-weight: 600;">{{ $concert->title }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Date</span>
                            <span>{{ $concert->date->format('M d, Y') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Time</span>
                            <span>{{ $concert->time->format('g:i A') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Venue</span>
                            <span>{{ $concert->venue->name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Selected Tickets -->
                <div style="margin-bottom: 2rem;">
                    <h4 style="font-weight: 700; margin-bottom: 1rem; color: var(--text-primary);">Selected Tickets</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($cartItems as $item)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 0.5rem; border-left: 4px solid var(--accent-primary);">
                                <div>
                                    <div style="font-weight: 700; color: var(--text-primary);">
                                        {{ $item['quantity'] ?? 1 }} × {{ $item['ticket_type'] }}
                                    </div>
                                    @if(isset($item['seat_number']))
                                        <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                            Seat {{ $item['seat_number'] }}
                                        </div>
                                    @endif
                                </div>
                                <div style="font-weight: 700; color: var(--accent-primary);">
                                    ₱{{ number_format($priceRecords[$item['concert_ticket_type_id']] * ($item['quantity'] ?? 1), 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Total -->
                <div style="border-top: 1px solid rgba(148, 163, 184, 0.2); padding-top: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary);">Total</span>
                        <span style="font-size: 1.5rem; font-weight: 700; color: var(--accent-primary);">₱{{ number_format($totalPrice, 2) }}</span>
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.5rem;">
                        {{ $totalQuantity }} ticket{{ $totalQuantity > 1 ? 's' : '' }}
                    </div>
                </div>
            </div>

            <div class="card-footer" style="display: flex; gap: 1rem;">
                <a href="{{ route('bookings.create', $concert) }}" class="btn btn-secondary" style="flex: 1;">Back</a>
                <form action="{{ route('bookings.checkout', $concert) }}" method="POST" style="flex: 1; margin: 0;">
                    @csrf
                    <input type="hidden" name="booking_items" value="{{ $bookingPayloadJson }}">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Proceed to Checkout</button>
                </form>
            </div>
        </div>

        <!-- SIDEBAR: EVENT INFO -->
        <div class="card no-hover">
            <div class="card-header">
                <h3 class="card-title">EVENT INFO</h3>
            </div>

            <div class="card-body">
                <div style="border-left: 3px solid var(--accent-primary); padding-left: 1.5rem; margin-bottom: 2rem;">
                    <h4 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $concert->title }}</h4>
                    <p style="color: var(--accent-secondary); font-weight: 600; margin-bottom: 1rem;">by {{ $concert->artist }}</p>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem; color: var(--text-secondary); font-size: 0.95rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Date</span>
                            <span>{{ $concert->date->format('M d, Y') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Time</span>
                            <span>{{ $concert->time->format('g:i A') }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Venue</span>
                            <span>{{ $concert->venue->name }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Tickets released</span>
                            <span>{{ number_format($eventTicketTotal) }}</span>
                        </div>
                    </div>
                </div>

                @if($concert->seat_plan_image)
                    <div style="margin-bottom: 1.5rem;">
                        <img src="{{ asset('storage/' . $concert->seat_plan_image) }}" alt="Seat Plan" style="width: 100%; height: auto; border-radius: 0.5rem;" />
                        <p style="font-size: 0.8rem; color: var(--text-secondary); text-align: center; margin-top: 0.5rem;">Reference seat plan</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>