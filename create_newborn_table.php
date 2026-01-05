<?php
/**
 * Run this script once to create the `newborns` table used by `doctor_portal.php`.
 * Usage (CLI):
 *   php create_newborn_table.php
 * Or open in a browser: http://yourhost/drea/create_newborn_table.php
 *
 * The script re-uses `db_connect.php` for credentials and will exit if the table
 * already exists. It prints simple success/error messages.
 */

require_once __DIR__ . '/db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS `newborns` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `baby_id` VARCHAR(64) DEFAULT NULL,
  `patient_user_id` INT DEFAULT NULL,
  `mother_name` VARCHAR(255) DEFAULT NULL,
  `child_name` VARCHAR(255) DEFAULT NULL,
  `gender` VARCHAR(16) DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `time_of_birth` TIME DEFAULT NULL,
  `blood_type` VARCHAR(16) DEFAULT NULL,
  `weight` DECIMAL(6,2) DEFAULT NULL,
  `notes` TEXT,
  `status` VARCHAR(64) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_baby_id` (`baby_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Table `newborns` created or already exists.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();

// If run from browser, render a small HTML response
if (php_sapi_name() !== 'cli') {
    echo "<p>Finished. Check your database to confirm the <code>newborns</code> table.</p>";
}

?>
