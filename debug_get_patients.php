<?php
// Local debug: return all patient profiles from patient_details.
// Accessible only from localhost.
header('Content-Type: application/json; charset=utf-8');
$remote = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!in_array($remote, ['127.0.0.1','::1','::ffff:127.0.0.1'])) {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden: debug endpoint only available on localhost','remote'=>$remote]);
    exit;
}

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS patient_details (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE NOT NULL,
        name VARCHAR(255),
        age VARCHAR(20),
        address TEXT,
        birthday DATE DEFAULT NULL,
        mobile_number VARCHAR(50),
        civil_status VARCHAR(50),
        nationality VARCHAR(100),
        email VARCHAR(255),
        religion VARCHAR(100),
        blood_type VARCHAR(10),
        allergies TEXT,
        past_medical_condition TEXT,
        current_medication TEXT,
        obstetric_history TEXT,
        number_of_pregnancies VARCHAR(10),
        number_of_deliveries VARCHAR(10),
        last_menstrual_period DATE DEFAULT NULL,
        expected_delivery_date DATE DEFAULT NULL,
        previous_pregnancy_complication TEXT,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $rows = $db->query('SELECT * FROM patient_details ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success'=>true,'count'=>count($rows),'patients'=>$rows]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]);
}

?>
