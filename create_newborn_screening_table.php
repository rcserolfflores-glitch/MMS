<?php
/**
 * Migration: create `newborn_screenings` table.
 * Run via CLI or browser once.
 */
require_once __DIR__ . '/db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS `newborn_screenings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `newborn_id` INT DEFAULT NULL,
  `baby_id` VARCHAR(64) DEFAULT NULL,
  `vit_k` TINYINT(1) DEFAULT 0,
  `hepa_b` TINYINT(1) DEFAULT 0,
  `bcg` TINYINT(1) DEFAULT 0,
  `newborn_screening` TINYINT(1) DEFAULT 0,
  `hearing_taken` TINYINT(1) DEFAULT 0,
  `hearing_result` VARCHAR(32) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_baby` (`baby_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Table `newborn_screenings` created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();

if (php_sapi_name() !== 'cli') echo "<p>Done. Check database for <code>newborn_screenings</code> table.</p>";

?>
