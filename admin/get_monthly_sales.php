<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin.php';

// Ensure user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

requireAdmin();

header('Content-Type: application/json');

$months = isset($_GET['months']) ? (int)$_GET['months'] : 6;
$result = getMonthlySales($months);

echo json_encode($result);
exit;

?>
