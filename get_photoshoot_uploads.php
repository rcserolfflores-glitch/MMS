<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Allow midwives (and doctors/admins) to list uploads for all patients.
// Allow a patient to request their own photos by passing ?patient_user_id=<id>.
$requestedPatient = isset($_GET['patient_user_id']) ? trim((string)$_GET['patient_user_id']) : null;
$userId = isset($_SESSION['user_id']) ? (string)$_SESSION['user_id'] : null;
$userType = isset($_SESSION['user_type']) ? (string)$_SESSION['user_type'] : '';
// If the requester is a patient and didn't include a patient_user_id, assume they want their own photos
if ($userType === 'patient' && empty($requestedPatient) && !empty($userId)) {
  $requestedPatient = $userId;
}

if (!in_array($userType, ['midwife','doctor','admin'])) {
  // only allow patients to fetch their own photos
  if ($userType !== 'patient' || !$requestedPatient || $requestedPatient !== $userId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
  }
}

// Prefer DB-backed list when available
$out = [];
if (file_exists(__DIR__ . '/db_connect.php')){
  try{
    require_once __DIR__ . '/db_connect.php';
    if(isset($conn)){
      // Prepare query; filter by patient_user_id when requested
      if($requestedPatient){
        $stmt = $conn->prepare("SELECT id, upload_group, patient_user_id, uploaded_by, original_filename, stored_filename, path, notes, patient_name, created_at FROM photoshoot_uploads WHERE patient_user_id = ? ORDER BY created_at DESC LIMIT 500");
        if($stmt){ $stmt->bind_param('s', $requestedPatient); $stmt->execute(); $res = $stmt->get_result(); }
      } else {
        $res = $conn->query("SELECT id, upload_group, patient_user_id, uploaded_by, original_filename, stored_filename, path, notes, patient_name, created_at FROM photoshoot_uploads ORDER BY created_at DESC LIMIT 500");
      }
      if($res){
        while($row = $res->fetch_assoc()){
          $out[] = [
            'id' => (int)$row['id'],
            'upload_group' => $row['upload_group'],
            'patient_user_id' => $row['patient_user_id'],
            'uploaded_by' => $row['uploaded_by'],
            'original_filename' => $row['original_filename'],
            'stored_filename' => $row['stored_filename'],
            'url' => $row['path'],
            'path' => $row['path'],
            'notes' => $row['notes'],
            'patient_name' => isset($row['patient_name']) ? $row['patient_name'] : null,
            'created_at' => $row['created_at'],
            'files' => [$row['path']]
          ];
        }
        echo json_encode(['success' => true, 'photos' => $out, 'photoshoot_uploads' => $out]);
        exit();
      }
    }
  }catch(Exception $e){ /* fallback to filesystem */ }
}

// Fallback: filesystem scan
$dir = __DIR__ . '/assets/uploads/photoshoots/';
if (is_dir($dir)) {
  $files = scandir($dir, SCANDIR_SORT_DESCENDING);
  foreach ($files as $f) {
    if ($f === '.' || $f === '..') continue;
    $full = $dir . $f;
    if (!is_file($full)) continue;
    $mtime = filemtime($full);
    $out[] = [
      'filename' => $f,
      'url' => 'assets/uploads/photoshoots/' . $f,
      'created_at' => date('Y-m-d H:i:s', $mtime),
      'files' => ['assets/uploads/photoshoots/' . $f]
    ];
  }
}

echo json_encode(['success' => true, 'photos' => $out, 'photoshoot_uploads' => $out]);
exit();
?>
