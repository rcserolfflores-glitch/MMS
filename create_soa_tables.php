<?php
// create_soa_tables.php
// Run this from CLI or visit in browser (only on dev/local) to create SOA related tables
// Usage (CLI): php create_soa_tables.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql1 = "CREATE TABLE IF NOT EXISTS soa_invoices (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        invoice_number VARCHAR(64) DEFAULT NULL,
        patient_user_id INT DEFAULT NULL,
        subtotal DECIMAL(10,2) DEFAULT 0.00,
        discount DECIMAL(10,2) DEFAULT 0.00,
        tax DECIMAL(10,2) DEFAULT 0.00,
        payments DECIMAL(10,2) DEFAULT 0.00,
        total_due DECIMAL(10,2) DEFAULT 0.00,
        status VARCHAR(40) DEFAULT 'unpaid',
        issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        due_date DATE DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_patient (patient_user_id),
        INDEX idx_invoice_number (invoice_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql1);
    echo "OK: soa_invoices table created or already exists.\n";

    $sql2 = "CREATE TABLE IF NOT EXISTS soa_payments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT UNSIGNED DEFAULT NULL,
        patient_user_id INT DEFAULT NULL,
        amount DECIMAL(10,2) DEFAULT 0.00,
        gcash_ref_no VARCHAR(128) DEFAULT NULL,
        file_url VARCHAR(255) DEFAULT NULL,
        verified TINYINT(1) DEFAULT 0,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_invoice (invoice_id),
        INDEX idx_patient (patient_user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql2);
    echo "OK: soa_payments table created or already exists.\n";

    // ensure uploads dir exists for receipts
    $uploadDir = __DIR__ . '/assets/uploads/receipts/';
    if (!is_dir($uploadDir)) {
        if (mkdir($uploadDir, 0755, true)) {
            echo "OK: Created upload directory: assets/uploads/receipts/\n";
        } else {
            echo "WARN: Failed to create upload directory: assets/uploads/receipts/ -- check permissions.\n";
        }
    } else {
        echo "OK: Upload directory already exists: assets/uploads/receipts/\n";
    }

    // show partial definitions
    $stmt = $pdo->query("SHOW CREATE TABLE soa_invoices");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && isset($row['Create Table'])) {
        echo "--- soa_invoices definition (truncated) ---\n";
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
