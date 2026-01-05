<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];
$role = strtolower($_SESSION['user_type'] ?? ($_SESSION['role'] ?? ''));

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'id is required']);
    exit;
}

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure appointments table exists (harmless create)
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

    // fetch appointment
    $stmt = $db->prepare('SELECT * FROM appointments WHERE id = ?');
    $stmt->execute([$id]);
    $appt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$appt) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Appointment not found']); exit; }

    // authorization: allow if owner or staff
    if ($appt['user_id'] != $userId && !in_array($role, ['midwife','doctor','admin','staff','nurse'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Not authorized to view this appointment']);
        exit;
    }

    // fetch patient details if available
    $pstmt = $db->prepare('SELECT * FROM patient_details WHERE user_id = ?');
    $pstmt->execute([$appt['user_id']]);
    $patient = $pstmt->fetch(PDO::FETCH_ASSOC);

    // create a canonical appointment object that the front-end can always rely on
    $canonical = [
        'id' => (int)$appt['id'],
        'user_id' => (int)$appt['user_id'],
        'service' => $appt['service'] ?? null,
        'date' => $appt['appointment_date'] ?? null,
        'time' => $appt['appointment_time'] ?? null,
        'requested_date' => $appt['requested_date'] ?? null,
        'requested_time' => $appt['requested_time'] ?? null,
        'status' => $appt['status'] ?? null,
        'visible' => isset($appt['visible']) ? (int)$appt['visible'] : 1,
        'notes' => $appt['notes'] ?? null,
        'created_at' => $appt['created_at'] ?? null,
        'updated_at' => $appt['updated_at'] ?? null,
        // patient fields (defaults null)
        'patient_name' => null,
        'age' => null,
        'address' => null,
        'birthday' => null,
        'mobile_number' => null,
        'email' => null,
        'obstetric_history' => null,
    ];

    if ($patient) {
        $canonical['patient_name'] = $patient['name'] ?? $patient['patient_name'] ?? null;
        $canonical['age'] = $patient['age'] ?? null;
        $canonical['address'] = $patient['address'] ?? null;
        $canonical['birthday'] = $patient['birthday'] ?? null;
        $canonical['mobile_number'] = $patient['mobile_number'] ?? ($patient['cellphone'] ?? null);
        $canonical['email'] = $patient['email'] ?? null;
        $canonical['obstetric_history'] = $patient['obstetric_history'] ?? ($patient['obstetric'] ?? null);
    }

    echo json_encode(['success' => true, 'appointment' => $canonical]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>
