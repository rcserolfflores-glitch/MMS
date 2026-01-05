<?php
require_once __DIR__ . '/db_connect.php';

$baby_id = isset($_GET['baby_id']) ? trim($_GET['baby_id']) : null;
$newborn_id = isset($_GET['newborn_id']) ? intval($_GET['newborn_id']) : null;

if(!$baby_id && !$newborn_id){
  echo json_encode(['success'=>false,'message'=>'baby_id or newborn_id required']);
  exit;
}

if($baby_id){
  $stmt = $conn->prepare('SELECT * FROM newborn_screenings WHERE baby_id = ? LIMIT 1');
  $stmt->bind_param('s', $baby_id);
} else {
  $stmt = $conn->prepare('SELECT * FROM newborn_screenings WHERE newborn_id = ? LIMIT 1');
  $stmt->bind_param('i', $newborn_id);
}

$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if(!$row){ echo json_encode(['success'=>false,'message'=>'not found']); exit; }

echo json_encode(['success'=>true,'record'=>$row]);

$conn->close();

?>
