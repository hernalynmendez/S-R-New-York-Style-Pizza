<?php
/**
 * Checkout
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = 'Checkout';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . 'user/login.php');
    exit;
}

checkSessionTimeout();

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: ' . SITE_URL . 'user/cart.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $delivery_address = isset($_POST['delivery_address']) ? sanitize($_POST['delivery_address']) : '';
    $delivery_city = isset($_POST['delivery_city']) ? sanitize($_POST['delivery_city']) : '';
    $delivery_state = isset($_POST['delivery_state']) ? sanitize($_POST['delivery_state']) : '';
    $delivery_postal_code = isset($_POST['delivery_postal_code']) ? sanitize($_POST['delivery_postal_code']) : '';
    $payment_method = isset($_POST['payment_method']) ? sanitize($_POST['payment_method']) : '';
    $special_instructions = isset($_POST['special_instructions']) ? sanitize($_POST['special_instructions']) : '';
    
    if (empty($delivery_address) || empty($delivery_city) || empty($payment_method)) {
        $error = 'Please fill all required fields';
    } else {
        // Calculate total
        $total = getCartTotal() + 2.50; // Add delivery fee

        // Generate order number
        $order_number = generateOrderNumber();

        // Start DB transaction
        $conn->begin_transaction();

        try {
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders 
                (user_id, order_number, total_amount, delivery_address, delivery_city, delivery_state, delivery_postal_code, payment_method, order_status, payment_status, special_instructions) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $order_status = 'pending';
            $payment_status = 'pending';

            $stmt->bind_param(
                "isdssssssss",
                $user_id,
                $order_number,
                $total,
                $delivery_address,
                $delivery_city,
                $delivery_state,
                $delivery_postal_code,
                $payment_method,
                $order_status,
                $payment_status,
                $special_instructions
            );

            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception('Failed to create order');
            }

            $order_id = $conn->insert_id;
            $stmt->close();

            // Add order items and update inventory (with row locking)
            foreach ($_SESSION['cart'] as $food_id => $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $price = (float)$item['price'];
                $quantity = (int)$item['quantity'];

                // Lock the food row for update to prevent race conditions
                $lockStmt = $conn->prepare("SELECT quantity_in_stock FROM food_items WHERE id = ? FOR UPDATE");
                $lockStmt->bind_param("i", $food_id);
                $lockStmt->execute();
                $lockRes = $lockStmt->get_result();
                if ($lockRes->num_rows === 0) {
                    $lockStmt->close();
                    throw new Exception('Food item not found (ID ' . $food_id . ')');
                }
                $row = $lockRes->fetch_assoc();
                $lockStmt->close();

                if ($row['quantity_in_stock'] < $quantity) {
                    throw new Exception('Insufficient stock for ' . ($item['name'] ?? 'item ' . $food_id));
                }

                $stmt = $conn->prepare("INSERT INTO order_items (order_id, food_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiidd", $order_id, $food_id, $quantity, $price, $subtotal);

                if (!$stmt->execute()) {
                    $stmt->close();
                    throw new Exception('Failed to create order items');
                }
                $stmt->close();

                // Deduct inventory
                $updateStmt = $conn->prepare("UPDATE food_items SET quantity_in_stock = quantity_in_stock - ? WHERE id = ?");
                $updateStmt->bind_param("ii", $quantity, $food_id);
                if (!$updateStmt->execute()) {
                    $updateStmt->close();
                    throw new Exception('Failed to update inventory for item ' . $food_id);
                }
                $updateStmt->close();
            }

            // Create payment record
            $stmt = $conn->prepare("INSERT INTO payments (order_id, amount, payment_method, payment_status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idss", $order_id, $total, $payment_method, $payment_status);
            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception('Failed to create payment record');
            }
            $stmt->close();

            // Commit transaction
            $conn->commit();

            // Clear cart and redirect
            unset($_SESSION['cart']);
            header('Location: ' . SITE_URL . 'user/order_success.php?order_id=' . $order_id);
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Failed to create order: ' . $e->getMessage();
        }
    }
}

$user = getCurrentUser();

require_once '../includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">Checkout</h2>
    
    <div class="row">
        <form method="POST" class="row w-100">
        <div class="col-md-8">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                        <div class="mb-3">
                            <label for="delivery_address" class="form-label">Delivery Address *</label>
                            <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="delivery_city" class="form-label">City *</label>
                                <input type="text" class="form-control" id="delivery_city" name="delivery_city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="delivery_state" class="form-label">State</label>
                                <input type="text" class="form-control" id="delivery_state" name="delivery_state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="delivery_postal_code" class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="delivery_postal_code" name="delivery_postal_code" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                        </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cash_on_delivery" required checked>
                            <label class="form-check-label" for="cod">
                                <strong>Cash on Delivery</strong>
                                <p class="text-muted small">Pay when your order arrives</p>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="online" value="online_payment" required>
                            <label class="form-check-label" for="online">
                                <strong>Online Payment</strong>
                                <p class="text-muted small">Credit/Debit Card, Digital Wallet</p>
                            </label>
                        </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Special Instructions</h5>
                </div>
                <div class="card-body">
                        <textarea class="form-control" name="special_instructions" rows="3" placeholder="Any special requests for your order?"></textarea>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title">Order Summary</h5>
                    <hr>
                    
                    <div style="max-height: 300px; overflow-y: auto;" class="mb-3">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="d-flex justify-content-between small mb-2">
                                <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                                <span><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <?php 
                    $subtotal = getCartTotal();
                    $delivery_fee = 2.50;
                    $total = $subtotal + $delivery_fee;
                    ?>
                    
                    <div class="d-flex justify-content-between small mb-2">
                        <span>Subtotal:</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span>Delivery Fee:</span>
                        <span><?php echo formatPrice($delivery_fee); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong><?php echo formatPrice($total); ?></strong>
                    </div>
                    
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check"></i> Place Order
                        </button>
                </div>
            </div>
        </div>
    </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
