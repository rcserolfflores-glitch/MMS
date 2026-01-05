<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- 1. Check login status ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in.'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // --- 2. Connect to database ---
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

try {
    // --- 3. Fetch patient details ---
    $stmt = $db->prepare("SELECT * FROM patient_details WHERE user_id = ?");
    $stmt->execute([$userId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // Remove sensitive or unnecessary fields
        unset($data['id'], $data['user_id'], $data['created_at'], $data['updated_at']);

        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No profile found.'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching data: ' . $e->getMessage()
    ]);
}
?>
