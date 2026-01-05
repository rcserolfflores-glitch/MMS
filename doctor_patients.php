<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'doctor') {
    header('Location: login.php');
    exit;
}
// Render lightweight patients view by including the portal in minimal mode
$_GET['panel'] = 'patients'; $_GET['minimal'] = '1';
require __DIR__ . '/doctor_portal.php';
exit;
