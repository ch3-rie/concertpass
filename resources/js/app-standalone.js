function renderAnalyticsCharts() {
    if (!window.analyticsData || typeof window.Chart === 'undefined') {
        return;
    }

    const { revenue, users, sales, channel } = window.analyticsData;

    const createChart = (canvasId, config) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            return;
        }
        const context = canvas.getContext('2d');
        if (!context) {
            return;
        }
        return new window.Chart(context, config);
    };

    if (revenue.labels.length > 0) {
        createChart('revenueLineChart', {
            type: 'line',
            data: {
                labels: revenue.labels,
                datasets: [{
                    label: 'Revenue',
                    data: revenue.values,
                    borderColor: '#0b84ff',
                    backgroundColor: 'rgba(11,132,255,0.15)',
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    borderWidth: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => `PHP ${value}`,
                        },
                    },
                },
                plugins: {
                    legend: { display: false },
                },
            },
        });
    }

    if (users.labels.length > 0) {
        createChart('usersAreaChart', {
            type: 'line',
            data: {
                labels: users.labels,
                datasets: [{
                    label: 'New Users',
                    data: users.values,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.25)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    borderWidth: 3,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
                plugins: {
                    legend: { display: false },
                },
            },
        });
    }

    if (sales.labels.length > 0) {
        createChart('salesColumnChart', {
            type: 'bar',
            data: {
                labels: sales.labels,
                datasets: [{
                    label: 'Payments',
                    data: sales.values,
                    backgroundColor: '#8b5cf6',
                    borderRadius: 8,
                    barPercentage: 0.6,
                    categoryPercentage: 0.7,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                    },
                },
                plugins: {
                    legend: { display: false },
                },
            },
        });
    }

    if (channel.labels.length > 0) {
        createChart('channelPieChart', {
            type: 'pie',
            data: {
                labels: channel.labels,
                datasets: [{
                    data: channel.values,
                    backgroundColor: ['#0b84ff', '#22c55e', '#f59e0b', '#ec4899', '#a855f7', '#14b8a6'],
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    renderAnalyticsCharts();
});
