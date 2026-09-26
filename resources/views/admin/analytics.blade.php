<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')

            <main class="admin-main">
                <header class="admin-header">
                    <div>
                        <h2>Analytics</h2>
                        <p>Analyze sales velocity, revenue trends, and payment channel performance.</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn" aria-label="Toggle theme" title="Toggle theme">
                            <span id="themeToggleIcon" aria-hidden="true">◐</span>
                        </button>
                        <a href="{{ route('admin.concerts.create') }}" class="ad-btn ad-btn-primary">Create Concert</a>
                    </div>
                </header>

                <section class="ad-grid ad-two-col">
                    <article class="ad-card">
                        <h3 class="ad-panel-title">Revenue Trend (Line Chart)</h3>
                        <div class="ad-line-chart">
                            <canvas id="revenueLineChart" class="ad-chart-canvas"></canvas>
                        </div>
                    </article>

                    <article class="ad-card">
                        <h3 class="ad-panel-title">New Users (Area Chart)</h3>
                        <div class="ad-line-chart">
                            <canvas id="usersAreaChart" class="ad-chart-canvas"></canvas>
                        </div>
                    </article>

                    <article class="ad-card">
                        <h3 class="ad-panel-title">Sales Volume (Column Chart)</h3>
                        <div class="ad-line-chart">
                            <canvas id="salesColumnChart" class="ad-chart-canvas"></canvas>
                        </div>
                    </article>

                    <article class="ad-card">
                        <h3 class="ad-panel-title">Channel Revenue (Pie Chart)</h3>
                        <div class="ad-line-chart" style="padding: 1rem 0;">
                            <canvas id="channelPieChart" class="ad-chart-canvas"></canvas>
                        </div>
                    </article>

                </section>

                <script>
                    window.analyticsData = {
                        revenue: {
                            labels: @json($monthlyRevenue->pluck('month_key')),
                            values: @json($monthlyRevenue->pluck('total_amount')->map(fn($value) => (float) $value)),
                        },
                        users: {
                            labels: @json($monthlyUsers->pluck('month_key')),
                            values: @json($monthlyUsers->pluck('user_count')->map(fn($value) => (int) $value)),
                        },
                        sales: {
                            labels: @json($monthlySalesCount->pluck('month_key')),
                            values: @json($monthlySalesCount->pluck('payments_count')->map(fn($value) => (int) $value)),
                        },
                        channel: {
                            labels: @json($channelRevenue->pluck('payment_method')->map(fn($method) => ucfirst($method))),
                            values: @json($channelRevenue->pluck('total_amount')->map(fn($value) => (float) $value)),
                        }
                    };
                </script>
            </main>
        </div>
    </section>

    @include('admin.partials.theme-script')
</x-app-layout>
