<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
// initialize PDO connection (try includes_db_Version4.php, else fall back to legacy db_connect.php)
// prefer the include-based PDO helper only when config.php exists alongside it
if (file_exists(__DIR__ . '/includes_db_Version4.php') && file_exists(__DIR__ . '/config.php')) {
    $db = require __DIR__ . '/includes_db_Version4.php';
} else {
    // legacy fallback: attempt to use db_connect.php variables to create a PDO
    if (file_exists(__DIR__ . '/db_connect.php')) {
        require_once __DIR__ . '/db_connect.php';
        try {
            $dsn = "mysql:host={$servername};dbname={$database};charset=utf8mb4";
            $db = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed (fallback)']);
            exit;
        }
    } else {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Missing DB configuration (includes_db_Version4.php or db_connect.php not found)']);
        exit;
    }
}


// ensure settings table exists
try{
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
      `k` VARCHAR(191) PRIMARY KEY,
      `v` TEXT,
      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}catch(Exception $e){ /* non-fatal */ }

// shared mail helper
require_once __DIR__ . '/mail_functions.php';

// determine action from form-data, query string, or JSON body
$action = $_POST['action'] ?? $_GET['action'] ?? '';
if (!$action) {
    $rawIn = @file_get_contents('php://input');
    $decoded = null;
    if ($rawIn !== false && trim($rawIn) !== '') {
        $decoded = @json_decode($rawIn, true);
        if (!is_array($decoded)) {
            parse_str($rawIn, $parsed);
            if (is_array($parsed) && count($parsed)) $decoded = $parsed;
        }
    }
    if (is_array($decoded)) {
        foreach ($decoded as $k => $v) {
            if (!isset($_POST[$k])) $_POST[$k] = $v;
        }
        $action = $_POST['action'] ?? '';
    }
}

if ($action === 'list') {
    // list users
    try{
        $stmt = $db->query('SELECT id, username, email, user_type, created_at FROM users ORDER BY id DESC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'users' => $rows]);
        exit;
    } catch(PDOException $e){
        echo json_encode(['success' => false, 'message' => 'Query error: '.$e->getMessage()]);
        exit;
    }
}

// Get a stored setting by key
if ($action === 'get_setting') {
    $key = $_POST['key'] ?? $_GET['key'] ?? '';
    if (!$key) { echo json_encode(['success'=>false,'message'=>'key required']); exit; }
    try{
        $stmt = $db->prepare('SELECT v FROM settings WHERE k = :k LIMIT 1');
        $stmt->execute([':k'=>$key]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true,'value'=> $r ? $r['v'] : null]); exit;
    } catch(PDOException $e){ echo json_encode(['success'=>false,'message'=>'Query error: '.$e->getMessage()]); exit; }
}

// Set a stored setting (key, value)
if ($action === 'set_setting') {
    $key = $_POST['key'] ?? '';
    $value = $_POST['value'] ?? null;
    if (!$key) { echo json_encode(['success'=>false,'message'=>'key required']); exit; }
    try{
        $ins = $db->prepare("INSERT INTO settings (k,v) VALUES (:k,:v) ON DUPLICATE KEY UPDATE v = :v, updated_at = NOW()");
        $ins->execute([':k'=>$key, ':v'=>$value]);
        echo json_encode(['success'=>true,'message'=>'Saved']); exit;
    } catch(PDOException $e){ echo json_encode(['success'=>false,'message'=>'Save failed: '.$e->getMessage()]); exit; }
}

