<?php
// CLI script to create or update an admin user
// Usage: php scripts/create_admin.php <username> <password>

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from the command line.\n";
    exit(1);
}

$argv0 = isset($argv[0]) ? $argv[0] : 'create_admin.php';
if (!isset($argv[1]) || !isset($argv[2])) {
    echo "Usage: php {$argv0} <username> <password>\n";
    exit(1);
}

$username = $argv[1];
$password = $argv[2];

// Load env file (simple parser)
$envPath = __DIR__ . '/../.env';
$env = [];
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        $value = preg_replace('/^["\']|["\']$/', '', $value);
        $env[$name] = $value;
    }
}

$dbHost = isset($env['DB_HOST']) ? $env['DB_HOST'] : 'localhost';
$dbUser = isset($env['DB_USER']) ? $env['DB_USER'] : 'root';
$dbPassword = isset($env['DB_PASSWORD']) ? $env['DB_PASSWORD'] : '';
$dbName = isset($env['DB_NAME']) ? $env['DB_NAME'] : 'food_ordering_system';

// Connect to DB
$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
if ($conn->connect_error) {
    fwrite(STDERR, "DB connection failed: " . $conn->connect_error . "\n");
    exit(1);
}

$username = $conn->real_escape_string($username);

$hash = password_hash($password, PASSWORD_BCRYPT);

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $user_id = (int)$row['id'];
    $stmt->close();

    $update = $conn->prepare("UPDATE users SET password = ?, is_admin = 1, is_active = 1 WHERE id = ?");
    $update->bind_param('si', $hash, $user_id);
    if ($update->execute()) {
        echo "Updated admin user '{$username}' (id={$user_id}).\n";
        $update->close();
        exit(0);
    } else {
        fwrite(STDERR, "Failed to update user: " . $conn->error . "\n");
        $update->close();
        exit(1);
    }

} else {
    $stmt->close();
    $email = $username . '@local';
    $first = 'Admin';
    $last = 'User';
    $insert = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, is_admin, is_active) VALUES (?, ?, ?, ?, ?, 1, 1)");
    $insert->bind_param('ssss', $username, $email, $hash, $first, $last);
    if ($insert->execute()) {
        $newId = $conn->insert_id;
        echo "Created admin user '{$username}' (id={$newId}).\n";
        $insert->close();
        exit(0);
    } else {
        fwrite(STDERR, "Failed to create user: " . $conn->error . "\n");
        $insert->close();
        exit(1);
    }
}

?>
