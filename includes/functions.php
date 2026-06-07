<?php
/**
 * Utility Functions
 * Food Ordering System
 */

// Sanitize input
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8'));
}

// Format price
function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

// Format currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Generate order number
function generateOrderNumber() {
    return 'ORD' . date('YmdHis') . substr(uniqid(), -4);
}

// Get all categories
function getAllCategories() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM categories WHERE is_active = TRUE ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all food items
function getAllFoodItems($category_id = null, $search = null) {
    global $conn;
    
    $query = "SELECT f.*, c.name as category_name FROM food_items f 
              JOIN categories c ON f.category_id = c.id 
              WHERE f.is_available = TRUE";
    
    $params = array();
    $types = "";
    
    if ($category_id) {
        $query .= " AND f.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }
    
    if ($search) {
        $query .= " AND MATCH(f.name, f.description) AGAINST(? IN BOOLEAN MODE)";
        $params[] = $search;
        $types .= "s";
    }
    
    $query .= " ORDER BY f.name";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get food item by ID
function getFoodItemById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT f.*, c.name as category_name FROM food_items f 
                           JOIN categories c ON f.category_id = c.id 
                           WHERE f.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->fetch_assoc();
}

// Get cart total
function getCartTotal() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

// Get cart count
function getCartCount() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}

// Log activity (for admin)
function logActivity($admin_id, $action, $description = '') {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $admin_id, $action, $description, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
}

// Prevent direct access
function preventDirectAccess($file) {
    if (basename($_SERVER['PHP_SELF']) === basename($file)) {
        die('Direct access not permitted');
    }
}

// Check session timeout (30 minutes)
function checkSessionTimeout() {
    $timeout = 1800; // 30 minutes
    
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $timeout) {
            logoutUser();
            return false;
        }
        $_SESSION['login_time'] = time();
    }
    
    return true;
}

// Get user by ID
function getUserById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->fetch_assoc();
}

// Get order by ID
function getOrderById($id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->fetch_assoc();
}

// Get order items
function getOrderItems($order_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT oi.*, f.name as food_name, f.image FROM order_items oi 
                           JOIN food_items f ON oi.food_id = f.id 
                           WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Format date
function formatDate($date) {
    return date('M d, Y H:i', strtotime($date));
}

// Get order status badge class
function formatOrderStatusLabel($status) {
    $map = [
        'pending' => 'Pending',
        'preparing' => 'Preparing',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancellation_requested' => 'Cancellation Requested',
        'cancelled' => 'Cancelled',
        'confirmed' => 'Confirmed',
        'ready' => 'Ready'
    ];

    return isset($map[$status]) ? $map[$status] : ucfirst(str_replace('_', ' ', $status));
}

// Get order status badge class (custom theme classes)
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'badge-pending';
        case 'preparing':
            return 'badge-preparing';
        case 'out_for_delivery':
            return 'badge-out';
        case 'delivered':
            return 'badge-delivered';
        case 'cancellation_requested':
            return 'badge-cancellation-requested';
        case 'cancelled':
            return 'badge-cancelled';
        default:
            return 'badge-secondary';
    }
}

// Get payment status badge class
function getPaymentStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'badge-warning';
        case 'completed':
            return 'badge-success';
        case 'failed':
            return 'badge-danger';
        case 'refunded':
            return 'badge-secondary';
        default:
            return 'badge-secondary';
    }
}
?>
