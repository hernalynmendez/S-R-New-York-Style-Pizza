<?php
/**
 * Admin Functions
 * Food Ordering System
 */

// Check if user is admin
function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ' . SITE_URL . 'user/login.php');
        exit;
    }
}

// Add food item
function addFoodItem($category_id, $name, $description, $price, $is_vegetarian, $preparation_time, $quantity_in_stock) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO food_items (category_id, name, description, price, is_vegetarian, preparation_time, quantity_in_stock) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdiii", $category_id, $name, $description, $price, $is_vegetarian, $preparation_time, $quantity_in_stock);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('status' => true, 'message' => 'Food item added successfully');
    } else {
        $stmt->close();
        return array('status' => false, 'message' => 'Failed to add food item');
    }
}

// Update food item
function updateFoodItem($id, $category_id, $name, $description, $price, $is_vegetarian, $preparation_time, $quantity_in_stock) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE food_items SET category_id = ?, name = ?, description = ?, price = ?, is_vegetarian = ?, preparation_time = ?, quantity_in_stock = ? WHERE id = ?");
    $stmt->bind_param("issdiiii", $category_id, $name, $description, $price, $is_vegetarian, $preparation_time, $quantity_in_stock, $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('status' => true, 'message' => 'Food item updated successfully');
    } else {
        $stmt->close();
        return array('status' => false, 'message' => 'Failed to update food item');
    }
}

// Delete food item
function deleteFoodItem($id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM food_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('status' => true, 'message' => 'Food item deleted successfully');
    } else {
        $stmt->close();
        return array('status' => false, 'message' => 'Failed to delete food item');
    }
}

// Upload food image
function uploadFoodImage($file, $food_id) {
    if ($file['size'] > 5242880) { // 5MB limit
        return array('status' => false, 'message' => 'File size exceeds 5MB limit');
    }
    
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif');
    if (!in_array($file['type'], $allowed_types)) {
        return array('status' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed');
    }
    
    $filename = 'food_' . $food_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $filepath = UPLOAD_FOOD_PATH . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        global $conn;
        $stmt = $conn->prepare("UPDATE food_items SET image = ? WHERE id = ?");
        $stmt->bind_param("si", $filename, $food_id);
        $stmt->execute();
        $stmt->close();
        
        return array('status' => true, 'message' => 'Image uploaded successfully', 'filename' => $filename);
    }
    
    return array('status' => false, 'message' => 'Failed to upload file');
}

// Get all users
function getAllUsers() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE is_admin = FALSE ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get all orders
function getAllOrders() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o 
                           JOIN users u ON o.user_id = u.id 
                           ORDER BY o.created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Update order status
function updateOrderStatus($order_id, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('status' => true, 'message' => 'Order status updated');
    } else {
        $stmt->close();
        return array('status' => false, 'message' => 'Failed to update order status');
    }
}

// Update payment status
function updatePaymentStatus($order_id, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('status' => true, 'message' => 'Payment status updated');
    } else {
        $stmt->close();
        return array('status' => false, 'message' => 'Failed to update payment status');
    }
}

// Get dashboard statistics
function getDashboardStats() {
    global $conn;
    
    $stats = array();
    
    // Total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = FALSE");
    $stats['total_users'] = $result->fetch_assoc()['count'];
    
    // Total orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders");
    $stats['total_orders'] = $result->fetch_assoc()['count'];
    
    // Total revenue
    $result = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'");
    $total = $result->fetch_assoc()['total'];
    $stats['total_revenue'] = $total ? $total : 0;
    
    // Pending orders
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'pending'");
    $stats['pending_orders'] = $result->fetch_assoc()['count'];
    
    // Completed orders (delivered or completed status)
    $result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status IN ('delivered', 'completed')");
    $stats['completed_orders'] = $result->fetch_assoc()['count'];
    
    // Total food items
    $result = $conn->query("SELECT COUNT(*) as count FROM food_items");
    $stats['total_items'] = $result->fetch_assoc()['count'];
    
    // Orders today (created today with completed payment)
    $today_start = date('Y-m-d 00:00:00');
    $today_end = date('Y-m-d 23:59:59');
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = DATE(NOW()) AND payment_status = 'completed'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['orders_today'] = $result->fetch_assoc()['count'];
    $stmt->close();
    
    // Revenue today (created today with completed payment)
    $stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE DATE(created_at) = DATE(NOW()) AND payment_status = 'completed'");
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    $stats['revenue_today'] = $total ? $total : 0;
    $stmt->close();
    
    return $stats;
}

