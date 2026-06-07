<?php
/**
 * User Logout
 * Food Ordering System
 */

require_once '../includes/config.php';
require_once '../includes/auth.php';

logoutUser();
header('Location: ' . SITE_URL);
exit;
?>
