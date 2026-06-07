<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

requireAdmin();
checkSessionTimeout();

$stats = getDashboardStats();
$low_stock_items = getLowStockItems(5);
$top_products = getTopProducts(5);
$orders = getAllOrders();

?>
<?php require_once '../includes/header.php'; ?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
:root{--bg:#0f0f0f;--bg-2:#141414;--accent:#ef4444;--card-radius:16px;--glass: rgba(255,255,255,0.03);} 
body{font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,Helvetica,Arial; background:linear-gradient(180deg,var(--bg),var(--bg-2)); color:#e6e6e6}
.admin-wrap{padding:28px}
.topbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px}
.brand{display:flex;align-items:center;gap:12px}
.brand img{width:44px;height:44px;border-radius:10px;object-fit:cover}
.brand h1{font-size:1.125rem;margin:0;font-weight:700;color:#fff}
.nav-pill{display:flex;gap:8px;align-items:center}
.nav-pill a{color:#bdbdbd;padding:8px 12px;border-radius:10px;text-decoration:none}
.nav-pill a.active, .nav-pill a:hover{background:rgba(255,255,255,0.02);color:var(--accent)}
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
.card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border-radius:var(--card-radius);box-shadow:0 6px 18px rgba(0,0,0,0.6);padding:16px}
.kpi .icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:linear-gradient(180deg,rgba(255,255,255,0.02), rgba(255,255,255,0.01));margin-right:12px}
.kpi .value{font-size:1.375rem;font-weight:700}
.kpi .meta{font-size:0.875rem;color:#9aa0a6}
.grid-2{display:grid;grid-template-columns:2fr 1fr;gap:16px}
.charts .card{padding:20px}
.section-title{font-size:0.95rem;margin-bottom:12px;color:#dfe7ea}
.list-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.03)}
.progress-wrap{height:10px;background:rgba(255,255,255,0.03);border-radius:8px;overflow:hidden}
.progress-bar{height:100%;background:linear-gradient(90deg,var(--accent),#ff7a7a)}
.table-wrap table{width:100%;border-collapse:collapse}
.table-wrap th,.table-wrap td{padding:10px;border-bottom:1px solid rgba(255,255,255,0.03);font-size:0.95rem}
.badge-status{padding:6px 10px;border-radius:999px;font-weight:600}
.status-completed{background:rgba(34,197,94,0.12);color:#22c55e}
.status-preparing{background:rgba(250,204,21,0.08);color:#f59e0b}
.status-out{background:rgba(59,130,246,0.08);color:#3b82f6}
.status-cancel{background:rgba(239,68,68,0.08);color:#ef4444}
@media(max-width:992px){.kpi-grid{grid-template-columns:repeat(2,1fr)}.grid-2{grid-template-columns:1fr} }
@media(max-width:576px){.kpi-grid{grid-template-columns:1fr}}
</style>

<style>
/* Recent Orders premium styles */
.recent-orders-card{background:linear-gradient(180deg, rgba(255,255,255,0.015), rgba(255,255,255,0.01));border:1px solid #1f1f1f;border-radius:16px;padding:18px;box-shadow:0 8px 30px rgba(0,0,0,0.6)}
.ro-controls .form-control{background:transparent;border:1px solid rgba(255,255,255,0.04);color:#e6e6e6}
.ro-table thead th{color:#cfd8da;border-bottom:1px solid rgba(255,255,255,0.03);background:transparent}
.ro-table tbody tr.ro-row{background:#161616;border-bottom:1px solid rgba(255,255,255,0.02);transition:transform 0.18s ease,box-shadow 0.18s ease}
.ro-table tbody tr.ro-row:hover{background:#1e1e1e;transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,0.6)}
.ro-table tbody tr.empty-row{background:transparent}
.ro-table tbody tr:nth-child(even).ro-row{background:rgba(255,255,255,0.02)}
.status-badge{display:inline-block;padding:6px 10px;border-radius:999px;font-weight:700;font-size:0.85rem;color:#fff}
.status-pending{background:linear-gradient(90deg,#ffb020,#ff8a00);box-shadow:0 6px 18px rgba(255,138,0,0.12)}
.status-preparing{background:linear-gradient(90deg,#60a5fa,#3b82f6);box-shadow:0 6px 18px rgba(59,130,246,0.12)}
.status-out{background:linear-gradient(90deg,#a78bfa,#7c3aed);box-shadow:0 6px 18px rgba(124,58,237,0.12)}
.status-completed{background:linear-gradient(90deg,#34d399,#10b981);box-shadow:0 6px 18px rgba(16,185,129,0.12)}
.status-cancel{background:linear-gradient(90deg,#fb7185,#ef4444);box-shadow:0 6px 18px rgba(239,68,68,0.12)}
.action-btn{width:40px;height:40px;border-radius:999px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);display:inline-flex;align-items:center;justify-content:center;color:#00d1ff;transition:transform 0.18s ease,box-shadow 0.18s ease}
.action-btn:hover{transform:scale(1.06);box-shadow:0 8px 20px rgba(14,165,233,0.12);color:#fff}
.btn.btn-icon{background:transparent;border:0;color:#9aa0a6}
.btn.btn-icon:hover{color:#fff}

/* Responsive: stack rows into cards on small screens */
@media(max-width:768px){
    .ro-table thead{display:none}
    .ro-table tbody tr.ro-row{display:block;padding:12px;border-radius:12px;margin-bottom:12px}
    .ro-table tbody tr.ro-row td{display:flex;justify-content:space-between;padding:6px 0;border-bottom:none}
}

</style>
</style>


    </div>

    <div class="kpi-grid">
        <div class="card kpi d-flex align-items-center">
            <div style="display:flex;align-items:center">
                <div class="icon"><i class="fas fa-users" style="color:var(--accent)"></i></div>
                <div>
                    <div class="value"><?php echo number_format($stats['total_users']); ?></div>
                    <div class="meta">Total Users <span style="color:#22c55e">↑ 4% this week</span></div>
                </div>
            </div>
        </div>

        <div class="card kpi d-flex align-items-center">
            <div style="display:flex;align-items:center">
                <div class="icon"><i class="fas fa-shopping-cart" style="color:var(--accent)"></i></div>
                <div>
                    <div class="value"><?php echo number_format($stats['total_orders']); ?></div>
                    <div class="meta">Total Orders <span style="color:#ef4444">↓ 3% last month</span></div>
                </div>
            </div>
        </div>

        <div class="card kpi d-flex align-items-center">
            <div style="display:flex;align-items:center">
                <div class="icon"><i class="fas fa-dollar-sign" style="color:var(--accent)"></i></div>
                <div>
                    <div class="value"><?php echo '₱' . number_format($stats['total_revenue'],2); ?></div>
                    <div class="meta">Total Revenue <span style="color:#22c55e">↑ 12% this month</span></div>
                </div>
            </div>
        </div>

        <div class="card kpi d-flex align-items-center">
            <div style="display:flex;align-items:center">
                <div class="icon"><i class="fas fa-hourglass-half" style="color:var(--accent)"></i></div>
                <div>
                    <div class="value"><?php echo number_format($stats['pending_orders']); ?></div>
                    <div class="meta">Pending Orders <span style="color:#f59e0b">↑ 2% today</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid-2 charts">
        <div class="card">
            <div class="section-title">Revenue Analytics</div>
            <div style="display:flex;gap:12px;align-items:center;margin-bottom:8px">
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-light" id="btn-daily">Daily</button>
                    <button class="btn btn-sm btn-outline-light active" id="btn-weekly">Weekly</button>
                    <button class="btn btn-sm btn-outline-light" id="btn-monthly">Monthly</button>
                </div>
                <div style="margin-left:auto;color:#9aa0a6">Peak: <strong>Sat 7PM</strong></div>
            </div>
            <canvas id="revenueChart" height="120"></canvas>
        </div>

        <div class="card">
            <div class="section-title">Best Selling Pizzas</div>
            <?php foreach ($top_products as $prod): ?>
                <div class="list-row">
                    <div>
                        <div style="font-weight:600"><?php echo htmlspecialchars($prod['name']); ?></div>
                        <div style="font-size:0.85rem;color:#9aa0a6">Revenue: ₱<?php echo number_format($prod['revenue'],2); ?> • Units: <?php echo $prod['quantity']; ?></div>
                    </div>
                    <div style="width:40%">
                        <div class="progress-wrap"><div class="progress-bar" style="width:<?php echo min(100, $prod['quantity'] * 2); ?>%"></div></div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="margin-top:12px">
                <div class="section-title">Inventory Monitoring</div>
                <?php foreach (['Mozzarella Cheese'=>70,'Pepperoni'=>40,'Dough'=>90,'Tomato Sauce'=>25,'Mushrooms'=>12] as $k=>$v): ?>
                    <div style="margin-bottom:8px">
                        <div style="display:flex;justify-content:space-between;font-size:0.9rem;color:#cdd6d9"><span><?php echo $k; ?></span><span><?php echo $v; ?>%</span></div>
                        <div class="progress-wrap"><div class="progress-bar" style="width:<?php echo $v; ?>%;background:<?php echo $v<25? 'rgba(239,68,68,0.85)':'linear-gradient(90deg,var(--accent),#ff7a7a)'; ?>"></div></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:16px;margin-top:18px;flex-direction:column">
        <div class="card recent-orders-card">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div>
                    <div class="section-title" style="font-size:1.05rem">Recent Orders</div>
                    <div style="color:#9aa0a6;font-size:0.9rem">Latest customer transactions</div>
                </div>

                <div class="ro-controls" style="display:flex;gap:8px;align-items:center">
                    <div class="input-group input-group-sm" style="min-width:220px">
                        <span class="input-group-text bg-transparent" style="border-right:0;color:#9aa0a6"><i class="fas fa-search"></i></span>
                        <input id="ro-search" type="text" class="form-control form-control-sm" placeholder="Search order, customer, amount...">
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="roFilter" data-bs-toggle="dropdown" aria-expanded="false">
                            Filter
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="roFilter">
                            <li><a class="dropdown-item" href="#">All</a></li>
                            <li><a class="dropdown-item" href="#">Pending <span class="dropdown-sign">P</span></a></li>
                            <li><a class="dropdown-item" href="#">Preparing <span class="dropdown-sign">PR</span></a></li>
                            <li><a class="dropdown-item" href="#">Out for Delivery <span class="dropdown-sign">OD</span></a></li>
                            <li><a class="dropdown-item" href="#">Completed <span class="dropdown-sign">C</span></a></li>
                            <li><a class="dropdown-item" href="#">Cancelled <span class="dropdown-sign">X</span></a></li>
                        </ul>
                    </div>

                    <button id="ro-export" class="btn btn-sm btn-outline-light" title="Export CSV"><i class="fas fa-file-export"></i></button>
                    <button id="ro-refresh" class="btn btn-sm btn-outline-light" title="Refresh"><i class="fas fa-sync-alt"></i></button>
                </div>
            </div>

            <div class="stat-cards" style="display:flex;gap:12px;margin-top:14px;flex-wrap:wrap">
                <div class="card" style="padding:10px 14px;border-radius:12px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border:1px solid #1f1f1f;min-width:160px">
                    <div style="font-size:0.8rem;color:#9aa0a6">Total Orders Today</div>
                    <div style="font-weight:700;font-size:1.25rem"><?php echo number_format($stats['orders_today'] ?? 0); ?></div>
                </div>
                <div class="card" style="padding:10px 14px;border-radius:12px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border:1px solid #1f1f1f;min-width:160px">
                    <div style="font-size:0.8rem;color:#9aa0a6">Total Revenue Today</div>
                    <div style="font-weight:700;font-size:1.25rem"><?php echo '₱' . number_format($stats['revenue_today'] ?? 0,2); ?></div>
                </div>
                <div class="card" style="padding:10px 14px;border-radius:12px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border:1px solid #1f1f1f;min-width:160px">
                    <div style="font-size:0.8rem;color:#9aa0a6">Pending Orders</div>
                    <div style="font-weight:700;font-size:1.25rem"><?php echo number_format($stats['pending_orders']); ?></div>
                </div>
                <div class="card" style="padding:10px 14px;border-radius:12px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border:1px solid #1f1f1f;min-width:160px">
                    <div style="font-size:0.8rem;color:#9aa0a6">Completed Orders</div>
                    <div style="font-weight:700;font-size:1.25rem"><?php echo number_format($stats['completed_orders'] ?? 0); ?></div>
                </div>
            </div>

            <div class="ro-table-wrap" style="margin-top:16px">
                <div class="table-responsive">
                    <table class="table ro-table align-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Date &amp; Time</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i=0; if(empty($orders)): ?>
                                <tr class="empty-row"><td colspan="7" style="padding:40px;text-align:center;color:#9aa0a6">📦 No Recent Orders<br><small style="color:#7a7a7a">Orders will appear here once customers place orders.</small></td></tr>
                            <?php else: foreach ($orders as $o): if($i++>9) break; 
                                $u = getUserById($o['user_id']);
                                $items = getOrderItems($o['id']);
                                $short = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','',$o['order_number']), -7));
                                $displayId = 'ORD-' . $short;
                                $st = $o['order_status'];
                                $statusClass = $st==='pending' ? 'status-pending' : ($st==='preparing'?'status-preparing':($st==='out_for_delivery'?'status-out':'status-completed'));
                            ?>
                            <tr class="ro-row">
                                <td style="font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, 'Roboto Mono', 'Courier New', monospace;"> 
                                    <span style="margin-right:8px;color:#9aa0a6;font-size:0.92rem"><?php echo $displayId; ?></span>
                                    <button class="btn btn-sm btn-icon" data-copy="<?php echo htmlspecialchars($o['order_number']); ?>" data-bs-toggle="tooltip" title="Copy full ID"><i class="fas fa-copy"></i></button>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <?php $initials = strtoupper(substr(($u['first_name']??'')[0] ?? '',0,1) . ($u['last_name']??'')[0] ?? ''); ?>
                                        <div style="width:40px;height:40px;border-radius:999px;background:#0b0b0b;border:1px solid rgba(255,255,255,0.04);display:flex;align-items:center;justify-content:center;font-weight:700"><?php echo $initials ?: 'U'; ?></div>
                                        <div>
                                            <div style="font-weight:700"><?php echo htmlspecialchars((($u['first_name']??'') . ' ' . ($u['last_name']??'')) ?: ($u['username'] ?? 'Guest')); ?></div>
                                            <div style="font-size:0.82rem;color:#9aa0a6">Customer ID #<?php echo $u['id'] ?? '—'; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:700;font-size:1rem;color:<?php echo ($o['payment_status'] ?? '')==='completed' ? '#22c55e' : '#fff'; ?>"><?php echo formatPrice($o['total_amount']); ?></div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst(str_replace('_',' ', $st)); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($o['payment_method'] ?? '—'); ?></td>
                                <td><?php echo formatDate($o['created_at']); ?></td>
                                <td class="text-end">
                                    <div style="display:flex;justify-content:flex-end;gap:8px">
                                        <button class="action-btn" data-bs-toggle="tooltip" title="View" onclick="location.href='view_order.php?id=<?php echo $o['id']; ?>'"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn" data-bs-toggle="tooltip" title="Edit" onclick="location.href='edit_order.php?id=<?php echo $o['id']; ?>'"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn" data-bs-toggle="tooltip" title="Invoice" onclick="window.open('invoice.php?order=<?php echo $o['id']; ?>','_blank')"><i class="fas fa-file-invoice"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:16px">
            <div class="card" style="flex:1">
                <div class="section-title">Recent Activity</div>
                <div>
                    <div class="list-row"> <div>✓ Order #102 Completed</div><div style="color:#9aa0a6">2m</div></div>
                    <div class="list-row"> <div>✓ New Customer Registered</div><div style="color:#9aa0a6">10m</div></div>
                    <div class="list-row"> <div>✓ Pizza Menu Updated</div><div style="color:#9aa0a6">1h</div></div>
                    <div class="list-row"> <div>✓ Inventory Restocked</div><div style="color:#9aa0a6">2h</div></div>
                    <div class="list-row"> <div>✓ Order #103 Pending</div><div style="color:#9aa0a6">3h</div></div>
                </div>
            </div>

            <div class="card" style="width:320px">
                <div class="section-title">Quick Actions</div>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <a href="add_food.php" class="btn btn-danger">+ Add Pizza</a>
                    <a href="manage_categories.php" class="btn btn-outline-light">+ Add Category</a>
                    <a href="manage_food.php" class="btn btn-outline-light">Manage Inventory</a>
                    <a href="#" class="btn btn-outline-light">View Reports</a>
                    <a href="#" class="btn btn-outline-light">Export Sales Data</a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>

<script>
// Fetch monthly sales (6 months) and render chart
function loadRevenue(months=6){
    fetch('get_monthly_sales.php?months='+months)
    .then(r=>r.json()).then(d=>{
        const ctx = document.getElementById('revenueChart').getContext('2d');
        if(window.revenueChart) window.revenueChart.destroy();
        window.revenueChart = new Chart(ctx,{
            type:'line',
            data:{labels:d.labels, datasets:[{label:'Revenue', data:d.data, borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,0.12)', fill:true, tension:0.3}]},
            options:{scales:{y:{beginAtZero:true}}, plugins:{legend:{display:false}}}
        });
    }).catch(console.error);
}

document.getElementById('btn-weekly').addEventListener('click', ()=>{loadRevenue(6);});
document.getElementById('btn-monthly').addEventListener('click', ()=>{loadRevenue(12);});
document.getElementById('btn-daily').addEventListener('click', ()=>{loadRevenue(1);});

// init
loadRevenue(6);
</script>

<script>
// Recent Orders interactions: tooltips, copy, search, export, refresh
document.addEventListener('DOMContentLoaded', function(){
    // enable bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined') {
        var tipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tipTriggerList.map(function (el) { return new bootstrap.Tooltip(el) })
    }

    // copy to clipboard
    document.querySelectorAll('[data-copy]').forEach(function(btn){
        btn.addEventListener('click', function(e){
            var txt = this.getAttribute('data-copy') || '';
            navigator.clipboard.writeText(txt).then(function(){
                // show tooltip change
                if(typeof bootstrap !== 'undefined'){
                    var t = bootstrap.Tooltip.getInstance(btn);
                    if(t){ t.hide(); btn.setAttribute('data-bs-original-title','Copied'); t.show(); setTimeout(()=>{ btn.setAttribute('data-bs-original-title','Copy full ID'); },1200);} 
                }
            });
        });
    });

    // refresh button
    var refresh = document.getElementById('ro-refresh');
    if(refresh) refresh.addEventListener('click', function(){
        // simple reload for now
        location.reload();
    });

    // export csv
    var exp = document.getElementById('ro-export');
    if(exp) exp.addEventListener('click', function(){
        var rows = [['Order ID','Customer','Amount','Status','Payment','Date']];
        document.querySelectorAll('.ro-table tbody tr.ro-row').forEach(function(r){
            var cells = r.querySelectorAll('td');
            if(cells.length>=6){
                var id = cells[0].innerText.trim();
                var cust = cells[1].innerText.trim();
                var amount = cells[2].innerText.trim();
                var status = cells[3].innerText.trim();
                var pay = cells[4].innerText.trim();
                var dt = cells[5].innerText.trim();
                rows.push([id,cust,amount,status,pay,dt]);
            }
        });
        var csv = rows.map(r=>r.map(c=>'"'+c.replace(/"/g,'""')+'"').join(',')).join('\n');
        var blob = new Blob([csv],{type:'text/csv;charset=utf-8;'});
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a'); a.href = url; a.download = 'recent-orders.csv'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
    });

    // search filter
    var search = document.getElementById('ro-search');
    if(search){
        search.addEventListener('input', function(){
            var q = this.value.toLowerCase().trim();
            document.querySelectorAll('.ro-table tbody tr.ro-row').forEach(function(r){
                var text = r.innerText.toLowerCase();
                r.style.display = q === '' || text.indexOf(q) !== -1 ? '' : 'none';
            });
        });
    }
});
</script>
