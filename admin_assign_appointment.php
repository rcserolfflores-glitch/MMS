<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;
$appointment_id = isset($data['appointment_id']) ? (int)$data['appointment_id'] : 0;
$assigned_to = isset($data['assigned_to']) ? (int)$data['assigned_to'] : null; // user id of staff
$assigned_role = isset($data['assigned_role']) ? trim($data['assigned_role']) : null; // optional

if (!$appointment_id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'appointment_id required']); exit; }

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure assigned_to column exists
    try{
        $q = $db->query("SHOW COLUMNS FROM appointments LIKE 'assigned_to'");
        $has = $q && $q->fetch(PDO::FETCH_ASSOC);
        if(!$has) $db->exec("ALTER TABLE appointments ADD COLUMN assigned_to INT DEFAULT NULL");
    }catch(Exception $e){}
    try{
        $q2 = $db->query("SHOW COLUMNS FROM appointments LIKE 'assigned_role'");
        $has2 = $q2 && $q2->fetch(PDO::FETCH_ASSOC);
        if(!$has2) $db->exec("ALTER TABLE appointments ADD COLUMN assigned_role VARCHAR(100) DEFAULT NULL");
    }catch(Exception $e){}

    $upd = $db->prepare('UPDATE appointments SET assigned_to = :assigned_to, assigned_role = :assigned_role, updated_at = NOW() WHERE id = :id');
    $upd->bindValue(':assigned_to', $assigned_to ? $assigned_to : null, PDO::PARAM_INT);
    $upd->bindValue(':assigned_role', $assigned_role ?: null);
    $upd->bindValue(':id', $appointment_id, PDO::PARAM_INT);
    $upd->execute();

    // audit table creation and insert
    $db->exec("CREATE TABLE IF NOT EXISTS appointment_audit (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        appointment_id INT NOT NULL,
        changed_by INT NOT NULL,
        old_status VARCHAR(255),
        new_status VARCHAR(255),
        note VARCHAR(255),
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $note = 'Assigned to user ' . ($assigned_to ? $assigned_to : 'none') . ($assigned_role ? ' as ' . $assigned_role : '');
    $aud = $db->prepare('INSERT INTO appointment_audit (appointment_id, changed_by, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)');
    $aud->execute([$appointment_id, $_SESSION['user_id'], null, null, $note]);

    echo json_encode(['success' => true, 'message' => 'Appointment updated']);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}

?>
