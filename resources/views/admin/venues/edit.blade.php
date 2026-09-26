<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')
            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div><h2>Edit Venue</h2><p>Revise venue specifications and operational details with full traceability.</p></div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn"><span id="themeToggleIcon">◐</span></button>
                        <a href="{{ route('admin.venues.index') }}" class="ad-btn">Back</a>
                    </div>
                </header>
                <section class="ad-card">
                    <h3 class="ad-panel-title">Edit Venue Information</h3>
                    @if(!empty($isUsedByConcerts) && $isUsedByConcerts)
                        <div style="margin-bottom: 1rem; padding: 0.7rem 0.9rem; border: 1px solid rgba(251,191,36,0.45); border-radius: 0.5rem; background: rgba(251,191,36,0.08); color: #fde68a;">
                            This venue is already used by concerts. Capacity can be changed, but it cannot go below sold tickets for any existing concert.
                        </div>
                        @if(!empty($concertSoldBreakdown) && $concertSoldBreakdown->isNotEmpty())
                            <div style="margin-bottom: 1rem; padding: 0.7rem 0.9rem; border: 1px solid rgba(148,163,184,0.35); border-radius: 0.5rem; background: rgba(148,163,184,0.08); color: #cbd5e1;">
                                Minimum allowed capacity based on sold tickets: <strong>{{ $maxSoldForConcert }}</strong>
                            </div>
                        @endif
                    @endif
                    <form method="POST" action="{{ route('admin.venues.update', $venue) }}" enctype="multipart/form-data" class="ad-form-grid-3" id="adminVenueEditForm">
                        @csrf @method('PUT')
                        <div class="ad-field">
                            <label class="ad-label" for="name">Venue Name</label>
                            <input class="ad-input" id="name" type="text" name="name" value="{{ old('name', $venue->name) }}" required {{ !empty($isUsedByConcerts) && $isUsedByConcerts ? 'readonly' : '' }}>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="location">Location</label>
                            <input class="ad-input" id="location" type="text" name="location" value="{{ old('location', $venue->location) }}" required {{ !empty($isUsedByConcerts) && $isUsedByConcerts ? 'readonly' : '' }}>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="capacity">Capacity</label>
                            <input class="ad-input" id="capacity" type="number" name="capacity" value="{{ old('capacity', $venue->capacity) }}" min="{{ !empty($isUsedByConcerts) && $isUsedByConcerts ? $maxSoldForConcert : 1 }}" required>
                            <p style="font-size: 0.8rem; color: #fbbf24; margin-top: 0.25rem;">
                                Note: After increasing venue capacity, update the ticket quantities of affected concerts so the added seats become available for selling.
                            </p>
                        </div>

                        <div class="ad-field ad-field-full">
                            <div class="ad-actions-row">
                                <button class="ad-btn ad-btn-primary" type="submit" id="adminVenueUpdateBtn" disabled>Update Venue</button>
                            </div>
                        </div>

                    </form>
                </section>
            </main>
        </div>
    </section>
    @include('admin.partials.theme-script')
    <script>
        const minAllowedCapacity = @json($maxSoldForConcert ?? 1);
        const capacityInput = document.getElementById('capacity');
        if (capacityInput) {
            capacityInput.addEventListener('input', () => {
                const value = Number(capacityInput.value || 0);
                if (value < minAllowedCapacity) {
                    capacityInput.setCustomValidity(`Capacity cannot be lower than sold tickets (${minAllowedCapacity}).`);
                } else {
                    capacityInput.setCustomValidity('');
                }
            });
        }

        const venueForm = document.getElementById('adminVenueEditForm');
        const venueSubmitButton = document.getElementById('adminVenueUpdateBtn');
        if (venueForm && venueSubmitButton) {
            const trackedFields = Array.from(venueForm.elements).filter((field) => {
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
                venueSubmitButton.disabled = !hasChanges;
            };

            trackedFields.forEach((field) => {
                field.addEventListener('input', evaluateDirty);
                field.addEventListener('change', evaluateDirty);
            });

            evaluateDirty();
        }
    </script>
</x-app-layout>
