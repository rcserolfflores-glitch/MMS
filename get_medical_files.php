<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])){ http_response_code(401); echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit; }

try{
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure patient_files table exists
    $db->exec("CREATE TABLE IF NOT EXISTS patient_files (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        patient_user_id INT DEFAULT NULL,
        filename VARCHAR(255) DEFAULT NULL,
        url VARCHAR(512) DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_by INT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $type = isset($_GET['type']) ? trim(strtolower($_GET['type'])) : '';
    $patientUserId = isset($_GET['patient_user_id']) && is_numeric($_GET['patient_user_id']) ? (int)$_GET['patient_user_id'] : null;

    // map friendly type keys to keyword lists (used to match filename or notes)
    $typeKeywords = [
        'midwife_checkup' => ['midwife','midwife_check','midwife result','midwife_result','midwife-checkup','midwife checkup'],
        'obgyn_checkup' => ['obgyn','ob-gyn','ob gyn','obgyn_check','obgyn result','ob-gyn result'],
        'pedia_checkup' => ['pedia','pediatric','pedi','pedia_checkup'],
        'transvaginal_ultrasound' => ['transvaginal','tvus','transvaginal ultrasound'],
        'pelvic_ultrasound' => ['pelvic ultrasound','pelvic_ultrasound','pelvic'],
        'newborn_screening' => ['newborn','new-born','new born','screening','newborn screening'],
        // laboratory is a special-case (handled by lab_results endpoint)
    ];

    if ($type === 'laboratory' || $type === 'laboratory_results') {
        // forward consumer to lab_results endpoint (client can use get_lab_results.php directly)
        // but for convenience, return lab results here as well
        $sql = 'SELECT id, patient_user_id, patient_name, appointment_id, result_type, filename, url, notes, uploaded_at, created_by FROM lab_results';
        if ($patientUserId) {
            $stmt = $db->prepare($sql . ' WHERE patient_user_id = ? ORDER BY uploaded_at DESC');
            $stmt->execute([$patientUserId]);
        } else {
            $stmt = $db->query($sql . ' ORDER BY uploaded_at DESC');
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true,'files'=>$rows]);
        exit;
    }

    // base query for patient_files
    $sql = 'SELECT id, patient_user_id, filename, url, notes, uploaded_at, created_by FROM patient_files';
    $conds = [];
    $params = [];
    if ($patientUserId) { $conds[] = 'patient_user_id = ?'; $params[] = $patientUserId; }

    if ($type && isset($typeKeywords[$type])){
        // If the requested type maps to medical result categories, prefer returning lab_results that were saved with that result_type
        $stmt = null;
        $sqlLab = 'SELECT id, patient_user_id, patient_name, appointment_id, result_type, filename, url, notes, uploaded_at, created_by FROM lab_results';
        $labParams = [];
        if ($patientUserId) { $sqlLab .= ' WHERE patient_user_id = ? AND LOWER(result_type) = ? ORDER BY uploaded_at DESC'; $labParams = [$patientUserId, strtolower($type)]; }
        else { $sqlLab .= ' WHERE LOWER(result_type) = ? ORDER BY uploaded_at DESC'; $labParams = [strtolower($type)]; }
        $stmt = $db->prepare($sqlLab);
        try{ $stmt->execute($labParams); $labRows = $stmt->fetchAll(PDO::FETCH_ASSOC); } catch(Exception $e){ $labRows = []; }
        if(!empty($labRows)){
            echo json_encode(['success'=>true,'files'=>$labRows]);
            exit;
        }

        // fallback to patient_files keyword matching when no lab_results were explicitly tagged with this type
        $kw = $typeKeywords[$type];
        $likes = [];
        foreach ($kw as $k) { $likes[] = "(LOWER(filename) LIKE ? OR LOWER(notes) LIKE ? )"; $params[] = '%'.strtolower($k).'%' ; $params[] = '%'.strtolower($k).'%' ; }
        if (count($likes)) $conds[] = '(' . implode(' OR ', $likes) . ')';
    }

    if (count($conds)) $sql .= ' WHERE ' . implode(' AND ', $conds);
    $sql .= ' ORDER BY uploaded_at DESC';

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success'=>true,'files'=>$rows]);
    exit;

} catch(PDOException $e){ http_response_code(500); echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]); exit; }

?>