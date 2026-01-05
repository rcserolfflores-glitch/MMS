<?php
session_start();
// support a "minimal" rendering mode: if `?panel=NAME&minimal=1` is present,
// the page will only render the requested panel block (useful for lightweight
// per-section pages that include this file server-side).
$MINIMAL_PANEL = '';
if (!empty($_GET['minimal']) && !empty($_GET['panel'])) {
  $MINIMAL_PANEL = (string)$_GET['panel'];
}

// Only allow logged-in doctors (doctor is considered "staff" role similar to midwife)
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'midwife') {
  header("Location: login.php");
  exit();
}

// Ensure profile info is present in session. If missing, attempt to load from midwife_info table so header/avatar shows correctly.
if (empty($_SESSION['user_avatar']) || empty($_SESSION['user_fullname'])) {
  if (file_exists(__DIR__ . '/db_connect.php')) {
    require_once __DIR__ . '/db_connect.php';
    try {
      $uid = (int)($_SESSION['user_id'] ?? 0);
      if ($uid && isset($conn)) {
        $stmt = $conn->prepare("SELECT name, avatar_url FROM midwife_info WHERE user_id = ? LIMIT 1");
        if ($stmt) {
          $stmt->bind_param('i', $uid);
          $stmt->execute();
          $res = $stmt->get_result();
          if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (!empty($row['avatar_url'])) $_SESSION['user_avatar'] = $row['avatar_url'];
            if (!empty($row['name'])) { $_SESSION['username'] = $row['name']; $_SESSION['user_fullname'] = $row['name']; }
          }
        }
      }
    } catch (Exception $e) {
      // non-fatal
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Midwife Dashboard — Drea Lying-In Clinic</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

<style>
  /* ========== Lavender Theme (same as patient portal) ========== */
  :root{
    --lav-1: #f6f2fc;
    --lav-2: #dcd0f9;
    --lav-3: #a48de7;
    --lav-4: #9077d1;
    --text-dark: #2b2450;
    --muted: #6b607f;
    --card-bg: #fff;
    --accent: #9c7de8;
    --success: #2ecc71;
    --warning: #f1c40f;
    --danger: #e74c3c;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{
    font-family:'Inter',sans-serif;
    background:var(--lav-1);
    color:var(--text-dark);
    min-height:100vh;
    display:flex;
    flex-direction:column;
  }
  a{color:inherit;text-decoration:none}

  /* Header (updated to match homepage gradient) */
  header.site-top{
    background: linear-gradient(90deg,#2b1b4f,#3b2c65);
    padding:12px 20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:1rem;
    box-shadow:0 2px 8px rgba(0,0,0,0.06);
    position:sticky;
    top:0;
    z-index:200;
    border-bottom-left-radius:18px;
    overflow:visible;
    color: #ffffff;
  }
  .header-left{display:flex;align-items:center;gap:1rem}
  .logo img{width:86px;height:86px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,0.12);box-shadow:0 6px 18px rgba(0,0,0,0.08)}
  .clinic-name{font-family:'Poppins',sans-serif;color:#fff;font-weight:700;font-size:1.35rem}
  .clinic-sub{font-size:0.85rem;color:rgba(255,255,255,0.9);margin-top:4px}
  .header-actions{display:flex;align-items:center;gap:0.5rem;color:#fff}
  .btn-pill{
    background:var(--accent);color:#fff;border:none;padding:10px 18px;border-radius:28px;font-weight:600;cursor:pointer;
    box-shadow:0 6px 18px rgba(156,125,232,0.18);
  }
  .btn-pill.ghost{background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.12);box-shadow:none}

  /* Profile dropdown + theme popover (patient header parity) */
  .profile-menu{position:relative}
  .profile-menu .profile-btn{display:inline-flex;align-items:center;gap:8px}
  .profile-avatar{width:30px;height:30px;border-radius:50%;object-fit:cover;margin-right:8px;border:2px solid rgba(255,255,255,0.12)}
  /* Profile dropdown (Facebook-like) */
  .profile-dropdown{
    display:none;position:absolute;right:0;top:48px;min-width:260px;background:#fff;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,0.12);overflow:hidden;z-index:350;border:1px solid rgba(0,0,0,0.06);
  }
  .profile-dropdown .pd-header{display:flex;align-items:center;gap:12px;padding:12px 14px;background:linear-gradient(90deg,var(--lav-1),#fff)}
  .profile-dropdown .pd-header img{width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid rgba(0,0,0,0.04)}
  .profile-dropdown .pd-header .pd-name{font-weight:800;color:var(--text-dark)}
  .profile-dropdown .pd-header .pd-sub{font-size:0.9rem;color:var(--muted)}
  .profile-dropdown .pd-sep{height:1px;background:linear-gradient(90deg, rgba(0,0,0,0.03), rgba(0,0,0,0.01));margin:6px 0}
  .profile-dropdown .pd-group{display:flex;flex-direction:column;padding:6px}
  .profile-dropdown .pd-item{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;color:var(--text-dark);cursor:pointer;font-weight:700;margin:6px 8px}
  .profile-dropdown .pd-item .icon{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;background:linear-gradient(90deg,var(--lav-1),#fff);color:var(--lav-4);font-weight:700}
  .profile-dropdown .pd-item:hover{background:linear-gradient(90deg,rgba(156,125,232,0.04), rgba(156,125,232,0.02))}
  .profile-dropdown .pd-item.logout{color:#c0392b;font-weight:800;margin-top:6px;border-top:1px solid rgba(0,0,0,0.03);padding-top:12px}

  .theme-popover{position:absolute;right:0;top:44px;min-width:160px;background:#fff;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,0.12);overflow:hidden;z-index:250;border:1px solid rgba(0,0,0,0.06)}
  .theme-popover .tp-item{padding:10px 14px;cursor:pointer;display:flex;align-items:center;justify-content:space-between;color:var(--text-dark);font-weight:700}
  .theme-popover .tp-item .label{display:flex;align-items:center;gap:10px}
  .theme-popover .tp-item:hover{background:#fbf8ff}
  .theme-popover .tp-item .check{color:var(--lav-4);display:none}
  .theme-popover .tp-item.active .check{display:inline-block}

  /* Layout */
  .layout{
    display:flex;
    gap:20px;
    padding:28px 34px;
    align-items:flex-start;
    width:100%;
    max-width:1200px;
    margin:18px auto;
    transition: margin-left .18s ease, background .18s ease;
  }

  /* Sidebar (copied visual style from patient portal) */
  /* Sidebar pinned to the very left edge */
  nav.sidebar{
    width:220px;
    background: linear-gradient(180deg, #fff, #fbf8ff);
    border-radius:14px;
    padding:20px;
    padding-bottom:18px;
    box-shadow:0 12px 40px rgba(40,20,80,0.06);
    display:flex;
    flex-direction:column;
    gap:8px;
    /* Span from header to bottom so footer stays visible */
    position:fixed;
    left:0;
    top:110px;
    bottom:20px;
    overflow:auto;
    z-index:60;
  }
  nav.sidebar .nav-item{
    display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:var(--lav-4);font-weight:700;margin-bottom:6px;cursor:pointer;font-size:0.95rem;line-height:1.1;transition:all .12s ease;
  }
  nav.sidebar .nav-item:hover{background:rgba(156,125,232,0.06);transform:translateX(2px)}
  nav.sidebar .nav-item.active{background:linear-gradient(90deg,var(--accent),var(--lav-4));color:#fff;box-shadow:0 8px 20px rgba(156,125,232,0.12)}

  /* footer area inside sidebar with avatar */
  .sidebar-footer{margin-top:auto;padding-top:10px;padding-bottom:6px;border-top:1px solid rgba(156,125,232,0.06);display:flex;align-items:center;gap:10px}
  .sidebar-avatar{width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid rgba(156,125,232,0.06)}
  .sidebar-name{font-weight:700;color:var(--lav-4);font-size:0.95rem}

  /* Adjust main layout so content clears the fixed sidebar */
  .layout.has-fixed-sidebar{ margin-left: 260px; max-width: calc(100% - 260px); }

  /* Content area */
  main.content-area{flex:1;min-height:600px}
  section.panel{
    background:var(--card-bg);border-radius:12px;padding:20px;box-shadow:0 6px 24px rgba(0,0,0,0.04);margin-bottom:20px;
  }
  .panel h2{color:var(--lav-4);margin-bottom:12px;font-family:'Poppins',sans-serif}
  .muted{color:var(--muted);font-size:0.95rem;margin-bottom:14px}

  /* Table */
  table{
    width:100%;border-collapse:collapse;margin-top:10px;font-size:0.95rem;
  }
  th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;vertical-align:middle}
  th{background:#f9f6ff;color:var(--lav-4);font-weight:700}
  tr:hover{background:#f6f2fc}
  td .btn{
    padding:6px 12px;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:0.85rem;
  }
  .btn-approve{background:var(--success);color:#fff}
  .btn-decline{background:#fff;color:var(--danger);border:1px solid rgba(231,76,60,0.14)}
  .btn-view{background:#fff;border:1px solid #eee;color:var(--lav-4)}
  .status-badge{display:inline-block;padding:6px 10px;border-radius:16px;font-weight:700;font-size:0.85rem}
  .status-confirmed{background:rgba(46,204,113,0.12);color:var(--success);border:1px solid rgba(46,204,113,0.16)}
  .status-completed{background:linear-gradient(90deg,#3b82f6,#2563eb);color:#fff;border:1px solid rgba(37,99,235,0.12)}
  .status-pending{background:rgba(241,196,15,0.12);color:var(--warning);border:1px solid rgba(241,196,15,0.16)}
  .status-cancelled{background:rgba(231,76,60,0.08);color:var(--danger);border:1px solid rgba(231,76,60,0.12)}

  /* Highlight rows cancelled by patient so doctors can quickly spot them */
  #panel-manage tbody tr.cancelled-by-patient{ background: rgba(231,76,60,0.03); }
  #panel-manage tbody tr.cancelled-by-patient td{ color: rgba(133,63,63,0.95); }
  #panel-manage tbody tr.cancelled-by-patient .status-badge{ background: rgba(231,76,60,0.12); color: var(--danger); border:1px solid rgba(231,76,60,0.16); }

  /* Small column widths */
  .col-service{min-width:160px;max-width:260px}
  .col-patient{min-width:160px;max-width:260px}
  .col-schedule{min-width:180px;max-width:260px}

  /* Forms */
  .form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
  .form-row{display:flex;flex-direction:column;margin-bottom:8px}
  .form-row label{font-weight:600;margin-bottom:6px;color:var(--muted)}
  .form-row input, .form-row select, .form-row textarea{
    padding:8px;border-radius:8px;border:1px solid #eee;font-size:0.95rem;background:#faf7ff;
  }
  .form-actions{text-align:right;margin-top:8px}


  /* responsive tweaks */
  @media (max-width: 900px) {
    .layout{padding:16px}
    nav.sidebar{display:none}
    table{font-size:0.9rem}
    .form-grid{grid-template-columns:1fr}
  }

  /* Mobile sidebar overlay and hamburger behaviour */
  #hamburgerBtn{ display:none }
  @media (max-width: 900px) {
    #hamburgerBtn{ display:inline-flex }
    /* when opened on mobile, show sidebar as fixed overlay */
    nav.sidebar.mobile-open{ display:flex; position:fixed; left:8px; top:92px; bottom:20px; width:320px; max-width:85%; z-index:1200; box-shadow:0 20px 60px rgba(0,0,0,0.28); transform:none; }
    /* dark overlay behind sidebar */
    #mobileSidebarOverlay{ display:block; position:fixed; inset:0; background:rgba(0,0,0,0.36); z-index:1100 }
    /* ensure layout does not reserve sidebar space on small screens */
    .layout.has-fixed-sidebar{ margin-left: 0; max-width:100%; }
    /* tables should scroll horizontally on small screens */
    .panel table{ display:block; width:100%; overflow-x:auto; -webkit-overflow-scrolling:touch }
    /* reduce header paddings to save space */
    header.site-top{ padding:10px 12px }
  }

  /* Confirm / modal styles (copied from patient_portal.php to ensure profilePicModal looks identical) */
  .confirm-modal{ display:none; position:fixed; inset:0;background:rgba(0,0,0,0.35);align-items:center;justify-content:center;z-index:9999 }
  .confirm-modal .dialog{ background:#fff;border-radius:10px;padding:18px 20px;max-width:420px;width:92%;box-shadow:0 10px 30px rgba(0,0,0,0.15); }
  .confirm-modal header h4{ margin:0 0 8px 0;font-size:1.05rem }
  .confirm-modal .actions{ display:flex;gap:10px;justify-content:flex-end }
  .confirm-modal .btn-cancel{ background:#f0f0f0;border:0;padding:8px 12px;border-radius:8px }
  .confirm-modal .btn-ok{ background:#7c3aed;color:#fff;border:0;padding:8px 12px;border-radius:8px }

  /* Ensure ghost / cancel buttons inside white dialog modals are readable */
  .confirm-modal .btn-pill.ghost,
  .confirm-modal .btn-cancel,
  .dialog .btn-pill.ghost,
  #filePreviewModal .btn-pill.ghost,
  #verifyPaymentModal .btn-pill.ghost,
  #paidPaymentModal .btn-pill.ghost,
  #profilePicModal .profile-pic-close,
  #patientInfoModal .btn-pill.ghost,
  #patientInfoModal .btn-cancel,
  .dialog .btn-cancel {
    color: var(--lav-4) !important;
    border: 1px solid rgba(0,0,0,0.06) !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  /* Fancy selects: improve appearance for patient/baby selectors */
  select.fancy-select{
    -webkit-appearance:none;
    -moz-appearance:none;
    appearance:none;
    background-color:#faf7ff;
    border:1px solid rgba(144,119,209,0.12);
    padding:10px 40px 10px 12px;
    border-radius:10px;
    font-size:0.95rem;
    color:var(--text-dark);
    box-shadow:0 6px 18px rgba(156,125,232,0.06);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5z' fill='%239077d1'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 12px center;
    background-size:16px 16px;
  }
  select.fancy-select:focus{ outline:2px solid rgba(156,125,232,0.16); outline-offset:2px; }
  /* ensure small selects keep good spacing */
  select.fancy-select.small{ padding:6px 34px 6px 10px;font-size:0.92rem;border-radius:8px }
  /* label style companion to make the grouping look refined */
  .form-row label{ font-weight:700;color:var(--muted);display:block;margin-bottom:6px }
</style>
</head>
<body>

  <!-- HEADER -->
  <header class="site-top">
    <div class="header-left">
      <div class="logo">
        <img src="assets/images/logodrea.jpg" alt="Clinic Logo">
      </div>
      <div>
        <div class="clinic-name">Drea Lying-In Clinic</div>
        <div class="clinic-sub">Midwife Dashboard</div>
      </div>
    </div>
      <div class="brand-right" style="display:flex;align-items:center;gap:12px">
        <button id="hamburgerBtn" class="btn-pill ghost" style="display:none;align-items:center;justify-content:center;padding:8px 10px;margin-right:6px" aria-label="Toggle navigation">☰</button>
        <div class="profile-menu">
          <button id="profileBtn" class="profile-btn btn-pill" type="button">
                  <img src="<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'assets/images/logodrea.jpg') ?>" class="profile-avatar" alt="avatar"/>
                  <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Profile'); ?></span>
                </button>
          <div id="profileDropdown" class="profile-dropdown" aria-hidden="true">
            <div class="pd-header">
                  <img src="<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'assets/images/logodrea.jpg') ?>" id="hdrAvatarSmall" alt="avatar">
              <div class="pd-meta">
                <div class="pd-name" id="hdrName"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Profile'); ?></div>
                <div class="pd-email" id="hdrEmail"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
              </div>
            </div>
            <div class="pd-sep"></div>

            <div class="pd-group">
              <button class="pd-item primary" type="button" onclick="toggleProfileDropdown(false); openDoctorCustomizeModal();">
                <span class="icon-badge">⚙️</span>
                <span class="pd-label">Customize Profile</span>
              </button>

              <button class="pd-item" type="button" onclick="toggleProfileDropdown(false); openDoctorCustomizeModal();">
                <span class="icon-badge">🧑‍⚕️</span>
                <span class="pd-label">Midwife Info</span>
              </button>
            </div>

            <div class="pd-sep"></div>

            <div class="pd-group">
              <form action="logout.php" method="POST" style="margin:0">
                <button type="submit" class="pd-item logout" style="width:100%;text-align:left;border:none;background:transparent;cursor:pointer;">
                  <span class="icon-badge">⎋</span>
                  <span class="pd-label" style="color:#c0392b">Log Out</span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

    <script>
      // Toggle and close handlers for profile dropdown (copied behavior from patient_portal)
      function toggleProfileDropdown(force){
        const dd = document.getElementById('profileDropdown');
        const btn = document.getElementById('profileBtn');
        if(!dd) return;
        if(typeof force === 'boolean'){
          if(force){
            dd.style.display = 'block';
            dd.setAttribute('aria-hidden','false');
          } else {
            // if a focused element is inside the dropdown, move focus to the profile button
            try{
              const active = document.activeElement;
              if(active && dd.contains(active) && btn) btn.focus();
            }catch(e){}
            dd.style.display = 'none';
            dd.setAttribute('aria-hidden','true');
          }
          return;
        }
        const isOpen = dd.style.display === 'block';
        if(isOpen){
          try{ const active = document.activeElement; if(active && dd.contains(active) && btn) btn.focus(); }catch(e){}
          dd.style.display = 'none'; dd.setAttribute('aria-hidden','true');
        } else {
          dd.style.display = 'block'; dd.setAttribute('aria-hidden','false');
        }
      }

      // Close dropdown when clicking outside
      document.addEventListener('click', function(e){
        const btn = document.getElementById('profileBtn');
        const dd = document.getElementById('profileDropdown');
        if(!dd) return;
        if(btn && btn.contains(e.target)) return;
        if(dd.contains(e.target)) return;
        dd.style.display = 'none'; dd.setAttribute('aria-hidden','true');
      });

      // Prevent clicks on the profile button from closing the dropdown
      document.getElementById('profileBtn')?.addEventListener('click', function(e){ e.stopPropagation(); toggleProfileDropdown(); });

      // Ensure header/sidebar reflect persisted localStorage values (avatar/name)
      document.addEventListener('DOMContentLoaded', function(){
        try{
          const saved = localStorage.getItem('user_avatar');
          const savedName = localStorage.getItem('user_fullname');
          if(saved && saved.length){ const headerAvatar = document.querySelector('.profile-menu .profile-btn img.profile-avatar'); if(headerAvatar) headerAvatar.src = saved; const hdr = document.getElementById('hdrAvatarSmall'); if(hdr) hdr.src = saved; const sa = document.getElementById('sidebarAvatar'); if(sa) sa.src = saved; }
          if(savedName){ const headerName = document.querySelector('.profile-menu .profile-btn span'); if(headerName) headerName.textContent = savedName; const hn = document.getElementById('hdrName'); if(hn) hn.textContent = savedName; const sn = document.getElementById('sidebarName'); if(sn) sn.textContent = savedName; }
        }catch(e){}
      });

      // Simple profile edit modal (opens when clicking 'Customize Profile')
      function openFormModal(mode){
        let modal = document.getElementById('formModal');
        if(!modal){
          // create a simple modal if not present
          modal = document.createElement('div');
          modal.id = 'formModal'; modal.className = 'modal'; modal.style.display = 'none'; modal.setAttribute('aria-hidden','true');
          modal.innerHTML = `
            <div class="dialog" role="dialog" aria-modal="true" style="max-width:640px;width:92%">
              <header style="display:flex;justify-content:space-between;align-items:center">
                <h4 style="margin:0;color:var(--lav-4)">Edit Profile</h4>
                <button class="close-btn btn-cancel" onclick="closeFormModal()">Close</button>
              </header>
              <form id="profileForm" style="margin-top:12px">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                  <div>
                    <label style="font-weight:700;color:var(--muted)">Full name</label>
                    <input id="pf_name" name="name" type="text" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" style="padding:8px;border-radius:8px;border:1px solid #eee;width:100%">
                  </div>
                  <div>
                    <label style="font-weight:700;color:var(--muted)">Email</label>
                    <input id="pf_email" name="email" type="email" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" style="padding:8px;border-radius:8px;border:1px solid #eee;width:100%">
                  </div>
                  <div>
                    <label style="font-weight:700;color:var(--muted)">Mobile</label>
                    <input id="pf_mobile" name="mobile" type="text" value="<?php echo htmlspecialchars($_SESSION['user_mobile'] ?? ''); ?>" style="padding:8px;border-radius:8px;border:1px solid #eee;width:100%">
                  </div>
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:12px;gap:8px">
                  <button type="button" class="btn-cancel" onclick="closeFormModal()">Cancel</button>
                  <button type="submit" class="btn-pill">Save</button>
                </div>
              </form>
            </div>`;
          document.body.appendChild(modal);

          // wire submit
          modal.querySelector('#profileForm').addEventListener('submit', async function(e){
            e.preventDefault();
            const data = Object.fromEntries(new FormData(this).entries());
            try{
              const res = await fetch('save_patient_details.php', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
              const txt = await res.text(); let j = null; try{ j = txt ? JSON.parse(txt) : null; }catch(e){ /* ignore parse */ }
              if(res.ok && j && j.success){ showToast(j.message || 'Profile updated', 'success'); closeFormModal(); location.reload(); }
              else { showToast((j && j.message) ? j.message : 'Failed to save profile', 'error'); }
            }catch(err){ console.error(err); showToast('Network error while saving profile', 'error'); }
          });
        }
        modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
      }

      function closeFormModal(){ const m = document.getElementById('formModal'); if(m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); } }
    </script>
  </header>

  <!-- MAIN LAYOUT -->
  <?php $layoutClass = $MINIMAL_PANEL ? 'layout' : 'layout has-fixed-sidebar'; ?>
  <div class="<?= $layoutClass ?>">
    <?php if (!$MINIMAL_PANEL): ?>
    <nav class="sidebar" aria-label="Main navigation">
    <div class="nav-item active" data-panel="patients">Patients</div>
    <div class="nav-item" data-panel="manage">View Appointments</div>
      <div class="nav-item" data-panel="newborn">Newborn Records</div>
      <div class="nav-item" data-panel="medical">Medical Records</div>
      <div class="nav-item" data-panel="prescriptions">Prescriptions</div>
      <div class="nav-item" data-panel="lab">Results Upload</div>
      <div class="nav-item" data-panel="photoshoot">Photoshoot Upload</div>
      
      <div class="sidebar-footer" id="sidebarFooter">
      <div class="sidebar-avatar-wrapper" style="position:relative;display:flex;align-items:center;gap:10px">
        <div style="position:relative;display:inline-block">
          <img src="<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'assets/images/logodrea.jpg') ?>" alt="avatar" class="sidebar-avatar" id="sidebarAvatar" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid rgba(0,0,0,0.06)">
          <!-- Camera overlay (small circle) -->
          <button type="button" id="sidebarAvatarBtn" title="Change profile photo" style="position:absolute;right:-4px;bottom:-4px;width:28px;height:28px;border-radius:50%;border:1px solid rgba(0,0,0,0.06);background:#fff;color:var(--lav-4);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(0,0,0,0.08);cursor:pointer;padding:3px">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 7h-3.17l-1.84-2.46A2 2 0 0 0 14.41 4H9.59a2 2 0 0 0-1.58.54L6.17 7H3a1 1 0 0 0-1 1v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1zm-9 11a5 5 0 1 1 0-10 5 5 0 0 1 0 10z" fill="var(--lav-4)"/></svg>
          </button>
          <input type="file" id="sidebarAvatarInput" accept="image/*" style="display:none">

          <!-- Small dropdown menu like Facebook edit (See / Choose) -->
          <div id="sidebarAvatarMenu" style="display:none;position:absolute;right:0;bottom:-8px;transform:translateY(100%);background:#fff;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,0.12);min-width:180px;overflow:hidden;font-size:0.95rem">
            <button type="button" id="avatarMenuView" style="display:flex;align-items:center;gap:8px;padding:10px 12px;width:100%;border:0;background:transparent;text-align:left;cursor:pointer">🔍 See profile picture</button>
            <div style="height:1px;background:rgba(0,0,0,0.04)"></div>
            <button type="button" id="avatarMenuChoose" style="display:flex;align-items:center;gap:8px;padding:10px 12px;width:100%;border:0;background:transparent;text-align:left;cursor:pointer">📷 Choose profile picture</button>
          </div>
        </div>

        <div>
          <!-- Sidebar name removed for privacy -->
        </div>
      </div>
      </div>
      </nav>
    <?php endif; ?>

    <main class="content-area">
      <?php if ($MINIMAL_PANEL): ?>
        <div style="margin:12px 0 18px 0">
          <a href="doctor_portal.php" class="btn-pill ghost" style="text-decoration:none;display:inline-block">← Back to Dashboard</a>
        </div>
      <?php endif; ?>

      <!-- PHOTOSHOOT UPLOAD PANEL -->
      <?php if(!$MINIMAL_PANEL || $MINIMAL_PANEL === 'photoshoot'): ?>

      <section id="panel-photoshoot" class="panel" hidden>
        <h2>Photoshoot Upload</h2>
        <p class="muted">Upload photos from patient photoshoots and associate them with a patient account.</p>

        <div style="margin-bottom:12px;display:flex;align-items:center;gap:12px">
          <button class="btn-pill" id="addPhotoshootBtn">Add Photoshoot</button>
        </div>

        <div id="photoshootUploadForm" style="margin-bottom:12px;padding:12px;border-radius:8px;background:#fbf8ff;border:1px solid #f0eaff;display:none">
          <form id="formPhotoshoot" enctype="multipart/form-data">
            <div style="margin-bottom:8px">
              <label style="font-weight:600;color:var(--muted);display:block;margin-bottom:6px">Select patient</label>
              <select id="photoshootPatientSelect" class="fancy-select" style="width:100%">
                <option value="">-- choose patient --</option>
              </select>
            </div>
            <input type="hidden" name="patient_user_id" id="photoshoot_patient_user_id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:center">
              <div>
                <label style="font-weight:600;color:var(--muted);display:block;margin-bottom:6px">Files (jpg/png)</label>
                <div style="display:flex;align-items:center;gap:8px">
                  <label id="photoshootFileBtn" class="btn" style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer">
                    <span style="font-weight:600">Choose Files</span>
                    <input type="file" name="files[]" id="photoshootFiles" accept="image/*" multiple style="display:none">
                  </label>
                  <div id="photoshootFileName" style="color:var(--muted);font-size:0.95rem">No files chosen</div>
                </div>
              </div>
              <div>
                <label style="font-weight:600;color:var(--muted);display:block;margin-bottom:6px">Notes</label>
                <textarea name="notes" id="photoshootNotes" placeholder="e.g. newborn photoshoot session" style="width:100%;padding:8px;border-radius:6px;border:1px solid #e0d5f5;font-size:0.95rem;min-height:64px"></textarea>
              </div>
            </div>
            <div style="text-align:right;margin-top:10px">
              <button type="button" class="btn-pill" id="uploadPhotoshootBtn">Upload Photos</button>
              <button type="button" class="btn-pill ghost" id="cancelPhotoshootUploadBtn" style="margin-left:8px">Cancel</button>
            </div>
            <div id="photoshootUploadProgressWrap" style="display:none;margin-top:8px">
              <div style="height:8px;background:#eee;border-radius:6px;overflow:hidden">
                <div id="photoshootUploadProgressBar" style="width:0%;height:100%;background:linear-gradient(90deg,#8e7bff,#d6b8ff);"></div>
              </div>
              <div id="photoshootUploadProgressText" style="font-size:0.85rem;color:var(--muted);margin-top:6px">Preparing upload…</div>
            </div>
          </form>
        </div>
        <script>
        // Show upload form only when Add Photoshoot button is clicked
        document.getElementById('addPhotoshootBtn')?.addEventListener('click', function() {
          document.getElementById('photoshootUploadForm').style.display = 'block';
          loadPhotoshootPatients();
        });
        // Hide upload form when Cancel is clicked
        document.getElementById('cancelPhotoshootUploadBtn')?.addEventListener('click', function() {
          document.getElementById('photoshootUploadForm').style.display = 'none';
        });
        </script>

        <div style="margin-top:12px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <h4 style="margin:0;color:var(--lav-4)">Uploaded Photos</h4>
            <div>
              <button id="btnRefreshPhotoshootList" type="button" title="Refresh list" aria-label="Refresh photoshoot list" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;background:#f6fbf7;border:1px solid #e6f3ea;color:#2e7d32;box-shadow:none;padding:0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                  <path d="M21 12a9 9 0 1 0-2.34 5.86" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M21 3v6h-6" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
            </div>
          </div>

          <table id="photoshootTable">
            <thead>
              <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Notes</th>
                <th>Files</th>
              </tr>
            </thead>
            <tbody id="photoshootTbody">
              <tr><td colspan="4" style="color:var(--muted);padding:20px;text-align:center">No photos uploaded yet.</td></tr>
            </tbody>
          </table>
        </div>

        <script>
        async function loadPhotoshootPatients(){
          const sel = document.getElementById('photoshootPatientSelect');
          if(!sel) return;
          sel.innerHTML = '<option value="">-- choose patient --</option>';
          try{
            const res = await fetch('get_patients.php', { credentials: 'same-origin' });
            if(!res.ok) return;
            const j = await res.json();
            const rows = j && (j.patients || j.data || j.results) || [];
            if(!Array.isArray(rows)) return;
            // populate select and also update global patients map for name resolution
            try{ window.patientsMap = window.patientsMap || {}; }catch(e){ /* ignore */ }
            rows.forEach(r=>{
              const id = r.user_id || r.id || r.patient_user_id || r.patient_id || '';
              const label = (r.name || r.patient_name || (r.first_name ? (r.first_name + (r.last_name?(' ' + r.last_name):'')) : '')) || ('#' + id);
              const opt = document.createElement('option'); opt.value = id; opt.textContent = label;
              sel.appendChild(opt);
              try{ if(window.patientsMap) window.patientsMap[String(id)] = r; }catch(e){ }
            });
          }catch(err){ console.error('loadPhotoshootPatients', err); }
        }

        async function uploadPhotoshoot(){
          const patientId = document.getElementById('photoshootPatientSelect')?.value || '';
          if(!patientId){
            showToast('Please choose a patient first', 'error');
            // highlight the select box for feedback
            const sel = document.getElementById('photoshootPatientSelect');
            if(sel) {
              sel.style.border = '2px solid #e74c3c';
              sel.focus();
              setTimeout(()=>{ sel.style.border = ''; }, 1800);
            }
            return;
          }
          const input = document.getElementById('photoshootFiles');
          if(!input || !input.files || input.files.length === 0){ showToast('Please choose one or more image files', 'error'); return; }
          const btn = document.getElementById('uploadPhotoshootBtn');
          btn.disabled = true;
          const progressWrap = document.getElementById('photoshootUploadProgressWrap');
          const progressBar = document.getElementById('photoshootUploadProgressBar');
          const progressText = document.getElementById('photoshootUploadProgressText');
          try{
            progressWrap.style.display = 'block'; progressBar.style.width = '2%'; progressText.textContent = 'Uploading...';
            const fd = new FormData();
            fd.append('patient_user_id', patientId);
            fd.append('notes', document.getElementById('photoshootNotes')?.value || '');
            for(let i=0;i<input.files.length;i++){ fd.append('files[]', input.files[i]); }

            const xhr = new XMLHttpRequest();
            xhr.open('POST','save_photoshoot_upload.php', true);
            xhr.withCredentials = true;
            xhr.upload.onprogress = function(e){ if(e.lengthComputable){ const pct = Math.round((e.loaded / e.total) * 100); progressBar.style.width = pct + '%'; progressText.textContent = 'Uploading: ' + pct + '%'; } };
            xhr.onreadystatechange = function(){ if(xhr.readyState === 4){ try{ const j = JSON.parse(xhr.responseText || '{}'); if(xhr.status === 200 && j && j.success){ showToast('Photos uploaded', 'success'); loadPhotoshootList(); } else { showToast((j && j.message) ? j.message : 'Upload failed', 'error'); } }catch(e){ console.error(e); showToast('Upload failed (invalid server response)', 'error'); } finally { progressWrap.style.display='none'; progressBar.style.width='0%'; btn.disabled=false; input.value=''; document.getElementById('photoshootFileName').textContent = 'No files chosen'; } } };
            xhr.send(fd);
          }catch(err){ console.error(err); showToast('Upload failed', 'error'); progressWrap.style.display='none'; btn.disabled=false; }
        }

        async function loadPhotoshootList(){
          const tbody = document.getElementById('photoshootTbody'); if(!tbody) return;
          tbody.innerHTML = `<tr><td colspan="4" style="color:var(--muted);padding:20px;text-align:center">Loading…</td></tr>`;
          try{
            const res = await fetch('get_photoshoot_uploads.php', { credentials: 'same-origin' });
            if(!res.ok){ tbody.innerHTML = `<tr><td colspan="4" style="color:var(--muted);padding:20px;text-align:center">Failed to load.</td></tr>`; return; }
            const j = await res.json().catch(()=>({}));
            const rows = j && (j.photos || j.results || j.data) || [];
            if(!Array.isArray(rows) || rows.length === 0){ tbody.innerHTML = `<tr><td colspan="4" style="color:var(--muted);padding:20px;text-align:center">No photos uploaded yet.</td></tr>`; return; }
            rows.sort((a,b)=> (b.created_at || b.uploaded_at || '') .localeCompare(a.created_at || a.uploaded_at || ''));
            tbody.innerHTML = rows.map(r=>{
              const dt = escapeHtml(formatDateTimeToDisplay(r.created_at || r.uploaded_at || r.date || ''));
              // prefer explicit patient name when provided; otherwise resolve patient_user_id via patientsMap
              let patientName = '';
              try{
                if(r.patient_user_id){
                  const pid = String(r.patient_user_id);
                  if(window.patientsMap && patientsMap[pid]){
                    patientName = patientsMap[pid].name || patientsMap[pid].patient_name || '';
                  }
                }
              }catch(e){ /* ignore */ }
              if(!patientName) patientName = r.patient_name || r.name || '';
              const patient = escapeHtml(patientName);
              const notes = escapeHtml(r.notes || '');
              const files = (r.files || r.filenames || r.urls || []).map(f=>{
                const href = f && !/^https?:\/\//i.test(f) ? (window.location.pathname.replace(/\/[^\/]*$/, '/') + f.replace(/^\/+/, '')) : f;
                return `<button class="btn photoshoot-view" data-url="${escapeHtml(href)}" data-fname="${escapeHtml(f)}">View</button>`;
              }).join(' ');
              return `<tr><td style="white-space:nowrap">${dt}</td><td>${patient}</td><td style="min-width:300px">${notes}</td><td>${files}</td></tr>`;
            }).join('');
          }catch(err){ console.error(err); tbody.innerHTML = `<tr><td colspan="4" style="color:var(--muted);padding:20px;text-align:center">Failed to load.</td></tr>`; }
        }

        // Delegate clicks on photoshoot view buttons to the shared file preview modal (or open in new tab)
        document.addEventListener('click', function(e){
          const btn = e.target.closest && e.target.closest('.photoshoot-view');
          if(!btn) return;
          e.preventDefault();
          const url = btn.dataset.url || btn.getAttribute('data-url');
          const fname = btn.dataset.fname || btn.getAttribute('data-fname') || '';
          try{
            if(typeof viewMedicalFile === 'function'){
              viewMedicalFile(url, fname);
              return;
            }
          }catch(e){}
          // fallback
          window.open(url, '_blank');
        });

        document.getElementById('btnRefreshPhotoshootPatients')?.addEventListener('click', ()=> loadPhotoshootPatients());
        document.getElementById('btnRefreshPhotoshootList')?.addEventListener('click', ()=> loadPhotoshootList());
        document.getElementById('uploadPhotoshootBtn')?.addEventListener('click', ()=> uploadPhotoshoot());
        document.getElementById('photoshootFiles')?.addEventListener('change', function(){ const fn = Array.from(this.files).map(f=>f.name).join(', '); document.getElementById('photoshootFileName').textContent = fn || 'No files chosen'; });

        // initial load
        setTimeout(()=>{ try{ loadPhotoshootPatients(); loadPhotoshootList(); }catch(e){} }, 600);
        </script>
      </section>
      <?php endif; ?>

      <!-- OVERVIEW PANEL removed from default navigation. Kept in code if needed later. -->

      <!-- PATIENTS PANEL -->
      <?php if(!$MINIMAL_PANEL || $MINIMAL_PANEL === 'patients'): ?>
      <section id="panel-patients" class="panel" hidden>
        <h2>Patients</h2>
        <p class="muted">List of registered patients. Click "View patient details" to see that patient's bookings and information.</p>

          <div style="display:flex;justify-content:flex-end;align-items:center;gap:8px;margin:10px 0 14px 0">
            <input id="patientSearch" type="search" placeholder="Search patients by name, phone, email or address" style="padding:8px;border-radius:8px;border:1px solid #eee;background:#faf7ff;font-size:0.95rem;min-width:220px" />
            <button id="patientSearchClear" class="btn-pill ghost" type="button">Clear</button>
          </div>

          <table id="patientsTable">
          <thead>
            <tr>
              <th>Name</th>
              <th>Age</th>
              <th>Mobile</th>
              <th>Email</th>
              <th>Address</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Loading patients…</td></tr>
          </tbody>
        </table>
        
        
      </section>
      <?php endif; ?>

      <!-- Profile picture viewer modal -->
      <div id="profilePicModal" class="confirm-modal" aria-hidden="true" style="display:none">
        <div class="dialog" role="dialog" aria-modal="true" style="max-width:640px;width:92%">
          <header style="display:flex;justify-content:space-between;align-items:center">
            <h4 style="margin:0;color:var(--lav-4)">Profile Picture</h4>
            <button class="profile-pic-close btn-cancel" onclick="(function(){const m=document.getElementById('profilePicModal'); if(m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); const img=document.getElementById('profilePicImg'); if(img) img.src=''; } })()">Close</button>
          </header>
          <div style="margin-top:12px;text-align:center">
            <img id="profilePicImg" src="" alt="Profile Picture" style="max-width:100%;max-height:80vh;border-radius:8px;display:inline-block">
          </div>
        </div>
      </div>

      <!-- Toast container -->
      <div id="toast" class="toast" aria-hidden="true" style="display:none">
        <div class="toast-inner">
          <div id="toastMsg" class="toast-msg">Message</div>
          <button id="toastClose" class="toast-close">OK</button>
        </div>
      </div>

      <script>
      // Toast helper copied from patient_portal for consistent UX
      function showToast(message, type='info', timeout=3500){
        try{
          const t = document.getElementById('toast');
          const msg = document.getElementById('toastMsg');
          const close = document.getElementById('toastClose');
          if(!t || !msg || !close) { alert(message); return; }
          msg.textContent = message || '';
          t.classList.remove('success','error');
          if(type === 'success') t.classList.add('success');
          if(type === 'error') t.classList.add('error');
          t.style.display = 'block';
          t.setAttribute('aria-hidden','false');
          if(t._timer) clearTimeout(t._timer);
          t._timer = setTimeout(()=>{ t.style.display='none'; t.setAttribute('aria-hidden','true'); t._timer = null; }, timeout);
          close.onclick = function(){ if(t._timer) clearTimeout(t._timer); t.style.display='none'; t.setAttribute('aria-hidden','true'); };
        }catch(e){ try{ alert(message); }catch(_){} }
      }

    (function(){
    const avatarBtn = document.getElementById('sidebarAvatarBtn');
    const avatarInput = document.getElementById('sidebarAvatarInput');
    const avatarImg = document.getElementById('sidebarAvatar');
    const avatarMenu = document.getElementById('sidebarAvatarMenu');
    const avatarMenuView = document.getElementById('avatarMenuView');
    const avatarMenuChoose = document.getElementById('avatarMenuChoose');
    if(!avatarBtn || !avatarInput || !avatarImg || !avatarMenu) return;
    let prevSrc = avatarImg.src;

    // If a recently-saved avatar was stored in localStorage (fallback when session hasn't refreshed yet),
    // use it to make the bottom-of-sidebar avatar appear permanent immediately and across reloads.
    try{
      const saved = localStorage.getItem('user_avatar');
      const savedName = localStorage.getItem('user_fullname');
      if(saved && saved.length && (!avatarImg.src || avatarImg.src.indexOf('logodrea.jpg') !== -1)){
        avatarImg.src = saved;
      }
      if(savedName){ const sn = document.getElementById('sidebarName'); if(sn) sn.textContent = savedName; }
      // also ensure header avatar + small header avatar reflect localStorage when available
      if(saved){ const headerAvatar = document.querySelector('.profile-menu .profile-btn img.profile-avatar'); if(headerAvatar) headerAvatar.src = saved; const hdr = document.getElementById('hdrAvatarSmall'); if(hdr) hdr.src = saved; }
    }catch(e){ /* ignore storage errors in private browsers */ }

    avatarBtn.addEventListener('click', (ev)=>{
      ev.stopPropagation();
      const shown = avatarMenu.style.display === 'block';
      document.querySelectorAll('#sidebarAvatarMenu').forEach(m=> m.style.display='none');
      if(shown){ avatarMenu.style.display = 'none'; return; }
      avatarMenu.style.display = 'block';
      avatarMenu.style.position = 'fixed';
      avatarMenu.style.transform = 'none';
      avatarMenu.style.zIndex = 9999;
      avatarMenu.style.visibility = 'hidden';
      const rect = avatarBtn.getBoundingClientRect();
      const mw = avatarMenu.offsetWidth || 200; const mh = avatarMenu.offsetHeight || 90;
      let top = rect.bottom + 8; let left = rect.left;
      // If menu would overflow to the right, position to the left of the avatar
      if (rect.left + mw > window.innerWidth - 8) {
        left = Math.max(8, rect.right - mw);
      }
      // If menu would overflow bottom, try placing above
      if (top + mh > window.innerHeight - 8) {
        top = Math.max(8, rect.top - mh - 8);
      }

      avatarMenu.style.left = Math.max(8, left) + 'px';
      avatarMenu.style.top = Math.max(8, top) + 'px';
      avatarMenu.style.right = 'auto';
      avatarMenu.style.bottom = 'auto';
      avatarMenu.style.visibility = 'visible';
    });

    // Clicking 'See profile picture' -> open modal viewer
    avatarMenuView.addEventListener('click', (ev)=>{
      ev.stopPropagation();
      avatarMenu.style.display = 'none';
      const src = avatarImg.src || '';
      if(!src) { showToast('No profile picture available', 'error'); return; }
      showProfilePic(src);
    });

    // Clicking 'Choose profile picture' -> open file picker
    avatarMenuChoose.addEventListener('click', (ev)=>{
      ev.stopPropagation();
      avatarMenu.style.display = 'none';
      try{ avatarInput.click(); }catch(e){}
    });

    // close menu when clicking outside or pressing Escape
    document.addEventListener('click', function(e){ if(avatarMenu && avatarMenu.style.display === 'block'){ avatarMenu.style.display = 'none'; } });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ if(avatarMenu) avatarMenu.style.display = 'none'; } });

    avatarInput.addEventListener('change', async function(){
      const f = this.files && this.files[0];
      if(!f) return;
      if(!f.type || !f.type.startsWith('image/')){ showToast('Please choose an image file.', 'error'); this.value=''; return; }
      // client-side limit must match server (3MB)
      if(f.size > 3 * 1024 * 1024){ showToast('Image too large (max 3MB).', 'error'); this.value=''; return; }

      const objectUrl = URL.createObjectURL(f);
      prevSrc = avatarImg.src;
      avatarImg.src = objectUrl;

      const fd = new FormData();
      fd.append('avatar', f);

      try{
        showToast('Uploading photo...', 'info', 1800);
        const res = await fetch('save_midwife_info.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        const txt = await res.text();
        let data = {};
        try{ data = txt ? JSON.parse(txt) : {}; } catch(e){
          // non-JSON response — include raw text in console for debugging
          console.error('Non-JSON response from save_midwife_info.php:', txt);
          throw new Error('Server returned an unexpected response. See console.');
        }

        if(!res.ok || !data.success){
          // prefer server-provided message when available
          const msg = data && data.message ? data.message : ('Upload failed: ' + (res.status || 'error'));
          throw new Error(msg);
        }

        showToast(data.message || 'Profile photo updated', 'success');
        if(data.data){
          window.midwifeProfile = Object.assign({}, window.midwifeProfile || {}, data.data);
          try{ populateProfile(window.midwifeProfile); } catch(e) { /* ignore */ }
          const newAvatar = window.midwifeProfile.avatar_url || data.data.avatar_url || '';
          const newName = window.midwifeProfile.name || data.data.name || data.data.full_name || '';
          if(newAvatar) {
            avatarImg.src = newAvatar;
            try{ localStorage.setItem('user_avatar', newAvatar); }catch(e){}
          }
          if(newName){ try{ localStorage.setItem('user_fullname', newName); }catch(e){} }
        }
      }catch(err){
        console.error('Avatar upload failed', err);
        showToast(err && err.message ? err.message : 'Failed to upload photo', 'error');
        avatarImg.src = prevSrc;
      } finally {
        try{ URL.revokeObjectURL(objectUrl); }catch(e){}
        avatarInput.value = '';
      }
    });
  })();
  
  // Populate profile UI elements after upload or when profile data is available
  function populateProfile(d){
    if(!d) return;
    try{
      var sn = document.getElementById('sidebarName');
      if(sn) sn.textContent = d.name || d.username || d.full_name || '<?php echo htmlspecialchars($_SESSION['username'] ?? 'Profile'); ?>';
      var sa = document.getElementById('sidebarAvatar');
      if(sa && (d.avatar_url || d.photo)) sa.src = d.avatar_url || d.photo;
      // header/profile button avatar and name
      try{
        const headerName = document.querySelector('.profile-menu .profile-btn span');
        if(headerName) headerName.textContent = d.name || d.username || '<?php echo htmlspecialchars($_SESSION['username'] ?? 'Profile'); ?>';
        const headerAvatar = document.querySelector('.profile-menu .profile-btn img.profile-avatar');
        if(headerAvatar && (d.avatar_url || d.photo)) headerAvatar.src = d.avatar_url || d.photo;
        // small header dropdown avatar and name
        try{ const hdr = document.getElementById('hdrAvatarSmall'); if(hdr && (d.avatar_url || d.photo)) hdr.src = d.avatar_url || d.photo; const hn = document.getElementById('hdrName'); if(hn) hn.textContent = d.name || d.username || '<?php echo htmlspecialchars($_SESSION['username'] ?? 'Profile'); ?>'; }catch(e){}
      }catch(e){}
      // persist to localStorage so the choice appears permanent immediately and across reloads
      try{
        if(d.avatar_url) localStorage.setItem('user_avatar', d.avatar_url);
        if(d.name) localStorage.setItem('user_fullname', d.name);
      }catch(e){}
    }catch(e){ console.error('populateProfile error', e); }
  }

  // Show profile picture in modal (uses existing markup `profilePicModal` and `profilePicImg`)
  function showProfilePic(src){
    try{
      const modal = document.getElementById('profilePicModal');
      const img = document.getElementById('profilePicImg');
      if(!modal || !img) return;
      img.src = src || '';
      try{ window.__lastFocusedBeforePatientModal = document.activeElement; }catch(e){}
      modal.style.display = 'flex';
      modal.setAttribute('aria-hidden','false');
      try{ const btn = modal.querySelector('.profile-pic-close'); if(btn) btn.focus(); }catch(e){}
    }catch(e){ console.error('showProfilePic error', e); }
  }
      </script>

      <!-- MANAGE PANEL: lists all upcoming appointments, with actions -->
      <?php if(!$MINIMAL_PANEL || $MINIMAL_PANEL === 'manage'): ?>
      <section id="panel-manage" class="panel" hidden>
        <h2>Manage Appointments</h2>
        <p class="muted">Approve or cancel patient appointments. Status changes will be visible to patients.</p>

        <div style="display:flex;justify-content:flex-end;align-items:center;gap:8px;margin-bottom:10px">
          <input id="manageSearch" placeholder="Search appointments by patient, service, date or ID" style="padding:8px;border-radius:8px;border:1px solid #eee;min-width:260px">
          <button id="manageSearchClear" class="btn-pill ghost" type="button">Clear</button>
        </div>

        <table id="manageTable">
          <thead>
            <tr>
              <th class="col-patient">Patient Name</th>
              <th class="col-service">Service</th>
              <th class="col-schedule">Date</th>
              <th>Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
              <tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Loading appointments…</td></tr>
          </tbody>
        </table>
      </section>
      <?php endif; ?>

      <!-- MEDICAL RECORDS PANEL -->

      <?php if(!$MINIMAL_PANEL || $MINIMAL_PANEL === 'medical'): ?>
      <section id="panel-medical" class="panel" hidden>
        <h2>Medical Records</h2>
        <p class="muted">Encode patient medical records (OB score, vitals, G/ P, LMP, EDD, etc.).</p>

        <div style="margin-bottom:12px;display:flex;gap:8px;align-items:center">
          <button class="btn-pill" id="newMedicalBtn">Add Medical Record</button>
          <button id="btnRefreshMedical" type="button" title="Refresh list" aria-label="Refresh medical records" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;background:#f6fbf7;border:1px solid #e6f3ea;color:#2e7d32;box-shadow:none;padding:0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M21 12a9 9 0 1 0-2.34 5.86" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M21 3v6h-6" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>

        <!-- Unified Medical Records and Appointments Table -->
        <div id="medicalRecordsWrap" style="margin-bottom:12px">
          <h3 style="margin:0 0 8px 0;color:var(--lav-4);font-size:1rem">Medical Records</h3>
          <table id="medicalTable">
            <thead>
              <tr>
                <th>Patient</th>
                <th>Cellphone</th>
                <th>OB Score</th>
                <th>LMP</th>
                <th>EDD</th>
                <th>BP</th>
                <th>Weight (kg)</th>
                <th>Pulse</th>
                <th>Resp. Rate</th>
                <th>Fetal Heart Tone</th>
                <th>Gravida</th>
                <th>Para</th>
                <th>Result</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="14" style="color:var(--muted);padding:20px;text-align:center">Loading medical records…</td></tr>
            </tbody>
          </table>
        </div>

        <div id="medicalForm" style="display:none;margin-bottom:14px">
          <!-- Choose Patient Dropdown -->
          <div class="form-row">
            <label>Choose Patient</label>
            <select id="medicalPatientSelect" class="fancy-select" style="width:100%" required>
              <option value="">-- choose patient --</option>
            </select>
          </div>
          <!-- shows which patient is selected for the record -->
          <div id="selectedPatientBanner" style="display:none;margin-bottom:10px;padding:10px;border-radius:8px;background:linear-gradient(90deg,#f8f5ff,#fff);border:1px solid #f0eaff;color:var(--lav-4);font-weight:700">
            Selected patient: <span id="selectedPatientName">(none)</span>
          </div>
          <form id="formMedical">
            <!-- hidden medical record id (for edit) and patient id so the server can associate the record to a patient account -->
            <input type="hidden" name="id" id="medical_record_id">
            <input type="hidden" name="patient_user_id" id="medical_patient_user_id">
            <div class="form-grid">
              <div class="form-row"><label>Patient Name</label><input id="medical_patient_name" name="patient_name" required readonly></div>
              <div class="form-row"><label>Age</label><input id="medical_age" type="number" name="age" readonly></div>
              <div class="form-row"><label>Cellphone Number</label><input id="medical_cellphone" name="cellphone" readonly></div>
              <div class="form-row"><label>OB Score</label><input name="ob_score"></div>
              <div class="form-row"><label>Last Menstrual Period</label><input type="date" name="lmp"></div>
              <div class="form-row"><label>Estimated Date of Delivery (EDD)</label><input type="date" name="edd"></div>
              <div class="form-row"><label>Age of Gestation (weeks)</label><input name="gestation_age"></div>
              <div class="form-row"><label>Blood Pressure</label><input name="blood_pressure" placeholder="e.g. 120/80"></div>
              <div class="form-row"><label>Weight (kg)</label><input type="number" step="0.1" name="weight"></div>
              <div class="form-row"><label>Pulse Rate</label><input name="pulse"></div>
              <div class="form-row"><label>Respiratory Rate</label><input name="respiratory_rate"></div>
              <div class="form-row"><label>Fetal Heart Tone</label><input name="fht"></div>
              <div class="form-row"><label>Gravida</label><input name="gravida"></div>
              <div class="form-row"><label>Para</label><input name="para"></div>
              <div class="form-row"><label>Result</label>
                <select name="result" id="medical_result" style="width:100%;padding:6px;border-radius:6px;border:1px solid #eee">
                  <option value="">-- choose --</option>
                  <option value="normal">Normal</option>
                  <option value="abnormal">Abnormal</option>
                </select>
              </div>
              <!-- Address, Day, and Notes removed per request -->
            </div>
            <div class="form-actions">
              <button type="button" class="btn-pill" id="saveMedicalBtn">Save Medical Record</button>
              <button type="button" class="btn-pill ghost" id="cancelMedicalBtn">Cancel</button>
            </div>
          </form>
        </div>
        <script>
        // Wire medical records refresh
        document.getElementById('btnRefreshMedical')?.addEventListener('click', function(){ try{ if(typeof loadMedicalRecords === 'function'){ loadMedicalRecords(); } else { console.debug('loadMedicalRecords() not defined'); } }catch(e){ console.error('Refresh medical records failed', e); } });
        // Load patients for the medical record dropdown
        async function loadMedicalPatients() {
          const sel = document.getElementById('medicalPatientSelect');
          if (!sel) return;
          sel.innerHTML = '<option value="">-- choose patient --</option>';
          try {
            const res = await fetch('get_patients.php', { credentials: 'same-origin' });
            if (!res.ok) return;
            const j = await res.json();
            const rows = j && (j.patients || j.data || j.results) || [];
            if (!Array.isArray(rows)) return;
            window.patientsMap = window.patientsMap || {};
            rows.forEach(r => {
              const id = r.user_id || r.id || r.patient_user_id || r.patient_id || '';
              const label = (r.name || r.patient_name || (r.first_name ? (r.first_name + (r.last_name ? (' ' + r.last_name) : '')) : '')) || ('#' + id);
              const opt = document.createElement('option'); opt.value = id; opt.textContent = label;
              sel.appendChild(opt);
              window.patientsMap[String(id)] = r;
            });
          } catch (err) { console.error('loadMedicalPatients', err); }
        }

        // When patient is selected, fill fields
        document.getElementById('medicalPatientSelect')?.addEventListener('change', function() {
          const userId = this.value;
          const p = window.patientsMap && window.patientsMap[userId];
          document.getElementById('medical_patient_user_id').value = userId || '';
          document.getElementById('medical_patient_name').value = p ? (p.name || p.patient_name || p.full_name || '') : '';
          document.getElementById('medical_cellphone').value = p ? (p.mobile_number || p.cellphone || p.mobile || '') : '';
          document.getElementById('medical_age').value = p ? (p.age || '') : '';
          // update banner
          const banner = document.getElementById('selectedPatientBanner');
          const bannerName = document.getElementById('selectedPatientName');
          if (userId && p) {
            if (banner && bannerName) {
              bannerName.textContent = p.name || p.patient_name || p.full_name || ('#' + userId);
              banner.style.display = 'block';
            }
          } else {
            if (banner) banner.style.display = 'none';
          }
        });

        // Load patients when form is shown
        document.getElementById('newMedicalBtn')?.addEventListener('click', () => {
          loadMedicalPatients();
        });
        // Also load on page load if form is visible
        if (document.getElementById('medicalForm').style.display !== 'none') {
          loadMedicalPatients();
        }
        </script>
      </section>
      <?php endif; ?>

      <!-- NEWBORN RECORDS PANEL (for mother-related newborn listing) -->
      <?php if(!$MINIMAL_PANEL || $MINIMAL_PANEL === 'newborn'): ?>
      <section id="panel-newborn" class="panel" hidden>
        <h2>Newborn Records (mothers)</h2>
        <p class="muted">List and add newborn records related to a patient.</p>

        <div style="margin-bottom:12px;display:flex;gap:8px;align-items:center">
          <button class="btn-pill" id="newNewbornBtn">Add Newborn Record</button>
          <button id="btnRefreshNewborns" type="button" title="Refresh list" aria-label="Refresh list" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;background:#f6fbf7;border:1px solid #e6f3ea;color:#2e7d32;box-shadow:none;padding:0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M21 12a9 9 0 1 0-2.34 5.86" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M21 3v6h-6" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>

        <div id="newbornForm" style="display:none;margin-bottom:14px">
          <form id="formNewborn">
            <!-- hidden id for editing existing newborn records -->
            <input type="hidden" name="id" id="newborn_record_id">
            <div class="form-grid">
              <div class="form-row"><label>Select Patient</label>
                  <select name="patient_user_id" id="newbornPatientSelect" class="fancy-select" required>
                  <option value="">-- choose patient --</option>
                </select>
              </div>
              <div class="form-row"><label>Mother Name</label><input name="mother_name" id="newbornMotherName" readonly></div>
              <div class="form-row"><label>Child Name</label><input name="child_name" required></div>
              <div class="form-row"><label>Gender</label><select name="gender"><option value="Male">Male</option><option value="Female">Female</option></select></div>
              <div class="form-row"><label>Date of Birth</label><input type="date" name="dob" required></div>
              <div class="form-row"><label>Time of Birth</label><input type="time" name="time_of_birth"></div>
              <div class="form-row"><label>Blood Type</label><input name="blood_type"></div>
              <div class="form-row"><label>Weight (kg)</label><input type="number" step="0.01" name="weight"></div>
              <div class="form-row"><label>Notes</label><textarea name="notes"></textarea></div>
            </div>
            <div class="form-actions">
              <button type="button" class="btn-pill" id="saveNewbornBtn">Save Newborn</button>
              <button type="button" class="btn-pill ghost" id="cancelNewbornBtn">Cancel</button>
            </div>
          </form>
        </div>

        <!-- Editable Screening Record Form -->
        <div id="screeningFormWrap" style="display:none;margin-bottom:14px">
          <form id="formScreening" enctype="multipart/form-data">
            <input type="hidden" name="baby_id" id="screeningBabyId">
            <div class="form-grid">
              <div class="form-row"><label>Baby</label>
                <select id="screeningNewbornSelect" name="baby_id" class="fancy-select" required></select>
              </div>
              <div class="form-row"><label>Vitamin K</label><input type="checkbox" id="chkVitK" name="vit_k"></div>
              <div class="form-row"><label>Hepa B</label><input type="checkbox" id="chkHepaB" name="hepa_b"></div>
              <div class="form-row"><label>BCG</label><input type="checkbox" id="chkBCG" name="bcg"></div>
              <div class="form-row"><label>Newborn Screening</label><input type="checkbox" id="chkNewbornScreening" name="newborn_screening"></div>
              <div class="form-row"><label>Hearing Taken</label><input type="checkbox" id="chkHearingTaken" name="hearing_taken"></div>
              <div class="form-row"><label>Hearing Result</label>
                <select id="selHearingResult" name="hearing_result">
                  <option value="">-- select --</option>
                  <option value="Pass">Pass</option>
                  <option value="Refer">Refer</option>
                </select>
              </div>
              <div class="form-row"><label>Screening Result File</label>
                <input type="file" id="screeningResultFile" name="result_file" accept="application/pdf,image/*">
                <div id="screeningFileLink" style="margin-top:6px;color:var(--muted);font-size:0.95rem"></div>
              </div>
              <div class="form-row"><label>Last Saved</label><span id="screeningLastSaved">-</span></div>
            </div>
            <div class="form-actions">
              <button type="button" class="btn-pill" id="btnSaveScreening">Save Screening</button>
              <button type="button" class="btn-pill ghost" id="btnCancelScreening">Cancel</button>
            </div>
          </form>
        </div>
        <table id="newbornsTable">
        <table id="newbornsTable">
          <thead>
            <tr>
              <th>Baby ID</th>
              <th>Baby Name</th>
              <th>Sex</th>
              <th>Birthday</th>
              <th>Weight</th>
              <th>Mother</th>
              <th>Vit K</th>
              <th>Hepa B</th>
              <th>BCG</th>
              <th>Newborn Screening</th>
              <th>Hearing Taken</th>
              <th>Hearing Result</th>
              <th>Screening Result</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="16" style="color:var(--muted);padding:20px;text-align:center">Loading newborn and screening records…</td></tr>
          </tbody>
        </table>
      </section>
      <?php endif; ?>

      <script>
      // Wire the Refresh List button for newborns
      document.getElementById('btnRefreshNewborns')?.addEventListener('click', function(){
        try{ if(typeof loadNewborns === 'function'){ loadNewborns(); } else { console.debug('loadNewborns() not defined'); } }catch(e){ console.error('Refresh newborns failed', e); }
      });
      </script>

      <!-- PRESCRIPTIONS PANEL -->
      <?php if(!$MINIMAL_PANEL || $MINIMAL_PANEL === 'prescriptions'): ?>
      <section id="panel-prescriptions" class="panel" hidden>
        <h2>Prescriptions</h2>
        <p class="muted">Write instructions and prescription drugs for a specific patient.</p>

        <div style="margin-bottom:12px;display:flex;gap:8px;align-items:center">
          <button class="btn-pill" id="newPrescriptionBtn">Add Prescription</button>
          <button id="btnRefreshPrescriptions" type="button" title="Refresh list" aria-label="Refresh prescriptions" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;background:#f6fbf7;border:1px solid #e6f3ea;color:#2e7d32;box-shadow:none;padding:0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M21 12a9 9 0 1 0-2.34 5.86" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M21 3v6h-6" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>

        <div id="prescriptionForm" style="display:none;margin-bottom:14px">
          <form id="formPrescription">
            <!-- hidden id for editing an existing prescription -->
            <input type="hidden" name="id" id="prescription_id">
            <div class="form-grid">
              <div class="form-row"><label>Select Patient</label>
                <select name="patient_user_id" id="prescriptionPatientSelect" class="fancy-select" required>
                  <option value="">-- choose patient --</option>
                </select>
              </div>
              <div class="form-row"><label>Patient Name</label><input name="patient_name" id="prescriptionPatientName" readonly required></div>
              <div class="form-row"><label>Date</label><input type="date" name="date"></div>
              <div class="form-row" style="grid-column:1 / -1"><label>Result File (PDF / image)</label>
                <div style="display:flex;align-items:center;gap:8px">
                  <label id="prescriptionFileBtn" class="btn" style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer">
                    <span style="font-weight:600">Choose File</span>
                    <input type="file" name="file" id="prescriptionFile" accept="application/pdf,image/*" style="display:none">
                  </label>
                  <div id="prescriptionFileName" style="color:var(--muted);font-size:0.95rem">No file chosen</div>
                </div>
                <div id="prescriptionFileLink" style="margin-top:6px;color:var(--muted);font-size:0.95rem"></div>
              </div>
            </div>
            <div class="form-actions">
              <button type="button" class="btn-pill" id="savePrescriptionBtn">Save Prescription</button>
              <button type="button" class="btn-pill ghost" id="cancelPrescriptionBtn">Cancel</button>
            </div>
          </form>
        </div>

        <table id="prescriptionsTable">
          <thead>
            <tr>
              <th>Patient</th>
              <th>File</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Loading prescriptions…</td></tr>
          </tbody>
        </table>
      </section>
      <?php endif; ?>

      <!-- Inventory moved to clerk_portal.php -->

      <!-- Payments panel removed per update: handled by Clerk portal -->

      <!-- LAB PANEL -->
      <?php if(!$MINIMAL_PANEL || $MINIMAL_PANEL === 'lab'): ?>
      <section id="panel-lab" class="panel" hidden>

        <div style="margin-bottom:12px;display:flex;gap:8px;align-items:center">
          <button class="btn-pill" id="addResultsUploadBtn">Add Results Upload</button>
          <button id="btnRefreshResults" type="button" title="Refresh list" aria-label="Refresh results uploads" style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;background:#f6fbf7;border:1px solid #e6f3ea;color:#2e7d32;box-shadow:none;padding:0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <path d="M21 12a9 9 0 1 0-2.34 5.86" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M21 3v6h-6" stroke="#2e7d32" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>

        <div id="labUploadForm" style="margin-bottom:12px;padding:12px;border-radius:8px;background:#fbf8ff;border:1px solid #f0eaff;display:none">
          <form id="formLabResult">
            <div style="margin-bottom:8px">
              <label style="font-weight:600;color:var(--muted);display:block;margin-bottom:6px">Choose Patient</label>
              <select id="labPatientSelect" class="fancy-select" style="width:100%" required>
                <option value="">-- choose patient --</option>
              </select>
            </div>
            <div id="labPatientNameDisplay" style="margin-bottom:8px;padding:8px;border-radius:6px;background:#fff;border:1px solid #e0d5f5;color:var(--text-dark);font-size:0.95rem;display:none">
              <strong>Patient:</strong> <span id="labPatientNameText"></span>
            </div>
            <input type="hidden" name="patient_user_id" id="lab_patient_user_id">
            <input type="hidden" name="patient_name" id="lab_patient_name">
            <input type="hidden" name="appointment_id" id="lab_appointment_id">
            <div style="margin-bottom:8px">
              <label style="font-weight:600;color:var(--muted);display:block;margin-bottom:6px">Choose Completed Appointment</label>
              <select id="labAppointmentForPatientSelect" class="fancy-select" style="width:100%" required>
                <option value="">-- choose appointment --</option>
              </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:center">
              <div>
                <label style="font-weight:600;color:var(--muted);display:block;margin-bottom:6px">File</label>
                <div style="display:flex;align-items:center;gap:8px">
                  <label id="labFileBtn" class="btn" style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer">
                    <span style="font-weight:600">Choose File</span>
                    <input type="file" name="file" id="labFile" accept="image/*,.pdf,.doc,.docx" style="display:none">
                  </label>
                  <div id="labFileName" style="color:var(--muted);font-size:0.95rem">No file chosen</div>
                </div>
              </div>
              <div>
                <label style="font-weight:600;color:var(--muted);display:block;margin-bottom:6px">Notes</label>
                <input type="text" name="notes" id="labNotes" placeholder="e.g. CBC - normal" style="width:100%;padding:8px;border-radius:6px;border:1px solid #e0d5f5;font-size:0.95rem">
              </div>
            </div>
            <div style="text-align:right;margin-top:10px">
              <button type="button" class="btn-pill" id="uploadLabBtn">Upload Result</button>
              <button type="button" class="btn-pill ghost" id="cancelLabUploadBtn" style="margin-left:8px">Cancel</button>
            </div>
            <div id="labUploadProgressWrap" style="display:none;margin-top:8px">
              <div style="height:8px;background:#eee;border-radius:6px;overflow:hidden">
                <div id="labUploadProgressBar" style="width:0%;height:100%;background:linear-gradient(90deg,#8e7bff,#d6b8ff);"></div>
              </div>
              <div id="labUploadProgressText" style="font-size:0.85rem;color:var(--muted);margin-top:6px">Preparing upload…</div>
            </div>
            <div id="labUploadMessage" style="display:none;margin-top:8px;padding:8px;border-radius:8px;font-size:0.9rem;max-width:420px">
              <span id="labUploadMessageText"></span>
              <button id="labUploadMessageClose" class="btn-pill ghost" style="float:right;padding:6px 10px;margin-left:8px">OK</button>
            </div>
          </form>
        </div>

<script>
// Show upload form only when Add Results Upload button is clicked
document.getElementById('addResultsUploadBtn')?.addEventListener('click', function() {
  document.getElementById('labUploadForm').style.display = 'block';
  populateLabPatientSelect();
});

// Hide upload form when Cancel is clicked
document.getElementById('cancelLabUploadBtn')?.addEventListener('click', function() {
  document.getElementById('labUploadForm').style.display = 'none';
});

// Populate patient dropdown for results upload
async function populateLabPatientSelect() {
  const sel = document.getElementById('labPatientSelect');
  if (!sel) return;
  sel.innerHTML = '<option value="">-- choose patient --</option>';
  try {
    const res = await fetch('get_patients.php', { credentials: 'include' });
    const j = await res.json();
    const patients = j.patients || j.data || j.results || [];
    if (!Array.isArray(patients)) return;
    patients.forEach(p => {
      const pid = p.user_id || p.id || '';
      const patientName = p.name || p.patient_name || p.full_name || ('#' + pid);
      const opt = document.createElement('option');
      opt.value = pid;
      opt.textContent = `${patientName}${p.mobile_number ? ' — ' + p.mobile_number : ''}`;
      opt.dataset.patientUserId = pid;
      opt.dataset.patientName = patientName;
      sel.appendChild(opt);
    });
  } catch (err) { console.error('Failed to populate patients for lab upload', err); }
}

// When patient is chosen, populate completed appointments for that patient
document.getElementById('labPatientSelect')?.addEventListener('change', async function() {
  const patientId = this.value;
  document.getElementById('lab_patient_user_id').value = patientId;
  const patientName = this.options[this.selectedIndex].dataset.patientName || '';
  document.getElementById('lab_patient_name').value = patientName;
  
  // Display patient name in the display box
  const patientNameDisplay = document.getElementById('labPatientNameDisplay');
  const patientNameText = document.getElementById('labPatientNameText');
  if (patientName && patientNameDisplay && patientNameText) {
    patientNameText.textContent = patientName;
    patientNameDisplay.style.display = 'block';
  } else if (patientNameDisplay) {
    patientNameDisplay.style.display = 'none';
  }
  
  const apptSel = document.getElementById('labAppointmentForPatientSelect');
  apptSel.innerHTML = '<option value="">-- choose appointment --</option>';
  if (!patientId) return;
  try {
    // Fetch all completed appointments for the patient
    const res = await fetch('get_manage_appointments.php?user_id=' + encodeURIComponent(patientId), { credentials: 'include' });
    if (!res.ok) return;
    const j = await res.json();
    const appts = Array.isArray(j.appointments) ? j.appointments : (Array.isArray(j.data) ? j.data : []);

    // Fetch already uploaded results for the patient
    const resResults = await fetch('get_results_upload.php', { credentials: 'include' });
    let uploadedApptIds = new Set();
    if (resResults.ok) {
      const jr = await resResults.json().catch(()=>null);
      const results = Array.isArray(jr && jr.results_uploaded) ? jr.results_uploaded : (Array.isArray(jr && jr.lab_results) ? jr.lab_results : []);
      results.forEach(r => {
        if (String(r.patient_user_id) === String(patientId) && r.appointment_id) {
          uploadedApptIds.add(String(r.appointment_id));
        }
      });
    }

    appts.forEach(a => {
      // Only show completed appointments that do NOT have uploaded results
      const isCompleted = String(a.status || '').toLowerCase() === 'completed';
      const apptId = String(a.appointment_id || a.id || '');
      if (!isCompleted || uploadedApptIds.has(apptId)) return;
      const opt = document.createElement('option');
      opt.value = apptId;
      opt.textContent = (a.service || a.appointment_service || '') + (a.date ? ' - ' + a.date : '') + (a.time ? ' ' + a.time : '');
      apptSel.appendChild(opt);
    });
  } catch (err) { console.error('Failed to populate appointments for patient', err); }
});
</script>

<script>
// Wire results uploads refresh
document.getElementById('btnRefreshResults')?.addEventListener('click', function(){ try{ if(typeof loadResultsUploads === 'function'){ loadResultsUploads(); } else { console.debug('loadResultsUploads() not defined'); } }catch(e){ console.error('Refresh results uploads failed', e); } });

// Wire prescriptions refresh
document.getElementById('btnRefreshPrescriptions')?.addEventListener('click', function(){ try{ if(typeof loadPrescriptions === 'function'){ loadPrescriptions(); } else { console.debug('loadPrescriptions() not defined'); } }catch(e){ console.error('Refresh prescriptions failed', e); } });

</script>

        <!-- Results Uploads table -->
        <div style="margin-top:0">
                  <script>
                  // Show/hide upload form logic
                  document.getElementById('showLabUploadFormBtn')?.addEventListener('click', function() {
                    const form = document.getElementById('labUploadForm');
                    if (form) form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
                  });
                  </script>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <h4 style="margin:0;color:var(--lav-4)">Uploaded Results</h4>
            <div>
              <button id="btnRefreshLabResults" class="btn-pill ghost" type="button">Refresh Results</button>
            </div>
          </div>

          <table id="resultsUploadsTable">
            <thead>
              <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Notes</th>
                <th>File</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="resultsUploadsTbody">
              <tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Loading uploaded results…</td></tr>
            </tbody>
          </table>
        </div>

        <script>
        async function loadResultsUploads(){
          const tbody = document.getElementById('resultsUploadsTbody');
          if(!tbody) return;
          tbody.innerHTML = `<tr><td colspan="4" style="color:var(--muted);padding:20px;text-align:center">Loading uploaded results…</td></tr>`;
          try{
            const res = await fetch('get_results_upload.php', { credentials: 'include' });
            if(!res.ok){
              tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Failed to load results (server error).</td></tr>`;
              return;
            }
            const j = await res.json();
            const rows = (j && (j.results_uploaded || j.lab_results || j.results)) || [];
            if(!Array.isArray(rows) || rows.length === 0){
              tbody.innerHTML = `<tr><td colspan="4" style="color:var(--muted);padding:20px;text-align:center">No uploaded results found.</td></tr>`;
              return;
            }
            rows.sort((a,b)=> (b.uploaded_at || b.uploadedAt || '') .localeCompare(a.uploaded_at || a.uploadedAt || ''));

            // helper: try to guess appointment type from available fields when no appointment exists
            function guessAppointmentType(r){
              const check = (v)=> v ? String(v).toLowerCase() : '';
              // only consider explicit appointment/service fields to avoid false positives from filenames/results
              const svc = check(r.service || r.appointment_service || r.appointment_name || r.appointment_ref);
              if(!svc) return '';
              if(svc.includes('ultrasound') || svc.includes('sono') || svc.includes('us')) return 'Ultrasound';
              if(svc.includes('lab') || svc.includes('soa') || svc.includes('cbc') || svc.includes('urine') || svc.includes('blood') || svc.includes('laboratory')) return 'Laboratory';
              if(svc.includes('midwife')) return 'Midwife Checkup';
              if(svc.includes('ob') || svc.includes('oby') || svc.includes('ob-gyn') || svc.includes('gy') || svc.includes('gyn')) return 'OB-GYN Consultation';
              if(svc.includes('pedia') || svc.includes('child') || svc.includes('pedi')) return 'Pedia';
              return '';
            }

            // Prefetch appointments list to map appointment_id -> service/type
            let apptMap = {};
            try{
              const ma = await fetch('get_manage_appointments.php', { credentials: 'include' });
              if(ma.ok){
                const mj = await ma.json().catch(()=>null);
                if(mj && Array.isArray(mj.appointments)){
                  mj.appointments.forEach(a=>{
                    const id = a.id || a.appointment_id || '';
                    if(!id) return;
                    // build a readable label: service (date time)
                    const svc = a.appointment_service || a.service || a.appointment_type || '';
                    const d = a.appointment_date || a.date || '';
                    const t = a.appointment_time || a.time || '';
                    let label = svc || '';
                    if(d) label += (label ? ' - ' : '') + d + (t ? (' ' + t) : '');
                    apptMap[String(id)] = label || '';
                  });
                }
              }
            }catch(e){ console.debug('Failed prefetch manage appointments', e); }

            tbody.innerHTML = rows.map(r=>{
              // prefer appointment booking date/time when available; fallback to upload timestamp
              const apptDate = r.appointment_date || r.appointmentDate || r.date || '';
              const apptTime = r.appointment_time || r.appointmentTime || r.time || '';
              const dateSource = apptDate ? (apptDate + (apptTime ? (' ' + apptTime) : '')) : (r.uploaded_at || r.uploadedAt || r.created_at || '');
              const date = escapeHtml(formatDateTimeToDisplay(dateSource));
              const patient = escapeHtml(r.patient_name || r.patient || '');
              // show appointment service/type in the Appointment column; use only the appointment_id (do not fall back to results id)
              const apptId = (r.appointment_id && String(r.appointment_id).trim()) ? String(r.appointment_id) : '';
              // Prefer appointment service/type from prefetched appointments or joined appointment/service fields.
              // Do NOT use result_type or notes here (they are test/result related), otherwise 'normal' appears in Appointment column.
              const rawApptCandidates = [ (apptId ? apptMap[String(apptId)] : ''), r.appointment_service, r.service, r.appointment_ref, r.appointment_name, r.type ];
              let chosenAppt = '';
              for(const c of rawApptCandidates){ if(c && String(c).trim()){ chosenAppt = String(c).trim(); break; } }
              let apptText = chosenAppt || '';
              if(!apptText){
                const guessed = guessAppointmentType(r);
                apptText = guessed || (apptId ? ('#' + apptId) : '—');
              }
              const appt = escapeHtml(apptText);
              const notes = escapeHtml(r.notes || r.note || '');
              const filename = r.filename || r.url || r.file || '';
              let fileHtml = '';
              if(filename){
                let href = filename || '';
                if(!/^https?:\/\//i.test(href)){
                  // compute base path to the application (e.g. '/drea/') and join with relative asset path
                  try{
                    const path = window.location.pathname || '/';
                    const base = path.replace(/\/[^\/]*$/, '/');
                    href = base + href.replace(/^\/+/, '');
                  }catch(e){ href = '/' + href.replace(/^\/+/, ''); }
                }
                fileHtml = `<a href="${escapeHtml(href)}" target="_blank" rel="noopener noreferrer" class="prescription-file-link btn btn-view" data-file="${escapeHtml(href)}">View</a> <span style="margin-left:8px;color:var(--muted)">${escapeHtml(filename)}</span>`;
              }
              // only include data-appt-id when we have a real appointment id
              const trApptAttr = apptId ? ` data-appt-id="${escapeHtml(apptId)}"` : '';
              const trNotesAttr = ` data-notes="${escapeHtml(r.notes || r.note || '')}" data-file="${escapeHtml(r.filename || r.url || r.file || '')}"`;
              const chooseBtn = `<button class="btn-pill small" onclick="editResultsUploadMidwife(this)" style="cursor:pointer">Edit</button>`;
              return `<tr data-id="${escapeHtml(r.id || '')}" data-patient-id="${escapeHtml(r.patient_user_id || '')}" data-patient-name="${escapeHtml(r.patient_name || '')}"${trApptAttr}${trNotesAttr}>
                <td>${date}</td>
                <td>${patient}</td>
                <td style="min-width:300px">${notes}</td>
                <td>${fileHtml}</td>
                <td>${chooseBtn}</td>
              </tr>`;
            }).join('');
            
            // For rows that have an appointment id but missing a readable service, fetch details and update the Appointment cell
            (async function updateApptCells(){
              try{
                const rowsWithAppt = Array.from(document.querySelectorAll('#resultsUploadsTable tbody tr[data-appt-id]'));
                if(rowsWithAppt.length === 0) return;
                for(const tr of rowsWithAppt){
                  const aid = tr.dataset.apptId || '';
                  const apptCell = tr.querySelector('.appt-cell');
                  if(!aid || !apptCell) continue;
                  const current = (apptCell.textContent||'').trim();
                  // only try to replace placeholders that start with '#' or are empty
                  if(current && !current.startsWith('#')) continue;
                  try{
                    console.debug('Fetching appointment details for', aid);
                    const resp = await fetch('get_appointment_details.php?id=' + encodeURIComponent(aid), { credentials: 'include' });
                    if(!resp.ok) { console.debug('get_appointment_details failed', aid, resp.status); }
                    const j = await resp.json().catch(()=>null);
                    const svc = j && j.appointment ? (j.appointment.service || j.appointment.appointment_service || j.appointment.appointment_type || '') : '';
                    if(svc){
                      apptCell.textContent = svc;
                      continue;
                    }
                    // if get_appointment_details didn't return a service, try fetching the manage list and search by id
                    try{
                      console.debug('Fallback: fetching manage appointments to find', aid);
                      const r2 = await fetch('get_manage_appointments.php', { credentials: 'include' });
                      if(r2.ok){
                        const data = await r2.json().catch(()=>null);
                        if(data && Array.isArray(data.appointments)){
                          const found = data.appointments.find(a => String(a.id) === String(aid) || String(a.appointment_id||a.id) === String(aid));
                          if(found && (found.service || found.appointment_service || found.appointment_type)){
                            apptCell.textContent = found.service || found.appointment_service || found.appointment_type;
                            continue;
                          }
                        }
                      } else {
                        console.debug('get_manage_appointments failed', r2.status);
                      }
                    }catch(e){ console.error('Fallback manage fetch failed', e); }

                    // final fallback: use any available appointment fields, else show id
                    console.debug('Appointment details missing service after fallbacks', aid, j);
                    const fallback = (j && j.appointment && (j.appointment.service || j.appointment.appointment_service || j.appointment.appointment_type || j.appointment.type || j.appointment.name)) || '';
                    if(fallback) apptCell.textContent = fallback;
                    else apptCell.textContent = (aid ? ('#' + aid) : 'Unknown');
                  }catch(e){ console.error('Failed fetching appointment details for', aid, e); }
                }
              }catch(e){ console.error('Error updating appointment cells', e); }
            })();
          } catch(err){
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Failed to load uploaded results.</td></tr>`;
          }
        }

        // Function to handle "Edit" button click from midwife results table
        async function editResultsUploadMidwife(btn) {
          const tr = btn.closest('tr');
          if (!tr) return;
          
          const resultId = tr.dataset.id;
          const patientId = tr.dataset.patientId;
          const patientName = tr.dataset.patientName;
          const apptId = tr.dataset.apptId;
          const notes = tr.dataset.notes || '';
          const filename = tr.dataset.file || '';
          
          if (!patientId) {
            alert('Patient ID not found');
            return;
          }
          
          // Set the patient select value
          const patientSel = document.getElementById('labPatientSelect');
          if (patientSel) {
            patientSel.value = patientId;
            // Trigger change event to populate appointments
            patientSel.dispatchEvent(new Event('change'));
          }
          
          // Scroll to the form
          const form = document.getElementById('labUploadForm');
          if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            // Make sure form is visible
            if (form.style.display === 'none') {
              form.style.display = 'block';
            }
          }
          
          // Set patient name in hidden field and display field
          const patientNameHidden = document.getElementById('lab_patient_name');
          if (patientNameHidden) patientNameHidden.value = patientName;
          
          const patientNameDisplay = document.getElementById('labPatientNameDisplay');
          const patientNameText = document.getElementById('labPatientNameText');
          if (patientNameDisplay && patientNameText) {
            patientNameText.textContent = patientName;
            patientNameDisplay.style.display = 'block';
          }
          
          // Populate notes field
          const notesEl = document.getElementById('labNotes');
          if (notesEl) notesEl.value = notes;
          
          // Display file name if present
          if (filename) {
            const fileNameDisplay = document.getElementById('labFileName');
            if (fileNameDisplay) fileNameDisplay.textContent = filename;
          } else {
            const fileNameDisplay = document.getElementById('labFileName');
            if (fileNameDisplay) fileNameDisplay.textContent = 'No file chosen';
          }
          
          // If an appointment exists, set it after a short delay (allow appointments to populate)
          if (apptId) {
            setTimeout(() => {
              const apptSel = document.getElementById('labAppointmentForPatientSelect');
              if (apptSel) {
                apptSel.value = apptId;
                document.getElementById('lab_appointment_id').value = apptId;
              }
            }, 200);
          }
          
          // Show existing file link if present
          if (filename) {
            try {
              const fileLinkWrap = document.getElementById('labFileLink');
              if (fileLinkWrap) {
                let href = filename;
                if (!/^https?:\/\//i.test(href)) {
                  const path = window.location.pathname || '/';
                  const base = path.replace(/\/[^\/]*$/, '/');
                  href = base + href.replace(/^\/+/, '');
                }
                fileLinkWrap.innerHTML = `<a href="${escapeHtml(href)}" target="_blank" rel="noopener noreferrer">View existing file</a>`;
              }
            } catch (e) { console.error('Error setting lab file link', e); }
          }
        }

        // wire refresh buttons
        document.getElementById('btnRefreshLabAppts')?.addEventListener('click', ()=>{
          try{ loadResultsUploads(); }catch(e){}
        });
        document.getElementById('btnRefreshLabResults')?.addEventListener('click', ()=> loadResultsUploads());

        // initial load shortly after script parse so panel shows data when opened
        setTimeout(()=>{ try{ loadResultsUploads(); }catch(e){} }, 600);
        </script>
        <script>
        async function loadPayments(){
          const tbody = document.querySelector('#paymentsTable tbody');
          if(!tbody) return;
          tbody.innerHTML = `<tr><td colspan="11" style="color:var(--muted);padding:20px;text-align:center">Loading payments…</td></tr>`;
          try {
            const res = await fetch('get_payments.php', { credentials: 'same-origin' });
            const j = await res.json();
            if(!j.success || !Array.isArray(j.payments)) {
              tbody.innerHTML = `<tr><td colspan="11" style="color:var(--muted);padding:20px;text-align:center">No payments.</td></tr>`;
              return;
            }
            const payments = (Array.isArray(j.payments) ? j.payments : []).filter(p => {
              const nm = (p.patient_name || p.name || '') + '';
              return nm.trim().length > 0; // only include payments that have a non-empty patient name
            });
            if(payments.length === 0){
              tbody.innerHTML = `<tr><td colspan="11" style="color:var(--muted);padding:20px;text-align:center">No payments.</td></tr>`;
              return;
            }
            const rowsHtml = payments.map((p, idx) => {
              const no = String(idx+1);
              const receipt = escapeHtml(p.receipt_no || p.receipt || p.reference || '');
              const date = escapeHtml(p.uploaded_at || p.date_uploaded || p.uploaded || '');
              const patient = escapeHtml(p.patient_name || p.name || '');
              const service = escapeHtml(p.service || p.description || p.notes || '');
              const amount = escapeHtml(p.amount || p.amount_paid || p.paid || '');
              const gcash = escapeHtml(p.gcash_ref_no || p.reference_no || p.gcash_ref || p.gcash || '');
              const uploadedBy = escapeHtml(p.uploaded_by || p.uploader || p.source || p.patient_name || '');
              const status = escapeHtml((p.status || p.payment_status || p.paymentStatus || '').toString() || '');
              const remarks = escapeHtml(p.remarks || p.note || p.notes || '');
              const isPaid = (p.paid && Number(p.paid) === 1) || (String(status).toLowerCase() === 'paid');
              const paidBtn = isPaid ? `<span class="status-badge status-confirmed">Paid</span>` : `<button class="btn btn-paid" data-id="${escapeHtml(String(p.id||''))}">Paid</button>`;
              const fileUrl = p.url || p.file_url || p.path || '';
              const fileName = p.filename || p.file_name || (fileUrl ? fileUrl.split('/').pop() : '');
              const fileBtn = fileUrl ? `<button class="btn btn-view-file" data-url="${escapeHtml(fileUrl)}" data-fname="${escapeHtml(fileName)}">View</button>` : '—';
              const isVerified = p.verified && Number(p.verified) === 1;
              const verifyBtn = isVerified ? `<span class="status-badge status-confirmed">Verified</span>` : `<button class="btn btn-verify" data-id="${escapeHtml(String(p.id||''))}">Verify</button>`;

              return `<tr>
                <td>${no}</td>
                <td>${receipt}</td>
                <td style="white-space:nowrap">${date}</td>
                <td>${patient}</td>
                <td>${service}</td>
                <td style="white-space:nowrap">${amount}</td>
                <td style="white-space:nowrap">${gcash}</td>
                <td style="white-space:nowrap">${fileBtn}</td>
                <td>${uploadedBy}</td>
                <td>${isPaid ? paidBtn : (status ? status + ' ' + paidBtn : paidBtn)}</td>
                <td style="white-space:nowrap">${remarks ? remarks : verifyBtn}</td>
              </tr>`;
            }).join('');
            tbody.innerHTML = rowsHtml;

            // wire view buttons to open inline preview modal
            document.querySelectorAll('#paymentsTable .btn-view-file').forEach(btn=>{
              btn.addEventListener('click', function(e){
                const url = this.dataset.url;
                const fname = this.dataset.fname || '';
                viewMedicalFile(url, fname);
              });
            });

            // wire verify buttons (open verification modal)
            document.querySelectorAll('#paymentsTable .btn-verify').forEach(btn=>{
              btn.addEventListener('click', function(e){
                const id = this.dataset.id;
                if(!id) return;
                const modal = document.getElementById('verifyPaymentModal');
                if(!modal){
                  (async ()=>{
                    if(!confirm('Mark this payment as verified?')) return;
                    try{
                      const res = await fetch('midwife_verify_payment.php', {
                        method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'},
                        body: JSON.stringify({ id: id, verified: 1 })
                      });
                      if(!res.ok){
                        if(res.status === 403){ showToast('Access denied. You do not have permission to verify payments.', 'error'); return; }
                        const t = await res.text().catch(()=>'(no body)'); console.error('midwife_verify_payment.php error', res.status, t); alert('Server error while verifying payment. See console.'); return;
                      }
                      const j = await res.json().catch(()=>null);
                      if(j && j.success){ showToast('Payment verified', 'success'); await loadPayments(); } else { alert('Verify failed: ' + (j && j.message ? j.message : 'server error')); }
                    } catch(err){ console.error('Failed verifying payment', err); alert('Network error while verifying payment.'); }
                  })();
                  return;
                }
                const hid = document.getElementById('verify_modal_payment_id');
                if(!hid){
                  // modal markup not present yet — fall back to immediate confirm+fetch flow
                  (async ()=>{
                    if(!confirm('Mark this payment as verified?')) return;
                    try{
                      const res = await fetch('midwife_verify_payment.php', {
                        method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'},
                        body: JSON.stringify({ id: id, verified: 1 })
                      });
                      if(!res.ok){ if(res.status===403){ showToast('Access denied. You do not have permission to verify payments.', 'error'); return; } const t = await res.text().catch(()=>'(no body)'); console.error('midwife_verify_payment.php error', res.status, t); alert('Server error while verifying payment. See console.'); return; }
                      const j = await res.json().catch(()=>null);
                      if(j && j.success){ showToast('Payment verified', 'success'); await loadPayments(); } else { alert('Verify failed: ' + (j && j.message ? j.message : 'server error')); }
                    }catch(err){ console.error('Failed verifying payment', err); alert('Network error while verifying payment.'); }
                  })();
                  return;
                }
                hid.value = id;
                try{ const amt = document.getElementById('verify_modal_amount'); if(amt) amt.value = this.dataset.amount || ''; }catch(_){}
                try{ const ref = document.getElementById('verify_modal_reference'); if(ref) ref.value = this.dataset.ref || ''; }catch(_){}
                try{ const dt = document.getElementById('verify_modal_date'); if(dt) dt.value = this.dataset.date || ''; }catch(_){}
                try{ const remarks = document.getElementById('verify_modal_remarks'); if(remarks) remarks.value = ''; }catch(_){}
                try{ window['__lastFocusedBefore_'+(modal.id||'')] = document.activeElement; }catch(_){}
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden','false');
                try{ const btn = modal.querySelector('#verifyPaymentClose'); if(btn) btn.focus(); }catch(e){}
              });
            });

            // wire paid buttons
            document.querySelectorAll('#paymentsTable .btn-paid').forEach(btn=>{
              btn.addEventListener('click', function(e){
                const id = this.dataset.id;
                if(!id) return;
                const modal = document.getElementById('paidPaymentModal');
                if(!modal){
                  // fallback to confirm+fetch if modal not created
                  (async ()=>{
                    if(!confirm('Mark this payment as paid?')) return;
                    try{
                      const res = await fetch('midwife_mark_paid.php', {
                        method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'},
                        body: JSON.stringify({ id: id })
                      });
                      if(!res.ok){ if(res.status === 403){ showToast('Access denied. You do not have permission to mark payments as paid.', 'error'); return; } const t = await res.text().catch(()=>'(no body)'); console.error('midwife_mark_paid.php error', res.status, t); alert('Server error while marking payment as paid. See Console.'); return; }
                      const j = await res.json().catch(()=>null);
                      if(j && j.success){ showToast('Marked as paid', 'success'); await loadPayments(); } else { alert('Failed: ' + (j && j.message ? j.message : 'server error')); }
                    }catch(err){ console.error('Failed marking paid', err); alert('Network error while marking payment as paid.'); }
                  })();
                  return;
                }
                const hid = document.getElementById('paid_modal_payment_id');
                if(!hid){
                  // if modal markup missing hidden input, fallback
                  (async ()=>{
                    if(!confirm('Mark this payment as paid?')) return;
                    try{
                      const res = await fetch('midwife_mark_paid.php', {
                        method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'},
                        body: JSON.stringify({ id: id })
                      });
                      if(!res.ok){ if(res.status === 403){ showToast('Access denied. You do not have permission to mark payments as paid.', 'error'); return; } const t = await res.text().catch(()=>'(no body)'); console.error('midwife_mark_paid.php error', res.status, t); alert('Server error while marking payment as paid. See Console.'); return; }
                      const j = await res.json().catch(()=>null);
                      if(j && j.success){ showToast('Marked as paid', 'success'); await loadPayments(); } else { alert('Failed: ' + (j && j.message ? j.message : 'server error')); }
                    }catch(err){ console.error('Failed marking paid', err); alert('Network error while marking payment as paid.'); }
                  })();
                  return;
                }
                hid.value = id;
                try{ window['__lastFocusedBefore_'+(modal.id||'')] = document.activeElement; }catch(_){}
                modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
                try{ const btn = document.getElementById('paidPaymentClose'); if(btn) btn.focus(); }catch(e){}
              });
            });
          } catch(err){
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="11" style="color:var(--muted);padding:20px;text-align:center">Failed to load payments.</td></tr>`;
          }
        }

        // create file preview modal and verification modal (if not present)
        try{
          const existing = document.getElementById('filePreviewModal');
          if(!existing){
            const div = document.createElement('div');
            div.id = 'filePreviewModal';
            div.style.display = 'none';
            div.style.position = 'fixed';
            div.style.inset = '0';
            div.style.alignItems = 'center';
            div.style.justifyContent = 'center';
            div.style.background = 'rgba(0,0,0,0.6)';
            div.style.zIndex = '1300';
            div.innerHTML = `
              <div id="filePreviewInner" style="background:#fff;padding:12px;border-radius:8px;max-width:980px;width:94%;height:80vh;display:flex;flex-direction:column;box-shadow:0 18px 60px rgba(0,0,0,0.28)">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                  <strong id="filePreviewTitle">Preview</strong>
                  <button id="filePreviewClose" class="btn-pill ghost" style="padding:6px 10px">Close</button>
                </div>
                <div id="filePreviewContent" style="flex:1;overflow:auto;display:flex;align-items:center;justify-content:center;padding:8px;border-radius:6px;background:#fafafa"></div>
                <div style="text-align:right;margin-top:8px"></div>
              </div>
            `;
            document.body.appendChild(div);
            const modal = div;
            const closeBtn = document.getElementById('filePreviewClose');
            function _hideModal(m){
              try{
                if(!m) return;
                try{ const active = document.activeElement; if(active && m.contains(active)){ try{ active.blur(); }catch(_){} } }catch(_){}
                m.style.display = 'none';
                m.setAttribute('aria-hidden','true');
                const c = document.getElementById('filePreviewContent'); if(c) c.innerHTML='';
                try{ const key = '__lastFocusedBefore_'+(m.id||''); const prev = window[key]; if(prev && typeof prev.focus === 'function'){ prev.focus(); } window[key] = null; }catch(e){}
              }catch(e){ console.error('hide modal error', e); }
            }
            function _showModal(m){
              try{
                if(!m) return;
                try{ window['__lastFocusedBefore_'+(m.id||'')] = document.activeElement; }catch(_){}
                m.style.display = 'flex';
                m.setAttribute('aria-hidden','false');
                try{ const btn = m.querySelector('#filePreviewClose'); if(btn) btn.focus(); }catch(e){}
              }catch(e){ console.error('show modal error', e); }
            }
            closeBtn.addEventListener('click', ()=>{ _hideModal(modal); });
            modal.addEventListener('click', function(e){ if(e.target === modal){ _hideModal(modal); } });
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ if(modal.style.display === 'flex' || modal.style.display === 'block'){ _hideModal(modal); } } });
          }
        }catch(e){ console.error('Failed to initialize file preview modal', e); }

        try{
          const existingVerify = document.getElementById('verifyPaymentModal');
          if(!existingVerify){
            const div = document.createElement('div');
            div.id = 'verifyPaymentModal';
            div.style.display = 'none';
            div.style.position = 'fixed';
            div.style.inset = '0';
            div.style.alignItems = 'center';
            div.style.justifyContent = 'center';
            div.style.background = 'rgba(0,0,0,0.6)';
            div.style.zIndex = '1400';
            div.innerHTML = `
              <div id="verifyPaymentInner" style="background:#fff;padding:18px;border-radius:8px;max-width:520px;width:94%;box-shadow:0 18px 60px rgba(0,0,0,0.28)">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                  <strong style="font-size:1.05rem">Make this verified?</strong>
                  <button id="verifyPaymentClose" class="btn-pill ghost" style="padding:6px 10px">Close</button>
                </div>
                <div style="margin-top:6px;color:var(--muted);font-size:0.95rem">Are you sure you want to mark this payment as verified?</div>
                <input type="hidden" id="verify_modal_payment_id" value="" />
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                  <button id="verifyModalCancel" class="btn-pill ghost">No</button>
                  <button id="verifyModalSubmit" class="btn-pill primary">Yes</button>
                </div>
              </div>
            `;
            document.body.appendChild(div);

            const modal = div;
            function _hideVerify(m){
              try{
                if(!m) return;
                try{ const active = document.activeElement; if(active && m.contains(active)){ try{ active.blur(); }catch(_){} } }catch(_){}
                m.style.display = 'none';
                m.setAttribute('aria-hidden','true');
                try{ const key = '__lastFocusedBefore_'+(m.id||''); const prev = window[key]; if(prev && typeof prev.focus === 'function'){ prev.focus(); } window[key] = null; }catch(e){}
              }catch(e){ console.error('hide verify modal error', e); }
            }
            function _showVerify(m){
              try{ window['__lastFocusedBefore_'+(m.id||'')] = document.activeElement; }catch(_){}
              try{ m.style.display = 'flex'; m.setAttribute('aria-hidden','false'); const btn = document.getElementById('verifyPaymentClose'); if(btn) btn.focus(); }catch(e){ console.error('show verify modal error', e); }
            }
            document.getElementById('verifyPaymentClose').addEventListener('click', ()=>{ _hideVerify(modal); });
            document.getElementById('verifyModalCancel').addEventListener('click', ()=>{ _hideVerify(modal); });
            modal.addEventListener('click', function(e){ if(e.target === modal){ _hideVerify(modal); } });
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ if(modal.style.display === 'flex' || modal.style.display === 'block'){ _hideVerify(modal); } } });

            document.getElementById('verifyModalSubmit').addEventListener('click', async function(){
              const modalEl = document.getElementById('verifyPaymentModal');
              const id = document.getElementById('verify_modal_payment_id')?.value || '';
              if(!id){ alert('No payment selected for verification'); return; }
              try{
                const res = await fetch('midwife_verify_payment.php', {
                  method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'},
                  body: JSON.stringify({ id: id, verified: 1 })
                });
                if(!res.ok){
                  if(res.status === 403){ showToast('Access denied. You do not have permission to verify payments.', 'error'); return; }
                  const t = await res.text().catch(()=>'(no body)'); console.error('midwife_verify_payment.php error', res.status, t); alert('Server error while verifying payment. See console.'); return;
                }
                const j = await res.json().catch(()=>null);
                if(j && j.success){ showToast('Payment verified', 'success'); _hideVerify(modalEl); await loadPayments(); } else { alert('Verify failed: ' + (j && j.message ? j.message : 'server error')); }
              }catch(err){ console.error('Failed verifying payment', err); alert('Network error while verifying payment.'); }
            });
          }
        }catch(e){ console.error('Failed to initialize verify modal', e); }

        // create paid confirmation modal if not present
        try{
          const existingPaid = document.getElementById('paidPaymentModal');
          if(!existingPaid){
            const d2 = document.createElement('div');
            d2.id = 'paidPaymentModal';
            d2.style.display = 'none';
            d2.style.position = 'fixed';
            d2.style.inset = '0';
            d2.style.alignItems = 'center';
            d2.style.justifyContent = 'center';
            d2.style.background = 'rgba(0,0,0,0.6)';
            d2.style.zIndex = '1450';
            d2.innerHTML = `
              <div id="paidPaymentInner" style="background:#fff;padding:18px;border-radius:8px;max-width:520px;width:94%;box-shadow:0 18px 60px rgba(0,0,0,0.28)">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                  <strong style="font-size:1.05rem">Mark this payment as paid?</strong>
                  <button id="paidPaymentClose" class="btn-pill ghost" style="padding:6px 10px">Close</button>
                </div>
                <div style="margin-top:6px;color:var(--muted);font-size:0.95rem">Are you sure you want to mark this payment as paid?</div>
                <input type="hidden" id="paid_modal_payment_id" value="" />
                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
                  <button id="paidModalCancel" class="btn-pill ghost">No</button>
                  <button id="paidModalSubmit" class="btn-pill" style="background:#2e7d32;color:#fff">Yes</button>
                </div>
              </div>
            `;
            document.body.appendChild(d2);
            const paidModal = d2;
            function _hidePaid(m){
              try{ if(!m) return; try{ const active = document.activeElement; if(active && m.contains(active)){ try{ active.blur(); }catch(_){} } }catch(_){}
                m.style.display='none'; m.setAttribute('aria-hidden','true'); try{ const key='__lastFocusedBefore_'+(m.id||''); const prev=window[key]; if(prev && typeof prev.focus==='function'){ prev.focus(); } window[key]=null; }catch(e){}
              }catch(e){ console.error('hidePaid error', e); }
            }
            function _showPaid(m){ try{ window['__lastFocusedBefore_'+(m.id||'')] = document.activeElement; }catch(_){} try{ m.style.display='flex'; m.setAttribute('aria-hidden','false'); const btn = document.getElementById('paidPaymentClose'); if(btn) btn.focus(); }catch(e){} }
            document.getElementById('paidPaymentClose').addEventListener('click', ()=>{ _hidePaid(paidModal); });
            document.getElementById('paidModalCancel').addEventListener('click', ()=>{ _hidePaid(paidModal); });
            paidModal.addEventListener('click', function(e){ if(e.target === paidModal){ _hidePaid(paidModal); } });
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ if(paidModal.style.display === 'flex' || paidModal.style.display === 'block'){ _hidePaid(paidModal); } } });

            document.getElementById('paidModalSubmit').addEventListener('click', async function(){
              const modalEl = document.getElementById('paidPaymentModal');
              const id = document.getElementById('paid_modal_payment_id')?.value || '';
              if(!id){ alert('No payment selected'); return; }
              try{
                const res = await fetch('midwife_mark_paid.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: id }) });
                if(!res.ok){ if(res.status===403){ showToast('Access denied. You do not have permission to mark payments as paid.', 'error'); return; } const t = await res.text().catch(()=>'(no body)'); console.error('midwife_mark_paid.php error', res.status, t); alert('Server error while marking payment as paid. See Console.'); return; }
                const j = await res.json().catch(()=>null);
                if(j && j.success){ showToast('Marked as paid', 'success'); _hidePaid(modalEl); await loadPayments(); } else { alert('Failed: ' + (j && j.message ? j.message : 'server error')); }
              }catch(err){ console.error('Failed marking paid', err); alert('Network error while marking payment as paid.'); }
            });
          }
        }catch(e){ console.error('Failed to initialize paid modal', e); }

  // Manage Appointments: client-side search/filter (debounced) and auto-reapply after table updates
  (function(){
    const input = document.getElementById('manageSearch');
    if(!input) return;

    function debounce(fn, wait){ let t; return function(...a){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,a), wait); }; }

    function applyFilter(){
      const q = (input.value||'').trim().toLowerCase();
      const tbody = document.querySelector('#manageTable tbody');
      if(!tbody) return;
      const rows = tbody.querySelectorAll('tr');
      rows.forEach(tr=>{
        // if this is the placeholder row (single td colspan) show/hide accordingly
        const firstTd = tr.querySelector('td');
        if(firstTd && firstTd.hasAttribute('colspan')){ tr.style.display = q ? 'none' : ''; return; }
        const txt = (tr.textContent || '').replace(/\s+/g,' ').toLowerCase();
        tr.style.display = q ? (txt.indexOf(q) !== -1 ? '' : 'none') : '';
      });
    }

    const debouncedApply = debounce(applyFilter, 240);
    input.addEventListener('input', debouncedApply);
    document.getElementById('manageSearchClear')?.addEventListener('click', ()=>{ input.value = ''; applyFilter(); input.focus(); });

    // Re-apply filter when table is updated by loadManageAppointments
    const tbody = document.querySelector('#manageTable tbody');
    if(tbody){
      const mo = new MutationObserver(()=>{ try{ applyFilter(); }catch(e){} });
      mo.observe(tbody, { childList: true, subtree: true });
    }

    // on Enter, focus the first visible action button in the filtered list
    input.addEventListener('keydown', function(e){ if(e.key === 'Enter'){ e.preventDefault(); const tbody = document.querySelector('#manageTable tbody'); if(!tbody) return; const rows = Array.from(tbody.querySelectorAll('tr')).filter(r=> r.style.display !== 'none'); if(rows.length){ const btn = rows[0].querySelector('button[data-action="view"]') || rows[0].querySelector('button'); if(btn) btn.focus(); } } });
  })();

        function viewMedicalFile(url, filename){
          if(!url){ alert('No file URL available'); return; }
          const modal = document.getElementById('filePreviewModal');
          const content = document.getElementById('filePreviewContent');
          const title = document.getElementById('filePreviewTitle');
          const download = document.getElementById('filePreviewDownload');
          if(!modal || !content){ window.open(url, '_blank'); return; }
          title.textContent = filename || url.split('/').pop() || 'File';
          if(download) download.href = url;
          
          content.innerHTML = '';
          const lower = String(url).toLowerCase();
          (async ()=>{
            let ok = false;
            try{ const h = await fetch(url, { method: 'HEAD', credentials: 'include' }); ok = h && h.ok; }catch(_){ ok = false; }
            if(!ok){ content.innerHTML = '<div style="padding:20px;color:var(--muted);text-align:center">File not found or inaccessible. Use Download to open it directly.</div>'; try{ window['__lastFocusedBefore_'+(modal.id||'')] = document.activeElement; }catch(_){} modal.style.display='flex'; modal.setAttribute('aria-hidden','false'); try{ const btn = document.getElementById('filePreviewClose'); if(btn) btn.focus(); }catch(e){} return; }
            try{
              if(lower.endsWith('.pdf')){
                const iframe = document.createElement('iframe'); iframe.src = url; iframe.style.width='100%'; iframe.style.height='100%'; iframe.style.border='0'; content.appendChild(iframe);
              } else if(lower.match(/\.(jpg|jpeg|png|gif|bmp|webp)$/)){
                const img = document.createElement('img'); img.src = url; img.style.maxWidth='100%'; img.style.maxHeight='100%'; img.style.display='block'; content.appendChild(img);
              } else {
                const a = document.createElement('a'); a.href = url; a.target = '_blank'; a.textContent = 'Open file in new tab'; content.appendChild(a);
              }
            }catch(err){ const a = document.createElement('a'); a.href = url; a.target = '_blank'; a.textContent = 'Open file in new tab'; content.appendChild(a); }
            try{ window['__lastFocusedBefore_'+(modal.id||'')] = document.activeElement; }catch(_){} modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false'); try{ const btn = document.getElementById('filePreviewClose'); if(btn) btn.focus(); }catch(e){}
          })();
        }
        </script>
      </section>
      <?php endif; ?>

    </main>
  </div>

<!-- Patient info modal (expanded, same layout as doctor portal) -->
<div id="patientInfoModal" style="display:none;position:fixed;inset:0;align-items:center;justify-content:center;background:rgba(0,0,0,0.45);z-index:1200">
  <div style="background:#fff;padding:18px;border-radius:10px;max-width:860px;width:96%;margin:auto;box-shadow:0 18px 60px rgba(0,0,0,0.28)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <h3 style="margin:0;color:var(--lav-4)">Patient Details</h3>
      <div style="display:flex;gap:8px;align-items:center">
        <!-- View Appointments button removed -->
        <button id="patientInfoClose" class="btn-pill ghost" style="padding:6px 10px">Close</button>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px">
      <div><strong>Name</strong><div id="mi_name" style="color:var(--muted);margin-top:6px"></div></div>
      <div><strong>Age</strong><div id="mi_age" style="color:var(--muted);margin-top:6px"></div></div>

      <div><strong>Civil Status</strong><div id="mi_civil_status" style="color:var(--muted);margin-top:6px"></div></div>
      <div><strong>Nationality</strong><div id="mi_nationality" style="color:var(--muted);margin-top:6px"></div></div>

      <div><strong>Email</strong><div id="mi_email" style="color:var(--muted);margin-top:6px"></div></div>
      <div><strong>Religion</strong><div id="mi_religion" style="color:var(--muted);margin-top:6px"></div></div>

      <div><strong>Mobile</strong><div id="mi_mobile" style="color:var(--muted);margin-top:6px"></div></div>
      <div><strong>Blood Type</strong><div id="mi_blood_type" style="color:var(--muted);margin-top:6px"></div></div>

      <div style="grid-column:1 / -1"><strong>Allergies</strong><div id="mi_allergies" style="color:var(--muted);margin-top:6px"></div></div>
      <div style="grid-column:1 / -1"><strong>Past Medical Conditions</strong><div id="mi_past_medical" style="color:var(--muted);margin-top:6px"></div></div>
      <div style="grid-column:1 / -1"><strong>Current Medications</strong><div id="mi_current_medications" style="color:var(--muted);margin-top:6px"></div></div>

      <div style="grid-column:1 / -1"><strong>Obstetric History</strong><div id="mi_obstetric" style="color:var(--muted);margin-top:6px"></div></div>

      <div><strong>Number of Pregnancies</strong><div id="mi_pregnancies" style="color:var(--muted);margin-top:6px"></div></div>
      <div><strong>Number of Deliveries</strong><div id="mi_deliveries" style="color:var(--muted);margin-top:6px"></div></div>

      <div><strong>Last Menstrual Period</strong><div id="mi_lmp" style="color:var(--muted);margin-top:6px"></div></div>
      <div><strong>Expected Delivery Date</strong><div id="mi_edd" style="color:var(--muted);margin-top:6px"></div></div>

      <div style="grid-column:1 / -1"><strong>Previous Pregnancy Complication</strong><div id="mi_prev_preg_complication" style="color:var(--muted);margin-top:6px"></div></div>
    </div>
  </div>
</div>

<!-- Action Confirm Modal -->
<div id="actionConfirmModal" class="confirm-modal" aria-hidden="true" style="display:none">
  <div class="dialog" role="dialog" aria-modal="true" style="max-width:420px;width:92%">
    <header style="display:flex;justify-content:space-between;align-items:center">
      <h4 id="actionConfirmTitle" style="margin:0;color:var(--lav-4)">Confirm</h4>
      <button class="btn-cancel" type="button" id="actionConfirmClose">Close</button>
    </header>
    <div style="margin-top:12px">
      <p id="actionConfirmMessage" style="color:var(--muted);white-space:pre-wrap"></p>
      <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
        <button id="actionConfirmCancelBtn" class="btn-cancel">Cancel</button>
        <button id="actionConfirmOkBtn" class="btn-pill">Confirm</button>
      </div>
    </div>
  </div>
</div>

      <!-- Prescription View Modal -->
      <div id="prescriptionViewModal" class="confirm-modal" aria-hidden="true" style="display:none">
        <div class="dialog" role="dialog" aria-modal="true" style="max-width:900px;width:94%;height:90%;display:flex;flex-direction:column">
          <header style="display:flex;justify-content:space-between;align-items:center">
            <h4 style="margin:0;color:var(--lav-4)">Result File</h4>
            <button class="profile-pic-close btn-cancel" id="prescriptionViewClose">Close</button>
          </header>
          <div id="prescriptionViewBody" style="flex:1;overflow:auto;background:#fff;border-radius:6px;margin-top:8px;display:flex;align-items:center;justify-content:center;padding:8px">
            <!-- content injected dynamically: iframe for PDF, img for images, or download link -->
          </div>
          <div style="text-align:right;margin-top:8px">
            <a id="prescriptionDownloadLink" href="#" class="btn-pill ghost" target="_blank" style="margin-right:8px">Download</a>
            <button class="btn-pill" id="prescriptionViewDone">Close</button>
          </div>
        </div>
      </div>

<script>
/* Utility: escape HTML */
function escapeHtml(s){ if(s===null||s===undefined) return ''; return String(s).replace(/[&<>"'`]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'}[c])); }

// Inline/floating lab upload message helper (floats next to file input)
let _labMsgTimeout = null;
function setLabMessage(msg, type){
  try{
    // remove any existing floating message
    const existing = document.getElementById('labFloatingMsg');
    if(existing) existing.remove();

    // target the visible file button/input
    const target = document.getElementById('labFileBtn') || document.getElementById('labFile');
    // create floating container
    const wrap = document.createElement('div'); wrap.id = 'labFloatingMsg';
    wrap.setAttribute('role','alert');
    Object.assign(wrap.style, {
      position: 'absolute',
      zIndex: 1600,
      padding: '8px 12px',
      borderRadius: '8px',
      display: 'flex',
      alignItems: 'center',
      gap: '8px',
      boxShadow: '0 8px 20px rgba(16,24,40,0.12)',
      fontSize: '0.95rem',
      maxWidth: '320px'
    });

    // icon
    const icon = document.createElement('span'); icon.style.display='inline-flex'; icon.style.alignItems='center'; icon.style.justifyContent='center'; icon.style.width='28px'; icon.style.height='28px'; icon.style.borderRadius='6px'; icon.style.flex='0 0 auto';
    const txt = document.createElement('div'); txt.textContent = msg || '';

    if(type === 'error'){
      wrap.style.background = '#fff3f0'; wrap.style.border = '1px solid #f4c6c6'; wrap.style.color = '#7a221f'; icon.textContent = '!'; icon.style.background = '#ffb86b';
    } else if(type === 'success'){
      wrap.style.background = '#f0fff6'; wrap.style.border = '1px solid #bdeccf'; wrap.style.color = '#064c2a'; icon.textContent = '✓'; icon.style.background = '#bdeccf';
    } else {
      wrap.style.background = '#fffbf0'; wrap.style.border = '1px solid #f3e0b8'; wrap.style.color = '#5b3f00'; icon.textContent = 'i'; icon.style.background = '#ffd86b';
    }
    icon.style.fontWeight = '700'; icon.style.color = '#fff';
    wrap.appendChild(icon); wrap.appendChild(txt);

    // position near target
    if(target && target.getBoundingClientRect){
      const rect = target.getBoundingClientRect();
      document.body.appendChild(wrap);
      // default placement below the element, centered
      const left = Math.max(8, rect.left + (rect.width/2) - 140);
      const top = rect.bottom + 10 + window.scrollY;
      wrap.style.left = `${left}px`;
      wrap.style.top = `${top}px`;
    } else {
      // fallback: place near progress area
      const prog = document.getElementById('labUploadProgressWrap');
      if(prog && prog.getBoundingClientRect){ const r = prog.getBoundingClientRect(); document.body.appendChild(wrap); wrap.style.left = `${r.left}px`; wrap.style.top = `${r.bottom + 8 + window.scrollY}px`; }
      else { document.body.appendChild(wrap); wrap.style.position='fixed'; wrap.style.right='18px'; wrap.style.bottom='18px'; }
    }

    // auto-hide
    if(_labMsgTimeout) clearTimeout(_labMsgTimeout);
    _labMsgTimeout = setTimeout(()=>{ try{ document.getElementById('labFloatingMsg')?.remove(); }catch(e){} }, 5200);

    // dismiss on click
    wrap.addEventListener('click', ()=>{ try{ wrap.remove(); if(_labMsgTimeout) clearTimeout(_labMsgTimeout); }catch(e){} });
  }catch(e){ console.error('setLabMessage error', e); }
}

/* Lightweight toast notifications (used across this page) */
function showToast(message, type = 'info', timeout = 3500){
  try{
    // create container if missing
    let container = document.getElementById('globalToastContainer');
    if(!container){
      container = document.createElement('div');
      container.id = 'globalToastContainer';
      Object.assign(container.style, {
        position: 'fixed',
        right: '18px',
        bottom: '18px',
        zIndex: 1400,
        display: 'flex',
        flexDirection: 'column',
        gap: '8px',
        alignItems: 'flex-end',
        pointerEvents: 'none'
      });
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'app-toast';
    toast.setAttribute('role','status');
    // base styles
    Object.assign(toast.style, {
      pointerEvents: 'auto',
      minWidth: '180px',
      maxWidth: '420px',
      padding: '10px 14px',
      borderRadius: '10px',
      color: '#123',
      boxShadow: '0 10px 28px rgba(16,24,40,0.12)',
      fontSize: '0.95rem',
      lineHeight: '1.2',
      transform: 'translateY(12px)',
      opacity: '0',
      transition: 'transform 220ms ease, opacity 220ms ease'
    });

    // color by type
    if(type === 'success'){
      toast.style.background = '#e9fff2';
      toast.style.border = '1px solid #bdeccf';
      toast.style.color = '#064c2a';
    } else if(type === 'error'){
      toast.style.background = '#fff3f3';
      toast.style.border = '1px solid #f4c6c6';
      toast.style.color = '#601212';
    } else if(type === 'warn' || type === 'warning'){
      toast.style.background = '#fffbf0';
      toast.style.border = '1px solid #f3e0b8';
      toast.style.color = '#5b3f00';
    } else {
      toast.style.background = '#f6f6ff';
      toast.style.border = '1px solid #e1dbff';
      toast.style.color = '#2e1576';
    }

    toast.textContent = message || '';

    // close on click
    toast.addEventListener('click', ()=>{
      try{ toast.style.opacity = '0'; toast.style.transform = 'translateY(12px)'; setTimeout(()=>{ try{ toast.remove(); }catch(e){} }, 260); }catch(e){}
    });

    container.appendChild(toast);
    // force reflow then show
    requestAnimationFrame(()=>{ toast.style.opacity = '1'; toast.style.transform = 'translateY(0)'; });

    // auto remove
    setTimeout(()=>{
      try{ toast.style.opacity = '0'; toast.style.transform = 'translateY(12px)'; setTimeout(()=>{ try{ toast.remove(); }catch(e){} }, 260); }catch(e){}
    }, timeout);
    return toast;
  }catch(e){ try{ console.error('showToast error', e); }catch(_){} }
}

// Wire patient info modal close button with proper focus/aria handling
try{
  (function(){
    const closeBtn = document.getElementById('patientInfoClose');
    function hidePatientModal(){
      try{
        const modal = document.getElementById('patientInfoModal');
        if(!modal) return;
        // restore focus to the element that had it before modal opened
        try{ const prev = window.__lastFocusedBeforePatientModal; if(prev && typeof prev.focus === 'function'){ prev.focus(); } }catch(e){}
        // hide and mark hidden for assistive tech
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden','true');
        // clear stored ref
        try{ window.__lastFocusedBeforePatientModal = null; }catch(e){}
      }catch(e){ /* ignore */ }
    }

    if(closeBtn){ closeBtn.addEventListener('click', function(e){ e.preventDefault(); hidePatientModal(); }); }

    // also allow clicking outside modal to close (respect focus restore)
    document.addEventListener('click', function(e){
      try{
        const modal = document.getElementById('patientInfoModal'); if(!modal) return;
        if(modal.style.display !== 'flex') return;
        const dialog = modal.querySelector('div'); if(dialog && !dialog.contains(e.target)){
          hidePatientModal();
        }
      }catch(err){}
    });
  })();
}catch(e){ /* ignore wiring errors */ }

// Promise-based confirmation dialog using the Action Confirm Modal (fallbacks to native confirm())
function showConfirmDialog(title, message){
  return new Promise((resolve)=>{
    try{
      const modal = document.getElementById('actionConfirmModal');
      const msgEl = document.getElementById('actionConfirmMessage');
      const titleEl = document.getElementById('actionConfirmTitle');
      const okBtn = document.getElementById('actionConfirmOkBtn');
      const cancelBtn = document.getElementById('actionConfirmCancelBtn');
      const closeBtn = document.getElementById('actionConfirmClose');
      if(!modal || !okBtn || !cancelBtn){
        // fallback
        const ok = confirm(message || (title || 'Are you sure?'));
        resolve(!!ok);
        return;
      }
      titleEl && (titleEl.textContent = title || 'Confirm');
      msgEl && (msgEl.textContent = message || 'Are you sure?');
      modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');

      function cleanup(){
        try{ modal.style.display = 'none'; modal.setAttribute('aria-hidden','true'); }catch(_){}
        okBtn.removeEventListener('click', onOk);
        cancelBtn.removeEventListener('click', onCancel);
        closeBtn.removeEventListener('click', onCancel);
        document.removeEventListener('keydown', onKey);
      }
      function onOk(){ cleanup(); resolve(true); }
      function onCancel(){ cleanup(); resolve(false); }
      function onKey(e){ if(e.key === 'Escape') onCancel(); }

      okBtn.addEventListener('click', onOk);
      cancelBtn.addEventListener('click', onCancel);
      closeBtn.addEventListener('click', onCancel);
      document.addEventListener('keydown', onKey);
    }catch(err){ console.error('showConfirmDialog error', err); const ok = confirm(message || (title || 'Are you sure?')); resolve(!!ok); }
  });
}

/* Format a YYYY-MM-DD date into DD/MM/YYYY for display. Returns empty string for falsy/invalid input. */
function formatDateToDisplay(d){ if(!d) return ''; try{ const parts = d.split('-'); if(parts.length!==3) return d; return `${parts[2]}/${parts[1]}/${parts[0]}`; } catch(e){ return d; } }

/* Format a datetime string like "YYYY-MM-DD HH:MM:SS" or ISO into "DD/MM/YYYY h:MM AM/PM" */
function formatDateTimeToDisplay(dt){ if(!dt) return ''; try{
    // normalize separator (accept space or T)
    const s = String(dt).trim();
    // try to split date and time
    let datePart = s, timePart = '';
    if(s.indexOf('T') !== -1){ [datePart, timePart] = s.split('T'); }
    else if(s.indexOf(' ') !== -1){ [datePart, timePart] = s.split(' '); }
    else { datePart = s; timePart = ''; }
    const dParts = datePart.split('-');
    if(dParts.length !== 3) return s;
    const yyyy = Number(dParts[0]) || 0;
    const mm = Number(dParts[1]) - 1;
    const dd = Number(dParts[2]) || 0;
    let hh = 0, min = 0, sec = 0;
    if(timePart){
      const t = timePart.split(':');
      hh = Number(t[0]) || 0; min = Number(t[1]) || 0; sec = Number(t[2]) || 0;
    }
    const dtObj = new Date(yyyy, mm, dd, hh, min, sec);
    if(isNaN(dtObj.getTime())) return s;
    const hours = dtObj.getHours();
    const minutes = String(dtObj.getMinutes()).padStart(2,'0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    const hour12 = ((hours + 11) % 12) + 1;
    const dateFmt = `${String(dd).padStart(2,'0')}/${String(dParts[1])}/${String(yyyy)}`;
    return `${dateFmt} ${hour12}:${minutes} ${ampm}`;
  } catch(e){ return dt; } }

/* Format a time string like "HH:MM" or "HH:MM:SS" into "h:MM AM/PM" */
function formatTimeToDisplay(t){ if(!t) return ''; try{
    const s = String(t).trim();
    const parts = s.split(':');
    if(parts.length < 1) return s;
    const hh = Number(parts[0]) || 0;
    const mm = parts.length > 1 ? Number(parts[1]) : 0;
    const ampm = hh >= 12 ? 'PM' : 'AM';
    const hour12 = ((hh + 11) % 12) + 1;
    return `${hour12}:${String(mm).padStart(2,'0')} ${ampm}`;
  }catch(e){ return t; } }

/* Panel switching from sidebar */
const navItems = document.querySelectorAll('nav.sidebar .nav-item');
const panels = {
  overview: document.getElementById('panel-overview'),
  manage: document.getElementById('panel-manage'),
  newborn: document.getElementById('panel-newborn'),
  medical: document.getElementById('panel-medical'),
  prescriptions: document.getElementById('panel-prescriptions'),
  inventory: document.getElementById('panel-inventory'),
  payments: document.getElementById('panel-payments'),
  patients: document.getElementById('panel-patients'),
  lab: document.getElementById('panel-lab'), // optional, if you add the panel
  photoshoot: document.getElementById('panel-photoshoot')
};
navItems.forEach(item=>{
  item.addEventListener('click', ()=>{
    navItems.forEach(n=>n.classList.toggle('active', n===item));
    const key = item.dataset.panel;
    Object.keys(panels).forEach(k => {
      panels[k] && (panels[k].hidden = (k !== key));
    });
    // load appropriate data when switching panels
    if(key === 'overview') loadOverviewAppointments();
    if(key === 'manage') loadManageAppointments();
    if(key === 'newborn'){
      // ensure patient mapping is ready so we can display mother names
      try { populateNewbornPatientSelect(); } catch(e){}
      loadNewborns();
    }
    if(key === 'patients') loadPatients();
    if(key === 'medical') loadMedicalRecords();
    if(key === 'medical') loadMedicalPatientsFromAppointments();
    if(key === 'prescriptions') loadPrescriptions();
    if(key === 'inventory' && typeof loadInventory === 'function') loadInventory();
    if(key === 'payments'){
      try{ if(typeof loadPayments === 'function') loadPayments(); }catch(_){ }
    }
    if(key === 'lab') loadLabResults && loadLabResults();
    if(key === 'photoshoot') try{ if(typeof loadPhotoshootPatients === 'function') loadPhotoshootPatients(); if(typeof loadPhotoshootList === 'function') loadPhotoshootList(); }catch(e){}
    // Reset scroll position so content starts at top when switching panels
    try{ window.scrollTo({ top: 0, left: 0, behavior: 'auto' }); document.documentElement.scrollTop = 0; document.body.scrollTop = 0; }catch(e){}
  });
});

// Activate a panel on initial load when `?panel=` is provided (or default to 'patients')
(function(){
  try{
    var serverRequested = '<?php echo isset($_GET["panel"]) ? addslashes($_GET["panel"]) : ''; ?>';
    var requested = '';
    if(serverRequested) requested = serverRequested;
    else {
      // fall back to URL param on the client side
      try{ const params = new URLSearchParams(window.location.search); requested = params.get('panel') || ''; }catch(e){}
    }
    if(!requested) requested = 'patients';
    const target = document.querySelector(`nav.sidebar .nav-item[data-panel="${requested}"]`);
    if(target){
      // simulate click to reuse existing handler
      target.click();
    } else {
      // if requested panel not found, fall back to patients
      const fallback = document.querySelector('nav.sidebar .nav-item[data-panel="patients"]');
      if(fallback) fallback.click();
    }
  }catch(e){ console.error('Error activating initial panel', e); }
})();

/* Helper: today's date in YYYY-MM-DD */
function todayDateString(){
  const t = new Date();
  const yyyy = t.getFullYear();
  const mm = String(t.getMonth()+1).padStart(2,'0');
  const dd = String(t.getDate()).padStart(2,'0');
  return `${yyyy}-${mm}-${dd}`;
}

/* ---------- Appointments (use get_appointments.php endpoints) ---------- */
/* Load appointments for overview (today's appointments) */
async function loadOverviewAppointments(){
  const tbody = document.querySelector('#overviewTable tbody');
  if(!tbody){ console.warn('Overview table not present on this page — skipping loadOverviewAppointments'); return; }
  tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Loading appointments…</td></tr>`;
  const date = todayDateString();
  try {
    // ensure credentials are always included so session cookies are sent
    const res = await fetch(`get_appointments.php?date=${encodeURIComponent(date)}`, { credentials: 'include' });
    if(!res.ok){
      const txt = await res.text().catch(()=>'(no body)');
      console.error('get_appointments.php responded with', res.status, txt);
      throw new Error('Network response not ok: ' + res.status);
    }
    let data;
    try{ data = await res.json(); } catch(parseErr){ const txt = await res.text().catch(()=>'(no body)'); console.error('Failed parsing JSON from get_appointments.php:', parseErr, txt); throw parseErr; }
    if(!data.success || !Array.isArray(data.appointments)) {
      tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">No appointments found.</td></tr>`;
      return;
    }

    // Accept both server date/time naming conventions (date/time OR appointment_date/appointment_time)
    const rows = data.appointments
      .filter(a => ((a.visible === undefined) || Number(a.visible) === 1) && ((typeof a.date === 'string') || (typeof a.appointment_date === 'string')))
      .sort((a,b) => {
        const aDate = (a.date || a.appointment_date || '') + ' ' + (a.time || a.appointment_time || '');
        const bDate = (b.date || b.appointment_date || '') + ' ' + (b.time || b.appointment_time || '');
        return bDate.localeCompare(aDate);
      });

    if(rows.length === 0){
      tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">No appointments for today.</td></tr>`;
      return;
    }

    tbody.innerHTML = rows.map(a=>{
      const id = escapeHtml(a.id || '');
      const patientName = escapeHtml(a.patient_name || a.patient || 'Unknown');
      const phone = escapeHtml(a.mobile_number || a.cellphone || '');
      const service = escapeHtml(a.service || '—');
      const dateText = escapeHtml(a.date || a.appointment_date || '');
      const timeRaw = (a.time || a.appointment_time || '');
      const timeText = escapeHtml(formatTimeToDisplay(timeRaw));
      const schedule = `${dateText}${timeText ? ' • ' + timeText : ''}`;
      const clinic = escapeHtml(a.address || a.clinic || '—');
      const rawStatus = (a.status || 'pending');
      const status = String(rawStatus).toLowerCase();

      // detect cancellations by patient using returned cancelled_by flag, cancelled_at timestamp, or status text
      const isCancelledByPatient = ((a.cancelled_by !== undefined && a.cancelled_by !== null && String(a.cancelled_by) !== '')
                || (a.cancelled_at !== undefined && a.cancelled_at !== null && String(a.cancelled_at).trim() !== '')
                || (status.includes('cancel') && status.includes('patient')));

      // normalize status for subsequent checks so cancelled-by-patient is treated as 'cancelled'
      const statusForChecks = isCancelledByPatient ? 'cancelled' : status;

      // determine completed state (date has passed and appointment not cancelled)
      const aptDate = (a.date || a.appointment_date || '');
      const todayStr = date; // from outer scope: today's date string YYYY-MM-DD
      const isCompleted = (statusForChecks.indexOf('completed') !== -1) || (aptDate && todayStr && aptDate < todayStr && statusForChecks.indexOf('cancel') === -1);

      let statusHtml = '';
      if (isCancelledByPatient) {
        statusHtml = `<span class="status-badge status-cancelled">Cancelled by patient</span>`;
      } else if (isCompleted) {
        statusHtml = `<span class="status-badge status-completed">Completed</span>`;
      } else {
        statusHtml = statusForChecks.includes('confirm') ? `<span class="status-badge status-confirmed">${escapeHtml(rawStatus)}</span>` :
                     statusForChecks.includes('cancel') ? `<span class="status-badge status-cancelled">${escapeHtml(rawStatus)}</span>` :
                     `<span class="status-badge status-pending">${escapeHtml(rawStatus)}</span>`;
      }

      const viewBtn = `<button class="btn btn-view" data-action="view" data-id="${id}">View</button>`;
      // only show action buttons when not completed and not cancelled
      // hide action buttons for midwife view — midwives only view schedules
      const showActions = false;
      const acceptBtn = showActions ? `<button class="btn btn-approve" data-action="confirm" data-id="${id}">Accept</button>` : '';
      const declineBtn = showActions ? `<button class="btn btn-decline" data-action="cancel" data-id="${id}">Decline</button>` : '';

      const rowClass = isCancelledByPatient ? 'cancelled-by-patient' : '';

      return `<tr data-id="${id}" class="${rowClass}">
                <td>${patientName}${phone ? `<div style="color:var(--muted);font-size:0.85rem">${phone}</div>` : ''}</td>
                <td>${service}</td>
                <td>${schedule}</td>
                <td style="white-space:nowrap">${viewBtn} ${clinic ? `<div style="color:var(--muted);font-size:0.85rem">${clinic}</div>` : ''}</td>
                <td style="white-space:nowrap">${statusHtml} ${acceptBtn} ${declineBtn}</td>
              </tr>`;
    }).join('');

    // wire buttons
    document.querySelectorAll('#overviewTable button').forEach(btn=>{
      btn.addEventListener('click', async (e)=>{
        const action = e.currentTarget.dataset.action;
        const apptId = e.currentTarget.dataset.id;
        if(!apptId) return;
        if(action === 'view'){
          try{
            const r = await fetch(`get_appointment_details.php?id=${encodeURIComponent(apptId)}`, { credentials: 'include' });
            if(!r.ok){ const t = await r.text().catch(()=>'(no body)'); console.error('get_appointment_details.php error', r.status, t); alert('Failed to load patient details (server error)'); return; }
            let j;
            try{ j = await r.json(); } catch(parseErr){ const t = await r.text().catch(()=>'(no body)'); console.error('Failed parsing JSON from get_appointment_details.php', parseErr, t); alert('Failed to load patient details (invalid response)'); return; }
            if(j.success && j.appointment){
              const ap = j.appointment;
              // populate modal (fields from patient_details)
              const modal = document.getElementById('patientInfoModal');
                // appointment details endpoint may return flattened patient fields (name/mobile_number/etc.)
                document.getElementById('mi_name').textContent = ap.name || ap.patient_name || '-';
                document.getElementById('mi_age').textContent = ap.age || '-';
                document.getElementById('mi_address').textContent = ap.address || '-';
                document.getElementById('mi_bday').textContent = ap.birthday || ap.bday || '-';
                document.getElementById('mi_mobile').textContent = ap.mobile_number || ap.cellphone || '-';
                document.getElementById('mi_email').textContent = ap.email || '-';
                document.getElementById('mi_obstetric').textContent = ap.obstetric_history || ap.obstetric || '-';
                const _mi_notes = document.getElementById('mi_notes'); if(_mi_notes) _mi_notes.textContent = ap.notes || '-';
              // open modal accessibly: save previous focus, expose to AT, and move focus to close button
              try{ window.__lastFocusedBeforePatientModal = document.activeElement; }catch(e){}
              modal.style.display = 'flex';
              modal.setAttribute('aria-hidden','false');
              try{ const closeBtn = document.getElementById('patientInfoClose'); if(closeBtn) closeBtn.focus(); }catch(e){}
            } else {
              alert('Unable to load patient details.');
            }
          } catch(err){ console.error(err); alert('Network error while fetching details.'); }
          return;
        }

        // accept / decline (use modal dialog)
        if(action === 'confirm' || action === 'cancel'){
          const confirmText = action === 'confirm' ? 'Accept this appointment?' : 'Decline this appointment?';
          const ok = await showConfirmDialog('Confirm', confirmText);
          if(!ok) return;
          try{
            const statusToSend = action === 'confirm' ? 'Confirmed' : 'Cancelled by midwife';
            const r = await fetch('update_appointment_status.php', { method:'POST', credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ appointment_id: apptId, status: statusToSend }) });
            if(!r.ok){ const t = await r.text().catch(()=>'(no body)'); console.error('update_appointment_status.php error', r.status, t); showToast('Failed to update appointment (server error)', 'error'); return; }
            const jr = await r.json();
            if(jr.success){ await loadOverviewAppointments(); await loadManageAppointments(); } else showToast('Failed: ' + (jr.message||'error'), 'error');
          } catch(err){ console.error(err); showToast('Network error', 'error'); }
        }
      });
    });

  } catch (err) {
    console.error('Failed loading overview appointments', err);
    tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Failed to load appointments.</td></tr>`;
  }
}

/* Load appointments for management (all upcoming) */
async function loadManageAppointments(userId){
  const tbody = document.querySelector('#manageTable tbody');
  tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Loading appointments…</td></tr>`;
  try {
    // fetch appointments (optionally filter by userId)
    let url = 'get_manage_appointments.php';
    if (userId) url += '?user_id=' + encodeURIComponent(userId);
    // use include so cookies/sessions are sent in different browser contexts
    const res = await fetch(url, { credentials: 'include' });
    if (!res.ok) {
      // read response body for debugging (may contain PHP error or helpful message)
      const body = await res.text().catch(() => '(no body)');
      console.error('get_appointments.php returned non-OK', res.status, res.statusText, body);
      if (res.status === 401) {
        tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Not authenticated — please log in.</td></tr>`;
        return;
      }
      if (res.status === 403) {
        tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Permission denied.</td></tr>`;
        return;
      }
      tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Failed to load appointments (server error).</td></tr>`;
      return;
    }
    let data;
    try {
      data = await res.json();
    } catch (parseErr) {
      const txt = await res.text().catch(()=>'(no body)');
      console.error('Failed parsing JSON from get_appointments.php:', parseErr, txt);
      tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Invalid server response while loading appointments.</td></tr>`;
      return;
    }
    if(!data.success || !Array.isArray(data.appointments)) {
      tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">No appointments found.</td></tr>`;
      return;
    }

    // sort appointments so the most recently BOOKED appointment appears first for active/upcoming rows,
    // but ensure completed appointments are pushed to the bottom of the list.
    // prefer explicit booking timestamps if available (booked_at/created_at/created/booked),
    // otherwise fall back to appointment date/time (descending).
    let rows = data.appointments.slice().sort((a, b) => {
      // determine if each appointment should be considered 'completed'
      const todayNow = (typeof todayDateString === 'function') ? todayDateString() : null;
      const aAptDate = a.date || a.appointment_date || '';
      const bAptDate = b.date || b.appointment_date || '';
      const aStatus = String(a.status || '').toLowerCase();
      const bStatus = String(b.status || '').toLowerCase();
      const aCancelled = !!(a.cancelled_by || a.cancelled_at || (aStatus && /cancel/i.test(aStatus)));
      const bCancelled = !!(b.cancelled_by || b.cancelled_at || (bStatus && /cancel/i.test(bStatus)));
      const aCompleted = (!aCancelled && aAptDate && todayNow && aAptDate < todayNow) || aStatus.includes('completed');
      const bCompleted = (!bCancelled && bAptDate && todayNow && bAptDate < todayNow) || bStatus.includes('completed');

      // push completed appointments to the end
      if (aCompleted && !bCompleted) return 1;
      if (!aCompleted && bCompleted) return -1;

      // both completed or both not completed — apply booking/date ordering
      const aBooked = a.booked_at || a.created_at || a.created || a.booked || '';
      const bBooked = b.booked_at || b.created_at || b.created || b.booked || '';
      try {
        if (aBooked && bBooked) {
          const ta = Date.parse(String(aBooked)) || 0;
          const tb = Date.parse(String(bBooked)) || 0;
          if (ta !== tb) return tb - ta; // newer bookings first
        } else if (aBooked && !bBooked) {
          return -1; // a has booking timestamp, put it before b
        } else if (!aBooked && bBooked) {
          return 1;
        }
      } catch (e) {
        // ignore parse errors and fall through to appointment date fallback
      }

      // fallback: compare appointment date/time (descending)
      const aDate = (a.date || a.appointment_date || '') + ' ' + (a.time || a.appointment_time || '');
      const bDate = (b.date || b.appointment_date || '') + ' ' + (b.time || b.appointment_time || '');
      const pa = Date.parse(aDate) || 0;
      const pb = Date.parse(bDate) || 0;
      if (pa !== pb) return pb - pa;
      return String(bDate).localeCompare(String(aDate));
    });
    // remove appointments with no identifiable patient (avoid showing 'Unknown' rows)
    rows = rows.filter(a => {
      const name = a.patient_name || (a.patient && a.patient.name) || a.patient || '';
      if(!name) return false;
      const s = String(name).trim().toLowerCase();
      if(!s) return false;
      if(s === 'unknown' || s === 'n/a' || s === 'anonymous') return false;
      return true;
    });

    // Auto-complete past appointments that are confirmed.
    try{
      const todayNow = todayDateString();
      const toComplete = [];
      rows.forEach(a => {
        const aptDate = a.date || a.appointment_date || '';
        const status = String(a.status || '').toLowerCase();
        const isCancelled = !!(a.cancelled_by || a.cancelled_at || (status && /cancel/i.test(status)));
        const isConfirmed = status.includes('confirm');
        if(aptDate && todayNow && aptDate < todayNow && isConfirmed && !isCancelled){
          const id = a.id || a.appointment_id || a.appointmentId || '';
          if(id) toComplete.push(id);
        }
      });
      if(toComplete.length){
        await Promise.all(toComplete.map(id => fetch('update_appointment_status.php', { method: 'POST', credentials: 'include', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ appointment_id: id, status: 'completed' }) }).catch(()=>null)));
        // reflect change locally so the UI shows Completed immediately
        rows = rows.map(a => { const id = a.id || a.appointment_id || a.appointmentId || ''; if(id && toComplete.indexOf(id) !== -1){ a.status = 'completed'; } return a; });
      }
    }catch(e){ console.error('Auto-complete appointments failed', e); }

    if(rows.length === 0){
      tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">No appointments available.</td></tr>`;
      return;
    }

    tbody.innerHTML = rows.map(a=>{
      const id = escapeHtml(a.id || '');
      const patientName = escapeHtml(a.patient_name || a.patient || 'Unknown');
      const phone = escapeHtml(a.mobile_number || a.cellphone || '');
      const service = escapeHtml(a.service || '—');
      const dateText = escapeHtml(a.date || a.appointment_date || '');
      const timeRaw = (a.time || a.appointment_time || '');
      const timeText = escapeHtml(formatTimeToDisplay(timeRaw));
      
      const rawStatus = a.status || 'pending';
      const status = String(rawStatus).toLowerCase();
      const aptDate = (a.date || a.appointment_date || '');
      const todayNow = todayDateString();

      // detect cancellations made by the patient using returned cancelled_by flag or cancelled_at timestamp,
      // or when the status text explicitly mentions "patient" cancellation
      const isCancelledByPatient = ((a.cancelled_by !== undefined && a.cancelled_by !== null && String(a.cancelled_by) !== '')
                || (a.cancelled_at !== undefined && a.cancelled_at !== null && String(a.cancelled_at).trim() !== '')
                || (status.includes('cancel') && status.includes('patient')));

      // normalize status for subsequent checks so cancelled-by-patient is treated as 'cancelled'
      const statusForChecks = isCancelledByPatient ? 'cancelled' : status;

      // If appointment date already passed and not cancelled, mark Completed
      const isCompleted = (statusForChecks.indexOf('completed') !== -1) || (aptDate && todayNow && aptDate < todayNow && statusForChecks !== 'cancelled');
      const isConfirmed = statusForChecks.includes('confirm');
      let statusHtml = '';
      if (isCancelledByPatient) {
        statusHtml = `<span class="status-badge status-cancelled">Cancelled by patient</span>`;
      } else if (isCompleted) {
        statusHtml = `<span class="status-badge status-completed">Completed</span>`;
      } else {
        statusHtml = isConfirmed ? `<span class="status-badge status-confirmed">${escapeHtml(rawStatus)}</span>` :
                   statusForChecks === 'cancelled' ? `<span class="status-badge status-cancelled">${escapeHtml(rawStatus)}</span>` :
                   `<span class="status-badge status-pending">${escapeHtml(rawStatus)}</span>`;
      }

      // hide action buttons for midwife view — midwives only view schedules
      const showActions = false;
      const approveBtn = showActions ? `<button class="btn btn-approve" data-action="confirm" data-id="${id}">Confirm</button>` : '';
      const approveRescheduleBtn = showActions && a.requested_date ? `<button class="btn" data-action="approve_reschedule" data-id="${id}">Approve Reschedule</button>` : '';
      const cancelBtn  = showActions ? `<button class="btn btn-decline" data-action="cancel" data-id="${id}">Cancel</button>` : '';
      // View button removed
      const rowClass = isCancelledByPatient ? 'cancelled-by-patient' : '';

      return `<tr data-id="${id}" class="${rowClass}">
                <td>${patientName}${phone ? `<div style="color:var(--muted);font-size:0.85rem">${phone}</div>` : ''}</td>
                <td>${service}</td>
                <td>${dateText}</td>
                <td>${timeText}</td>
                <td>${statusHtml}</td>
                <td style="white-space:nowrap">${approveBtn} ${approveRescheduleBtn} ${cancelBtn}</td>
              </tr>`;
    }).join('');

    // wire action buttons
    document.querySelectorAll('#manageTable button').forEach(btn=>{
      btn.addEventListener('click', onManageAction);
    });

  } catch (err) {
    console.error('Failed loading manage appointments', err);
    tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Failed to load appointments.</td></tr>`;
  }
}

/* Handler for approve/cancel/view actions */
async function onManageAction(e){
  const btn = e.currentTarget;
  const action = btn.dataset.action;
  const apptId = btn.dataset.id;
  if(!apptId) return;

  if(action === 'view'){
    // fetch full appointment details from get_appointment_details.php
    try {
      const res = await fetch(`get_appointment_details.php?id=${encodeURIComponent(apptId)}`, { credentials: 'same-origin' });
      if(res.ok){
        const data = await res.json();
        if(data.success && data.appointment){
          const appt = data.appointment;
          const patientObj = appt.patient || {};
          const patientName = patientObj.name || appt.patient_name || 'Unknown';
          const clinic = patientObj.clinic || appt.clinic || '-';
          const phone = patientObj.cellphone || appt.cellphone || '-';
          const address = patientObj.address || appt.address || '-';
          const service = appt.service || '-';
          const date = appt.date || '-';
          const time = appt.time || '-';
          const status = appt.status || '-';
          const notes = appt.notes || '';

          // populate the patient info modal (use same fields as overview handler)
          try{
            const modal = document.getElementById('patientInfoModal');
            if(modal){
              document.getElementById('mi_name').textContent = appt.name || appt.patient_name || patientName || '-';
              document.getElementById('mi_age').textContent = appt.age || '-';
              document.getElementById('mi_address').textContent = appt.address || address || '-';
              document.getElementById('mi_bday').textContent = appt.birthday || appt.bday || '-';
              document.getElementById('mi_mobile').textContent = appt.mobile_number || appt.cellphone || phone || '-';
              document.getElementById('mi_email').textContent = appt.email || '-';
              document.getElementById('mi_obstetric').textContent = appt.obstetric_history || appt.obstetric || '-';
              const _mi_notes = document.getElementById('mi_notes'); if(_mi_notes) _mi_notes.textContent = appt.notes || notes || '-';
              try{ window.__lastFocusedBeforePatientModal = document.activeElement; }catch(e){}
              modal.style.display = 'flex';
              modal.setAttribute('aria-hidden','false');
              try{ const cb = document.getElementById('patientInfoClose'); if(cb) cb.focus(); }catch(e){}
            } else {
              alert([patientName, clinic, phone, address, service, date + ' ' + time, status, notes].join('\n'));
            }
          }catch(err){ console.error('Failed to open patient modal', err); alert('Unable to show patient details.'); }
          return;
        }
      }
    } catch (err) {
      // fallback
    }
    alert('Unable to load full appointment details. If this persists, contact the system administrator.');
    return;
  }

  // confirm action with midwife (use modal)
  const confirmText = action === 'confirm' ? 'mark this appointment as Confirmed' : 'cancel this appointment';
  const proceed = await showConfirmDialog('Confirm', `Are you sure you want to ${confirmText}?`);
  if(!proceed) return;

    try {
      if (action === 'approve_reschedule') {
        const res = await fetch('update_appointment_status.php', {
            method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ appointment_id: apptId, action: 'approve_reschedule' })
          });
          if (!res.ok) {
            const body = await res.text().catch(()=>'(no body)');
            console.error('approve_reschedule failed', res.status, res.statusText, body);
            alert('Server error while approving reschedule. See console for details.');
            return;
          }
          let result;
          try { result = await res.json(); } catch(parseErr){ const t = await res.text().catch(()=>'(no body)'); console.error('Invalid JSON from approve_reschedule', parseErr, t); alert('Invalid server response. See console.'); return; }
          if (result.success) { await loadManageAppointments(); await loadOverviewAppointments(); return; }
          else { alert('Failed: ' + (result.message || 'server error')); return; }
      }
        const newStatus = action === 'confirm' ? 'confirmed' : 'cancelled';
        const res = await fetch('update_appointment_status.php', { method: 'POST', credentials: 'include', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ appointment_id: apptId, status: newStatus }) });
        if (!res.ok) {
          const body = await res.text().catch(()=>'(no body)');
          console.error('update_appointment_status.php returned non-OK', res.status, res.statusText, body);
          if (res.status === 401) { alert('You are not authenticated. Please log in.'); return; }
          if (res.status === 403) { alert('You do not have permission to perform this action.'); return; }
          alert('Server error while updating appointment. See console for details.');
          return;
        }
        let result;
        try { result = await res.json(); } catch (parseErr) { const t = await res.text().catch(()=>'(no body)'); console.error('Invalid JSON from update_appointment_status.php', parseErr, t); alert('Invalid server response. See console.'); return; }
        if (data.success) {
          showToast(data.message || 'Profile saved successfully', 'success');
          if (data.data) {
            window.doctorProfile = data.data;
            try{ populateProfile(data.data); }catch(e){}
          } else {
            // fetch fresh profile (midwife)
            try{
              const gd = await fetch('get_midwife_info.php', { credentials: 'same-origin' });
              if(gd.ok){
                const jd = await gd.json();
                if(jd && jd.success && jd.data){ window.doctorProfile = jd.data; try{ populateProfile(jd.data); }catch(e){} }
              }
            } catch(_){ /* ignore */ }
          }
          closeDoctorCustomizeModal();
        } else {
          showToast('Error: ' + (data.message || 'Could not save'), 'error');
        }
      } catch (err) {
        console.error('Network/fetch error while saving midwife details:', err);
        showToast('Network error: ' + (err && err.message ? err.message : 'Failed to send request'), 'error');
      } finally {
        if(submitBtn) submitBtn.disabled = false;
      }
}

/* ---------- Newborns (form + listing) ---------- */
document.getElementById('newNewbornBtn')?.addEventListener('click', ()=> {
  document.getElementById('newbornForm').style.display = 'block';
  // ensure patient select is populated when opening the form
  populateNewbornPatientSelect();
});
document.getElementById('cancelNewbornBtn')?.addEventListener('click', ()=> {
  document.getElementById('newbornForm').style.display = 'none';
});
document.getElementById('saveNewbornBtn')?.addEventListener('click', async ()=>{
  const form = document.getElementById('formNewborn');
  const data = Object.fromEntries(new FormData(form).entries());
  // ensure patient_user_id is present
  if(!data.patient_user_id){ alert('Please choose a patient to associate this newborn with.'); return; }
  try {
    const res = await fetch('save_newborn_clean.php', {
      method:'POST', credentials:'same-origin',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(data)
    });

    // read raw response text first so we can show HTML/PHP errors if any
    const rawText = await res.text().catch(()=>null);
    let j = null;
    try {
      j = rawText ? JSON.parse(rawText) : null;
    } catch(parseErr){
      console.error('save_newborn_clean.php returned non-JSON response', res.status, rawText);
      alert('Server error while saving newborn. See developer console for details.');
      return;
    }

    if(!j){
      alert('Empty response from server. See console for details.');
      return;
    }
    if(j.success){
      // clear edit id after successful save
      const idField = document.getElementById('newborn_record_id'); if(idField) idField.value = '';
      form.reset();
      document.getElementById('newbornForm').style.display = 'none';
      // reset Save button text in case it was 'Update Newborn'
      const saveBtn = document.getElementById('saveNewbornBtn'); if(saveBtn) saveBtn.textContent = 'Save Newborn';
      await loadNewborns();
      // refresh screening select after newborns change
      try{ populateScreeningNewbornSelect(); }catch(e){ console.error('populateScreeningNewbornSelect error', e); }
    } else {
      alert('Save failed: ' + (j.message||'server error'));
    }
  } catch(err){ console.error(err); alert('Network error'); }
});

// Map of patient_id => patient object (name, etc.) used to render mother name in lists
let patientsMap = {};
// map of newborn id/code => newborn object (populated by loadNewborns)
let newbornsMap = {};

async function populateNewbornPatientSelect(){
  const sel = document.getElementById('newbornPatientSelect');
  const motherInput = document.getElementById('newbornMotherName');
  if(!sel) return;
  sel.innerHTML = '<option value="">-- choose patient --</option>';
  try{
    const res = await fetch('get_patients.php', { credentials: 'include' });
    if(!res.ok){ console.error('get_patients.php failed', res.status); return; }
    const j = await res.json();
    if(!j.success || !Array.isArray(j.patients)) return;
    patientsMap = {};
    j.patients.forEach(p => {
      const id = p.user_id || p.id || p.userId || p.user_id;
      const name = p.name || p.patient_name || p.full_name || '';
      if(id == null) return;
      patientsMap[id] = p;
      const opt = document.createElement('option');
      opt.value = id;
      opt.textContent = name || (`#${id}`);
      sel.appendChild(opt);
    });
    // also populate prescription select if present
    const presSel = document.getElementById('prescriptionPatientSelect');
    if(presSel){
      // clear except first
      presSel.innerHTML = '<option value="">-- choose patient --</option>';
      Object.keys(patientsMap).forEach(k=>{
        const p = patientsMap[k];
        const o = document.createElement('option'); o.value = k; o.textContent = p.name || p.patient_name || '';
        presSel.appendChild(o);
      });
      presSel.addEventListener('change', ()=>{
        const v = presSel.value;
        const pname = (v && patientsMap[v]) ? (patientsMap[v].name || '') : '';
        const pin = document.getElementById('prescriptionPatientName'); if(pin) pin.value = pname;
      });
    }
    // when a patient is selected, prefill mother name field
    sel.addEventListener('change', ()=>{
      const v = sel.value;
      if(v && patientsMap[v]) motherInput.value = patientsMap[v].name || '';
      else motherInput.value = '';
      // when changing selection for a new record, clear any edit id
      const recId = document.getElementById('newborn_record_id'); if(recId) recId.value = '';
    });
  } catch(err){ console.error('Failed populating newborn patient select', err); }
}

async function loadNewborns(){
  const tbody = document.querySelector('#newbornsTable tbody');
  tbody.innerHTML = `<tr><td colspan="16" style="color:var(--muted);padding:20px;text-align:center">Loading newborn and screening records…</td></tr>`;
  try {
    const res = await fetch('get_newborns.php', { credentials: 'same-origin' });
    if(!res.ok){
      console.error('get_newborns.php returned non-OK', res.status, res.statusText);
      tbody.innerHTML = `<tr><td colspan="16" style="color:var(--muted);padding:20px;text-align:center">No newborn records.</td></tr>`;
      return;
    }

    let j;
    try{ j = await res.json(); } catch(parseErr){
      console.error('Failed parsing JSON from get_newborns.php', parseErr);
      tbody.innerHTML = `<tr><td colspan="16" style="color:var(--muted);padding:20px;text-align:center">No newborn records.</td></tr>`;
      return;
    }

    if(!j.success || !Array.isArray(j.newborns)) {
      tbody.innerHTML = `<tr><td colspan="16" style="color:var(--muted);padding:20px;text-align:center">No newborn records.</td></tr>`;
      return;
    }
    newbornsMap = {};
    // Fetch all screening records for all babies
    const screeningRecords = [];
    for(const n of j.newborns){
      const id = n.baby_id || n.newborn_code || n.newborn_id || n.id || '';
      newbornsMap[id] = n;
      try {
        const r = await fetch(`get_newborn_screening.php?baby_id=${encodeURIComponent(id)}`, { credentials: 'include' });
        if(r.ok){
          const jr = await r.json();
          if(jr && jr.success && jr.record) screeningRecords.push({ babyId: id, newborn: n, screening: jr.record });
          else screeningRecords.push({ babyId: id, newborn: n, screening: null });
        } else {
          screeningRecords.push({ babyId: id, newborn: n, screening: null });
        }
      } catch(e){ screeningRecords.push({ babyId: id, newborn: n, screening: null }); }
    }
    // Render combined table rows
    tbody.innerHTML = screeningRecords.map(rec => {
      const n = rec.newborn;
      const s = rec.screening || {};
      const pid = n.patient_user_id || n.patient_id || null;
      const mother = pid && patientsMap[pid] ? (patientsMap[pid].name || '') : (n.patient_name || n.mother_name || '');
      const babyName = n.child_name || n.baby_name || n.name || '';
      const gender = n.gender || n.sex || '';
      const dob = n.date_of_birth || n.date_delivery || n.dob || '';
      const wt = n.weight || n.wt || '';
      // const status = n.status || n.status_text || n.state || n.status_label || '';
      const dobDisplay = formatDateToDisplay(dob);
      // Screening columns
      const vitk = s.vit_k ? 'Yes' : 'No';
      const hepa = s.hepa_b ? 'Yes' : 'No';
      const bcg = s.bcg ? 'Yes' : 'No';
      const nbs = s.newborn_screening ? 'Yes' : 'No';
      const hearingTaken = s.hearing_taken ? 'Yes' : 'No';
      const hearingResult = s.hearing_result || '-';
      const resultFile = s.result_file_url ? `<a href="${escapeHtml(s.result_file_url)}" class="screening-file-link" data-file="${escapeHtml(s.result_file_url)}" target="_blank">View</a>` : '-';
      const lastSaved = formatDateTimeToDisplay(s.updated_at || s.created_at || '');
      const editBtn = `<button class="btn" data-action="edit-newborn" data-id="${escapeHtml(rec.babyId)}">View/Edit</button>`;
      return `<tr data-id="${escapeHtml(rec.babyId)}">
        <td>${escapeHtml(rec.babyId)}</td>
        <td>${escapeHtml(babyName)}</td>
        <td>${escapeHtml(gender)}</td>
        <td>${escapeHtml(dobDisplay)}</td>
        <td>${escapeHtml(wt)}</td>
        <td>${escapeHtml(mother)}</td>
        <td>${vitk}</td>
        <td>${hepa}</td>
        <td>${bcg}</td>
        <td>${nbs}</td>
        <td>${hearingTaken}</td>
        <td>${hearingResult}</td>
        <td>${resultFile}</td>
        <td style="white-space:nowrap">${editBtn}</td>
      </tr>`;
    }).join('');

    // wire edit buttons (use global newbornsMap)
    document.querySelectorAll('#newbornsTable button[data-action="edit-newborn"]').forEach(btn=>{
      btn.addEventListener('click', async (e) => {
        const id = e.currentTarget.dataset.id;
        const rec = newbornsMap[id];
        if(!rec){ alert('Record not found'); return; }
        populateNewbornForm(rec);
        // Also select the baby in the screening form and load its screening record
        const sel = document.getElementById('screeningNewbornSelect');
        if(sel){ sel.value = id; }
        if(typeof loadScreeningForBaby === 'function') await loadScreeningForBaby(id);
      });
    });
    // populate screening newborn select for the screening form
    try{ populateScreeningNewbornSelect(); }catch(e){ console.error('populateScreeningNewbornSelect error', e); }
    try{ loadNewbornScreenings(); }catch(e){ /* ignore */ }
  } catch(err){
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Failed to load newborn records.</td></tr>`;
  }
}

/* Fill the newborn form for editing an existing record */
function populateNewbornForm(rec){
  const formWrap = document.getElementById('newbornForm'); if(formWrap) formWrap.style.display = 'block';
  const idField = document.getElementById('newborn_record_id'); if(idField) idField.value = rec.id || '';
  const sel = document.getElementById('newbornPatientSelect');
  const motherInput = document.getElementById('newbornMotherName');
  if(rec.patient_user_id && sel){
    sel.value = rec.patient_user_id;
    if(patientsMap[rec.patient_user_id]) motherInput.value = patientsMap[rec.patient_user_id].name || '';
    else motherInput.value = rec.patient_name || '';
  } else {
    if(sel) sel.value = '';
    motherInput.value = rec.patient_name || rec.mother_name || '';
  }
  // populate other fields
  const setVal = (name, val) => { const el = document.querySelector(`#formNewborn [name="${name}"]`); if(el) el.value = val || ''; };
  setVal('child_name', rec.child_name || rec.baby_name || '');
  setVal('gender', rec.gender || '');
  // date input expects YYYY-MM-DD; server uses date_of_birth
  setVal('dob', rec.date_of_birth || rec.dob || '');
  setVal('time_of_birth', rec.time_of_birth || rec.time || '');
  setVal('blood_type', rec.blood_type || '');
  setVal('weight', rec.weight || '');
  setVal('notes', rec.notes || '');
  // change save button label to Update
  const saveBtn = document.getElementById('saveNewbornBtn'); if(saveBtn) saveBtn.textContent = 'Update Newborn';
}

/* ---------- Newborn Screening UI handlers ---------- */
function populateScreeningNewbornSelect(){
  const sel = document.getElementById('screeningNewbornSelect');
  if(!sel) return;
  // clear existing options
  sel.innerHTML = '<option value="">-- choose baby --</option>';
  Object.keys(newbornsMap).forEach(k=>{
    const n = newbornsMap[k];
    // display baby id if available, otherwise child name
    const babyId = k || (n.id || '');
    const label = (babyId && babyId !== 'undefined') ? `${babyId} — ${n.child_name || n.baby_name || ''}`.trim() : (n.child_name || n.baby_name || ('#'+(n.id||'')));
    const opt = document.createElement('option'); opt.value = babyId; opt.textContent = label; sel.appendChild(opt);
  });
}

document.getElementById('btnLoadScreening')?.addEventListener('click', async ()=>{
  const sel = document.getElementById('screeningNewbornSelect'); if(!sel) return;
  const val = sel.value; if(!val){ showToast('Choose a baby to load screening'); return; }
  await loadScreeningForBaby(val);
});

// helper: remove leading numeric id and separator from labels like "6 — BabyName"
function stripLeadingIdLabel(s){
  try{ if(!s) return s; return String(s).replace(/^\s*#?\s*\d+\s*[-–—:]\s*/,''); }catch(e){ return s; }
}

async function loadScreeningForBaby(babyId){
  try{
    const res = await fetch(`get_newborn_screening.php?baby_id=${encodeURIComponent(babyId)}`, { credentials: 'include' });
    if(!res.ok){ showToast('Failed loading screening', 'error'); return; }
    const j = await res.json();
    // Show the screening form for editing
    const formWrap = document.getElementById('screeningFormWrap');
    if(formWrap) formWrap.style.display = 'block';
    const babyIdField = document.getElementById('screeningBabyId');
    if(babyIdField) babyIdField.value = babyId;
    if(!j.success || !j.record){
      // clear form with null checks
      const elVitK = document.getElementById('chkVitK'); if(elVitK) elVitK.checked = false;
      const elHepaB = document.getElementById('chkHepaB'); if(elHepaB) elHepaB.checked = false;
      const elBCG = document.getElementById('chkBCG'); if(elBCG) elBCG.checked = false;
      const elNewbornScreening = document.getElementById('chkNewbornScreening'); if(elNewbornScreening) elNewbornScreening.checked = false;
      const elHearingTaken = document.getElementById('chkHearingTaken'); if(elHearingTaken) elHearingTaken.checked = false;
      const elHearingResult = document.getElementById('selHearingResult'); if(elHearingResult) elHearingResult.value = '';
      const elScreeningLastSaved = document.getElementById('screeningLastSaved'); if(elScreeningLastSaved) elScreeningLastSaved.textContent = '-';
      showToast('No screening record found for selected baby', 'info');
      return;
    }
    const r = j.record;
    const elVitK = document.getElementById('chkVitK'); if(elVitK) elVitK.checked = !!Number(r.vit_k);
    const elHepaB = document.getElementById('chkHepaB'); if(elHepaB) elHepaB.checked = !!Number(r.hepa_b);
    const elBCG = document.getElementById('chkBCG'); if(elBCG) elBCG.checked = !!Number(r.bcg);
    const elNewbornScreening = document.getElementById('chkNewbornScreening'); if(elNewbornScreening) elNewbornScreening.checked = !!Number(r.newborn_screening);
    const elHearingTaken = document.getElementById('chkHearingTaken'); if(elHearingTaken) elHearingTaken.checked = !!Number(r.hearing_taken);
    const elHearingResult = document.getElementById('selHearingResult'); if(elHearingResult) elHearingResult.value = r.hearing_result || '';
    const elScreeningLastSaved = document.getElementById('screeningLastSaved'); if(elScreeningLastSaved) elScreeningLastSaved.textContent = formatDateTimeToDisplay(r.updated_at || r.created_at || '');
    try{
      const wrap = document.getElementById('screeningFileLink');
      if(wrap){
        if(r.result_file_url){ wrap.innerHTML = `<a href="${escapeHtml(r.result_file_url)}" class="screening-file-link" data-file="${escapeHtml(r.result_file_url)}" target="_blank">View current file</a>`; }
        else { wrap.innerHTML = ''; }
      }
    }catch(e){ console.error('Error setting screening file link', e); }
    showToast('Screening loaded', 'success');
    // Update the Baby Name column in the screenings table for this baby (in case it was missing)
    try{
      const tr = document.querySelector('#newbornScreeningsTable tr[data-baby="' + babyId + '"]');
      const sel = document.getElementById('screeningNewbornSelect');
      const selName = sel && sel.options && sel.selectedIndex >= 0 ? stripLeadingIdLabel(sel.options[sel.selectedIndex].text || '') : '';
      const nameToUse = selName || stripLeadingIdLabel(r.child_name || r.baby_name || r.child || '');
      if(tr){ const td = tr.querySelector('td'); if(td) td.textContent = nameToUse; }
    }catch(e){}
  }catch(err){ console.error(err); showToast('Error loading screening', 'error'); }
}

document.getElementById('btnSaveScreening')?.addEventListener('click', async ()=>{
document.getElementById('btnCancelScreening')?.addEventListener('click', ()=>{
  const formWrap = document.getElementById('screeningFormWrap');
  if(formWrap) formWrap.style.display = 'none';
});
  const sel = document.getElementById('screeningNewbornSelect'); if(!sel) return;
  const babyId = sel.value; if(!babyId){ showToast('Select baby first', 'error'); return; }
  try{
    const fd = new FormData();
    fd.append('baby_id', babyId);
    fd.append('vit_k', document.getElementById('chkVitK').checked ? 1 : 0);
    fd.append('hepa_b', document.getElementById('chkHepaB').checked ? 1 : 0);
    fd.append('bcg', document.getElementById('chkBCG').checked ? 1 : 0);
    fd.append('newborn_screening', document.getElementById('chkNewbornScreening').checked ? 1 : 0);
    fd.append('hearing_taken', document.getElementById('chkHearingTaken').checked ? 1 : 0);
    fd.append('hearing_result', document.getElementById('selHearingResult').value || '');
    const fileEl = document.getElementById('screeningResultFile');
    if(fileEl && fileEl.files && fileEl.files.length) fd.append('result_file', fileEl.files[0]);

    const res = await fetch('save_newborn_screening.php', { method: 'POST', credentials: 'include', body: fd });
    const raw = await res.text().catch(()=>null);
    let j = null; try{ j = raw ? JSON.parse(raw) : null; } catch(e){ console.error('save_newborn_screening returned non-JSON', raw); showToast('Server error while saving screening', 'error'); return; }
    if(!j){ showToast('Empty response from server', 'error'); return; }
    if(j.success){
      showToast('Screening saved', 'success');
      document.getElementById('screeningLastSaved').textContent = formatDateTimeToDisplay(j.record && (j.record.updated_at || j.record.created_at) ? (j.record.updated_at || j.record.created_at) : new Date().toISOString());
      // show file link if returned
      try{ const wrap = document.getElementById('screeningFileLink'); if(wrap){ if(j.record && j.record.result_file_url) wrap.innerHTML = `<a href="${escapeHtml(j.record.result_file_url)}" class="screening-file-link" data-file="${escapeHtml(j.record.result_file_url)}" target="_blank">View current file</a>`; else wrap.innerHTML = ''; } }catch(e){}
      await loadNewbornScreenings();
    } else {
      showToast('Failed to save: ' + (j.message || 'server error'), 'error');
    }
  }catch(err){ console.error(err); showToast('Network error saving screening', 'error'); }
});

// Load and render newborn screening records into the table below the form.
async function loadNewbornScreenings(){
  const tbody = document.getElementById('newbornScreeningsTbody');
  if(!tbody) return;
  tbody.innerHTML = `<tr><td colspan="11" style="color:var(--muted);padding:20px;text-align:center">Loading screening records…</td></tr>`;

  // Bulk endpoint `get_newborn_screenings.php` may not exist on all installs.
  // Skip attempting a blind GET to that path to avoid 404 noise; instead
  // fall back to per-baby requests below which use the existing
  // `get_newborn_screening.php?baby_id=...` endpoint.

  // Fallback: iterate known newborns and request screening per-baby
  const ids = Object.keys(newbornsMap || {});
  if(!ids.length){
    tbody.innerHTML = `<tr><td colspan="11" style="color:var(--muted);padding:20px;text-align:center">No newborn records available.</td></tr>`;
    return;
  }

  const records = [];
  for(const id of ids){
    try{
      const r = await fetch(`get_newborn_screening.php?baby_id=${encodeURIComponent(id)}`, { credentials: 'include' });
      if(!r.ok) continue;
      const jr = await r.json();
      if(jr && jr.success && jr.record) records.push(jr.record);
    }catch(e){ /* ignore individual fetch errors */ }
  }

  if(records.length === 0){
    tbody.innerHTML = `<tr><td colspan="11" style="color:var(--muted);padding:20px;text-align:center">No screening records found.</td></tr>`;
    return;
  }
  renderScreenings(records);
}

function renderScreenings(records){
  const tbody = document.getElementById('newbornScreeningsTbody');
  if(!tbody) return;
  // try to prefer the label used in the select dropdown when available
  const sel = document.getElementById('screeningNewbornSelect');
  const rows = records.map(r => {
  const babyId = r.baby_id || r.newborn_id || r.newborn_code || '';
  let babyName = r.child_name || r.baby_name || r.child || '';
  // determine mother/patient name: prefer record field, then newbornsMap -> patientsMap, then newborn record's patient_name
  let motherName = r.patient_name || '';
  try{
    if(!motherName && babyId && typeof newbornsMap !== 'undefined' && newbornsMap && newbornsMap[babyId]){
      const nb = newbornsMap[babyId];
      const pid = nb.patient_user_id || nb.patient_id || null;
      if(pid && typeof patientsMap !== 'undefined' && patientsMap && patientsMap[pid]){
        motherName = patientsMap[pid].name || patientsMap[pid].patient_name || '';
      }
      if(!motherName) motherName = nb.patient_name || nb.mother_name || '';
    }
  }catch(e){}
  try{
    if(sel && babyId){ const opt = sel.querySelector(`option[value="${babyId}"]`); if(opt && opt.textContent) babyName = stripLeadingIdLabel(opt.textContent); }
  }catch(e){ /* ignore selector issues */ }
    const vitk = r.vit_k ? 'Yes' : 'No';
    const hepa = r.hepa_b ? 'Yes' : 'No';
    const bcg = r.bcg ? 'Yes' : 'No';
    const nbs = r.newborn_screening ? 'Yes' : 'No';
    const hearingTaken = r.hearing_taken ? 'Yes' : 'No';
    const hearingResult = r.hearing_result || '-';
    const resultFile = r.result_file_url || '';
    const lastSaved = formatDateTimeToDisplay(r.updated_at || r.created_at || '');
    const loadBtn = `<button class="btn" data-action="load-screening" data-baby="${escapeHtml(babyId)}">Load</button>`;
    return `<tr data-baby="${escapeHtml(babyId)}">
      <td>${escapeHtml(babyName)}</td>
      <td>${escapeHtml(motherName || '-')}</td>
      <td>${escapeHtml(vitk)}</td>
      <td>${escapeHtml(hepa)}</td>
      <td>${escapeHtml(bcg)}</td>
      <td>${escapeHtml(nbs)}</td>
      <td>${escapeHtml(hearingTaken)}</td>
      <td>${escapeHtml(hearingResult)}</td>
      <td>${resultFile ? `<a href="${escapeHtml(resultFile)}" class="screening-file-link" data-file="${escapeHtml(resultFile)}" target="_blank">View</a>` : '-'}</td>
      <td>${escapeHtml(lastSaved)}</td>
      <td style="white-space:nowrap">${loadBtn}</td>
    </tr>`;
  }).join('');
  tbody.innerHTML = rows;

  // wire actions
  document.querySelectorAll('#newbornScreeningsTable button[data-action="load-screening"]').forEach(b => {
    b.addEventListener('click', async (e) => {
      const baby = e.currentTarget.dataset.baby;
      const sel = document.getElementById('screeningNewbornSelect'); if(sel){ sel.value = baby; }
      await loadScreeningForBaby(baby);
    });
  });
  // when the screening select changes, update the Baby Name cell in the screenings table
  try{
    const sel = document.getElementById('screeningNewbornSelect');
    if(sel){
      // helper to safely update the Baby Name cell given baby id
      function updateBabyNameInTable(baby, name){
        if(!baby) return;
        try{
          // use CSS.escape when available to safely build selector
          let tr = null;
          if(window.CSS && typeof CSS.escape === 'function'){
            tr = document.querySelector('#newbornScreeningsTable tr[data-baby="' + CSS.escape(baby) + '"]'.replace(/' +/g,''));
          }
          if(!tr){
            // fallback: loop rows and compare dataset
            document.querySelectorAll('#newbornScreeningsTable tr[data-baby]').forEach(r=>{ if(r.dataset && String(r.dataset.baby) === String(baby)) tr = r; });
          }
          if(tr){ const td = tr.querySelector('td'); if(td) td.textContent = name || td.textContent; }
        }catch(e){ /* ignore */ }
      }

      sel.addEventListener('change', function(){
        try{
          const baby = this.value;
          const raw = this.options && this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
          const name = stripLeadingIdLabel(raw);
          updateBabyNameInTable(baby, name);
        }catch(e){ /* ignore */ }
      });
    }
  }catch(e){}
}

document.getElementById('btnRefreshScreenings')?.addEventListener('click', ()=> loadNewbornScreenings());


/* Select a patient to prefill the Medical Record form */
function selectPatientForMedical(userId, row){
  const p = patientsMap[userId];
  if(!p){ console.warn('Patient not found for selection', userId); return; }
  // ensure medical form is visible
  const formWrap = document.getElementById('medicalForm');
  if(formWrap) formWrap.style.display = 'block';

  // set hidden id and fill fields if present
  const idField = document.getElementById('medical_patient_user_id');
  const nameField = document.getElementById('medical_patient_name');
  const phoneField = document.getElementById('medical_cellphone');
  const ageField = document.getElementById('medical_age');
  if(idField) idField.value = userId;
  if(nameField) nameField.value = p.name || p.patient_name || p.full_name || '';
  if(phoneField) phoneField.value = p.mobile_number || p.cellphone || p.mobile || '';
  if(ageField) ageField.value = p.age || '';

  // highlight selected row
  try{
    document.querySelectorAll('#patientsTable tbody tr').forEach(r=> r.style.background = '');
    if(row) row.style.background = 'rgba(16,124,189,0.06)';
  }catch(e){}

  // update selected patient banner
  const banner = document.getElementById('selectedPatientBanner');
  const bannerName = document.getElementById('selectedPatientName');
  if(banner && bannerName){
    bannerName.textContent = p.name || p.patient_name || p.full_name || (`#${userId}`);
    banner.style.display = 'block';
  }
  // ensure any previous edit state is cleared (we are creating a new record for this patient)
  const recId = document.getElementById('medical_record_id'); if(recId) recId.value = '';
  const saveBtn = document.getElementById('saveMedicalBtn'); if(saveBtn) saveBtn.textContent = 'Save Medical Record';
}

/* ---------- Medical Records ---------- */
document.getElementById('newMedicalBtn')?.addEventListener('click', ()=> {
  document.getElementById('medicalForm').style.display = 'block';
});
document.getElementById('cancelMedicalBtn')?.addEventListener('click', ()=> {
  document.getElementById('medicalForm').style.display = 'none';
  // clear selection banner when form cancelled
  const banner = document.getElementById('selectedPatientBanner'); if(banner) banner.style.display = 'none';
  // reset edit state
  const recId = document.getElementById('medical_record_id'); if(recId) recId.value = '';
  const saveBtn = document.getElementById('saveMedicalBtn'); if(saveBtn) saveBtn.textContent = 'Save Medical Record';
});
document.getElementById('saveMedicalBtn')?.addEventListener('click', async ()=>{
  const form = document.getElementById('formMedical');
  const data = Object.fromEntries(new FormData(form).entries());
  // require a selected patient to avoid orphan records
  const selectedId = document.getElementById('medical_patient_user_id')?.value || '';
  if(!selectedId){ alert('Please select a patient from the Patients list before saving a medical record.'); return; }
  try {
    const res = await fetch('save_medical_record.php', {
      method:'POST', credentials:'same-origin',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify(data)
    });
    const j = await res.json();
    if(j.success){
      form.reset();
      // clear edit id and reset save button text
      const recId = document.getElementById('medical_record_id'); if(recId) recId.value = '';
      const saveBtn = document.getElementById('saveMedicalBtn'); if(saveBtn) saveBtn.textContent = 'Save Medical Record';
      document.getElementById('medicalForm').style.display = 'none';
      const banner = document.getElementById('selectedPatientBanner'); if(banner) banner.style.display = 'none';
      await loadMedicalRecords();
    } else alert('Save failed: ' + (j.message||'server error'));
  } catch(err){ console.error(err); alert('Network error'); }
});

async function loadMedicalRecords(){
  const tbody = document.querySelector('#medicalTable tbody');
  tbody.innerHTML = `<tr><td colspan="14" style="color:var(--muted);padding:20px;text-align:center">Loading medical records…</td></tr>`;
  try {
    const res = await fetch('get_medical_records.php', { credentials: 'same-origin' });
    const j = await res.json();
    if(!j.success || !Array.isArray(j.records)) {
      tbody.innerHTML = `<tr><td colspan="14" style="color:var(--muted);padding:20px;text-align:center">No medical records.</td></tr>`;
      return;
    }
    // store records in a map for quick lookup by id when editing
    const medicalRecordsMap = {};
    tbody.innerHTML = j.records.map(r=>{
      medicalRecordsMap[r.id] = r;
      const editBtn = `<button class="btn" data-action="edit" data-id="${escapeHtml(r.id||'')}">Edit</button>`;
      const lmpTxt = formatDateToDisplay(r.lmp || '');
      const eddTxt = formatDateToDisplay(r.edd || '');
      const rawResult = r.result || r.result_status || r.result_text || '';
      let resultTxt = '-';
      if(rawResult){ const rr = String(rawResult).toLowerCase(); resultTxt = rr === 'normal' ? 'Normal' : (rr === 'abnormal' ? 'Abnormal' : escapeHtml(rawResult)); }
      // Table columns: Patient, Cellphone, OB Score, LMP, EDD, BP, Weight, Pulse, Resp. Rate, Fetal Heart Tone, Gravida, Para, Result, Action (14 columns)
      return `<tr data-id="${escapeHtml(r.id||'')}">
        <td>${escapeHtml(r.patient_name||'')}</td>
        <td>${escapeHtml(r.cellphone||'')}</td>
        <td>${escapeHtml(r.ob_score||'')}</td>
        <td>${escapeHtml(lmpTxt)}</td>
        <td>${escapeHtml(eddTxt)}</td>
        <td>${escapeHtml(r.blood_pressure||'')}</td>
        <td>${escapeHtml(r.weight||'')}</td>
        <td>${escapeHtml(r.pulse||'')}</td>
        <td>${escapeHtml(r.respiratory_rate||'')}</td>
        <td>${escapeHtml(r.fht||'')}</td>
        <td>${escapeHtml(r.gravida||'')}</td>
        <td>${escapeHtml(r.para||'')}</td>
        <td>${escapeHtml(resultTxt)}</td>
        <td style="white-space:nowrap">${editBtn}</td>
      </tr>`;
    }).join('');

    // wire edit buttons (use medicalRecordsMap closure)
    document.querySelectorAll('#medicalTable button[data-action="edit"]').forEach(btn=>{
      btn.addEventListener('click', (e)=>{
        const id = e.currentTarget.dataset.id;
        const rec = medicalRecordsMap[id];
        if(!rec){ alert('Record not found'); return; }
        populateMedicalForm(rec);
      });
    });
  } catch(err){
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="14" style="color:var(--muted);padding:20px;text-align:center">Failed to load medical records.</td></tr>`;
  }
}

/* Load patients who have upcoming appointments so midwife can quickly select them for medical records */
async function loadMedicalPatientsFromAppointments(){
  const tbody = document.querySelector('#medicalPatientsTable tbody');
  if(!tbody) return;
  tbody.innerHTML = `<tr><td colspan="3" style="color:var(--muted);padding:10px;text-align:center">Loading patients with appointments…</td></tr>`;
  try{
    const res = await fetch('get_manage_appointments.php', { credentials: 'include' });
    if(!res.ok){ tbody.innerHTML = `<tr><td colspan="4" style="color:var(--muted);padding:10px;text-align:center">Failed loading appointments.</td></tr>`; return; }
    const j = await res.json();
    if(!j.success || !Array.isArray(j.appointments)) { tbody.innerHTML = `<tr><td colspan="4" style="color:var(--muted);padding:10px;text-align:center">No appointments found.</td></tr>`; return; }

    // Build unique patients map by id (if present) or by phone+name
    const seen = {};
    const patients = [];
    j.appointments.forEach(a => {
      const pid = a.patient_user_id || a.user_id || a.patient_id || a.patient_user || null;
      const name = a.patient_name || a.name || (a.patient && a.patient.name) || '';
      const phone = a.mobile_number || a.cellphone || (a.patient && (a.patient.mobile_number||a.patient.cellphone)) || '';
      const age = a.age || (a.patient && a.patient.age) || '';
      const apptDate = a.date || a.appointment_date || '';
      const key = pid ? `id:${pid}` : (`name:${name}|phone:${phone}`);
      if(seen[key]) return; seen[key] = true;
      patients.push({ id: pid, name, phone, age, apptDate, raw: a });
    });

    if(patients.length === 0){ tbody.innerHTML = `<tr><td colspan="3" style="color:var(--muted);padding:10px;text-align:center">No patients with appointments.</td></tr>`; return; }

    tbody.innerHTML = patients.map(p=>{
      const apptShort = p.apptDate ? formatDateToDisplay(p.apptDate) : '';
      return `<tr data-user="${escapeHtml(p.id||'')}">
        <td>${escapeHtml(p.name||'')}</td>
        <td>${escapeHtml(p.phone||'')}</td>
        <td>${escapeHtml(apptShort)}</td>
      </tr>`;
    }).join('');

    // Wire click handlers to select the patient for medical form
    document.querySelectorAll('#medicalPatientsTable tbody tr[data-user], #medicalPatientsTable tbody tr').forEach(row=>{
      row.addEventListener('click', ()=>{
        const uid = row.dataset.user || '';
        // if we have a user id that exists in patientsMap, reuse selectPatientForMedical
        if(uid && patientsMap[uid]){ selectPatientForMedical(uid, row); return; }
        // otherwise, construct a small object from the row cells
        const cells = row.querySelectorAll('td');
        const rec = {
          patient_user_id: uid || null,
          patient_name: (cells[0] && cells[0].textContent.trim()) || '',
          cellphone: (cells[1] && cells[1].textContent.trim()) || ''
        };
        selectAppointmentPatientForMedical(rec, row);
      });
    });

  } catch(err){ console.error('Failed loading appointment patients', err); tbody.innerHTML = `<tr><td colspan="3" style="color:var(--muted);padding:10px;text-align:center">Failed to load.</td></tr>`; }
}

/* When an appointment-only patient is selected (no full patient_details), prefill the medical form with available info */
function selectAppointmentPatientForMedical(p, row){
  const formWrap = document.getElementById('medicalForm'); if(formWrap) formWrap.style.display = 'block';
  if(document.getElementById('medical_patient_user_id')) document.getElementById('medical_patient_user_id').value = p.patient_user_id || '';
  if(document.getElementById('medical_patient_name')) document.getElementById('medical_patient_name').value = p.patient_name || '';
  if(document.getElementById('medical_cellphone')) document.getElementById('medical_cellphone').value = p.cellphone || '';
  // highlight selected row
  try{ document.querySelectorAll('#medicalPatientsTable tbody tr').forEach(r=> r.style.background = ''); if(row) row.style.background = 'rgba(16,124,189,0.06)'; }catch(e){}
  // show selected banner
  const banner = document.getElementById('selectedPatientBanner'); const bannerName = document.getElementById('selectedPatientName');
  if(banner && bannerName){ bannerName.textContent = p.patient_name || '#'+(p.patient_user_id||''); banner.style.display = 'block'; }
  // clear edit state (we're creating a new record)
  const recId = document.getElementById('medical_record_id'); if(recId) recId.value = '';
  const saveBtn = document.getElementById('saveMedicalBtn'); if(saveBtn) saveBtn.textContent = 'Save Medical Record';
}

/* Fill the medical form with an existing record for editing */
function populateMedicalForm(rec){
  // ensure form visible
  const formWrap = document.getElementById('medicalForm'); if(formWrap) formWrap.style.display = 'block';
  // set record id
  document.getElementById('medical_record_id').value = rec.id || '';
  // set patient id if available
  if(rec.patient_user_id) document.getElementById('medical_patient_user_id').value = rec.patient_user_id;
  // fill common fields
  document.getElementById('medical_patient_name').value = rec.patient_name || '';
  document.getElementById('medical_cellphone').value = rec.cellphone || '';
  document.getElementById('medical_age').value = rec.age || '';
  // set result select if present
  try{ const resEl = document.querySelector('#medical_result'); if(resEl) resEl.value = rec.result || rec.result_status || ''; }catch(e){}
  // other fields in the form (names must match)
  const names = ['ob_score','lmp','edd','gestation_age','blood_pressure','weight','pulse','respiratory_rate','fht','gravida','para'];
  names.forEach(n=>{ const el = document.querySelector(`[name="${n}"]`); if(el) el.value = rec[n] || rec[n]===0 ? rec[n] : (rec[n] || ''); });

  // show selected banner
  const banner = document.getElementById('selectedPatientBanner');
  const bannerName = document.getElementById('selectedPatientName');
  if(banner && bannerName){ bannerName.textContent = rec.patient_name || '#'+(rec.patient_user_id||rec.id); banner.style.display = 'block'; }

  // change save button to Update
  const saveBtn = document.getElementById('saveMedicalBtn'); if(saveBtn) saveBtn.textContent = 'Update Medical Record';
}

/* ---------- Prescriptions ---------- */
document.getElementById('newPrescriptionBtn')?.addEventListener('click', ()=> {
  // ensure patient select is populated when opening the prescription form
  try { populateNewbornPatientSelect(); } catch(e){ console.error('Failed populating patients for prescriptions', e); }
  // clear any edit state
  const idf = document.getElementById('prescription_id'); if(idf) idf.value = '';
  const form = document.getElementById('formPrescription'); if(form) form.reset();
  const saveBtn = document.getElementById('savePrescriptionBtn'); if(saveBtn) saveBtn.textContent = 'Save Prescription';
  document.getElementById('prescriptionForm').style.display = 'block';
});
document.getElementById('cancelPrescriptionBtn')?.addEventListener('click', ()=> {
  document.getElementById('prescriptionForm').style.display = 'none';
});
document.getElementById('savePrescriptionBtn')?.addEventListener('click', async ()=>{
  const form = document.getElementById('formPrescription');
  // require selecting a patient from the list so prescriptions are linked to the patient account
  const currentPatient = form.querySelector('[name="patient_user_id"]')?.value;
  if(!currentPatient){ alert('Please select a patient from the dropdown so the prescription is linked to their account.'); return; }

  try{
    // use FormData so file uploads are supported
    const fd = new FormData(form);
    const res = await fetch('save_prescription.php', { method: 'POST', credentials: 'same-origin', body: fd });
    const rawText = await res.text().catch(()=>null);
    let j = null;
    try{ j = rawText ? JSON.parse(rawText) : null; } catch(parseErr){ console.error('save_prescription.php returned non-JSON response', rawText); alert('Server error while saving prescription. See console for details.'); return; }
    if(!j){ alert('Empty response from server. See console for details.'); return; }
    if(j.success){
      form.reset();
      // clear edit id and reset label
      const idf = document.getElementById('prescription_id'); if(idf) idf.value = '';
      const saveBtn = document.getElementById('savePrescriptionBtn'); if(saveBtn) saveBtn.textContent = 'Save Prescription';
      document.getElementById('prescriptionForm').style.display = 'none';
      await loadPrescriptions();
    } else {
      alert('Save failed: ' + (j.message||'server error'));
    }
  } catch(err){ console.error(err); alert('Network error'); }
});

async function loadPrescriptions(){
  const tbody = document.querySelector('#prescriptionsTable tbody');
  tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Loading prescriptions…</td></tr>`;
  try {
    const res = await fetch('get_prescriptions.php', { credentials: 'same-origin' });
    if(!res.ok){ const txt = await res.text().catch(()=>'(no body)'); console.error('get_prescriptions.php error', res.status, txt); tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Failed to load prescriptions.</td></tr>`; return; }
    let j;
    try { j = await res.json(); } catch(parseErr){ const txt = await res.text().catch(()=>'(no body)'); console.error('Failed parsing JSON from get_prescriptions.php', parseErr, txt); tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">No prescriptions.</td></tr>`; return; }
    if(!j.success || !Array.isArray(j.prescriptions)) {
      tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">No prescriptions.</td></tr>`;
      return;
    }

    // map for quick lookup when editing
    const presMap = {};
    tbody.innerHTML = j.prescriptions.map(p=>{
      const id = p.id || '';
      presMap[id] = p;
      const dateDisplay = formatDateToDisplay(p.date || '');
      const fileLink = p.file_url ? `<a href="${escapeHtml(p.file_url)}" class="prescription-file-link" data-file="${escapeHtml(p.file_url)}" target="_blank">View</a>` : '-';
      const editBtn = `<button class="btn" data-action="edit-prescription" data-id="${escapeHtml(id)}">Edit</button>`;
      return `<tr data-id="${escapeHtml(id)}">
        <td>${escapeHtml(p.patient_name||'')}</td>
        
        <td>${fileLink}</td>
        <td>${escapeHtml(dateDisplay)}</td>
        <td style="white-space:nowrap">${editBtn}</td>
      </tr>`;
    }).join('');

    // wire edit buttons
    document.querySelectorAll('#prescriptionsTable button[data-action="edit-prescription"]').forEach(btn=>{
      btn.addEventListener('click', (e)=>{
        const id = e.currentTarget.dataset.id;
        const rec = presMap[id];
        if(!rec){ alert('Record not found'); return; }
        populatePrescriptionForm(rec);
      });
    });

  } catch(err){
    console.error(err);
    tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Failed to load prescriptions.</td></tr>`;
  }
}

/* Populate prescription form for editing */
function populatePrescriptionForm(rec){
  const wrap = document.getElementById('prescriptionForm'); if(wrap) wrap.style.display = 'block';
  const idField = document.getElementById('prescription_id'); if(idField) idField.value = rec.id || '';
  // set patient select and name
  const sel = document.getElementById('prescriptionPatientSelect');
  const pname = document.getElementById('prescriptionPatientName');
  // ensure patient select is populated (safe) then set the value
  try { populateNewbornPatientSelect(); } catch(e){}
  // small delay to let populateNewbornPatientSelect attach options
  setTimeout(()=>{
    if(rec.patient_user_id && sel) sel.value = rec.patient_user_id;
    if(pname) pname.value = rec.patient_name || (patientsMap[rec.patient_user_id] ? patientsMap[rec.patient_user_id].name : '');
  }, 150);
  // fill other fields
  const setVal = (name, val) => { const el = document.querySelector(`#formPrescription [name="${name}"]`); if(el) el.value = val || ''; };
  setVal('instruction', rec.instruction || '');
  setVal('drugs', rec.drugs || '');
  // date input expects YYYY-MM-DD
  setVal('date', rec.date || '');
  // show existing file link if present
  try{
    const fileLinkWrap = document.getElementById('prescriptionFileLink');
    if(fileLinkWrap){
      if(rec.file_url){ fileLinkWrap.innerHTML = `<a href="${escapeHtml(rec.file_url)}" target="_blank">View current file</a>`; }
      else { fileLinkWrap.innerHTML = ''; }
    }
  }catch(e){ console.error('Error setting prescription file link', e); }
  const saveBtn = document.getElementById('savePrescriptionBtn'); if(saveBtn) saveBtn.textContent = 'Update Prescription';
}

/* ---------- Patients ---------- */
async function loadPatients(){
  const tbody = document.querySelector('#patientsTable tbody');
  tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Loading patients…</td></tr>`;
  try{
    const res = await fetch('get_patients.php', { credentials: 'include' });
    if(!res.ok){ const body = await res.text().catch(()=>'(no body)'); console.error('get_patients.php error', res.status, res.statusText, body); tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Failed to load patients.</td></tr>`; return; }
    const j = await res.json();
    if(!j.success || !Array.isArray(j.patients)) { tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">No patients found.</td></tr>`; return; }

    // Filter out patients that do not have any name (so rows with blank names are removed)
    const visiblePatients = (j.patients || []).filter(p => {
      // helper to treat null/undefined/'null'/'undefined' and whitespace as blank
      const isBlank = (v) => {
        if (v === null || v === undefined) return true;
        const s = String(v).trim();
        if (!s) return true;
        const low = s.toLowerCase();
        if (low === 'null' || low === 'undefined') return true;
        return false;
      };

      const nameCandidate = (p.name || p.patient_name || p.full_name || p.fullName);
      const emailCandidate = (p.email || p.user_email || p.email_address);
      const mobileCandidate = (p.mobile_number || p.cellphone || p.mobile);
      const addressCandidate = (p.address || p.addr || p.location);

      // Exclude rows that have no meaningful data (all key display fields are blank)
      const allBlank = isBlank(nameCandidate) && isBlank(emailCandidate) && isBlank(mobileCandidate) && isBlank(addressCandidate);
      return !allBlank;
    });
    if (!visiblePatients.length) {
      tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">No patients found.</td></tr>`;
    } else {
      tbody.innerHTML = visiblePatients.map(p=>{
        const id = escapeHtml(p.user_id || p.id || '');
        const displayName = escapeHtml(p.name || p.patient_name || p.full_name || p.fullName || ('#' + (p.user_id || p.id || '')));
        return `<tr data-user="${id}">
          <td>${displayName}</td>
          <td>${escapeHtml(p.age||'')}</td>
          <td>${escapeHtml(p.mobile_number||'')}</td>
          <td>${escapeHtml(p.email||'')}</td>
          <td>${escapeHtml(p.address||'')}</td>
          <td><button class="btn btn-view" data-action="view-patient" data-user="${id}">View patient details</button></td>
        </tr>`;
      }).join('');
    }

    // wire buttons — open patient details modal
    document.querySelectorAll('#patientsTable button[data-action="view-patient"]').forEach(btn=>{
      btn.addEventListener('click', (e)=>{
        const userId = e.currentTarget.dataset.user;
        openPatientInfoModal(userId);
      });
    });

    // store patients into patientsMap so other panels (medical/newborn) can reuse
    j.patients.forEach(p => {
      const id = p.user_id || p.id || p.userId;
      if(id == null) return;
      patientsMap[id] = p;
    });

    // clicking a table row (not a button) selects that patient for the medical form
    document.querySelectorAll('#patientsTable tbody tr[data-user]').forEach(row=>{
      row.addEventListener('click', (e)=>{
        if(e.target.closest('button')) return; // ignore clicks on the action button
        const userId = row.dataset.user;
        selectPatientForMedical(userId, row);
      });
    });

  } catch(err){ console.error('Failed loading patients', err); tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Failed to load patients.</td></tr>`; }
}

// Patients sidebar search: filter rows in #patientsTable by name/mobile/email/address
(function(){
  const input = document.getElementById('patientSearch');
  if(!input) return;

  function debounce(fn, wait){ let t; return function(...a){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,a), wait); }; }

  function applyFilter(){
    try{
      const q = (input.value||'').trim().toLowerCase();
      const tbody = document.querySelector('#patientsTable tbody'); if(!tbody) return;
      const rows = tbody.querySelectorAll('tr');
      rows.forEach(tr=>{
        const firstTd = tr.querySelector('td');
        if(firstTd && firstTd.hasAttribute('colspan')){ tr.style.display = q ? 'none' : ''; return; }
        const txt = (tr.textContent || '').replace(/\s+/g,' ').toLowerCase();
        tr.style.display = q ? (txt.indexOf(q) !== -1 ? '' : 'none') : '';
      });
    }catch(e){ console.error('Patient search filter error', e); }
  }

  const debounced = debounce(applyFilter, 240);
  input.addEventListener('input', debounced);
  document.getElementById('patientSearchClear')?.addEventListener('click', ()=>{ input.value = ''; applyFilter(); input.focus(); });

  // Re-apply filter whenever the patients table body changes (e.g., after loadPatients)
  const tbody = document.querySelector('#patientsTable tbody');
  if(tbody && window.MutationObserver){
    const mo = new MutationObserver(debounce(()=>{ try{ applyFilter(); }catch(e){} }, 120));
    mo.observe(tbody, { childList:true, subtree:true });
  }

  // on Enter, focus the first visible action button in the filtered patients list
  input.addEventListener('keydown', function(e){
    if(e.key === 'Enter'){
      e.preventDefault();
      const tbody = document.querySelector('#patientsTable tbody'); if(!tbody) return;
      const rows = Array.from(tbody.querySelectorAll('tr')).filter(r=> r.style.display !== 'none');
      if(rows.length){
        const btn = rows[0].querySelector('button[data-action="view-patient"]') || rows[0].querySelector('button');
        if(btn) btn.focus();
      }
    }
  });
})();


/* Inventory moved to clerk_portal.php — JS removed from this file to avoid duplication.
   Canonical inventory UI and handlers now live in clerk_portal.php. */

async function loadLabResults(){
// Populate the lab upload select with patients (from patient DB) rather than appointments
async function populateLabAppointments(){
  const sel = document.getElementById('labAppointmentSelect');
  if(!sel) return;
  sel.innerHTML = '<option value="">-- choose patient --</option>';
  try{
    const res = await fetch('get_patients.php', { credentials: 'include' });
    if(!res.ok){ console.error('get_patients.php failed', res.status); return; }
    const j = await res.json();
    if(!j.success || !Array.isArray(j.patients)) return;

    j.patients.forEach(p=>{
      const pid = p.user_id || p.id || '';
      const patientName = p.name || p.patient_name || p.full_name || ('#' + pid);
      const opt = document.createElement('option');
      opt.value = pid;
      opt.textContent = `${patientName}${p.mobile_number ? ' — ' + p.mobile_number : ''}`;
      opt.dataset.patientUserId = pid;
      opt.dataset.patientName = patientName;
      sel.appendChild(opt);
    });

    sel.addEventListener('change', ()=>{
      const v = sel.value;
      const opt = sel.options[sel.selectedIndex];
      // when selecting a patient, clear appointment_id (we'll populate appointment choices)
      const apptEl = document.getElementById('lab_appointment_id'); if(apptEl) apptEl.value = '';
      document.getElementById('lab_patient_user_id').value = opt ? (opt.dataset.patientUserId || '') : '';
      document.getElementById('lab_patient_name').value = opt ? (opt.dataset.patientName || '') : '';
      // populate appointments for this patient into the appointment select
      try{ populateAppointmentsForPatient(opt ? (opt.dataset.patientUserId || '') : ''); }catch(e){ console.error('populateAppointmentsForPatient failed', e); }
    });
  } catch(err){ console.error('Failed to populate patients for lab upload', err); }
}

// Populate appointments for a selected patient into the lab appointment select
async function populateAppointmentsForPatient(patientId){
  const sel = document.getElementById('labAppointmentForPatientSelect');
  if(!sel) return;
  // reset
  sel.innerHTML = '<option value="">-- choose appointment --</option>';
  if(!patientId) return;
  try{
    // load existing uploaded results so we can skip appointments that already have results
    let uploadedApptIds = new Set();
    try{
      const resExisting = await fetch('get_results_upload.php', { credentials: 'include' });
      if(resExisting && resExisting.ok){
        const jr = await resExisting.json().catch(()=>null);
        const existing = Array.isArray(jr && jr.results_uploaded) ? jr.results_uploaded : (Array.isArray(jr && jr.lab_results) ? jr.lab_results : []);
        existing.forEach(r=>{
          const pid = String(r.patient_user_id || r.patient_userid || r.patient_id || '');
          const aid = String(r.appointment_id || r.appointmentId || r.appointment_id || '');
          if(pid === String(patientId) && aid) uploadedApptIds.add(aid);
        });
      }
    }catch(e){ console.error('Failed fetching existing results', e); }
  // server expects `user_id` query param — use that so results are scoped to the chosen patient
  const res = await fetch('get_manage_appointments.php?user_id=' + encodeURIComponent(patientId), { credentials: 'include' });
    if(!res.ok) return;
    const j = await res.json();
    const appts = Array.isArray(j.appointments) ? j.appointments : (Array.isArray(j.data) ? j.data : []);
    appts.forEach(a=>{
      // skip appointments that were cancelled (by patient or otherwise)
      const isCancelled = !!(a.cancelled_by || a.cancelled_at || (a.status && /cancel/i.test(String(a.status))));
      if(isCancelled) return; // ignore cancelled bookings

      // determine if this appointment should be considered "completed"
      const rawStatus = String(a.status || '').toLowerCase();
      const aptDate = a.date || a.appointment_date || '';
      const today = (typeof todayDateString === 'function') ? todayDateString() : null;
      const isCompleted = rawStatus.indexOf('completed') !== -1 || (aptDate && today && aptDate < today);
      // Only include completed appointments in the results-upload appointment list
      if(!isCompleted) return;

      const id = a.id || a.appointment_id || a.appointmentId || '';
      // skip appointments that already have uploaded results for this patient
      if(id && uploadedApptIds.has(String(id))) return;
      const date = a.date || a.appointment_date || a.appointment_date_time || '';
      const time = a.time || a.appointment_time || '';
      const service = a.service || a.appointment_service || a.type || '';
      const opt = document.createElement('option');
      opt.value = id;
      const parts = [];
      if(date) parts.push(formatDateToDisplay(date));
      if(time) parts.push(formatTimeToDisplay(time));
      if(service) parts.push(service);
      opt.textContent = parts.join(' • ') || ('#' + id);
      opt.dataset.date = date || '';
      opt.dataset.time = time || '';
      opt.dataset.service = service || '';
      sel.appendChild(opt);
    });
    sel.addEventListener('change', ()=>{
      const v = sel.value || '';
      const hid = document.getElementById('lab_appointment_id'); if(hid) hid.value = v;
    });
  }catch(err){ console.error('Failed populating appointments for patient', err); }
}

  // ensure patient map available
  try{ await populateNewbornPatientSelect(); } catch(e){}

  // populate appointments (patients) select
  await populateLabAppointments();

  // load existing lab results
  const tbody = document.querySelector('#labResultsTable tbody');
  if(!tbody) return;
  tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Loading laboratory results…</td></tr>`;
  try{
    const res = await fetch('get_results_upload.php', { credentials: 'include' });
    if(!res.ok){ const t = await res.text().catch(()=>'(no body)'); console.error('get_results_upload.php failed', res.status, t); tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Failed to load lab results.</td></tr>`; return; }
    let j;
    try{ j = await res.json(); } catch(e){ const t = await res.text().catch(()=>'(no body)'); console.error('Invalid JSON from get_results_upload.php', e, t); tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">No lab results.</td></tr>`; return; }
    const results = Array.isArray(j.results_uploaded) ? j.results_uploaded : (Array.isArray(j.lab_results) ? j.lab_results : []);
    if(!j.success || results.length === 0) { tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">No lab results.</td></tr>`; return; }

    tbody.innerHTML = results.map(r=>{
      const patient = r.patient_name || (r.patient_user_id && patientsMap[r.patient_user_id] ? patientsMap[r.patient_user_id].name : '') || 'Unknown';
      // prefer a human-friendly appointment type/service/result label when available
      let appt = '';
          if(r.appointment_type || r.type || r.result_type || r.service){
        appt = String(r.appointment_type || r.type || r.result_type || r.service);
      } else if(r.appointment_id){
        // try to derive a friendly label from the appointments select options populated earlier
        try{
              const opt = document.querySelector(`#labAppointmentForPatientSelect option[value="${r.appointment_id}"], #labAppointmentSelect option[value="${r.appointment_id}"]`);
          if(opt){
            appt = opt.dataset.type || opt.dataset.service || opt.textContent || '';
          }
        }catch(e){ appt = ''; }
      }
      if(!appt) appt = '-';
      const notes = r.notes || '';
      const filePath = r.file_url || r.url || r.filename || '';
      const link = filePath ? `<a href="${escapeHtml(filePath)}" class="prescription-file-link" data-file="${escapeHtml(filePath)}" target="_blank">View</a>` : '-';
      const date = formatDateTimeToDisplay(r.uploaded_at || r.date_uploaded || r.uploaded || '');
      // compute appointment date/time display
      const apptDateRaw = r.date || r.appointment_date || r.appointment_date_time || r.booked_at || '';
      const apptTimeRaw = r.time || r.appointment_time || r.appointment_time_of_day || '';
      const apptDateDisplay = `${formatDateToDisplay(apptDateRaw)}${apptTimeRaw ? ' • ' + formatTimeToDisplay(apptTimeRaw) : ''}`.trim();
      let apptDateCell = apptDateDisplay ? apptDateDisplay : '';
      // if the record didn't include appointment date/time, try to read it from the appointments select option
        if(!apptDateCell && r.appointment_id){
        try{
          const opt = document.querySelector(`#labAppointmentForPatientSelect option[value="${r.appointment_id}"], #labAppointmentSelect option[value="${r.appointment_id}"]`);
          if(opt){
            const d = opt.dataset.date || '';
            const t = opt.dataset.time || '';
            if(d || t){
              apptDateCell = `${formatDateToDisplay(d)}${t ? ' • ' + formatTimeToDisplay(t) : ''}`.trim();
            }
          }
        }catch(e){ /* ignore */ }
      }
      if(!apptDateCell) apptDateCell = '-';
      return `<tr>
        <td>${escapeHtml(patient)}</td>
        <td>${escapeHtml(appt)}</td>
        <td>${escapeHtml(apptDateCell)}</td>
        <td>${escapeHtml(notes)}</td>
        <td>${link}</td>
        <td>${escapeHtml(date)}</td>
      </tr>`;
    }).join('');

  } catch(err){ console.error('Failed loading lab results', err); tbody.innerHTML = `<tr><td colspan="5" style="color:var(--muted);padding:20px;text-align:center">Failed to load lab results.</td></tr>`; }
}

// wire refresh and upload
document.getElementById('btnRefreshLabAppts')?.addEventListener('click', ()=> populateLabAppointments());
document.getElementById('uploadLabBtn')?.addEventListener('click', ()=>{
  const form = document.getElementById('formLabResult');
  const fileEl = document.getElementById('labFile');
  const uploadBtn = document.getElementById('uploadLabBtn');
  const progressWrap = document.getElementById('labUploadProgressWrap');
  const progressBar = document.getElementById('labUploadProgressBar');
  const progressText = document.getElementById('labUploadProgressText');
  if(!fileEl || !fileEl.files || fileEl.files.length === 0){ setLabMessage('Please choose a file to upload.','error'); try{ fileEl && fileEl.focus(); }catch(e){} return; }

  // prepare form data
  const fd = new FormData();
  fd.append('file', fileEl.files[0]);
  fd.append('patient_user_id', document.getElementById('lab_patient_user_id').value || '');
  fd.append('patient_name', document.getElementById('lab_patient_name').value || '');
  fd.append('appointment_id', document.getElementById('lab_appointment_id').value || '');
  fd.append('notes', document.getElementById('labNotes').value || '');

  // UI: show progress, disable button
  if(progressWrap) progressWrap.style.display = 'block';
  if(progressBar) progressBar.style.width = '0%';
  if(progressText) progressText.textContent = 'Uploading...';
  if(uploadBtn) uploadBtn.disabled = true;

  try{
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'save_results_upload.php', true);
    xhr.withCredentials = true;
    xhr.upload.onprogress = function(e){
      if(e.lengthComputable){
        const pct = Math.round((e.loaded / e.total) * 100);
        if(progressBar) progressBar.style.width = pct + '%';
        if(progressText) progressText.textContent = `Uploading — ${pct}%`;
      }
    };
    xhr.onreadystatechange = function(){
      if(xhr.readyState !== 4) return;
      if(xhr.status >= 200 && xhr.status < 300){
        let j = null;
        try{ j = xhr.responseText ? JSON.parse(xhr.responseText) : null; } catch(err){ console.error('Invalid JSON from save_results_upload.php', xhr.responseText); }
        if(j && j.success){
          if(progressBar) progressBar.style.width = '100%';
          if(progressText) progressText.textContent = 'Upload complete';
          try{ form.reset(); }catch(_){ }
          // refresh the table and open preview if server provided a URL
          (async ()=>{
            try{ await loadLabResults(); }catch(e){}
            if(j.record && (j.record.file_url || j.record.url || j.record.result_file_url || j.url)){
              const fileUrl = j.record.file_url || j.record.url || j.record.result_file_url || j.url;
              try{ showPrescriptionModal(fileUrl); } catch(e){ window.open(fileUrl, '_blank'); }
            }
            showToast ? showToast('Uploaded successfully','success') : alert('Uploaded successfully');
            // hide progress after a short delay
            setTimeout(()=>{ if(progressWrap) progressWrap.style.display = 'none'; if(uploadBtn) uploadBtn.disabled = false; if(progressText) progressText.textContent = ''; }, 900);
          })();
        } else {
          const msg = (j && j.message) ? j.message : ('Server error while uploading.');
          showToast ? showToast(msg,'error') : alert(msg);
          if(uploadBtn) uploadBtn.disabled = false;
          if(progressText) progressText.textContent = 'Upload failed';
        }
      } else {
        const txt = xhr.responseText || (`Status ${xhr.status}`);
        console.error('save_results_upload.php error', xhr.status, txt);
        showToast ? showToast('Failed uploading lab result (server error)','error') : alert('Failed uploading lab result (server error)');
        if(uploadBtn) uploadBtn.disabled = false;
        if(progressText) progressText.textContent = 'Upload failed';
      }
    };
    xhr.onerror = function(){
      showToast ? showToast('Network error while uploading lab result','error') : alert('Network error while uploading lab result');
      if(uploadBtn) uploadBtn.disabled = false;
      if(progressText) progressText.textContent = 'Upload failed';
    };
    xhr.send(fd);
  }catch(err){
    console.error('Failed uploading lab result', err);
    showToast ? showToast('Network error while uploading lab result','error') : alert('Network error while uploading lab result');
    if(uploadBtn) uploadBtn.disabled = false;
    if(progressText) progressText.textContent = 'Upload failed';
  }
});

/* ---------- Newborns (already implemented) ---------- */
// (Re-use functions defined above)

/* ---------- Patients ---------- */
async function loadPatients(){
  const tbody = document.querySelector('#patientsTable tbody');
  tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Loading patients…</td></tr>`;
  try{
    const res = await fetch('get_patients.php', { credentials: 'include' });
    if(!res.ok){ const body = await res.text().catch(()=>'(no body)'); console.error('get_patients.php error', res.status, res.statusText, body); tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Failed to load patients.</td></tr>`; return; }
    const j = await res.json();
    if(!j.success || !Array.isArray(j.patients)) { tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">No patients found.</td></tr>`; return; }

    tbody.innerHTML = j.patients.map(p=>{
      const id = escapeHtml(p.user_id || p.id || '');
      return `<tr data-user="${id}">
        <td>${escapeHtml(p.name||'')}</td>
        <td>${escapeHtml(p.age||'')}</td>
        <td>${escapeHtml(p.mobile_number||'')}</td>
        <td>${escapeHtml(p.email||'')}</td>
        <td>${escapeHtml(p.address||'')}</td>
        <td><button class="btn btn-view" data-action="view-patient" data-user="${id}">View patient details</button></td>
      </tr>`;
    }).join('');

    // wire buttons — open patient details modal
    document.querySelectorAll('#patientsTable button[data-action="view-patient"]').forEach(btn=>{
      btn.addEventListener('click', (e)=>{
        const userId = e.currentTarget.dataset.user;
        openPatientInfoModal(userId);
      });
    });

    // store patients into patientsMap so other panels (medical/newborn) can reuse

    // store patients into patientsMap so other panels (medical/newborn) can reuse
    j.patients.forEach(p => {
      const id = p.user_id || p.id || p.userId;
      if(id == null) return;
      patientsMap[id] = p;
    });

    // clicking a table row (not a button) selects that patient for the medical form
    document.querySelectorAll('#patientsTable tbody tr[data-user]').forEach(row=>{
      row.addEventListener('click', (e)=>{
        if(e.target.closest('button')) return; // ignore clicks on the action button
        const userId = row.dataset.user;
        selectPatientForMedical(userId, row);
      });
    });

  } catch(err){ console.error('Failed loading patients', err); tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Failed to load patients.</td></tr>`; }
}

/* Patient details modal helper and wiring (used by Patients list) */
function pick(obj, ...keys){ for(const k of keys){ try{ if(obj && obj[k] != null && String(obj[k]).trim() !== '') return obj[k]; }catch(e){} } return ''; }

async function openPatientInfoModal(patient_user_id){
  if(!patient_user_id) return alert('No patient id');
  const modal = document.getElementById('patientInfoModal');
  const closeBtn = document.getElementById('patientInfoClose');
  const viewAppts = document.getElementById('patientViewAppts');
  try{
    const res = await fetch('doctor_get_patient_details.php?patient_user_id=' + encodeURIComponent(patient_user_id), { credentials: 'include' });
    if(!res.ok) return alert('Failed to load patient details');
    const j = await res.json().catch(()=>null);
    if(!j || !j.success || !j.data) return alert(j && j.message ? j.message : 'No profile found');
    const r = j.data || {};
    // populate fields (flexible keys)
    try{ document.getElementById('mi_name').textContent = pick(r,'patient_name','name','full_name') || '';}catch(e){}
    try{ document.getElementById('mi_age').textContent = pick(r,'age','patient_age') || ''; }catch(e){}
    try{ document.getElementById('mi_address').textContent = pick(r,'address','addr','location') || ''; }catch(e){}
    try{ document.getElementById('mi_bday').textContent = pick(r,'birthdate','birthday','bday') || ''; }catch(e){}
    try{ document.getElementById('mi_mobile').textContent = pick(r,'mobile_number','mobile','cellphone','phone') || ''; }catch(e){}
    try{ document.getElementById('mi_email').textContent = pick(r,'email','user_email','email_address') || ''; }catch(e){}
    try{ document.getElementById('mi_civil_status').textContent = pick(r,'civil_status','civilstatus') || ''; }catch(e){}
    try{ document.getElementById('mi_nationality').textContent = pick(r,'nationality') || ''; }catch(e){}
    try{ document.getElementById('mi_religion').textContent = pick(r,'religion') || ''; }catch(e){}
    try{ document.getElementById('mi_blood_type').textContent = pick(r,'blood_type','bloodtype') || ''; }catch(e){}
    try{ document.getElementById('mi_allergies').textContent = pick(r,'allergies') || ''; }catch(e){}
    try{ document.getElementById('mi_past_medical').textContent = pick(r,'past_medical_conditions','past_medical') || ''; }catch(e){}
    try{ document.getElementById('mi_current_medications').textContent = pick(r,'current_medications') || ''; }catch(e){}
    try{ document.getElementById('mi_obstetric').textContent = pick(r,'obstetric_history','obstetric') || ''; }catch(e){}
    try{ document.getElementById('mi_pregnancies').textContent = pick(r,'number_of_pregnancies','pregnancies') || ''; }catch(e){}
    try{ document.getElementById('mi_deliveries').textContent = pick(r,'number_of_deliveries','deliveries') || ''; }catch(e){}
    try{ document.getElementById('mi_lmp').textContent = pick(r,'lmp') || ''; }catch(e){}
    try{ document.getElementById('mi_edd').textContent = pick(r,'edd') || ''; }catch(e){}
    try{ document.getElementById('mi_prev_preg_complication').textContent = pick(r,'previous_pregnancy_complication','prev_pregnancy_complication') || ''; }catch(e){}

    // set dataset for view appointments
    if(viewAppts) viewAppts.dataset.user = String(patient_user_id);
    if(modal){ modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false'); }
    // focus close for accessibility
    try{ if(closeBtn) closeBtn.focus(); }catch(e){}
  }catch(err){ console.error('openPatientInfoModal error', err); alert('Failed to load patient details'); }
}

// modal controls: close + view appointments
document.getElementById('patientInfoClose')?.addEventListener('click', ()=>{
  const m = document.getElementById('patientInfoModal'); if(m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); }
});
document.getElementById('patientViewAppts')?.addEventListener('click', (e)=>{
  const uid = e.currentTarget.dataset.user; const m = document.getElementById('patientInfoModal'); if(m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); }
  if(uid) setTimeout(()=> loadManageAppointments(uid), 80);
});

/* Initial load: activate the currently marked sidebar item (or default to Patients)
   This ensures the matching panel is unhidden and its data loader runs. */
const initialNav = document.querySelector('nav.sidebar .nav-item.active') || document.querySelector('nav.sidebar .nav-item[data-panel="patients"]');
if(initialNav){
  // trigger the same behavior as a user click
  initialNav.click();
} else {
  // fallback: load patients list and show the patients panel
  loadPatients();
  const p = document.getElementById('panel-patients'); if(p) p.hidden = false;
}
// patient info modal close is handled above with proper focus/aria management

// Ensure filename display updates when a file is selected (attach once)
(function(){
  const fileEl = document.getElementById('labFile');
  if(!fileEl) return;
  fileEl.addEventListener('change', function(){
    try{
      const nameWrap = document.getElementById('labFileName');
      if(!nameWrap) return;
      if(this.files && this.files.length) nameWrap.textContent = this.files[0].name; else nameWrap.textContent = 'No file chosen';
    }catch(e){}
  });
})();

// Ensure prescription filename display updates when a file is selected
(function(){
  const fileEl = document.getElementById('prescriptionFile');
  if(!fileEl) return;
  fileEl.addEventListener('change', function(){
    try{
      const nameWrap = document.getElementById('prescriptionFileName');
      if(!nameWrap) return;
      if(this.files && this.files.length) nameWrap.textContent = this.files[0].name; else nameWrap.textContent = 'No file chosen';
    }catch(e){}
  });
})();

// Prescription file preview modal handlers
function showPrescriptionModal(fileUrl){
  try{
    const modal = document.getElementById('prescriptionViewModal');
    const body = document.getElementById('prescriptionViewBody');
    const download = document.getElementById('prescriptionDownloadLink');
    if(!modal || !body) return;
    // clear body
    body.innerHTML = '';
    download.href = fileUrl || '#';
    const lower = (fileUrl || '').toLowerCase();
    // Normalize bare filenames (e.g. "1763313911_...pdf") into the uploads path so
    // the HEAD request doesn't go to the site root and return 404.
    (async ()=>{
      try{
        const isAbsolute = /^(https?:)?\/\//i.test(String(fileUrl || '')) || String(fileUrl || '').startsWith('/');
        const looksLikeBareFilename = !!fileUrl && !isAbsolute && String(fileUrl).indexOf('/') === -1;
        const scriptDir = (window.location && window.location.pathname) ? window.location.pathname.replace(/\/[^/]*$/, '') : '';
        if(looksLikeBareFilename){
          // derive script base (e.g. '/drea') from current pathname and prefix uploads folder
          const candidate = (scriptDir ? scriptDir + '/' : '/') + 'assets/uploads/results_uploaded/' + String(fileUrl);
          fileUrl = candidate.replace(/\/+/g, '/');
        }

        // if fileUrl is relative without leading slash, prefix with scriptDir to make root-relative
        if(fileUrl && !/^(https?:)?\/\//i.test(fileUrl) && !fileUrl.startsWith('/')){
          fileUrl = (scriptDir ? scriptDir + '/' : '/') + String(fileUrl);
        }

        // If fileUrl looks like '/drea/<filename>.pdf' (missing uploads folder), rewrite to uploads path
        try{
          if(fileUrl && typeof fileUrl === 'string'){
            const parts = fileUrl.split('/').filter(Boolean);
            const last = parts.length ? parts[parts.length-1] : '';
            if(last && /\.(pdf|png|jpe?g|gif|bmp|webp)$/i.test(last)){
              const containsUploads = fileUrl.indexOf('assets/uploads') !== -1 || fileUrl.indexOf('uploads/results_uploaded') !== -1;
              // typical bad form: ['/drea', '17633...pdf'] -> rewrite
              if(!containsUploads && parts.length === 2){
                // parts[0] is scriptDir without leading slash, parts[1] is filename
                fileUrl = '/' + parts[0] + '/assets/uploads/results_uploaded/' + last;
              }
            }
          }
        }catch(_){ /* ignore */ }
      }catch(_){ /* ignore normalization errors and proceed */ }

      // preflight: try a HEAD request to make sure the file exists before embedding
      
      if(!fileUrl){
        body.innerHTML = '<div style="padding:12px;color:var(--muted)">No file specified.</div>';
        modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
        return;
      }
      let ok = false;
      try{
        const h = await fetch(fileUrl, { method: 'HEAD', credentials: 'include' });
        ok = h && h.ok;
      }catch(_){ ok = false; }

      if(!ok){
        // file not reachable — show friendly message and keep download link so user can try
        body.innerHTML = `<div style="padding:20px;text-align:center;color:var(--muted)">The file could not be loaded (not found or inaccessible). Use the Download link to try opening it directly.</div>`;
        modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
        try{ const doneBtn = document.getElementById('prescriptionViewDone'); if(doneBtn) doneBtn.focus(); }catch(e){}
        return;
      }

      // file exists — embed according to file type
      try{
        if(lower.endsWith('.pdf')){
          const iframe = document.createElement('iframe');
          iframe.src = fileUrl;
          iframe.style.width = '100%'; iframe.style.height = '100%'; iframe.style.border = '0';
          body.appendChild(iframe);
        } else if(lower.match(/\.(png|jpe?g|gif|bmp|webp)$/)){
          const img = document.createElement('img');
          img.src = fileUrl;
          img.style.maxWidth = '100%'; img.style.maxHeight = '100%'; img.style.objectFit = 'contain';
          body.appendChild(img);
        } else {
          const a = document.createElement('a'); a.href = fileUrl; a.target = '_blank'; a.textContent = 'Open file in new tab / download';
          body.appendChild(a);
        }
        modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
        try{ const doneBtn = document.getElementById('prescriptionViewDone'); if(doneBtn) doneBtn.focus(); }catch(e){}
      }catch(e){ console.error('Error embedding file in modal', e); body.innerHTML = '<div style="padding:12px;color:var(--muted)">Unable to display file.</div>'; modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false'); }
    })();
    // save last focused element so we can restore focus on close
    try{ window.__lastFocusedBeforePrescriptionModal = document.activeElement; }catch(e){}
    modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
    // move focus into the modal (to a sensible control) for accessibility
    try{ const doneBtn = document.getElementById('prescriptionViewDone'); if(doneBtn) doneBtn.focus(); }catch(e){}
  }catch(e){ console.error('showPrescriptionModal error', e); window.open(fileUrl, '_blank'); }
}

function closePrescriptionModal(){
  const modal = document.getElementById('prescriptionViewModal');
  const body = document.getElementById('prescriptionViewBody');
  if(!modal) return;
  // move focus out of the modal before hiding it to avoid aria-hidden on a focused element
  try{
    // prefer restoring previous focused element
    const prev = window.__lastFocusedBeforePrescriptionModal;
    // blur any focused element inside the modal first
    if(document.activeElement && modal.contains(document.activeElement)){
      try{ document.activeElement.blur(); }catch(e){}
    }
    if(prev && typeof prev.focus === 'function'){
      try{ prev.focus(); }catch(e){}
    } else {
      try{ document.body.focus(); }catch(e){}
    }
  }catch(e){ /* ignore focus restore errors */ }

  modal.style.display = 'none';
  modal.setAttribute('aria-hidden','true');
  if(body) body.innerHTML = '';
}

document.getElementById('prescriptionViewClose')?.addEventListener('click', ()=> closePrescriptionModal());
document.getElementById('prescriptionViewDone')?.addEventListener('click', ()=> closePrescriptionModal());

// Delegate clicks on prescription file links to open modal (links have class `prescription-file-link`)
document.addEventListener('click', function(e){
  const a = e.target.closest && (e.target.closest('a.prescription-file-link') || e.target.closest('a.screening-file-link'));
  if(!a) return;
  try{ e.preventDefault(); const file = a.dataset.file || a.getAttribute('href'); if(file) showPrescriptionModal(file); } catch(err){ console.error(err); }
});

/* Make sure manage panel loads when user clicks Manage in sidebar */
document.querySelectorAll('nav.sidebar .nav-item').forEach(item=>{
  if(item.dataset.panel === 'manage'){
    item.addEventListener('click', ()=> loadManageAppointments());
  }
});

// ensure patients nav item loads patients
document.querySelectorAll('nav.sidebar .nav-item').forEach(item=>{
  if(item.dataset.panel === 'patients'){
    item.addEventListener('click', ()=> loadPatients());
  }
});

/* Periodically refresh overview (optional) */
setInterval(()=> {
  const active = document.querySelector('nav.sidebar .nav-item.active')?.dataset.panel;
  if(active === 'patients') loadPatients();
}, 60_000); /* refresh every 60s */

// Mobile sidebar toggle (hamburger)
document.addEventListener('DOMContentLoaded', function(){
  const hb = document.getElementById('hamburgerBtn');
  const sidebar = document.querySelector('nav.sidebar');
  if(!hb || !sidebar) return;
  let overlay = null;
  function ensureOverlay(){
    if(overlay) return overlay;
    overlay = document.getElementById('mobileSidebarOverlay');
    if(!overlay){ overlay = document.createElement('div'); overlay.id = 'mobileSidebarOverlay'; overlay.style.display='none'; document.body.appendChild(overlay); }
    overlay.addEventListener('click', closeSidebar);
    return overlay;
  }
  function openSidebar(){
    ensureOverlay();
    sidebar.classList.add('mobile-open');
    overlay.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar(){
    sidebar.classList.remove('mobile-open');
    if(overlay) overlay.style.display = 'none';
    document.body.style.overflow = '';
  }
  hb.addEventListener('click', function(e){
    e.stopPropagation();
    if(sidebar.classList.contains('mobile-open')) closeSidebar(); else openSidebar();
  });
  // close on Escape or when resizing to wide
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeSidebar(); });
  window.addEventListener('resize', function(){ if(window.innerWidth > 900) closeSidebar(); });
});

</script>
</body>
</html>

<!-- Doctor Customize Profile Modal (added) -->
<div id="doctorCustomizeModal" class="confirm-modal" aria-hidden="true" style="display:none">
  <div class="dialog" role="dialog" aria-modal="true" style="max-width:720px;width:94%">
    <header style="display:flex;justify-content:space-between;align-items:center">
      <h4 style="margin:0;color:var(--lav-4)">Customize Profile</h4>
      <button class="btn-cancel" type="button" onclick="closeDoctorCustomizeModal()">Close</button>
    </header>
    <div style="margin-top:12px">
      <form id="doctorCustomizeForm" enctype="multipart/form-data">
        <div style="display:flex;gap:16px;align-items:center;margin-bottom:12px">
          <div style="width:120px;height:120px;border-radius:8px;overflow:hidden;border:1px solid #eee;background:#fff;display:flex;align-items:center;justify-content:center">
            <img id="docModalAvatarImg" src="<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'assets/images/logodrea.jpg') ?>" alt="avatar" style="width:100%;height:100%;object-fit:cover">
          </div>
          <div>
            <label class="btn">Choose avatar
              <input type="file" id="docModalAvatarInput" name="avatar" accept="image/*" style="display:none">
            </label>
            <div style="margin-top:8px;color:var(--muted);font-size:0.95rem">Max 5MB. jpg/png/gif</div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div class="form-row"><label>Full name</label><input id="doc_name" name="name" type="text" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>"></div>
          <div class="form-row"><label>Specialty</label><input id="doc_specialty" name="specialty" type="text" value=""></div>
          <div class="form-row"><label>Phone</label><input id="doc_phone" name="phone" type="text" value="<?= htmlspecialchars($_SESSION['user_mobile'] ?? '') ?>"></div>
          <div class="form-row"><label>Email</label><input id="doc_email" name="email" type="email" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>"></div>
          <div class="form-row" style="grid-column:1 / -1"><label>Clinic Address</label><input id="doc_clinic_address" name="clinic_address" type="text" value=""></div>
          <div class="form-row" style="grid-column:1 / -1"><label>Bio</label><textarea id="doc_bio" name="bio" rows="3"></textarea></div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
          <button type="button" class="btn-cancel" onclick="closeDoctorCustomizeModal()">Cancel</button>
          <button type="submit" class="btn-pill">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Doctor customize modal handlers
async function openDoctorCustomizeModal(){
  try{
    const m = document.getElementById('doctorCustomizeModal');
    if(!m) return;

    // fetch latest doctor info from server and populate fields
    try{
      const res = await fetch('get_midwife_info.php', { credentials: 'same-origin' });
      if(res.ok){
        const j = await res.json();
        if(j && j.success){
          const d = j.data || {};
          document.getElementById('doc_name').value = d.name || document.getElementById('doc_name').value || '';
          document.getElementById('doc_specialty').value = d.specialty || '';
          document.getElementById('doc_phone').value = d.phone || document.getElementById('doc_phone').value || '';
          document.getElementById('doc_email').value = d.email || document.getElementById('doc_email').value || '';
          document.getElementById('doc_clinic_address').value = d.clinic_address || '';
          document.getElementById('doc_bio').value = d.bio || '';
          if(d.avatar_url) document.getElementById('docModalAvatarImg').src = d.avatar_url;
          window.doctorProfile = d;
        }
      } else {
        console.warn('get_midwife_info.php responded with', res.status);
      }
    }catch(fetchErr){
      console.warn('Failed to fetch doctor info', fetchErr);
    }

    m.style.display = 'flex'; m.setAttribute('aria-hidden','false');
  }catch(e){ console.error('openDoctorCustomizeModal', e); }
}

function closeDoctorCustomizeModal(){ const m = document.getElementById('doctorCustomizeModal'); if(m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); } }

document.getElementById('docModalAvatarInput')?.addEventListener('change', function(){
  const f = this.files && this.files[0]; if(!f) return;
  if(!f.type || !f.type.startsWith('image/')){ showToast('Please choose an image file.', 'error'); this.value=''; return; }
  if(f.size > 3*1024*1024){ showToast('Image too large (max 3MB).', 'error'); this.value=''; return; }
  const reader = new FileReader(); reader.onload = e=> { document.getElementById('docModalAvatarImg').src = e.target.result; }; reader.readAsDataURL(f);
});

document.getElementById('doctorCustomizeForm')?.addEventListener('submit', async function(e){
  e.preventDefault();
  const formEl = document.getElementById('doctorCustomizeForm');
  const submitBtn = formEl.querySelector('button[type="submit"]');
  if(submitBtn) submitBtn.disabled = true;

  const fd = new FormData(formEl);
  try {
  showToast('Saving profile...', 'info', 2000);
  const res = await fetch('save_midwife_info.php', { method: 'POST', body: fd, credentials: 'same-origin' });
    const text = await res.text();
    let data = {};
    try { data = text ? JSON.parse(text) : {}; } catch(err){ console.error('Non-JSON response from save_midwife_info.php:', text); showToast('Server returned an unexpected response. Check server logs and console.', 'error'); return; }

    if (!res.ok) {
      console.error('Server error while saving midwife details:', res.status, data);
      showToast('Server error: ' + (data.message || res.status), 'error');
      return;
    }

    if (data.success) {
      showToast(data.message || 'Profile saved successfully', 'success');
      if (data.data) {
        window.doctorProfile = data.data;
        try{ populateProfile(data.data); }catch(e){}
      } else {
        // fetch fresh profile
        try{ const gd = await fetch('get_doctor_info.php', { credentials: 'same-origin' }); if(gd.ok){ const jd = await gd.json(); if(jd && jd.success && jd.data){ window.doctorProfile = jd.data; try{ populateProfile(jd.data); }catch(e){} } } } catch(_){}
      }
      closeDoctorCustomizeModal();
    } else {
      showToast('Error: ' + (data.message || 'Could not save'), 'error');
    }
  } catch (err) {
    console.error('Network/fetch error while saving doctor details:', err);
    showToast('Network error: ' + (err && err.message ? err.message : 'Failed to send request'), 'error');
  } finally {
    if(submitBtn) submitBtn.disabled = false;
  }
});
</script>
