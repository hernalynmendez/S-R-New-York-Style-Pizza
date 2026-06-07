<?php
/**
 * Cancel Order
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

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

// Only allow cancellation request if not already delivered, cancelled, or requested
if (in_array($order['order_status'], ['delivered', 'cancelled', 'cancellation_requested'])) {
    header('Location: ' . SITE_URL . 'user/orders.php');
    exit;
}

// Set previous_status and mark as cancellation requested
$new_status = 'cancellation_requested';
$previous = $order['order_status'];
$stmt = $conn->prepare("UPDATE orders SET previous_status = ?, order_status = ? WHERE id = ?");
$stmt->bind_param("ssi", $previous, $new_status, $order_id);
$stmt->execute();
$stmt->close();

// Redirect back to orders with a friendly notice
header('Location: ' . SITE_URL . 'user/orders.php?message=cancellation_requested');
exit;
?>
