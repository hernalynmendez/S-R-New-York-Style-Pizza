<?php
/**
 * CLI runner for ETL: executes `database/etl/etl_load.sql` using the MySQL client
 * and then displays the latest ETL run log entries.
 *
 * Usage:
 *   php scripts/run_etl.php [path/to/etl_load.sql]
 *
 * Notes:
 * - This script does not include `includes/config.php` to avoid sending HTTP headers in CLI.
 * - It reads DB connection values from .env if present, or falls back to defaults.
 */

$defaultFile = __DIR__ . '/../database/etl/etl_load.sql';
$sqlFile = $argv[1] ?? $defaultFile;

if (!file_exists($sqlFile)) {
    fwrite(STDERR, "ETL SQL file not found: $sqlFile\n");
    exit(2);
}

// simple .env parser (same semantics as includes/config.php)
$envPath = __DIR__ . '/../.env';
$env = [];
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = array_map('trim', explode('=', $line, 2));
        $value = preg_replace('/^"|"$|^\'|\'$/', '', $value);
        $env[$name] = $value;
    }
}

$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbUser = $env['DB_USER'] ?? 'root';
$dbPassword = $env['DB_PASSWORD'] ?? '';

// Build mysql client command. Note: passing password on cli may show in process list.
$mysqlBin = 'mysql';
$cmd = sprintf('%s -h %s -u %s', escapeshellcmd($mysqlBin), escapeshellarg($dbHost), escapeshellarg($dbUser));
if ($dbPassword !== '') {
    // safe-quote password
    $cmd .= ' -p' . escapeshellarg($dbPassword);
}

// run
fwrite(STDOUT, "Running ETL using SQL file: $sqlFile\n");
$fullCmd = $cmd . ' < ' . escapeshellarg($sqlFile) . ' 2>&1';
fwrite(STDOUT, "Command: $fullCmd\n");

$output = [];
exec($fullCmd, $output, $exitCode);

foreach ($output as $line) {
    fwrite(STDOUT, $line . PHP_EOL);
}

if ($exitCode !== 0) {
    fwrite(STDERR, "ETL process failed with exit code $exitCode\n");
    exit($exitCode);
}

fwrite(STDOUT, "ETL executed; querying ETL run log...\n");

// connect to MySQL via mysqli to show latest runs
$mysqli = new mysqli($dbHost, $dbUser, $dbPassword);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB connection failed: " . $mysqli->connect_error . "\n");
    exit(3);
}

$res = $mysqli->query("SELECT run_id, started_at, finished_at, status, rows_loaded, LEFT(IFNULL(error_message,''),200) AS error_short FROM food_dw.etl_run_log ORDER BY run_id DESC LIMIT 20");
if ($res) {
    $fmt = "%-6s %-20s %-20s %-10s %-10s %s\n";
    printf($fmt, 'run_id', 'started_at', 'finished_at', 'status', 'rows', 'error');
    while ($r = $res->fetch_assoc()) {
        printf($fmt, $r['run_id'], $r['started_at'] ?? '-', $r['finished_at'] ?? '-', $r['status'], $r['rows_loaded'], $r['error_short']);
    }
    $res->free();
}

$mysqli->close();

fwrite(STDOUT, "Done.\n");

exit(0);

?>
