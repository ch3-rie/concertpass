<x-app-layout>
    <div style="margin-bottom: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; align-items: center;">
            <div style="text-align: center; padding: 1rem; background: rgba(255, 102, 0, 0.08); border-left: 4px solid var(--accent-primary);">
                <p style="font-size: 0.75rem; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.5rem; color: var(--text-tertiary);">Step 1</p>
                <h3 style="margin: 0; font-size: 1rem; font-weight: 700;">Choose Tickets</h3>
            </div>
            <div style="text-align: center; padding: 1rem; background: rgba(0, 0, 0, 0.03); border-left: 4px solid transparent;">
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
        <p style="color: var(--accent-primary); font-weight: 700; text-transform: uppercase; font-size: 0.875rem; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Ticket Selection</p>
        <h1 class="page-title" style="font-size: 3.5rem;">CHOOSE YOUR TICKETS</h1>
        <p style="color: var(--text-secondary); font-size: 1.1rem; margin-top: 1rem;">{{ $concert->title }} • {{ $concert->date->format('M d, Y') }} • {{ $concert->venue->name }}</p>
    </div>

    <div class="grid-2 gap-8">
        <!-- MAIN: TICKET SELECTION FORM -->
        <div class="card no-hover">
            <div class="card-header">
                <div>
                    <h3 class="card-title">TICKET TYPES</h3>
                    <p style="color: var(--text-secondary); font-size: 0.95rem;">Max 5 tickets per transaction</p>
                </div>
            </div>

                <form action="{{ route('bookings.store', $concert) }}" method="POST">
                @csrf
                @error('booking_items')
                    <div style="background: #fee2e2; border: 1px solid #fecaca; border-radius: 0.5rem; padding: 0.85rem 1rem; margin-bottom: 1rem; color: #991b1b; font-size: 0.95rem;">{{ $message }}</div>
                @enderror
                @php $ticketPrices = $concert->ticketPrices; @endphp

                <div class="card-body">
                    <!-- Capacity Check Warning -->
                    @if($remainingEventTickets <= 0)
                        <div style="background: #fee2e2; border: 1px solid #fecaca; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; text-align: center;">
                            <p style="color: #dc2626; font-weight: 600; margin: 0;">🎫 SOLD OUT</p>
                            <p style="color: #991b1b; margin-top: 0.5rem;">All tickets allocated for this event have been sold.</p>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-secondary w-full" disabled>Tickets Unavailable</button>
                        </div>
                    @else
                        <!-- Ticket Type Selection -->
                        <div style="margin-bottom: 1.5rem;">
                            <label for="ticket_type" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">Ticket Type</label>
                            <select id="ticket_type" name="ticket_type" required style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 0.25rem; font-size: 0.95rem; background: var(--bg-tertiary); color: var(--text-primary);">
                                <option value="">Select ticket type</option>
                                @foreach($ticketPrices as $ticketPrice)
                                    <option
                                        value="{{ $ticketPrice->id }}"
                                        data-price="{{ $ticketPrice->price }}"
                                        data-ticket-slug="{{ $ticketPrice->ticketType->name }}"
                                        data-section="{{ $ticketPrice->section }}"
                                        data-remaining="{{ $ticketAvailability[$ticketPrice->id] ?? 0 }}"
                                    >
                                        {{ $ticketPrice->section }} - ₱{{ number_format($ticketPrice->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Quantity Selection (for VIP and Gen Ad) -->
                        <div id="quantity-section" style="margin-bottom: 1.5rem; display: none;">
                            <label for="ticket_quantity" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">Quantity</label>
                            <div style="display: grid; grid-template-columns: 1fr 150px; gap: 0.75rem; align-items: flex-end; margin-bottom: 1.5rem;">
                                <select id="ticket_quantity" name="ticket_quantity" style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 0.25rem; font-size: 0.95rem; background: var(--bg-tertiary); color: var(--text-primary);">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                                <button type="button" id="add-quantity-ticket-btn" style="padding: 0.75rem 1rem; background: var(--accent-primary); color: white; border: none; border-radius: 0.25rem; font-weight: 700; cursor: pointer; white-space: nowrap; width: 100%;">+ ADD TICKET</button>
                            </div>
                        </div>

                        <!-- Seat Selection (for UBB and LBB) -->
                        <div id="seat-selection" style="margin-bottom: 1.5rem; display: none;">
                            <label for="seat_dropdown_toggle" style="display: block; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-primary);">Select Seat</label>
                            <div style="display: grid; grid-template-columns: 1fr 150px; gap: 0.75rem; align-items: flex-end; margin-bottom: 1.5rem; position: relative;">
                                <div class="custom-seat-dropdown" style="position: relative; width: 100%;">
                                    <button type="button" id="seat_dropdown_toggle" class="seat-dropdown-toggle" style="width: 100%; text-align: left; padding: 0.75rem 1rem; border: 1px solid rgba(148,163,184,0.25); border-radius: 0.25rem; font-size: 0.95rem; color: var(--text-primary); background: var(--bg-secondary); cursor: pointer;">
                                        Choose a seat...
                                    </button>
                                </div>
                                <input type="hidden" id="seat_dropdown" name="seat_dropdown" value="">
                                <button type="button" id="add-seat-ticket-btn" style="padding: 0.75rem 1rem; background: var(--accent-primary); color: white; border: none; border-radius: 0.25rem; font-weight: 700; cursor: pointer; white-space: nowrap; width: 100%;">+ ADD TICKET</button>
                            </div>
                        </div>

                        <!-- Added Tickets List -->
                        <div id="added-tickets" style="margin-bottom: 1rem; padding: 0.85rem; background: rgba(255, 102, 0, 0.05); border-left: 4px solid var(--accent-primary); border-radius: 0.5rem; display: none;">
                            <h4 style="font-weight: 700; margin-bottom: 0.75rem; color: var(--text-primary); font-size: 1rem;">Selected Tickets</h4>
                            <div id="tickets-list" style="display: flex; flex-direction: column; gap: 0.5rem;"></div>
                        </div>

                        <div id="total-preview" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(148, 163, 184, 0.12); border-radius: 0.5rem; padding: 1rem; text-align: center; margin-bottom: 1rem; display: none;">
                            <div style="font-size: 1.25rem; font-weight: 700; color: var(--accent-primary); margin-bottom: 0.25rem;" id="total-price">$0.00</div>
                            <div style="font-size: 0.875rem; color: var(--text-secondary);" id="total-details"></div>
                            <div id="selected-seats-summary" style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.5rem; display: none;">
                                <strong>Seats:</strong> <span id="seats-summary-text"></span>
                            </div>
                        </div>

                        <!-- Hidden inputs for cart items -->
                        <input type="hidden" id="booking_items" name="booking_items" value="">

                        <div style="text-align: center; color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1.5rem;">
                            <strong>Max 5 tickets per purchase</strong> | Seats are limited to the first available seats in the selected section
                        </div>
                    @endif
                </div>

                <div class="card-footer">
                    <button id="checkout-button" type="submit" class="btn btn-primary w-full" style="font-weight: 700; letter-spacing: 0.05em;" disabled>SELECT TICKET TYPE</button>
                </div>
            </form>
        </div>

        <!-- Seat Dropdown Panel (moved outside card to avoid clipping) -->
        <div id="seat_dropdown_panel" style="display: none; position: fixed; z-index: 9999; background: var(--bg-secondary); border: 1px solid rgba(148,163,184,0.25); border-radius: 0.5rem; box-shadow: 0 18px 40px rgba(15,23,42,0.35); max-height: 280px; overflow: hidden;">
            <div style="padding: 0.75rem;">
                <input id="seat_dropdown_search" type="text" placeholder="Search seat number..." style="width: 100%; padding: 0.75rem 1rem; border: 1px solid rgba(148,163,184,0.35); border-radius: 0.35rem; font-size: 0.95rem; color: var(--text-primary); background: var(--bg-tertiary);" aria-label="Search seat number">
            </div>
            <div id="seat_dropdown_list" style="max-height: 200px; overflow-y: auto; border-top: 1px solid rgba(148,163,184,0.15);"></div>
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
                        <p style="font-size: 0.8rem; color: var(--text-secondary); text-align: center; margin-top: 0.5rem;">Reference seat plan - tickets assigned within type</p>
                    </div>
                @else
                    <div style="margin-bottom: 1.5rem; padding: 1rem; border: 1px dashed rgba(148, 163, 184, 0.5); border-radius: 0.5rem; background: rgba(255,255,255,0.02); text-align: center; color: var(--text-secondary);">
                        <p style="margin: 0; font-weight: 700;">No Seat Plan Available</p>
                        <p style="margin: 0.5rem 0 0; font-size: 0.85rem;">Upload a seat plan image in the concert admin panel to show it here.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div style="margin-top: 1.5rem;">
        <div class="card no-hover">
            <div class="card-header">
                <h3 class="card-title">All Available Prices</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    @foreach($ticketPrices as $price)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; background: rgba(255, 255, 255, 0.03); border-left: 4px solid {{ $price->color ?? '#777' }}; border-radius: 0.5rem;">
                            <span>{{ $price->section }}</span>
                            <span style="font-weight: 700;">₱{{ number_format($price->price, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ticketTypeSelect = document.getElementById('ticket_type');
            const quantitySelect = document.getElementById('ticket_quantity');
            const quantitySection = document.getElementById('quantity-section');
            const seatSelection = document.getElementById('seat-selection');
            const seatDropdownHidden = document.getElementById('seat_dropdown');
            const seatDropdownToggle = document.getElementById('seat_dropdown_toggle');
            const seatDropdownPanel = document.getElementById('seat_dropdown_panel');
            const seatDropdownSearch = document.getElementById('seat_dropdown_search');
            const seatDropdownList = document.getElementById('seat_dropdown_list');
            const addQuantityTicketBtn = document.getElementById('add-quantity-ticket-btn');
            const addSeatTicketBtn = document.getElementById('add-seat-ticket-btn');
            const addedTicketsDiv = document.getElementById('added-tickets');
            const ticketsList = document.getElementById('tickets-list');
            const cartItemsInput = document.getElementById('booking_items');
            const totalPreview = document.getElementById('total-preview');
            const totalPriceEl = document.getElementById('total-price');
            const totalDetailsEl = document.getElementById('total-details');
            const selectedSeatsSummary = document.getElementById('selected-seats-summary');
            const seatsSummaryText = document.getElementById('seats-summary-text');
            const checkoutButton = document.getElementById('checkout-button');

            let seatsData = [];
            let seatDropdownOptions = [];
            let cartItems = [];
            let loadingSeats = false;
            const concertId = {{ $concert->id }};
            const maxTickets = 5;
            const seatRequiredSlugs = ['VIP Seated', 'LBB', 'UBB', 'LBA', 'UBA'];
            const seatSectionMap = {
                'VIP Seated': 'VIP Seated',
                'LBB': 'Lower Box B (LBB)',
                'UBB': 'Upper Box B (UBB)',
                'LBA': 'Lower Box A (LBA)',
                'UBA': 'Upper Box A (UBA)'
            };

            function getCartQuantity() {
                return cartItems.reduce((sum, item) => sum + (item.quantity ?? 1), 0);
            }

            function getRemainingForSelectedType() {
                const selected = ticketTypeSelect.selectedOptions[0];
                if (!selected || !selected.value) {
                    return 0;
                }
                const raw = selected.dataset.remaining;
                const n = parseInt(raw, 10);
                return Number.isFinite(n) ? Math.max(0, n) : 0;
            }

            function getReservedQuantityForTicketType(typeId) {
                const idStr = String(typeId);
                return cartItems.reduce((sum, item) => {
                    if (String(item.concert_ticket_type_id) !== idStr) {
                        return sum;
                    }
                    return sum + (item.quantity ?? 1);
                }, 0);
            }

            function refreshQuantityOptions() {
                if (!quantitySelect) {
                    return;
                }
                const type = ticketTypeSelect.value;
                const remaining = getRemainingForSelectedType();
                const reserved = getReservedQuantityForTicketType(type);
                const cartQty = getCartQuantity();
                const maxPerBooking = Math.max(0, maxTickets - cartQty + reserved);
                const maxAddable = Math.max(0, Math.min(remaining - reserved, maxPerBooking, maxTickets));
                const prev = quantitySelect.value;
                quantitySelect.innerHTML = '';
                const upper = Math.min(maxTickets, maxAddable);
                for (let i = 1; i <= upper; i++) {
                    const opt = document.createElement('option');
                    opt.value = String(i);
                    opt.textContent = String(i);
                    quantitySelect.appendChild(opt);
                }
                if (quantitySelect.options.length === 0) {
                    const opt = document.createElement('option');
                    opt.value = '0';
                    opt.textContent = remaining <= reserved ? 'Sold out' : 'None left';
                    opt.disabled = true;
                    quantitySelect.appendChild(opt);
                }
                const parsedPrev = parseInt(prev, 10);
                if (Number.isFinite(parsedPrev) && parsedPrev >= 1 && parsedPrev <= upper) {
                    quantitySelect.value = String(parsedPrev);
                } else if (quantitySelect.options.length && !quantitySelect.options[0].disabled) {
                    quantitySelect.selectedIndex = 0;
                }
            }

            function getTicketPrice(type) {
                const option = Array.from(ticketTypeSelect.options).find(opt => opt.value === type);
                return option ? parseFloat(option.dataset.price) : 0;
            }

            function getTicketSlug(type) {
                const option = Array.from(ticketTypeSelect.options).find(opt => opt.value === type);
                return option ? option.dataset.ticketSlug : '';
            }

            function getTicketDisplayName(type) {
                const option = Array.from(ticketTypeSelect.options).find(opt => opt.value === type);
                return option ? option.dataset.section || option.dataset.ticketSlug : '';
            }

            function getTicketSection(type) {
                const slug = getTicketSlug(type);
                return seatSectionMap[slug] || '';
            }

            // Load seats data
            async function loadSeats() {
                if (loadingSeats) {
                    return;
                }
                loadingSeats = true;
                try {
                    const ticketTypeId = ticketTypeSelect.value;
                    const response = await fetch(`/concerts/${concertId}/seats?concert_ticket_type_id=${encodeURIComponent(ticketTypeId)}`);
                    const payload = await response.json();
                    if (!response.ok || !payload.success) {
                        seatsData = [];
                        alert(payload.message || 'Unable to load seats right now. Please try again.');
                    } else {
                        const list = payload.data && Array.isArray(payload.data.seats) ? payload.data.seats : [];
                        seatsData = list;
                    }
                    populateSeatDropdown();
                } catch (error) {
                    console.error('Error loading seats:', error);
                    seatsData = [];
                    populateSeatDropdown();
                } finally {
                    loadingSeats = false;
                }
            }

            // Populate seat dropdown
            function populateSeatDropdown() {
                const selectedOption = ticketTypeSelect.selectedOptions[0];
                const ticketSlug = selectedOption ? selectedOption.dataset.ticketSlug : '';
                const ticketSection = seatSectionMap[ticketSlug] || '';
                seatDropdownHidden.value = '';
                seatDropdownToggle.textContent = ticketSection ? 'Choose a seat...' : 'Select ticket type first';
                seatDropdownSearch.value = '';
                seatDropdownPanel.style.display = 'none';

                seatDropdownOptions = seatsData
                    .filter(seat => ticketSection && seat.section === ticketSection)
                    .sort((a, b) => parseInt(a.seat_number, 10) - parseInt(b.seat_number, 10));

                renderSeatDropdown(seatDropdownOptions);
            }

            function renderSeatDropdown(options, query = '') {
                const normalizedQuery = query.trim().toLowerCase();
                const filteredOptions = normalizedQuery
                    ? options.filter(seat => seat.seat_number.toString().toLowerCase().includes(normalizedQuery))
                    : options;

                seatDropdownList.innerHTML = '';

                if (!filteredOptions.length) {
                    const emptyMessage = document.createElement('div');
                    emptyMessage.style.cssText = 'padding: 0.75rem 1rem; color: var(--text-secondary);';
                    emptyMessage.textContent = ticketDropdownEmptyMessage();
                    seatDropdownList.appendChild(emptyMessage);
                    return;
                }

                const fragment = document.createDocumentFragment();
                const maxVisible = 200;
                const visibleSeats = filteredOptions.slice(0, maxVisible);

                visibleSeats.forEach(seat => {
                    const isSelected = cartItems.some(item => item.seat_id == seat.id);
                    const seatLabel = `${seat.section} - Seat ${seat.seat_number}`;
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.style.cssText = 'width: 100%; text-align: left; padding: 0.75rem 1rem; border: none; border-bottom: 1px solid rgba(148,163,184,0.12); background: transparent; color: var(--text-primary); cursor: pointer; transition: background 150ms ease;';
                    button.textContent = seatLabel;

                    if (seat.status !== 'available') {
                        button.disabled = true;
                        button.style.color = 'rgba(148, 163, 184, 0.9)';
                        button.style.background = 'rgba(255,255,255,0.02)';
                        button.textContent += ' — Unavailable';
                    } else if (isSelected) {
                        button.disabled = true;
                        button.style.color = 'rgba(148, 163, 184, 0.9)';
                        button.style.background = 'rgba(255,255,255,0.02)';
                        button.textContent += ' — Selected';
                    } else {
                        button.addEventListener('click', () => {
                            seatDropdownHidden.value = JSON.stringify({ id: seat.id, number: seat.seat_number, section: seat.section });
                            seatDropdownToggle.textContent = seatLabel;
                            seatDropdownPanel.style.display = 'none';
                        });
                        button.addEventListener('mouseenter', () => {
                            button.style.background = 'rgba(255, 102, 0, 0.12)';
                        });
                        button.addEventListener('mouseleave', () => {
                            button.style.background = 'transparent';
                        });
                    }

                    fragment.appendChild(button);
                });

                seatDropdownList.appendChild(fragment);

                if (filteredOptions.length > maxVisible) {
                    const footer = document.createElement('div');
                    footer.style.cssText = 'padding: 0.75rem 1rem; color: var(--text-secondary); font-size: 0.85rem;';
                    footer.textContent = `Showing ${maxVisible} of ${filteredOptions.length} matching seats`;
                    seatDropdownList.appendChild(footer);
                }
            }

            function ticketDropdownEmptyMessage() {
                const selectedOption = ticketTypeSelect.selectedOptions[0];
                const ticketSlug = selectedOption ? selectedOption.dataset.ticketSlug : '';
                return ticketSlug ? 'No matching seats found.' : 'Select a seat type to show seats.';
            }

            seatDropdownToggle.addEventListener('click', async () => {
                if (!seatDropdownOptions.length) {
                    await loadSeats();
                }

                if (seatDropdownPanel.style.display === 'block') {
                    seatDropdownPanel.style.display = 'none';
                    return;
                }

                positionDropdownPanel();
                seatDropdownPanel.style.display = 'block';
                seatDropdownSearch.focus();
            });

            function positionDropdownPanel() {
                const rect = seatDropdownToggle.getBoundingClientRect();
                seatDropdownPanel.style.width = `${rect.width}px`;
                seatDropdownPanel.style.left = `${rect.left}px`;
                seatDropdownPanel.style.top = `${rect.bottom}px`;
            }

            // Reposition dropdown on scroll and resize
            window.addEventListener('scroll', () => {
                if (seatDropdownPanel.style.display === 'block') {
                    positionDropdownPanel();
                }
            });

            window.addEventListener('resize', () => {
                if (seatDropdownPanel.style.display === 'block') {
                    positionDropdownPanel();
                }
            });

            seatDropdownSearch.addEventListener('input', () => {
                renderSeatDropdown(seatDropdownOptions, seatDropdownSearch.value);
            });

            document.addEventListener('click', (event) => {
                if (!seatSelection.contains(event.target) && !seatDropdownPanel.contains(event.target)) {
                    seatDropdownPanel.style.display = 'none';
                }
            });

            function updateCartInput() {
                cartItemsInput.value = JSON.stringify(cartItems);
            }

            // Add ticket to the cart
            function addTicket() {
                const type = ticketTypeSelect.value;
                const ticketSection = getTicketSection(type);
                if (!type) {
                    alert('Please select a ticket type');
                    return;
                }

                const currentQuantity = getCartQuantity();
                if (currentQuantity >= maxTickets) {
                    alert(`Maximum ${maxTickets} tickets per booking`);
                    return;
                }

                const ticketSlug = getTicketSlug(type);
                if (seatRequiredSlugs.includes(ticketSlug)) {
                    if (!seatDropdownHidden.value) {
                        alert('Please select a seat');
                        return;
                    }

                    if (currentQuantity + 1 > maxTickets) {
                        alert(`Total tickets cannot exceed ${maxTickets}`);
                        return;
                    }

                    const seatData = JSON.parse(seatDropdownHidden.value);
                    cartItems.push({
                        concert_ticket_type_id: type,
                        ticket_type: ticketSection,
                        seat_id: seatData.id,
                        seat_number: seatData.number,
                        seat_section: seatData.section
                    });
                    populateSeatDropdown();
                } else {
                    const quantity = parseInt(quantitySelect.value, 10);
                    if (quantity < 1) {
                        alert('Please choose at least one ticket');
                        return;
                    }

                    const remaining = getRemainingForSelectedType();
                    const reserved = getReservedQuantityForTicketType(type);
                    if (reserved + quantity > remaining) {
                        const left = Math.max(0, remaining - reserved);
                        alert(left <= 0
                            ? 'No tickets left for this type.'
                            : `Only ${left} ticket(s) left for this type.`);
                        return;
                    }

                    if (currentQuantity + quantity > maxTickets) {
                        alert(`Total tickets cannot exceed ${maxTickets}`);
                        return;
                    }

                    const existingIndex = cartItems.findIndex(item => String(item.concert_ticket_type_id) === String(type) && item.quantity);
                    if (existingIndex !== -1) {
                        cartItems[existingIndex].quantity += quantity;
                    } else {
                        cartItems.push({
                            concert_ticket_type_id: type,
                            ticket_type: getTicketDisplayName(type),
                            quantity: quantity
                        });
                    }
                }

                updateCartInput();
                updateCartDisplay();
                updatePreview();
                refreshQuantityOptions();
            }

            // Update tickets display
            function updateCartDisplay() {
                ticketsList.innerHTML = '';
                if (cartItems.length > 0) {
                    cartItems.forEach((item, index) => {
                        const ticketTag = document.createElement('div');
                        ticketTag.style.cssText = 'display: flex; justify-content: space-between; align-items: center; background: rgba(255, 255, 255, 0.5); padding: 0.5rem 0.75rem; border-radius: 0.25rem; border-left: 3px solid var(--accent-primary); gap: 0.75rem; border: 1px solid rgba(0,0,0,0.12);';
                        
                        const ticketInfo = document.createElement('span');
                        ticketInfo.style.cssText = 'font-weight: 600; color: var(--text-primary); flex: 1; font-size: 0.95rem;';
                        ticketInfo.textContent = item.quantity
                            ? `${item.quantity} × ${item.ticket_type}`
                            : `${item.ticket_type} - ${item.seat_section || 'Seat'} ${item.seat_number}`;
                        
                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.className = 'remove-ticket-btn';
                        removeBtn.dataset.index = index;
                        removeBtn.setAttribute('aria-label', 'Remove ticket');
                        removeBtn.style.cssText = 'background: var(--accent-primary); color: white; border: none; padding: 0.35rem; border-radius: 0.25rem; cursor: pointer; font-size: 1rem; font-weight: 700; line-height: 1; width: 2rem; height: 2rem; display: inline-flex; align-items: center; justify-content: center;';
                        removeBtn.textContent = '×';

                        ticketTag.appendChild(ticketInfo);
                        ticketTag.appendChild(removeBtn);
                        ticketsList.appendChild(ticketTag);
                    });
                    addedTicketsDiv.style.display = 'block';

                    document.querySelectorAll('.remove-ticket-btn').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const index = parseInt(e.target.dataset.index, 10);
                            cartItems.splice(index, 1);
                            populateSeatDropdown();
                            updateCartDisplay();
                            updatePreview();
                            updateCartInput();
                            refreshQuantityOptions();
                        });
                    });
                } else {
                    addedTicketsDiv.style.display = 'none';
                }

                updateCartInput();
            }

            // Update preview
            function updatePreview() {
                const totalQuantity = getCartQuantity();
                if (totalQuantity === 0) {
                    totalPreview.style.display = 'none';
                    checkoutButton.disabled = true;
                    checkoutButton.textContent = 'ADD TICKETS';
                    return;
                }

                const total = cartItems.reduce((sum, item) => {
                    const price = getTicketPrice(item.concert_ticket_type_id);
                    return sum + price * (item.quantity ?? 1);
                }, 0);

                const details = cartItems.map(item => {
                    return item.quantity
                        ? `${item.quantity} × ${item.ticket_type}`
                        : `${item.ticket_type} - ${item.seat_section || 'Seat'} ${item.seat_number}`;
                }).join(', ');

                const seatItems = cartItems
                    .filter(item => item.seat_id)
                    .map(item => `${item.seat_section || 'Seat'} ${item.seat_number}`);

                totalPriceEl.textContent = `₱${total.toFixed(2)}`;
                totalDetailsEl.textContent = details;
                selectedSeatsSummary.style.display = seatItems.length ? 'block' : 'none';
                seatsSummaryText.textContent = seatItems.join(', ');
                totalPreview.style.display = 'block';
                checkoutButton.disabled = false;
                checkoutButton.textContent = `REVIEW ORDER (₱${total.toFixed(2)})`;
            }

            // Handle ticket type change
            ticketTypeSelect.addEventListener('change', function() {
                const ticketSlug = getTicketSlug(this.value);
                if (seatRequiredSlugs.includes(ticketSlug)) {
                    quantitySection.style.display = 'none';
                    seatSelection.style.display = 'block';
                    loadSeats();
                } else if (ticketSlug === 'VIP Standing' || ticketSlug === 'GEN AD') {
                    quantitySection.style.display = 'block';
                    seatSelection.style.display = 'none';
                    refreshQuantityOptions();
                } else {
                    quantitySection.style.display = 'none';
                    seatSelection.style.display = 'none';
                }

                updatePreview();
            });

            addQuantityTicketBtn.addEventListener('click', addTicket);
            addSeatTicketBtn.addEventListener('click', addTicket);

            refreshQuantityOptions();
            updatePreview();
        });
    </script>

    <style>
        .card.no-hover:hover {
            transform: none !important;
            box-shadow: var(--shadow-md) !important;
        }
        .card.no-hover:hover::after {
            opacity: 0 !important;
        }
    </style>
</x-app-layout>
