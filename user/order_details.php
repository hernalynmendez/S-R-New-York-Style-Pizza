<?php
/**
 * Order Details
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'Order Details';

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

$order_items = getOrderItems($order_id);

require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8">
            <h2 class="mb-4">Order Details</h2>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Order Number:</strong></p>
                            <p><?php echo htmlspecialchars($order['order_number']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Order Date:</strong></p>
                            <p><?php echo formatDate($order['created_at']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order Status:</strong></p>
                            <p><span class="badge <?php echo getStatusBadgeClass($order['order_status']); ?>"><?php echo ucfirst(str_replace('_', ' ', $order['order_status'])); ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Payment Status:</strong></p>
                            <p><span class="badge <?php echo getPaymentStatusBadgeClass($order['payment_status']); ?>"><?php echo ucfirst($order['payment_status']); ?></span></p>
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
                                    <td><?php echo htmlspecialchars($item['food_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo formatPrice($item['subtotal']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Delivery Address:</strong></p>
                    <p><?php echo htmlspecialchars($order['delivery_address']); ?>, <?php echo htmlspecialchars($order['delivery_city']); ?></p>
                    
                    <?php if (!empty($order['special_instructions'])): ?>
                        <p><strong>Special Instructions:</strong></p>
                        <p><?php echo htmlspecialchars($order['special_instructions']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Amount Details</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $subtotal = $order['total_amount'] - 2.50;
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Fee:</span>
                        <span><?php echo formatPrice(2.50); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
