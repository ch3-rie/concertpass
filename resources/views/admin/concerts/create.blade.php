<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')
            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div><h2>Create Concert</h2><p>Register a new event with complete schedule, venue, and media details.</p></div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn"><span id="themeToggleIcon">◐</span></button>
                        <a href="{{ route('admin.concerts.index') }}" class="ad-btn">Back</a>
                    </div>
                </header>
                <section class="ad-card">
                    <h3 class="ad-panel-title">Concert Information</h3>
                    <form method="POST" action="{{ route('admin.concerts.store') }}" enctype="multipart/form-data" class="ad-form-grid-2">
                        @csrf
                        @php
                            $ticketOrder = ['VIP Standing', 'VIP Seated', 'LBA', 'LBB', 'UBA', 'UBB', 'GEN AD'];
                            $ticketTypes = $ticketTypes
                                ->unique('name')
                                ->sortBy(function ($type) use ($ticketOrder) {
                                    $index = array_search($type->name, $ticketOrder, true);
                                    return $index === false ? count($ticketOrder) : $index;
                                })
                                ->values();
                        @endphp
                        <div class="ad-field">
                            <label class="ad-label" for="title">Title</label>
                            <input class="ad-input" id="title" type="text" name="title" value="{{ old('title') }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="artist">Artist</label>
                            <input class="ad-input" id="artist" type="text" name="artist" value="{{ old('artist') }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="venue_id">Venue</label>
                            <select class="ad-select" id="venue_id" name="venue_id" required>
                                <option value="">Select venue</option>
                                @foreach($venues as $venue)
                                    <option value="{{ $venue->id }}" {{ old('venue_id') == $venue->id ? 'selected' : '' }}>{{ $venue->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="date">Date</label>
                            <input class="ad-input" id="date" type="date" name="date" value="{{ old('date') }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="time">Time</label>
                            <input class="ad-input" id="time" type="time" name="time" value="{{ old('time') }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="poster">Poster</label>
                            <input class="ad-input" id="poster" type="file" name="poster" accept="image/*" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="seat_plan_image">Seat Plan Image</label>
                            <input class="ad-input" id="seat_plan_image" type="file" name="seat_plan_image" accept="image/*" required>
                            <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.25rem;">Upload venue seat plan image for customer reference</p>
                        </div>
                        <div class="ad-field ad-field-full">
                            <label class="ad-label" for="description">Description</label>
                            <textarea class="ad-textarea" id="description" name="description">{{ old('description') }}</textarea>
                        </div>
                        <div class="ad-field ad-field-full ad-ticket-pricing-panel" style="border: 1px solid rgba(255,255,255,0.08); padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background: rgba(255,255,255,0.02);">
                            <h3 class="ad-panel-title" style="margin-bottom: 0.75rem;">Ticket Pricing & Types</h3>
                            <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 1rem;">Configure ticket types, prices, and quantities. Quantities determine how many tickets are available for each type.</p>

                            <div class="ad-grid-5" style="display: grid; grid-template-columns: minmax(0, 1.5fr) repeat(3, minmax(0, 1fr)) minmax(0, 0.9fr); gap: 1rem; align-items: end; margin-bottom: 1rem;">
                                <div>
                                    <label class="ad-label" for="ticket_type_select">Ticket Type</label>
                                    <select class="ad-select" id="ticket_type_select" aria-label="Ticket type">
                                        <option value="">Select ticket type</option>
                                        @foreach($ticketTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}{{ $type->description ? ' — '.$type->description : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="ad-label" for="ticket_price_input">Price (PHP)</label>
                                    <input class="ad-input" id="ticket_price_input" type="number" min="0" step="0.01" placeholder="0.00" aria-label="Ticket price">
                                </div>
                                <div>
                                    <label class="ad-label" for="ticket_quantity_input">Quantity</label>
                                    <input class="ad-input" id="ticket_quantity_input" type="number" min="0" placeholder="0" aria-label="Ticket quantity">
                                </div>
                                <div>
                                    <label class="ad-label" for="ticket_color_input">Ticket Color</label>
                                    <input class="ad-input" id="ticket_color_input" type="color" value="#ff6600" aria-label="Ticket color" style="width: 100%; height: 3rem; padding: 0.2rem;">
                                </div>
                                <div style="display: flex; align-items: center;">
                                    <button type="button" id="add_ticket_type_btn" class="ad-btn ad-btn-secondary" style="width: 100%; height: 3rem;">Add Ticket Type</button>
                                </div>
                            </div>

                            <div id="ticket_types_list" style="display: grid; gap: 0.75rem;"></div>

                            @error('ticket_types')
                                <p style="color: #f87171; font-size: 0.9rem; margin-top: 0.75rem;">{{ $message }}</p>
                            @enderror
                            @error('ticket_types.*.ticket_type_id')
                                <p style="color: #f87171; font-size: 0.9rem; margin-top: 0.75rem;">Please select a valid ticket type.</p>
                            @enderror
                            @error('ticket_types.*.price')
                                <p style="color: #f87171; font-size: 0.9rem; margin-top: 0.75rem;">Please provide a valid ticket price.</p>
                            @enderror
                            @error('ticket_types.*.quantity')
                                <p style="color: #f87171; font-size: 0.9rem; margin-top: 0.75rem;">Please provide a valid ticket quantity.</p>
                            @enderror
                            @error('ticket_types.*.color')
                                <p style="color: #f87171; font-size: 0.9rem; margin-top: 0.75rem;">Please provide a valid color code.</p>
                            @enderror
                        </div>
                        <div class="ad-field ad-field-full">
                            <div class="ad-actions-row">
                                <button class="ad-btn ad-btn-primary" type="submit">Create Concert</button>
                            </div>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </section>
    @include('admin.partials.theme-script')

    <script>
        const ticketTypes = @json($ticketTypes->map(fn($type) => ['id' => $type->id, 'name' => $type->name, 'description' => $type->description])->values());
        const existingTicketTypes = @json(old('ticket_types', []));

        const ticketTypeSelect = document.getElementById('ticket_type_select');
        const ticketPriceInput = document.getElementById('ticket_price_input');
        const ticketQuantityInput = document.getElementById('ticket_quantity_input');
        const ticketColorInput = document.getElementById('ticket_color_input');
        const addTicketTypeBtn = document.getElementById('add_ticket_type_btn');
        const ticketTypesList = document.getElementById('ticket_types_list');

        let selectedTicketTypes = existingTicketTypes.map((ticket) => ({
            ticket_type_id: ticket.ticket_type_id,
            price: ticket.price,
            quantity: ticket.quantity ?? 0,
            color: ticket.color,
        }));

        function renderTicketTypes() {
            ticketTypesList.innerHTML = '';

            selectedTicketTypes.forEach((ticket, index) => {
                const ticketType = ticketTypes.find((type) => type.id === Number(ticket.ticket_type_id));
                const label = ticketType ? `${ticketType.name}${ticketType.description ? ' — ' + ticketType.description : ''}` : 'Selected ticket';

                const row = document.createElement('div');
                row.className = 'ad-field';
                row.style = 'display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: center; padding: 1rem; border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; background: rgba(255,255,255,0.02);';
                row.innerHTML = `
                    <div>
                        <p style="margin: 0 0 0.25rem; font-weight: 700;">${label}</p>
                        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; align-items: center;">
                            <div style="font-size: 0.95rem; color: #cbd5e1;">Price: ₱${Number(ticket.price).toFixed(2)}</div>
                            <div style="font-size: 0.95rem; color: #cbd5e1;">Quantity: ${ticket.quantity}</div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: #cbd5e1;"><span style="width: 1rem; height: 1rem; display: inline-block; border-radius: 9999px; background: ${ticket.color};"></span>Color</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end; align-items: center;">
                        <button type="button" class="ad-btn ad-btn-secondary" style="padding: 0.6rem 1rem;" onclick="editTicketType(${index})">Edit</button>
                        <button type="button" class="ad-btn ad-btn-secondary" style="padding: 0.6rem 1rem;" onclick="removeTicketType(${index})">Remove</button>
                    </div>
                    <input type="hidden" name="ticket_types[${index}][ticket_type_id]" value="${ticket.ticket_type_id}">
                    <input type="hidden" name="ticket_types[${index}][price]" value="${ticket.price}">
                    <input type="hidden" name="ticket_types[${index}][quantity]" value="${ticket.quantity}">
                    <input type="hidden" name="ticket_types[${index}][color]" value="${ticket.color}">
                `;
                ticketTypesList.appendChild(row);
            });
        }

        function addTicketType() {
            const selectedTypeId = ticketTypeSelect.value;
            const price = ticketPriceInput.value;
            const quantity = ticketQuantityInput.value;
            const color = ticketColorInput.value;

            if (!selectedTypeId) {
                alert('Please select a ticket type');
                return;
            }
            if (price === '' || Number(price) < 0) {
                alert('Please enter a valid ticket price');
                return;
            }
            if (quantity === '' || Number(quantity) < 0) {
                alert('Please enter a valid ticket quantity');
                return;
            }

            const alreadyAdded = selectedTicketTypes.some((ticket) => Number(ticket.ticket_type_id) === Number(selectedTypeId));
            if (alreadyAdded) {
                alert('This ticket type has already been added.');
                return;
            }

            selectedTicketTypes.push({
                ticket_type_id: Number(selectedTypeId),
                price: Number(price).toFixed(2),
                quantity: Number(quantity),
                color,
            });

            ticketTypeSelect.value = '';
            ticketPriceInput.value = '';
            ticketQuantityInput.value = '';
            ticketColorInput.value = '#ff6600';
            renderTicketTypes();
        }

        function removeTicketType(index) {
            selectedTicketTypes.splice(index, 1);
            renderTicketTypes();
        }

        // Edit ticket type function
        function editTicketType(index) {
            const ticket = selectedTicketTypes[index];
            ticketTypeSelect.value = ticket.ticket_type_id;
            ticketPriceInput.value = ticket.price;
            ticketQuantityInput.value = ticket.quantity;
            ticketColorInput.value = ticket.color;
            // Remove the ticket from the list so it can be re-added/updated
            selectedTicketTypes.splice(index, 1);
            renderTicketTypes();
        }

        addTicketTypeBtn.addEventListener('click', addTicketType);

        document.addEventListener('DOMContentLoaded', () => {
            renderTicketTypes();
        });
    </script>
</x-app-layout>