if ($action === 'create') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? 'patient';
    // optional patient details from admin modal
    $full_name = trim($_POST['full_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $religion = trim($_POST['religion'] ?? '');

    if (!$username || !$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'username, email and password required']); exit;
    }

    try{
        // ensure username/email unique; but if an existing user is found, link/update them instead of failing
        $chk = $db->prepare('SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1');
        $chk->execute([':u'=>$username, ':e'=>$email]);
        $existing = $chk->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $existingId = (int)$existing['id'];
            try{
                // mark existing user as verified
                try{
                    $db->prepare('UPDATE users SET is_verified = 1 WHERE id = :id')->execute([':id'=>$existingId]);
                } catch(PDOException $inner){
                    try{ $db->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0"); $db->prepare('UPDATE users SET is_verified = 1 WHERE id = :id')->execute([':id'=>$existingId]); } catch(PDOException $ee) { /* non-fatal */ }
                }

                // ensure patient_details exists and insert if missing
                $pcheck = $db->prepare('SELECT id FROM patient_details WHERE user_id = :uid');
                try{ $pcheck->execute([':uid'=>$existingId]); $has = $pcheck->fetch(); } catch(PDOException $e){ $has = false; }
                if (!$has) {
                    // create table if missing
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
                      created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      UNIQUE KEY ux_patient_user (user_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
                    $pstmt = $db->prepare('INSERT INTO patient_details (user_id, name, age, address, birthday, mobile_number, civil_status, nationality, email, religion) VALUES (:uid,:name,:age,:addr,:bday,:mobile,:civil,:nat,:email,:religion)');
                    $pstmt->execute([':uid'=>$existingId, ':name'=>$full_name, ':age'=>$age, ':addr'=>$address, ':bday'=>($dob?:null), ':mobile'=>$contact, ':civil'=>$civil_status, ':nat'=>$nationality, ':email'=>$email, ':religion'=>$religion]);
                }

                // send notification about linking/verification. For patient accounts
                // set a known default password and include it in the email so the
                // clinic can inform the patient. Reminder: user should change it.
                try{
                    if (strtolower($user_type) === 'patient') {
                        $passwordSent = 'P@ssw0rd';
                        $hashPwd = password_hash($passwordSent, PASSWORD_DEFAULT);
                        try {
                            $db->prepare('UPDATE users SET password = :p WHERE id = :id')->execute([':p'=>$hashPwd, ':id'=>$existingId]);
                        } catch (Exception $e) { /* non-fatal */ }
                        $subject = 'Account linked and verified by admin';
                        $body = "Hello " . ($full_name ?: $username) . ",\n\nAn administrator created or linked your account and marked it verified.\n\nYour password is \"{$passwordSent}\"\nPlease change your password after you receive this account to keep your account secure.\n\nYou can log in using your existing credentials.\n\nRegards,\nDrea Lying-In Clinic";
                    } else {
                        $subject = 'Account linked and verified by admin';
                        $body = "Hello " . ($full_name ?: $username) . ",\n\nAn administrator created or linked your account and marked it verified. You can log in using your existing credentials.\n\nRegards,\nDrea Lying-In Clinic";
                    }
                    send_notification_email($email, $subject, $body);
                } catch(Exception $e) { /* non-fatal */ }

                echo json_encode(['success'=>true,'message'=>'Linked to existing user and verified','user_id'=>$existingId]); exit;
            } catch(PDOException $e){ echo json_encode(['success'=>false,'message'=>'Link existing failed: '.$e->getMessage()]); exit; }
        }

        // For patient accounts created by admin, set a default known password
        // so the clinic can inform the patient via email. This mirrors the
        // requested behavior: password = P@ssw0rd. For other user types,
        // use the provided password from the admin form.
        // Use password provided by admin (frontend should supply generated password).
        // Do not fallback to the old literal "P@ssw0rd" value.
        $passwordSent = $password;
        $hash = password_hash($passwordSent, PASSWORD_DEFAULT);
        $ins = $db->prepare('INSERT INTO users (username, email, password, user_type, created_at) VALUES (:u,:e,:p,:t,NOW())');
        $ins->execute([':u'=>$username,':e'=>$email,':p'=>$hash,':t'=>$user_type]);
        $newUserId = (int)$db->lastInsertId();

        // try to mark the user as verified (admin-created walk-in should be verified)
        try{
            $db->prepare('UPDATE users SET is_verified = 1 WHERE id = :id')->execute([':id'=>$newUserId]);
        } catch(PDOException $inner){
            // if column missing, attempt to add it then update
            try{ $db->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0"); $db->prepare('UPDATE users SET is_verified = 1 WHERE id = :id')->execute([':id'=>$newUserId]); } catch(PDOException $ee) { /* non-fatal */ }
        }

        // if a patient was created or patient details provided, create patient_details row
        if ($user_type === 'patient' || $full_name !== '') {
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
              created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY ux_patient_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $pstmt = $db->prepare('INSERT INTO patient_details (user_id, name, age, address, birthday, mobile_number, civil_status, nationality, email, religion) VALUES (:uid,:name,:age,:addr,:bday,:mobile,:civil,:nat,:email,:religion)');
            $pstmt->execute([':uid'=>$newUserId, ':name'=>$full_name, ':age'=>$age, ':addr'=>$address, ':bday'=>($dob?:null), ':mobile'=>$contact, ':civil'=>$civil_status, ':nat'=>$nationality, ':email'=>$email, ':religion'=>$religion]);
        }

        // send welcome email. If this is a patient account created by admin,
        // include the default plaintext password and a reminder to change it.
        try {
            $subject = 'Your account has been created';
            $loginUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/login.php';
            if (strtolower($user_type) === 'patient') {
                $body = "Good day,\n\nYour account has been successfully created.\n\nFor your initial login, please use the temporary password below:\n\n";
                // Do not include the literal generated password in the email; instead
                // explain the password format so patients know how to construct it.
                $body .= "Password: Your First Name + Your Birthdate\n(example: Maria010203)\n\n";
                $body .= "For security purposes, we strongly recommend changing your password immediately after logging in.\n\n";
                $body .= "If you did not request this account or need assistance, please contact our support team.\n\n";
                $body .= "Thank you,\nDrea Lying In Clinic\n\nYou may log in at: {$loginUrl}";
            } else {
                $body = "Good day,\n\nYour account has been successfully created.\n\nYou may log in at: {$loginUrl}\n\nThank you,\nDrea Lying In Clinic";
            }
            send_notification_email($email, $subject, $body);
        } catch(Exception $e) { /* non-fatal */ }

        echo json_encode(['success'=>true,'message'=>'User created','user_id'=>$newUserId]); exit;
    } catch(PDOException $e){
        echo json_encode(['success'=>false,'message'=>'Insert failed: '.$e->getMessage()]); exit;
    }
}

if ($action === 'list_verifications') {
    $status = $_GET['status'] ?? 'pending';
    try{
        // if table missing, return empty
        $stmt = $db->prepare("SELECT v.*, u.username, u.email, COALESCE(p.name,'') AS patient_name FROM user_verifications v LEFT JOIN users u ON u.id = v.user_id LEFT JOIN patient_details p ON p.user_id = v.user_id WHERE v.status = :status ORDER BY v.submitted_at DESC");
        $stmt->execute([':status'=>$status]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true,'verifications'=>$rows]); exit;
    } catch(PDOException $e){
        echo json_encode(['success'=>false,'message'=>'Query error: '.$e->getMessage()]); exit;
    }
}

if ($action === 'review_verification') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $decision = $_POST['decision'] ?? '';
    $notes = $_POST['notes'] ?? '';
    if (!$id || !in_array($decision, ['approve','reject'])) { echo json_encode(['success'=>false,'message'=>'id and valid decision required']); exit; }
    try{
        $db->beginTransaction();
        $status = $decision === 'approve' ? 'approved' : 'rejected';
        // fetch the verification row
        $q = $db->prepare('SELECT user_id FROM user_verifications WHERE id = :id FOR UPDATE');
        $q->execute([':id'=>$id]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if (!$row) { $db->rollBack(); echo json_encode(['success'=>false,'message'=>'Verification not found']); exit; }
        $userId = (int)$row['user_id'];
        $upd = $db->prepare('UPDATE user_verifications SET status = :status, notes = :notes, reviewed_by = :rb, reviewed_at = NOW() WHERE id = :id');
        $upd->execute([':status'=>$status,':notes'=>$notes,':rb'=>$_SESSION['user_id'],':id'=>$id]);
        if ($decision === 'approve') {
            // best-effort set users.is_verified = 1
            $db->prepare('UPDATE users SET is_verified = 1 WHERE id = :uid')->execute([':uid'=>$userId]);
        }
        $db->commit();
        echo json_encode(['success'=>true,'message'=>'Updated']); exit;
    } catch(PDOException $e){
        try{ if($db->inTransaction()) $db->rollBack(); } catch(Exception $ee){}
        echo json_encode(['success'=>false,'message'=>'Review failed: '.$e->getMessage()]); exit;
    }
}

