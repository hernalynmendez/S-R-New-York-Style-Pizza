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
    
    // Total food items
    $result = $conn->query("SELECT COUNT(*) as count FROM food_items");
    $stats['total_items'] = $result->fetch_assoc()['count'];
    
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
?>
