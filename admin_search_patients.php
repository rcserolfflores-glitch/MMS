<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// admin only
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = max(5, min(100, (int)($_GET['per_page'] ?? 10)));
$offset = ($page - 1) * $per_page;

try {
    $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ensure table exists (same as get_patients)
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

    $params = [];
    $where = '1=1';
    if ($q !== '') {
        // search name or mobile_number or exact user_id if numeric
        if (ctype_digit($q)) {
            $where = '(user_id = :uid OR name LIKE :like OR mobile_number LIKE :like)';
            $params[':uid'] = (int)$q;
            $params[':like'] = "%$q%";
        } else {
            $where = '(name LIKE :like OR mobile_number LIKE :like OR email LIKE :like)';
            $params[':like'] = "%$q%";
        }
    }

    // count total
    $countStmt = $db->prepare("SELECT COUNT(*) as c FROM patient_details WHERE $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // fetch page
    $sql = "SELECT * FROM patient_details WHERE $where ORDER BY name ASC LIMIT :lim OFFSET :off";
    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':lim', (int)$per_page, PDO::PARAM_INT);
    $stmt->bindValue(':off', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    echo json_encode(['success' => true, 'total' => $total, 'page' => $page, 'per_page' => $per_page, 'patients' => $out]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

?>
