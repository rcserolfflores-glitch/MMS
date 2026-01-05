<?php
// Simple forgot password request form
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Forgot Password</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    body{font-family:Inter,Arial;margin:0;background:#f6f2fc;display:flex;align-items:center;justify-content:center;height:100vh}
    .box{background:#fff;padding:28px;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.08);width:360px}
    h2{color:#6b4fc9;margin-bottom:8px}
    p{color:#444;margin-bottom:18px}
    input{width:100%;padding:12px;border:1px solid #ccc;border-radius:8px;margin-bottom:12px}
    button{width:100%;padding:12px;border-radius:8px;border:none;background:#a48de7;color:#fff;font-weight:600}
    a{display:block;text-align:center;margin-top:12px;color:#8a70d6}
  </style>
</head>
<body>
  <div class="box">
    <h2>Forgot Password</h2>
    <p>Enter the email address associated with your account. We'll send a link to reset your password.</p>
    <form method="POST" action="forgot_password_process.php">
      <input type="email" name="email" placeholder="Email address" required>
      <button type="submit">Send reset link</button>
    </form>
    <a href="login_process.php">Back to login</a>
  </div>
</body>
</html>
