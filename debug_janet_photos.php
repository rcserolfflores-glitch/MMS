<?php
// Quick debug script to check photoshoots for Janet (user ID 6)
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'db_connect.php';

// Check photoshoots for user ID 6
$userId = 6;
$res = $conn->query("SELECT * FROM photoshoot_uploads WHERE patient_user_id = $userId ORDER BY created_at DESC");

if(!$res) {
  echo json_encode(['error' => 'Table error: ' . $conn->error]);
  exit;
}

$photos = [];
while($row = $res->fetch_assoc()) {
  $photos[] = $row;
}

echo json_encode([
  'success' => true,
  'patient_user_id' => $userId,
  'count' => count($photos),
  'photos' => $photos
], JSON_PRETTY_PRINT);
?>
