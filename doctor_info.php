<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'doctor') {
        header('Location: login_process.php');
        exit;
}
require_once 'db_connect.php';

$userId = (int)$_SESSION['user_id'];
$doctor = null;

// fetch existing doctor info if any
try {
        $pdo = new PDO('mysql:host=localhost;dbname=drea_db;charset=utf8', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare('SELECT * FROM doctor_info WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
        $doctor = null;
}

$name = htmlspecialchars($doctor['name'] ?? '', ENT_QUOTES);
$specialty = htmlspecialchars($doctor['specialty'] ?? '', ENT_QUOTES);
$phone = htmlspecialchars($doctor['phone'] ?? '', ENT_QUOTES);
$email = htmlspecialchars($doctor['email'] ?? '', ENT_QUOTES);
$clinic_address = htmlspecialchars($doctor['clinic_address'] ?? '', ENT_QUOTES);
$bio = htmlspecialchars($doctor['bio'] ?? '', ENT_QUOTES);
$avatar_url = $doctor['avatar_url'] ?? 'assets/images/logodrea.jpg';
?>
<!doctype html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Doctor Info — DREA</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
        <style>
            :root{ --lav-1:#f6f2fc; --lav-4:#9077d1; --muted:#6b607f; --accent:#9c7de8; --card-bg:#fff; --text-dark:#2b2450 }
            body{ font-family:'Inter',sans-serif;background:var(--lav-1);margin:0;padding:24px; color:var(--text-dark) }
            .center-wrap{ max-width:920px;margin:28px auto }
            .dialog{ background:var(--card-bg); padding:18px;border-radius:12px; box-shadow:0 12px 36px rgba(0,0,0,0.08) }
            header.form-header{ display:flex;justify-content:space-between;align-items:center;margin-bottom:12px }
            header.form-header h3{ margin:0;color:var(--lav-4) }
            .close-btn{ background:#fff;border:1px solid #eee;padding:8px 10px;border-radius:8px;cursor:pointer }
            .form-grid{ display:grid; grid-template-columns: 1fr 1fr; gap:12px }
            label{ display:block;font-weight:600;margin-bottom:6px;color:var(--lav-4) }
            input[type="text"], input[type="email"], textarea{ padding:10px;border-radius:8px;border:1px solid #e6e1f6;background:#fff;width:100%; box-sizing:border-box }
            textarea{ min-height:90px; resize:vertical }
            .avatar-row{ display:flex; gap:18px; align-items:center }
            .avatar-preview{ width:120px; height:120px; border-radius:12px; overflow:hidden; border:2px solid rgba(0,0,0,0.04); background:#fff }
            .avatar-preview img{ width:100%; height:100%; object-fit:cover; display:block }
            .btn-pill{ background:var(--accent); color:#fff; border:none; padding:10px 16px; border-radius:999px; cursor:pointer; font-weight:700 }
            .btn-ghost{ background:#fff;color:var(--lav-4);border:1px solid rgba(0,0,0,0.06) }
            .muted{ color:var(--muted); font-size:0.95rem }
            .form-actions{ display:flex; justify-content:flex-end; gap:10px; margin-top:12px }

            /* Toast (small) */
            .toast{ position:fixed; right:18px; bottom:18px; z-index:99999; display:none }
            .toast .toast-inner{ background:#fff;border-radius:10px;padding:14px 16px;box-shadow:0 8px 24px rgba(0,0,0,0.12); display:flex; align-items:center; gap:12px }
            .toast.success .toast-inner{ border-left:6px solid #28a745 }
            .toast.error .toast-inner{ border-left:6px solid #e11d48 }
            .toast .toast-msg{ flex:1 }
            @media(max-width:860px){ .form-grid{ grid-template-columns: 1fr } }
        </style>
</head>
<body>

<div class="center-wrap">
    <div class="dialog" role="dialog" aria-modal="true">
        <header class="form-header">
            <h3>Doctor Information</h3>
            <div>
                <button class="close-btn" onclick="window.location.href='doctor_portal.php'">Close</button>
            </div>
        </header>

        <form id="doctorForm" enctype="multipart/form-data" method="post">
            <div style="display:flex;flex-direction:column;gap:12px">
                <div class="avatar-row">
                    <div class="avatar-preview" id="avatarPreview"><img id="avatarImg" src="<?= $avatar_url ?>" alt="avatar"></div>
                    <div style="flex:1">
                        <label style="font-weight:700">Profile Photo</label>
                        <div class="muted">Upload a professional photo. Accepted: JPG, PNG, GIF, WEBP. Max 3MB.</div>
                        <div style="margin-top:10px">
                            <label class="btn-pill btn-ghost" style="cursor:pointer">
                                Choose photo
                                <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display:none">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label for="name">Full name</label>
                        <input name="name" id="name" type="text" value="<?= $name ?>">
                    </div>
                    <div>
                        <label for="specialty">Specialty</label>
                        <input name="specialty" id="specialty" type="text" value="<?= $specialty ?>">
                    </div>
                    <div>
                        <label for="phone">Phone</label>
                        <input name="phone" id="phone" type="text" value="<?= $phone ?>">
                    </div>
                    <div>
                        <label for="email">Email</label>
                        <input name="email" id="email" type="email" value="<?= $email ?>">
                    </div>
                    <div style="grid-column:1 / -1">
                        <label for="clinic_address">Clinic Address</label>
                        <textarea id="clinic_address" name="clinic_address"><?= $clinic_address ?></textarea>
                    </div>
                    <div style="grid-column:1 / -1">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio"><?= $bio ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-ghost" onclick="window.location.href='doctor_portal.php'">Cancel</button>
                    <button type="submit" class="btn-pill" id="saveBtn">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Toast container -->
<div id="toast" class="toast" aria-hidden="true">
    <div class="toast-inner">
        <div id="toastMsg" class="toast-msg">Message</div>
        <button id="toastClose" class="close-btn">OK</button>
    </div>
</div>

<script>
// small toast helper (copied from patient UI)
function showToast(message, type='info', timeout=3000){
    const t = document.getElementById('toast');
    const msg = document.getElementById('toastMsg');
    const close = document.getElementById('toastClose');
    if(!t || !msg || !close){ alert(message); return; }
    msg.textContent = message || '';
    t.className = 'toast';
    if(type === 'success') t.classList.add('success');
    if(type === 'error') t.classList.add('error');
    t.style.display = 'block'; t.setAttribute('aria-hidden','false');
    if(t._timer) clearTimeout(t._timer);
    t._timer = setTimeout(()=>{ t.style.display='none'; t.setAttribute('aria-hidden','true'); t._timer = null; }, timeout);
    close.onclick = function(){ if(t._timer) clearTimeout(t._timer); t.style.display='none'; t.setAttribute('aria-hidden','true'); };
}

(function(){
    const avatarInput = document.getElementById('avatarInput');
    const avatarImg = document.getElementById('avatarImg');
    const form = document.getElementById('doctorForm');
    const saveBtn = document.getElementById('saveBtn');
    const MAX_SIZE = 3 * 1024 * 1024; // 3MB

    avatarInput.addEventListener('change', function(){
        const f = this.files && this.files[0]; if(!f) return;
        if(!f.type || !f.type.startsWith('image/')){ showToast('Please choose an image file.', 'error'); this.value=''; return; }
        if(f.size > MAX_SIZE){ showToast('Image too large (max 3MB).', 'error'); this.value=''; return; }
        const reader = new FileReader(); reader.onload = e => { avatarImg.src = e.target.result; }; reader.readAsDataURL(f);
    });

    form.addEventListener('submit', async function(e){
        e.preventDefault();
        if(saveBtn) saveBtn.disabled = true;
        const fd = new FormData(form);
        try{
            showToast('Saving profile...', 'info', 1500);
            const res = await fetch('save_doctor_info.php', { method: 'POST', body: fd, credentials: 'same-origin' });
            const text = await res.text();
            let data = {};
            try{ data = text ? JSON.parse(text) : {}; } catch(err){ console.error('Non-JSON response:', text); showToast('Server returned unexpected response.', 'error'); return; }
            if(!res.ok){ console.error('Server error', res.status, data); showToast('Server error: ' + (data.message || res.status), 'error'); return; }
            if(data.success){
                showToast(data.message || 'Saved', 'success');
                if(data.data && data.data.avatar_url) avatarImg.src = data.data.avatar_url;
                // update sidebar/header via parent page when returning to portal
                setTimeout(()=>{ window.location.href = 'doctor_portal.php'; }, 900);
            } else {
                showToast('Error: ' + (data.message || 'Could not save'), 'error');
            }
        }catch(err){ console.error('Save error', err); showToast('Network error while saving', 'error'); }
        finally{ if(saveBtn) saveBtn.disabled = false; }
    });
})();
</script>
</body>
</html>