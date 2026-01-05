<?php
// Run this once to create the photoshoot_uploads table.
require_once __DIR__ . '/db_connect.php';

try {
  $sql = "CREATE TABLE IF NOT EXISTS photoshoot_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    upload_group VARCHAR(64) NOT NULL,
    patient_user_id VARCHAR(64) DEFAULT NULL,
    uploaded_by INT DEFAULT NULL,
    original_filename VARCHAR(255) DEFAULT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    path VARCHAR(511) NOT NULL,
    notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

  if ($conn->query($sql) === TRUE) {
    echo "Table photoshoot_uploads created or already exists.\n";
  } else {
    echo "Error creating table: " . $conn->error . "\n";
  }
} catch (Exception $e) {
  echo 'Exception: ' . $e->getMessage();
}

?>
