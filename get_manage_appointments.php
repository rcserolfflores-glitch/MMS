<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// normalize and trim role value
$role = strtolower(trim((string)($_SESSION['user_type'] ?? ($_SESSION['role'] ?? ''))));

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure appointments table exists (non-destructive)
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

    // Ensure optional assigned staff columns exist (safe attempt)
    try {
        $db->exec("ALTER TABLE appointments ADD COLUMN assigned_midwife VARCHAR(255) DEFAULT NULL");
    } catch (Exception $e) { /* ignore if column exists or other non-critical error */ }
    try {
        $db->exec("ALTER TABLE appointments ADD COLUMN assigned_provider VARCHAR(255) DEFAULT NULL");
    } catch (Exception $e) { /* ignore if column exists or other non-critical error */ }

    // Only staff should call this endpoint (include clerk)
    // allow flexible role names (e.g. 'clerk_xyz') by pattern matching
    if (!preg_match('/\b(midwife|doctor|admin|staff|nurse|clerk)\b/i', $role)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Permission denied']);
        exit;
    }

    $filters = [];
    $params = [];
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) { $filters[] = 'a.user_id = ?'; $params[] = (int)$_GET['user_id']; }
    if (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) { $filters[] = 'a.appointment_date = ?'; $params[] = $_GET['date']; }

    // If the current user is a clinician (midwife/doctor), restrict results to appointments
    // explicitly assigned to them for privacy. Admin/staff/clerk can see all.
    $sessionUserName = trim((string)($_SESSION['username'] ?? ''));
    if (in_array($role, ['midwife','doctor'])) {
        // only include appointments where assigned_midwife or assigned_provider matches current username
        $filters[] = '(a.assigned_midwife = ? OR a.assigned_provider = ?)';
        $params[] = $sessionUserName;
        $params[] = $sessionUserName;
    }

    $where = '';
    if (count($filters) > 0) { $where = 'WHERE ' . implode(' AND ', $filters); }

    $sql = "SELECT a.*, a.appointment_date AS date, a.appointment_time AS time,
                   p.name AS patient_name, p.mobile_number AS mobile_number, p.email AS email, p.address AS address
            FROM appointments a
            LEFT JOIN patient_details p ON p.user_id = a.user_id
            $where
            ORDER BY a.appointment_date ASC, a.appointment_time ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach ($rows as $r) {
        // normalize status: if the appointment has a cancelled_by set, prefer an explicit "Cancelled by patient" label
        $status = isset($r['status']) ? $r['status'] : null;
        if (isset($r['cancelled_by']) && !is_null($r['cancelled_by'])) {
            if (stripos((string)$status, 'patient') === false) {
                $status = 'Cancelled by patient';
            }
        }

        $out[] = [
            'id' => (int)$r['id'],
            'user_id' => (int)$r['user_id'],
            'service' => $r['service'] ?? null,
            'date' => $r['date'] ?? null,
            'time' => $r['time'] ?? null,
            'requested_date' => $r['requested_date'] ?? null,
            'requested_time' => $r['requested_time'] ?? null,
            'status' => $status,
            'visible' => isset($r['visible']) ? (int)$r['visible'] : 1,
            'cancelled_by' => isset($r['cancelled_by']) ? (int)$r['cancelled_by'] : null,
            'cancelled_at' => $r['cancelled_at'] ?? null,
            'notes' => $r['notes'] ?? null,
            'created_at' => $r['created_at'] ?? null,
            'updated_at' => $r['updated_at'] ?? null,
            'patient_name' => $r['patient_name'] ?? null,
            'mobile_number' => $r['mobile_number'] ?? ($r['cellphone'] ?? null),
            'email' => $r['email'] ?? null,
            'address' => $r['address'] ?? null,
            // assigned staff fields
            'assigned_midwife' => $r['assigned_midwife'] ?? null,
            'assigned_provider' => $r['assigned_provider'] ?? null,
        ];
    }

    echo json_encode(['success' => true, 'appointments' => $out]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>
