<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

requireAdmin();

// Get selected period (default: 30 days)
$period = isset($_GET['period']) ? (int)$_GET['period'] : 30;
if ($period < 1 || $period > 365) $period = 30;

// Fetch data
$revenue_data = getDailyRevenueData($period);
$products = getRevenueByProduct(10, $period);
$categories = getRevenueByCategory($period);
$payment_methods = getRevenueByPaymentMethod($period);
$insights = getRevenueInsights($period);

// Calculate revenue comparison
$prev_period_start = date('Y-m-d 00:00:00', strtotime("-" . ($period * 2) . " days"));
$prev_period_end = date('Y-m-d 23:59:59', strtotime("-{$period} days"));

$stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed' AND created_at BETWEEN ? AND ?");
$stmt->bind_param("ss", $prev_period_start, $prev_period_end);
$stmt->execute();
$result = $stmt->get_result();
$prev_revenue = $result->fetch_assoc()['total'] ?? 0;
$stmt->close();

$revenue_growth = $prev_revenue > 0 ? (($insights['total_revenue'] - $prev_revenue) / $prev_revenue) * 100 : 0;

?>
<?php require_once '../includes/header.php'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');

:root {
    --bg: #0f0f0f;
    --bg-2: #141414;
    --accent: #ef4444;
    --card-radius: 16px;
    --glass: rgba(255,255,255,0.03);
}

body {
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial;
    background: linear-gradient(180deg, var(--bg), var(--bg-2));
    color: #e6e6e6;
}

.analytics-wrap {
    padding: 28px;
    max-width: 1600px;
    margin: 0 auto;
}

.header-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}

.header-section h1 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: #fff;
}

.period-selector {
    display: flex;
    gap: 8px;
    align-items: center;
}

.period-btn {
    padding: 8px 14px;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px;
    background: rgba(255,255,255,0.02);
    color: #9aa0a6;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    text-decoration: none;
}

.period-btn:hover, .period-btn.active {
    background: rgba(239,68,68,0.2);
    color: #ef4444;
    border-color: #ef4444;
}

.kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.kpi-card {
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    border: 1px solid #1f1f1f;
    border-radius: var(--card-radius);
    padding: 20px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.6);
}

.kpi-label {
    font-size: 0.85rem;
    color: #9aa0a6;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kpi-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
}

.kpi-change {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.kpi-change.positive {
    color: #51cf66;
}

.kpi-change.negative {
    color: #ff6b6b;
}

.chart-card {
    background: linear-gradient(180deg, rgba(255,255,255,0.015), rgba(255,255,255,0.01));
    border: 1px solid #1f1f1f;
    border-radius: var(--card-radius);
    padding: 24px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.6);
    margin-bottom: 24px;
}

.chart-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #e6e6e6;
    margin-bottom: 16px;
}

.grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.grid-3 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}

.insight-item {
    background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    border: 1px solid #1f1f1f;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
}

.insight-label {
    font-size: 0.8rem;
    color: #9aa0a6;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.insight-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #fff;
    word-break: break-word;
}

.table-card {
    background: linear-gradient(180deg, rgba(255,255,255,0.015), rgba(255,255,255,0.01));
    border: 1px solid #1f1f1f;
    border-radius: var(--card-radius);
    padding: 24px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.6);
    overflow-x: auto;
}

.table-card table {
    width: 100%;
    border-collapse: collapse;
}

.table-card th {
    background: transparent;
    color: #cfd8da;
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    font-size: 0.9rem;
    font-weight: 600;
}

.table-card td {
    padding: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    font-size: 0.95rem;
}

.table-card tr:hover {
    background: rgba(255,255,255,0.02);
}

.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(239,68,68,0.2);
    color: #ef4444;
    font-size: 0.85rem;
    font-weight: 700;
}

canvas {
    max-height: 350px;
}

