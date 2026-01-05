<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Use same session key as other endpoints
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

// Accept JSON or form POST
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = $_POST;
}

$appointment_id = isset($input['id']) ? (int)$input['id'] : 0;
$new_date = isset($input['date']) ? trim($input['date']) : '';
$new_time = isset($input['time']) ? trim($input['time']) : '';
$note = isset($input['note']) ? trim($input['note']) : '';

if (!$appointment_id || !$new_date || !$new_time) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Appointment id, date and time are required']);
    exit;
}

// Validate date/time formats
$dValid = DateTime::createFromFormat('Y-m-d', $new_date) !== false;
$tValid = DateTime::createFromFormat('H:i', $new_time) !== false;
if (!$dValid || !$tValid) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid date or time format (expected Y-m-d and HH:MM)']);
    exit;
}

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure appointments table exists (will be created by other endpoints normally)
    $db->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        service VARCHAR(255) NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time VARCHAR(20) NOT NULL,
        requested_date DATE DEFAULT NULL,
        requested_time VARCHAR(20) DEFAULT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Pending',
        visible TINYINT(1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // check ownership
    $check = $db->prepare('SELECT id, user_id, status FROM appointments WHERE id = ? AND visible = 1');
    $check->execute([$appointment_id]);
    $appt = $check->fetch(PDO::FETCH_ASSOC);
    if (!$appt) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
        exit;
    }
    if ($appt['user_id'] != $userId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Not authorized to reschedule this appointment']);
        exit;
    }

    // Only allow reschedule for pending or confirmed
    $allowedStatuses = ['Pending','pending','Confirmed','confirmed'];
    if (!in_array($appt['status'], $allowedStatuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot reschedule appointment in current status']);
        exit;
    }

    // Check slot availability similar to booking (MAX_PER_SLOT)
    $MAX_PER_SLOT = 3;
    $slotCheck = $db->prepare('SELECT COUNT(*) FROM appointments WHERE service = (SELECT service FROM appointments WHERE id = ?) AND appointment_date = ? AND appointment_time = ? AND visible = 1');
    $slotCheck->execute([$appointment_id, $new_date, $new_time]);
    $existingCount = (int)$slotCheck->fetchColumn();
    if ($existingCount >= $MAX_PER_SLOT) {
        echo json_encode(['success' => false, 'message' => 'Selected time slot is full. Please choose another slot.']);
        exit;
    }

    // set requested_date/requested_time and update status to Reschedule requested
    $status = 'Reschedule requested';
    $upd = $db->prepare('UPDATE appointments SET requested_date = ?, requested_time = ?, status = ?, updated_at = NOW() WHERE id = ?');
    $upd->execute([$new_date, $new_time, $status, $appointment_id]);

    // add audit
    try {
        $aud = $db->prepare('INSERT INTO appointment_audit (appointment_id, changed_by, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)');
        $aud->execute([$appointment_id, $userId, $appt['status'], $status, $note ?: 'Patient requested reschedule']);
    } catch (Exception $e) { /* ignore audit errors */ }

    // notify clinic (best-effort)
    try {
        $clinicEmail = 'clinic@example.com';
        if (!empty($clinicEmail)) {
            $subject = "Reschedule request for appointment #{$appointment_id}";
            $body = "Patient ID: {$userId}\nAppointment ID: {$appointment_id}\nRequested: {$new_date} {$new_time}\n\nPlease review in staff portal.";
            @mail($clinicEmail, $subject, $body, "From: no-reply@localhost\r\n");
        }
    } catch (Exception $e) {}

    echo json_encode(['success' => true, 'message' => 'Reschedule request submitted']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>