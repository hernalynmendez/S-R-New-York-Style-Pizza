<?php
/**
 * User Orders
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'My Orders';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . 'user/login.php');
    exit;
}

checkSessionTimeout();

$user_id = $_SESSION['user_id'];

// Get user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once '../includes/header.php';
// compute stats and handle filters
$total_orders = count($orders);
$active_statuses = ['pending','preparing','out_for_delivery','cancellation_requested','confirmed','ready'];
$active_orders = 0;
$delivered_orders = 0;
$cancelled_orders = 0;
foreach ($orders as $o) {
    if ($o['order_status'] === 'delivered') $delivered_orders++;
    if ($o['order_status'] === 'cancelled') $cancelled_orders++;
    if (in_array($o['order_status'], $active_statuses)) $active_orders++;
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

function matches_filter($order, $filter) {
    if ($filter === 'all') return true;
    if ($filter === 'active') return in_array($order['order_status'], ['pending','preparing','out_for_delivery','cancellation_requested','confirmed','ready']);
    if ($filter === 'completed') return $order['order_status'] === 'delivered';
    if ($filter === 'cancelled') return $order['order_status'] === 'cancelled';
    if ($filter === 'cancellation_requested') return $order['order_status'] === 'cancellation_requested';
    return true;
}

?>

<div class="page-background-cover" style="background-image: linear-gradient(135deg, rgba(227, 47, 47, 0.08) 0%, rgba(0, 0, 0, 0.75) 50%, rgba(0, 0, 0, 0.85) 100%), linear-gradient(to right, rgba(255, 87, 69, 0.05), transparent 50%, rgba(227, 47, 47, 0.05)), url('<?php echo SITE_URL; ?>uploads/profiles/order.jpg');">
    <div class="container py-5">
        <h2 class="mb-3">My Orders</h2>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card kpi">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon bg-dark me-3"><i class="fas fa-list"></i></div>
                        <div>
                            <div class="value"><?php echo $total_orders; ?></div>
                            <div class="meta">Total Orders</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card kpi">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon bg-dark me-3"><i class="fas fa-spinner"></i></div>
                        <div>
                            <div class="value"><?php echo $active_orders; ?></div>
                            <div class="meta">Active Orders</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card kpi">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon bg-dark me-3"><i class="fas fa-truck"></i></div>
                        <div>
                            <div class="value"><?php echo $delivered_orders; ?></div>
                            <div class="meta">Delivered</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card kpi">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon bg-dark me-3"><i class="fas fa-times-circle"></i></div>
                        <div>
                            <div class="value"><?php echo $cancelled_orders; ?></div>
                            <div class="meta">Cancelled</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="btn-group" role="group" aria-label="Filters">
                <?php $filters = ['all' => 'All Orders', 'active' => 'Active Orders', 'completed' => 'Completed Orders', 'cancelled' => 'Cancelled Orders', 'cancellation_requested' => 'Cancellation Requested'];
                foreach ($filters as $key => $label): ?>
                    <a href="?filter=<?php echo $key; ?>" class="btn <?php echo $filter === $key ? 'btn-red' : 'btn-outline-light'; ?> btn-sm"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">No orders yet. <a href="menu.php">Start ordering</a></div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order):
                    if (!matches_filter($order, $filter)) continue;
                    $items = getOrderItems($order['id']);
                    $shortId = '#' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $order['order_number']), -7));
                    $cardClass = $order['order_status'] === 'cancelled' ? 'card card-cancelled' : 'card';
                ?>
                <div class="col-md-6 mb-4">
                    <div class="<?php echo $cardClass; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($shortId); ?> <small class="text-muted"><?php echo htmlspecialchars($order['order_number']); ?></small></h5>
                                    <p class="text-muted small"><?php echo formatDate($order['created_at']); ?></p>
                                </div>
                                <div class="text-end">
                                    <div class="mb-2"><span class="badge <?php echo getStatusBadgeClass($order['order_status']); ?>"><?php echo formatOrderStatusLabel($order['order_status']); ?></span></div>
                                    <?php if ($order['order_status'] === 'cancellation_requested'): ?>
                                        <div><span class="badge badge-cancellation-requested">Cancellation Requested</span></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-3 d-flex justify-content-between">
                                <div>
                                    <p class="mb-1"><strong>Items:</strong> <?php echo count($items); ?></p>
                                    <p class="mb-1"><strong>Payment:</strong> <span class="badge <?php echo getPaymentStatusBadgeClass($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span></p>
                                </div>
                                <div class="text-end">
                                    <p class="mb-1"><strong>Total:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                                </div>
                            </div>

                            <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary me-2"><i class="fas fa-eye"></i> View Details</a>
                            <?php if (!in_array($order['order_status'], ['cancelled','delivered','cancellation_requested'])): ?>
                                <a href="cancel_order.php?order_id=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Request cancellation for this order?')"><i class="fas fa-times"></i> Cancel</a>
                            <?php elseif ($order['order_status'] === 'cancellation_requested'): ?>
                                <span class="text-muted small">Cancellation pending review</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
