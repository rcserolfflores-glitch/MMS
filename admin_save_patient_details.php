<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

$input = [];
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) $input = $decoded;
} else {
    $input = $_POST;
}

// accept multiple possible keys for the patient identifier
$user_id = 0;
$possibleKeys = ['user_id','id','patient_user_id','userId','uid'];
foreach ($possibleKeys as $k) {
    if (isset($input[$k]) && (string)$input[$k] !== '') { $user_id = (int)$input[$k]; break; }
}

// if still not present and an email was provided, attempt a lookup (best-effort)
if (!$user_id && !empty($input['email'])) {
    try {
        $tmpDb = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
        $tmpDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $q = $tmpDb->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $q->execute([trim($input['email'])]);
        $found = $q->fetch(PDO::FETCH_ASSOC);
        if ($found && !empty($found['user_id'])) {
            $user_id = (int)$found['user_id'];
        }
    } catch (Exception $e) {
        // lookup failed — ignore and continue to return an error below
    }
}

if (!$user_id) {
    http_response_code(400);
    // include available input keys to aid debugging
    $presentKeys = array_keys((array)$input);
    echo json_encode(['success'=>false,'message'=>'user_id required','received_keys'=>$presentKeys]);
    exit;
}

try{
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure patient_details table exists (same schema as save_patient_details.php)
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

    $data = [
        'name' => trim($input['name'] ?? ''),
        'age' => trim($input['age'] ?? ''),
        'address' => trim($input['address'] ?? ''),
        'birthday' => $input['birthday'] ?? null,
        'mobile_number' => trim($input['mobile_number'] ?? ($input['cellphone'] ?? '')),
        'civil_status' => trim($input['civil_status'] ?? ''),
        'nationality' => trim($input['nationality'] ?? ''),
        'email' => trim($input['email'] ?? ''),
        'religion' => trim($input['religion'] ?? ''),
        'blood_type' => trim($input['blood_type'] ?? ''),
        'allergies' => trim($input['allergies'] ?? ''),
        'past_medical_condition' => trim($input['past_medical_condition'] ?? ''),
        'current_medication' => trim($input['current_medication'] ?? ''),
        'obstetric_history' => trim($input['obstetric_history'] ?? ''),
        'number_of_pregnancies' => trim($input['number_of_pregnancies'] ?? ''),
        'number_of_deliveries' => trim($input['number_of_deliveries'] ?? ''),
        'last_menstrual_period' => $input['last_menstrual_period'] ?? null,
        'expected_delivery_date' => $input['expected_delivery_date'] ?? null,
        'previous_pregnancy_complication' => trim($input['previous_pregnancy_complication'] ?? ''),
    ];

    $query = "
        INSERT INTO patient_details (
            user_id, name, age, address, birthday, mobile_number, cellphone, civil_status, nationality, email, religion, blood_type,
            allergies, past_medical_condition, current_medication, obstetric_history, number_of_pregnancies,
            number_of_deliveries, last_menstrual_period, expected_delivery_date, previous_pregnancy_complication
        ) VALUES (
            :user_id, :name, :age, :address, :birthday, :mobile_number, :cellphone, :civil_status, :nationality, :email, :religion, :blood_type,
            :allergies, :past_medical_condition, :current_medication, :obstetric_history, :number_of_pregnancies,
            :number_of_deliveries, :last_menstrual_period, :expected_delivery_date, :previous_pregnancy_complication
        ) ON DUPLICATE KEY UPDATE
            name = VALUES(name), age = VALUES(age), address = VALUES(address), birthday = VALUES(birthday),
            mobile_number = VALUES(mobile_number), cellphone = VALUES(cellphone), civil_status = VALUES(civil_status), nationality = VALUES(nationality),
            email = VALUES(email), religion = VALUES(religion), blood_type = VALUES(blood_type),
            allergies = VALUES(allergies), past_medical_condition = VALUES(past_medical_condition),
            current_medication = VALUES(current_medication), obstetric_history = VALUES(obstetric_history),
            number_of_pregnancies = VALUES(number_of_pregnancies), number_of_deliveries = VALUES(number_of_deliveries),
            last_menstrual_period = VALUES(last_menstrual_period), expected_delivery_date = VALUES(expected_delivery_date),
            previous_pregnancy_complication = VALUES(previous_pregnancy_complication)
    ";

    $stmt = $db->prepare($query);
    $params = array_merge(['user_id'=>$user_id,'cellphone'=>$data['mobile_number']], $data);
    $stmt->execute($params);

    $selectStmt = $db->prepare('SELECT * FROM patient_details WHERE user_id = ?');
    $selectStmt->execute([$user_id]);
    $updatedData = $selectStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['success'=>true,'message'=>'Profile saved','data'=>$updatedData]);
    exit;
} catch(PDOException $e){ http_response_code(500); echo json_encode(['success'=>false,'message'=>'DB error: '.$e->getMessage()]); exit; }

?>
