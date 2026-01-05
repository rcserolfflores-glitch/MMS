<?php
/**
 * Create `results_uploaded` table and uploads folder.
 * Run: php create_results_uploaded_table.php
 */

$host = 'localhost';
$dbname = 'drea_db';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try{
    $db = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected to database $dbname\n";

    $sql = "CREATE TABLE IF NOT EXISTS results_uploaded (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        patient_user_id INT DEFAULT NULL,
        patient_name VARCHAR(255) DEFAULT NULL,
        appointment_id INT DEFAULT NULL,
        result_type VARCHAR(120) DEFAULT NULL,
        filename VARCHAR(255) DEFAULT NULL,
        url VARCHAR(512) DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_by INT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db->exec($sql);
    echo "Table `results_uploaded` ensured.\n";

    // ensure uploads directory
    $uploadDir = __DIR__ . '/assets/uploads/results_uploaded';
    if(!is_dir($uploadDir)){
        if(!is_dir(dirname($uploadDir))) mkdir(dirname($uploadDir), 0755, true);
        mkdir($uploadDir, 0755, true);
        echo "Created uploads directory: $uploadDir\n";
    } else {
        echo "Uploads directory already exists: $uploadDir\n";
    }

    echo "Done.\n";

} catch (PDOException $e){
    echo "DB error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $ex){
    echo "Error: " . $ex->getMessage() . "\n";
    exit(1);
}

?>