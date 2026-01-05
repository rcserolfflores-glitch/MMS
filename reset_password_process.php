<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_process.php');
    exit;
}

$token = isset($_POST['token']) ? trim($_POST['token']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

if (empty($token) || empty($password) || empty($password_confirm)) {
    $_SESSION['fp_message'] = 'Please fill all fields.';
    header('Location: login_process.php');
    exit;
}
if ($password !== $password_confirm) {
    $_SESSION['fp_message'] = 'Passwords do not match.';
    header('Location: reset_password.php?token=' . urlencode($token));
    exit;
}
if (strlen($password) < 6) {
    $_SESSION['fp_message'] = 'Password must be at least 6 characters.';
    header('Location: reset_password.php?token=' . urlencode($token));
    exit;
}

// find token
$stmt = $conn->prepare('SELECT pr.id, pr.user_id, pr.expires_at, pr.used FROM password_resets pr WHERE pr.token = ? LIMIT 1');
if (!$stmt) {
    $_SESSION['fp_message'] = 'Invalid request.';
    header('Location: login_process.php');
    exit;
}
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows !== 1) {
    $_SESSION['fp_message'] = 'Invalid or expired link.';
    header('Location: login_process.php');
    exit;
}
$row = $res->fetch_assoc();
if (intval($row['used']) === 1 || strtotime($row['expires_at']) < time()) {
    $_SESSION['fp_message'] = 'This reset link is not valid.';
    header('Location: login_process.php');
    exit;
}
$stmt->close();

// update user password
$hash = password_hash($password, PASSWORD_DEFAULT);
$up = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
if ($up) {
    $up->bind_param('si', $hash, $row['user_id']);
    $up->execute();
    $up->close();
}

// mark token used
$mu = $conn->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
if ($mu) {
    $mu->bind_param('i', $row['id']);
    $mu->execute();
    $mu->close();
}

$_SESSION['fp_message'] = 'Password updated. You can now log in with your new password.';
header('Location: login_process.php');
exit;
