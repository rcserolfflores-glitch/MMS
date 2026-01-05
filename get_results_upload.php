<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])){
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

try{
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure table exists (idempotent) - use `results_uploaded` as canonical table name
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

    // ensure result_type column exists for older schemas
    $col = $db->query("SHOW COLUMNS FROM results_uploaded LIKE 'result_type'")->fetch(PDO::FETCH_ASSOC);
    if(!$col){
        $db->exec("ALTER TABLE results_uploaded ADD COLUMN result_type VARCHAR(120) DEFAULT NULL");
    }

    $isPatient = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'patient';

    // Return results along with linked appointment date/time when available
    $baseSelect = "SELECT r.*, a.appointment_date AS appointment_date, a.appointment_time AS appointment_time, a.service AS appointment_service
                   FROM results_uploaded r
                   LEFT JOIN appointments a ON a.id = r.appointment_id";

    if($isPatient){
        $stmt = $db->prepare($baseSelect . ' WHERE r.patient_user_id = :pid ORDER BY r.uploaded_at DESC');
        $stmt->execute([':pid' => (int)$_SESSION['user_id']]);
    } else {
        // staff view, optionally filter by patient_user_id if provided
        if(isset($_GET['patient_user_id']) && $_GET['patient_user_id'] !== ''){
            $stmt = $db->prepare($baseSelect . ' WHERE r.patient_user_id = :pid ORDER BY r.uploaded_at DESC');
            $stmt->execute([':pid' => (int)$_GET['patient_user_id']]);
        } else {
            $stmt = $db->query($baseSelect . ' ORDER BY r.uploaded_at DESC');
        }
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Ensure returned rows include a usable file URL (absolute or root-relative) so clients
    // can open previews reliably. The `url` column may be stored as a relative path like
    // "assets/uploads/..."; normalize to a root-relative path and also provide a
    // fully-qualified `file_url` including host when possible.
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // e.g. '/drea'
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    foreach($rows as &$rw){
        $rw['file_url'] = '';
        // prefer explicit `url` column, otherwise fall back to `filename` placed under uploads
        $candidate = '';
        if(!empty($rw['url'])){
            $candidate = $rw['url'];
        } else if(!empty($rw['filename'])){
            $candidate = 'assets/uploads/results_uploaded/' . ltrim($rw['filename'], '/');
            $rw['url'] = $candidate; // keep url populated for backward compatibility
        }

        if($candidate){
            // if candidate looks like a bare filename (no slash), assume uploads folder
            if(strpos($candidate, '/') === false){
                $candidate = 'assets/uploads/results_uploaded/' . $candidate;
            }
            // make root-relative
            $rootRel = '/' . ltrim($candidate, '/');
            // if url didn't already include the script dir (subfolder), prefix it
            if($scriptDir && strpos($rootRel, $scriptDir . '/') !== 0){
                $rootRel = $scriptDir . '/' . ltrim($candidate, '/');
            }
            $rw['url'] = $rootRel;
            // fully-qualified URL
            $rw['file_url'] = $scheme . '://' . $host . $rootRel;
        } else {
            $rw['file_url'] = '';
        }
    }
    // Return both keys for backward compatibility; prefer `results_uploaded` as canonical
    echo json_encode(['success'=>true,'results_uploaded'=>$rows,'lab_results'=>$rows]);
    exit;

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
    exit;
}

?>