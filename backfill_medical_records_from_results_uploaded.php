<?php
/**
 * Backfill `medical_records` entries for rows in `results_uploaded` that have no corresponding
 * medical_records row with the same file_url.
 * Run: php backfill_medical_records_from_results_uploaded.php
 */

$host = 'localhost';
$dbname = 'drea_db';
$user = 'root';
$pass = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try{
    $db = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Connected to database $dbname\n";

    // ensure medical_records exists
    $db->exec("CREATE TABLE IF NOT EXISTS medical_records (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            patient_user_id INT NULL,
            patient_name VARCHAR(255) DEFAULT NULL,
            age VARCHAR(32) DEFAULT NULL,
            cellphone VARCHAR(80) DEFAULT NULL,
            ob_score VARCHAR(80) DEFAULT NULL,
            lmp DATE DEFAULT NULL,
            edd DATE DEFAULT NULL,
            blood_pressure VARCHAR(80) DEFAULT NULL,
            gestation_age VARCHAR(32) DEFAULT NULL,
            weight VARCHAR(32) DEFAULT NULL,
            pulse VARCHAR(32) DEFAULT NULL,
            respiratory_rate VARCHAR(32) DEFAULT NULL,
            fht VARCHAR(32) DEFAULT NULL,
            result VARCHAR(32) DEFAULT NULL,
            gravida VARCHAR(32) DEFAULT NULL,
            para VARCHAR(32) DEFAULT NULL,
            notes TEXT,
            filename VARCHAR(255) DEFAULT NULL,
            file_url VARCHAR(512) DEFAULT NULL,
            note_type VARCHAR(120) DEFAULT NULL,
            created_by INT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // find results_uploaded rows without matching medical_records.file_url
    $sql = "SELECT r.* FROM results_uploaded r
            LEFT JOIN medical_records m ON (m.file_url IS NOT NULL AND m.file_url = r.url)
            WHERE m.id IS NULL";
    $stmt = $db->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($rows);
    echo "Found $count results_uploaded rows without medical_records entries.\n";
    if($count === 0){ echo "Nothing to do.\n"; exit(0); }

    $ins = $db->prepare('INSERT INTO medical_records (patient_user_id, patient_name, notes, filename, file_url, note_type, created_by, created_at) VALUES (:pid, :pname, :notes, :fname, :file_url, :note_type, :created_by, :created_at)');
    $copied = 0;
    foreach($rows as $r){
        $pid = !empty($r['patient_user_id']) ? (int)$r['patient_user_id'] : null;
        // If patient_user_id missing, try to resolve from appointments
        if((empty($pid) || $pid === null) && !empty($r['appointment_id'])){
            try{
                $q = $db->prepare('SELECT user_id FROM appointments WHERE id = :id LIMIT 1');
                $q->execute([':id' => (int)$r['appointment_id']]);
                $ar = $q->fetch(PDO::FETCH_ASSOC);
                if($ar && !empty($ar['user_id'])) $pid = (int)$ar['user_id'];
            }catch(PDOException $e){ }
        }
        $pname = !empty($r['patient_name']) ? $r['patient_name'] : null;
        $notes = !empty($r['notes']) ? $r['notes'] : null;
        $fname = !empty($r['filename']) ? $r['filename'] : null;
        $file_url = !empty($r['url']) ? $r['url'] : null;
        $note_type = !empty($r['result_type']) ? $r['result_type'] : 'laboratory';
        $created_at = !empty($r['uploaded_at']) ? $r['uploaded_at'] : date('Y-m-d H:i:s');

        $ins->bindValue(':pid', $pid ? $pid : null, PDO::PARAM_INT);
        $ins->bindValue(':pname', $pname ?: null);
        $ins->bindValue(':notes', $notes ?: null);
        $ins->bindValue(':fname', $fname ?: null);
        $ins->bindValue(':file_url', $file_url ?: null);
        $ins->bindValue(':note_type', $note_type ?: 'laboratory');
        $ins->bindValue(':created_by', null, PDO::PARAM_NULL);
        $ins->bindValue(':created_at', $created_at);
        try{
            $ins->execute();
            $copied++;
        }catch(PDOException $e){ echo "Failed inserting for url {$file_url}: " . $e->getMessage() . "\n"; }
    }

    echo "Inserted $copied medical_records rows.\n";

} catch (PDOException $e){
    echo "DB error: " . $e->getMessage() . "\n";
    exit(1);
}

?>