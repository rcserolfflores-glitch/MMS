<?php
// Simple PDO connection helper. Usage: $pdo = require __DIR__ . '/db.php';

// Harden: do not echo PHP errors to API responses; log instead
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$config = require __DIR__ . '/config.php';

$host    = $config['db_host'];
$db      = $config['db_name'];
$user    = $config['db_user'];
$pass    = $config['db_pass'];
$charset = $config['db_charset'] ?? 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // For API usage: return JSON error and exit to avoid HTML dump
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    } else {
        throw $e;
    }
}

return $pdo;