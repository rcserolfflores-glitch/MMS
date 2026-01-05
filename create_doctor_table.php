<?php
// create_doctor_table.php
// Run this from CLI or visit in browser (only on dev/local) to create the doctor_info table
// Usage (CLI): php create_doctor_table.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS doctor_info (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255),
        specialty VARCHAR(255),
        phone VARCHAR(60),
        email VARCHAR(255),
        clinic_address TEXT,
        bio TEXT,
        avatar_url VARCHAR(255) NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY ux_doctor_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    echo "OK: doctor_info table created or already exists.\n";

    // ensure uploads dir exists
    $uploadDir = __DIR__ . '/assets/uploads/avatars/';
    if (!is_dir($uploadDir)) {
        if (mkdir($uploadDir, 0755, true)) {
            echo "OK: Created upload directory: assets/uploads/avatars/\n";
        } else {
            echo "WARN: Failed to create upload directory: assets/uploads/avatars/ -- check permissions.\n";
        }
    } else {
        echo "OK: Upload directory already exists: assets/uploads/avatars/\n";
    }

    // Optionally, you can inspect the table structure
    $stmt = $pdo->query("SHOW CREATE TABLE doctor_info");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['Create Table'])) {
        echo "--- table definition (truncated) ---\n";
        $def = $row['Create Table'];
        echo substr($def, 0, 800) . (strlen($def) > 800 ? "...\n" : "\n");
    }

    exit(0);

} catch (PDOException $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    if (php_sapi_name() !== 'cli') {
        echo "<pre>ERROR: " . htmlspecialchars($e->getMessage()) . "</pre>";
    }
    exit(1);
}
