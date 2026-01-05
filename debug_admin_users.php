<?php
// Debug script: lists admin users. Run locally only.
require_once 'db_connect.php';
header('Content-Type: text/plain; charset=utf-8');
try{
    $res = $conn->query("SELECT id, username, email, user_type, is_verified, created_at FROM users WHERE user_type = 'admin' ORDER BY id DESC");
    if(!$res){ echo "Query failed: " . $conn->error . PHP_EOL; exit(1); }
    if($res->num_rows === 0){ echo "No admin users found\n"; exit(0); }
    while($row = $res->fetch_assoc()){
        echo "ID: " . $row['id'] . "\n";
        echo "Username: " . ($row['username'] ?? '') . "\n";
        echo "Email: " . ($row['email'] ?? '') . "\n";
        echo "User Type: " . ($row['user_type'] ?? '') . "\n";
        echo "Is Verified: " . ($row['is_verified'] ?? '') . "\n";
        echo "Created: " . ($row['created_at'] ?? '') . "\n";
        echo str_repeat('-', 30) . "\n";
    }
}catch(Exception $e){ echo 'Error: ' . $e->getMessage() . PHP_EOL; }
?>