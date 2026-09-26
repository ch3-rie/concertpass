<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')

            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header" id="dashboard-overview">
                    <div>
                        <h2>Welcome back, {{ auth()->user()->name ?? 'Admin' }}</h2>
                        <p>Review operational performance, platform health, and current booking activity.</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn" aria-label="Toggle theme" title="Toggle theme">
                            <span id="themeToggleIcon" aria-hidden="true">◐</span>
                        </button>
                        <a href="{{ route('admin.concerts.create') }}" class="ad-btn ad-btn-primary">Create Concert</a>
                    </div>
                </header>

                <section class="ad-grid ad-summary-grid">
                    <article class="ad-card">
                        <p class="ad-card-label">Tickets Sold</p>
                        <p class="ad-card-value">{{ number_format($metrics['tickets_sold']) }}</p>
                        <p class="ad-card-trend">Live from sold concert seats</p>
                    </article>
                    <article class="ad-card">
                        <p class="ad-card-label">Revenue</p>
                        <p class="ad-card-value">₱{{ number_format($metrics['revenue'], 2) }}</p>
                        <p class="ad-card-trend">Paid transactions</p>
                    </article>
                    <article class="ad-card">
                        <p class="ad-card-label">Active Users</p>
                        <p class="ad-card-value">{{ number_format($metrics['users']) }}</p>
                        <p class="ad-card-trend">Registered accounts</p>
                    </article>
                    <article class="ad-card">
                        <p class="ad-card-label">Total Concerts</p>
                        <p class="ad-card-value">{{ number_format($metrics['concerts']) }}</p>
                        <p class="ad-card-trend">{{ number_format($metrics['bookings']) }} bookings recorded</p>
                    </article>
                </section>

                <section class="ad-grid ad-two-col" style="margin-top: 1rem;">
                    <article class="ad-card">
                        <h3 class="ad-panel-title">Revenue per Concert (Bar Chart)</h3>
                        <div class="ad-bar-wrap" style="height: 260px;">
                            <canvas id="revenueConcertChart" width="1000" height="260" style="width:100%;height:100%;background:transparent;"></canvas>
                        </div>
                    </article>

                    <article class="ad-card">
                        <h3 class="ad-panel-title">Revenue by Channel (Bar Chart)</h3>
                        <div class="ad-bar-wrap" style="height: 260px;">
                            <canvas id="revenueChannelChart" width="400" height="260" style="width:100%;height:100%;background:transparent;"></canvas>
                        </div>
                    </article>
                </section>

                <section class="ad-card" style="margin-top: 1rem;">
                    <h3 class="ad-panel-title">Recent Activity and Transactions</h3>
                    <div class="ad-table-wrap">
                        <table class="ad-table">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Activity</th>
                                    <th>Reference</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $booking)
                                    <tr>
                                        <td>{{ $booking->id }}</td>
                                        <td>{{ optional($booking->concert)->title ?? 'Deleted Concert' }} - {{ optional($booking->user)->name ?? 'Deleted User' }}</td>
                                        <td>#BOOK-{{ str_pad((string)$booking->id, 6, '0', STR_PAD_LEFT) }}</td>
                                        <td>₱{{ number_format((float)$booking->total_price, 2) }}</td>
                                        <td>
                                            <span class="ad-status {{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'pending' : 'info') }}">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">No bookings found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

            </main>
        </div>
    </section>

    @include('admin.partials.theme-script')
    <script>
    (function () {
        let concertChartInstance = null;
        let channelChartInstance = null;
        let refreshTimer = null;

        function normalizeLabel(value, fallback) {
            if (typeof value !== 'string') {
                return fallback;
            }

            const trimmed = value.trim();
            return trimmed.length > 0 ? trimmed : fallback;
        }

        function mapDashboardData(payload) {
            const concerts = Array.isArray(payload?.concerts) ? payload.concerts : [];
            const channels = Array.isArray(payload?.channels) ? payload.channels : [];

            return {
                concertLabels: concerts.map((concert) => normalizeLabel(concert.concert_name ?? concert.title, 'Untitled Concert')),
                concertValues: concerts.map((concert) => Number(concert.total_revenue ?? concert.revenue ?? 0)),
                channelLabels: channels.map((channel) => normalizeLabel(channel.payment_method, 'Unknown')),
                channelValues: channels.map((channel) => Number(channel.total_amount ?? channel.revenue ?? 0)),
            };
        }

        function createOrReplaceChart(instance, canvasId, config) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                return instance;
            }

            const context = canvas.getContext('2d');
            if (!context) {
                return instance;
            }

            if (instance) {
                instance.destroy();
            }

            return new window.Chart(context, config);
        }

        function renderCharts(mapped) {
            concertChartInstance = createOrReplaceChart(concertChartInstance, 'revenueConcertChart', {
                type: 'bar',
                data: {
                    labels: mapped.concertLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: mapped.concertValues,
                        backgroundColor: '#ff6600',
                        borderRadius: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } },
                },
            });

            channelChartInstance = createOrReplaceChart(channelChartInstance, 'revenueChannelChart', {
                type: 'bar',
                data: {
                    labels: mapped.channelLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: mapped.channelValues,
                        backgroundColor: '#444',
                        borderRadius: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } },
                },
            });
        }

        async function fetchAndRenderCharts() {
            if (!window.Chart) {
                return;
            }

            try {
                const response = await fetch('/admin/api/analytics', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Analytics request failed with status ${response.status}`);
                }

                const payload = await response.json();
                const mapped = mapDashboardData(payload);
                renderCharts(mapped);
            } catch (error) {
                console.error('Dashboard chart render failed:', error);
            }
        }

        function bootDashboardCharts(attempt = 0) {
            const hasCanvases = document.getElementById('revenueConcertChart') && document.getElementById('revenueChannelChart');
            if (!hasCanvases) {
                return;
            }

            if (!window.Chart) {
                if (attempt < 20) {
                    setTimeout(() => bootDashboardCharts(attempt + 1), 150);
                }
                return;
            }

            fetchAndRenderCharts();

            if (!refreshTimer) {
                refreshTimer = window.setInterval(fetchAndRenderCharts, 30000);
            }
        }

        document.addEventListener('DOMContentLoaded', () => bootDashboardCharts(0));
        window.addEventListener('load', () => bootDashboardCharts(0));
    })();
    </script>
 </x-app-layout>
