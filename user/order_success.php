<?php
/**
 * Order Success
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'Order Success';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . 'user/login.php');
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id === 0) {
    header('Location: ' . SITE_URL . 'user/orders.php');
    exit;
}

$order = getOrderById($order_id);

// Verify order belongs to current user
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: ' . SITE_URL . 'user/orders.php');
    exit;
}

require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h2 class="mb-2">Order Placed Successfully!</h2>
                    <p class="text-muted mb-4">Thank you for your order. Your delicious food will be delivered soon.</p>
                    
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <p class="mb-2"><strong>Order Number:</strong></p>
                            <p class="h5 mb-3"><?php echo htmlspecialchars($order['order_number']); ?></p>
                            
                            <p class="mb-2"><strong>Total Amount:</strong></p>
                            <p class="h5 mb-3"><?php echo formatPrice($order['total_amount']); ?></p>
                            
                            <p class="mb-2"><strong>Payment Method:</strong></p>
                            <p class="mb-3"><?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($order['payment_method']))); ?></p>
                            
                            <p class="mb-2"><strong>Estimated Delivery:</strong></p>
                            <p class="mb-0">30-45 minutes</p>
                        </div>
                    </div>
                    
                    <a href="<?php echo SITE_URL; ?>user/orders.php" class="btn btn-primary mb-2 w-100">
                        <i class="fas fa-list"></i> View My Orders
                    </a>
                    <a href="<?php echo SITE_URL; ?>user/menu.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-shopping-cart"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
