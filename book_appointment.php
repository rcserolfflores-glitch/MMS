<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$userId = $_SESSION['user_id'];

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input.']);
    exit;
}

$service = trim($data['service'] ?? '');
$appointment_date = trim($data['appointment_date'] ?? '');
$appointment_time = trim($data['appointment_time'] ?? '');

if (!$service || !$appointment_date || !$appointment_time) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Service, date and time are required.']);
    exit;
}

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure table exists (non-destructive)
    $db->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        service VARCHAR(255) NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time VARCHAR(20) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'Pending',
        visible TINYINT(1) NOT NULL DEFAULT 1,
        cancelled_by INT DEFAULT NULL,
        cancelled_at DATETIME DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        assigned_midwife VARCHAR(255) DEFAULT NULL,
        assigned_provider VARCHAR(255) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Ensure optional columns exist (some older deployments may have an older table schema)
    try {
        // Try a safe ALTER that works on MySQL 8+: ADD COLUMN IF NOT EXISTS
        $db->exec("ALTER TABLE appointments 
            ADD COLUMN IF NOT EXISTS assigned_midwife VARCHAR(255) DEFAULT NULL,
            ADD COLUMN IF NOT EXISTS assigned_provider VARCHAR(255) DEFAULT NULL");
    } catch (Exception $e) {
        // Fallback for older MySQL versions: attempt to add individually and ignore errors
        try { $db->exec("ALTER TABLE appointments ADD COLUMN assigned_midwife VARCHAR(255) DEFAULT NULL"); } catch (Exception $__e) { /* ignore */ }
        try { $db->exec("ALTER TABLE appointments ADD COLUMN assigned_provider VARCHAR(255) DEFAULT NULL"); } catch (Exception $__e) { /* ignore */ }
    }
    // simple duplicate check: same user + same date + same time
    $check = $db->prepare('SELECT COUNT(*) FROM appointments WHERE user_id = ? AND appointment_date = ? AND appointment_time = ? AND visible = 1');
    $check->execute([$userId, $appointment_date, $appointment_time]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'You already have an appointment at that date/time.']);
        exit;
    }
    // Load appointment settings (if present) to enforce rules
    $dailyLimit = 20;
    $allowSameDay = true;
    $autoApprove = false;
    try{
        $sstmt = $db->prepare('SELECT v FROM settings WHERE k = :k LIMIT 1');
        $sstmt->execute([':k' => 'appointment_settings']);
        $sv = $sstmt->fetchColumn();
        if ($sv) {
            $cfg = json_decode($sv, true);
            if (is_array($cfg)){
                $dailyLimit = isset($cfg['limit_per_day']) ? (int)$cfg['limit_per_day'] : $dailyLimit;
                $allowSameDay = isset($cfg['same_day']) ? (bool)$cfg['same_day'] : $allowSameDay;
                $autoApprove = isset($cfg['auto_approve']) ? (bool)$cfg['auto_approve'] : $autoApprove;
            }
        }
    } catch (Exception $e) { /* ignore and use defaults */ }

    // prevent same-day booking if disabled
    if (!$allowSameDay && $appointment_date === date('Y-m-d')){
        echo json_encode(['success' => false, 'message' => 'Same-day bookings are not allowed. Please choose another date.']);
        exit;
    }

    // enforce maximum appointments per day
    $countStmt = $db->prepare('SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND visible = 1 AND status != ?');
    $countStmt->execute([$appointment_date, 'Cancelled']);
    $currentCount = (int) $countStmt->fetchColumn();
    if ($currentCount >= $dailyLimit) {
        echo json_encode(['success' => false, 'message' => 'The selected date is fully booked. Please choose another date.']);
        exit;
    }

    // accept optional assigned_midwife/assigned_provider from client to assign appointment to a staff account
    $assignedMidwife = isset($data['assigned_midwife']) && $data['assigned_midwife'] !== '' ? trim($data['assigned_midwife']) : null;
    $assignedProvider = isset($data['assigned_provider']) && $data['assigned_provider'] !== '' ? trim($data['assigned_provider']) : null;
    $stmt = $db->prepare('INSERT INTO appointments (user_id, service, appointment_date, appointment_time, status, assigned_midwife, assigned_provider) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $status = $autoApprove ? 'Confirmed' : 'Pending';
    $stmt->execute([$userId, $service, $appointment_date, $appointment_time, $status, $assignedMidwife, $assignedProvider]);
    $id = $db->lastInsertId();

    $select = $db->prepare('SELECT id, service, appointment_date, appointment_time, status, assigned_midwife, assigned_provider, created_at FROM appointments WHERE id = ?');
    $select->execute([$id]);
    $row = $select->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'message' => 'Appointment requested', 'appointment' => $row]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>
