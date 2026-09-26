<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')
            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div><h2>Edit Concert</h2><p>Update event metadata to ensure listing accuracy and schedule integrity.</p></div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn"><span id="themeToggleIcon">◐</span></button>
                        <a href="{{ route('admin.concerts.index') }}" class="ad-btn">Back</a>
                    </div>
                </header>
                <section class="ad-card">
                    <h3 class="ad-panel-title">Edit Concert Information</h3>
                    <form method="POST" action="{{ route('admin.concerts.update', $concert) }}" enctype="multipart/form-data" class="ad-form-grid-2" id="adminConcertEditForm">
                        @csrf @method('PUT')
                        <div class="ad-field">
                            <label class="ad-label" for="title">Title</label>
                            <input class="ad-input" id="title" type="text" name="title" value="{{ old('title', $concert->title) }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="artist">Artist</label>
                            <input class="ad-input" id="artist" type="text" name="artist" value="{{ old('artist', $concert->artist) }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="venue_id">Venue</label>
                            <select class="ad-select" id="venue_id" name="venue_id" required disabled>
                                @foreach($venues as $venue)
                                    <option value="{{ $venue->id }}" {{ old('venue_id', $concert->venue_id) == $venue->id ? 'selected' : '' }}>{{ $venue->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="venue_id" value="{{ old('venue_id', $concert->venue_id) }}">
                            <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.25rem;">Venue cannot be changed.</p>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="date">Date</label>
                            <input class="ad-input" id="date" type="date" name="date" value="{{ old('date', optional($concert->date)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="time">Time</label>
                            <input class="ad-input" id="time" type="time" name="time" value="{{ old('time', optional($concert->time)->format('H:i')) }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="poster">Update Poster</label>
                            <input class="ad-input" id="poster" type="file" name="poster" accept="image/*">
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="seat_plan_image">Update Seat Plan Image</label>
                            <input class="ad-input" id="seat_plan_image" type="file" name="seat_plan_image" accept="image/*">
                            <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 0.25rem;">Update venue seat plan image for customer reference</p>
                        </div>
                        <div class="ad-field ad-field-full">
                            <label class="ad-label" for="description">Description</label>
                            <textarea class="ad-textarea" id="description" name="description">{{ old('description', $concert->description) }}</textarea>
                        </div>
                        <div class="ad-field ad-field-full ad-ticket-pricing-panel" style="border: 1px solid rgba(255,255,255,0.08); padding: 0.75rem; margin-bottom: 0.75rem; border-radius: 0.5rem; background: rgba(255,255,255,0.02);">
                            <h3 class="ad-panel-title" style="margin-bottom: 0.75rem;">Ticket Pricing & Types</h3>
                            <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.75rem;">
                                Edit ticket prices, colors, and quantities. Quantities cannot be reduced below already sold tickets.
                            </p>
                            @if($hasSoldTickets)
                                <div style="margin-bottom: 0.75rem; padding: 0.6rem 0.8rem; border: 1px solid rgba(248,113,113,0.4); border-radius: 0.5rem; background: rgba(248,113,113,0.08); color: #fecaca;">
                                    Ticket pricing and colors are locked because tickets have already been sold for this concert. Quantity can still be increased (but not below sold tickets).
                                </div>
                            @endif

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
                        @if($concert->poster_url || $concert->seat_plan_image)
                            <div class="ad-field ad-field-full" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; align-items: start;">
                                <div style="display: grid; gap: 0.5rem;">
                                    <label class="ad-label">Current Poster</label>
                                    @if($concert->poster_url)
                                        <img src="{{ asset('storage/'.$concert->poster_url) }}" alt="{{ $concert->title }} poster" style="width: 100%; height: auto; max-height: 28rem; object-fit: contain; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; padding: 0.5rem;">
                                    @else
                                        <div style="width: 100%; min-height: 14rem; display: grid; place-items: center; color: #94a3b8; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem;">No poster uploaded</div>
                                    @endif
                                </div>
                                <div style="display: grid; gap: 0.5rem;">
                                    <label class="ad-label">Current Seat Plan</label>
                                    @if($concert->seat_plan_image)
                                        <img src="{{ asset('storage/'.$concert->seat_plan_image) }}" alt="{{ $concert->title }} seat plan" style="width: 100%; height: auto; max-height: 28rem; object-fit: contain; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; padding: 0.5rem;">
                                    @else
                                        <div style="width: 100%; min-height: 14rem; display: grid; place-items: center; color: #94a3b8; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem;">No seat plan uploaded</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="ad-field ad-field-full">
                            <div class="ad-actions-row">
                                <button class="ad-btn ad-btn-primary" type="submit" id="adminConcertUpdateBtn" disabled>Update Concert</button>
                            </div>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </section>
    @include('admin.partials.theme-script')

    @php
        $ticketTypesJson = $ticketTypes->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
            ];
        })->values()->all();

        $existingTicketTypesJson = old('ticket_types', $concert->concertTicketTypes->map(function ($type) {
            return [    
                'id' => $type->id,
                'ticket_type_id' => $type->ticket_type_id,
                'price' => $type->price,
                'quantity' => $type->quantity,
                'color' => $type->color,
                'sold' => $type->sold_quantity,
            ];
        })->values()->all());
    @endphp

    <script>
        const ticketTypes = @json($ticketTypesJson);
        const existingTicketTypes = @json($existingTicketTypesJson);
        const hasSoldTickets = @json($hasSoldTickets);

        const ticketTypesList = document.getElementById('ticket_types_list');
        let selectedTicketTypes = existingTicketTypes.map((ticket) => ({
            id: ticket.id,
            ticket_type_id: Number(ticket.ticket_type_id),
            price: Number(ticket.price),
            quantity: Number(ticket.quantity ?? 0),
            color: ticket.color || '#ff6600',
            sold: Number(ticket.sold ?? 0),
        }));

        function renderTicketTypes() {
            ticketTypesList.innerHTML = '';

            selectedTicketTypes.forEach((ticket, index) => {
                const ticketType = ticketTypes.find((type) => type.id === Number(ticket.ticket_type_id));
                const label = ticketType ? `${ticketType.name}${ticketType.description ? ' — ' + ticketType.description : ''}` : 'Selected ticket';

                const row = document.createElement('div');
                row.className = 'ad-field';
                row.style = 'display: grid; grid-template-columns: 1fr; gap: 0.65rem; align-items: center; padding: 0.65rem 0.8rem; border: 1px solid rgba(255,255,255,0.1); border-radius: 0.5rem; background: rgba(255,255,255,0.02);';
                row.innerHTML = `
                    <div>
                        <p style="margin: 0 0 0.2rem; font-weight: 700; font-size: 0.95rem;">${label}</p>
                        <p style="margin: 0 0 0.5rem; font-size: 0.82rem; color: #94a3b8;">Sold: ${ticket.sold || 0} | Available: ${Math.max(0, ticket.quantity - (ticket.sold || 0))}</p>
                        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.55rem; align-items: end;">
                            <div>
                                <label class="ad-label" style="margin-bottom: 0.25rem; font-size: 0.75rem;">Price (PHP)</label>
                                <input class="ad-input" type="number" min="0" step="0.01" value="${Number(ticket.price).toFixed(2)}" ${hasSoldTickets ? 'disabled' : ''} data-action="price" data-index="${index}" style="padding: 0.5rem 0.7rem; min-height: 2.2rem; font-size: 0.9rem;">
                            </div>
                            <div>
                                <label class="ad-label" style="margin-bottom: 0.25rem; font-size: 0.75rem;">Quantity</label>
                                <input class="ad-input" type="number" min="${ticket.sold || 0}" value="${ticket.quantity}" data-action="quantity" data-index="${index}" style="padding: 0.5rem 0.7rem; min-height: 2.2rem; font-size: 0.9rem;">
                            </div>
                            <div>
                                <label class="ad-label" style="margin-bottom: 0.25rem; font-size: 0.75rem;">Color</label>
                                <input class="ad-input" type="color" value="${ticket.color}" ${hasSoldTickets ? 'disabled' : ''} data-action="color" data-index="${index}" style="width: 100%; height: 2.2rem; padding: 0.1rem;">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="ticket_types[${index}][id]" value="${ticket.id}">
                    <input type="hidden" name="ticket_types[${index}][ticket_type_id]" value="${ticket.ticket_type_id}">
                    <input type="hidden" name="ticket_types[${index}][price]" value="${Number(ticket.price).toFixed(2)}">
                    <input type="hidden" name="ticket_types[${index}][quantity]" value="${ticket.quantity}">
                    <input type="hidden" name="ticket_types[${index}][color]" value="${ticket.color}">
                `;
                ticketTypesList.appendChild(row);
            });

            ticketTypesList.querySelectorAll('[data-action="price"]').forEach((input) => {
                input.addEventListener('input', (event) => {
                    const index = Number(event.currentTarget.dataset.index);
                    const nextPrice = Number(event.currentTarget.value);
                    if (Number.isFinite(nextPrice) && nextPrice >= 0) {
                        selectedTicketTypes[index].price = nextPrice;
                        const hiddenPrice = rowQuery(index, 'price');
                        if (hiddenPrice) {
                            hiddenPrice.value = nextPrice.toFixed(2);
                        }
                    }
                });
            });

            ticketTypesList.querySelectorAll('[data-action="quantity"]').forEach((input) => {
                input.addEventListener('input', (event) => {
                    const index = Number(event.currentTarget.dataset.index);
                    const nextQuantity = Number(event.currentTarget.value);
                    const minQuantity = selectedTicketTypes[index].sold || 0;
                    
                    // Always clear validity first
                    event.currentTarget.setCustomValidity('');
                    
                    if (Number.isFinite(nextQuantity) && nextQuantity >= minQuantity) {
                        selectedTicketTypes[index].quantity = nextQuantity;
                        const hiddenQuantity = rowQuery(index, 'quantity');
                        if (hiddenQuantity) {
                            hiddenQuantity.value = nextQuantity;
                        }
                    } else if (Number.isFinite(nextQuantity) && nextQuantity < minQuantity) {
                        event.currentTarget.setCustomValidity(`Quantity cannot be lower than sold tickets (${minQuantity}).`);
                    }
                });
            });

            ticketTypesList.querySelectorAll('[data-action="color"]').forEach((input) => {
                input.addEventListener('input', (event) => {
                    const index = Number(event.currentTarget.dataset.index);
                    selectedTicketTypes[index].color = event.currentTarget.value;
                    const hiddenColor = rowQuery(index, 'color');
                    if (hiddenColor) {
                        hiddenColor.value = event.currentTarget.value;
                    }
                });
            });

        }

        function rowQuery(index, field) {
            return ticketTypesList.querySelector(`input[name="ticket_types[${index}][${field}]"]`);
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderTicketTypes();
            const form = document.getElementById('adminConcertEditForm');
            if (form) {
                const submitButton = document.getElementById('adminConcertUpdateBtn');
                const trackedFields = Array.from(form.elements).filter((field) => {
                    return field.name && !field.disabled && field.type !== 'hidden';
                });
                const initialValues = new Map(
                    trackedFields.map((field) => [
                        field.name,
                        field.type === 'file' ? '' : field.value,
                    ])
                );

                const evaluateDirty = () => {
                    const hasChanges = trackedFields.some((field) => {
                        if (field.type === 'file') {
                            return field.files && field.files.length > 0;
                        }
                        return field.value !== initialValues.get(field.name);
                    });

                    if (submitButton) {
                        submitButton.disabled = !hasChanges;
                    }
                };

                trackedFields.forEach((field) => {
                    field.addEventListener('input', evaluateDirty);
                    field.addEventListener('change', evaluateDirty);
                });

                evaluateDirty();

                form.addEventListener('submit', (event) => {
                    const invalidSold = selectedTicketTypes.find((row) => Number(row.quantity || 0) < Number(row.sold || 0));
                    if (invalidSold) {
                        event.preventDefault();
                        alert(`A ticket type has quantity below sold tickets (${invalidSold.sold}).`);
                        return;
                    }
                });
            }
        });
    </script>
</x-app-layout>
