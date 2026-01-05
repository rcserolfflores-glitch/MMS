<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'clerk') {
    header('Location: login.php');
    exit;
}
$_GET['panel'] = 'manage'; $_GET['minimal'] = '1';
require __DIR__ . '/clerk_portal.php';
exit;
