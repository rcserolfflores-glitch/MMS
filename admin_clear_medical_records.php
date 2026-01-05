<?php
session_start();
require_once 'db_connect.php';
// Only allow admin users to run this tool by default
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin'){
  http_response_code(403);
  echo "<h3>Forbidden</h3><p>This script may only be run by an administrator. Log in as an admin or run the equivalent DELETE in your database.</p>";
  exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === '1'){
  try{
    $db = db_connect();
    $stmt = $db->prepare('DELETE FROM medical_records');
    $stmt->execute();
    echo "<h3>All medical records cleared</h3><p>The table \`medical_records\` has been emptied.</p><p><a href=\"admin_clear_medical_records.php\">Back</a></p>";
    exit();
  } catch (Exception $e){
    http_response_code(500);
    echo "<h3>Error</h3><pre>".htmlspecialchars($e->getMessage())."</pre>";
    exit();
  }
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Clear Medical Records — Admin</title>
  <style>body{font-family:Inter,system-ui,Arial;padding:28px;color:#222} .btn{background:#c0392b;color:#fff;padding:10px 14px;border-radius:8px;border:0;cursor:pointer} .btn.ghost{background:#fff;color:#333;border:1px solid #ddd}</style>
</head>
<body>
  <h2>Clear All Medical Records</h2>
  <p><strong>WARNING:</strong> This will permanently delete all rows in the <code>medical_records</code> table. Make a database backup before continuing.</p>
  <form method="POST">
    <input type="hidden" name="confirm" value="1">
    <button type="submit" class="btn">Delete all medical records</button>
  </form>
  <p style="margin-top:12px"><a href="patient_portal.php" class="btn ghost">Return to Dashboard</a></p>
</body>
</html>