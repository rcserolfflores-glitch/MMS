<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])){ http_response_code(401); echo json_encode(['success'=>false,'message'=>'Not authenticated']); exit; }

$isAdmin = strtolower($_SESSION['user_type'] ?? '') === 'admin';

if (!$isAdmin && !isset($_GET['patient_user_id'])){ http_response_code(400); echo json_encode(['success'=>false,'message'=>'patient_user_id required']); exit; }

$patientUserId = isset($_GET['patient_user_id']) ? (int)$_GET['patient_user_id'] : (int)$_SESSION['user_id'];

try{
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS patient_files (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        patient_user_id INT DEFAULT NULL,
        filename VARCHAR(255) DEFAULT NULL,
        url VARCHAR(512) DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_by INT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $db->prepare('SELECT id, patient_user_id, filename, url, notes, uploaded_at, created_by FROM patient_files WHERE patient_user_id = ? ORDER BY uploaded_at DESC');
    $stmt->execute([$patientUserId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'count'=>count($rows),'files'=>$rows]);
    exit;
} catch(PDOException $e){ http_response_code(500); echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]); exit; }

?>
