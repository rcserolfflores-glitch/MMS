<?php
/**
 * Scan `assets/uploads/results_uploaded` and insert any files into `results_uploaded` table.
 * Run: php import_results_uploaded.php
 */

$host = 'localhost';
$dbname = 'drea_db';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try{
    $db = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected to database $dbname\n";

    // ensure table exists
    $db->exec("CREATE TABLE IF NOT EXISTS results_uploaded (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $uploadDir = __DIR__ . '/assets/uploads/results_uploaded';
    if(!is_dir($uploadDir)){
        throw new Exception("Upload directory not found: $uploadDir");
    }

    $files = array_values(array_filter(scandir($uploadDir), function($f){ return $f !== '.' && $f !== '..' && !is_dir($f); }));
    if(empty($files)){
        echo "No files found in $uploadDir\n";
        exit(0);
    }

    $inserted = 0; $skipped = 0;
    $checkStmt = $db->prepare('SELECT id FROM results_uploaded WHERE filename = :fn LIMIT 1');
    $insStmt = $db->prepare('INSERT INTO results_uploaded (patient_user_id, patient_name, appointment_id, result_type, filename, url, notes, created_by) VALUES (:pid, :pname, :aid, :rtype, :fname, :url, :notes, :created_by)');

    foreach($files as $f){
        $checkStmt->execute([':fn' => $f]);
        $found = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if($found){ $skipped++; continue; }
        $relPath = 'assets/uploads/results_uploaded/' . $f;
        // Insert with minimal metadata — staff can later edit patient/appointment mapping
        $insStmt->execute([
            ':pid' => null,
            ':pname' => null,
            ':aid' => null,
            ':rtype' => 'laboratory',
            ':fname' => $f,
            ':url' => $relPath,
            ':notes' => 'Imported from uploads directory',
            ':created_by' => null
        ]);
        $inserted++;
    }

    echo "Import complete. Inserted: $inserted, Skipped (already present): $skipped\n";
    exit(0);

} catch(PDOException $e){
    echo "DB error: " . $e->getMessage() . "\n";
    exit(1);
} catch(Exception $ex){
    echo "Error: " . $ex->getMessage() . "\n";
    exit(1);
}

?>
