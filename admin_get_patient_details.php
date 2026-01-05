<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

if (!isset($_GET['patient_user_id']) || !is_numeric($_GET['patient_user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'patient_user_id required']);
    exit();
}

$uid = (int)$_GET['patient_user_id'];
try{
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure patient_details table exists
    $db->exec("CREATE TABLE IF NOT EXISTS patient_details (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255),
        age VARCHAR(20),
        address TEXT,
        birthday DATE,
        mobile_number VARCHAR(40),
        cellphone VARCHAR(40),
        civil_status VARCHAR(50),
        nationality VARCHAR(80),
        email VARCHAR(255),
        religion VARCHAR(80),
        blood_type VARCHAR(8),
        allergies TEXT,
        past_medical_condition TEXT,
        current_medication TEXT,
        obstetric_history TEXT,
        number_of_pregnancies VARCHAR(20),
        number_of_deliveries VARCHAR(20),
        last_menstrual_period DATE,
        expected_delivery_date DATE,
        previous_pregnancy_complication TEXT,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY ux_patient_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $stmt = $db->prepare('SELECT * FROM patient_details WHERE user_id = ? LIMIT 1');
    $stmt->execute([$uid]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$r) {
        echo json_encode(['success'=>true,'data'=>null,'message'=>'No profile']);
        exit;
    }
    echo json_encode(['success'=>true,'data'=>$r]);
    exit;
} catch(PDOException $e){
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
    exit;
}

?>
