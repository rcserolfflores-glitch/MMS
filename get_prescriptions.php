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

    // ensure table exists
    $db->exec("CREATE TABLE IF NOT EXISTS prescriptions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        patient_user_id INT DEFAULT NULL,
        patient_name VARCHAR(255) DEFAULT NULL,
        instruction TEXT,
        drugs TEXT,
        date DATE DEFAULT NULL,
        prescription_file_url VARCHAR(512) DEFAULT NULL,
        created_by INT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Ensure column exists for older DBs
    try{
        $colStmt = $db->query("SHOW COLUMNS FROM prescriptions LIKE 'prescription_file_url'");
        $colExists = $colStmt && $colStmt->fetch(PDO::FETCH_ASSOC);
        if(!$colExists){
            $db->exec("ALTER TABLE prescriptions ADD COLUMN prescription_file_url VARCHAR(512) DEFAULT NULL");
        }
    } catch(Exception $e){
        error_log('Failed to ensure prescription_file_url in get_prescriptions: ' . $e->getMessage());
    }

    $params = [];
    $sql = 'SELECT * FROM prescriptions';

    $role = strtolower($_SESSION['user_type'] ?? '');
    if($role === 'patient'){
        $sql .= ' WHERE patient_user_id = :uid';
        $params[':uid'] = (int)$_SESSION['user_id'];
    } elseif(isset($_GET['patient_user_id']) && $_GET['patient_user_id'] !== ''){
        $sql .= ' WHERE patient_user_id = :uid';
        $params[':uid'] = (int)$_GET['patient_user_id'];
    }

    $sql .= ' ORDER BY date DESC, created_at DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach($rows as $r){
        $out[] = [
            'id' => isset($r['id']) ? (int)$r['id'] : null,
            'patient_user_id' => isset($r['patient_user_id']) ? (int)$r['patient_user_id'] : null,
            'patient_name' => $r['patient_name'] ?? null,
            'instruction' => $r['instruction'] ?? null,
            'drugs' => $r['drugs'] ?? null,
            'date' => $r['date'] ?? null,
            'file_url' => $r['prescription_file_url'] ?? null,
            'created_by' => isset($r['created_by']) ? (int)$r['created_by'] : null,
            'created_at' => $r['created_at'] ?? null,
        ];
    }

    echo json_encode(['success' => true, 'count' => count($out), 'prescriptions' => $out]);

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