// List pending registrations (signup submissions awaiting admin approval)
if ($action === 'list_pending_registrations') {
    $status = $_GET['status'] ?? 'pending';
    try{
        $sql = "SELECT * FROM pending_registrations";
        if ($status && $status !== 'all') $sql .= " WHERE status = :status";
        $sql .= " ORDER BY submitted_at DESC";
        $stmt = $db->prepare($sql);
        if ($status && $status !== 'all') $stmt->execute([':status'=>$status]); else $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true,'pending'=>$rows]); exit;
    } catch(PDOException $e){ echo json_encode(['success'=>false,'message'=>'Query error: '.$e->getMessage()]); exit; }
}

// Review a pending registration: approve -> create user, reject -> mark rejected
if ($action === 'review_pending_registration') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $decision = $_POST['decision'] ?? '';
    $notes = $_POST['notes'] ?? '';
    if (!$id || !in_array($decision, ['approve','reject'])) { echo json_encode(['success'=>false,'message'=>'id and valid decision required']); exit; }
    try{
        // Ensure patient_details table exists BEFORE starting a transaction.
        // Running DDL inside a transaction can cause an implicit commit in MySQL
        // which ends the transaction and later makes commit() fail with
        // "There is no active transaction". Create table first (best-effort).
        try{
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
              created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY ux_patient_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        } catch(PDOException $e) { /* non-fatal */ }

        $db->beginTransaction();
        $q = $db->prepare('SELECT * FROM pending_registrations WHERE id = :id FOR UPDATE');
        $q->execute([':id'=>$id]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if (!$row) { $db->rollBack(); echo json_encode(['success'=>false,'message'=>'Pending registration not found']); exit; }
        if ($decision === 'reject') {
            $upd = $db->prepare('UPDATE pending_registrations SET status = :status, admin_notes = :notes WHERE id = :id');
            $upd->execute([':status'=>'rejected', ':notes'=>$notes, ':id'=>$id]);
            $db->commit();
            echo json_encode(['success'=>true,'message'=>'Registration rejected']); exit;
        }

        // approve -> create user and patient details
        // ensure username/email not already taken
        $check = $db->prepare('SELECT id FROM users WHERE username = :u OR email = :e');
        $check->execute([':u'=>$row['username'], ':e'=>$row['email']]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            // User already exists — link the pending registration to this existing user instead of failing.
            $existingId = (int)$existing['id'];
            // mark existing user as verified (best-effort)
            $db->prepare('UPDATE users SET is_verified = 1 WHERE id = :id')->execute([':id'=>$existingId]);
            // ensure patient_details exists for this user
            $pcheck = $db->prepare('SELECT id FROM patient_details WHERE user_id = :uid');
            $pcheck->execute([':uid'=>$existingId]);
            if (!$pcheck->fetch()) {
                $pstmt = $db->prepare('INSERT INTO patient_details (user_id, name, age, address, birthday, mobile_number, civil_status, nationality, email, religion) VALUES (:uid,:name,:age,:addr,:bday,:mobile,:civil,:nat,:email,:religion)');
                $pstmt->execute([':uid'=>$existingId, ':name'=>$row['name'], ':age'=>$row['age'], ':addr'=>$row['address'], ':bday'=>($row['birthday']?:null), ':mobile'=>$row['contact'], ':civil'=>$row['civil_status'], ':nat'=>$row['nationality'], ':email'=>$row['email'], ':religion'=>$row['religion']]);
            }
            // mark pending registration approved and note it was linked
            $upd = $db->prepare('UPDATE pending_registrations SET status = :status, admin_notes = :notes WHERE id = :id');
            $noteText = trim('Linked to existing user id ' . $existingId . '. ' . $notes);
            $upd->execute([':status'=>'approved', ':notes'=>$noteText, ':id'=>$id]);
            $db->commit();
            // notify user that their registration was approved/linked
            try {
                $subject = 'Your account has been approved';
                $body = "Hello,\n\nYour account registration has been approved by the clinic. You can now log in using your registered email address.\n\nIf you did not expect this, please contact support.\n\nRegards,\nDrea Lying-In Clinic";
                send_notification_email($row['email'], $subject, $body);
            } catch (Exception $e) { /* non-fatal */ }
            echo json_encode(['success'=>true,'message'=>'Registration linked to existing user and approved']); exit;
        }

        $ins = $db->prepare('INSERT INTO users (username, email, password, user_type, created_at) VALUES (:u,:e,:p,:t,NOW())');
        $ins->execute([':u'=>$row['username'], ':e'=>$row['email'], ':p'=>$row['password_hash'], ':t'=>'patient']);
        $newUserId = (int)$db->lastInsertId();

        // create patient_details table if missing and insert
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
          created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          UNIQUE KEY ux_patient_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $pstmt = $db->prepare('INSERT INTO patient_details (user_id, name, age, address, birthday, mobile_number, civil_status, nationality, email, religion) VALUES (:uid,:name,:age,:addr,:bday,:mobile,:civil,:nat,:email,:religion)');
        $pstmt->execute([':uid'=>$newUserId, ':name'=>$row['name'], ':age'=>$row['age'], ':addr'=>$row['address'], ':bday'=>($row['birthday']?:null), ':mobile'=>$row['contact'], ':civil'=>$row['civil_status'], ':nat'=>$row['nationality'], ':email'=>$row['email'], ':religion'=>$row['religion']]);

        // mark pending registration approved
        $upd = $db->prepare('UPDATE pending_registrations SET status = :status, admin_notes = :notes WHERE id = :id');
        $upd->execute([':status'=>'approved', ':notes'=>$notes, ':id'=>$id]);

        $db->commit();
        // notify newly created user by email
        try {
            $subject = 'Your account is ready';
            $loginUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/login.php';
            $body = "Hello {$row['name']},\n\nYour account has been created and approved by the clinic. You can log in here: {$loginUrl}\n\nRegards,\nDrea Lying-In Clinic";
            send_notification_email($row['email'], $subject, $body);
        } catch (Exception $e) { /* non-fatal */ }
        echo json_encode(['success'=>true,'message'=>'Registration approved and user created']); exit;
    } catch(PDOException $e){ try{ if($db->inTransaction()) $db->rollBack(); }catch(Exception$e){} echo json_encode(['success'=>false,'message'=>'Review failed: '.$e->getMessage()]); exit; }
}

