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
            <div style="text-align: center; padding: 1rem; background: rgba(255, 102, 0, 0.08); border-left: 4px solid var(--accent-primary);">
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
        <p style="color: var(--accent-primary); font-weight: 700; text-transform: uppercase; font-size: 0.875rem; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Complete Your Purchase</p>
        <h1 class="page-title" style="font-size: 3.5rem;">CHECKOUT</h1>
        <p style="color: var(--text-secondary); font-size: 1.1rem; margin-top: 1rem;">{{ $concert->title }} • {{ $concert->date->format('M d, Y') }} • {{ $concert->venue->name }}</p>
    </div>

    <div class="grid-2 gap-8">
        <!-- MAIN: PAYMENT FORM -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">PAYMENT DETAILS</h3>
                <p style="color: var(--text-secondary); font-size: 0.95rem;">Enter your card details to complete this purchase.</p>
            </div>

            <form id="checkout-main-form" action="{{ route('bookings.confirm-payment', $concert) }}" method="POST">
                @csrf
                <input type="hidden" name="booking_items" value="{{ old('booking_items', $bookingPayloadJson ?? '') }}">

                @if ($errors->any())
                    <div style="margin-bottom: 1.5rem; padding: 1rem; border: 1px solid rgba(248, 113, 113, 0.4); background: rgba(254, 226, 226, 0.2); border-radius: 0.5rem; color: #991b1b;">
                        <ul style="margin: 0; padding-left: 1.25rem; list-style: disc;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card-body">
                    <div style="margin-bottom: 2rem;">
                        <div style="padding: 1.5rem; background: rgba(255, 255, 255, 0.03); border-radius: 0.5rem; border: 1px solid rgba(148, 163, 184, 0.2);">
                            <h4 style="font-weight: 700; margin-bottom: 1rem; color: var(--text-primary);">Payment Information</h4>
                            <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 1rem;">
                                Provide the card details used for this purchase.
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div>
                                    <label for="card_number" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">Card Number</label>
                                    <input type="text" name="card_number" placeholder="1234 5678 9012 3456" inputmode="numeric" autocomplete="cc-number" maxlength="23" required oninput="let r=this.value.replace(/\D/g,'').slice(0,19);this.value=r.replace(/(\d{4})(?=\d)/g,'$1 ');this.dataset.raw=r;this.setCustomValidity('');" oninvalid="this.setCustomValidity('Please enter a valid card number (13–19 digits).')" onblur="this.value=this.value.replace(/\s+/g,'')" style="width:100%;padding:0.75rem 1rem;border:1px solid rgba(0,0,0,0.12);border-radius:0.25rem;font-size:0.95rem;" value="{{ old('card_number') }}">
                                    @error('card_number')
                                        <p style="margin-top: 0.5rem; color: #f87171; font-size: 0.9rem;">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <label for="expiry" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">Expiry Date</label>
                                        <input type="text" id="expiry" name="expiry" placeholder="MM/YY" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid rgba(0,0,0,0.12); border-radius: 0.25rem; font-size: 0.95rem;" value="{{ old('expiry') }}" required>
                                        @error('expiry')
                                            <p style="margin-top: 0.5rem; color: #f87171; font-size: 0.9rem;">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="cvv" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">CVV</label>
                                        <input type="text" id="cvv" name="cvv" placeholder="123" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid rgba(0,0,0,0.12); border-radius: 0.25rem; font-size: 0.95rem;" value="{{ old('cvv') }}" required>
                                        @error('cvv')
                                            <p style="margin-top: 0.5rem; color: #f87171; font-size: 0.9rem;">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="cardholder_name" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">Cardholder Name</label>
                                    <input type="text" id="cardholder_name" name="cardholder_name" placeholder="John Doe" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid rgba(0,0,0,0.12); border-radius: 0.25rem; font-size: 0.95rem;" value="{{ old('cardholder_name') }}" required>
                                    @error('cardholder_name')
                                        <p style="margin-top: 0.5rem; color: #f87171; font-size: 0.9rem;">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div style="margin-bottom: 2rem;">
                        <input type="hidden" name="terms" value="0">
                        <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} style="margin-top: 0.25rem; width: 1.1rem; height: 1.1rem; accent-color: #f97316; border: 2px solid rgba(255,255,255,0.8); background: transparent; appearance: auto;">
                            <span style="font-size: 0.95rem; color: var(--text-secondary);">
                                I agree to the <a href="#" style="color: var(--accent-primary); text-decoration: underline;">Terms and Conditions</a> and <a href="#" style="color: var(--accent-primary); text-decoration: underline;">Privacy Policy</a>.
                            </span>
                        </label>
                        @error('terms')
                            <p style="margin-top: 0.5rem; color: #f87171; font-size: 0.9rem;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </form>

            <div class="card-footer" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <form action="{{ route('bookings.review', $concert) }}" method="POST" style="flex: 1; min-width: 150px; margin: 0;">
                    @csrf
                    <input type="hidden" name="booking_items" value="{{ old('booking_items', $bookingPayloadJson ?? '') }}">
                    <button type="submit" class="btn btn-secondary" style="width: 100%; text-align: center;">Back</button>
                </form>
                <button type="submit" form="checkout-main-form" class="btn btn-primary" style="flex: 1; min-width: 150px; font-weight: 700; letter-spacing: 0.05em;">CONFIRM PAYMENT</button>
            </div>
        </div>

        <!-- SIDEBAR: ORDER SUMMARY -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ORDER SUMMARY</h3>
            </div>

            <div class="card-body">
                <!-- Event Details -->
                <div style="margin-bottom: 2rem; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 0.5rem;">
                    <h4 style="font-weight: 700; margin-bottom: 0.75rem; color: var(--text-primary);">{{ $concert->title }}</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; color: var(--text-secondary); font-size: 0.9rem;">
                        <div>{{ $concert->date->format('M d, Y') }} at {{ $concert->time->format('g:i A') }}</div>
                        <div>{{ $concert->venue->name }}</div>
                    </div>
                </div>

                <!-- Selected Tickets -->
                <div style="margin-bottom: 2rem;">
                    <h4 style="font-weight: 700; margin-bottom: 1rem; color: var(--text-primary);">Tickets</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($cartItems as $item)
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 600; color: var(--text-primary);">
                                        {{ $item['quantity'] ?? 1 }} × {{ $item['ticket_type'] }}
                                    </div>
                                    @if(isset($item['seat_number']))
                                        <div style="font-size: 0.875rem; color: var(--text-secondary);">
                                            Seat {{ $item['seat_number'] }}
                                        </div>
                                    @endif
                                </div>
                                <div style="font-weight: 600; color: var(--accent-primary);">
                                    ₱{{ number_format($priceRecords[$item['concert_ticket_type_id']] * ($item['quantity'] ?? 1), 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Total -->
                <div style="border-top: 1px solid rgba(148, 163, 184, 0.2); padding-top: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span style="font-weight: 600;">Subtotal</span>
                        <span>₱{{ number_format($totalPrice, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span style="font-weight: 600;">Processing Fee</span>
                        <span>₱0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 1.25rem; font-weight: 700; color: var(--accent-primary); border-top: 1px solid rgba(148, 163, 184, 0.2); padding-top: 0.75rem;">
                        <span>Total</span>
                        <span>₱{{ number_format($totalPrice, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>