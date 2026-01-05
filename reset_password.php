<?php
session_start();
require_once 'db_connect.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$valid = false;
$error = '';
if (!empty($token)) {
    $stmt = $conn->prepare('SELECT pr.id, pr.user_id, pr.expires_at, pr.used, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (intval($row['used']) === 1) {
                $error = 'This reset link has already been used.';
            } elseif (strtotime($row['expires_at']) < time()) {
                $error = 'This reset link has expired.';
            } else {
                $valid = true;
            }
        } else {
            $error = 'Invalid reset link.';
        }
        $stmt->close();
    } else {
        $error = 'Invalid reset link.';
    }
} else {
    $error = 'Missing token.';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Reset Password</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;background:#f6f2fc;display:flex;align-items:center;justify-content:center;height:100vh} .box{background:#fff;padding:24px;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,.08);width:420px} input{width:100%;padding:12px;margin:8px 0;border-radius:8px;border:1px solid #ccc} button{background:#a48de7;color:#fff;border:none;padding:12px;width:100%;border-radius:8px} .error{color:#b71c1c;margin-bottom:10px}</style>
</head>
<body>
  <div class="box">
    <h2>Reset Password</h2>
    <?php if (!$valid): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <div><a href="forgot_password.php">Request a new reset link</a></div>
    <?php else: ?>
      <form method="POST" action="reset_password_process.php">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <label>New password</label>
        <input type="password" name="password" required minlength="6">
        <label>Confirm password</label>
        <input type="password" name="password_confirm" required minlength="6">
        <button type="submit">Set new password</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
