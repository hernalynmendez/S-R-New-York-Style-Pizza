<?php
/**
 * Database Configuration File
 * Food Ordering System
 */

// Load environment variables from .env (if present)
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        // remove surrounding quotes
        $value = preg_replace('/^["\']|["\']$/', '', $value);
        putenv("{$name}={$value}");
        $_ENV[$name] = $value;
    }
}

// Database credentials (from env or defaults)
$dbHost = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost';
$dbUser = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
$dbPassword = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
$dbName = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'food_ordering_system';

define('DB_HOST', $dbHost);
define('DB_USER', $dbUser);
define('DB_PASSWORD', $dbPassword);
define('DB_NAME', $dbName);

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants
define('SITE_URL', 'http://localhost/FoodSystem/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_FOOD_PATH', UPLOAD_PATH . 'food/');
define('UPLOAD_PROFILE_PATH', UPLOAD_PATH . 'profiles/');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
?>
