<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Require a logged-in user; clinicians may only list patients assigned to them.
// Keep the initial 401 for not-logged-in users above.

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure table exists (safe to run)
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

    $role = strtolower($_SESSION['user_type'] ?? '');
    $username = trim((string)($_SESSION['username'] ?? ''));

    if (in_array($role, ['doctor','midwife'], true)) {
        // Clinicians: only return patients who have at least one appointment assigned to this clinician
        $sql = 'SELECT DISTINCT p.* FROM patient_details p JOIN appointments a ON a.user_id = p.user_id WHERE (a.assigned_midwife = ? OR a.assigned_provider = ?) ORDER BY p.created_at DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $username]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Admins, clerks, staff: return all patients
        $stmt = $db->query('SELECT * FROM patient_details ORDER BY created_at DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $out = [];
    foreach ($rows as $r) {
        $out[] = [
            'id' => isset($r['id']) ? (int)$r['id'] : null,
            'user_id' => isset($r['user_id']) ? (int)$r['user_id'] : null,
            'name' => $r['name'] ?? null,
            'age' => $r['age'] ?? null,
            'address' => $r['address'] ?? null,
            'birthday' => $r['birthday'] ?? null,
            'mobile_number' => $r['mobile_number'] ?? ($r['cellphone'] ?? null),
            'email' => $r['email'] ?? null,
            'obstetric_history' => $r['obstetric_history'] ?? null,
            'created_at' => $r['created_at'] ?? null,
            'updated_at' => $r['updated_at'] ?? null,
        ];
    }

    echo json_encode(['success' => true, 'count' => count($out), 'patients' => $out]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>
