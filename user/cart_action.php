<?php
/**
 * Cart Actions
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

$action = isset($_POST['action']) ? $_POST['action'] : '';
$food_id = isset($_POST['food_id']) ? (int)$_POST['food_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

switch ($action) {
    case 'add':
        if ($food_id > 0 && $quantity > 0) {
            $food = getFoodItemById($food_id);
            if ($food) {
                // Check if item already in cart
                if (isset($_SESSION['cart'][$food_id])) {
                    $_SESSION['cart'][$food_id]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$food_id] = array(
                        'id' => $food['id'],
                        'name' => $food['name'],
                        'price' => $food['price'],
                        'image' => $food['image'],
                        'quantity' => $quantity
                    );
                }
            }
        }
        break;
    
    case 'update':
        if ($food_id > 0 && $quantity > 0) {
            if (isset($_SESSION['cart'][$food_id])) {
                $_SESSION['cart'][$food_id]['quantity'] = $quantity;
            }
        }
        break;
    
    case 'remove':
        if ($food_id > 0 && isset($_SESSION['cart'][$food_id])) {
            unset($_SESSION['cart'][$food_id]);
        }
        break;
    
    case 'clear':
        $_SESSION['cart'] = array();
        break;
}

// Redirect back to cart
header('Location: ' . SITE_URL . 'user/cart.php');
exit;
?>
