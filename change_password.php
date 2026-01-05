<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login_process.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Change Password — Drea Clinic</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <style>
    body{font-family:'Inter',sans-serif;background:#f6f2fc;padding:28px}
    .card{max-width:520px;margin:28px auto;background:#fff;border-radius:12px;padding:20px;box-shadow:0 12px 40px rgba(0,0,0,0.06)}
    h2{color:#7c3aed;margin-bottom:6px}
    .muted{color:#6b607f;margin-bottom:12px}
    label{display:block;margin-top:10px;font-weight:600;color:#4b3564}
    input{width:100%;padding:10px;border-radius:8px;border:1px solid #e6e1f6;margin-top:6px}
    .actions{display:flex;gap:8px;justify-content:flex-end;margin-top:14px}
    .btn{background:#7c3aed;color:#fff;border:0;padding:10px 14px;border-radius:8px;cursor:pointer}
    .btn.ghost{background:#fff;color:#7c3aed;border:1px solid #efe6fb}
    .note{font-size:0.9rem;color:#6b607f;margin-top:8px}
  </style>
</head>
<body>
  <div class="card">
    <h2>Change Password</h2>
    <div class="muted">Update your account password. Your current password will be validated.</div>

    <form id="changePasswordForm">
      <label for="current_password">Current password</label>
      <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>

      <label for="new_password">New password</label>
      <input id="new_password" name="new_password" type="password" autocomplete="new-password" required>

      <label for="confirm_password">Confirm new password</label>
      <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required>

      <div class="note">Use at least 8 characters. Avoid reusing passwords from other sites.</div>

      <div class="actions">
        <button type="button" class="btn ghost" onclick="window.location.href='patient_portal.php'">Cancel</button>
        <button type="submit" class="btn">Change password</button>
      </div>
    </form>

    <div id="message" style="margin-top:12px;display:none"></div>
  </div>

<script>
document.getElementById('changePasswordForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const cur = document.getElementById('current_password').value.trim();
  const nw = document.getElementById('new_password').value.trim();
  const conf = document.getElementById('confirm_password').value.trim();
  const msg = document.getElementById('message');
  msg.style.display = 'none';

  if(!cur || !nw || !conf){ msg.style.display='block'; msg.textContent='Please fill all fields.'; msg.style.color='red'; return; }
  if(nw.length < 8){ msg.style.display='block'; msg.textContent='New password must be at least 8 characters.'; msg.style.color='red'; return; }
  if(nw !== conf){ msg.style.display='block'; msg.textContent='New password and confirmation do not match.'; msg.style.color='red'; return; }

  try{
    const fd = new FormData();
    fd.append('current_password', cur);
    fd.append('new_password', nw);

    const res = await fetch('update_password.php', { method: 'POST', body: fd, credentials: 'same-origin' });
    const txt = await res.text();
    let j = {};
    try{ j = txt ? JSON.parse(txt) : {}; } catch(e){ throw new Error('Invalid server response'); }
    if(!res.ok || !j.success){ msg.style.display='block'; msg.textContent = j.message || 'Failed to change password'; msg.style.color='red'; return; }

    msg.style.display='block'; msg.style.color='green'; msg.textContent = j.message || 'Password changed successfully';
    // after short delay redirect back to portal
    setTimeout(()=>{ window.location.href='patient_portal.php'; }, 1200);
  }catch(err){ console.error(err); msg.style.display='block'; msg.style.color='red'; msg.textContent = 'Network or server error'; }
});
</script>
</body>
</html>
