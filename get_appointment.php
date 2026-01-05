<?php
// backend/get_appointments.php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$patient_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT id, service, appointment_date, appointment_time, status FROM appointments WHERE patient_id = ? ORDER BY appointment_date DESC, appointment_time ASC");
    $stmt->execute([$patient_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'appointments' => $appointments
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
