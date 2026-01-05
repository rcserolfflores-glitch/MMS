<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])){
  http_response_code(401);
  echo json_encode(['success'=>false,'message'=>'Not authenticated']);
  exit();
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;
$announcement_id = isset($data['announcement_id']) ? (int)$data['announcement_id'] : 0;
if (!$announcement_id){
  echo json_encode(['success'=>false,'message'=>'announcement_id required']);
  exit();
}

$user_id = (int)$_SESSION['user_id'];

try{
  $db = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8mb4', 'root', '');
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  // ensure table exists (safe to run repeatedly)
  $db->exec("CREATE TABLE IF NOT EXISTS announcement_dismissals (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      announcement_id INT UNSIGNED NOT NULL,
      user_id INT NOT NULL,
      dismissed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY uniq_ann_user (announcement_id, user_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

  // upsert: insert ignore, so repeated dismissals won't error
  $ins = $db->prepare('INSERT IGNORE INTO announcement_dismissals (announcement_id, user_id) VALUES (:aid, :uid)');
  $ins->bindValue(':aid', $announcement_id, PDO::PARAM_INT);
  $ins->bindValue(':uid', $user_id, PDO::PARAM_INT);
  $ok = $ins->execute();
  if(!$ok){ echo json_encode(['success'=>false,'message'=>'DB insert failed']); exit(); }
  echo json_encode(['success'=>true,'announcement_id'=>$announcement_id]);
  exit();
} catch (PDOException $e){
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'DB error','error'=>$e->getMessage()]);
  exit();
}
