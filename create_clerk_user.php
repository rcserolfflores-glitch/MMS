<?php
/**
 * One-off script to create a clerk user in the `users` table.
 * Usage (GET or POST): create_clerk_user.php?username=clerk1&password=Secret123!&email=clerk@example.com&name=Clerk+One
 * If username/password not provided the script generates secure values and prints them.
 */
require_once __DIR__ . '/db_connect.php';

function out($s){ echo $s . "\n"; }

$input = (object) array_merge($_GET, $_POST);
$username = isset($input->username) && trim($input->username) !== '' ? trim($input->username) : null;
$password = isset($input->password) && trim($input->password) !== '' ? $input->password : null;
$email = isset($input->email) && trim($input->email) !== '' ? trim($input->email) : null;
$name = isset($input->name) && trim($input->name) !== '' ? trim($input->name) : null;

if (!$username) {
    // generate one
    $username = 'clerk' . rand(100,999);
}
if (!$password) {
    // random secure-ish password
    $password = bin2hex(random_bytes(6)) . 'A!1';
}

// basic validation
if (!preg_match('/^[A-Za-z0-9_\.\-]{3,150}$/', $username)) {
    http_response_code(400);
    echo "Invalid username format. Use letters, numbers, . _ - and 3-150 chars."; exit;
}
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "Invalid email"; exit;
}

// check whether username exists either in users or pending_registrations
$check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
if ($check) {
    $e = $email ?: '';
    $check->bind_param('ss', $username, $e);
    $check->execute();
    $res = $check->get_result();
    if ($res && $res->num_rows > 0) {
        http_response_code(409);
        echo "A user with that username or email already exists."; exit;
    }
    $check->close();
}

// insert into users
$hash = password_hash($password, PASSWORD_DEFAULT);
$ins = $conn->prepare("INSERT INTO users (username, password, user_type, email) VALUES (?, ?, 'clerk', ?)");
if (!$ins) {
    http_response_code(500);
    echo "Failed to prepare insert statement: " . $conn->error; exit;
}
$em = $email ?: '';
$ins->bind_param('sss', $username, $hash, $em);
if (!$ins->execute()) {
    http_response_code(500);
    echo "Insert failed: " . $ins->error; exit;
}
$newId = $ins->insert_id;
$ins->close();

// optional: store clerk's display name in clerk_info table if desired (best-effort)
@ $conn->query("CREATE TABLE IF NOT EXISTS clerk_info (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255),
    avatar_url VARCHAR(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
if ($name) {
    $ci = $conn->prepare("INSERT INTO clerk_info (user_id, name) VALUES (?, ?)");
    if ($ci) { $ci->bind_param('is', $newId, $name); $ci->execute(); $ci->close(); }
}

// make output user-friendly for browser/CLI
header('Content-Type: text/plain; charset=utf-8');
echo "Clerk user created successfully\n";
echo "ID: " . $newId . "\n";
echo "Username: " . $username . "\n";
if ($email) echo "Email: " . $email . "\n";
if ($name) echo "Name: " . $name . "\n";
echo "Password: " . $password . "\n";
echo "\nIMPORTANT: change this password after first login.\n";

$conn->close();
