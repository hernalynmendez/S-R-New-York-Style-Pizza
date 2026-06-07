<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

$page_title = 'Revenue Analytics';
$extra_css = [SITE_URL . 'css/analytics.css'];
$extra_js = [SITE_URL . 'js/analytics.js'];

requireAdmin();
checkSessionTimeout();

require_once '../includes/header.php';
?>
<body class="analytics">
<div class="analytics-wrap">
    <div class="analytics-header">
        <div>
            <div class="analytics-title">Revenue Analytics</div>
            <div class="analytics-meta">Last updated: <?php echo date('M j, Y H:i'); ?></div>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <div class="btn btn-sm btn-outline-light">Date range</div>
            <div class="btn btn-sm btn-primary">Export</div>
        </div>
    </div>

    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total Revenue</div>
            <div class="kpi-value">$124,530 <span class="kpi-trend">+12.4%</span></div>
            <div style="height:26px;margin-top:8px"><!-- sparkline placeholder --></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Orders</div>
            <div class="kpi-value">3,241</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Avg Order Value</div>
            <div class="kpi-value">$38.42</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Growth</div>
            <div class="kpi-value">+12.4%</div>
        </div>
    </div>

    <div class="main-grid">
        <div class="chart-card">
            <div class="peak-card">🔥 Peak Revenue<br><small>$8,240 • Sat 7PM</small></div>
            <canvas id="revenueChart" style="width:100%;height:360px"></canvas>
            <div class="compare-legend">
                <div class="legend-item"><span class="legend-swatch" style="background:#3B82F6"></span> Current</div>
                <div class="legend-item"><span class="legend-swatch" style="background:rgba(255,255,255,0.12)"></span> Previous</div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">
            <div class="donut-card" style="height:240px">
                <h5 style="color:#fff;margin-top:0">Revenue Breakdown</h5>
                <div style="height:160px"><canvas id="donutChart" style="width:100%;height:160px"></canvas></div>
                <div style="display:flex;gap:8px;margin-top:8px;color:rgba(255,255,255,0.75)">
                    <div>Online Sales 62%</div>
                    <div>Subscription 24%</div>
                    <div>Enterprise 14%</div>
                </div>
            </div>

            <div class="insight-card">
                <h5>AI Insights</h5>
                <ul class="insight-list">
                    <li>Revenue increased 12.4% compared to last week</li>
                    <li>Peak sales occur between 6 PM–8 PM on Saturdays</li>
                    <li>Revenue drops significantly on Tuesday mornings</li>
                </ul>
            </div>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
