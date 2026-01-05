<?php
// Always return JSON
header('Content-Type: application/json; charset=utf-8');
// avoid mysqli throwing exceptions that render HTML
mysqli_report(MYSQLI_REPORT_OFF);
session_start();
require_once __DIR__ . '/db_connect.php';

// allow a debug override for local testing: ?test_user_id=123 (only when session missing)
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if(!$user_id && isset($_GET['test_user_id'])){
  $user_id = intval($_GET['test_user_id']);
  @file_put_contents(__DIR__ . '/logs/debug_newborn_screenings.log', date('c') . " USING_TEST_USER_ID: $user_id\n", FILE_APPEND);
}
if(!$user_id){
  http_response_code(401);
  echo json_encode(['success'=>false,'message'=>'Not authenticated']);
  exit;
}

// Select screenings joined to newborns by newborn_id; include s.baby_id (stored on screenings)
// Determine if `newborns` table has a `baby_id` column. Some older DBs may not.
$hasBabyId = false;
try{
  $chk = $conn->query("SHOW COLUMNS FROM newborns LIKE 'baby_id'");
  if($chk && $chk->num_rows > 0) $hasBabyId = true;
}catch(Exception $e){
  // ignore; default to false
}

// detect if `patients` table exists in this schema (some installs don't have it)
$hasPatients = false;
try{
  $chk2 = $conn->query("SHOW TABLES LIKE 'patients'");
  if($chk2 && $chk2->num_rows > 0) $hasPatients = true;
}catch(Exception $e){ /* ignore */ }

