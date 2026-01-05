<?php
// Shared mail helper used by admin_api.php and id_verification.php
function send_notification_email($to, $subject, $body) {
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $cfg = [];
    if (file_exists(__DIR__ . '/mail_config.php')) {
        $cfg = include __DIR__ . '/mail_config.php';
    } elseif (file_exists(__DIR__ . '/mail_config.sample.php')) {
        $cfg = include __DIR__ . '/mail_config.sample.php';
    }
    $sent = false;
    // try PHPMailer
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        $havePHPMailer = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
    } elseif (file_exists(__DIR__ . '/vendor/phpmailer/phpmailer/autoload.php')) {
        require_once __DIR__ . '/vendor/phpmailer/phpmailer/autoload.php';
        $havePHPMailer = class_exists('PHPMailer\\PHPMailer\\PHPMailer');
    } else {
        $havePHPMailer = false;
    }
    if ($havePHPMailer) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            // When running from CLI, capture SMTP debug to file for diagnostics
            if (php_sapi_name() === 'cli') {
                $logDir = __DIR__ . '/logs';
                if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
                $debugFile = $logDir . '/smtp_debug.log';
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
            $fromEmail = $cfg['from_email'] ?? ('noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $fromName = $cfg['from_name'] ?? 'Drea Lying-In Clinic';
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            $mail->send();
            $sent = true;
        } catch (Exception $e) {
            @file_put_contents($logDir . '/mail_errors.log', date('[Y-m-d H:i:s] ') . "PHPMailer error: " . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
            $sent = false;
        }
    }
    if (!$sent) {
        $headers = 'From: ' . ($cfg['from_email'] ?? ('noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'))) . "\r\n" . 'Content-Type: text/plain; charset=utf-8';
        $res = @mail($to, $subject, $body, $headers);
        if (!$res) @file_put_contents($logDir . '/mail_errors.log', date('[Y-m-d H:i:s] ') . "mail() failed for: {$to}\n", FILE_APPEND | LOCK_EX);
    }
}
