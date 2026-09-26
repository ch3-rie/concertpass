// Chart.js CDN loader for dashboard
(function() {
    if (window.Chart) return;
    var script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
    script.onload = function() {
        document.dispatchEvent(new Event('chartjs:loaded'));
    };
    document.head.appendChild(script);
})();
