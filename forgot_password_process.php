<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot_password.php');
    exit;
}

$email = trim($_POST['email']);
if (empty($email)) {
    $_SESSION['fp_message'] = 'Please provide an email address.';
    header('Location: forgot_password.php');
    exit;
}

// Attempt to find the user by email
$stmt = $conn->prepare('SELECT id, email FROM users WHERE email = ? LIMIT 1');
if ($stmt) {
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = ($res && $res->num_rows === 1) ? $res->fetch_assoc() : null;
    $stmt->close();
} else {
    $user = null;
}

// Always show the same response to avoid user enumeration
// If user exists, create token and send email
if ($user) {
    // Ensure password_resets table exists
    $createSql = "CREATE TABLE IF NOT EXISTS password_resets (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      token VARCHAR(128) NOT NULL,
      expires_at DATETIME NOT NULL,
      used TINYINT(1) NOT NULL DEFAULT 0,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      INDEX (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->query($createSql);

    // generate token
    try {
        $token = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $token = bin2hex(openssl_random_pseudo_bytes(32));
    }
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

    $ins = $conn->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
    if ($ins) {
        $ins->bind_param('iss', $user['id'], $token, $expires);
        $ins->execute();
        $ins->close();
    }

    // build reset link
    $server_port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $server_port == 443 ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $reset_link = $protocol . '://' . $host . $path . '/reset_password.php?token=' . $token;

    $subject = 'Password reset request';
    $message = "We received a request to reset your password. Click the link below (valid 1 hour):\n\n" . $reset_link . "\n\nIf you did not request this, you can ignore this email.";

    // Try to use PHPMailer if installed and configured via mail_config.php
    $sent = false;
    // Prefer composer autoload, but also support manual vendor/phpmailer/phpmailer/autoload.php
    $sent = false;
    $cfg = [];
    if (file_exists(__DIR__ . '/mail_config.php')) {
        $cfg = include __DIR__ . '/mail_config.php';
    } elseif (file_exists(__DIR__ . '/mail_config.sample.php')) {
        $cfg = include __DIR__ . '/mail_config.sample.php';
    }

    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        $havePHPMailer = class_exists('\PHPMailer\\PHPMailer\\PHPMailer');
    } elseif (file_exists(__DIR__ . '/vendor/phpmailer/phpmailer/autoload.php')) {
        require_once __DIR__ . '/vendor/phpmailer/phpmailer/autoload.php';
        $havePHPMailer = class_exists('\PHPMailer\\PHPMailer\\PHPMailer');
    } else {
        $havePHPMailer = false;
    }

    if ($havePHPMailer) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            // Enable verbose SMTP debug when running from CLI for diagnostics
            if (php_sapi_name() === 'cli') {
                $logDir = __DIR__ . '/logs';
                if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                $debugFile = $logDir . '/smtp_debug.log';
                // capture debug output to file
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = function($str, $level) use ($debugFile) {
                    $line = date('[Y-m-d H:i:s] ') . "SMTP[$level]: $str\n";
                    @file_put_contents($debugFile, $line, FILE_APPEND | LOCK_EX);
                };
            }
            if (!empty($cfg['use_smtp'])) {
                $mail->isSMTP();
                $mail->Host = $cfg['smtp_host'] ?? '';
                $mail->Port = $cfg['smtp_port'] ?? 587;
                $mail->SMTPAuth = true;
                $mail->Username = $cfg['smtp_user'] ?? '';
                $mail->Password = $cfg['smtp_pass'] ?? '';
                $secure = $cfg['smtp_secure'] ?? '';
                if (!empty($secure)) $mail->SMTPSecure = $secure;
            }
            $fromEmail = $cfg['from_email'] ?? ('noreply@' . $_SERVER['HTTP_HOST']);
            $fromName = $cfg['from_name'] ?? 'Drea Lying-In Clinic';
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($user['email']);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = $message;
            $mail->send();
            $sent = true;
        } catch (Exception $e) {
            $sent = false;
            $logDir = __DIR__ . '/logs';
            if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
            $msg = date('[Y-m-d H:i:s] ') . "PHPMailer error: " . $e->getMessage() . "\n";
            @file_put_contents($logDir . '/mail_errors.log', $msg, FILE_APPEND | LOCK_EX);
        }
    }

    if (!$sent) {
        $headers = 'From: noreply@' . $_SERVER['HTTP_HOST'] . "\r\n" . 'Content-Type: text/plain; charset=utf-8';
        $result = @mail($user['email'], $subject, $message, $headers);
        if (!$result) {
            $logDir = __DIR__ . '/logs';
            if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
            $msg = date('[Y-m-d H:i:s] ') . "mail() failed for: {$user['email']}\n";
            @file_put_contents($logDir . '/mail_errors.log', $msg, FILE_APPEND | LOCK_EX);
        }
    }
}

// Always redirect to a confirmation page (or back to login) with neutral message
$_SESSION['fp_message'] = 'If an account with that email exists, a password reset link has been sent.';
header('Location: login_process.php');
exit;
