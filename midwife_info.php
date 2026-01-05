<?php
session_start();

// Only midwives may view this page
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'midwife') {
  header('Location: login.php');
  exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Midwife Profile — Drea</title>
  <style>
    body{font-family:Inter, Arial, sans-serif;background:#f6f2fc;color:#2b2450;padding:28px}
    .card{max-width:760px;margin:16px auto;background:#fff;padding:20px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.06)}
    .row{display:flex;gap:16px;align-items:center}
    .avatar{width:96px;height:96px;border-radius:50%;object-fit:cover;border:2px solid #fff}
    h1{margin:0 0 6px 0;color:#5f4bb6}
    .muted{color:#6b607f}
    .field{margin-top:12px}
    .label{font-weight:700;color:#6b607f}
    .value{margin-top:6px;padding:10px;border-radius:8px;background:#faf7ff;border:1px solid #f0eafc}
    .actions{text-align:right;margin-top:14px}
    .btn{background:#7c3aed;color:#fff;border:0;padding:8px 14px;border-radius:10px;cursor:pointer}
    .btn.ghost{background:#fff;color:#7c3aed;border:1px solid rgba(124,58,237,0.12)}
  </style>
</head>
<body>
  <div class="card">
    <div class="row">
      <div style="flex:0 0 110px;text-align:center">
        <img id="mwAvatar" class="avatar" src="assets/images/logodrea.jpg" alt="avatar">
      </div>
      <div style="flex:1">
        <h1 id="mwName">Midwife</h1>
        <div id="mwRole" class="muted">Profile</div>
      </div>
      <div style="flex:0 0 auto">
        <div class="actions">
          <a href="midwife_portal.php" class="btn ghost">Back to Dashboard</a>
          <a href="midwife_portal.php" class="btn" style="margin-left:8px">Edit Profile</a>
        </div>
      </div>
    </div>

    <div class="field">
      <div class="label">Specialty</div>
      <div id="mwSpecialty" class="value">—</div>
    </div>
    <div class="field">
      <div class="label">Phone</div>
      <div id="mwPhone" class="value">—</div>
    </div>
    <div class="field">
      <div class="label">Email</div>
      <div id="mwEmail" class="value">—</div>
    </div>
    <div class="field">
      <div class="label">Clinic Address</div>
      <div id="mwAddress" class="value">—</div>
    </div>
    <div class="field">
      <div class="label">Bio</div>
      <div id="mwBio" class="value">—</div>
    </div>

    <script>
      async function loadMidwife(){
        try{
          const res = await fetch('get_midwife_info.php', { credentials: 'same-origin' });
          if(!res.ok){ console.warn('get_midwife_info failed', res.status); return; }
          const j = await res.json(); if(!j || !j.success) return;
          const d = j.data || {};
          document.getElementById('mwAvatar').src = d.avatar_url || 'assets/images/logodrea.jpg';
          document.getElementById('mwName').textContent = d.name || '<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>';
          document.getElementById('mwSpecialty').textContent = d.specialty || '—';
          document.getElementById('mwPhone').textContent = d.phone || '—';
          document.getElementById('mwEmail').textContent = d.email || '—';
          document.getElementById('mwAddress').textContent = d.clinic_address || '—';
          document.getElementById('mwBio').textContent = d.bio || '—';
        }catch(e){ console.error('Failed to load midwife info', e); }
      }
      loadMidwife();
    </script>
  </div>
</body>
</html>
