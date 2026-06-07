<?php
/**
 * Manage Orders
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/admin.php';

$page_title = 'Manage Orders';

requireAdmin();
checkSessionTimeout();

$success = '';
$error = '';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    
    if ($action === 'update_status') {
        $status = isset($_POST['status']) ? sanitize($_POST['status']) : '';
        $result = updateOrderStatus($order_id, $status);
        if ($result['status']) {
            $success = $result['message'];
            logActivity($_SESSION['user_id'], 'UPDATE_ORDER_STATUS', 'Order ' . $order_id . ' status updated to ' . $status);
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'update_payment') {
        $payment_status = isset($_POST['payment_status']) ? sanitize($_POST['payment_status']) : '';
        $result = updatePaymentStatus($order_id, $payment_status);
        if ($result['status']) {
            $success = $result['message'];
            logActivity($_SESSION['user_id'], 'UPDATE_PAYMENT_STATUS', 'Order ' . $order_id . ' payment status updated to ' . $payment_status);
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'approve_cancellation') {
        // Approve a pending cancellation request: set order_status to cancelled and clear previous_status
        $stmt = $conn->prepare("UPDATE orders SET order_status = 'cancelled', previous_status = NULL WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            $success = 'Cancellation approved and order cancelled.';
            logActivity($_SESSION['user_id'], 'APPROVE_CANCELLATION', 'Approved cancellation for order ' . $order_id);
        } else {
            $error = 'Failed to approve cancellation.';
        }
        $stmt->close();
    } elseif ($action === 'reject_cancellation') {
        // Reject cancellation: restore previous_status and clear previous_status
        $stmt = $conn->prepare("UPDATE orders SET order_status = COALESCE(previous_status, 'pending'), previous_status = NULL WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            $success = 'Cancellation request rejected; order restored to previous status.';
            logActivity($_SESSION['user_id'], 'REJECT_CANCELLATION', 'Rejected cancellation for order ' . $order_id);
        } else {
            $error = 'Failed to reject cancellation.';
        }
        $stmt->close();
    }
}

$orders = getAllOrders();

require_once '../includes/header.php';
?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Manage Orders</h2>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Orders List</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Order Status</th>
                        <th>Payment Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td><span class="badge <?php echo getStatusBadgeClass($order['order_status']); ?>"><?php echo formatOrderStatusLabel($order['order_status']); ?></span></td>
                            <td><span class="badge <?php echo getPaymentStatusBadgeClass($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                            <td><?php echo formatDate($order['created_at']); ?></td>
                            <td>
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php if ($order['order_status'] === 'cancellation_requested'): ?>
                                    <form method="post" style="display:inline-block;margin-left:6px;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="action" value="approve_cancellation">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve cancellation and cancel this order?')">Approve</button>
                                    </form>
                                    <form method="post" style="display:inline-block;margin-left:6px;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="action" value="reject_cancellation">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject cancellation request and keep order active?')">Reject</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
