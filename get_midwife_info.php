<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Allow access when logged in as midwife (or as doctor/admin for compatibility)
if (!isset($_SESSION['user_id']) || !in_array(($_SESSION['user_type'] ?? ''), ['midwife','doctor','admin'], true)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare('SELECT * FROM midwife_info WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => true, 'data' => null]);
        exit;
    }

    if (empty($row['avatar_url'])) $row['avatar_url'] = null;

    echo json_encode(['success' => true, 'data' => $row]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

?>
