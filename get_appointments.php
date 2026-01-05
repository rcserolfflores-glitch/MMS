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

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // create table if missing so listing does not fail
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
        cancelled_by INT DEFAULT NULL,
        cancelled_at DATETIME DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        assigned_midwife VARCHAR(255) DEFAULT NULL,
        assigned_provider VARCHAR(255) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // If staff (midwife/doctor/admin) return enriched list with patient details, otherwise return patient's own appointments
    if (in_array($role, ['midwife','doctor','admin','staff','nurse'])) {
        // build filters from optional query params: user_id, date
        $filters = [];
        $params = [];
        if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) { $filters[] = 'a.user_id = ?'; $params[] = (int)$_GET['user_id']; }
        // if current user is a midwife, only show appointments assigned to them
        // if current user is a midwife or doctor, only show appointments assigned to them
        if (in_array($role, ['midwife','doctor'])) { $filters[] = '(a.assigned_provider = ? OR a.assigned_midwife = ?)'; $params[] = $_SESSION['username'] ?? ''; $params[] = $_SESSION['username'] ?? ''; }
        if (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) { $filters[] = 'a.appointment_date = ?'; $params[] = $_GET['date']; }

        $where = '';
        if (count($filters) > 0) { $where = 'WHERE ' . implode(' AND ', $filters); }

        $sql = "SELECT a.*, a.appointment_date AS date, a.appointment_time AS time, a.requested_date, a.requested_time,
                   p.name AS patient_name, p.mobile_number AS mobile_number, p.email AS email, p.address AS address, a.assigned_midwife, a.assigned_provider
                FROM appointments a
                LEFT JOIN patient_details p ON p.user_id = a.user_id
                $where
                ORDER BY a.appointment_date ASC, a.appointment_time ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // normalize each row to a canonical shape expected by the front-end
        $out = [];
        foreach ($rows as $r) {
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
                'assigned_midwife' => $r['assigned_midwife'] ?? null,
                'assigned_provider' => $r['assigned_provider'] ?? null,
            ];
        }

        echo json_encode(['success' => true, 'appointments' => $out]);
        exit;
    }

    // Public availability check: return minimal appointment slot info for a given date
    // This allows the booking UI to mark already-confirmed slots as unavailable without exposing PII.
    if (isset($_GET['public']) && $_GET['public'] === '1' && isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date'])) {
        // Public availability: return only staff-confirmed appointments for that date
        // so the booking UI can disable those slots for all patients.
        $date = $_GET['date'];
        $stmt = $db->prepare('SELECT appointment_time, service, status FROM appointments WHERE appointment_date = ? AND LOWER(status) LIKE ?');
        $stmt->execute([$date, '%confirm%']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'appointment_time' => $r['appointment_time'] ?? null,
                'service' => $r['service'] ?? null,
                'status' => $r['status'] ?? null,
            ];
        }
        echo json_encode(['success' => true, 'appointments' => $out]);
        exit;
    }

    // patient view: own appointments
    $stmt = $db->prepare('SELECT * FROM appointments WHERE user_id = ? AND visible = 1 ORDER BY appointment_date ASC, appointment_time ASC');
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = [];
    foreach ($rows as $r) {
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
            'date' => $r['appointment_date'] ?? null,
            'time' => $r['appointment_time'] ?? null,
            'requested_date' => $r['requested_date'] ?? null,
            'requested_time' => $r['requested_time'] ?? null,
            'status' => $status,
            'visible' => isset($r['visible']) ? (int)$r['visible'] : 1,
            'cancelled_by' => isset($r['cancelled_by']) ? (int)$r['cancelled_by'] : null,
            'cancelled_at' => $r['cancelled_at'] ?? null,
            'notes' => $r['notes'] ?? null,
            'created_at' => $r['created_at'] ?? null,
            'updated_at' => $r['updated_at'] ?? null,
        ];
    }

    echo json_encode(['success' => true, 'appointments' => $out]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
