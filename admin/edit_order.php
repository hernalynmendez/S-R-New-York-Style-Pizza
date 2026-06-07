<?php
/**
 * Edit Order (Admin)
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

requireAdmin();
checkSessionTimeout();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    header('Location: ' . SITE_URL . 'admin/manage_orders.php');
    exit;
}

$order = getOrderById($order_id);
if (!$order) {
    header('Location: ' . SITE_URL . 'admin/manage_orders.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'update_status') {
        $status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
        $res = updateOrderStatus($order_id, $status);
        if ($res['status']) {
            $message = 'Order status updated.';
            logActivity($_SESSION['user_id'], 'UPDATE_ORDER_STATUS', 'Order ' . $order_id . ' status updated to ' . $status);
            $order = getOrderById($order_id);
        } else {
            $error = $res['message'];
        }
    } elseif ($action === 'update_payment') {
        $payment = isset($_POST['payment_status']) ? sanitize($_POST['payment_status']) : '';
        $res = updatePaymentStatus($order_id, $payment);
        if ($res['status']) {
            $message = 'Payment status updated.';
            logActivity($_SESSION['user_id'], 'UPDATE_PAYMENT_STATUS', 'Order ' . $order_id . ' payment updated to ' . $payment);
            $order = getOrderById($order_id);
        } else {
            $error = $res['message'];
        }
    }
}

$items = getOrderItems($order_id);

require_once '../includes/header.php';
?>
<div class="container-fluid py-4">
    <h2 class="mb-4">Edit Order</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <h5>Order: <?php echo htmlspecialchars($order['order_number']); ?></h5>
                    <p class="small text-muted"><?php echo formatDate($order['created_at']); ?></p>
                    <p><strong>Customer ID:</strong> <?php echo $order['user_id']; ?></p>
                    <p><strong>Total:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Update Status</h5>
                    <form method="post">
                        <input type="hidden" name="action" value="update_status">
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                <?php $statuses = ['pending','preparing','out_for_delivery','delivered','cancellation_requested','cancelled','confirmed','ready'];
                                foreach ($statuses as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $order['order_status'] === $s ? 'selected' : ''; ?>><?php echo formatOrderStatusLabel($s); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary" type="submit">Update Status</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Update Payment</h5>
                    <form method="post">
                        <input type="hidden" name="action" value="update_payment">
                        <div class="mb-3">
                            <select name="payment_status" class="form-select">
                                <?php $p = ['pending','completed','failed','refunded']; foreach ($p as $ps): ?>
                                    <option value="<?php echo $ps; ?>" <?php echo $order['payment_status'] === $ps ? 'selected' : ''; ?>><?php echo ucfirst($ps); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary" type="submit">Update Payment</button>
                    </form>
                </div>
            </div>

        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Items</h5>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($items as $it): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent">
                                <div>
                                    <strong><?php echo htmlspecialchars($it['food_name']); ?></strong>
                                    <div class="small text-muted">Qty: <?php echo $it['quantity']; ?> • Price: <?php echo formatPrice($it['price']); ?></div>
                                </div>
                                <div><?php echo formatPrice($it['subtotal']); ?></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-3 text-end">
                        <a class="btn btn-outline-primary" href="invoice.php?order=<?php echo $order_id; ?>" target="_blank">Open Invoice</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