// Get low-stock food items (default threshold = 5)
function getLowStockItems($threshold = 5) {
    global $conn;

    $stmt = $conn->prepare("SELECT id, name, quantity_in_stock FROM food_items WHERE quantity_in_stock <= ? AND is_available = TRUE ORDER BY quantity_in_stock ASC");
    $stmt->bind_param("i", $threshold);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Get monthly sales for the past N months (returns labels and totals)
function getMonthlySales($months = 6) {
    global $conn;

    $months = (int)$months;
    if ($months < 1) $months = 6;

    // Build months array (YYYY-MM)
    $labels = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $labels[] = date('Y-m', strtotime("-{$i} months"));
    }

    // Prepare date range
    $start_date = date('Y-m-01 00:00:00', strtotime("-" . ($months - 1) . " months"));

    $stmt = $conn->prepare(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') as period, SUM(total_amount) as total
         FROM orders
         WHERE payment_status = 'completed' AND created_at >= ?
         GROUP BY period"
    );
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $totals = [];
    while ($row = $result->fetch_assoc()) {
        $totals[$row['period']] = (float)$row['total'];
    }
    $stmt->close();

    // Fill missing months with 0
    $data = [];
    foreach ($labels as $label) {
        $data[] = isset($totals[$label]) ? $totals[$label] : 0;
    }

    return ['labels' => $labels, 'data' => $data];
}

// Get top-selling products (by quantity) with optional limit
function getTopProducts($limit = 10) {
    global $conn;

    $limit = (int)$limit;
    if ($limit < 1) $limit = 10;

    $stmt = $conn->prepare(
        "SELECT f.id, f.name, SUM(oi.quantity) as total_quantity, SUM(oi.subtotal) as total_revenue
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         JOIN food_items f ON oi.food_id = f.id
         WHERE o.payment_status = 'completed'
         GROUP BY oi.food_id
         ORDER BY total_quantity DESC
         LIMIT ?"
    );
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'quantity' => (int)$row['total_quantity'],
            'revenue' => (float)$row['total_revenue']
        ];
    }
    $stmt->close();

    return $items;
}

// Deactivate user
function deactivateUser($user_id) {
    global $conn;
    
    $inactive = FALSE;
    $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    $stmt->bind_param("bi", $inactive, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('status' => true, 'message' => 'User deactivated');
    } else {
        $stmt->close();
        return array('status' => false, 'message' => 'Failed to deactivate user');
    }
}

// Activate user
function activateUser($user_id) {
    global $conn;
    
    $active = TRUE;
    $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    $stmt->bind_param("bi", $active, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return array('status' => true, 'message' => 'User activated');
    } else {
        $stmt->close();
        return array('status' => false, 'message' => 'Failed to activate user');
    }
}

