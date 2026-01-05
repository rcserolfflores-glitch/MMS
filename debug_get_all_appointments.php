<?php
// Local debug: return all appointments joined with patient details.
// Accessible only from localhost (127.0.0.1 or ::1).
header('Content-Type: application/json; charset=utf-8');
$remote = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!in_array($remote, ['127.0.0.1','::1','::ffff:127.0.0.1'])) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden: debug endpoint only available on localhost','remote'=>$remote]);
    exit;
}

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        service VARCHAR(255) NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time VARCHAR(20) NOT NULL,
        requested_date DATE DEFAULT NULL,
        requested_time VARCHAR(20) DEFAULT NULL,
        status VARCHAR(100) NOT NULL DEFAULT 'Pending',
        visible TINYINT(1) NOT NULL DEFAULT 1,
        notes TEXT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $sql = "SELECT a.*, p.name AS patient_name, p.mobile_number, p.email, p.address
            FROM appointments a
            LEFT JOIN patient_details p ON p.user_id = a.user_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $stmt = $db->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success'=>true,'count'=>count($rows),'appointments'=>$rows]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
}

?>
