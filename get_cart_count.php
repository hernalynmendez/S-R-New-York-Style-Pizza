<!-- Food Ordering System - Get Cart Count API -->
<?php
/**
 * Get Cart Count (AJAX)
 * Food Ordering System
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

$count = getCartCount();

echo json_encode(['count' => $count]);
?>
