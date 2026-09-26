<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')
            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div>
                        <h2>{{ $concert->title }}</h2>
                        <p>{{ $concert->artist }} at {{ optional($concert->venue)->name ?? 'No Venue' }}</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn"><span id="themeToggleIcon">◐</span></button>
                        <a href="{{ route('admin.concerts.index') }}" class="ad-btn">Back</a>
                        <a href="{{ route('admin.concerts.edit', $concert) }}" class="ad-btn ad-btn-primary">Edit</a>
                    </div>
                </header>

                <section class="ad-card ad-concert-show-card">
@php
                        $ticketCounts = [
                            'total' => \App\Models\Ticket::whereHas('booking', function($q) use ($concert) {
                                $q->where('concert_id', $concert->id);
                            })->count(),
                        ];
                        $revenue = (float) $concert->bookings->sum('total_price');
                        $typeCapacity = $concert->concertTicketTypes->sum('quantity');
                        $remainingTickets = max(0, $typeCapacity - $ticketCounts['total']);
                    @endphp

                    <div class="ad-concert-show-layout">
                        <div class="ad-concert-main">
                            <div class="ad-concert-poster-wrap">
                                @if($concert->poster_url)
                                    <img class="ad-poster" src="{{ asset('storage/'.$concert->poster_url) }}" alt="{{ $concert->title }} poster">
                                @else
                                    <div class="ad-empty-poster">No poster uploaded</div>
                                @endif
                            </div>

                            <div class="ad-concert-details-panel">
                                <h3 class="ad-panel-title">Concert Details</h3>
                                <div class="ad-concert-details-grid">
                                    <div class="ad-concert-detail-box"><span class="ad-label">Concert ID</span><strong>#{{ $concert->id }}</strong></div>
                                    <div class="ad-concert-detail-box"><span class="ad-label">Artist</span><strong>{{ $concert->artist }}</strong></div>
                                    <div class="ad-concert-detail-box"><span class="ad-label">Venue</span><strong>{{ optional($concert->venue)->name ?? 'N/A' }}</strong></div>
                                    <div class="ad-concert-detail-box"><span class="ad-label">Location</span><strong>{{ optional($concert->venue)->location ?? 'N/A' }}</strong></div>
                                    <div class="ad-concert-detail-box"><span class="ad-label">Date</span><strong>{{ optional($concert->date)->format('M d, Y') ?? 'N/A' }}</strong></div>
                                    <div class="ad-concert-detail-box"><span class="ad-label">Time</span><strong>{{ optional($concert->time)->format('h:i A') ?? 'N/A' }}</strong></div>
                                </div>
                            </div>
                        </div>

                        <aside class="ad-concert-analytics">
                            <h3 class="ad-panel-title">Performance Analytics</h3>
                            <div class="ad-kpi-grid ad-concert-kpis">
                                <div class="ad-kpi"><span class="label">Total Tickets Sold</span><span class="value">{{ number_format($ticketCounts['total']) }}</span></div>
                                <div class="ad-kpi"><span class="label">Total Bookings</span><span class="value">{{ $concert->bookings->count() }}</span></div>
                                <div class="ad-kpi"><span class="label">Total Revenue</span><span class="value">₱{{ number_format($revenue, 2) }}</span></div>
                                <div class="ad-kpi"><span class="label">Tickets Available</span><span class="value">{{ $remainingTickets > 0 ? number_format($remainingTickets) : '0' }}</span></div>
                            </div>

                            @if($ticketTypeAvailability->isNotEmpty())
                                <div class="ad-ticket-type-availability" style="margin-top: 1rem;">
                                    <p class="ad-label">Available by Ticket Type</p>
                                    <div class="ad-kpi-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem;">
                                        @foreach($ticketTypeAvailability as $type)
                                            <div class="ad-kpi"><span class="label">{{ $type['label'] }}</span><span class="value">{{ $type['remaining'] }}</span></div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="ad-chart-block">
                                <p class="ad-label">Sales Overview</p>
                                <div class="ad-chart-row">
                                    <span>Bookings</span>
                                    <div class="ad-chart-track"><div class="ad-chart-fill sold" style="width: 100%;"></div></div>
                                    <strong>{{ $concert->bookings->count() }}</strong>
                                </div>
                                <div class="ad-chart-row">
                                    <span>Tickets</span>
                                    <div class="ad-chart-track"><div class="ad-chart-fill available" style="width: 100%;"></div></div>
                                    <strong>{{ number_format($ticketCounts['total']) }}</strong>
                                </div>
                            </div>

                            {{-- Concert Seat Plan Image --}}
                            @if($concert->seat_plan_image)
                                <div class="ad-card" style="margin-top: 1rem;">
                                    <h3 class="ad-panel-title">Concert Seat Plan</h3>
                                    <div style="text-align: center;">
                                        <img src="{{ asset('storage/' . $concert->seat_plan_image) }}" alt="Concert Seat Plan" style="max-width: 100%; height: auto; border-radius: 0.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
                                    </div>
                                </div>
                            @endif
                        </aside>
                    </div>
                </section>

                <section class="ad-card" style="margin-top: 1rem;">
                    <h3 class="ad-panel-title">Description</h3>
                    <p>{{ $concert->description ?: 'No description available for this concert.' }}</p>
                </section>

                <section class="ad-card" style="margin-top: 1rem;">
                    <h3 class="ad-panel-title">Ticket Pricing</h3>
                    <div style="display: grid; gap: 0.75rem;">
                        @forelse($concert->concertTicketTypes as $ticketType)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.08);">
                                <div>
                                    <p style="margin: 0 0 0.35rem; font-weight: 700; color: #fff;">{{ $ticketType->custom_name ?: ($ticketType->ticketType->name ?? 'Ticket') }}</p>
                                    @if($ticketType->ticketType?->description)
                                        <p style="margin: 0; color: #94a3b8; font-size: 0.9rem;">{{ $ticketType->ticketType->description }}</p>
                                    @endif
                                </div>
                                <div style="text-align: right;">
                                    <span style="display: inline-block; width: 0.9rem; height: 0.9rem; background: {{ $ticketType->color ?: '#888' }}; border-radius: 9999px; margin-bottom: 0.35rem;"></span>
                                    <p style="margin: 0; font-size: 1.1rem; font-weight: 800;">₱{{ number_format($ticketType->price, 2) }}</p>
                                </div>
                            </div>
                        @empty
                            <p style="margin: 0; color: #94a3b8;">No ticket pricing configured for this concert yet.</p>
                        @endforelse
                    </div>
                </section>
            </main>
        </div>
    </section>
    @include('admin.partials.theme-script')
</x-app-layout>
