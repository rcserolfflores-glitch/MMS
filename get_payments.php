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

    $db->exec("CREATE TABLE IF NOT EXISTS payments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        patient_user_id INT DEFAULT NULL,
        patient_name VARCHAR(255) DEFAULT NULL,
        receipt_no VARCHAR(128) DEFAULT NULL,
        service VARCHAR(255) DEFAULT NULL,
        amount DECIMAL(10,2) DEFAULT 0.00,
        amount_paid DECIMAL(10,2) DEFAULT 0.00,
        gcash_ref_no VARCHAR(128) DEFAULT NULL,
        reference_no VARCHAR(128) DEFAULT NULL,
        filename VARCHAR(255) DEFAULT NULL,
        url VARCHAR(512) DEFAULT NULL,
        uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        appointment_id INT DEFAULT NULL,
        paid TINYINT(1) DEFAULT 0,
        verified TINYINT(1) DEFAULT 0,
        verified_by INT DEFAULT NULL,
        verified_at DATETIME DEFAULT NULL,
        payment_status VARCHAR(64) DEFAULT NULL,
        remarks TEXT DEFAULT NULL,
        date_received DATETIME DEFAULT NULL,
        created_by INT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $role = strtolower($_SESSION['user_type'] ?? '');
    $params = [];
    // include uploader name if available (joined from patient_details.created_by)
    $sql = 'SELECT payments.*, pd.name AS uploaded_by FROM payments LEFT JOIN patient_details pd ON pd.user_id = payments.created_by';
    if($role === 'patient'){
        $sql .= ' WHERE patient_user_id = :uid';
        $params[':uid'] = (int)$_SESSION['user_id'];
    } elseif(isset($_GET['patient_user_id']) && $_GET['patient_user_id'] !== ''){
        $sql .= ' WHERE patient_user_id = :uid';
        $params[':uid'] = (int)$_GET['patient_user_id'];
    }

    $sql .= ' ORDER BY uploaded_at DESC';
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach($rows as $r){
        $out[] = [
            'id' => isset($r['id']) ? (int)$r['id'] : null,
            'patient_user_id' => isset($r['patient_user_id']) ? (int)$r['patient_user_id'] : null,
            'patient_name' => $r['patient_name'] ?? null,
            // uploader name: prefer joined patient_details.name, otherwise fall back to patient_name
            'uploaded_by' => $r['uploaded_by'] ?? ($r['patient_name'] ?? null),
            'service' => $r['service'] ?? null,
            'filename' => $r['filename'] ?? null,
            'url' => $r['url'] ?? null,
            'uploaded_at' => $r['uploaded_at'] ?? null,
            'created_by' => isset($r['created_by']) ? (int)$r['created_by'] : null,
            'appointment_id' => isset($r['appointment_id']) ? (int)$r['appointment_id'] : null,
            'verified' => isset($r['verified']) ? (int)$r['verified'] : 0,
            'verified_by' => isset($r['verified_by']) ? (int)$r['verified_by'] : null,
            'verified_at' => $r['verified_at'] ?? null,
            'amount' => isset($r['amount']) ? (string)$r['amount'] : (isset($r['amount_paid']) ? (string)$r['amount_paid'] : null),
            'reference_no' => $r['reference_no'] ?? null,
            'date_received' => $r['date_received'] ?? null,
            // payment status flags
            'payment_status' => $r['payment_status'] ?? ($r['status'] ?? null),
            'paid' => isset($r['paid']) ? (int)$r['paid'] : (isset($r['payment_status']) && strtolower($r['payment_status']) === 'paid' ? 1 : 0),
            'remarks' => $r['remarks'] ?? $r['notes'] ?? null,
        ];
    }

    echo json_encode(['success' => true, 'count' => count($out), 'payments' => $out]);
    exit;

} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

?>
