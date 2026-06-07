<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin.php';

requireAdmin();
checkSessionTimeout();

header('Content-Type: application/json');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$result = getTopProducts($limit);

echo json_encode($result);
exit;

?>
