<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure table exists (safe to call)
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
        created_by INT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}

// Determine filter: if ?user_id provided, use it. If logged in patient, restrict to their user_id.
$filterUserId = null;
if (isset($_GET['user_id']) && $_GET['user_id'] !== '') {
    $filterUserId = (int)$_GET['user_id'];
} elseif (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'patient') {
    $filterUserId = (int)($_SESSION['user_id'] ?? 0);
}

// allow callers to request that staff-created records be excluded (so patients only see non-staff records)
$excludeStaff = isset($_GET['exclude_staff']) && ($_GET['exclude_staff'] === '1' || $_GET['exclude_staff'] === 'true');

try {
    if ($filterUserId) {
        if ($excludeStaff) {
            // only return records for this patient that were not created by staff (created_by IS NULL or 0)
            $stmt = $db->prepare('SELECT * FROM medical_records WHERE patient_user_id = ? AND (created_by IS NULL OR created_by = 0) ORDER BY created_at DESC');
            $stmt->execute([$filterUserId]);
        } else {
            $stmt = $db->prepare('SELECT * FROM medical_records WHERE patient_user_id = ? ORDER BY created_at DESC');
            $stmt->execute([$filterUserId]);
        }
    } else {
        // if staff (midwife/doctor) or no filter, return all records
        $stmt = $db->query('SELECT * FROM medical_records ORDER BY created_at DESC');
    }
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // normalize keys and ensure patient_name exists
    $out = array_map(function($r){
        return [
            'id' => isset($r['id']) ? (int)$r['id'] : null,
            'patient_user_id' => isset($r['patient_user_id']) ? (int)$r['patient_user_id'] : null,
            'patient_name' => $r['patient_name'] ?? null,
            'age' => $r['age'] ?? null,
            'cellphone' => $r['cellphone'] ?? null,
            'ob_score' => $r['ob_score'] ?? null,
            'lmp' => $r['lmp'] ?? null,
            'edd' => $r['edd'] ?? null,
            'blood_pressure' => $r['blood_pressure'] ?? null,
            'gestation_age' => $r['gestation_age'] ?? null,
            'weight' => $r['weight'] ?? null,
            'pulse' => $r['pulse'] ?? null,
            'respiratory_rate' => $r['respiratory_rate'] ?? null,
            'fht' => $r['fht'] ?? null,
            'result' => $r['result'] ?? null,
            'gravida' => $r['gravida'] ?? null,
            'para' => $r['para'] ?? null,
            'notes' => $r['notes'] ?? null,
            // include file attachment fields so clients can show view/download actions
            'filename' => $r['filename'] ?? ($r['file_name'] ?? null),
            'file_url' => $r['file_url'] ?? ($r['url'] ?? ($r['document_url'] ?? null)),
            'note_type' => $r['note_type'] ?? ($r['type'] ?? null),
            'provider' => $r['provider'] ?? ($r['provider_name'] ?? null),
            'created_by' => isset($r['created_by']) ? (int)$r['created_by'] : null,
            'created_at' => $r['created_at'] ?? null
        ];
    }, $rows);

    echo json_encode(['success' => true, 'records' => $out]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to read records: ' . $e->getMessage()]);
}

?>
