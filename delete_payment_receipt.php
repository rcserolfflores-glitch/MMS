<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid receipt ID']);
    exit;
}

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the receipt details first to check ownership and get filename
    $stmt = $db->prepare('SELECT * FROM payments WHERE id = :id AND (patient_user_id = :uid OR created_by = :uid)');
    $stmt->bindValue(':id', (int)$_POST['id'], PDO::PARAM_INT);
    $stmt->bindValue(':uid', (int)$_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$receipt) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Receipt not found or access denied']);
        exit;
    }

    // Delete the physical file if it exists
    if ($receipt['filename']) {
        $filepath = __DIR__ . '/assets/uploads/receipts/' . $receipt['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    // Delete from database
    $del = $db->prepare('DELETE FROM payments WHERE id = :id AND (patient_user_id = :uid OR created_by = :uid)');
    $del->bindValue(':id', (int)$_POST['id'], PDO::PARAM_INT);
    $del->bindValue(':uid', (int)$_SESSION['user_id'], PDO::PARAM_INT);
    $del->execute();

    echo json_encode(['success' => true]);
    exit;

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>