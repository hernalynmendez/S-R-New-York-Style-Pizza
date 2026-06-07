<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin.php';

requireAdmin();
checkSessionTimeout();

header('Content-Type: application/json');

$months = isset($_GET['months']) ? (int)$_GET['months'] : 6;
$result = getMonthlySales($months);

echo json_encode($result);
exit;

?>