// Use a safer subquery approach: select screenings where either the newborn_id belongs
// to this patient or the baby_id matches a newborn owned by this patient. This
// avoids complex JOIN ON clauses that may reference missing columns in older schemas.
if($hasBabyId){
  if($hasPatients){
    $sql = "SELECT s.*, 
      COALESCE((SELECT p.name FROM patients p WHERE p.user_id = s.patient_user_id LIMIT 1), (SELECT n.patient_name FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.patient_name FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS patient_name,
      COALESCE((SELECT n.child_name FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.child_name FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS child_name, 
      COALESCE((SELECT n.date_of_birth FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.date_of_birth FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS date_of_birth\n"
        . "FROM newborn_screenings s\n"
        . "WHERE (s.newborn_id IS NOT NULL AND s.newborn_id IN (SELECT id FROM newborns WHERE patient_user_id = ?))\n"
         . "   OR (s.baby_id IS NOT NULL AND s.baby_id <> '' AND EXISTS (\n"
         . "         SELECT 1 FROM newborns n WHERE n.patient_user_id = ? AND (\n"
         . "           (n.baby_id IS NOT NULL AND n.baby_id <> '' AND s.baby_id COLLATE utf8mb4_general_ci = n.baby_id COLLATE utf8mb4_general_ci)\n"
         . "           OR (s.baby_id COLLATE utf8mb4_general_ci = CAST(n.id AS CHAR) COLLATE utf8mb4_general_ci)\n"
         . "         )\n"
         . "       ))\n"
        . "   OR (s.patient_user_id IS NOT NULL AND s.patient_user_id = ?)\n"
      . "ORDER BY date_of_birth DESC, s.id DESC\n"
      . "LIMIT 200";
  } else {
    $sql = "SELECT s.*, 
      COALESCE((SELECT n.patient_name FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.patient_name FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS patient_name,
      COALESCE((SELECT n.child_name FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.child_name FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS child_name, 
      COALESCE((SELECT n.date_of_birth FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.date_of_birth FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS date_of_birth\n"
        . "FROM newborn_screenings s\n"
        . "WHERE (s.newborn_id IS NOT NULL AND s.newborn_id IN (SELECT id FROM newborns WHERE patient_user_id = ?))\n"
        . "   OR (s.baby_id IS NOT NULL AND s.baby_id <> '' AND EXISTS (\n"
        . "         SELECT 1 FROM newborns n WHERE n.patient_user_id = ? AND (\n"
        . "           (n.baby_id IS NOT NULL AND n.baby_id <> '' AND s.baby_id COLLATE utf8mb4_general_ci = n.baby_id COLLATE utf8mb4_general_ci)\n"
        . "           OR (s.baby_id COLLATE utf8mb4_general_ci = CAST(n.id AS CHAR) COLLATE utf8mb4_general_ci)\n"
        . "         )\n"
        . "       ))\n"
        . "   OR (s.patient_user_id IS NOT NULL AND s.patient_user_id = ?)\n"
      . "ORDER BY date_of_birth DESC, s.id DESC\n"
      . "LIMIT 200";
  }
} else {
  if($hasPatients){
    $sql = "SELECT s.*, 
      COALESCE((SELECT p.name FROM patients p WHERE p.user_id = s.patient_user_id LIMIT 1), (SELECT n.patient_name FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.patient_name FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS patient_name,
      COALESCE((SELECT n.child_name FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.child_name FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS child_name, 
      COALESCE((SELECT n.date_of_birth FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.date_of_birth FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS date_of_birth\n"
        . "FROM newborn_screenings s\n"
        . "WHERE (s.newborn_id IS NOT NULL AND s.newborn_id IN (SELECT id FROM newborns WHERE patient_user_id = ?))\n"
        . "   OR (s.patient_user_id IS NOT NULL AND s.patient_user_id = ?)\n"
      . "ORDER BY date_of_birth DESC, s.id DESC\n"
      . "LIMIT 200";
  } else {
    $sql = "SELECT s.*, 
      COALESCE((SELECT n.patient_name FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.patient_name FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS patient_name,
      COALESCE((SELECT n.child_name FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.child_name FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS child_name, 
      COALESCE((SELECT n.date_of_birth FROM newborns n WHERE n.id = s.newborn_id LIMIT 1), (SELECT n.date_of_birth FROM newborns n WHERE n.patient_user_id = ? ORDER BY n.id DESC LIMIT 1)) AS date_of_birth\n"
        . "FROM newborn_screenings s\n"
        . "WHERE (s.newborn_id IS NOT NULL AND s.newborn_id IN (SELECT id FROM newborns WHERE patient_user_id = ?))\n"
        . "   OR (s.patient_user_id IS NOT NULL AND s.patient_user_id = ?)\n"
      . "ORDER BY date_of_birth DESC, s.id DESC\n"
      . "LIMIT 200";
  }
}
$logDir = __DIR__ . '/logs';
if(!is_dir($logDir)){
  @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/debug_newborn_screenings.log';
@file_put_contents($logFile, date('c') . " BUILD_QUERY hasBabyId=" . ($hasBabyId? '1':'0') . "\n", FILE_APPEND);

// prepare statement and surface DB errors to logs for debugging
$stmt = $conn->prepare($sql);
if(!$stmt){
  $err = $conn->error ?? 'unknown';
  $msg = date('c') . " PREPARE_FAILED: $err\nSQL: " . str_replace("\n"," ", $sql) . "\n";
  @file_put_contents($logFile, $msg, FILE_APPEND);
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'DB prepare error', 'db_error'=> $err]);
  $conn->close();
  exit;
}
// bind parameters depending on which placeholders are present
if($hasBabyId){
  // placeholders (in order): patient_name fallback user_id, child_name fallback user_id, date_of_birth fallback user_id,
  // newborns.patient_user_id (newborn_id IN), newborns.patient_user_id (baby_id IN), s.patient_user_id
  $stmt->bind_param('iiiiii', $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
} else {
  // placeholders: patient_name fallback user_id, child_name fallback user_id, date_of_birth fallback user_id,
  // newborns.patient_user_id (newborn_id IN), s.patient_user_id
  $stmt->bind_param('iiiii', $user_id, $user_id, $user_id, $user_id, $user_id);
}
if(!$stmt->execute()){
  $err = $stmt->error ?? ($conn->error ?? 'unknown');
  $msg = date('c') . " EXECUTE_FAILED: $err\n";
  @file_put_contents($logFile, $msg, FILE_APPEND);
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'DB execute error', 'db_error'=> $err]);
  $stmt->close();
  $conn->close();
  exit;
}
$res = $stmt->get_result();
$rows = [];
while($r = $res->fetch_assoc()){
  $rows[] = $r;
}
$stmt->close();

echo json_encode(['success'=>true,'records'=>$rows]);
$conn->close();
?>