// Get recent activity logs (with limit, default 5)
function getRecentActivity($limit = 5) {
    global $conn;

    $limit = (int)$limit;
    if ($limit < 1) $limit = 5;

    $stmt = $conn->prepare(
        "SELECT a.id, a.admin_id, a.action, a.description, a.created_at, u.username, u.first_name, u.last_name
         FROM activity_logs a
         LEFT JOIN users u ON a.admin_id = u.id
         ORDER BY a.created_at DESC
         LIMIT ?"
    );
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Format relative time (e.g., "2m ago", "1h ago")
function getRelativeTime($datetime) {
    $created = new DateTime($datetime);
    $now = new DateTime();
    $interval = $now->diff($created);

    if ($interval->days > 0) {
        return $interval->days . 'd';
    } elseif ($interval->h > 0) {
        return $interval->h . 'h';
    } elseif ($interval->i > 0) {
        return $interval->i . 'm';
    } else {
        return $interval->s . 's';
    }
}

// Get recent new orders with item details (last N orders)
function getRecentNewOrders($limit = 5) {
    global $conn;

    $limit = (int)$limit;
    if ($limit < 1) $limit = 5;

    $stmt = $conn->prepare(
        "SELECT o.id, o.order_number, o.total_amount, u.first_name, u.last_name, u.username, o.created_at,
                GROUP_CONCAT(CONCAT(oi.quantity, 'x ', f.name) SEPARATOR ', ') as items_summary,
                COUNT(oi.id) as item_count
         FROM orders o
         JOIN users u ON o.user_id = u.id
         LEFT JOIN order_items oi ON o.id = oi.order_id
         LEFT JOIN food_items f ON oi.food_id = f.id
         GROUP BY o.id
         ORDER BY o.created_at DESC
         LIMIT ?"
    );
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Get recent payments received
function getRecentPayments($limit = 5) {
    global $conn;

    $limit = (int)$limit;
    if ($limit < 1) $limit = 5;

    $stmt = $conn->prepare(
        "SELECT o.id, o.order_number, o.total_amount, o.payment_method, u.first_name, u.last_name, o.updated_at as completed_at
         FROM orders o
         JOIN users u ON o.user_id = u.id
         WHERE o.payment_status = 'completed'
         ORDER BY o.updated_at DESC
         LIMIT ?"
    );
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Get recent order deliveries
function getRecentDeliveries($limit = 5) {
    global $conn;

    $limit = (int)$limit;
    if ($limit < 1) $limit = 5;

    $stmt = $conn->prepare(
        "SELECT o.id, o.order_number, u.first_name, u.last_name, o.updated_at as delivered_at
         FROM orders o
         JOIN users u ON o.user_id = u.id
         WHERE o.order_status IN ('delivered', 'completed')
         ORDER BY o.updated_at DESC
         LIMIT ?"
    );
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Get recent cancelled orders
function getRecentCancellations($limit = 5) {
    global $conn;

    $limit = (int)$limit;
    if ($limit < 1) $limit = 5;

    $stmt = $conn->prepare(
        "SELECT o.id, o.order_number, u.first_name, u.last_name, o.updated_at as cancelled_at
         FROM orders o
         JOIN users u ON o.user_id = u.id
         WHERE o.order_status = 'cancelled'
         ORDER BY o.updated_at DESC
         LIMIT ?"
    );
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Get recent customer registrations
function getRecentRegistrations($limit = 5) {
    global $conn;

    $limit = (int)$limit;
    if ($limit < 1) $limit = 5;

    $stmt = $conn->prepare(
        "SELECT id, first_name, last_name, username, email, created_at
         FROM users
         WHERE is_admin = FALSE
         ORDER BY created_at DESC
         LIMIT ?"
    );
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Get unified recent business activity feed
function getRecentBusinessActivity($limit = 10) {
    $activities = array();
    
    // Get recent orders
    $new_orders = getRecentNewOrders(3);
    foreach ($new_orders as $order) {
        $activities[] = array(
            'type' => 'new_order',
            'icon' => '🍕',
            'color' => '#ff6b6b',
            'title' => 'New Order',
            'order_number' => $order['order_number'],
            'customer_name' => trim($order['first_name'] . ' ' . $order['last_name']) ?: $order['username'],
            'amount' => $order['total_amount'],
            'items_summary' => $order['items_summary'],
            'item_count' => $order['item_count'],
            'timestamp' => $order['created_at'],
            'time_ago' => getRelativeTime($order['created_at']),
            'order_id' => $order['id']
        );
    }
    
    // Get recent payments
    $payments = getRecentPayments(3);
    foreach ($payments as $payment) {
        $activities[] = array(
            'type' => 'payment',
            'icon' => '💰',
            'color' => '#51cf66',
            'title' => 'Payment Received',
            'order_number' => $payment['order_number'],
            'customer_name' => trim($payment['first_name'] . ' ' . $payment['last_name']),
            'amount' => $payment['total_amount'],
            'payment_method' => $payment['payment_method'],
            'timestamp' => $payment['completed_at'],
            'time_ago' => getRelativeTime($payment['completed_at']),
            'order_id' => $payment['id']
        );
    }
    
    // Get recent deliveries
    $deliveries = getRecentDeliveries(2);
    foreach ($deliveries as $delivery) {
        $activities[] = array(
            'type' => 'delivery',
            'icon' => '🚚',
            'color' => '#4c6ef5',
            'title' => 'Order Delivered',
            'order_number' => $delivery['order_number'],
            'customer_name' => trim($delivery['first_name'] . ' ' . $delivery['last_name']),
            'timestamp' => $delivery['delivered_at'],
            'time_ago' => getRelativeTime($delivery['delivered_at']),
            'order_id' => $delivery['id']
        );
    }
    
    // Get recent registrations
    $registrations = getRecentRegistrations(2);
    foreach ($registrations as $reg) {
        $activities[] = array(
            'type' => 'registration',
            'icon' => '👤',
            'color' => '#a78bfa',
            'title' => 'New Customer',
            'customer_name' => trim($reg['first_name'] . ' ' . $reg['last_name']) ?: $reg['username'],
            'email' => $reg['email'],
            'timestamp' => $reg['created_at'],
            'time_ago' => getRelativeTime($reg['created_at'])
        );
    }
    
    // Get recent cancellations
    $cancellations = getRecentCancellations(2);
    foreach ($cancellations as $cancel) {
        $activities[] = array(
            'type' => 'cancellation',
            'icon' => '❌',
            'color' => '#ff6b6b',
            'title' => 'Order Cancelled',
            'order_number' => $cancel['order_number'],
            'customer_name' => trim($cancel['first_name'] . ' ' . $cancel['last_name']),
            'timestamp' => $cancel['cancelled_at'],
            'time_ago' => getRelativeTime($cancel['cancelled_at']),
            'order_id' => $cancel['id']
        );
    }
    
    // Sort by timestamp descending and limit
    usort($activities, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return array_slice($activities, 0, $limit);
}

// Get revenue by product (for analytics)
function getRevenueByProduct($limit = 10, $days = 30) {
    global $conn;

    $limit = (int)$limit;
    $days = (int)$days;
    if ($limit < 1) $limit = 10;
    if ($days < 1) $days = 30;

    $start_date = date('Y-m-d 00:00:00', strtotime("-{$days} days"));

    $stmt = $conn->prepare(
        "SELECT f.id, f.name, SUM(oi.quantity) as units_sold, SUM(oi.subtotal) as revenue, 
                ROUND(SUM(oi.subtotal) / NULLIF(SUM(oi.quantity), 0), 2) as avg_price
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         JOIN food_items f ON oi.food_id = f.id
         WHERE o.payment_status = 'completed' AND o.created_at >= ?
         GROUP BY oi.food_id
         ORDER BY revenue DESC
         LIMIT ?"
    );
    $stmt->bind_param("si", $start_date, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Get revenue by category (for pie chart)
function getRevenueByCategory($days = 30) {
    global $conn;

    $days = (int)$days;
    if ($days < 1) $days = 30;

    $start_date = date('Y-m-d 00:00:00', strtotime("-{$days} days"));

    $stmt = $conn->prepare(
        "SELECT c.id, c.name, SUM(oi.subtotal) as revenue, COUNT(DISTINCT o.id) as order_count
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         JOIN food_items f ON oi.food_id = f.id
         JOIN categories c ON f.category_id = c.id
         WHERE o.payment_status = 'completed' AND o.created_at >= ?
         GROUP BY c.id
         ORDER BY revenue DESC"
    );
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Get revenue by payment method
function getRevenueByPaymentMethod($days = 30) {
    global $conn;

    $days = (int)$days;
    if ($days < 1) $days = 30;

    $start_date = date('Y-m-d 00:00:00', strtotime("-{$days} days"));

    $stmt = $conn->prepare(
        "SELECT payment_method, COUNT(*) as transaction_count, SUM(total_amount) as revenue
         FROM orders
         WHERE payment_status = 'completed' AND created_at >= ?
         GROUP BY payment_method
         ORDER BY revenue DESC"
    );
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Get daily revenue data for chart
function getDailyRevenueData($days = 30) {
    global $conn;

    $days = (int)$days;
    if ($days < 1) $days = 30;

    $start_date = date('Y-m-d 00:00:00', strtotime("-{$days} days"));

    $stmt = $conn->prepare(
        "SELECT DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as order_count
         FROM orders
         WHERE payment_status = 'completed' AND created_at >= ?
         GROUP BY DATE(created_at)
         ORDER BY date ASC"
    );
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $items;
}

// Get revenue insights
function getRevenueInsights($days = 30) {
    global $conn;

    $days = (int)$days;
    if ($days < 1) $days = 30;

    $start_date = date('Y-m-d 00:00:00', strtotime("-{$days} days"));
    $insights = array();

    // Best selling product
    $stmt = $conn->prepare(
        "SELECT f.name, SUM(oi.quantity) as qty
         FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         JOIN food_items f ON oi.food_id = f.id
         WHERE o.payment_status = 'completed' AND o.created_at >= ?
         GROUP BY f.id ORDER BY qty DESC LIMIT 1"
    );
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $insights['best_product'] = $row ? $row['name'] : 'N/A';
    $stmt->close();

    // Highest revenue day
    $stmt = $conn->prepare(
        "SELECT DATE(created_at) as date, SUM(total_amount) as revenue
         FROM orders
         WHERE payment_status = 'completed' AND created_at >= ?
         GROUP BY DATE(created_at) ORDER BY revenue DESC LIMIT 1"
    );
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $insights['highest_revenue_day'] = $row ? date('M d, Y', strtotime($row['date'])) : 'N/A';
    $insights['highest_revenue_amount'] = $row ? $row['revenue'] : 0;
    $stmt->close();

    // Most used payment method
    $stmt = $conn->prepare(
        "SELECT payment_method, COUNT(*) as count
         FROM orders
         WHERE payment_status = 'completed' AND created_at >= ?
         GROUP BY payment_method ORDER BY count DESC LIMIT 1"
    );
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $insights['top_payment_method'] = $row ? ucfirst($row['payment_method']) : 'N/A';
    $stmt->close();

    // Average order value
    $stmt = $conn->prepare(
        "SELECT AVG(total_amount) as avg_value
         FROM orders
         WHERE payment_status = 'completed' AND created_at >= ?"
    );
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $insights['avg_order_value'] = $row ? $row['avg_value'] : 0;
    $stmt->close();

    // Total revenue
    $stmt = $conn->prepare(
        "SELECT SUM(total_amount) as total
         FROM orders
         WHERE payment_status = 'completed' AND created_at >= ?"
    );
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $insights['total_revenue'] = $row ? $row['total'] : 0;
    $stmt->close();

    // Total orders
    $stmt = $conn->prepare(
        "SELECT COUNT(*) as count
         FROM orders
         WHERE payment_status = 'completed' AND created_at >= ?"
    );
    $stmt->bind_param("s", $start_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $insights['total_orders'] = $row ? $row['count'] : 0;
    $stmt->close();

    return $insights;
}
?>