if ($action === 'update') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if (!$id) { echo json_encode(['success'=>false,'message'=>'id required']); exit; }
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $user_type = $_POST['user_type'] ?? 'patient';
    $password = $_POST['password'] ?? null;
    try{
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE users SET username=:u,email=:e,user_type=:t,password=:p WHERE id=:id');
            $stmt->execute([':u'=>$username,':e'=>$email,':t'=>$user_type,':p'=>$hash,':id'=>$id]);
        } else {
            $stmt = $db->prepare('UPDATE users SET username=:u,email=:e,user_type=:t WHERE id=:id');
            $stmt->execute([':u'=>$username,':e'=>$email,':t'=>$user_type,':id'=>$id]);
        }
        echo json_encode(['success'=>true,'message'=>'Updated']); exit;
    } catch(PDOException $e){ echo json_encode(['success'=>false,'message'=>'Update failed: '.$e->getMessage()]); exit; }
}

if ($action === 'delete') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if (!$id) { echo json_encode(['success'=>false,'message'=>'id required']); exit; }
    try{
        $stmt = $db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id'=>$id]);
        echo json_encode(['success'=>true,'message'=>'Deleted']); exit;
    } catch(PDOException $e){ echo json_encode(['success'=>false,'message'=>'Delete failed: '.$e->getMessage()]); exit; }
}

if ($action === 'stats') {
    try{
        // total registered patients
        $stmt = $db->prepare("SELECT COUNT(*) AS c FROM users WHERE user_type = 'patient'"); $stmt->execute(); $patients = (int)$stmt->fetchColumn();
        // total doctors and midwives
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'doctor'"); $stmt->execute(); $doctors = (int)$stmt->fetchColumn();
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'midwife'"); $stmt->execute(); $midwives = (int)$stmt->fetchColumn();

        // appointments today
        $stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()"); $stmt->execute(); $apptToday = (int)$stmt->fetchColumn();

        // appointments this week
        $stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"); $stmt->execute(); $apptWeek = (int)$stmt->fetchColumn();

        // upcoming deliveries (appointments whose service mentions 'deliver' within 30 days)
        $stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE (LOWER(service) LIKE '%deliver%' OR LOWER(service) LIKE '%delivery%') AND appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"); $stmt->execute(); $upcomingDeliveries = (int)$stmt->fetchColumn();

        // newborns (table may not exist on some installs)
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM newborns"); $stmt->execute(); $newborns = (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            $newborns = 0;
        }

        // payments
        $stmt = $db->prepare("SELECT COUNT(*) FROM payments"); $stmt->execute(); $paymentsTotal = (int)$stmt->fetchColumn();
        // payments pending - no dedicated flag in current schema; report 0 and include note
        $paymentsPending = 0;

        // recent activity: union of last appointments (created_at), payments (uploaded_at), lab_results (uploaded_at)
        $activities = [];
        // appointments - include patient name when available
        $stmt = $db->prepare("SELECT a.id, a.user_id AS patient_user_id, COALESCE(p.name, '') AS patient_name, a.service AS summary, a.appointment_date AS appt_date, a.appointment_time AS appt_time, a.created_at AS ts, 'appointment' AS type FROM appointments a LEFT JOIN patient_details p ON p.user_id = a.user_id ORDER BY a.created_at DESC LIMIT 6");
        $stmt->execute();
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r){
            $summary = $r['summary'];
            if(!empty($r['appt_date'])) $summary .= ' on ' . $r['appt_date'];
            if(!empty($r['appt_time'])) $summary .= ' at ' . $r['appt_time'];
            $activities[] = [
                'type' => 'appointment',
                'id' => $r['id'],
                'patient_user_id' => (int)$r['patient_user_id'],
                'patient_name' => $r['patient_name'] ?? '',
                'summary' => $summary,
                'ts' => $r['ts']
            ];
        }

        // payments - prefer stored patient_name, fallback to patient_details
        $stmt = $db->prepare("SELECT pay.id, pay.patient_user_id, COALESCE(pay.patient_name, p.name, '') AS patient_name, pay.filename AS summary, pay.uploaded_at AS ts, 'payment' AS type FROM payments pay LEFT JOIN patient_details p ON p.user_id = pay.patient_user_id ORDER BY pay.uploaded_at DESC LIMIT 6");
        $stmt->execute();
        foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r){
            $activities[] = [
                'type' => 'payment',
                'id' => $r['id'],
                'patient_user_id' => isset($r['patient_user_id']) ? (int)$r['patient_user_id'] : null,
                'patient_name' => $r['patient_name'] ?? '',
                'summary' => $r['summary'],
                'ts' => $r['ts']
            ];
        }

        // lab results - include patient name if available (table may not exist)
        try {
            $stmt = $db->prepare("SELECT l.id, l.patient_user_id, COALESCE(p.name, '') AS patient_name, l.filename AS summary, l.uploaded_at AS ts, 'lab' AS type FROM lab_results l LEFT JOIN patient_details p ON p.user_id = l.patient_user_id ORDER BY l.uploaded_at DESC LIMIT 6");
            $stmt->execute();
            foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r){
                $activities[] = [
                    'type' => 'lab',
                    'id' => $r['id'],
                    'patient_user_id' => isset($r['patient_user_id']) ? (int)$r['patient_user_id'] : null,
                    'patient_name' => $r['patient_name'] ?? '',
                    'summary' => $r['summary'],
                    'ts' => $r['ts']
                ];
            }
        } catch (PDOException $e) {
            // missing table or other DB issue — skip lab results in activity feed
        }

        // sort activities by ts desc and limit 10
        usort($activities, function($a,$b){ return strtotime($b['ts']) <=> strtotime($a['ts']); });
        $activities = array_slice($activities,0,10);

        echo json_encode([
            'success'=>true,
            'total_patients'=>$patients,
            'total_doctors'=>$doctors,
            'total_midwives'=>$midwives,
            'appointments_today'=>$apptToday,
            'appointments_week'=>$apptWeek,
            'upcoming_deliveries'=>$upcomingDeliveries,
            'newborns'=>$newborns,
            'payments_total'=>$paymentsTotal,
            'payments_pending'=>$paymentsPending,
            'recent_activity'=>$activities
        ]);
        exit;
    } catch(PDOException $e){ echo json_encode(['success'=>false,'message'=>'Stats error: '.$e->getMessage()]); exit; }
}