@media(max-width: 1024px) {
    .grid-2, .grid-3 {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 768px) {
    .analytics-wrap {
        padding: 16px;
    }
    
    .header-section {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-section h1 {
        font-size: 1.5rem;
    }
    
    .kpi-row {
        grid-template-columns: 1fr;
    }
}

.export-btn {
    padding: 10px 16px;
    background: linear-gradient(90deg, #ef4444, #ff7a7a);
    border: none;
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 600;
    transition: transform 0.2s ease;
}

.export-btn:hover {
    transform: translateY(-2px);
}
</style>

<div class="analytics-wrap">
    <!-- Header -->
    <div class="header-section">
        <div>
            <h1>Revenue Analytics</h1>
            <div style="color: #9aa0a6; font-size: 0.95rem; margin-top: 4px;">Comprehensive sales performance & profitability insights</div>
        </div>
        <div class="period-selector">
            <a href="?period=7" class="period-btn <?php echo $period === 7 ? 'active' : ''; ?>">7 Days</a>
            <a href="?period=30" class="period-btn <?php echo $period === 30 ? 'active' : ''; ?>">30 Days</a>
            <a href="?period=90" class="period-btn <?php echo $period === 90 ? 'active' : ''; ?>">90 Days</a>
            <a href="?period=365" class="period-btn <?php echo $period === 365 ? 'active' : ''; ?>">Yearly</a>
        </div>
    </div>

    <!-- KPI Cards Row -->
    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-label">Total Revenue</div>
            <div class="kpi-value">₱<?php echo number_format($insights['total_revenue'], 2); ?></div>
            <div class="kpi-change <?php echo $revenue_growth >= 0 ? 'positive' : 'negative'; ?>">
                <span><?php echo $revenue_growth >= 0 ? '↑' : '↓'; ?></span>
                <span><?php echo abs(round($revenue_growth, 1)); ?>% vs last period</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Total Orders</div>
            <div class="kpi-value"><?php echo number_format($insights['total_orders']); ?></div>
            <div class="kpi-change positive" style="color: #4c6ef5;">
                <span>📦</span>
                <span><?php echo $period; ?> days selected</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Average Order Value</div>
            <div class="kpi-value">₱<?php echo number_format($insights['avg_order_value'], 2); ?></div>
            <div class="kpi-change positive">
                <span>✓</span>
                <span>Performance metric</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-label">Revenue Growth</div>
            <div class="kpi-value"><?php echo round($revenue_growth, 1); ?>%</div>
            <div class="kpi-change <?php echo $revenue_growth >= 0 ? 'positive' : 'negative'; ?>">
                <span><?php echo $revenue_growth >= 0 ? '📈' : '📉'; ?></span>
                <span>Period comparison</span>
            </div>
        </div>
    </div>

    <!-- Revenue Trend Chart -->
    <div class="chart-card">
        <div class="chart-title">Revenue Trend</div>
        <canvas id="revenueTrendChart" height="80"></canvas>
    </div>

    <!-- Monthly Revenue Chart -->
    <div class="chart-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <div class="chart-title" style="margin-bottom: 4px;">Monthly Revenue</div>
                <div style="font-size: 0.9rem; color: #9aa0a6;">Revenue trend over the last six months.</div>
            </div>
            <button style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #9aa0a6; cursor: pointer; display: flex; align-items: center; justify-content: center;">⋯</button>
        </div>
        <canvas id="monthlyRevenueChart" height="100"></canvas>
    </div>

    <!-- Two Column Section -->
    <div class="grid-2">
        <!-- Revenue by Product -->
        <div class="chart-card">
            <div class="chart-title">Revenue by Product</div>
            <canvas id="revenueByProductChart" height="100"></canvas>
        </div>

        <!-- Revenue Distribution -->
        <div class="chart-card">
            <div class="chart-title">Revenue by Category</div>
            <canvas id="revenueByCategoryChart" height="100"></canvas>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="chart-card">
        <div class="chart-title">Revenue by Payment Method</div>
        <canvas id="paymentMethodChart" height="80"></canvas>
    </div>

    <!-- Insights Panel -->
    <div>
        <div class="chart-title" style="margin-bottom: 16px;">Revenue Insights</div>
        <div class="insights-grid">
            <div class="insight-item">
                <div class="insight-label">Best Selling Product</div>
                <div class="insight-value"><?php echo htmlspecialchars($insights['best_product']); ?></div>
            </div>
            <div class="insight-item">
                <div class="insight-label">Highest Revenue Day</div>
                <div class="insight-value"><?php echo $insights['highest_revenue_day']; ?></div>
                <div style="font-size: 0.8rem; color: #7a7a7a; margin-top: 4px;">₱<?php echo number_format($insights['highest_revenue_amount'], 2); ?></div>
            </div>
            <div class="insight-item">
                <div class="insight-label">Top Payment Method</div>
                <div class="insight-value"><?php echo $insights['top_payment_method']; ?></div>
            </div>
            <div class="insight-item">
                <div class="insight-label">Avg Customer Spend</div>
                <div class="insight-value">₱<?php echo number_format($insights['avg_order_value'], 2); ?></div>
            </div>
        </div>
    </div>

    <!-- Top Revenue Products Table -->
    <div class="table-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div class="chart-title" style="margin: 0;">Top Revenue Generating Products</div>
            <button class="export-btn" onclick="exportTableToCSV('top-products.csv')">📥 Export CSV</button>
        </div>
        <table id="top-products-table">
            <thead>
                <tr>
                    <th style="width: 40px;">Rank</th>
                    <th>Product Name</th>
                    <th style="text-align: right;">Units Sold</th>
                    <th style="text-align: right;">Revenue Generated</th>
                    <th style="text-align: right;">Avg Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="5" style="text-align: center; padding: 40px; color: #9aa0a6;">No data available</td></tr>
                <?php else: foreach ($products as $idx => $prod): ?>
                    <tr>
                        <td><div class="rank-badge"><?php echo $idx + 1; ?></div></td>
                        <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                        <td style="text-align: right;"><?php echo number_format($prod['units_sold']); ?></td>
                        <td style="text-align: right; color: #51cf66; font-weight: 600;">₱<?php echo number_format($prod['revenue'], 2); ?></td>
                        <td style="text-align: right;">₱<?php echo number_format($prod['avg_price'], 2); ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>

<script>
// Wait for Chart.js to be loaded and DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Chart.js configuration for dark theme
        Chart.defaults.color = '#9aa0a6';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.03)';

        // Revenue Trend Chart
        <?php
        $labels = array();
        $data = array();
        foreach ($revenue_data as $item) {
            $labels[] = date('M d', strtotime($item['date']));
            $data[] = $item['revenue'];
        }
        ?>
        var revenueTrendLabels = <?php echo json_encode($labels); ?>;
        var revenueTrendData = <?php echo json_encode($data); ?>;
        
        console.log('Revenue Trend Labels:', revenueTrendLabels);
        console.log('Revenue Trend Data:', revenueTrendData);
        
        var ctxRevenue = document.getElementById('revenueTrendChart');
        if(ctxRevenue) {
            var revenueCtx = ctxRevenue.getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: revenueTrendLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: revenueTrendData,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239,68,68,0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Revenue by Product Chart
        <?php
        $productLabels = array();
        $productData = array();
        foreach ($products as $prod) {
            $productLabels[] = $prod['name'];
            $productData[] = $prod['revenue'];
        }
        ?>
        var productChartLabels = <?php echo json_encode($productLabels); ?>;
        var productChartData = <?php echo json_encode($productData); ?>;
        
        console.log('Product Labels:', productChartLabels);
        console.log('Product Data:', productChartData);
        
        var ctxProduct = document.getElementById('revenueByProductChart');
        if(ctxProduct) {
            var productCtx = ctxProduct.getContext('2d');
            new Chart(productCtx, {
                type: 'bar',
                data: {
                    labels: productChartLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: productChartData,
                        backgroundColor: ['#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16'],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Revenue by Category Chart
        <?php
        $categoryLabels = array();
        $categoryData = array();
        $categoryColors = ['#ef4444', '#3b82f6', '#51cf66', '#f59e0b', '#a78bfa'];
        $colorIdx = 0;
        foreach ($categories as $cat) {
            $categoryLabels[] = $cat['category_name'];
            $categoryData[] = $cat['revenue'];
        }
        ?>
        var categoryChartLabels = <?php echo json_encode($categoryLabels); ?>;
        var categoryChartData = <?php echo json_encode($categoryData); ?>;
        
        console.log('Category Labels:', categoryChartLabels);
        console.log('Category Data:', categoryChartData);
        
        var ctxCategory = document.getElementById('revenueByCategoryChart');
        if(ctxCategory) {
            var categoryCtx = ctxCategory.getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryChartLabels,
                    datasets: [{
                        data: categoryChartData,
                        backgroundColor: ['#ef4444', '#3b82f6', '#51cf66', '#f59e0b', '#a78bfa'],
                        borderColor: '#0f0f0f',
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        }

        // Payment Method Chart
        <?php
        $paymentLabels = array();
        $paymentData = array();
        foreach ($payment_methods as $pm) {
            $paymentLabels[] = ucfirst($pm['payment_method']);
            $paymentData[] = $pm['revenue'];
        }
        ?>
        var paymentChartLabels = <?php echo json_encode($paymentLabels); ?>;
        var paymentChartData = <?php echo json_encode($paymentData); ?>;
        
        console.log('Payment Labels:', paymentChartLabels);
        console.log('Payment Data:', paymentChartData);
        
        var ctxPayment = document.getElementById('paymentMethodChart');
        if(ctxPayment) {
            var paymentCtx = ctxPayment.getContext('2d');
            new Chart(paymentCtx, {
                type: 'bar',
                data: {
                    labels: paymentChartLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: paymentChartData,
                        backgroundColor: ['#51cf66', '#4c6ef5', '#a78bfa', '#f59e0b'],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Monthly Revenue Chart (Last 6 months)
        <?php
        $monthlyData = getMonthlySales(6);
        $monthlyLabels = $monthlyData['labels'];
        $monthlyValues = $monthlyData['data'];
        // Format labels to show month names
        $formattedMonthlyLabels = array();
        foreach ($monthlyLabels as $label) {
            $formattedMonthlyLabels[] = date('M', strtotime($label . '-01'));
        }
        ?>
        var monthlyChartLabels = <?php echo json_encode($formattedMonthlyLabels); ?>;
        var monthlyChartData = <?php echo json_encode($monthlyValues); ?>;
        
        console.log('Monthly Labels:', monthlyChartLabels);
        console.log('Monthly Data:', monthlyChartData);
        
        var ctxMonthly = document.getElementById('monthlyRevenueChart');
        if(ctxMonthly) {
            var monthlyCtx = ctxMonthly.getContext('2d');
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthlyChartLabels,
                    datasets: [{
                        label: 'Revenue',
                        data: monthlyChartData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#0f0f0f',
                        pointBorderWidth: 2,
                        borderWidth: 3,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            titleColor: '#fff',
                            bodyColor: '#e6e6e6',
                            borderColor: 'rgba(255,255,255,0.1)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.parsed.y.toLocaleString('en-US', { maximumFractionDigits: 2 });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: 'rgba(255,255,255,0.03)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255,255,255,0.03)'
                            }
                        }
                    }
                }
            });
        }
    } catch(error) {
        console.error('Chart initialization error:', error);
        console.error('Error stack:', error.stack);
    }
});

// Export to CSV
function exportTableToCSV(filename) {
    var csv = [];
    var rows = document.querySelectorAll('#top-products-table tr');
    
    rows.forEach(function(row) {
        var cells = row.querySelectorAll('td, th');
        var rowData = [];
        cells.forEach(function(cell) {
            rowData.push('"' + cell.innerText.replace(/"/g, '""') + '"');
        });
        csv.push(rowData.join(','));
    });
    
    var blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}
</script>
