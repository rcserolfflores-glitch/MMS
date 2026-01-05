<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || !in_array(($_SESSION['user_type'] ?? ''), ['admin','clerk'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;
$id = isset($data['id']) ? (int)$data['id'] : 0;
$verified = isset($data['verified']) ? (int)$data['verified'] : 1;
$amount = isset($data['amount']) && $data['amount'] !== '' ? (float)$data['amount'] : null;
$reference = isset($data['reference']) ? trim($data['reference']) : null;
$date_received = isset($data['date_received']) ? trim($data['date_received']) : null;

if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id required']); exit; }

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure payments table and verification columns exist
    $db->exec("CREATE TABLE IF NOT EXISTS payments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        patient_user_id INT DEFAULT NULL,
        patient_name VARCHAR(255) DEFAULT NULL,
        filename VARCHAR(255) DEFAULT NULL,
        url VARCHAR(512) DEFAULT NULL,
        uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_by INT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // add verification columns if missing
    $cols = [
        'verified' => "TINYINT(1) DEFAULT 0",
        'verified_by' => "INT DEFAULT NULL",
        'verified_at' => "DATETIME DEFAULT NULL",
        'amount' => "DECIMAL(10,2) DEFAULT NULL",
        'reference_no' => "VARCHAR(255) DEFAULT NULL",
        'date_received' => "DATE DEFAULT NULL"
    ];
    foreach ($cols as $col => $def) {
        try {
            $q = $db->query("SHOW COLUMNS FROM payments LIKE '" . str_replace("'","\\'", $col) . "'");
            $has = $q && $q->fetch(PDO::FETCH_ASSOC);
            if (!$has) $db->exec("ALTER TABLE payments ADD COLUMN {$col} {$def}");
        } catch (Exception $e) { /* ignore */ }
    }

    $fields = ['verified' => $verified, 'verified_by' => $_SESSION['user_id'], 'verified_at' => date('Y-m-d H:i:s')];
    if ($amount !== null) $fields['amount'] = $amount;
    if ($reference) $fields['reference_no'] = $reference;
    if ($date_received) $fields['date_received'] = $date_received;

    $sets = [];
    $params = [];
    foreach ($fields as $k => $v) {
        $sets[] = "`$k` = :$k";
        $params[":$k"] = $v;
    }
    $params[':id'] = $id;
    $sql = 'UPDATE payments SET ' . implode(', ', $sets) . ' WHERE id = :id';
    $upd = $db->prepare($sql);
    foreach ($params as $k => $v) $upd->bindValue($k, $v);
    $upd->execute();

    echo json_encode(['success' => true, 'message' => 'Payment updated']);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}

?>
