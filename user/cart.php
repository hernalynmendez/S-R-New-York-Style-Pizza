<?php
/**
 * Shopping Cart
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'Shopping Cart';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . 'user/login.php');
    exit;
}

checkSessionTimeout();

require_once '../includes/header.php';
?>

<div class="page-background-cover" style="background-image: linear-gradient(135deg, rgba(227, 47, 47, 0.08) 0%, rgba(0, 0, 0, 0.75) 50%, rgba(0, 0, 0, 0.85) 100%), linear-gradient(to right, rgba(255, 87, 69, 0.05), transparent 50%, rgba(227, 47, 47, 0.05)), url('<?php echo SITE_URL; ?>uploads/profiles/lr3.jpg');">
    <div class="container py-5">
        <h2 class="mb-4">Shopping Cart</h2>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="menu.php">Continue shopping</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Food Item</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['cart'] as $food_id => $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo SITE_URL; ?>uploads/food/<?php echo htmlspecialchars($item['image']); ?>" 
                                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                                <?php endif; ?>
                                                <span><?php echo htmlspecialchars($item['name']); ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo formatPrice($item['price']); ?></td>
                                        <td>
                                            <form method="POST" action="cart_action.php" class="d-inline">
                                                <input type="hidden" name="food_id" value="<?php echo $food_id; ?>">
                                                <input type="hidden" name="action" value="update">
                                                <div class="input-group input-group-sm" style="width: 100px;">
                                                    <input type="number" name="quantity" class="form-control" value="<?php echo $item['quantity']; ?>" min="1" max="10">
                                                    <button class="btn btn-outline-secondary" type="submit">
                                                        <i class="fas fa-refresh"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                        <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                        <td>
                                            <form method="POST" action="cart_action.php" class="d-inline">
                                                <input type="hidden" name="food_id" value="<?php echo $food_id; ?>">
                                                <input type="hidden" name="action" value="remove">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Summary</h5>
                        <hr>
                        
                        <?php $total = getCartTotal(); ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery Fee:</span>
                            <span><?php echo formatPrice(2.50); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong><?php echo formatPrice($total + 2.50); ?></strong>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-credit-card"></i> Proceed to Checkout
                        </a>
                        <a href="menu.php" class="btn btn-outline-primary w-100">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
