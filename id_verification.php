<?php
require_once 'db_connect.php';

$error = '';
$success = false;
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : (isset($_POST['pid']) ? intval($_POST['pid']) : 0);

// Ensure the id_verification_uploads table exists even before any uploads (create on page load)
$conn->query("CREATE TABLE IF NOT EXISTS id_verification_uploads (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pending_registration_id INT UNSIGNED NOT NULL,
  file_type VARCHAR(64) NOT NULL,
  file_path VARCHAR(512) NOT NULL,
  uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  uploader_ip VARCHAR(45) DEFAULT NULL,
  uploader_agent VARCHAR(255) DEFAULT NULL,
  INDEX (pending_registration_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

if (!$pid) {
  $error = 'Missing verification request.';
}

// handle POST: file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pid && empty($error)) {
    // ensure pending exists
    $stmt = $conn->prepare('SELECT id, status FROM pending_registrations WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows === 0) {
        $error = 'Pending registration not found.';
    } else {
        $row = $res->fetch_assoc();
        // accept uploads
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'ids';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
        $maxBytes = 5 * 1024 * 1024;
        $allowed = ['jpg','jpeg','png','webp','gif','pdf'];

        // Ensure the uploads audit table exists so we can record each file upload
        $conn->query("CREATE TABLE IF NOT EXISTS id_verification_uploads (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          pending_registration_id INT UNSIGNED NOT NULL,
          file_type VARCHAR(64) NOT NULL,
          file_path VARCHAR(512) NOT NULL,
          uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
          uploader_ip VARCHAR(45) DEFAULT NULL,
          uploader_agent VARCHAR(255) DEFAULT NULL,
          INDEX (pending_registration_id),
          CONSTRAINT fk_idv_pending FOREIGN KEY (pending_registration_id) REFERENCES pending_registrations(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $frontPath = null; $backPath = null; $selfiePath = null;
        $map = ['id_front' => 'frontPath', 'id_back' => 'backPath', 'id_selfie' => 'selfiePath'];
        foreach ($map as $field => $varName) {
            if (!empty($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$field];
                if ($file['size'] <= $maxBytes) {
                    $orig = basename($file['name']);
                    $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                    if (in_array($ext, $allowed)) {
                      $safe = time() . '_' . bin2hex(random_bytes(6)) . '_' . $field . '.' . $ext;
                      $dest = $uploadDir . DIRECTORY_SEPARATOR . $safe;
                      if (move_uploaded_file($file['tmp_name'], $dest)) {
                        ${$varName} = 'assets/uploads/ids/' . $safe;
                        // record upload in id_verification_uploads
                        $ins = $conn->prepare('INSERT INTO id_verification_uploads (pending_registration_id, file_type, file_path, uploader_ip, uploader_agent) VALUES (?,?,?,?,?)');
                        if ($ins) {
                          $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
                          $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'],0,250) : null;
                          $ins->bind_param('issss', $pid, $field, ${$varName}, $ip, $ua);
                          @$ins->execute();
                          $ins->close();
                        }
                      }
                    }
                }
            }
        }

        // update pending_registrations
        $up = $conn->prepare('UPDATE pending_registrations SET id_front_path = ?, id_back_path = ?, id_selfie_path = ?, submitted_at = NOW() WHERE id = ?');
        $front = $frontPath; $back = $backPath; $selfie = $selfiePath;
        $up->bind_param('sssi', $front, $back, $selfie, $pid);
        if ($up->execute()) {
          $success = true;
          // notify admin users by email about the new pending verification
          try {
            if (file_exists(__DIR__ . '/mail_functions.php')) require_once __DIR__ . '/mail_functions.php';
            // fetch admin emails
            $recipients = [];
            $aStmt = $conn->prepare("SELECT email FROM users WHERE LOWER(user_type) = 'admin' AND email IS NOT NULL AND email != ''");
            if ($aStmt) {
              $aStmt->execute();
              $ares = $aStmt->get_result();
              if ($ares) {
                while ($ar = $ares->fetch_assoc()) {
                  $ae = trim($ar['email'] ?? '');
                  if ($ae) $recipients[] = $ae;
                }
              }
              $aStmt->close();
            }

            // ensure the clinic admin email is always notified
            $clinicAdmin = 'isiah.gabrielle1211@gmail.com';
            if (!in_array($clinicAdmin, $recipients, true)) $recipients[] = $clinicAdmin;

            // dedupe
            $recipients = array_values(array_unique($recipients));

            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $host . '/admin_dashboard.php?panel=verifications';
            $subject = 'New ID verification submitted';
            $body = "A user has submitted ID verification.\n\nPending ID: {$pid}\nEmail: " . ($row['email'] ?? '') . "\nUsername: " . ($row['username'] ?? '') . "\n\nReview here: {$link}\n";

            foreach ($recipients as $ae) {
              if ($ae) {
                try { send_notification_email($ae, $subject, $body); } catch (Exception $ignore) { @file_put_contents(__DIR__ . '/logs/mail_errors.log', date('[Y-m-d H:i:s] ') . "notify admin send failed to {$ae}: " . $ignore->getMessage() . "\n", FILE_APPEND | LOCK_EX); }
              }
            }
          } catch (Exception $e) {
            // non-fatal — logging
            @file_put_contents(__DIR__ . '/logs/mail_errors.log', date('[Y-m-d H:i:s] ') . "notify admin error: " . $e->getMessage() . "\n", FILE_APPEND | LOCK_EX);
          }
        } else {
          $error = 'Failed to save files.';
        }
        $up->close();
    }
    $stmt->close();
}

// load pending registration for display
$pending = null;
if (empty($error)) {
    $q = $conn->prepare('SELECT * FROM pending_registrations WHERE id = ? LIMIT 1');
    $q->bind_param('i', $pid);
    $q->execute();
    $r = $q->get_result();
    if ($r && $r->num_rows) $pending = $r->fetch_assoc();
    $q->close();
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ID Verification</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<style>
body{font-family:Inter,Arial;background:#f6f2fc;padding:30px}
.card{max-width:480px;margin:20px auto;background:#fff;padding:30px;border-radius:14px;box-shadow:0 14px 40px rgba(164,141,231,0.08)}
h1{font-family:Poppins,Arial;font-size:20px;text-align:center;color:#6b49a8;margin-bottom:6px}
p.lead{color:#666;text-align:center;margin-bottom:12px}
.label{font-weight:700;color:#7a57bf;margin-bottom:6px}
.input-row{margin-bottom:12px}
input[type=file]{display:block}
.btn{display:block;width:100%;padding:14px 16px;border-radius:10px;border:none;background:#a48de7;color:#fff;font-weight:700;cursor:pointer;font-size:16px}
.btn.ghost{display:block;width:100%;background:#f3eefe;color:#6b49a8;border:1px solid #e9e1fb;padding:12px;border-radius:10px;cursor:pointer;margin-top:10px;text-align:center}
.small{font-size:13px;color:#777;text-align:center;margin-top:10px}
.preview-row{display:flex;gap:8px;flex-wrap:wrap}
.preview-row img{width:110px;height:72px;object-fit:cover;border-radius:6px;border:1px solid #f0eafc}
.short-btn{display:inline-block;padding:10px 22px;border-radius:10px;background:#a48de7;color:#fff;font-weight:700;text-decoration:none}
/* override broad .btn rules to keep short-btn compact inside the card */
.btn.short-btn{display:inline-block !important; width:auto !important; min-width:160px; max-width:320px; margin:8px auto 0;}
.btn.short-btn:hover{background:#9077d1}
</style>
</head>
<body>

<div class="card">
  <h1>ID Verification</h1>
  <p class="lead">Please upload a valid ID to complete your signup.</p>
  <?php if(isset($_GET['msg']) && $_GET['msg'] === 'unverified'): ?>
    <div style="color:#d35400;margin:10px 0;text-align:center;font-weight:700">User unverified — please upload your ID to complete verification.</div>
  <?php endif; ?>

  <?php if($error): ?>
  <div style="color:#c0392b;margin:10px 0;text-align:center"><?=htmlspecialchars($error)?></div>
<?php elseif($success): ?>
  <div style="text-align:center;color:#1b5e20;font-weight:700;margin-bottom:8px">Submitted</div>
  <p class="small">Status: Your ID is now under review. This usually takes 5–10 minutes. Your information is kept private and secure.</p>
  <div style="margin-top:12px;text-align:center">
    <a href="login_process.php" class="btn short-btn">Back to Login</a>
  </div>
<?php else: ?>
  <?php if($pending): ?>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="pid" value="<?=htmlspecialchars($pid)?>">
      <div style="margin-bottom:10px"><strong>List of Accepted IDs:</strong>
        <ul style="margin-top:6px;color:#333">
          <li>PhilHealth ID</li>
          <li>UMID</li>
          <li>Driver's License</li>
          <li>National ID</li>
          <li>Passport</li>
        </ul>
      </div>

      <div class="input-row">
        <div class="label">Upload Front ID Photo</div>
        <div class="file-control">
          <label class="file-btn" for="id_front">Choose File</label>
          <span class="file-name" id="fn_id_front">No file chosen</span>
          <input type="file" id="id_front" name="id_front" accept="image/*,application/pdf" style="display:none">
        </div>
      </div>
      <div class="input-row">
        <div class="label">Upload Back ID Photo (optional)</div>
        <div class="file-control">
          <label class="file-btn" for="id_back">Choose File</label>
          <span class="file-name" id="fn_id_back">No file chosen</span>
          <input type="file" id="id_back" name="id_back" accept="image/*,application/pdf" style="display:none">
        </div>
      </div>
      <div class="input-row">
        <div class="label">Upload Selfie with ID</div>
        <div class="file-control">
          <label class="file-btn" for="id_selfie">Choose File</label>
          <span class="file-name" id="fn_id_selfie">No file chosen</span>
          <input type="file" id="id_selfie" name="id_selfie" accept="image/*" style="display:none">
        </div>
      </div>

      <div style="margin-top:12px">
        <button type="submit" class="btn">Submit Verification</button>
      </div>
      <p class="small">Status: Your ID will be under review after submission.</p>
    </form>

    <?php if(!empty($pending['id_front_path']) || !empty($pending['id_back_path']) || !empty($pending['id_selfie_path'])): ?>
      <div style="margin-top:12px">
        <strong>Previously uploaded files</strong>
        <div class="preview-row">
          <?php if(!empty($pending['id_front_path'])): ?><a href="<?=htmlspecialchars($pending['id_front_path'])?>" target="_blank"><img src="<?=htmlspecialchars($pending['id_front_path'])?>" alt="front"></a><?php endif; ?>
          <?php if(!empty($pending['id_back_path'])): ?><a href="<?=htmlspecialchars($pending['id_back_path'])?>" target="_blank"><img src="<?=htmlspecialchars($pending['id_back_path'])?>" alt="back"></a><?php endif; ?>
          <?php if(!empty($pending['id_selfie_path'])): ?><a href="<?=htmlspecialchars($pending['id_selfie_path'])?>" target="_blank"><img src="<?=htmlspecialchars($pending['id_selfie_path'])?>" alt="selfie"></a><?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <p style="text-align:center;color:#666">Verification request not found.</p>
  <?php endif; ?>
<?php endif; ?>

</div>

</body>
</html>

<style>
/* file control styles appended at end to keep near markup */
.file-control{display:flex;gap:10px;align-items:center}
.file-btn{background:#fff;border:1px solid #e8ddff;color:#6b49a8;padding:8px 12px;border-radius:8px;cursor:pointer;font-weight:700}
.file-name{color:#666;font-size:0.95rem;flex:1}
@media(max-width:520px){.file-control{flex-direction:column;align-items:stretch}.file-btn{width:100%}.file-name{text-align:left;padding-top:6px}}
</style>

<script>
// Wire custom file controls to show selected filename
document.addEventListener('DOMContentLoaded', function(){
  function bind(id){
    var input = document.getElementById(id);
    if(!input) return;
    var fn = document.getElementById('fn_' + id);
    input.addEventListener('change', function(){
      var name = 'No file chosen';
      if(this.files && this.files.length) name = this.files[0].name;
      if(fn) fn.textContent = name;
    });
    // initialize if a file path exists (from previous uploads)
    if(fn && fn.textContent && fn.textContent.indexOf('/') !== -1) {
      // try to extract filename from earlier stored path
      var parts = fn.textContent.split('/'); fn.textContent = parts[parts.length-1];
    }
  }
  ['id_front','id_back','id_selfie'].forEach(bind);
  // clicking label will trigger file input automatically via for attribute
});
</script>

<style>
/* short button used on success page */
.short-btn{display:inline-block;padding:10px 22px;border-radius:10px;background:#a48de7;color:#fff;font-weight:700;text-decoration:none}
</style>
