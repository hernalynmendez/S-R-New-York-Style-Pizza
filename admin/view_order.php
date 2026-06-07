<?php
/**
 * View Order Details
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

$page_title = 'Order Details';

requireAdmin();
checkSessionTimeout();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id === 0) {
    header('Location: ' . SITE_URL . 'admin/manage_orders.php');
    exit;
}

$order = getOrderById($order_id);

if (!$order) {
    header('Location: ' . SITE_URL . 'admin/manage_orders.php');
    exit;
}

$order_items = getOrderItems($order_id);
$user = getUserById($order['user_id']);

$success = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'update_status') {
        $status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
        $result = updateOrderStatus($order_id, $status);
        if ($result['status']) {
            $success = $result['message'];
            $order = getOrderById($order_id);
            logActivity($_SESSION['user_id'], 'UPDATE_ORDER_STATUS', 'Updated to ' . $status);
        }
    } elseif ($action === 'update_payment') {
        $payment_status = isset($_POST['payment_status']) ? sanitize($_POST['payment_status']) : '';
        $result = updatePaymentStatus($order_id, $payment_status);
        if ($result['status']) {
            $success = $result['message'];
            $order = getOrderById($order_id);
            logActivity($_SESSION['user_id'], 'UPDATE_PAYMENT_STATUS', 'Updated to ' . $payment_status);
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container py-5">
    <a href="manage_orders.php" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Back to Orders
    </a>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success ?? ''); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number'] ?? ''); ?></p>
                            <p><strong>Order Date:</strong> <?php echo formatDate($order['created_at']); ?></p>
                            <p><strong>Total Amount:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Food Item</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['food_name'] ?? ''); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo formatPrice($item['subtotal']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Delivery Address:</strong></p>
                    <p><?php echo htmlspecialchars($order['delivery_address'] ?? ''); ?></p>
                    <p><?php echo htmlspecialchars(($order['delivery_city'] ?? '') . (!empty($order['delivery_city']) && (!empty($order['delivery_state']) || !empty($order['delivery_postal_code'])) ? ', ' : '') . ($order['delivery_state'] ?? '') . (!empty($order['delivery_state']) && !empty($order['delivery_postal_code']) ? ' ' : '') . ($order['delivery_postal_code'] ?? '')); ?></p>
                    
                    <?php if (!empty($order['special_instructions'])): ?>
                        <p><strong>Special Instructions:</strong></p>
                        <p><?php echo htmlspecialchars($order['special_instructions'] ?? ''); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Update Order Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <div class="mb-3">
                            <label for="status" class="form-label">Order Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="pending" <?php echo $order['order_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $order['order_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="preparing" <?php echo $order['order_status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="ready" <?php echo $order['order_status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                <option value="delivered" <?php echo $order['order_status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['order_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Update Payment Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_payment">
                        <div class="mb-3">
                            <label for="payment_status" class="form-label">Payment Status</label>
                            <select class="form-control" id="payment_status" name="payment_status">
                                <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $order['payment_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                <option value="refunded" <?php echo $order['payment_status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Update Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
