<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['patient_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$patient_id = $_SESSION['patient_id'];
$appointment_id = $_POST['id'] ?? '';

if (empty($appointment_id)) {
    echo json_encode(['success' => false, 'message' => 'Appointment ID required']);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=your_db', 'user', 'pass'); // Update with your DB details
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure the appointment belongs to the patient and is not already completed
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND patient_id = ? AND status != 'completed'");
    $stmt->execute([$appointment_id, $patient_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Appointment cancelled']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not cancel appointment']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>