// Run a report requested by the admin dashboard
if ($action === 'run_report') {
    $reportType = $_POST['report_type'] ?? $_GET['report_type'] ?? '';
    $category = $_POST['category'] ?? $_GET['category'] ?? 'all';
    try{
        $records = [];
        if ($reportType === 'daily_patients') {
            $stmt = $db->prepare("SELECT DATE(created_at) AS date, COUNT(*) AS total_count FROM users WHERE user_type = 'patient' GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30");
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($reportType === 'daily_payments') {
            try{
                $stmt = $db->prepare("SELECT DATE(uploaded_at) AS date, COUNT(*) AS total_count, COALESCE(SUM(amount),0) AS total_amount FROM payments GROUP BY DATE(uploaded_at) ORDER BY date DESC LIMIT 30");
                $stmt->execute();
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(PDOException $e){ $records = []; }
        } elseif ($reportType === 'monthly_appointments') {
            try{
                $stmt = $db->prepare("SELECT DATE_FORMAT(appointment_date,'%Y-%m') AS date, COUNT(*) AS total_count FROM appointments GROUP BY DATE_FORMAT(appointment_date,'%Y-%m') ORDER BY date DESC LIMIT 12");
                $stmt->execute();
                $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch(PDOException $e){ $records = []; }
        } elseif ($reportType === 'annual_report') {
            // Expect 'year' param
            $year = isset($_POST['year']) ? intval($_POST['year']) : (isset($_GET['year']) ? intval($_GET['year']) : (int)date('Y'));
            $annual = [];
            // 1. Total Patients for the Year
            try{
                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'patient' AND YEAR(created_at) = :yr");
                $stmt->execute([':yr'=>$year]); $totalPatients = (int)$stmt->fetchColumn();
            } catch(PDOException $e){ $totalPatients = 0; }
            try{
                $stmt = $db->prepare("SELECT COUNT(DISTINCT user_id) FROM appointments WHERE LOWER(service) LIKE '%prenatal%' AND YEAR(appointment_date) = :yr");
                $stmt->execute([':yr'=>$year]); $prenatalCount = (int)$stmt->fetchColumn();
            } catch(PDOException $e){ $prenatalCount = 0; }
            try{
                $stmt = $db->prepare("SELECT COUNT(*) FROM newborns WHERE YEAR(date_of_birth) = :yr"); $stmt->execute([':yr'=>$year]); $newbornsCount = (int)$stmt->fetchColumn();
            } catch(PDOException $e){ $newbornsCount = 0; }
            try{
                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'patient' AND YEAR(created_at) = :yr"); $stmt->execute([':yr'=>$year]); $newPatients = (int)$stmt->fetchColumn();
            } catch(PDOException $e){ $newPatients = 0; }
            try{
                $stmt = $db->prepare("SELECT COUNT(DISTINCT a.user_id) FROM appointments a JOIN users u ON u.id = a.user_id WHERE YEAR(a.appointment_date) = :yr AND YEAR(u.created_at) < :yr"); $stmt->execute([':yr'=>$year]); $returningPatients = (int)$stmt->fetchColumn();
            } catch(PDOException $e){ $returningPatients = 0; }
            $annual['patients'] = ['total'=>$totalPatients,'prenatal'=>$prenatalCount,'newborns'=>$newbornsCount,'new_patients'=>$newPatients,'returning_patients'=>$returningPatients];

            // 2. Annual Appointments Summary
            try{ $stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE LOWER(service) LIKE '%check%' AND YEAR(appointment_date)=:yr"); $stmt->execute([':yr'=>$year]); $totalCheckups = (int)$stmt->fetchColumn(); } catch(PDOException $e){ $totalCheckups = 0; }
            try{ $stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE (LOWER(status) LIKE '%cancel%' OR LOWER(status) LIKE '%no show%' OR LOWER(status) = 'cancelled') AND YEAR(appointment_date)=:yr"); $stmt->execute([':yr'=>$year]); $missed = (int)$stmt->fetchColumn(); } catch(PDOException $e){ $missed = 0; }
            $mostActiveMonths = [];
            try{ $stmt = $db->prepare("SELECT DATE_FORMAT(appointment_date,'%Y-%m') AS month, COUNT(*) AS cnt FROM appointments WHERE YEAR(appointment_date)=:yr GROUP BY month ORDER BY cnt DESC LIMIT 3"); $stmt->execute([':yr'=>$year]); $mostActiveMonths = $stmt->fetchAll(PDO::FETCH_ASSOC); } catch(PDOException $e){ $mostActiveMonths = []; }
            $annual['appointments'] = ['total_checkups'=>$totalCheckups,'missed_or_cancelled'=>$missed,'most_active_months'=>$mostActiveMonths];

            // 3. Financial Summary
            try{ $stmt = $db->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE YEAR(uploaded_at)=:yr"); $stmt->execute([':yr'=>$year]); $totalIncome = $stmt->fetchColumn(); } catch(PDOException $e){ $totalIncome = 0; }
            $byCategory = [];
            try{ $stmt = $db->prepare("SELECT COALESCE(category,'Uncategorized') AS cat, COALESCE(SUM(amount),0) AS total FROM payments WHERE YEAR(uploaded_at)=:yr GROUP BY cat"); $stmt->execute([':yr'=>$year]); foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $byCategory[$r['cat']] = $r['total']; } catch(PDOException $e){ $byCategory = new stdClass(); }
            $byMethod = [];
            try{ $stmt = $db->prepare("SELECT COALESCE(method,'Unknown') AS m, COALESCE(SUM(amount),0) AS total FROM payments WHERE YEAR(uploaded_at)=:yr GROUP BY m"); $stmt->execute([':yr'=>$year]); foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $byMethod[$r['m']] = $r['total']; } catch(PDOException $e){ $byMethod = new stdClass(); }
            $annual['finance'] = ['total_income'=>$totalIncome, 'by_category'=>$byCategory, 'by_method'=>$byMethod];

            // 4. Inventory Usage Summary (best-effort)
            $inv = ['total_used'=>0,'top_consumed'=>[],'restock_frequency'=>null];
            try{
                // try inventory_usage table
                $stmt = $db->prepare("SELECT COALESCE(SUM(quantity),0) FROM inventory_usage WHERE YEAR(used_at)=:yr"); $stmt->execute([':yr'=>$year]); $inv['total_used'] = (int)$stmt->fetchColumn();
                $stmt = $db->prepare("SELECT item_name AS item, COALESCE(SUM(quantity),0) AS used FROM inventory_usage WHERE YEAR(used_at)=:yr GROUP BY item_name ORDER BY used DESC LIMIT 5"); $stmt->execute([':yr'=>$year]); $inv['top_consumed'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                // restock frequency: average number of restocks per item
                $stmt = $db->prepare("SELECT AVG(cnt) as avgcnt FROM (SELECT COUNT(*) AS cnt FROM inventory_restock WHERE YEAR(restocked_at)=:yr GROUP BY item_id) x"); $stmt->execute([':yr'=>$year]); $inv['restock_frequency'] = $stmt->fetchColumn();
            } catch(PDOException $e){ // fallback try inventory table compare in/out - skip
            }
            $annual['inventory'] = $inv;

            // 5. Staff Activity Reports
            $staff = ['doctor_appointments'=>0,'midwife_services'=>0,'overtime'=>null];
            try{ $stmt = $db->prepare("SELECT COUNT(*) FROM appointments a JOIN users u ON u.id = a.assigned_to WHERE LOWER(u.user_type)='doctor' AND YEAR(a.appointment_date)=:yr"); $stmt->execute([':yr'=>$year]); $staff['doctor_appointments'] = (int)$stmt->fetchColumn(); } catch(PDOException $e){ }
            try{ $stmt = $db->prepare("SELECT COUNT(*) FROM appointments a JOIN users u ON u.id = a.assigned_to WHERE LOWER(u.user_type)='midwife' AND YEAR(a.appointment_date)=:yr"); $stmt->execute([':yr'=>$year]); $staff['midwife_services'] = (int)$stmt->fetchColumn(); } catch(PDOException $e){ }
            $annual['staff'] = $staff;

            // 6. Laboratory & Prescription Summary
            $lp = ['lab_tests'=>0,'prescriptions'=>0];
            try{ $stmt = $db->prepare("SELECT COUNT(*) FROM lab_results WHERE YEAR(uploaded_at)=:yr"); $stmt->execute([':yr'=>$year]); $lp['lab_tests'] = (int)$stmt->fetchColumn(); } catch(PDOException $e){ }
            try{ $stmt = $db->prepare("SELECT COUNT(*) FROM prescriptions WHERE YEAR(created_at)=:yr"); $stmt->execute([':yr'=>$year]); $lp['prescriptions'] = (int)$stmt->fetchColumn(); } catch(PDOException $e){ }
            $annual['lab_prescriptions'] = $lp;

            // 7. Annual Issues or Alerts
            $issues = ['emergencies'=>0,'system_downtimes'=>null,'policy_changes'=>null];
            try{ $stmt = $db->prepare("SELECT COUNT(*) FROM appointments WHERE LOWER(service) LIKE '%emerg%' AND YEAR(appointment_date)=:yr"); $stmt->execute([':yr'=>$year]); $issues['emergencies'] = (int)$stmt->fetchColumn(); } catch(PDOException $e){ }
            $annual['issues'] = $issues;

            echo json_encode(['success'=>true,'annual'=>$annual]); exit;
        } elseif ($reportType === 'inventory_snapshot') {
            try{
                $stmt = $db->prepare("SELECT COUNT(*) AS total_count, SUM(CASE WHEN quantity <= 5 THEN 1 ELSE 0 END) AS low_stock FROM inventory");
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $records = [[ 'date' => date('Y-m-d'), 'report_type' => 'inventory_snapshot', 'total_count' => (int)($row['total_count'] ?? 0), 'low_stock' => (int)($row['low_stock'] ?? 0), 'status' => 'Completed' ]];
            } catch(PDOException $e){ $records = [[ 'date' => date('Y-m-d'), 'report_type' => 'inventory_snapshot', 'total_count' => 0, 'low_stock' => 0, 'status' => 'Completed' ]]; }
        } else {
            // unknown report type: return empty success
            $records = [];
        }
        echo json_encode(['success' => true, 'records' => $records]); exit;
    } catch(PDOException $e){ echo json_encode(['success'=>false,'message'=>'Report failed: '.$e->getMessage()]); exit; }
}

// recent reports placeholder (no persistence implemented)
if ($action === 'recent_reports') {
    echo json_encode(['success'=>true,'recent'=>[]]); exit;
}

// Service management: ensure table exists and implement list/save/delete for admin
if (in_array($action, ['list_services','save_service','delete_service'])) {
    try{
        // create services table if missing
        $db->exec("CREATE TABLE IF NOT EXISTS services (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          name VARCHAR(255) NOT NULL,
          price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
          description TEXT,
          category VARCHAR(80) DEFAULT 'General',
          active TINYINT(1) DEFAULT 1,
          created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        if ($action === 'list_services') {
            $stmt = $db->prepare('SELECT id, name, price, description, category, active, created_at FROM services ORDER BY id DESC');
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true,'services'=>$rows]); exit;
        }

        if ($action === 'save_service') {
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : 0;
            $name = trim($_POST['name'] ?? '');
            $price = isset($_POST['price']) ? floatval(str_replace(',', '', $_POST['price'])) : 0.00;
            $desc = $_POST['description'] ?? null;
            $cat = $_POST['category'] ?? 'General';
            if ($name === '') { echo json_encode(['success'=>false,'message'=>'name required']); exit; }
            if ($id) {
                $upd = $db->prepare('UPDATE services SET name = :name, price = :price, description = :desc, category = :cat, updated_at = NOW() WHERE id = :id');
                $upd->execute([':name'=>$name,':price'=>$price,':desc'=>$desc,':cat'=>$cat,':id'=>$id]);
                $stmt = $db->prepare('SELECT id, name, price, description, category, active FROM services WHERE id = :id'); $stmt->execute([':id'=>$id]); $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success'=>true,'message'=>'Updated','service'=>$row]); exit;
            } else {
                $ins = $db->prepare('INSERT INTO services (name, price, description, category, active, created_at) VALUES (:name,:price,:desc,:cat,1,NOW())');
                $ins->execute([':name'=>$name,':price'=>$price,':desc'=>$desc,':cat'=>$cat]);
                $nid = (int)$db->lastInsertId();
                $stmt = $db->prepare('SELECT id, name, price, description, category, active FROM services WHERE id = :id'); $stmt->execute([':id'=>$nid]); $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success'=>true,'message'=>'Created','service'=>$row]); exit;
            }
        }

        if ($action === 'delete_service') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if (!$id) { echo json_encode(['success'=>false,'message'=>'id required']); exit; }
            // toggle active by default; if permanent=1 then delete
            $permanent = isset($_POST['permanent']) && ($_POST['permanent'] === '1' || $_POST['permanent'] === 1);
            if ($permanent) {
                $del = $db->prepare('DELETE FROM services WHERE id = :id'); $del->execute([':id'=>$id]);
                echo json_encode(['success'=>true,'message'=>'Deleted']); exit;
            } else {
                // fetch current
                $q = $db->prepare('SELECT active FROM services WHERE id = :id'); $q->execute([':id'=>$id]); $r = $q->fetch(PDO::FETCH_ASSOC);
                if (!$r) { echo json_encode(['success'=>false,'message'=>'Service not found']); exit; }
                $new = $r['active'] ? 0 : 1;
                $upd = $db->prepare('UPDATE services SET active = :a, updated_at = NOW() WHERE id = :id'); $upd->execute([':a'=>$new,':id'=>$id]);
                echo json_encode(['success'=>true,'message'=>'Toggled','active'=>$new]); exit;
            }
        }
    } catch(PDOException $e){ echo json_encode(['success'=>false,'message'=>'Service action failed: '.$e->getMessage()]); exit; }
}
// Leave & Staff management APIs used by admin dashboard
if (in_array($action, ['list_leave_requests','approve_leave_request','reject_leave_request','list_staff','set_staff_on_leave','clear_staff_on_leave','toggle_staff_account'])) {
    try{
        // ensure leave_requests table
        $db->exec("CREATE TABLE IF NOT EXISTS leave_requests (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          user_id INT NOT NULL,
          staff_name VARCHAR(255),
          role VARCHAR(80),
          start_date DATE,
          end_date DATE,
          reason TEXT,
          status VARCHAR(32) DEFAULT 'pending',
          submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          reviewed_by INT DEFAULT NULL,
          reviewed_at DATETIME DEFAULT NULL,
          notes TEXT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // ensure users has admin-friendly flags
        try{ $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS active TINYINT(1) NOT NULL DEFAULT 1"); } catch(Exception $e){ /* some MySQL versions don't support IF NOT EXISTS for ALTER; fallback below */ }
        try{ $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS on_leave TINYINT(1) NOT NULL DEFAULT 0"); } catch(Exception $e){ }
        try{ $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS leave_start DATE DEFAULT NULL"); } catch(Exception $e){ }
        try{ $db->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS leave_until DATE DEFAULT NULL"); } catch(Exception $e){ }

        // older MySQL without IF NOT EXISTS — best-effort add columns when missing
        $cols = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
        $haveActive = in_array('active', $cols);
        $haveOnLeave = in_array('on_leave', $cols);
        $haveLeaveStart = in_array('leave_start', $cols);
        $haveLeaveUntil = in_array('leave_until', $cols);
        if (!$haveActive) { try{ $db->exec("ALTER TABLE users ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1"); }catch(Exception$e){} }
        if (!$haveOnLeave) { try{ $db->exec("ALTER TABLE users ADD COLUMN on_leave TINYINT(1) NOT NULL DEFAULT 0"); }catch(Exception$e){} }
        if (!$haveLeaveStart) { try{ $db->exec("ALTER TABLE users ADD COLUMN leave_start DATE DEFAULT NULL"); }catch(Exception$e){} }
        if (!$haveLeaveUntil) { try{ $db->exec("ALTER TABLE users ADD COLUMN leave_until DATE DEFAULT NULL"); }catch(Exception$e){} }

        // create a test leave request (useful for debugging; remove in production)
        if ($action === 'create_leave_request') {
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $staff_name = trim($_POST['staff_name'] ?? 'Test Staff');
            $role = trim($_POST['role'] ?? 'midwife');
            $start = $_POST['start_date'] ?? null;
            $end = $_POST['end_date'] ?? null;
            $reason = $_POST['reason'] ?? 'Testing leave request';
            try{
                $ins = $db->prepare('INSERT INTO leave_requests (user_id, staff_name, role, start_date, end_date, reason, status, submitted_at) VALUES (:uid,:name,:role,:s,:e,:reason,\'pending\', NOW())');
                $ins->execute([':uid'=>$user_id, ':name'=>$staff_name, ':role'=>$role, ':s'=>($start?:null), ':e'=>($end?:null), ':reason'=>$reason]);
                echo json_encode(['success'=>true,'message'=>'Created','id'=>$db->lastInsertId()]); exit;
            } catch(Exception $e){ echo json_encode(['success'=>false,'message'=>'Create failed: '.$e->getMessage()]); exit; }
        }

        if ($action === 'list_leave_requests') {
            $stmt = $db->prepare('SELECT lr.*, u.username FROM leave_requests lr LEFT JOIN users u ON u.id = lr.user_id ORDER BY lr.submitted_at DESC');
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success'=>true,'requests'=>$rows]); exit;
        }

        if ($action === 'approve_leave_request') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if (!$id) { echo json_encode(['success'=>false,'message'=>'id required']); exit; }
            $db->beginTransaction();
            try{
                $q = $db->prepare('SELECT * FROM leave_requests WHERE id = :id FOR UPDATE'); $q->execute([':id'=>$id]); $r = $q->fetch(PDO::FETCH_ASSOC);
                if (!$r) { $db->rollBack(); echo json_encode(['success'=>false,'message'=>'Request not found']); exit; }
                $upd = $db->prepare('UPDATE leave_requests SET status = :s, reviewed_by = :rb, reviewed_at = NOW() WHERE id = :id');
                $upd->execute([':s'=>'approved', ':rb'=>($_SESSION['user_id'] ?? null), ':id'=>$id]);
                // mark user on leave if user exists and dates present
                if (!empty($r['user_id'])) {
                    try{
                        $st = $db->prepare('UPDATE users SET on_leave = 1, leave_start = :ls, leave_until = :le WHERE id = :uid');
                        $st->execute([':ls'=>($r['start_date']?:null), ':le'=>($r['end_date']?:null), ':uid'=>$r['user_id']]);
                    }catch(Exception $e){ /* non-fatal */ }
                }
                $db->commit();
                echo json_encode(['success'=>true,'message'=>'Approved']); exit;
            } catch(Exception $e){ try{ if($db->inTransaction()) $db->rollBack(); }catch(Exception$ee){} echo json_encode(['success'=>false,'message'=>'Approve failed: '.$e->getMessage()]); exit; }
        }

        if ($action === 'reject_leave_request') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if (!$id) { echo json_encode(['success'=>false,'message'=>'id required']); exit; }
            try{
                $upd = $db->prepare('UPDATE leave_requests SET status = :s, reviewed_by = :rb, reviewed_at = NOW() WHERE id = :id');
                $upd->execute([':s'=>'rejected', ':rb'=>($_SESSION['user_id'] ?? null), ':id'=>$id]);
                echo json_encode(['success'=>true,'message'=>'Rejected']); exit;
            } catch(Exception $e){ echo json_encode(['success'=>false,'message'=>'Reject failed: '.$e->getMessage()]); exit; }
        }

        if ($action === 'list_staff') {
            // return doctors and midwives
            try{
                $stmt = $db->prepare("SELECT u.id, u.username, u.email, u.user_type AS role, COALESCE(p.name, u.username) AS full_name, u.active, u.on_leave, u.leave_until FROM users u LEFT JOIN patient_details p ON p.user_id = u.id WHERE LOWER(u.user_type) IN ('doctor','midwife') ORDER BY u.id DESC");
                $stmt->execute(); $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success'=>true,'staff'=>$rows]); exit;
            } catch(Exception $e){ echo json_encode(['success'=>false,'message'=>'Query failed: '.$e->getMessage()]); exit; }
        }

        if ($action === 'set_staff_on_leave') {
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $start = $_POST['start_date'] ?? null; $end = $_POST['end_date'] ?? null;
            if (!$user_id) { echo json_encode(['success'=>false,'message'=>'user_id required']); exit; }
            try{
                $upd = $db->prepare('UPDATE users SET on_leave = 1, leave_start = :ls, leave_until = :le WHERE id = :uid');
                $upd->execute([':ls'=>($start?:null), ':le'=>($end?:null), ':uid'=>$user_id]);
                echo json_encode(['success'=>true,'message'=>'Staff set on leave']); exit;
            } catch(Exception $e){ echo json_encode(['success'=>false,'message'=>'Set on leave failed: '.$e->getMessage()]); exit; }
        }

        if ($action === 'clear_staff_on_leave') {
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            if (!$user_id) { echo json_encode(['success'=>false,'message'=>'user_id required']); exit; }
            try{
                $upd = $db->prepare('UPDATE users SET on_leave = 0, leave_start = NULL, leave_until = NULL WHERE id = :uid');
                $upd->execute([':uid'=>$user_id]);
                echo json_encode(['success'=>true,'message'=>'Leave cleared']); exit;
            } catch(Exception $e){ echo json_encode(['success'=>false,'message'=>'Clear leave failed: '.$e->getMessage()]); exit; }
        }

        if ($action === 'toggle_staff_account') {
            $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
            $active = isset($_POST['active']) ? (int)$_POST['active'] : null;
            if (!$user_id || $active === null) { echo json_encode(['success'=>false,'message'=>'user_id and active required']); exit; }
            try{
                $upd = $db->prepare('UPDATE users SET active = :a WHERE id = :uid');
                $upd->execute([':a'=>$active, ':uid'=>$user_id]);
                echo json_encode(['success'=>true,'message'=>'Account status updated']); exit;
            } catch(Exception $e){ echo json_encode(['success'=>false,'message'=>'Toggle failed: '.$e->getMessage()]); exit; }
        }

    } catch(Exception $e){ echo json_encode(['success'=>false,'message'=>'Leave/staff action failed: '.$e->getMessage()]); exit; }
}
// Unknown action — return JSON error without setting HTTP 4xx to avoid noisy network errors
$posted = [];
try{ foreach($_POST as $k=>$v) $posted[$k]=$v; } catch(Exception $e){}
$raw = @file_get_contents('php://input');
$headers = function_exists('getallheaders') ? getallheaders() : [];
// include session info (sanitized) to help diagnose auth/session issues
$sessionInfo = ['user_id'=>$_SESSION['user_id'] ?? null, 'user_type'=>$_SESSION['user_type'] ?? null, 'session_active'=>session_status() === PHP_SESSION_ACTIVE];
echo json_encode([
    'success'=>false,
    'message'=>'Unknown action',
    'posted'=>$posted,
    'get'=>$_GET,
    'raw'=>$raw,
    'content_type'=>($_SERVER['CONTENT_TYPE'] ?? ''),
    'method'=>($_SERVER['REQUEST_METHOD'] ?? ''),
    'headers'=>$headers,
    'files'=>array_map(function($f){ return is_array($f)?array_intersect_key($f, array_flip(['name','type','size','error'])):$f; }, $_FILES),
    'session'=>$sessionInfo,
    'request_uri'=>($_SERVER['REQUEST_URI'] ?? '')
]);

?>
