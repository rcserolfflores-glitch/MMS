<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Allow admin, midwife, doctor and clerk roles to mark payments as paid
if (!isset($_SESSION['user_id']) || !in_array(($_SESSION['user_type'] ?? ''), ['admin','midwife','doctor','clerk'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;
$id = isset($data['id']) ? (int)$data['id'] : 0;

if (!$id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'id required']); exit; }

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure payments table and paid columns exist
    $db->exec("CREATE TABLE IF NOT EXISTS payments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        patient_user_id INT DEFAULT NULL,
        patient_name VARCHAR(255) DEFAULT NULL,
        filename VARCHAR(255) DEFAULT NULL,
        url VARCHAR(512) DEFAULT NULL,
        uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_by INT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $cols = [
        'paid' => "TINYINT(1) DEFAULT 0",
        'paid_by' => "INT DEFAULT NULL",
        'paid_at' => "DATETIME DEFAULT NULL"
    ];
    foreach ($cols as $col => $def) {
        try {
            $q = $db->query("SHOW COLUMNS FROM payments LIKE '" . str_replace("'","\\'", $col) . "'");
            $has = $q && $q->fetch(PDO::FETCH_ASSOC);
            if (!$has) $db->exec("ALTER TABLE payments ADD COLUMN {$col} {$def}");
        } catch (Exception $e) { /* ignore */ }
    }

    $sql = 'UPDATE payments SET paid = 1, paid_by = :uid, paid_at = :ts WHERE id = :id';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':uid', $_SESSION['user_id']);
    $stmt->bindValue(':ts', date('Y-m-d H:i:s'));
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Marked as paid']);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}
?>