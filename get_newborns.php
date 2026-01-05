<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])){
	http_response_code(401);
	echo json_encode(['success' => false, 'message' => 'Not authenticated']);
	exit;
}

try{
	$db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// ensure table exists for compatibility
	$db->exec("CREATE TABLE IF NOT EXISTS newborns (
		id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		patient_user_id INT DEFAULT NULL,
		patient_name VARCHAR(255) DEFAULT NULL,
		child_name VARCHAR(255) DEFAULT NULL,
		gender VARCHAR(16) DEFAULT NULL,
		date_of_birth DATE DEFAULT NULL,
		time_of_birth TIME DEFAULT NULL,
		blood_type VARCHAR(16) DEFAULT NULL,
		weight DECIMAL(6,2) DEFAULT NULL,
		notes TEXT,
		created_by INT DEFAULT NULL,
		created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

	$params = [];
	$sql = 'SELECT * FROM newborns';

	// if logged in patient, restrict to their records
	$role = strtolower($_SESSION['user_type'] ?? '');
	if($role === 'patient'){
		$sql .= ' WHERE patient_user_id = :uid';
		$params[':uid'] = (int)$_SESSION['user_id'];
	} elseif(isset($_GET['patient_user_id']) && $_GET['patient_user_id'] !== ''){
		$sql .= ' WHERE patient_user_id = :uid';
		$params[':uid'] = (int)$_GET['patient_user_id'];
	}

	$sql .= ' ORDER BY date_of_birth DESC, created_at DESC';

	$stmt = $db->prepare($sql);
	$stmt->execute($params);
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$out = [];
	foreach($rows as $r){
		$out[] = [
			'id' => isset($r['id']) ? (int)$r['id'] : null,
			'patient_user_id' => isset($r['patient_user_id']) ? (int)$r['patient_user_id'] : null,
			'patient_name' => $r['patient_name'] ?? null,
			'baby_name' => $r['child_name'] ?? null,
			'child_name' => $r['child_name'] ?? null,
			'gender' => $r['gender'] ?? null,
			'date_of_birth' => $r['date_of_birth'] ?? null,
			'time_of_birth' => $r['time_of_birth'] ?? null,
			'blood_type' => $r['blood_type'] ?? null,
			'weight' => $r['weight'] ?? null,
			'notes' => $r['notes'] ?? null,
			'created_by' => isset($r['created_by']) ? (int)$r['created_by'] : null,
			'created_at' => $r['created_at'] ?? null,
		];
	}

	echo json_encode(['success' => true, 'count' => count($out), 'newborns' => $out]);

} catch(PDOException $e){
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

