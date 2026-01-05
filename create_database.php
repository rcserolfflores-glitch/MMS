<?php
/**
 * create_database.php
 *
 * Simple helper to create the `drea_db` database on a local MySQL server
 * (useful for XAMPP). Run from command line: `php create_database.php`
 */

$servername = 'localhost';
$username = 'root';
$password = '';
$database = 'drea_db';

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    fwrite(STDERR, "Connection failed: " . $conn->connect_error . PHP_EOL);
    exit(1);
}

$sql = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Database '$database' created or already exists\n";
} else {
    fwrite(STDERR, "Error creating database: " . $conn->error . PHP_EOL);
    exit(1);
}

$conn->close();
echo "Done. You can now run migration scripts, for example:\n  php create_newborn_table.php\n  php create_newborn_screening_table.php\n  php create_doctor_table.php\" . PHP_EOL;

?>
