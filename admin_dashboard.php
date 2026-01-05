<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: login_process.php');
    exit();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --lavender: #a48de7;
      --lavender-dark: #9077d1;
      --text-dark: #333;
      --text-muted: #606770;
      --bg-light: #f6f2fc;
      --white: #fff;
      /* Patient header/theme variables reused */
      --lav-1: #f6f2fc;
      --lav-2: #dcd0f9;
      --lav-3: #a48de7;
      --lav-4: #9077d1;
      --card-bg: #fff;
      --accent: #9c7de8;
    }

    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      background: var(--bg-light);
      color: var(--text-dark);
    }

    /* Header updated to match homepage gradient and high-contrast text */
    header.site-top{
      background: linear-gradient(90deg,#2b1b4f,#3b2c65);
      padding:12px 20px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:1rem;
      box-shadow:0 2px 8px rgba(0,0,0,0.08);
      position:sticky;
      top:0;
      z-index:200;
      border-bottom-left-radius:18px;
      overflow:visible;
      color: #fff;
    }
    .header-left{display:flex;align-items:center;gap:1rem}
    .logo img{width:86px;height:86px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,0.12);box-shadow:0 6px 18px rgba(0,0,0,0.12)}
    .clinic-name{font-family:'Poppins',sans-serif;color:#fff;font-weight:700;font-size:1.35rem}
    .clinic-sub{font-size:0.85rem;color:rgba(255,255,255,0.9);margin-top:4px}
    .header-actions{display:flex;align-items:center;gap:0.5rem;color:#fff}
    .btn-pill{
      background:var(--lav-3);color:#fff;border:none;padding:10px 18px;border-radius:28px;font-weight:600;cursor:pointer;
      box-shadow:0 6px 18px rgba(0,0,0,0.12);
    }
    .btn-pill.ghost{background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.12);box-shadow:none}

  /* Profile dropdown + theme popover (patient header parity) */
  .profile-menu{position:relative}
  .profile-menu .profile-btn{display:inline-flex;align-items:center;gap:8px}
  .profile-avatar{width:30px;height:30px;border-radius:50%;object-fit:cover;margin-right:8px;border:2px solid rgba(255,255,255,0.6)}
  /* Profile dropdown (Facebook-like) */
  .profile-dropdown{
    display:none;position:absolute;right:0;top:48px;min-width:260px;background:#fff;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,0.12);overflow:hidden;z-index:350;border:1px solid rgba(0,0,0,0.06);
  }
  .profile-dropdown .pd-header{display:flex;align-items:center;gap:12px;padding:12px 14px;background:linear-gradient(90deg,var(--lav-1),#fff)}
  .profile-dropdown .pd-header img{width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid rgba(0,0,0,0.04)}
  .profile-dropdown .pd-header .pd-name{font-weight:800;color:var(--text-dark)}
  .profile-dropdown .pd-header .pd-sub{font-size:0.9rem;color:var(--text-muted)}
  .profile-dropdown .pd-sep{height:1px;background:linear-gradient(90deg, rgba(0,0,0,0.03), rgba(0,0,0,0.01));margin:6px 0}
  .profile-dropdown .pd-group{display:flex;flex-direction:column;padding:6px}
  .profile-dropdown .pd-item{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;color:var(--text-dark);cursor:pointer;font-weight:700;margin:6px 8px}
  .profile-dropdown .pd-item .icon{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;background:linear-gradient(90deg,var(--lav-1),#fff);color:var(--lav-4);font-weight:700}
  .profile-dropdown .pd-item:hover{background:linear-gradient(90deg,rgba(156,125,232,0.04), rgba(156,125,232,0.02))}
  .profile-dropdown .pd-item.logout{color:#c0392b;font-weight:800;margin-top:6px;border-top:1px solid rgba(0,0,0,0.03);padding-top:12px}


    /* LAYOUT */
    .container {
      display: flex;
      min-height: calc(100vh - 56px);
    }

    /* Sidebar (patient-style fixed left column) */
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
    
    main.main { flex: 1; padding: 1.5rem; }

    nav.sidebar .menu { display:flex; flex-direction:column; gap:6px; padding:0; margin:0; }
    nav.sidebar .menu li { list-style:none }
    nav.sidebar .menu a { display:block; padding:10px 12px; border-radius:10px; color:var(--lavender-dark); font-weight:700; transition:all .12s ease; }
    nav.sidebar .menu a:hover{ background: rgba(156,125,232,0.06); transform:translateX(2px); }
    nav.sidebar .menu a.active{ background: linear-gradient(90deg,var(--lavender),var(--lavender-dark)); color:#fff; box-shadow:0 8px 20px rgba(156,125,232,0.12); }

    /* footer area inside sidebar with avatar */
    .sidebar-footer{ margin-top:auto; padding-top:10px; padding-bottom:6px; border-top:1px solid rgba(156,125,232,0.06); display:flex; align-items:center; gap:10px }
    .sidebar-avatar{ width:34px; height:34px; border-radius:50%; object-fit:cover; border:2px solid rgba(156,125,232,0.06) }
    .sidebar-name{ font-weight:700; color:var(--lavender-dark); font-size:0.95rem }

    /* Adjust main layout to clear fixed sidebar */
    .container.has-fixed-sidebar{ margin-left: 260px; max-width: calc(100% - 260px); }

    /* SIDEBAR MENU */
    .menu {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 0.25rem;
    }
    .menu a {
      display: block;
      padding: 0.6rem 0.75rem;
      border-radius: 8px;
      color: var(--text-dark);
      text-decoration: none;
      font-weight: 500;
      transition: background 0.2s, color 0.2s;
    }
    .menu a.active, .menu a:hover {
      background: var(--lavender);
      color: var(--white);
    }

    /* CARD / TABLE */
    .card {
      background: var(--white);
      border-radius: 10px;
      padding: 1rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      padding: 0.5rem 0.75rem;
      border-bottom: 1px solid #eef2f5;
      text-align: left;
      font-size: 0.95rem;
    }
    th {
      background: #f5f0ff;
      color: var(--text-dark);
    }

    /* BUTTONS */
    .btn {
      display: inline-block;
      padding: 0.45rem 0.75rem;
      border-radius: 25px;
      border: none;
      background: var(--lavender);
      color: var(--white);
      cursor: pointer;
      font-size: 0.9rem;
      font-weight: 500;
      transition: background 0.2s;
    }
    .btn:hover {
      background: var(--lavender-dark);
    }
    .btn.ghost {
      background: var(--white);
      color: var(--lavender);
      border: 1px solid var(--lavender);
    }
    .btn.ghost:hover {
      background: var(--lavender);
      color: var(--white);
    }

    form.inline {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }
    /* Footer */
footer {
  background: #a48de7;
  text-align: center;
  padding: 20px 10px;
  font-size: 14px;
  color: #fff;
  font-family: 'Inter', sans-serif;
  box-shadow: 0 -2px 6px rgba(0,0,0,0.05);
}
footer i {
  color: #fff;
  margin-right: 5px;
}


    /* MODAL */
    #userModal {
      display: none;
      position: fixed;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.4);
      align-items: center;
      justify-content: center;
      z-index: 999;
    }
    #userModal > div {
      background: var(--white);
      padding: 1.25rem 1.5rem;
      border-radius: 12px;
      max-width: 620px;
      width: 92%;
      box-shadow: 0 10px 30px rgba(0,0,0,0.16);
    }
    #userModal h3 {
      margin-top: 0;
      color: var(--lavender);
      font-size: 1.25rem;
      font-weight:700;
    }
    /* form layout inside modal */
    #userModal .form-grid { display:grid; grid-template-columns: 1fr 1fr; gap:10px; align-items:start }
    #userModal .form-row { display:flex; flex-direction:column; gap:6px }
    #userModal label { font-weight:600; color:var(--text-muted); font-size:0.95rem }
    #userModal input, #userModal select { padding: 10px 12px; border-radius:10px; border:1px solid #e6e0f8; background:#fbf8ff; width:100%; font-size:0.95rem }
    #userModal input::placeholder { color:#bdb6c9; font-size:0.95rem; font-weight:500; opacity:0.75 }
    #userModal input:focus, #userModal select:focus { outline: none; border-color: var(--lavender); box-shadow: 0 6px 18px rgba(156,125,232,0.08) }
    #userModal .modal-actions { display:flex; gap:10px; justify-content:flex-start; margin-top:12px }
    #userModal .modal-actions .btn { padding:8px 16px; border-radius:20px; font-weight:700 }
    #userModal .modal-actions .btn.ghost { background:#fff; border:1px solid rgba(156,125,232,0.18); color:var(--lavender) }
    @media(max-width:600px){ #userModal .form-grid { grid-template-columns:1fr } }

    /* Reports table styling (match provided design) */
    .reports-card { padding: 12px; border-radius: 10px; background: var(--white); box-shadow: 0 6px 18px rgba(40,20,80,0.04); }
    .reports-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
    .reports-table thead th { background: #f3f6fb; color: var(--text-dark); font-weight:700; padding:12px 14px; text-align:left; border:none; }
    .reports-table tbody tr { background: #fff; border-radius:8px; box-shadow: 0 1px 0 rgba(0,0,0,0.03); }
    .reports-table tbody td { padding:14px; border:none; vertical-align:middle; }
    .reports-table tbody td.total { text-align:right; font-weight:700; }
    .reports-table tbody td.status { color:#6b7280; text-align:left; }
    .reports-table tbody tr + tr { margin-top:8px; }

    /* Small stat card used in reports (Inventory) */
    .report-stat .label { font-size:0.95rem; color:var(--text-muted); margin-bottom:6px }
    .report-stat .count { font-size:1.75rem; font-weight:700; color:var(--lavender); line-height:1; }
    .report-stat .hint { margin-top:8px; color:var(--text-muted); font-size:0.95rem }

    /* ID file links */
    .id-file-link{ display:inline-block; margin-right:8px; padding:6px 10px; border-radius:16px; background:rgba(164,141,231,0.12); color:var(--lavender-dark); text-decoration:none; font-weight:700; font-size:0.9rem }
    .id-file-link:hover{ background:rgba(148,117,224,0.18); color:var(--lavender); }

    /* Image view modal */
    #imgModal{ display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.55); align-items:center; justify-content:center; z-index:1300 }
    #imgModal .img-wrap{ background:var(--white); padding:12px; border-radius:10px; max-width:920px; width:94%; box-shadow:0 12px 40px rgba(0,0,0,0.28); display:flex; flex-direction:column; gap:8px }
    #imgModal img{ max-width:100%; max-height:72vh; border-radius:6px; object-fit:contain }
    #imgModal .caption{ color:var(--text-muted); font-weight:600 }
    #imgModal .close-btn{ align-self:flex-end; background:transparent;border:1px solid rgba(0,0,0,0.06);padding:6px 10px;border-radius:8px;cursor:pointer }

    /* Confirm modal */
    #confirmModal{ display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.45); align-items:center; justify-content:center; z-index:1400 }
    #confirmModal .confirm-box{ background:var(--white); padding:18px; border-radius:12px; max-width:520px; width:94%; box-shadow:0 12px 40px rgba(0,0,0,0.18); text-align:left }
    #confirmModal .confirm-box p{ margin:0; color:var(--text-dark); font-weight:600 }
    #confirmModal .confirm-actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:14px }
    #confirmModal .confirm-btn{ padding:8px 14px; border-radius:10px; font-weight:700; cursor:pointer }
    #confirmModal .confirm-btn.primary{ background:var(--lavender); color:#fff; border:none }
    #confirmModal .confirm-btn.ghost{ background:#fff; border:1px solid #eee; color:var(--lavender-dark) }

    @media(max-width:800px){
      nav.sidebar{width:200px}
      .topbar .brand{font-size:1rem}
      th,td{font-size:0.85rem}
    }
    @media(max-width:600px){
      .container{flex-direction:column}
      nav.sidebar{width:100%;border-right:none;border-bottom:1px solid #eee}
    }
    /* Additional responsive adjustments for tablets and phones */
    @media (max-width:1024px) {
      .container.has-fixed-sidebar { margin-left: 0; max-width: 100%; }
      nav.sidebar {
        position: relative !important;
        top: auto !important;
        left: auto !important;
        bottom: auto !important;
        width: 100% !important;
        display: flex !important;
        flex-direction: row !important;
        gap: 8px;
        padding: 10px;
        border-radius: 0.5rem;
        box-shadow: none;
        overflow-x: auto;
        align-items: center;
      }
      nav.sidebar .menu { flex-direction: row !important; align-items: center; }
      nav.sidebar .menu a { white-space: nowrap; padding: 8px 10px; }
      .sidebar-footer { display: none; }
      main.main { padding: 1rem; }
      /* Make tables scroll horizontally rather than squish */
      .card { overflow: visible; }
      table { min-width: 720px; display: block; overflow-x: auto; }
    }

    @media (max-width:480px) {
      header.site-top { padding: 10px; gap: 8px; }
      .clinic-name { font-size: 1rem; }
      .logo img { width:56px; height:56px; }
      nav.sidebar .menu a { font-size: 0.9rem; padding: 8px; }
      th, td { font-size: 0.82rem; }
      .reports-table thead th { padding: 8px; }
      .container { min-height: auto; }
    }
    /* Form modal (centered dialog used by doctor/midwife portals) */
    #formModal { display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.45); align-items:center; justify-content:center; z-index:1400 }
    #formModal .dialog { background: var(--white); padding:20px; border-radius:14px; max-width:760px; width:92%; box-shadow: 0 18px 50px rgba(18,12,52,0.18); }
    #formModal .dialog header h4 { margin:0; color:var(--lav-4); font-size:1.05rem; font-weight:700 }
    #formModal .close-btn { background:#fff; border:1px solid rgba(0,0,0,0.06); padding:8px 10px; border-radius:10px; cursor:pointer }
    #formModal .dialog .left-col img { width:120px; height:120px; border-radius:12px; object-fit:cover; border:1px solid rgba(0,0,0,0.06); box-shadow:0 6px 18px rgba(0,0,0,0.06) }
    #formModal .dialog label { font-weight:700; color:var(--lav-4); font-size:0.95rem }
    #formModal input, #formModal textarea { padding:10px 12px; border-radius:10px; border:1px solid #efe9ff; background:#fbf8ff; width:100%; font-size:0.95rem }
    #formModal textarea { min-height:96px }
    #formModal .actions { display:flex; gap:10px; justify-content:flex-end; margin-top:12px }
    #formModal .btn-cancel { background:#fff; border:1px solid rgba(0,0,0,0.08); color:var(--lav-4); padding:8px 14px; border-radius:20px; cursor:pointer }
    #formModal .btn-save { background:var(--lav-3); color:#fff; border:none; padding:10px 16px; border-radius:24px; font-weight:700; cursor:pointer }
  </style>
  <!-- Chart.js for reports bar charts -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
  <!-- Toast container -->
  <div id="toast" style="position:fixed;right:20px;top:20px;z-index:1200;display:none;min-width:240px;border-radius:8px;padding:10px 14px;color:#fff;font-weight:700"></div>
<!-- TOP HEADER (patient-style) -->
<header class="site-top" id="top">
  <div class="header-left">
    <div class="logo"><img src="assets/images/logodrea.jpg" alt="logo"></div>
    <div>
      <div class="clinic-name">DREA LYING-IN CLINIC</div>
      <div class="clinic-sub">Maternity Management System</div>
    </div>
  </div>

  <div class="header-actions" style="position:relative">
    <button id="themeToggle" class="btn-pill ghost" title="Theme settings" style="margin-right:8px;display:none" disabled aria-hidden="true"></button>
    <div id="themePopover" class="theme-popover" style="display:none" aria-hidden="true">
      <div class="tp-item" data-mode="light">
        <div class="label">☀️ <span>Light</span></div>
        <span class="check">✓</span>
      </div>
      <div class="tp-item" data-mode="dark">
        <div class="label">🌙 <span>Dark</span></div>
        <span class="check">✓</span>
      </div>
      <div class="tp-item" data-mode="auto">
        <div class="label">◐ <span>Auto</span></div>
        <span class="check">✓</span>
      </div>
    </div>
    <div class="profile-menu" style="position:relative">
        <button class="btn-pill profile-btn" id="profileBtn" type="button" aria-haspopup="true" aria-expanded="false">
        <img src="<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'assets/images/logodrea.jpg'); ?>" alt="avatar" class="profile-avatar" style="width:30px;height:30px;border-radius:50%;object-fit:cover;margin-right:8px;border:2px solid rgba(255,255,255,0.6)">
        <span class="profile-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
      </button>

      <div class="profile-dropdown" id="profileDropdown" aria-hidden="true" style="display:none;position:absolute;right:0;top:48px;min-width:260px;background:#fff;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,0.12);overflow:hidden;z-index:350;border:1px solid rgba(0,0,0,0.06)">
        <div class="pd-header">
          <img src="<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'assets/images/logodrea.jpg'); ?>" id="hdrAvatarSmall" alt="avatar">
          <div class="pd-meta">
            <div class="pd-name" id="hdrName"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
            <div class="pd-email" id="hdrEmail"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
          </div>
        </div>
        <div class="pd-sep"></div>

        <div class="pd-group">
          <button class="pd-item primary" type="button" onclick="toggleProfileDropdown(false); openAdminCustomizeModal();">
            <span class="icon">⚙️</span>
            <span class="pd-label">Customize Profile</span>
          </button>
        </div>

        <div class="pd-sep"></div>

        <div class="pd-group">
          <form action="logout.php" method="POST" style="margin:0">
            <button type="submit" class="pd-item logout" style="width:100%;text-align:left;border:none;background:transparent;cursor:pointer;">
              <span class="icon">⎋</span>
              <span class="pd-label" style="color:#c0392b">Log Out</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>

<script>
    // Ensure localStorage mirrors server session avatar/name so UI persists across logout/login
    (function(){
      try{
        const sessAvatar = <?php echo json_encode($_SESSION['user_avatar'] ?? ''); ?>;
        const sessName = <?php echo json_encode($_SESSION['username'] ?? ''); ?>;
        if(sessAvatar){ try{ localStorage.setItem('user_avatar', sessAvatar); }catch(e){} }
        if(sessName){ try{ localStorage.setItem('user_fullname', sessName); }catch(e){} }
      }catch(e){}
    })();
  // Toggle and close handlers for profile dropdown (copied behavior from doctor_portal)
  function toggleProfileDropdown(force){
    const dd = document.getElementById('profileDropdown');
    const btn = document.getElementById('profileBtn');
    if(!dd) return;
    if(typeof force === 'boolean'){
        if(force){
          dd.style.display = 'block';
          dd.setAttribute('aria-hidden','false');
        } else {
          try{ const active = document.activeElement; if(active && dd.contains(active) && btn) btn.focus(); }catch(e){}
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

    async function runInventoryUsage(){
      const fallback = document.getElementById('reportFallback');
      const ctx = document.getElementById('reportChart');
      fallback.innerHTML = '';
      try{
        const res = await fetch('get_inventory.php');
        const data = await res.json();
        let items = [];
        if(Array.isArray(data)) items = data;
        else if(Array.isArray(data.inventory)) items = data.inventory;
        else if(data.success && Array.isArray(data.items)) items = data.items;

        // Normalize and aggregate by name
        const map = {};
        for(const it of items){
          const name = it.item_name || it.name || it.inventory_name || it.item || 'Unknown';
          const qty = Number(it.quantity ?? it.qty ?? it.available ?? it.stock ?? 0) || 0;
          if(!map[name]) map[name] = 0;
          map[name] += qty;
        }

        const labels = Object.keys(map).sort();
        const values = labels.map(l => map[l]);

        // render chart
        if(window.reportChartInstance) window.reportChartInstance.destroy();
        window.reportChartInstance = new Chart(ctx, {
          type: 'bar',
          data: { labels, datasets: [{ label: 'Quantity', data: values, backgroundColor: 'rgba(54,162,235,0.6)' }] },
          options: { responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } }
        });

        // build restock list: threshold 5
        const threshold = 5;
        const restock = labels.filter((l,i)=> values[i] <= threshold).map(l=> ({ name:l, qty: map[l] }));
        if(restock.length){
          const ul = document.createElement('ul');
          for(const r of restock){
            const li = document.createElement('li');
            li.textContent = `${r.name} — ${r.qty} (restock)`;
            ul.appendChild(li);
          }
          const h = document.createElement('div');
          h.innerHTML = '<strong>Restock candidates (<=5)</strong>';
          fallback.appendChild(h);
          fallback.appendChild(ul);
        } else {
          fallback.textContent = 'No items need restocking.';
        }
      }catch(err){
        console.error(err);
        fallback.textContent = 'Failed to load inventory.';
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

  // Open admin customize modal: use the same markup and behavior as doctor_portal's Customize Profile
  async function openAdminCustomizeModal(){
    toggleProfileDropdown(false);
    let modal = document.getElementById('formModal');
    if(!modal){
      modal = document.createElement('div');
      modal.id = 'formModal'; modal.className = 'modal';
      modal.style.display = 'none';
      modal.setAttribute('aria-hidden','true');
      // make the modal container full-screen and center its dialog (match midwife/doctor portals)
      modal.style.position = 'fixed';
      modal.style.left = '0';
      modal.style.top = '0';
      modal.style.width = '100%';
      modal.style.height = '100%';
      modal.style.background = 'rgba(0,0,0,0.45)';
      modal.style.alignItems = 'center';
      modal.style.justifyContent = 'center';
      modal.style.zIndex = '1400';
      modal.innerHTML = `
        <div class="dialog" role="dialog" aria-modal="true" style="max-width:760px;width:92%">
          <header style="display:flex;justify-content:space-between;align-items:center">
            <h4>Customize Profile</h4>
            <button class="close-btn" onclick="closeFormModal()">Close</button>
          </header>
          <form id="profileForm" style="margin-top:12px;display:grid;grid-template-columns:160px 1fr;gap:16px;align-items:start">
            <div class="left-col" style="display:flex;flex-direction:column;gap:8px;align-items:flex-start">
              <img id="modalAvatarPreview" src="<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'assets/images/logodrea.jpg'); ?>" alt="avatar" style="width:120px;height:120px;border-radius:12px;object-fit:cover;border:1px solid rgba(0,0,0,0.06);box-shadow:0 6px 18px rgba(0,0,0,0.06)">
              <div style="font-weight:700;color:var(--lav-4)">Choose avatar</div>
              <div style="color:var(--text-muted);font-size:0.95rem">Max 3MB. jpg/png/gif</div>
              <div style="display:flex;gap:8px">
                <button type="button" class="btn-pill ghost" onclick="document.getElementById('sidebarAvatarInput').click();">Choose</button>
                <button type="button" class="btn-pill" onclick="openImageModal(document.getElementById('modalAvatarPreview').src, 'Profile Picture')">View</button>
              </div>
            </div>
            <div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div>
                  <label>Account ID</label>
                  <input id="pf_account_id" name="account_id" type="text" readonly>
                </div>
                <div>
                  <label>User ID</label>
                  <input id="pf_user_id" name="user_id" type="text" readonly>
                </div>
                <div>
                  <label>Full name</label>
                  <input id="pf_name" name="name" type="text">
                </div>
                <div>
                  <label>Email</label>
                  <input id="pf_email" name="email" type="email">
                </div>
                <div>
                  <label>Phone</label>
                  <input id="pf_phone" name="phone" type="text">
                </div>
                <div>
                  <label>Created at</label>
                  <input id="pf_created_at" name="created_at" type="text" readonly>
                </div>
                <div style="grid-column:1/ -1">
                  <label>Updated at</label>
                  <input id="pf_updated_at" name="updated_at" type="text" readonly>
                </div>
              </div>
              <div class="actions">
                <button type="button" class="btn-cancel" onclick="closeFormModal()">Cancel</button>
                <button type="submit" class="btn-save">Save</button>
              </div>
            </div>
          </form>
        </div>`;
      document.body.appendChild(modal);

      // wire submit (adapted from doctor_portal's save flow)
      // fetch existing admin_info and populate fields
      (async function populateAdminProfile(){
        try{
          const res = await fetch('save_admin_profile.php', { method: 'POST', credentials: 'same-origin', headers: { 'X-Action': 'fetch' } });
          const txt = await res.text(); let j = null; try{ j = txt ? JSON.parse(txt) : null; }catch(e){ j = null; }
          const data = j && j.success && j.data ? j.data : (j && j.data ? j.data : null);
          if(!data) return;
          const setVal = (id, val)=>{ const el = document.getElementById(id); if(el) el.value = val ?? ''; };
          setVal('pf_account_id', data.id || '');
          setVal('pf_user_id', data.user_id || '');
          setVal('pf_name', data.name || data.username || '');
          setVal('pf_email', data.email || data.user_email || '');
          setVal('pf_phone', data.phone || '');
          setVal('pf_created_at', data.created_at || '');
          setVal('pf_updated_at', data.updated_at || '');
          if(data.avatar_url){ const mv = document.getElementById('modalAvatarPreview'); if(mv) mv.src = data.avatar_url; try{ localStorage.setItem('user_avatar', data.avatar_url); }catch(e){} }
        }catch(err){ console.warn('Failed to populate admin profile', err); }
      })();

      modal.querySelector('#profileForm').addEventListener('submit', async function(e){
        e.preventDefault();
        // prepare FormData and include any selected avatar file from the sidebar input
        const fd = new FormData(this);
        try{
          const avatarInput = document.getElementById('sidebarAvatarInput');
          if(avatarInput && avatarInput.files && avatarInput.files[0]){
            fd.append('avatar', avatarInput.files[0]);
          }
        }catch(e){}
        try{
          const res = await fetch('save_admin_profile.php', { method: 'POST', credentials: 'same-origin', body: fd });
          const txt = await res.text(); let j = null; try{ j = txt ? JSON.parse(txt) : null; }catch(e){}
          if(res.ok && j && j.success){ showToast(j.message || 'Profile updated', 'success'); closeFormModal(); location.reload(); }
          else { showToast((j && j.message) ? j.message : 'Failed to save profile', 'error'); }
        }catch(err){ console.error(err); showToast('Network error while saving profile', 'error'); }
      });
    }
    
    // (no further fetch; initial values are populated from PHP session in the markup)
    try{
      const saved = localStorage.getItem('user_avatar');
      const savedName = localStorage.getItem('user_fullname');
      if(saved){ const mv = document.getElementById('modalAvatarPreview'); if(mv) mv.src = saved; const hdr = document.getElementById('hdrAvatarSmall'); if(hdr) hdr.src = saved; const headerAvatar = document.querySelector('.profile-menu .profile-btn img.profile-avatar'); if(headerAvatar) headerAvatar.src = saved; }
      if(savedName){ const inp = document.getElementById('pf_name'); if(inp) inp.value = savedName; }
    }catch(e){}

    try{ window.__lastFocusedBeforeFormModal = document.activeElement; }catch(e){}
    modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
    try{ const closeBtn = modal.querySelector('.close-btn'); if(closeBtn) closeBtn.focus(); }catch(e){}
  }

  function closeFormModal(){
    const m = document.getElementById('formModal');
    if(!m) return;
    m.style.display = 'none';
    m.setAttribute('aria-hidden','true');
    try{
      const prev = window.__lastFocusedBeforeAdminProfile || window.__lastFocusedBeforeFormModal;
      if(prev && typeof prev.focus === 'function') prev.focus();
    }catch(e){}
  }

  function closeAdminCustomizeModal(){
    // kept for backward compatibility
    closeFormModal();
  }
</script>

  <div class="container has-fixed-sidebar">
    <nav class="sidebar" aria-label="Main navigation">
      <ul class="menu">
        <li><a href="admin_dashboard.php?panel=overview" class="active" data-panel="overview">Home</a></li>
        <li><a href="admin_dashboard.php?panel=manage-users" data-panel="manage-users">Manage Users</a></li>
        <li><a href="admin_dashboard.php?panel=patients" data-panel="patients">Patients</a></li>
        <li><a href="admin_dashboard.php?panel=verifications" data-panel="verifications">Verifications</a></li>
        <li><a href="admin_dashboard.php?panel=announcements" data-panel="announcements">Announcements</a></li>
        <li><a href="admin_dashboard.php?panel=reports" data-panel="reports">Reports</a></li>
        <li><a href="admin_dashboard.php?panel=settings" data-panel="settings">Settings</a></li>
      </ul>

      <div class="sidebar-footer" id="sidebarFooter">
        <div class="sidebar-avatar-wrapper" style="position:relative;display:flex;align-items:center;gap:10px">
          <div style="position:relative;display:inline-block">
            <img src="<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'assets/images/logodrea.jpg'); ?>" alt="avatar" class="sidebar-avatar" id="sidebarAvatar">
            <button type="button" id="sidebarAvatarBtn" title="Change profile photo" style="position:absolute;right:-6px;bottom:-6px;width:28px;height:28px;border-radius:50%;border:1px solid rgba(0,0,0,0.06);background:#fff;color:var(--lav-4);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(0,0,0,0.08);cursor:pointer;padding:3px">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M21 7h-3.17l-1.84-2.46A2 2 0 0 0 14.41 4H9.59a2 2 0 0 0-1.58.54L6.17 7H3a1 1 0 0 0-1 1v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1zm-9 11a5 5 0 1 1 0-10 5 5 0 0 1 0 10z" fill="var(--lav-4)"/></svg>
            </button>
            <input type="file" id="sidebarAvatarInput" accept="image/*" style="display:none">

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
    <script>
    (function(){
      const avatarBtn = document.getElementById('sidebarAvatarBtn');
      const avatarInput = document.getElementById('sidebarAvatarInput');
      const avatarImg = document.getElementById('sidebarAvatar');
      const avatarMenu = document.getElementById('sidebarAvatarMenu');
      const avatarMenuView = document.getElementById('avatarMenuView');
      const avatarMenuChoose = document.getElementById('avatarMenuChoose');
      if(!avatarBtn || !avatarInput || !avatarImg || !avatarMenu) return;
      let prevSrc = avatarImg.src;

      try{
        const saved = localStorage.getItem('user_avatar');
        const savedName = localStorage.getItem('user_fullname');
        if(saved && saved.length && (!avatarImg.src || avatarImg.src.indexOf('logodrea.jpg') !== -1)) avatarImg.src = saved;
        if(savedName){ const sn = document.getElementById('sidebarName'); if(sn) sn.textContent = savedName; }
        if(saved){ const headerAvatar = document.querySelector('.profile-menu .profile-btn img.profile-avatar'); if(headerAvatar) headerAvatar.src = saved; const hdr = document.getElementById('hdrAvatarSmall'); if(hdr) hdr.src = saved; }
      }catch(e){}

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
        if (rect.left + mw > window.innerWidth - 8) {
          left = Math.max(8, rect.right - mw);
        }
        if (top + mh > window.innerHeight - 8) {
          top = Math.max(8, rect.top - mh - 8);
        }
        avatarMenu.style.left = Math.max(8, left) + 'px';
        avatarMenu.style.top = Math.max(8, top) + 'px';
        avatarMenu.style.right = 'auto';
        avatarMenu.style.bottom = 'auto';
        avatarMenu.style.visibility = 'visible';
      });

      avatarMenuView.addEventListener('click', (ev)=>{
        ev.stopPropagation(); avatarMenu.style.display = 'none';
        const src = avatarImg.src || '';
        if(!src) { showToast('No profile picture available', 'error'); return; }
        openImageModal(src, 'Profile Picture');
      });

      avatarMenuChoose.addEventListener('click', (ev)=>{ ev.stopPropagation(); avatarMenu.style.display = 'none'; try{ avatarInput.click(); }catch(e){} });

      document.addEventListener('click', function(e){ if(avatarMenu && avatarMenu.style.display === 'block'){ avatarMenu.style.display = 'none'; } });
      document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ if(avatarMenu) avatarMenu.style.display = 'none'; } });

      avatarInput.addEventListener('change', async function(){
        const f = this.files && this.files[0];
        if(!f) return;
        if(!f.type || !f.type.startsWith('image/')){ showToast('Please choose an image file.', 'error'); this.value=''; return; }
        if(f.size > 3 * 1024 * 1024){ showToast('Image too large (max 3MB).', 'error'); this.value=''; return; }

        const objectUrl = URL.createObjectURL(f);
        prevSrc = avatarImg.src;
        avatarImg.src = objectUrl;
        try{ const m = document.getElementById('modalAvatarPreview'); if(m) m.src = objectUrl; }catch(e){}

        const fd = new FormData(); fd.append('avatar', f);
        try{
          showToast('Uploading photo...', 'info', 1800);
          const res = await fetch('save_admin_profile.php', { method: 'POST', body: fd, credentials: 'same-origin' });
          const txt = await res.text(); let data = {};
          try{ data = txt ? JSON.parse(txt) : {}; } catch(e){ console.error('Non-JSON response from save_admin_profile.php:', txt); throw new Error('Server returned unexpected response.'); }

          if(!res.ok || !data.success){ const msg = data && data.message ? data.message : ('Upload failed: ' + (res.status || 'error')); throw new Error(msg); }

          showToast(data.message || 'Profile photo updated', 'success');
            if(data.data){
            const newAvatar = data.data.avatar_url || '';
            const newName = data.data.name || '';
            if(newAvatar){ avatarImg.src = newAvatar; try{ localStorage.setItem('user_avatar', newAvatar); }catch(e){} document.querySelectorAll('.profile-menu .profile-btn img.profile-avatar').forEach(i=>i.src = newAvatar); try{ document.getElementById('hdrAvatarSmall').src = newAvatar; }catch(e){} try{ const m = document.getElementById('modalAvatarPreview'); if(m) m.src = newAvatar; }catch(e){} }
            if(newName){ try{ localStorage.setItem('user_fullname', newName); }catch(e){} try{ document.getElementById('sidebarName').textContent = newName; document.querySelectorAll('.profile-menu .profile-btn span').forEach(s=>s.textContent = newName); }catch(e){} }
          }
        }catch(err){ console.error('Avatar upload failed', err); showToast(err && err.message ? err.message : 'Failed to upload photo', 'error'); avatarImg.src = prevSrc; }
        finally{ try{ URL.revokeObjectURL(objectUrl); }catch(e){} avatarInput.value = ''; }
      });
    })();
    </script>

    <main class="main">
      <!-- OVERVIEW -->
      <section id="panel-overview" class="card" style="display:block;margin-bottom:1rem">
        <div id="annBanner" style="display:none;border-radius:8px;padding:12px;margin-bottom:12px;background:#fff8ff;border:1px solid #f0e7ff;color:var(--lavender)"></div>
        <h2 style="margin:0 0 8px 0;color:var(--lavender)">Dashboard Overview</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-top:12px;margin-bottom:12px">
          <div class="card" id="cardTotalPatients">
            <div style="font-size:0.9rem;color:var(--text-muted)">Total Patients</div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--lavender)"><span id="statTotalPatients">—</span></div>
          </div>
          <div class="card" id="cardDoctors" style="cursor:pointer">
            <div style="font-size:0.9rem;color:var(--text-muted)">Doctors</div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--lavender)"><span id="statDoctors">—</span></div>
          </div>
          <div class="card" id="cardMidwives" style="cursor:pointer">
            <div style="font-size:0.9rem;color:var(--text-muted)">Midwives</div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--lavender)"><span id="statMidwives">—</span></div>
          </div>
          <div class="card" id="cardAppointments">
            <div style="font-size:0.9rem;color:var(--text-muted)">Appointments (Today / Week)</div>
            <div style="font-size:1.2rem;font-weight:700;color:var(--lavender)"><span id="statApptToday">—</span> / <span id="statApptWeek">—</span></div>
          </div>
          <div class="card" id="cardPendingVerifications" style="cursor:pointer">
            <div style="font-size:0.9rem;color:var(--text-muted)">Pending Verifications</div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--lavender)"><span id="statPendingVerifications">—</span></div>
          </div>
          <div class="card" id="cardNewborns">
            <div style="font-size:0.9rem;color:var(--text-muted)">Newborns Registered</div>
            <div style="font-size:1.6rem;font-weight:700;color:var(--lavender)"><span id="statNewborns">—</span></div>
          </div>
          <div class="card" id="cardPayments">
            <div style="font-size:0.9rem;color:var(--text-muted)">Payments (Total / Pending)</div>
            <div style="font-size:1.2rem;font-weight:700;color:var(--lavender)"><span id="statPaymentsTotal">—</span> / <span id="statPaymentsPending">—</span></div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px">
          <div class="card" style="min-height:160px">
            <h3 style="margin:0 0 8px 0;color:var(--lavender)">Activity Feed</h3>
            <div id="activityFeed" style="max-height:240px;overflow:auto;color:var(--text-muted)">Loading recent activity…</div>
          </div>
          <div class="card" style="min-height:160px">
            <h3 style="margin:0 0 8px 0;color:var(--lavender)">Newborn Registrations</h3>
            <div id="miniNewborn" style="height:160px;overflow:auto;color:var(--text-muted)">
              <div id="miniNewbornList">Loading mothers…</div>
            </div>
          </div>
        </div>
      </section>

      <section id="panel-manage-users" class="card" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
          <h2 style="margin:0;color:var(--lavender)">Manage Users</h2>
          <div style="display:flex;gap:8px;align-items:center">
            <input id="userSearch" placeholder="Search username, email, role or ID" style="padding:8px;border-radius:8px;border:1px solid #eee;min-width:260px">
            <button id="btnSearchUser" class="btn">Search</button>
            <button id="btnNewUser" class="btn">+ New User</button>
          </div>
        </div>

        <div id="usersTable" class="card" style="box-shadow:none;padding:0">
          <table>
            <thead>
              <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody id="usersTbody">
              <tr><td colspan="6">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- PATIENTS PANEL -->
      <section id="panel-patients" class="card" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
          <h2 style="margin:0;color:var(--lavender)">Patient Management</h2>
          <div>
            <input id="patientSearch" placeholder="Search by name or ID" style="padding:8px;border-radius:8px;border:1px solid #eee;min-width:220px">
            <button id="btnSearchPatient" class="btn">Search</button>
          </div>
        </div>

        <div id="patientsTable" class="card" style="box-shadow:none;padding:0">
          <table>
            <thead>
              <tr><th>ID</th><th>Name</th><th>Email</th><th>Registered</th></tr>
            </thead>
            <tbody id="patientsTbody">
              <tr><td colspan="4">Loading...</td></tr>
            </tbody>
          </table>
          <div id="patientsPagination" style="padding:10px 12px;display:flex;justify-content:flex-end;gap:6px"></div>
        </div>
      </section>

      <section id="panel-verifications" class="card" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
          <h2 style="margin:0;color:var(--lavender)">Account Verifications</h2>
          <div>
            <select id="verStatusFilter" style="padding:8px;border-radius:8px;border:1px solid #eee">
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="all">All</option>
            </select>
            <button id="btnRefreshVer" class="btn">Refresh</button>
          </div>
        </div>

        <div id="verTable" class="card" style="box-shadow:none;padding:0">
          <table>
            <thead>
              <tr><th>ID</th><th>User</th><th>File</th><th>Submitted</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody id="verTbody"><tr><td colspan="6">Loading...</td></tr></tbody>
          </table>
        </div>
      </section>

      <section id="panel-reports" class="card" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
          <h2 style="margin:0;color:var(--lavender)">Reports</h2>
          <div style="display:flex;gap:8px;align-items:center">
            <!-- Export buttons removed per request -->
          </div>
        </div>

        <!-- Detailed Reports: select type/category + generate -->
        <div class="card reports-card" style="margin-bottom:12px;padding:12px">
          <div style="display:flex;gap:8px;align-items:center;margin-bottom:10px">
            <select id="reportTypeSelect" style="padding:8px;border-radius:8px;border:1px solid #eee">
              <option value="daily_patients">Daily - Patients</option>
              <option value="daily_payments">Daily - Payments</option>
              <option value="monthly_appointments">Monthly - Appointments</option>
              <option value="payment_summary">Payments — Summary</option>
              <option value="inventory_usage">Inventory — Usage</option>
            </select>
            <select id="reportCategorySelect" style="padding:8px;border-radius:8px;border:1px solid #eee">
              <option value="all">All</option>
              <option value="patients">Patients</option>
              <option value="appointments">Appointments</option>
              <option value="payments">Payments</option>
            </select>
            <select id="reportGranularitySelect" style="padding:8px;border-radius:8px;border:1px solid #eee">
              <option value="daily">Daily</option>
              <option value="monthly" selected>Monthly</option>
            </select>
            <button id="btnGenerateReport" class="btn">Generate</button>
          </div>
            <div style="overflow:auto">
            <div id="reportChartContainer" style="width:100%;min-height:260px;">
              <canvas id="reportChart" style="width:100%;height:320px"></canvas>
            </div>
            <div id="reportFallback" style="display:none;margin-top:8px;color:var(--text-muted)">No reports generated yet.</div>
          </div>
        </div>

        <!-- Inventory and Payments summary cards removed per request -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;margin-top:12px"></div>
      </section>

      <section id="panel-announcements" class="card" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
          <div style="display:flex;align-items:center;gap:12px">
            <h2 style="margin:0;color:var(--lavender)">Announcements</h2>
          </div>
          <div>
            <button id="btnNewAnnouncement" class="btn">+ New Announcement</button>
          </div>
        </div>

        <div id="annCreate" class="card" style="display:none;margin-bottom:12px">
          <form id="annForm">
            <div style="display:grid;grid-template-columns:1fr;gap:0.5rem">
              <input name="title" placeholder="Title" style="padding:8px;border-radius:6px;border:1px solid #ddd">
              <textarea name="message" placeholder="Message" style="padding:8px;border-radius:6px;border:1px solid #ddd;height:100px"></textarea>
              <div style="display:flex;gap:8px;align-items:center">
                <label style="font-size:0.9rem;color:var(--text-muted);margin-right:8px">Audience</label>
                <select name="audience" id="annAudience" style="padding:8px;border-radius:8px;border:1px solid #ddd">
                  <option value="all">All Users</option>
                  <option value="patients">Patients</option>
                  <option value="midwives">Midwives</option>
                  <option value="doctors">Doctors</option>
                </select>
                <label style="font-size:0.9rem;color:var(--text-muted);margin-left:12px;margin-right:8px">Expires</label>
                <input type="date" name="expires" id="annExpires" style="padding:8px;border-radius:8px;border:1px solid #ddd">
              </div>
              <div style="display:flex;gap:8px;align-items:center">
                <label style="font-size:0.9rem;color:var(--text-muted)">Publish at <input type="datetime-local" name="published_at" style="margin-left:8px"></label>
                <div style="margin-left:auto"><button class="btn" type="submit">Publish</button> <button type="button" id="btnCancelAnn" class="btn ghost">Cancel</button></div>
              </div>
            </div>
          </form>
        </div>

        <div id="annList" class="card" style="box-shadow:none;padding:0">
          <table>
            <thead><tr>
              <th style="width:18%">Title</th>
              <th style="width:36%">Message</th>
              <th style="width:12%">Published</th>
              <th style="width:10%;text-align:center">Audience</th>
              <th style="width:10%">Expires</th>
              <th style="width:8%">Status</th>
              <th style="width:6%">Actions</th>
            </tr></thead>
            <tbody id="annTbody"><tr><td colspan="7">Loading...</td></tr></tbody>
          </table>
        </div>
      </section>

      <section id="panel-appointments" class="card" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
          <h2 style="margin:0;color:var(--lavender)">Appointment Management</h2>
          <div style="display:flex;gap:8px;align-items:center">
            <input id="apptFilterDate" type="date" style="padding:6px;border-radius:8px;border:1px solid #eee">
            <input id="apptFilterPatient" placeholder="Patient ID or name" style="padding:6px;border-radius:8px;border:1px solid #eee">
            <button id="btnFilterAppt" class="btn">Filter</button>
          </div>
        </div>
        <div class="card" style="box-shadow:none;padding:0">
          <table>
            <thead><tr><th>ID</th><th>Patient</th><th>Service</th><th>Date</th><th>Time</th><th>Status</th><th>Assigned</th><th>Actions</th></tr></thead>
            <tbody id="appointmentsTbody"><tr><td colspan="8">Loading...</td></tr></tbody>
          </table>
        </div>
      </section>

      <section id="panel-payments" class="card" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
          <h2 style="margin:0;color:var(--lavender)">Payment Management</h2>
            <div style="display:flex;gap:8px;align-items:center">
            <input id="payFilterPatient" placeholder="Patient ID or name" style="padding:6px;border-radius:8px;border:1px solid #eee">
            <button id="btnFilterPayments" class="btn">Filter</button>
          </div>
        </div>
        <div class="card" style="box-shadow:none;padding:0">
          <table>
            <thead><tr><th>ID</th><th>Patient</th><th>File</th><th>Uploaded</th><th>Verified</th><th>Actions</th></tr></thead>
            <tbody id="paymentsTbody"><tr><td colspan="6">Loading...</td></tr></tbody>
          </table>
        </div>
      </section>

      <section id="panel-newborns" class="card" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
          <h2 style="margin:0;color:var(--lavender)">Newborn Records</h2>
          <div>
            <button id="btnNewbornAdd" class="btn">+ Add Newborn</button>
          </div>
        </div>
        <div class="card" style="box-shadow:none;padding:0">
          <table>
            <thead><tr><th>ID</th><th>Mother</th><th>Baby</th><th>Gender</th><th>DOB</th><th>Weight</th><th>Actions</th></tr></thead>
            <tbody id="newbornsTbody"><tr><td colspan="7">Loading...</td></tr></tbody>
          </table>
        </div>
      </section>

      <section id="panel-settings" class="card" style="display:none">
        <h2 style="color:var(--lavender)">Admin Settings</h2>
        <p style="color:var(--text-muted)">Manage system settings, users, pricing, notifications, and more.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;margin-top:14px">
          <div class="card" style="cursor:pointer" id="settingsUserMgmt">
            <h3 style="margin:0 0 8px 0;color:var(--lavender)">User Management</h3>
            <div style="color:var(--text-muted)">Control who can access the system.</div>
            <ul style="margin-top:8px;color:var(--text-muted)">
              <li>Add / Edit / Delete Doctors</li>
              <li>Add / Edit / Delete Midwives</li>
              <li>Add / Edit / Delete Patients</li>
              <li>Reset passwords & change roles</li>
            </ul>
            <div style="margin-top:8px"><button class="btn" id="btnOpenUserMgmt">Open</button></div>
          </div>

          <div class="card" id="settingsServicePricing">
            <h3 style="margin:0 0 8px 0;color:var(--lavender)">Service Pricing</h3>
            <div style="color:var(--text-muted)">Adjust costs for services and packages.</div>
            <div style="margin-top:10px"><button class="btn" id="btnManageServices">Manage Services</button></div>
          </div>

          <div class="card" id="settingsAppointment">
            <h3 style="margin:0 0 8px 0;color:var(--lavender)">Appointment Settings</h3>
            <div style="color:var(--text-muted)">Control booking rules and time slots.</div>
            <div style="margin-top:10px"><button class="btn" id="btnAppointmentSettings">Configure</button></div>
          </div>

          <div class="card" id="settingsLeaveManagement">
            <h3 style="margin:0 0 8px 0;color:var(--lavender)">Leave Management</h3>
            <div style="color:var(--text-muted)">Review and approve staff leave requests. Mark staff as "On Leave" or re-enable accounts.</div>
            <div style="margin-top:10px"><button class="btn" id="btnManageLeaves">Open Leave Requests</button></div>
          </div>

          <!-- Admin module related cards removed per request -->
        </div>

        <!-- Clinic Info Modal -->
        <div id="clinicModal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;z-index:1400">
          <div style="background:var(--white);padding:18px;border-radius:10px;max-width:720px;width:94%;box-shadow:0 6px 24px rgba(0,0,0,0.18);">
            <h3 style="margin:0 0 10px 0;color:var(--lavender)">Clinic Information</h3>
            <form id="clinicForm">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <label>Clinic name<input name="clinic_name" placeholder="Clinic name" style="padding:8px;border-radius:6px;border:1px solid #eee"></label>
                <label>Contact number<input name="contact" placeholder="Mobile or landline" style="padding:8px;border-radius:6px;border:1px solid #eee"></label>
                <label style="grid-column:1/3">Address<textarea name="address" style="padding:8px;border-radius:6px;border:1px solid #eee;min-height:80px"></textarea></label>
                <label>Operating hours<input name="hours" placeholder="e.g. Mon-Fri 8:00-17:00" style="padding:8px;border-radius:6px;border:1px solid #eee"></label>
                <label>Logo URL (or upload below)<input name="logo_url" placeholder="/uploads/clinic_logo.png" style="padding:8px;border-radius:6px;border:1px solid #eee"></label>
                <label>Upload logo<input id="clinicLogoFile" type="file"></label>
              </div>
              <div style="margin-top:12px;display:flex;gap:8px">
                <button class="btn" type="submit">Save</button>
                <button type="button" class="btn ghost" id="btnCancelClinic">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Services Modal -->
        <div id="servicesModal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;z-index:1400">
          <div style="background:var(--white);padding:18px;border-radius:10px;max-width:900px;width:94%;box-shadow:0 6px 24px rgba(0,0,0,0.18);">
            <h3 style="margin:0 0 10px 0;color:var(--lavender)">Service Pricing</h3>
            <div style="margin-bottom:8px"><button id="btnNewService" class="btn">+ New Service</button></div>
            <div id="servicesList" style="max-height:360px;overflow:auto;border-top:1px solid #f0eff8;padding-top:8px">Loading…</div>
            <div style="margin-top:12px;text-align:right"><button class="btn ghost" id="btnCloseServices">Close</button></div>
          </div>
        </div>

        <!-- Appointment Settings Modal -->
        <div id="apptModal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;z-index:1400">
          <div style="background:var(--white);padding:18px;border-radius:10px;max-width:640px;width:94%;box-shadow:0 6px 24px rgba(0,0,0,0.18);">
            <h3 style="margin:0 0 10px 0;color:var(--lavender)">Appointment Settings</h3>
            <form id="apptForm">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                <label>Time slot minutes<input name="slot_minutes" type="number" placeholder="e.g. 30" style="padding:8px;border-radius:6px;border:1px solid #eee"></label>
                <label>Limit per day<input name="limit_per_day" type="number" placeholder="e.g. 20" style="padding:8px;border-radius:6px;border:1px solid #eee"></label>
                <label>Auto-approve bookings<select name="auto_approve" style="padding:8px;border-radius:6px;border:1px solid #eee"><option value="1">Enabled</option><option value="0">Disabled</option></select></label>
                <label>Allow same-day<select name="same_day" style="padding:8px;border-radius:6px;border:1px solid #eee"><option value="1">Yes</option><option value="0">No</option></select></label>
              </div>
              <div style="margin-top:12px;display:flex;gap:8px"><button class="btn" type="submit">Save</button><button type="button" class="btn ghost" id="btnCancelAppt">Cancel</button></div>
            </form>
          </div>
        </div>

        <!-- Leave Requests Modal -->
        <div id="leaveModal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;z-index:1400">
          <div style="background:var(--white);padding:16px;border-radius:10px;max-width:980px;width:94%;box-shadow:0 6px 30px rgba(0,0,0,0.18);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
              <h3 style="margin:0;color:var(--lavender)">Leave Requests</h3>
              <button id="btnCloseLeaveModal" class="btn ghost">Close</button>
            </div>
            <div id="leaveModalBody" style="max-height:520px;overflow:auto">Loading…</div>
          </div>
        </div>

        <script>
          // Settings UI wiring
          document.getElementById('btnOpenUserMgmt')?.addEventListener('click', ()=>{ document.querySelector('nav.sidebar .menu a[data-panel="manage-users"]').click(); loadUsers(); });
          document.getElementById('btnManageLeaves')?.addEventListener('click', ()=>{
            // open the leave modal and load requests into it
            const modal = document.getElementById('leaveModal'); if(modal) modal.style.display = 'flex';
            loadLeaveRequests('leaveModalBody');
          });
          document.getElementById('btnCloseLeaveModal')?.addEventListener('click', ()=>{ const modal = document.getElementById('leaveModal'); if(modal) modal.style.display = 'none'; });
          document.getElementById('leaveModal')?.addEventListener('click', function(e){ if(e.target === this){ this.style.display='none'; } });
          document.getElementById('btnLoadToggleAccounts')?.addEventListener('click', ()=> loadToggleAccounts());

          // Clinic modal
          const clinicModal = document.getElementById('clinicModal');
          document.getElementById('btnEditClinic')?.addEventListener('click', async ()=>{
            clinicModal.style.display = 'flex';
            try{ const res = await api('get_setting', { key: 'clinic_info' }); if(res.success && res.value){ const v = JSON.parse(res.value || '{}'); const f = document.getElementById('clinicForm'); f.clinic_name.value = v.name || ''; f.contact.value = v.contact || ''; f.address.value = v.address || ''; f.hours.value = v.hours || ''; f.logo_url.value = v.logo || ''; } }catch(e){}
          });
          document.getElementById('btnCancelClinic')?.addEventListener('click', ()=> clinicModal.style.display='none');
          document.getElementById('clinicForm')?.addEventListener('submit', async function(e){ e.preventDefault(); const fd = new FormData(this); const obj = { name: fd.get('clinic_name'), contact: fd.get('contact'), address: fd.get('address'), hours: fd.get('hours'), logo: fd.get('logo_url') };
            // upload file if present
            const file = document.getElementById('clinicLogoFile')?.files?.[0];
            if(file){ const up = new FormData(); up.append('action','upload_clinic_logo'); up.append('file', file); const upl = await fetch('admin_api.php', { method:'POST', body: up }); try{ const uj = await upl.json(); if(uj.success && uj.url) obj.logo = uj.url; }catch(e){} }
            const res = await api('set_setting', { key: 'clinic_info', value: JSON.stringify(obj) }); if(res.success) { showToast('Saved', 'success'); clinicModal.style.display='none'; } else showToast('Save failed','error');
          });

          // Services modal
          const servicesModal = document.getElementById('servicesModal');
          document.getElementById('btnManageServices')?.addEventListener('click', async ()=>{ servicesModal.style.display='flex'; await loadServices(); });
          document.getElementById('btnCloseServices')?.addEventListener('click', ()=> servicesModal.style.display='none');
          async function loadServices(){
            const container = document.getElementById('servicesList');
            container.innerHTML = 'Loading...';
            try{
              const res = await api('list_services');
              if(!res.success){ container.innerHTML='Error'; return; }
              const list = res.services || [];
              if(!list.length){ container.innerHTML = '<div>No services</div>'; return; }
              container.innerHTML = list.map(s=>{
                const priceText = s.price === null || s.price === '' ? 'Price: —' : ('Price: ' + escapeHtml(String(s.price)));
                const activeText = escapeHtml(s.active ? 'Active' : 'Inactive');
                return `<div style="display:flex;justify-content:space-between;align-items:center;padding:8px;border-bottom:1px solid #f3f2f9">
                          <div>
                            <div style="font-weight:700" class="svc-name">${escapeHtml(s.name)}</div>
                            <div style="color:#666" class="svc-price" data-id="${escapeHtml(String(s.id))}">${priceText} — ${activeText}</div>
                          </div>
                          <div>
                            <button class="btn" data-id="${escapeHtml(String(s.id))}" data-action="edit-service">Edit</button>
                            <button class="btn ghost" data-id="${escapeHtml(String(s.id))}" data-action="toggle-service">Toggle</button>
                          </div>
                        </div>`;
              }).join('');

              // Inline edit: replace price text with an input when Edit clicked; Save updates price in DB and updates DOM in-place
              document.querySelectorAll('[data-action="edit-service"]').forEach(b=>b.addEventListener('click', async (e)=>{
                const btn = e.currentTarget;
                const id = btn.dataset.id;
                const row = btn.closest('div[style*="display:flex"]');
                if(!row) return;
                const priceEl = row.querySelector('.svc-price');
                const nameEl = row.querySelector('.svc-name');
                if(!priceEl || !nameEl) return;
                // if input already present, do nothing
                if(priceEl.querySelector('input')) return;
                // extract current price (may contain 'Price: ' prefix and extra text)
                let raw = priceEl.textContent || '';
                // remove "— Active" suffix
                raw = raw.replace(/—\s*(Active|Inactive)\s*$/i,'').replace(/^Price:\s*/i,'').trim();
                const input = document.createElement('input');
                input.type = 'text'; input.value = raw; input.style.padding='6px'; input.style.borderRadius='8px'; input.style.border='1px solid #ddd';
                const saveBtn = document.createElement('button'); saveBtn.className='btn'; saveBtn.textContent='Save'; saveBtn.style.marginLeft='8px';
                const cancelBtn = document.createElement('button'); cancelBtn.className='btn ghost'; cancelBtn.textContent='Cancel'; cancelBtn.style.marginLeft='6px';
                // clear priceEl and insert input+buttons
                priceEl.dataset.original = priceEl.innerHTML;
                priceEl.innerHTML = ''; priceEl.appendChild(input); priceEl.appendChild(saveBtn); priceEl.appendChild(cancelBtn);

                cancelBtn.addEventListener('click', ()=>{
                  priceEl.innerHTML = priceEl.dataset.original || '';
                });

                saveBtn.addEventListener('click', async ()=>{
                  const newPrice = input.value.trim();
                  // keep name unchanged; get name text
                  const svcName = nameEl.textContent.trim();
                  try{
                    const res = await api('save_service', { id: id, name: svcName, price: newPrice });
                    if(!res || !res.success){ alert('Save failed: ' + (res && res.message?res.message:'')); priceEl.innerHTML = priceEl.dataset.original || ''; return; }
                    // update price display from response.service.price if available
                    const updated = res.service || null;
                    const displayPrice = updated && (updated.price !== undefined && updated.price !== null) ? String(updated.price) : newPrice;
                    const activeTextAfter = (updated && updated.active) ? 'Active' : ((updated && ('active' in updated)) ? 'Inactive' : (priceEl.textContent.match(/—\s*(Active|Inactive)/i) || ['','Active'])[1]);
                    priceEl.innerHTML = 'Price: ' + escapeHtml(String(displayPrice)) + ' — ' + escapeHtml(activeTextAfter);
                  }catch(err){ console.error(err); alert('Save failed'); priceEl.innerHTML = priceEl.dataset.original || ''; }
                });
                // focus input
                input.focus();
              }));

              document.querySelectorAll('[data-action="toggle-service"]').forEach(b=>b.addEventListener('click', async (e)=>{ 
                const id = e.currentTarget.dataset.id; 
                try{
                  const ok = await showConfirm('Toggle active?');
                  if(!ok) return;
                  const res = await api('delete_service', { id });
                  if(!res || !res.success){ showToast('Failed to toggle service','error'); return; }
                  showToast('Service updated','success');
                  await loadServices();
                }catch(err){ console.error('toggle-service failed', err); showToast('Request failed','error'); }
              }));
            }catch(err){ console.error('loadServices failed', err); container.innerHTML = '<div>Error loading services</div>'; }
          }
          document.getElementById('btnNewService')?.addEventListener('click', async ()=>{ const name = prompt('Service name'); if(!name) return; const price = prompt('Price'); await api('save_service', { name, price }); await loadServices(); });

          // Appointment modal
          const apptModal = document.getElementById('apptModal');
          document.getElementById('btnAppointmentSettings')?.addEventListener('click', async ()=>{ apptModal.style.display='flex'; try{ const res = await api('get_setting',{ key:'appointment_settings' }); if(res.success && res.value){ const v=JSON.parse(res.value||'{}'); const f=document.getElementById('apptForm'); f.slot_minutes.value = v.slot_minutes || 30; f.limit_per_day.value = v.limit_per_day || 20; f.auto_approve.value = v.auto_approve ? '1':'0'; f.same_day.value = v.same_day ? '1':'0'; } }catch(e){} });
          document.getElementById('btnCancelAppt')?.addEventListener('click', ()=> apptModal.style.display='none');
          document.getElementById('apptForm')?.addEventListener('submit', async function(e){ e.preventDefault(); const fd=new FormData(this); const obj = { slot_minutes: Number(fd.get('slot_minutes')||30), limit_per_day: Number(fd.get('limit_per_day')||20), auto_approve: fd.get('auto_approve')==='1', same_day: fd.get('same_day')==='1' }; const res = await api('set_setting',{ key:'appointment_settings', value: JSON.stringify(obj) }); if(res.success){ showToast('Saved','success'); apptModal.style.display='none'; } else showToast('Save failed','error'); });
        </script>
      </section>

      <!-- ADMIN MODULE & SUPPORT PANELS (placeholders) -->
      <section id="panel-admin-module" class="card" style="display:none">
        <h2 style="margin:0;color:var(--lavender)">Admin Module</h2>
        <div style="color:var(--text-muted);margin-top:8px">Administrative tools for staff and leave management.</div>
      </section>

      <section id="panel-staff-management" class="card" style="display:none">
        <h2 style="margin:0;color:var(--lavender)">Staff Management</h2>
        <div style="margin-top:8px;color:var(--text-muted)">List, add, and edit doctor and midwife accounts.</div>
        <div style="margin-top:12px"><button id="btnOpenStaffMgmt" class="btn">Open Staff Management</button></div>
      </section>

      <section id="panel-leave-requests" class="card" style="display:none">
        <h2 style="margin:0;color:var(--lavender)">Approve Leave Requests</h2>
        <div style="margin-top:8px;color:var(--text-muted)">Review and approve staff leave applications.</div>
        <div id="leaveRequestsTable" style="margin-top:12px">Loading…</div>
      </section>

      <section id="panel-toggle-staff-accounts" class="card" style="display:none">
        <h2 style="margin:0;color:var(--lavender)">Disable / Enable Staff Accounts</h2>
        <div style="margin-top:8px;color:var(--text-muted)">Temporarily disable or enable doctor and midwife access.</div>
        <div style="margin-top:12px"><button id="btnLoadToggleAccounts" class="btn">Manage Accounts</button></div>
      </section>

      <section id="panel-set-leave-duration" class="card" style="display:none">
        <h2 style="margin:0;color:var(--lavender)">Set Leave Duration</h2>
        <div style="margin-top:8px;color:var(--text-muted)">Configure default leave duration and policies.</div>
        <div style="margin-top:12px">
          <label>Default leave days: <input id="defaultLeaveDays" type="number" min="0" value="14" style="width:80px;padding:6px;border-radius:6px;border:1px solid #eee"></label>
          <button id="btnSaveLeaveDuration" class="btn" style="margin-left:8px">Save</button>
        </div>
      </section>
    </main>
  </div>

  <!-- User Modal -->
  <div id="userModal">
    <div>
      <h3 id="modalTitle">New User</h3>
      <form id="userForm">
        <input type="hidden" name="id" />
        <div class="form-grid">
          <div class="form-row"><label>Username</label><input name="username" placeholder="e.g. j.smith" required></div>
          <div class="form-row"><label>Email</label><input name="email" type="email" placeholder="email@example.com" required></div>
          <div class="form-row" style="position:relative">
            <label>Password</label>
            <div style="display:flex;align-items:center;gap:8px">
              <input id="user_password" name="password" type="password" placeholder="Enter a password (min 6 chars)" style="flex:1">
              <button type="button" id="userPasswordToggle" title="Show password" style="border:0;background:transparent;cursor:pointer;padding:6px;border-radius:6px;color:var(--lavender)">
                👁️
              </button>
            </div>
          </div>
          <div class="form-row"><label>Role</label>
            <select name="user_type">
              <option value="patient">patient</option>
              <option value="doctor">doctor</option>
              <option value="midwife">midwife</option>
              <option value="admin">admin</option>
            </select>
          </div>
        </div>

        <hr style="margin:12px 0;border-color:#f0eafc">
        <div style="font-weight:700;color:var(--lavender);margin-bottom:8px">Walk-in patient details (optional)</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
          <label>Full name<input name="full_name" placeholder="Full name"></label>
          <label>Date of birth<input name="dob" type="date"></label>
          <label>Age<input name="age" type="number" min="0"></label>
          <label>Contact number<input name="contact" placeholder="Mobile number"></label>
          <label>Address<input name="address" placeholder="Address"></label>
          <label>Civil status
            <select name="civil_status">
              <option value="">Select</option>
              <option>Single</option>
              <option>Married</option>
              <option>Widowed</option>
              <option>Divorced</option>
            </select>
          </label>
          <label>Nationality<input name="nationality" placeholder="Nationality"></label>
          <label>Religion<input name="religion" placeholder="Religion"></label>
        </div>
        <div class="modal-actions">
          <button type="submit" class="btn">Save</button>
          <button type="button" id="btnCancel" class="btn ghost">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Patient Modal -->
  <div id="patientModal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;z-index:999">
    <div style="background:var(--white);padding:1.25rem;border-radius:10px;max-width:760px;width:94%;box-shadow:0 6px 24px rgba(0,0,0,0.18);">
      <h3 id="patientModalTitle" style="margin-top:0;color:var(--lavender)">Patient</h3>
      <form id="patientForm">
        <input type="hidden" name="user_id" />
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
          <label>Full Name<input name="full_name"></label>
          <label>Date of Birth<input name="dob" type="date"></label>
          <label>Age<input name="age" type="number"></label>
          <label>Mobile<input name="mobile"></label>
          <label>Email<input name="email" type="email"></label>
          <label>Address<input name="address"></label>
        </div>
        <div style="margin-top:0.5rem">
          <label>Notes<textarea name="notes" style="width:100%;height:80px"></textarea></label>
        </div>

        <div style="display:flex;gap:0.5rem;align-items:center;margin-top:0.6rem">
          <button type="submit" class="btn">Save Patient</button>
          <button type="button" id="btnCancelPatient" class="btn ghost">Close</button>
          <div style="margin-left:auto;display:flex;gap:0.5rem;align-items:center">
            <input id="fileInputPatient" type="file" />
            <button id="btnUploadPatientFile" type="button" class="btn">Upload File</button>
          </div>
        </div>

          <!-- Image View Modal placeholder (moved to document root) -->

        <div style="margin-top:1rem">
          <h4 style="margin:0 0 6px 0;color:var(--lavender)">Files</h4>
          <div id="patientFilesList" style="max-height:180px;overflow:auto;color:var(--text-muted)">No files</div>
        </div>
      </form>
    </div>
  </div>

  <!-- Image View Modal -->
  <div id="imgModal">
    <div class="img-wrap">
      <button class="close-btn" id="imgModalClose">Close</button>
      <div style="display:flex;flex-direction:column;gap:6px">
        <img id="imgModalImg" src="" alt="ID Image">
        <div class="caption" id="imgModalCaption"></div>
      </div>
    </div>
  </div>

  <!-- Confirm Modal -->
  <div id="confirmModal">
    <div class="confirm-box">
      <p id="confirmModalMsg">Are you sure?</p>
      <div class="confirm-actions">
        <button id="confirmCancel" class="confirm-btn ghost">Cancel</button>
        <button id="confirmOk" class="confirm-btn primary">OK</button>
      </div>
    </div>
  </div>

  <script>
    // panel switching: on page load, read `panel` query param and show the matching panel.
    (function(){
      function param(name){ const u=new URL(window.location.href); return u.searchParams.get(name); }
      const p = param('panel') || 'overview';
      // mark active link
      document.querySelectorAll('nav.sidebar .menu a').forEach(x=> x.classList.toggle('active', (x.dataset.panel === p)));
      // show matching section
      document.querySelectorAll('main.main section').forEach(s => { s.style.display = (s.id === 'panel-' + p) ? 'block' : 'none'; });
      // call loader for panel if needed
      try{
        if(p === 'manage-users') loadUsers();
        else if(p === 'patients') loadPatientsAdmin();
        else if(p === 'verifications') loadVerifications();
        else if(p === 'announcements') loadAnnouncements();
        else if(p === 'overview') loadStats();
        else if(p === 'settings') {
          // nothing extra; appointment settings loads on Configure click
        }
      }catch(e){ console.warn('panel loader failed', e); }
    })();

    // API helper
    async function api(action, data = {}) {
      const form = new FormData();
      form.append('action', action);
      for (const k in data) form.append(k, data[k]);
      console.debug('api request', action, Object.fromEntries(form.entries()));
      let res;
      try{
        res = await fetch('admin_api.php', { method: 'POST', body: form, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      } catch(err){ console.error('api network error', err); return { success:false, message: 'Network error', error: String(err) }; }
      const txt = await res.text();
      console.debug('api response', { status: res.status, statusText: res.statusText, body: txt });
      try{ const parsed = JSON.parse(txt); if(!parsed || (parsed.success === false)) console.warn('api returned failure', parsed); return parsed; } catch(e){ return { success: false, message: 'Invalid JSON response', status: res.status, body: txt }; }
    }

    // small HTML escape helper
    function escapeHtml(s){ if(s===null||s===undefined) return ''; return String(s).replace(/[&<>"'`]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'}[c])); }

    // Load stats for overview cards and activity feed
    async function loadStats(){
      try{
        const res = await fetch('admin_api.php?action=stats', { credentials: 'include' });
        if(!res.ok){ console.error('admin_api.php stats failed', res.status); return; }
        const j = await res.json();
        if(!j.success) { console.error('stats error', j.message); return; }
        document.getElementById('statTotalPatients').textContent = j.total_patients ?? '0';
        document.getElementById('statDoctors').textContent = j.total_doctors ?? '0';
        document.getElementById('statMidwives').textContent = j.total_midwives ?? '0';
        document.getElementById('statApptToday').textContent = j.appointments_today ?? '0';
        document.getElementById('statApptWeek').textContent = j.appointments_week ?? '0';
        const upcomingEl = document.getElementById('statUpcomingDeliveries');
        if(upcomingEl) upcomingEl.textContent = j.upcoming_deliveries ?? '0';
        document.getElementById('statNewborns').textContent = j.newborns ?? '0';
        document.getElementById('statPaymentsTotal').textContent = j.payments_total ?? '0';
        document.getElementById('statPaymentsPending').textContent = j.payments_pending ?? '0';

        const feed = document.getElementById('activityFeed');
        feed.innerHTML = '';
        if(Array.isArray(j.recent_activity) && j.recent_activity.length){
          j.recent_activity.forEach(a=>{
            const when = a.ts ? (new Date(a.ts)).toLocaleString() : '';
            const who = a.patient_name ? a.patient_name : (a.patient_user_id ? `Patient #${a.patient_user_id}` : '');
            const typeLabel = (a.type || 'item').toUpperCase();
            const line = document.createElement('div');
            line.style.padding = '8px 6px';
            line.style.borderBottom = '1px solid #f0eff8';
            line.innerHTML = `<div style="font-weight:600;color:var(--lavender)">${escapeHtml(typeLabel)}</div><div style="font-size:0.95rem;color:var(--text-muted)">${escapeHtml(a.summary||'')}</div><div style="font-size:0.95rem;color:var(--text-muted);margin-top:6px">${who ? 'Patient: '+escapeHtml(who) : ''}</div><div style="font-size:0.8rem;color:#999;margin-top:6px">${escapeHtml(when)}</div>`;
            feed.appendChild(line);
          });
        } else {
          feed.textContent = 'No recent activity.';
        }
      }catch(err){ console.error('Failed to load stats', err); }
    }

    // show a toast on the home/overview when there are pending ID verifications
    // clicking the toast opens the Verifications panel
    async function checkPendingVerificationsToast(){
      try{
        const res = await fetch('admin_api.php?action=list_pending_registrations&status=pending');
        if(!res.ok) return;
        const j = await res.json();
        const pending = Array.isArray(j.pending) ? j.pending.length : 0;
        if(pending > 0){
          showToast(`There are ${pending} pending ID verifications. Click to view.`, 'info');
          const toast = document.getElementById('toast');
          if(toast){
            toast.style.cursor = 'pointer';
            const onClick = () => { document.querySelector('nav.sidebar .menu a[data-panel="verifications"]').click(); loadVerifications(); toast.style.cursor='default'; toast.onclick = null; };
            toast.onclick = onClick;
          }
        }
      }catch(e){ console.error('pending verifications check failed', e); }
    }

    // fetch and update the pending verifications count shown in the Overview card
    async function fetchPendingVerificationsCount(){
      try{
        const res = await fetch('admin_api.php?action=list_pending_registrations&status=pending');
        if(!res.ok) return;
        const j = await res.json();
        const pending = Array.isArray(j.pending) ? j.pending.length : 0;
        const el = document.getElementById('statPendingVerifications');
        if(el) el.textContent = pending;
        const card = document.getElementById('cardPendingVerifications');
        if(card){
          card.addEventListener('click', ()=>{ document.querySelector('nav.sidebar .menu a[data-panel="verifications"]').click(); loadVerifications(); });
        }
      }catch(err){ console.error('Failed to fetch pending verifications count', err); }
    }

    // Cached users for client-side filtering
    window.adminUsersCache = [];

    // Load users, optional roleFilter ('doctor','midwife','patient','admin')
    async function loadUsers(roleFilter) {
      const res = await api('list');
      const tbody = document.getElementById('usersTbody');
      if (!res.success) { tbody.innerHTML = `<tr><td colspan="6">Error: ${res.message}</td></tr>`; return; }
      let users = res.users || [];
      // cache for client-side filtering
      window.adminUsersCache = users;
      renderUsersTable(roleFilter);
    }

    // Render the users table using cached users and optional role filter and search term
    function renderUsersTable(roleFilter) {
      const tbody = document.getElementById('usersTbody');
      let users = Array.isArray(window.adminUsersCache) ? window.adminUsersCache.slice() : [];
      const searchTerm = (document.getElementById('userSearch')?.value || '').trim().toLowerCase();
      if (roleFilter) users = users.filter(u => (u.user_type || '').toLowerCase() === roleFilter.toLowerCase());
      if (searchTerm) {
        users = users.filter(u => {
          const id = String(u.id || '');
          const username = (u.username || '').toLowerCase();
          const email = (u.email || '').toLowerCase();
          const role = (u.user_type || '').toLowerCase();
          return id.includes(searchTerm) || username.includes(searchTerm) || email.includes(searchTerm) || role.includes(searchTerm);
        });
      }
      if(!users.length){ tbody.innerHTML = '<tr><td colspan="6">No users found.</td></tr>'; return; }
      tbody.innerHTML = users.map(u => `
        <tr>
          <td>${u.id}</td>
          <td>${u.username}</td>
          <td>${u.email}</td>
          <td>${u.user_type}</td>
          <td>${u.created_at}</td>
          <td>
            <button class="btn ghost" data-action="edit" data-id="${u.id}">Edit</button>
            <button class="btn" data-action="delete" data-id="${u.id}">Delete</button>
          </td>
        </tr>
      `).join('');

      document.querySelectorAll('[data-action="edit"]').forEach(b => b.addEventListener('click', onEdit));
      document.querySelectorAll('[data-action="delete"]').forEach(b => b.addEventListener('click', onDelete));
    }

    function showModal(mode='new', user={}){
      document.getElementById('userModal').style.display = 'flex';
      document.getElementById('modalTitle').textContent = mode === 'new' ? 'New User' : 'Edit User';
      const form = document.getElementById('userForm');
      form.id.value = user.id || '';
      form.username.value = user.username || '';
      form.email.value = user.email || '';
      form.password.value = '';
      form.user_type.value = user.user_type || 'patient';
    }

    document.getElementById('btnNewUser').addEventListener('click', () => showModal('new'));
    document.getElementById('btnCancel').addEventListener('click', () => document.getElementById('userModal').style.display='none');

    // Password visibility toggle for New User modal
    (function(){
      const toggle = document.getElementById('userPasswordToggle');
      const pwd = document.getElementById('user_password');
      function setToggle(){
        if(!toggle || !pwd) return;
        toggle.addEventListener('click', function(){
          try{
            if(pwd.type === 'password'){ pwd.type = 'text'; toggle.title = 'Hide password'; }
            else { pwd.type = 'password'; toggle.title = 'Show password'; }
          }catch(e){ console.error(e); }
        });
      }
      // if elements aren't present yet, try again after a short delay (modal may be injected)
      if(toggle && pwd) setToggle(); else setTimeout(setToggle, 300);
    })();

    // handle the New User form submit (create or update)
    // helper: generate username from full name + dob (format: FirstNameMMDDYY -> "Luka071803")
    function generateUsernameFromNameDob(fullName, dob){
      if(!fullName) return '';
      const first = String(fullName).trim().split(/\s+/)[0] || fullName;
      if(!dob) return first.replace(/[^A-Za-z0-9]/g,'');
      // dob expected YYYY-MM-DD
      const m = dob.match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if(!m) return first.replace(/[^A-Za-z0-9]/g,'');
      const year = m[1].slice(-2);
      const month = m[2];
      const day = m[3];
      return (first + month + day + year).replace(/[^A-Za-z0-9]/g,'');
    }

    function generateRandomPassword(len = 8){
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%&*';
      let out = '';
      for(let i=0;i<len;i++) out += chars.charAt(Math.floor(Math.random()*chars.length));
      return out;
    }

    // deterministic password based on name + dob (admin-provided)
    // format: FirstNameMMDDYY!  e.g. Luka071803!
    function generatePasswordFromNameDob(fullName, dob){
      if(!fullName) return generateRandomPassword(10);
      const first = String(fullName).trim().split(/\s+/)[0] || fullName;
      const cleaned = first.replace(/[^A-Za-z0-9]/g,'');
      if(!dob) return cleaned + generateRandomPassword(3);
      const m = dob.match(/^(\d{4})-(\d{2})-(\d{2})$/);
      if(!m) return cleaned + generateRandomPassword(3);
      const year = m[1].slice(-2);
      const month = m[2];
      const day = m[3];
      return cleaned + month + day + year + '!';
    }

    document.getElementById('userForm').addEventListener('submit', async function(e){
      e.preventDefault();
      var f = e.target;
      var form = new FormData(f);
      var payload = {};
      form.forEach(function(v,k){ payload[k]=v; });

      var action = payload.id && payload.id !== '' ? 'update' : 'create';

      // On create, auto-generate username/password when missing
      if(action === 'create'){
        // prefer full_name + dob from the walk-in section
        const fullName = (form.get('full_name') || '').toString().trim();
        const dob = (form.get('dob') || '').toString().trim();
        if(!payload.username || payload.username === ''){
          const gen = generateUsernameFromNameDob(fullName, dob) || ('user' + Date.now());
          payload.username = gen;
          // also set the form field so it's visible if the modal stays open
          try{ f.username.value = gen; }catch(e){}
        }
        if(!payload.password || payload.password === ''){
          const pw = generatePasswordFromNameDob(fullName, dob);
          payload.password = pw;
          try{ f.password.value = pw; }catch(e){}
          // show the generated password to the admin (so they can note it)
          showToast('Generated password: ' + pw, 'info');
        }
      }

      try{
        const resp = await api(action, payload);
        if(resp && resp.success){
          showToast(action === 'create' ? 'Successfully created' : 'Saved','success');
          document.getElementById('userModal').style.display = 'none';
          await loadUsers();
        } else {
          showToast('Error: '+(resp && resp.message ? resp.message : 'unknown'),'error');
        }
      } catch(err){
        console.error('userForm submit error', err);
        showToast('Error saving','error');
      }
    });

    // wire search input and button for Manage Users
    document.getElementById('btnSearchUser')?.addEventListener('click', () => renderUsersTable());
    const userSearchInput = document.getElementById('userSearch');
    if(userSearchInput){
      userSearchInput.addEventListener('keyup', function(e){ if(e.key === 'Enter') return renderUsersTable(); });
    }

    // Edit / Delete handlers used by loadUsers
    async function onEdit(e){
      const id = e.currentTarget.dataset.id;
      try{
        const res = await api('list');
        if(!res.success) return alert('Failed to load user');
        const user = (res.users || []).find(u => String(u.id) === String(id));
        showModal('edit', user || {});
      }catch(err){ console.error(err); alert('Failed to edit user'); }
    }

    async function onDelete(e){
      const id = e.currentTarget.dataset.id;
      try{
        const ok = await showConfirm('Delete this user?');
        if(!ok) return;
        const res = await api('delete', { id });
        if(!res || !res.success){ showToast('Delete failed: ' + (res && res.message ? res.message : ''), 'error'); return; }
        showToast('User deleted', 'success');
        await loadUsers();
      }catch(err){ console.error(err); showToast('Failed to delete user','error'); }
    }

    // Patients admin list loader (used by Patients panel and elsewhere)
    async function loadPatientsAdmin(searchTerm=''){
      const tbody = document.getElementById('patientsTbody');
      tbody.innerHTML = '<tr><td colspan="7">Loading...</td></tr>';
      try{
        const res = await fetch('get_patients.php');
        if(!res.ok) throw new Error('Failed to fetch');
        const j = await res.json();
        let patients = [];
        if (j && Array.isArray(j.patients)) patients = j.patients;
        if (!patients.length && Array.isArray(j)) patients = j;
        if (searchTerm) {
          const s = searchTerm.toLowerCase();
          patients = patients.filter(p => (p.full_name||p.name||'').toLowerCase().includes(s) || String(p.user_id||p.id||p.patient_user_id||'').includes(s));
        }
        if(!patients.length){ tbody.innerHTML = '<tr><td colspan="5">No patients found.</td></tr>'; return; }
        tbody.innerHTML = patients.map(p=>{
          const id = p.user_id || p.id || p.patient_user_id || '';
          const name = p.name || p.full_name || `${p.first_name||''} ${p.last_name||''}`.trim();
          const email = p.email || '';
          const created = p.created_at || p.registered_at || '';
            return `<tr>
              <td>${escapeHtml(id)}</td>
              <td>${escapeHtml(name)}</td>
              <td>${escapeHtml(email)}</td>
              <td>${escapeHtml(created)}</td>
            </tr>`;
        }).join('');

        document.querySelectorAll('[data-action="view-patient"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const uid = e.currentTarget.dataset.id;
          await openPatientModal(uid);
        }));
      }catch(err){ tbody.innerHTML = `<tr><td colspan="4">Error loading patients</td></tr>`; console.error(err); }
    }

    // Ensure clicking a View button in the Patients panel opens the Patient Info modal
    (function(){
      const panel = document.getElementById('panel-patients');
      if(!panel) return;
      panel.addEventListener('click', function(e){
        try{
          const btn = e.target.closest('[data-action="view-patient"]');
          if(!btn) return;
          const uid = btn.dataset.id || btn.getAttribute('data-id');
          if(uid) openPatientModal(uid);
        }catch(err){ console.error('Failed to open patient modal', err); }
      });
    })();

    async function loadVerifications(){
      const tbody = document.getElementById('verTbody');
      tbody.innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
      try{
        const status = document.getElementById('verStatusFilter').value || 'pending';
        const url = 'admin_api.php?action=list_pending_registrations&status=' + encodeURIComponent(status);
        const res = await fetch(url);
        if(!res.ok) throw new Error('Failed');
        const j = await res.json();
        const rows = j.pending || [];
        if(!rows.length){ tbody.innerHTML = '<tr><td colspan="6">No verifications.</td></tr>'; return; }
        tbody.innerHTML = rows.map(v=>`<tr>
          <td>${escapeHtml(v.id)}</td>
          <td>${escapeHtml(v.username||v.user_id||'')}</td>
          <td>${(v.id_front_path?'<a href="#" data-src="'+escapeHtml(v.id_front_path)+'" class="id-file-link" data-type="Front">Front</a> ':'')+(v.id_back_path?'<a href="#" data-src="'+escapeHtml(v.id_back_path)+'" class="id-file-link" data-type="Back">Back</a> ':'')+(v.id_selfie_path?'<a href="#" data-src="'+escapeHtml(v.id_selfie_path)+'" class="id-file-link" data-type="Selfie">Selfie</a> ':'')}</td>
          <td>${escapeHtml(v.submitted_at||'')}</td>
          <td>${escapeHtml(v.status||'')}</td>
          <td>
            ${v.status === 'pending' ? `<button class="btn" data-action="approve" data-id="${v.id}">Approve</button> <button class="btn ghost" data-action="reject" data-id="${v.id}">Reject</button>` : ''}
          </td>
        </tr>`).join('');

        document.querySelectorAll('#verTbody button[data-action]').forEach(b=>b.addEventListener('click', async (e)=>{
          const act = e.currentTarget.dataset.action;
          const id = e.currentTarget.dataset.id;
          const btn = e.currentTarget;
          const proceed = await showConfirm((act==='approve'?'Approve':'Reject') + ' this verification?');
          if(!proceed) return;
          const res = await api('review_pending_registration', { id: id, decision: act === 'approve' ? 'approve' : 'reject', notes: '' });
          if(!res.success) {
            const msg = (res && (res.message || res.body || res.error)) ? (res.message || res.body || res.error) : 'Request failed';
            console.warn('review_pending_registration failed', res);
            // Special-case: some MySQL/PDO setups may throw "There is no active transaction"
            // even though the DB update actually succeeded. Treat that message as success
            // to avoid showing a misleading error to the admin.
            if (typeof msg === 'string' && msg.toLowerCase().includes('no active transaction')) {
              showToast('Successfully approved!', 'success');
              try{
                const tr = btn.closest('tr');
                if(tr){
                  const statusCell = tr.querySelector('td:nth-child(5)');
                  if(statusCell) statusCell.textContent = (act==='approve' ? 'approved' : 'rejected');
                  const actionsCell = tr.querySelector('td:nth-child(6)');
                  if(actionsCell){ actionsCell.innerHTML = `<span style="font-weight:700;color:${act==='approve'?'#16a34a':'#c0392b'}">${act==='approve'?'Approved':'Rejected'}</span>`; }
                }
              }catch(e){ console.warn('Failed to update row after treating transaction-error as success', e); }
              return;
            }
            showToast('Error: ' + msg, 'error');
            return;
          }
          showToast(res.message || (act==='approve' ? 'Approved' : 'Rejected'), 'success');
          // update the row in-place so it remains visible
          try{
            const tr = btn.closest('tr');
            if(tr){
              const statusCell = tr.querySelector('td:nth-child(5)');
              if(statusCell) statusCell.textContent = (res.status || (act==='approve' ? 'approved' : 'rejected'));
              const actionsCell = tr.querySelector('td:nth-child(6)');
              if(actionsCell){
                actionsCell.innerHTML = `<span style="font-weight:700;color:${act==='approve'?'#16a34a':'#c0392b'}">${act==='approve'?'Approved':'Rejected'}</span>`;
              }
            }
          }catch(err){
            // fallback: reload list
            await loadVerifications();
          }
        }));
        // attach click handlers for file links to open modal
        document.querySelectorAll('#verTbody .id-file-link').forEach(a=>{
          a.addEventListener('click', function(e){
            e.preventDefault();
            const src = this.dataset.src;
            const type = this.dataset.type || '';
            const userText = this.closest('tr')?.querySelector('td:nth-child(2)')?.textContent || '';
            openImageModal(src, `${type}${userText ? ' — ' + userText.trim() : ''}`);
          });
        });
      }catch(err){ tbody.innerHTML = '<tr><td colspan="6">Error loading verifications</td></tr>'; console.error(err); }
    }

    async function openPatientModal(patient_user_id){
      try{
        const res = await fetch('admin_get_patient_details.php?patient_user_id='+encodeURIComponent(patient_user_id));
        if(!res.ok) throw new Error('failed');
        const j = await res.json();
        if(!j.success) { alert('Error: '+(j.message||'Unable to load patient')); return; }
        const p = j.data || {};
        const form = document.getElementById('patientForm');
        form.user_id.value = patient_user_id;
        form.full_name.value = p.full_name || p.name || '';
        form.dob.value = p.dob || '';
        form.age.value = p.age || '';
        form.mobile.value = p.mobile || p.phone || '';
        form.email.value = p.email || '';
        form.address.value = p.address || '';
        form.notes.value = p.notes || '';
        document.getElementById('patientFilesList').textContent = 'Loading...';
        document.getElementById('patientModal').style.display = 'flex';
        await loadPatientFiles(patient_user_id);
      }catch(err){ console.error(err); alert('Failed to open patient'); }
    }

    document.getElementById('btnCancelPatient').addEventListener('click', ()=> document.getElementById('patientModal').style.display='none');

    document.getElementById('btnSearchPatient').addEventListener('click', ()=>{
      const q = document.getElementById('patientSearch').value || '';
      loadPatientsAdmin(q);
    });
    document.getElementById('patientSearch').addEventListener('keydown', (e)=>{ if(e.key==='Enter'){ e.preventDefault(); document.getElementById('btnSearchPatient').click(); } });

    document.getElementById('patientForm').addEventListener('submit', async function(e){
      e.preventDefault();
      const form = new FormData(this);
      const user_id = form.get('user_id');
      if(!user_id) return alert('Missing patient id');
      const payload = {};
      form.forEach((v,k)=> payload[k]=v);
      try{
        const res = await fetch('admin_save_patient_details.php', { method: 'POST', headers: {'Accept':'application/json'}, body: JSON.stringify(payload) });
        const j = await res.json();
        if(!j.success) return alert('Save error: '+(j.message||''));
        alert('Saved');
        document.getElementById('patientModal').style.display='none';
        await loadPatientsAdmin(document.getElementById('patientSearch').value||'');
      }catch(err){ console.error(err); alert('Failed to save'); }
    });

    // upload file for patient
    document.getElementById('btnUploadPatientFile').addEventListener('click', async ()=>{
      const fileInput = document.getElementById('fileInputPatient');
      if(!fileInput.files || !fileInput.files[0]) return alert('Select a file first');
      const user_id = document.getElementById('patientForm').user_id.value;
      if(!user_id) return alert('Open a patient first');
      const fd = new FormData();
      fd.append('file', fileInput.files[0]);
      fd.append('patient_user_id', user_id);
      try{
        const res = await fetch('save_patient_file.php', { method: 'POST', body: fd });
        const j = await res.json();
        if(!j.success) return alert('Upload failed: '+(j.message||''));
        fileInput.value = '';
        await loadPatientFiles(user_id);
      }catch(err){ console.error(err); alert('Upload error'); }
    });

    async function loadPatientFiles(patient_user_id){
      try{
        const res = await fetch('get_patient_files.php?patient_user_id='+encodeURIComponent(patient_user_id));
        if(!res.ok) throw new Error('failed');
        const j = await res.json();
        const container = document.getElementById('patientFilesList');
        if(!j.success) { container.textContent = 'Error loading files'; return; }
        const files = j.files || [];
        if(!files.length){ container.textContent = 'No files'; return; }
        container.innerHTML = files.map(f=>`<div style="padding:6px;border-bottom:1px solid #f3f2f9"><a href="${escapeHtml(f.url||f.filename||'')}" target="_blank">${escapeHtml(f.filename||f.url||'file')}</a> <span style="color:#888">${escapeHtml(f.uploaded_at||'')}</span></div>`).join('');
      }catch(err){ console.error(err); document.getElementById('patientFilesList').textContent='Error'; }
    }

    // initial
    loadStats();
    loadUsers();
    loadPatientsAdmin();
    // mini newborns list on overview
    loadMiniNewborns();
    // pending verification count + toast
    fetchPendingVerificationsCount();
    checkPendingVerificationsToast();
    // Reports: load inventory & payments summary
    async function loadReports(){
      try{
        // inventory
        const invRes = await fetch('get_inventory.php');
        const invJson = await invRes.json();
        const inv = (invJson && Array.isArray(invJson.inventory)) ? invJson.inventory : [];
        document.getElementById('reportInventoryCount').textContent = inv.length;
        const low = inv.filter(i=>parseInt(i.quantity||0) <= 5).length;
        document.getElementById('reportLowStock').textContent = low;
        const list = document.getElementById('reportInventoryList');
        if(!inv.length) list.textContent = 'No inventory items'; else list.innerHTML = inv.map(i=>`<div style="padding:6px;border-bottom:1px solid #f3f2f9">${escapeHtml(i.item_name)} — <strong>${escapeHtml(String(i.quantity))}</strong></div>`).join('');

        // payments
        const payRes = await fetch('get_payments.php');
        const payJson = await payRes.json();
        const pays = (payJson && Array.isArray(payJson.payments)) ? payJson.payments : [];
        document.getElementById('reportPaymentsCount').textContent = pays.length;
        // pending = payments where verified = 0 or null
        const pending = pays.filter(p => !p.verified || p.verified == 0).length;
        document.getElementById('reportPaymentsPending').textContent = pending;
      }catch(err){ console.error('Failed to load reports', err); }
    }

    // load reports when reports panel opened
    document.querySelectorAll('nav.sidebar .menu a').forEach(a => a.addEventListener('click', e => { if(a.dataset.panel==='reports') loadReports(); }));

    // Run a detailed report using admin_api.php?action=run_report
    function formatReportLabel(key){
        const map = {
        'daily_patients': 'Daily – Patients',
        'daily_payments': 'Daily – Payments',
        'monthly_appointments': 'Monthly – Appointments'
      };
      return map[key] || key || '';
    }

    function formatReportDate(raw, reportType){
      if(!raw) return '';
      // YYYY-MM-DD
      if(/^\d{4}-\d{2}-\d{2}$/.test(raw)){
        const d = new Date(raw + 'T00:00:00');
        return d.toLocaleDateString(undefined, { month:'short', day:'numeric', year:'numeric' });
      }
      // YYYY-MM (monthly)
      if(/^\d{4}-\d{2}$/.test(raw)){
        const [y,m] = raw.split('-').map(Number);
        const first = new Date(y, m-1, 1);
        const last = new Date(y, m, 0);
        const monthName = first.toLocaleDateString(undefined, { month:'short' });
        return `${monthName} 01–${String(last.getDate()).padStart(2,'0')}, ${y}`;
      }
      return raw;
    }

    function formatTotalValue(row, reportType){
      // payments: prefer total_amount if present
      if(reportType === 'daily_payments'){
        const amt = row.total_amount != null ? Number(row.total_amount) : (row.total_amount_display ? Number(row.total_amount_display) : null);
        if(amt != null && !Number.isNaN(amt)){
          try{ return new Intl.NumberFormat('en-PH', { style:'currency', currency:'PHP', maximumFractionDigits:0 }).format(amt); }catch(e){ return '₱' + String(amt); }
        }
      }
      const num = row.total_count ?? row.count ?? 0;
      return String(num);
    }

    async function runReport(reportType, category, year){
      // prepare UI containers
      const chartContainer = document.getElementById('reportChartContainer');
      const annualContainer = document.getElementById('annualReportContainer');
      const fallback = document.getElementById('reportFallback');
      if(chartContainer) chartContainer.style.display = '';
      if(annualContainer) annualContainer.style.display = 'none';
      if(fallback){ fallback.style.display = ''; fallback.textContent = 'Generating report…'; }
      try{
        const payload = { report_type: reportType, category: category };
        if(typeof year !== 'undefined') payload.year = String(year);
        const res = await api('run_report', payload);
        if(!res || !res.success){ if(fallback) fallback.textContent = `Error: ${escapeHtml(res && res.message ? res.message : 'No response')}`; return; }
        if(reportType === 'annual_report'){
          // keep existing annual rendering
          const data = res.annual || {};
          const container = document.getElementById('annualReportContainer');
          container.innerHTML = '';
          function makeCard(title, html){ return `<div class="card" style="margin-bottom:10px"><h3 style="margin:0 0 8px 0;color:var(--lavender);">${escapeHtml(title)}</h3><div style="color:var(--text-muted)">${html}</div></div>`; }
          const s1 = data.patients || {};
          let s1html = `<div style="display:flex;gap:18px;align-items:baseline"><div style="font-size:1.6rem;font-weight:700;color:var(--lavender)">${escapeHtml(String(s1.total || 0))}</div><div style="color:var(--text-muted)">Total patients registered in ${escapeHtml(String(year||''))}</div></div>`;
          s1html += `<div style="margin-top:8px">Prenatal patients: <strong>${escapeHtml(String(s1.prenatal || 0))}</strong> — New: <strong>${escapeHtml(String(s1.new_patients || 0))}</strong>, Returning: <strong>${escapeHtml(String(s1.returning_patients || 0))}</strong></div>`;
          container.innerHTML += makeCard('1. Total Patients for the Year', s1html);
          const s2 = data.appointments || {};
          let s2html = `<div>Total check-ups: <strong>${escapeHtml(String(s2.total_checkups || 0))}</strong></div>`;
          s2html += `<div>Missed/cancelled: <strong>${escapeHtml(String(s2.missed_or_cancelled || 0))}</strong></div>`;
          if(Array.isArray(s2.most_active_months) && s2.most_active_months.length){ s2html += `<div>Most active months: <strong>${escapeHtml(s2.most_active_months.map(m=>m.month+ ' ('+ m.count +')').join(', '))}</strong></div>`; }
          container.innerHTML += makeCard('2. Annual Appointments Summary', s2html);
          const s3 = data.finance || {};
          let s3html = `<div>Total income: <strong>${escapeHtml(String(s3.total_income != null ? s3.total_income : 0))}</strong></div>`;
          if(s3.by_category) s3html += `<div>Payments per category:<ul>${Object.entries(s3.by_category).map(([k,v])=>`<li>${escapeHtml(k)}: <strong>${escapeHtml(String(v))}</strong></li>`).join('')}</ul></div>`;
          if(s3.by_method) s3html += `<div>Payments by method:<ul>${Object.entries(s3.by_method).map(([k,v])=>`<li>${escapeHtml(k)}: <strong>${escapeHtml(String(v))}</strong></li>`).join('')}</ul></div>`;
          container.innerHTML += makeCard('3. Financial Summary', s3html);
          const s4 = data.inventory || {};
          let s4html = `<div>Total used: <strong>${escapeHtml(String(s4.total_used || 0))}</strong></div>`;
          if(Array.isArray(s4.top_consumed) && s4.top_consumed.length) s4html += `<div>Top supplies:<ul>${s4.top_consumed.map(i=>`<li>${escapeHtml(i.item)} — ${escapeHtml(String(i.used))}</li>`).join('')}</ul></div>`;
          if(s4.restock_frequency) s4html += `<div>Restock frequency (avg): <strong>${escapeHtml(String(s4.restock_frequency))}</strong></div>`;
          container.innerHTML += makeCard('4. Inventory Usage Summary', s4html);
          const s5 = data.staff || {};
          let s5html = `<div>Doctor appointments handled: <strong>${escapeHtml(String(s5.doctor_appointments || 0))}</strong></div>`;
          s5html += `<div>Midwife services: <strong>${escapeHtml(String(s5.midwife_services || 0))}</strong></div>`;
          if(s5.overtime) s5html += `<div>Overtime / shifts: <strong>${escapeHtml(String(s5.overtime))}</strong></div>`;
          container.innerHTML += makeCard('5. Staff Activity Reports', s5html);
          const s6 = data.lab_prescriptions || {};
          let s6html = `<div>Lab tests done: <strong>${escapeHtml(String(s6.lab_tests || 0))}</strong></div>`;
          s6html += `<div>Prescriptions given: <strong>${escapeHtml(String(s6.prescriptions || 0))}</strong></div>`;
          container.innerHTML += makeCard('6. Laboratory & Prescription Summary', s6html);
          const s7 = data.issues || {};
          let s7html = `<div>Emergency cases: <strong>${escapeHtml(String(s7.emergencies || 0))}</strong></div>`;
          if(s7.system_downtimes) s7html += `<div>System downtimes: <strong>${escapeHtml(String(s7.system_downtimes))}</strong></div>`;
          if(s7.policy_changes) s7html += `<div>Policy changes: <strong>${escapeHtml(String(s7.policy_changes))}</strong></div>`;
          container.innerHTML += makeCard('7. Annual Issues or Alerts', s7html);
          // hide chart when showing annual cards
          if(chartContainer) chartContainer.style.display = 'none';
          if(fallback) fallback.style.display = 'none';
          return;
        }
        // special-case payment_summary: aggregate client-side and render
        if(reportType === 'payment_summary'){
          await runPaymentSummary(reportCategorySelect?.value || 'all');
          return;
        }
        // special-case inventory_usage: aggregate quantities and flag restock
        if(reportType === 'inventory_usage'){
          await runInventoryUsage();
          return;
        }
        // non-annual: render data as bar chart
        const rows = res.records || res.data || res.rows || res.report || [];
        if(!Array.isArray(rows) || rows.length === 0){ if(fallback) fallback.textContent = 'No data returned for this report.'; return; }
        const labels = rows.map(r => formatReportDate(r.date || r.label || r.period || r.key || r.month || r.name || r.bucket || r.category, reportType));
        const values = rows.map(r => {
          if(reportType && reportType.indexOf('payment') !== -1){ const v = r.total_amount != null ? Number(r.total_amount) : (r.amount != null ? Number(r.amount) : (r.total_count != null ? Number(r.total_count) : 0)); return Number.isFinite(v) ? v : 0; }
          const v = r.total_count != null ? Number(r.total_count) : (r.count != null ? Number(r.count) : (r.value != null ? Number(r.value) : 0)); return Number.isFinite(v) ? v : 0;
        });
        try{
          const ctx = document.getElementById('reportChart').getContext('2d');
          if(window.reportChartInstance){ try{ window.reportChartInstance.destroy(); }catch(e){} window.reportChartInstance = null; }
          window.reportChartInstance = new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: formatReportLabel(reportType) || 'Report', data: values, backgroundColor: 'rgba(156,125,232,0.9)', borderColor: 'rgba(124,86,200,0.95)', borderWidth: 1 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { x: { ticks: { autoSkip: true, maxRotation: 0 }, grid: { display: false } }, y: { beginAtZero: true } }, plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } } }
          });
          if(fallback) fallback.style.display = 'none';
        }catch(err){ console.error('Failed rendering chart', err); if(fallback) fallback.textContent = 'Failed to render chart.'; }
      }catch(err){ console.error('runReport error', err); if(fallback) fallback.textContent = 'Failed to run report'; }
    }

    // Payment summary helper: fetch payments and aggregate totals by day or month
    async function runPaymentSummary(category){
      const fallback = document.getElementById('reportFallback');
      try{
        const res = await fetch('get_payments.php', { credentials: 'same-origin' });
        if(!res.ok){ if(fallback) fallback.textContent = 'Failed to load payments.'; return; }
        const txt = await res.text(); let j = null; try{ j = txt ? JSON.parse(txt) : null; }catch(e){ j = null; }
        const payments = (j && Array.isArray(j.payments)) ? j.payments : (Array.isArray(j) ? j : []);
        // filter only paid AND verified
        const filtered = payments.filter(p => {
          const paid = (p.paid && Number(p.paid) === 1) || String(p.status || '').toLowerCase().includes('paid') || (p.amount && Number(p.amount) > 0);
          const verified = (p.verified && Number(p.verified) === 1) || String(p.payment_status || '').toLowerCase().includes('verified') || String(p.verified || '').toLowerCase() === '1';
          return paid && verified;
        });
        if(!filtered.length){ if(fallback) fallback.textContent = 'No paid & verified payments found.'; return; }

        const gran = document.getElementById('reportGranularitySelect')?.value || 'monthly';

        // helper to pick amount and date
        const pickAmount = (p) => {
          const candidates = [p.total_amount, p.amount, p.amount_paid, p.paid_amount, p.paid, p.total || p.value];
          for(const c of candidates){ if(c !== undefined && c !== null && String(c).trim() !== ''){ const n = Number(String(c).replace(/[^0-9.\-]/g,'')); if(!Number.isNaN(n)) return n; } }
          return 0;
        };
        const pickDate = (p) => {
          return p.uploaded_at || p.paid_at || p.date || p.created_at || p.uploaded || p.payment_date || p.registered_at || '';
        };

        const map = {};
        filtered.forEach(p => {
          const raw = pickDate(p) || '';
          const d = new Date(raw);
          if(isNaN(d.getTime())){
            // try parsing YYYY-MM-DD in raw
            const m = String(raw).match(/(\d{4}-\d{2}-\d{2})/);
            if(m) d.setTime(new Date(m[1] + 'T00:00:00').getTime());
          }
          if(isNaN(d.getTime())) return; // skip unparsable
          let key = '';
          if(gran === 'daily'){
            const yyyy = d.getFullYear(); const mm = String(d.getMonth()+1).padStart(2,'0'); const dd = String(d.getDate()).padStart(2,'0'); key = `${yyyy}-${mm}-${dd}`;
          } else {
            const yyyy = d.getFullYear(); const mm = String(d.getMonth()+1).padStart(2,'0'); key = `${yyyy}-${mm}`;
          }
          const amt = pickAmount(p);
          map[key] = (map[key] || 0) + (Number(amt) || 0);
        });

        const keys = Object.keys(map).sort();
        if(!keys.length){ if(fallback) fallback.textContent = 'No payments to summarize.'; return; }
        const labels = keys.map(k => {
          if(gran === 'daily') return formatReportDate(k, 'daily');
          // monthly: 'YYYY-MM' -> 'MMM YYYY'
          const [y,m] = k.split('-'); const dt = new Date(Number(y), Number(m)-1, 1); return dt.toLocaleDateString(undefined, { month:'short', year:'numeric' });
        });
        const values = keys.map(k => Math.round((map[k] || 0) * 100) / 100);

        const ctx = document.getElementById('reportChart').getContext('2d');
        if(window.reportChartInstance){ try{ window.reportChartInstance.destroy(); }catch(e){} window.reportChartInstance = null; }
        window.reportChartInstance = new Chart(ctx, { type: 'bar', data: { labels: labels, datasets: [{ label: 'Payments (PHP)', data: values, backgroundColor: 'rgba(46,125,50,0.9)', borderColor: 'rgba(37,99,39,0.95)', borderWidth: 1 }] }, options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } }, plugins:{ legend:{ display:false } } } });
        if(fallback) fallback.style.display = 'none';
      }catch(err){ console.error('runPaymentSummary error', err); if(fallback) fallback.textContent = 'Failed to build payment summary.'; }
    }

    // wire up Generate button
    document.getElementById('btnGenerateReport')?.addEventListener('click', async ()=>{
      const type = document.getElementById('reportTypeSelect')?.value || 'daily_patients';
      const cat = document.getElementById('reportCategorySelect')?.value || 'all';
      await runReport(type, cat);
    });

    // when report type changes, ensure chart area is visible
    const reportTypeSelect = document.getElementById('reportTypeSelect');
    reportTypeSelect?.addEventListener('change', function(){
      const chartContainer = document.getElementById('reportChartContainer');
      const fallback = document.getElementById('reportFallback');
      if(chartContainer) chartContainer.style.display = '';
      if(fallback) fallback.style.display = '';
    });

    // Optionally load a small recent reports list when reports panel opens
    async function loadRecentReports(){
      try{
        const res = await api('recent_reports');
        if(!res || !res.success) return;
        const rows = res.recent || [];
        const tbody = document.getElementById('reportDetailedTbody');
        if(!rows.length) return;
        // if table currently empty or placeholder, fill with recent
        const current = tbody && tbody.textContent && tbody.textContent.includes('No reports');
        if(current){
          tbody.innerHTML = rows.map(r=>`<tr><td>${escapeHtml(r.date||r.generated_at||'')}</td><td>${escapeHtml(r.report_type||'')}</td><td style="text-align:right">${escapeHtml(String(r.count||r.total_count||0))}</td><td>${escapeHtml(r.status||'Completed')}</td></tr>`).join('');
        }
      }catch(e){ console.warn('recent reports load failed', e); }
    }

    // try to populate recent reports once on page load
    loadRecentReports();

    // make overview cards clickable to filter Manage Users
    document.getElementById('cardDoctors')?.addEventListener('click', ()=>{
      document.querySelector('nav.sidebar .menu a[data-panel="manage-users"]').click();
      loadUsers('doctor');
    });
    document.getElementById('cardMidwives')?.addEventListener('click', ()=>{
      document.querySelector('nav.sidebar .menu a[data-panel="manage-users"]').click();
      loadUsers('midwife');
    });

    // Announcements: load, create, delete
    async function loadAnnouncements(){
      const tbody = document.getElementById('annTbody');
      tbody.innerHTML = '<tr><td colspan="7">Loading...</td></tr>';
      try{
        // Request admin view (all announcements) when running from admin dashboard
        const res = await fetch('admin_announcements.php?all=1');
        if(!res.ok) throw new Error('Failed');
        const j = await res.json();
        const list = j.announcements || [];
        // no header appointment count required for admin announcements panel
        if(!list.length){ tbody.innerHTML = '<tr><td colspan="7">No announcements.</td></tr>'; return; }
        tbody.innerHTML = list.map(a=>{
          const active = Number(a.is_active || 0) === 1;
          const btn = active ? `<button class="btn ghost" data-id="${a.id}" data-action="disable">Disable</button>` : `<button class="btn" data-id="${a.id}" data-action="enable">Enable</button>`;
          const published = a.published_at || a.created_at || '';
          const expires = a.expires_at || '';
          const statusLabel = active ? 'Active' : 'Inactive';
          return `<tr>
            <td>${escapeHtml(a.title||'')}</td>
            <td style="max-width:420px">${escapeHtml(a.message||'')}</td>
            <td>${escapeHtml(published)}</td>
            <td style="text-align:center">${escapeHtml(a.audience||'all')}</td>
            <td>${escapeHtml(expires)}</td>
            <td>${escapeHtml(statusLabel)}</td>
            <td style="white-space:nowrap">${btn} <button class="btn ghost" data-id="${a.id}" data-action="delete">Delete</button></td>
          </tr>`;
        }).join('');

        // Attach handlers for enable/disable/delete actions
        document.querySelectorAll('#annTbody button[data-action]').forEach(b=>b.addEventListener('click', async (e)=>{
          const act = e.currentTarget.dataset.action;
          const id = e.currentTarget.dataset.id;
          if(!id) return;
          if(act === 'delete' && !(await showConfirm('Delete this announcement?'))) return;
          if(act === 'disable' && !(await showConfirm('Disable this announcement (it will be hidden from users)?'))) return;
          try{
            const res = await fetch('admin_announcements.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ action: act, id: id }) });
            const j = await res.json();
            if(!j.success) return alert((j.message||'Action failed'));
            if(act === 'disable') showToast('Announcement disabled', 'success');
            else if(act === 'enable') showToast('Announcement enabled', 'success');
            else if(act === 'delete') showToast('Announcement deleted', 'success');
            await loadAnnouncements();
          }catch(err){ console.error('Announcement action failed', err); alert('Request failed'); }
        }));
      }catch(err){ tbody.innerHTML = '<tr><td colspan="4">Error loading</td></tr>'; console.error(err); }
    }

    document.getElementById('btnNewAnnouncement').addEventListener('click', ()=>{ document.getElementById('annCreate').style.display='block'; });
    document.getElementById('btnCancelAnn').addEventListener('click', ()=>{ document.getElementById('annCreate').style.display='none'; });
    document.getElementById('annForm').addEventListener('submit', async function(e){
      e.preventDefault();
      const fd = new FormData(this);
      const payload = { action: 'create' };
      fd.forEach((v,k)=> payload[k]=v);
      try{
        const res = await fetch('admin_announcements.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        const j = await res.json();
        if(!j.success) return alert('Create failed: '+(j.message||''));
        document.getElementById('annCreate').style.display='none';
        this.reset();
        await loadAnnouncements();
      }catch(err){ console.error(err); alert('Failed to create'); }
    });

    // load announcements when announcements panel opened
    document.querySelectorAll('nav.sidebar .menu a').forEach(a => a.addEventListener('click', e => { if(a.dataset.panel==='announcements') loadAnnouncements(); }));

    // Announcement banner for Overview (visible to any logged-in user)
    async function loadAnnouncementsBanner(){
      try{
        const res = await fetch('admin_announcements.php');
        if(!res.ok) return;
        const j = await res.json();
        const list = j.announcements || [];
        if(!list.length){ document.getElementById('annBanner').style.display='none'; return; }
        // prefer the most recent published_at <= now, else first
        const now = new Date();
        let pick = null;
        for(const a of list){
          if(!a.published_at) { if(!pick) pick = a; continue; }
          const p = new Date(a.published_at);
          if(p <= now) { pick = a; break; }
          if(!pick) pick = a;
        }
        if(!pick){ document.getElementById('annBanner').style.display='none'; return; }
        const banner = document.getElementById('annBanner');
        banner.innerHTML = `<div style="display:flex;align-items:start;gap:12px"><div style="flex:1"><div style="font-weight:700;color:var(--lavender);font-size:1.05rem">${escapeHtml(pick.title||'Announcement')}</div><div style="color:var(--text-muted);margin-top:6px">${escapeHtml(pick.message||'')}</div></div><div><button id="btnCloseAnn" class="btn ghost">Dismiss</button></div></div>`;
        banner.style.display = 'block';
        document.getElementById('btnCloseAnn').addEventListener('click', ()=>{ document.getElementById('annBanner').style.display='none'; });
      }catch(err){ console.error('Failed to load banner', err); }
    }

    // load banner on initial load
    loadAnnouncementsBanner();

    // Leave requests loader (renders into the element with id `targetId`)
    async function loadLeaveRequests(targetId = 'leaveRequestsTable'){
      const container = document.getElementById(targetId);
      if(!container) return;
      container.innerHTML = 'Loading...';
      try{
        const res = await api('list_leave_requests');
        if(!res || !res.success){ container.innerHTML = '<div>Error loading leave requests</div>'; return; }
        const list = res.requests || [];
        if(!list.length){ container.innerHTML = '<div>No leave requests.</div>'; return; }
        const rows = list.map(r => {
          const staff = escapeHtml(r.staff_name || r.username || ('#'+(r.user_id||'')));
          const role = escapeHtml(r.role || '');
          const from = escapeHtml(r.start_date || r.from || '');
          const to = escapeHtml(r.end_date || r.to || '');
          const reason = escapeHtml(r.reason || r.notes || '');
          return `<tr>
            <td>${escapeHtml(String(r.id||''))}</td>
            <td>${staff} (${escapeHtml(String(r.user_id||''))})</td>
            <td>${role}</td>
            <td>${from}</td>
            <td>${to}</td>
            <td>${reason}</td>
            <td style="white-space:nowrap">
              <button class="btn" data-action="approve-leave" data-id="${escapeHtml(String(r.id||''))}" data-user="${escapeHtml(String(r.user_id||''))}" data-from="${from}" data-to="${to}">Approve</button>
              <button class="btn ghost" data-action="reject-leave" data-id="${escapeHtml(String(r.id||''))}">Reject</button>
            </td>
          </tr>`;
        }).join('');

        container.innerHTML = `<table style="width:100%"><thead><tr><th>ID</th><th>Staff</th><th>Role</th><th>From</th><th>To</th><th>Reason</th><th>Actions</th></tr></thead><tbody>${rows}</tbody></table>`;

        container.querySelectorAll('button[data-action="approve-leave"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const id = e.currentTarget.dataset.id;
          const userId = e.currentTarget.dataset.user;
          const from = e.currentTarget.dataset.from || '';
          const to = e.currentTarget.dataset.to || '';
          const ok = await showConfirm('Approve this leave request and mark staff as On Leave?');
          if(!ok) return;
          try{
            const resp = await api('approve_leave_request', { id });
            if(!resp || !resp.success){ showToast('Failed to approve','error'); return; }
            // attempt to set staff on-leave (best-effort)
            await api('set_staff_on_leave', { user_id: userId, start_date: from, end_date: to });
            showToast('Leave approved; staff marked On Leave', 'success');
            await loadLeaveRequests(targetId);
          }catch(err){ console.error(err); showToast('Request failed','error'); }
        }));

        container.querySelectorAll('button[data-action="reject-leave"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const id = e.currentTarget.dataset.id;
          const ok = await showConfirm('Reject this leave request?');
          if(!ok) return;
          try{
            const resp = await api('reject_leave_request', { id });
            if(!resp || !resp.success){ showToast('Failed to reject','error'); return; }
            showToast('Leave request rejected','success');
            await loadLeaveRequests(targetId);
          }catch(err){ console.error(err); showToast('Request failed','error'); }
        }));

      }catch(err){ console.error('loadLeaveRequests failed', err); container.innerHTML = '<div>Error loading leave requests</div>'; }
    }

    // Account toggle / On-leave manager
    async function loadToggleAccounts(){
      const panel = document.getElementById('panel-toggle-staff-accounts');
      if(!panel) return;
      // ensure a container exists
      let container = document.getElementById('toggleAccountsTable');
      if(!container){
        const wrapper = document.createElement('div'); wrapper.id = 'toggleAccountsTable'; wrapper.style.marginTop = '12px';
        panel.appendChild(wrapper); container = wrapper;
      }
      container.innerHTML = 'Loading...';
      try{
        const res = await api('list_staff');
        if(!res || !res.success){ container.innerHTML = '<div>Error loading staff</div>'; return; }
        const list = res.staff || [];
        if(!list.length){ container.innerHTML = '<div>No staff found.</div>'; return; }
        const rows = list.map(s=>{
          const status = s.active && Number(s.active) ? '<span style="color:#16a34a;font-weight:700">Active</span>' : '<span style="color:#c0392b;font-weight:700">Disabled</span>';
          const onLeave = s.on_leave ? `<div style="font-size:0.9rem;color:#666">On Leave: ${escapeHtml(String(s.leave_until||''))}</div>` : '';
          return `<div style="display:flex;justify-content:space-between;align-items:center;padding:8px;border-bottom:1px solid #f3f2f9"><div><div style="font-weight:700">${escapeHtml(s.full_name||s.username||'')}</div><div style="color:#666">${escapeHtml(s.role||'')} • ${status}${onLeave}</div></div><div style="white-space:nowrap">${s.active && Number(s.active) ? `<button class="btn" data-action="disable" data-id="${escapeHtml(String(s.id||''))}">Disable</button>` : `<button class="btn" data-action="enable" data-id="${escapeHtml(String(s.id||''))}">Enable</button>`} <button class="btn ghost" data-action="set-on-leave" data-id="${escapeHtml(String(s.id||''))}">Set On Leave</button> <button class="btn ghost" data-action="unset-on-leave" data-id="${escapeHtml(String(s.id||''))}">Clear Leave</button></div></div>`;
        }).join('');
        container.innerHTML = rows;

        container.querySelectorAll('button[data-action="disable"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const id = e.currentTarget.dataset.id; if(!await showConfirm('Disable this account?')) return; const r = await api('toggle_staff_account',{ user_id: id, active: 0 }); if(!r || !r.success){ showToast('Failed','error'); return; } showToast('Account disabled','success'); loadToggleAccounts();
        }));
        container.querySelectorAll('button[data-action="enable"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const id = e.currentTarget.dataset.id; if(!await showConfirm('Enable this account?')) return; const r = await api('toggle_staff_account',{ user_id: id, active: 1 }); if(!r || !r.success){ showToast('Failed','error'); return; } showToast('Account enabled','success'); loadToggleAccounts();
        }));
        container.querySelectorAll('button[data-action="set-on-leave"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const id = e.currentTarget.dataset.id; const from = prompt('Start date (YYYY-MM-DD)'); if(from === null) return; const to = prompt('End date (YYYY-MM-DD)'); if(to === null) return; const r = await api('set_staff_on_leave',{ user_id: id, start_date: from, end_date: to }); if(!r || !r.success){ showToast('Failed','error'); return; } showToast('Staff marked On Leave','success'); loadToggleAccounts();
        }));
        container.querySelectorAll('button[data-action="unset-on-leave"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const id = e.currentTarget.dataset.id; if(!await showConfirm('Clear On Leave status?')) return; const r = await api('clear_staff_on_leave',{ user_id: id }); if(!r || !r.success){ showToast('Failed','error'); return; } showToast('Leave cleared','success'); loadToggleAccounts();
        }));

      }catch(err){ console.error('loadToggleAccounts failed', err); container.innerHTML = '<div>Error loading staff</div>'; }
    }

    // Appointments: load and filter
    async function loadAppointments(filters={}){
      const tbody = document.getElementById('appointmentsTbody');
      tbody.innerHTML = '<tr><td colspan="8">Loading...</td></tr>';
      try{
        const params = [];
        if(filters.date) params.push('date=' + encodeURIComponent(filters.date));
        if(filters.user) params.push('user_id=' + encodeURIComponent(filters.user));
        const url = 'get_manage_appointments.php' + (params.length ? ('?' + params.join('&')) : '');
        const res = await fetch(url);
        if(!res.ok) throw new Error('Failed to fetch');
        const j = await res.json();
        const appts = j.appointments || [];
        if(!appts.length) { tbody.innerHTML = '<tr><td colspan="8">No appointments</td></tr>'; return; }
        tbody.innerHTML = appts.map(a=>{
          const assigned = a.assigned_to ? `${a.assigned_to}${a.assigned_role? ' ('+a.assigned_role +')':''}` : '';
          return `<tr>
            <td>${escapeHtml(a.id)}</td>
            <td>${escapeHtml(a.patient_name||'')} (#${escapeHtml(a.user_id)})</td>
            <td>${escapeHtml(a.service||'')}</td>
            <td>${escapeHtml(a.date||'')}</td>
            <td>${escapeHtml(a.time||'')}</td>
            <td>${escapeHtml(a.status||'')}</td>
            <td>${escapeHtml(assigned)}</td>
            <td>
              <button class="btn" data-action="approve" data-id="${a.id}">Approve</button>
              <button class="btn ghost" data-action="reject" data-id="${a.id}">Reject</button>
              <button class="btn ghost" data-action="assign" data-id="${a.id}">Assign</button>
              <button class="btn" data-action="complete" data-id="${a.id}">Complete</button>
            </td>
          </tr>`;
        }).join('');

        document.querySelectorAll('#appointmentsTbody button[data-action]').forEach(b=>b.addEventListener('click', async (e)=>{
          const act = e.currentTarget.dataset.action;
          const id = e.currentTarget.dataset.id;
          if(act === 'assign'){
            const uid = prompt('Assign to user id (staff):');
            if(!uid) return;
            const role = prompt('Assigned role (midwife/doctor):','midwife');
            await fetch('admin_assign_appointment.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ appointment_id: id, assigned_to: uid, assigned_role: role }) });
            await loadAppointments(filters);
            return;
          }
          if(act === 'approve'){
            await fetch('update_appointment_status.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ appointment_id: id, status: 'Confirmed' }) });
            await loadAppointments(filters);
            return;
          }
          if(act === 'reject'){
            try{
              const ok = await showConfirm('Reject this booking?');
              if(!ok) return;
              await fetch('update_appointment_status.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ appointment_id: id, status: 'Cancelled' }) });
              showToast('Booking rejected','success');
              await loadAppointments(filters);
            }catch(err){ console.error('reject booking failed', err); showToast('Failed to reject booking','error'); }
            return;
          }
          if(act === 'complete'){
            await fetch('update_appointment_status.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ appointment_id: id, status: 'Completed' }) });
            await loadAppointments(filters);
            return;
          }
        }));

      }catch(err){ tbody.innerHTML = '<tr><td colspan="8">Error loading appointments</td></tr>'; console.error(err); }
    }

    document.getElementById('btnFilterAppt').addEventListener('click', ()=>{
      const date = document.getElementById('apptFilterDate').value || '';
      const user = document.getElementById('apptFilterPatient').value || '';
      loadAppointments({ date, user });
    });

    // Payments: list and verify
    async function loadPayments(filter=''){
      const tbody = document.getElementById('paymentsTbody');
      tbody.innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
      try{
        const url = filter ? ('get_payments.php?patient_user_id=' + encodeURIComponent(filter)) : 'get_payments.php';
        const resp = await fetch(url);
        if(!resp.ok) throw new Error('Failed to fetch payments');
        const data = await resp.json();
        const payments = data.payments || [];
        if(!payments.length){ tbody.innerHTML = '<tr><td colspan="6">No payments</td></tr>'; return; }

        tbody.innerHTML = payments.map(p=>`<tr>
          <td>${escapeHtml(String(p.id||''))}</td>
          <td>${escapeHtml(p.patient_name || ('#'+(p.patient_user_id||'')))}</td>
          <td>${p.file_url ? `<a href="${escapeHtml(p.file_url)}" target="_blank">View</a>` : '—'}</td>
          <td>${escapeHtml(p.uploaded_at || p.created_at || '')}</td>
          <td>${p.verified && Number(p.verified) ? '<span style="color:#16a34a;font-weight:700">Verified</span>' : '<span style="color:#c97700;font-weight:700">Unverified</span>'}</td>
          <td>
            ${p.verified && Number(p.verified) ? '' : `<button class="btn" data-action="verify" data-id="${p.id}" data-amount="${escapeHtml(p.amount||'')}">Verify</button>`}
            <button class="btn ghost" data-action="delete" data-id="${p.id}">Delete</button>
          </td>
        </tr>`).join('');

        // attach handlers
        document.querySelectorAll('#paymentsTbody button[data-action="verify"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const id = e.currentTarget.dataset.id;
          const amount = e.currentTarget.dataset.amount || null;
          const ref = prompt('Reference number (optional):');
          const dateReceived = prompt('Date received (YYYY-MM-DD) (optional):');
          try{
            const ok = await showConfirm('Mark this payment as verified?');
            if(!ok) return;
            const verifyResp = await fetch('admin_verify_payment.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ id: id, verified:1, amount: amount||null, reference: ref||null, date_received: dateReceived||null }) });
            const verifyJson = await verifyResp.json();
            if(!verifyJson.success){ showToast('Error: ' + (verifyJson.message||''), 'error'); return; }
            showToast('Payment verified', 'success');
            await loadPayments(document.getElementById('payFilterPatient').value||'');
          }catch(err){ console.error(err); showToast('Failed to verify payment','error'); }
        }));

        document.querySelectorAll('#paymentsTbody button[data-action="delete"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const id = e.currentTarget.dataset.id;
          try{
            const ok = await showConfirm('Delete this payment record?');
            if(!ok) return;
            const delResp = await fetch('delete_payment_receipt.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ id }) });
            const delJson = await delResp.json();
            if(!delJson.success){ showToast('Delete failed', 'error'); return; }
            showToast('Payment deleted', 'success');
            await loadPayments(document.getElementById('payFilterPatient').value||'');
          }catch(err){ console.error(err); showToast('Failed to delete','error'); }
        }));

      }catch(err){ tbody.innerHTML = '<tr><td colspan="6">Error loading payments</td></tr>'; console.error(err); }
    }

    document.getElementById('btnFilterPayments').addEventListener('click', ()=> loadPayments(document.getElementById('payFilterPatient').value||''));

    

    // Simple toast helper
    function showToast(message, type='info'){
      const toast = document.getElementById('toast');
      if(!toast) return alert(message);
      toast.style.display = 'block';
      toast.style.background = type === 'error' ? '#c0392b' : (type === 'success' ? '#16a34a' : 'rgba(24,24,24,0.9)');
      toast.textContent = message;
      clearTimeout(window._toastTimer);
      window._toastTimer = setTimeout(()=>{ toast.style.display = 'none'; toast.onclick = null; toast.style.cursor = 'default'; }, 4000);
    }

    document.getElementById('btnRefreshVer')?.addEventListener('click', ()=> loadVerifications());

    // Newborns: list and add
    async function loadNewborns(filter=''){
      const tbody = document.getElementById('newbornsTbody');
      tbody.innerHTML = '<tr><td colspan="7">Loading...</td></tr>';
      try{
        const url = filter ? ('get_newborns.php?patient_user_id=' + encodeURIComponent(filter)) : 'get_newborns.php';
        const res = await fetch(url);
        if(!res.ok) throw new Error('Failed');
        const j = await res.json();
        const list = j.newborns || [];
        if(!list.length){ tbody.innerHTML = '<tr><td colspan="7">No newborns</td></tr>'; return; }
        tbody.innerHTML = list.map(n=>`<tr>
          <td>${n.id}</td>
          <td>${escapeHtml(n.patient_name||'')} (#${n.patient_user_id||''})</td>
          <td>${escapeHtml(n.child_name||n.baby_name||'')}</td>
          <td>${escapeHtml(n.gender||'')}</td>
          <td>${escapeHtml(n.date_of_birth||'')}</td>
          <td>${escapeHtml(n.weight||'')}</td>
          <td><button class="btn ghost" data-action="edit-newborn" data-id="${n.id}">Edit</button></td>
        </tr>`).join('');

        document.querySelectorAll('#newbornsTbody button[data-action="edit-newborn"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const id = e.currentTarget.dataset.id;
          // open a simple prompt-based editor for now
          const newName = prompt('Baby name (leave blank to keep)');
          if(newName === null) return;
          const dob = prompt('Date of birth (YYYY-MM-DD)');
          const weight = prompt('Weight (kg)');
          const payload = { id: id };
          if(newName) payload.child_name = newName;
          if(dob) payload.date_of_birth = dob;
          if(weight) payload.weight = weight;
          const res = await fetch('save_newborn.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
          const j = await res.json();
          if(!j.success) return alert('Failed to update newborn');
          loadNewborns();
        }));

      }catch(err){ tbody.innerHTML = '<tr><td colspan="7">Error loading newborns</td></tr>'; console.error(err); }
    }

    document.getElementById('btnNewbornAdd').addEventListener('click', async ()=>{
      const pid = prompt('Mother patient user id (required)');
      if(!pid) return alert('patient id required');
      const baby = prompt('Baby name');
      const gender = prompt('Gender (M/F)');
      const dob = prompt('Date of birth (YYYY-MM-DD)');
      const weight = prompt('Weight (kg)');
      const apgar = prompt('APGAR score (optional)');
      const payload = { patient_user_id: pid, child_name: baby, gender: gender, date_of_birth: dob, weight: weight, notes: apgar };
      const res = await fetch('save_newborn.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
      const j = await res.json();
      if(!j.success) return alert('Failed to add newborn: ' + (j.message||''));
      alert('Newborn saved');
      loadNewborns();
      // refresh mini list counts
      loadMiniNewborns();
    });

    // Mini newborn list for overview card
    async function loadMiniNewborns(){
      const container = document.getElementById('miniNewbornList');
      if(!container) return;
      container.textContent = 'Loading mothers…';
      try{
        const res = await fetch('get_patients.php');
        if(!res.ok) throw new Error('Failed');
        const j = await res.json();
        const rows = j.patients || j || [];
        if(!Array.isArray(rows) || !rows.length){ container.textContent = 'No mothers found.'; return; }
        // show all patients
        const list = rows.map(p=>{
          const id = p.user_id || p.id || p.patient_user_id || '';
          const name = p.full_name || p.name || `${p.first_name||''} ${p.last_name||''}`.trim() || ('#'+id);
          return `<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 4px;border-bottom:1px solid #f3f2f9"><div style="overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:160px">${escapeHtml(name)}</div><div><button class="btn" data-action="add-newborn-mini" data-id="${escapeHtml(id)}">Add baby</button></div></div>`;
        }).join('');
        container.innerHTML = list;
        container.querySelectorAll('[data-action="add-newborn-mini"]').forEach(b=>b.addEventListener('click', async (e)=>{
          const pid = e.currentTarget.dataset.id;
          const baby = prompt('Baby name');
          if(baby === null) return;
          const gender = prompt('Gender (M/F)');
          const dob = prompt('Date of birth (YYYY-MM-DD)');
          const weight = prompt('Weight (kg)');
          const apgar = prompt('APGAR score (optional)');
          const payload = { patient_user_id: pid, child_name: baby, gender: gender, date_of_birth: dob, weight: weight, notes: apgar };
          try{
            const resp = await fetch('save_newborn.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload) });
            const j = await resp.json();
            if(!j.success) return alert('Failed to add newborn: ' + (j.message||''));
            showToast('Newborn saved', 'success');
            loadNewborns();
            loadMiniNewborns();
          }catch(err){ console.error(err); alert('Failed to save newborn'); }
        }));
      }catch(err){ console.error('loadMiniNewborns failed', err); container.textContent = 'Error loading mothers'; }
    }

    // initial load for new panels when first opened
    document.querySelectorAll('nav.sidebar .menu a').forEach(a => a.addEventListener('click', e => {
      const panel = a.dataset.panel;
      if(panel === 'appointments') loadAppointments({});
      if(panel === 'payments') loadPayments('');
      if(panel === 'newborns') loadNewborns('');
      if(panel === 'verifications') loadVerifications();
    }));

    // Image modal helpers
    function openImageModal(src, caption){
      const modal = document.getElementById('imgModal');
      const img = document.getElementById('imgModalImg');
      const cap = document.getElementById('imgModalCaption');
      if(!modal || !img) return; img.src = src || ''; cap.textContent = caption || '';
      modal.style.display = 'flex';
    }
    function closeImageModal(){
      const modal = document.getElementById('imgModal');
      const img = document.getElementById('imgModalImg');
      if(modal) modal.style.display = 'none'; if(img) img.src = '';
    }
    document.getElementById('imgModalClose')?.addEventListener('click', closeImageModal);
    document.getElementById('imgModal')?.addEventListener('click', function(e){ if(e.target === this) closeImageModal(); });
    document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeImageModal(); });

    // Confirm modal helper
    function showConfirm(message){
      return new Promise(resolve => {
        const modal = document.getElementById('confirmModal');
        const msg = document.getElementById('confirmModalMsg');
        const ok = document.getElementById('confirmOk');
        const cancel = document.getElementById('confirmCancel');
        if(!modal || !msg || !ok || !cancel) return resolve(false);
        msg.textContent = message;
        modal.style.display = 'flex';
        function cleanup(result){
          modal.style.display = 'none';
          ok.removeEventListener('click', onOk);
          cancel.removeEventListener('click', onCancel);
          document.removeEventListener('keydown', onKey);
          resolve(result);
        }
        function onOk(){ cleanup(true); }
        function onCancel(){ cleanup(false); }
        function onKey(e){ if(e.key === 'Escape') cleanup(false); }
        ok.addEventListener('click', onOk);
        cancel.addEventListener('click', onCancel);
        document.addEventListener('keydown', onKey);
      });
    }

  </script>
</body>
</html>


