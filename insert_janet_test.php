<?php
// Debug helper: writes a small placeholder image and inserts a photoshoot_uploads row for patient_user_id = 6
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';

$patientId = '6';
$patientName = 'Janet Example';
$uploadDir = __DIR__ . '/assets/uploads/photoshoots/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

// tiny 1x1 PNG placeholder
$base64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';
$filename = 'test_janet_' . time() . '.png';
$fullpath = $uploadDir . $filename;
$relpath = 'assets/uploads/photoshoots/' . $filename;
file_put_contents($fullpath, base64_decode($base64));

$upload_group = 'ps_test_' . uniqid();
$uploaded_by = 1; // admin/midwife test ID
$orig = 'placeholder.png';
$notes = 'Debug test insert for Janet (user_id=6)';

// ensure patient_name column exists
try{
  $colChk = $conn->query("SHOW COLUMNS FROM photoshoot_uploads LIKE 'patient_name'");
  if(!$colChk || $colChk->num_rows === 0){
    $conn->query("ALTER TABLE photoshoot_uploads ADD COLUMN patient_name VARCHAR(255) DEFAULT NULL");
  }
}catch(Exception $e){ /* ignore */ }

// insert record
try{
  $stmt = $conn->prepare("INSERT INTO photoshoot_uploads (upload_group, patient_user_id, patient_name, uploaded_by, original_filename, stored_filename, path, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  if(!$stmt){
    echo json_encode(['success'=>false,'message'=>'Prepare failed','error'=>$conn->error]); exit;
  }
  $stmt->bind_param('sssissss', $upload_group, $patientId, $patientName, $uploaded_by, $orig, $filename, $relpath, $notes);
  $ok = $stmt->execute();
  if(!$ok){ echo json_encode(['success'=>false,'message'=>'Execute failed','error'=>$stmt->error]); exit; }
  $id = $conn->insert_id;
  echo json_encode(['success'=>true,'id'=>$id,'path'=>$relpath,'message'=>'Inserted test photoshoot for Janet']);
}catch(Exception $e){
  echo json_encode(['success'=>false,'message'=>'Exception','error'=>$e->getMessage()]);
}
?>