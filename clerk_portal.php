<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'clerk') {
  header('Location: login.php'); exit;
}
// Provide some defaults for avatar/name if session doesn't have them
$userAvatar = htmlspecialchars($_SESSION['user_avatar'] ?? 'assets/images/logodrea.jpg');
$userName = htmlspecialchars($_SESSION['username'] ?? ($_SESSION['user_fullname'] ?? 'Clerk'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Clerk Dashboard — Drea Lying-In Clinic</title>
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
  a{Color:inherit;text-decoration:none}

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

  /* Profile dropdown + theme popover */
  .profile-menu{position:relative}
  .profile-menu .profile-btn{display:inline-flex;align-items:center;gap:8px}
  .profile-avatar{width:30px;height:30px;border-radius:50%;object-fit:cover;margin-right:8px;border:2px solid rgba(255,255,255,0.12)}
  .profile-dropdown{display:none;position:absolute;right:0;top:48px;min-width:260px;background:#fff;border-radius:12px;box-shadow:0 12px 40px rgba(0,0,0,0.12);overflow:hidden;z-index:350;border:1px solid rgba(0,0,0,0.06);}
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

  /* Layout and sidebar */
  .layout{display:flex;gap:20px;padding:28px 34px;align-items:flex-start;width:100%;max-width:1200px;margin:18px auto;transition: margin-left .18s ease, background .18s ease;}
  nav.sidebar{width:220px;background: linear-gradient(180deg, #fff, #fbf8ff);border-radius:14px;padding:20px;padding-bottom:18px;box-shadow:0 12px 40px rgba(40,20,80,0.06);display:flex;flex-direction:column;gap:8px;position:fixed;left:0;top:110px;bottom:20px;overflow:auto;z-index:60}
  nav.sidebar .nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:var(--lav-4);font-weight:700;margin-bottom:6px;cursor:pointer;font-size:0.95rem;line-height:1.1;transition:all .12s ease}
  nav.sidebar .nav-item:hover{background:rgba(156,125,232,0.06);transform:translateX(2px)}
  nav.sidebar .nav-item.active{background:linear-gradient(90deg,var(--accent),var(--lav-4));color:#fff;box-shadow:0 8px 20px rgba(156,125,232,0.12)}
  .sidebar-footer{margin-top:auto;padding-top:10px;padding-bottom:6px;border-top:1px solid rgba(156,125,232,0.06);display:flex;align-items:center;gap:10px}
  .sidebar-avatar{width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid rgba(156,125,232,0.06)}
  .sidebar-name{font-weight:700;color:var(--lav-4);font-size:0.95rem}
  .layout.has-fixed-sidebar{ margin-left: 260px; max-width: calc(100% - 260px); }
  main.content-area{flex:1;min-height:600px}
  section.panel{background:var(--card-bg);border-radius:12px;padding:20px;box-shadow:0 6px 24px rgba(0,0,0,0.04);margin-bottom:20px;}
  .panel h2{color:var(--lav-4);margin-bottom:12px;font-family:'Poppins',sans-serif}
  .muted{color:var(--muted);font-size:0.95rem;margin-bottom:14px}
  @media (max-width: 900px) { .layout{padding:16px} nav.sidebar{display:none} main.content-area{margin-left:0} }
  /* Payments table styling to match midwife portal visuals */
  #paymentsTable{width:100%;border-collapse:collapse;font-size:0.95rem;margin-top:12px}
  #paymentsTable thead tr{background:linear-gradient(90deg,var(--lav-2),#fff);color:var(--lav-4)}
  #paymentsTable thead th{padding:12px 10px;text-align:left;font-weight:700;border-bottom:1px solid rgba(0,0,0,0.06);font-size:0.95rem}
  #paymentsTable tbody td{padding:12px 10px;border-bottom:1px solid rgba(0,0,0,0.04);vertical-align:middle}
  #paymentsTable tbody tr:hover{background:rgba(156,125,232,0.03)}

  /* Status badges (pill style) */
  .status-badge{display:inline-block;padding:6px 10px;border-radius:999px;font-weight:700;font-size:0.85rem}
  .status-confirmed{background:linear-gradient(90deg,#eaf7ee,#dff0df);color:#1b5e20;border:1px solid rgba(36,125,60,0.08)}
  .status-pending{background:linear-gradient(90deg,#fff9ee,#fff3dd);color:#7a5b00;border:1px solid rgba(255,193,7,0.08)}
  .status-cancelled{background:linear-gradient(90deg,#fff0f0,#ffecec);color:#8b1e1e;border:1px solid rgba(231,76,60,0.08)}

  /* Buttons inside table rows */
  .btn{background:transparent;border:1px solid rgba(0,0,0,0.06);padding:8px 10px;border-radius:8px;cursor:pointer;font-weight:700}
  .btn.ghost{background:transparent;border:1px solid rgba(255,255,255,0.06);color:inherit}
  .btn.btn-paid{background:#2e7d32;color:#fff;border:none;padding:6px 10px;border-radius:18px}
  .btn.btn-verify{background:var(--accent);color:#fff;border:none;padding:6px 10px;border-radius:18px}
  .btn.btn-view-file{background:transparent;border:1px dashed rgba(0,0,0,0.06);padding:6px 8px;border-radius:6px}

  /* Small helpers to keep table readable on narrow screens */
  @media (max-width: 760px){ #paymentsTable thead{display:none} #paymentsTable tbody td{display:block;width:100%;box-sizing:border-box} #paymentsTable tbody tr{display:block;margin-bottom:12px;background:#fff;padding:10px;border-radius:8px} }

  /* Make ghost-style close buttons visible on white modal backgrounds */
  #filePreviewModal .btn-pill.ghost,
  #filePreviewInner .btn-pill.ghost,
  #verifyPaymentModal .btn-pill.ghost,
  #verifyPaymentInner .btn-pill.ghost,
  #paidPaymentModal .btn-pill.ghost,
  #paidPaymentInner .btn-pill.ghost{
    color: var(--lav-4);
    border: 1px solid rgba(0,0,0,0.06);
    background: transparent;
    box-shadow: none;
  }

  /* Manage table small column helpers (match midwife portal) */
  .col-service{min-width:160px;max-width:260px}
  .col-patient{min-width:160px;max-width:260px}
  .col-schedule{min-width:180px;max-width:260px}
  .btn-approve{background:var(--success);color:#fff;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;font-weight:600;font-size:0.85rem}
  .btn-decline{background:#fff;color:var(--danger);border:1px solid rgba(231,76,60,0.14);padding:6px 12px;border-radius:6px;cursor:pointer;font-weight:600;font-size:0.85rem}
  .btn-view{background:#fff;border:1px solid #eee;color:var(--lav-4);padding:6px 12px;border-radius:6px}
  .status-completed{background:linear-gradient(90deg,#3b82f6,#2563eb);color:#fff;border:1px solid rgba(37,99,235,0.12)}

  /* Manage table general styles (match midwife portal) */
  #manageTable{width:100%;border-collapse:collapse;margin-top:10px;font-size:0.95rem}
  #manageTable th, #manageTable td{padding:10px;border-bottom:1px solid #eee;text-align:left;vertical-align:middle}
  #manageTable th{background:#f9f6ff;color:var(--lav-4);font-weight:700}
  #manageTable tr:hover{background:#f6f2fc}
  #panel-manage tbody tr.cancelled-by-patient{ background: rgba(231,76,60,0.03); }
  #panel-manage tbody tr.cancelled-by-patient td{ color: rgba(133,63,63,0.95); }
  #panel-manage tbody tr.cancelled-by-patient .status-badge{ background: rgba(231,76,60,0.12); color: var(--danger); border:1px solid rgba(231,76,60,0.16); }

  </style>
  <style>
  /* Modal button styles (actionConfirmModal) */
  #actionConfirmModal .btn-cancel{background:transparent;border:1px solid rgba(0,0,0,0.06);color:var(--lav-4);padding:8px 12px;border-radius:10px;cursor:pointer;font-weight:700}
  #actionConfirmModal .btn-cancel:hover{background:rgba(156,125,232,0.06)}
  #actionConfirmModal #actionConfirmClose{background:transparent;border:1px solid rgba(0,0,0,0.06);padding:6px 8px;border-radius:8px;font-size:0.95rem}
  #actionConfirmModal #actionConfirmOkBtn{background:var(--accent);color:#fff;border:none;padding:8px 14px;border-radius:20px;font-weight:800;cursor:pointer}
  #actionConfirmModal #actionConfirmOkBtn:hover{filter:brightness(0.97)}
  #actionConfirmModal .dialog{position:relative}
  </style>
</head>
<body>
  <header class="site-top">
    <div class="header-left">
      <div class="logo">
        <img src="assets/images/logodrea.jpg" alt="Clinic Logo">
      </div>
      <div>
        <div class="clinic-name">Drea Lying-In Clinic</div>
        <div class="clinic-sub">Clerk Dashboard</div>
      </div>
    </div>
      <div class="brand-right" style="display:flex;align-items:center;gap:12px">
        <button id="hamburgerBtn" class="btn-pill ghost" style="display:none;align-items:center;justify-content:center;padding:8px 10px;margin-right:6px" aria-label="Toggle navigation">☰</button>
        <div class="profile-menu">
          <button id="profileBtn" class="profile-btn btn-pill" type="button">
                  <img src="<?= $userAvatar ?>" class="profile-avatar" alt="avatar"/>
                  <span><?= $userName ?></span>
                </button>
          <div id="profileDropdown" class="profile-dropdown" aria-hidden="true">
            <div class="pd-header">
                  <img src="<?= $userAvatar ?>" id="hdrAvatarSmall" alt="avatar">
              <div class="pd-meta">
                <div class="pd-name" id="hdrName"><?= $userName ?></div>
                <div class="pd-email" id="hdrEmail"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></div>
              </div>
            </div>
            <div class="pd-sep"></div>

            <div class="pd-group">
              <button class="pd-item primary" type="button" onclick="toggleProfileDropdown(false); openFormModal('edit');">
                <span class="icon-badge">⚙️</span>
                <span class="pd-label">Customize Profile</span>
              </button>

              <button class="pd-item" type="button" onclick="toggleProfileDropdown(false); openFormModal('info');">
                <span class="icon-badge">🧾</span>
                <span class="pd-label">Clerk Info</span>
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
      function toggleProfileDropdown(force){
        const dd = document.getElementById('profileDropdown');
        const btn = document.getElementById('profileBtn');
        if(!dd) return;
        if(typeof force === 'boolean'){
          if(force){ dd.style.display = 'block'; dd.setAttribute('aria-hidden','false'); } else { try{ const active = document.activeElement; if(active && dd.contains(active) && btn) btn.focus(); }catch(e){} dd.style.display = 'none'; dd.setAttribute('aria-hidden','true'); }
          return;
        }
        const isOpen = dd.style.display === 'block';
        if(isOpen){ try{ const active = document.activeElement; if(active && dd.contains(active) && btn) btn.focus(); }catch(e){} dd.style.display = 'none'; dd.setAttribute('aria-hidden','true'); }
        else { dd.style.display = 'block'; dd.setAttribute('aria-hidden','false'); }
      }

      document.addEventListener('click', function(e){
        const btn = document.getElementById('profileBtn');
        const dd = document.getElementById('profileDropdown');
        if(!dd) return;
        if(btn && btn.contains(e.target)) return;
        if(dd.contains(e.target)) return;
        dd.style.display = 'none'; dd.setAttribute('aria-hidden','true');
      });

      document.getElementById('profileBtn')?.addEventListener('click', function(e){ e.stopPropagation(); toggleProfileDropdown(); });

      document.addEventListener('DOMContentLoaded', function(){
        try{
          const saved = localStorage.getItem('user_avatar');
          const savedName = localStorage.getItem('user_fullname');
          if(saved && saved.length){ const headerAvatar = document.querySelector('.profile-menu .profile-btn img.profile-avatar'); if(headerAvatar) headerAvatar.src = saved; const hdr = document.getElementById('hdrAvatarSmall'); if(hdr) hdr.src = saved; const sa = document.getElementById('sidebarAvatar'); if(sa) sa.src = saved; }
          if(savedName){ const headerName = document.querySelector('.profile-menu .profile-btn span'); if(headerName) headerName.textContent = savedName; const hn = document.getElementById('hdrName'); if(hn) hn.textContent = savedName; const sn = document.getElementById('sidebarName'); if(sn) sn.textContent = savedName; }
        }catch(e){}
      });

      function openFormModal(mode){
        let modal = document.getElementById('formModal');
        if(!modal){
          modal = document.createElement('div'); modal.id = 'formModal'; modal.className = 'modal'; modal.style.display = 'none'; modal.setAttribute('aria-hidden','true');
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
                    <input id="pf_name" name="name" type="text" value="<?= $userName ?>" style="padding:8px;border-radius:8px;border:1px solid #eee;width:100%">
                  </div>
                  <div>
                    <label style="font-weight:700;color:var(--muted)">Email</label>
                    <input id="pf_email" name="email" type="email" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" style="padding:8px;border-radius:8px;border:1px solid #eee;width:100%">
                  </div>
                </div>
                <div style="display:flex;justify-content:flex-end;margin-top:12px;gap:8px">
                  <button type="button" class="btn-cancel" onclick="closeFormModal()">Cancel</button>
                  <button type="submit" class="btn-pill">Save</button>
                </div>
              </form>
            </div>`;
          document.body.appendChild(modal);
          modal.querySelector('#profileForm').addEventListener('submit', async function(e){ e.preventDefault(); showToast('Saving…'); try{ const data = Object.fromEntries(new FormData(this).entries()); const res = await fetch('admin_save_patient_details.php', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) }); const txt = await res.text(); let j=null; try{ j = txt ? JSON.parse(txt) : null; }catch(e){} if(res.ok && j && j.success){ showToast(j.message || 'Saved', 'success'); closeFormModal(); location.reload(); } else { showToast((j && j.message) ? j.message : 'Failed to save', 'error'); } }catch(err){ console.error(err); showToast('Network error', 'error'); } });
        }
        modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
        try{ const sb = modal.querySelector('button[type=submit]'); if(sb) sb.focus(); }catch(e){}
      }
      function closeFormModal(){ const m = document.getElementById('formModal'); if(m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); } }
    </script>
  </header>

  <div class="layout has-fixed-sidebar">
    <nav class="sidebar" aria-label="Main navigation">
        <div class="nav-item active" data-panel="manage">Manage Appointments</div>
      <div class="nav-item" data-panel="payments">Payments</div>
        <div class="nav-item" data-panel="inventory">Inventory</div>
      <div class="nav-item" data-panel="reports">Reports</div>
      
      <div class="sidebar-footer" id="sidebarFooter">
      <div class="sidebar-avatar-wrapper" style="position:relative;display:flex;align-items:center;gap:10px">
        <div style="position:relative;display:inline-block">
          <img src="<?= $userAvatar ?>" alt="avatar" class="sidebar-avatar" id="sidebarAvatar" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid rgba(0,0,0,0.06)">
          <button type="button" id="sidebarAvatarBtn" title="Change profile photo" style="position:absolute;right:-4px;bottom:-4px;width:28px;height:28px;border-radius:50%;border:1px solid rgba(0,0,0,0.06);background:#fff;color:var(--lav-4);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(0,0,0,0.08);cursor:pointer;padding:3px">
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

    <main class="content-area">
      <section id="panel-pending" class="panel">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <div>
            <h2 style="margin:0">Pending Appointments</h2>
            <div class="small-muted">Approve or reject appointments so staff can see confirmed schedules.</div>
          </div>
          <div class="filters">
            <button id="refreshBtn" class="btn ghost">Refresh</button>
          </div>
        </div>

        <div id="list" class="list"><div class="small-muted">Loading…</div></div>
      </section>

      <!-- INVENTORY PANEL (moved from midwife portal) -->
      <section id="panel-inventory" class="panel" style="display:none">
        <h2>Inventory</h2>
        <p class="muted">Track stocks and supplies (dextrose, abocath, syringe, gloves, oxygen, cannula adult, pedia under pads, sanitex, adult diapers, medicine).</p>

        <div style="margin-bottom:12px">
          <button class="btn-pill" id="newInventoryBtn">Update / Add Item</button>
        </div>

        <div id="inventoryForm" style="display:none;margin-bottom:14px">
          <form id="formInventory">
            <input type="hidden" name="id" id="inventory_item_id">
            <div class="form-grid">
              <div class="form-row"><label for="inventory_item_name">Item</label>
                <div style="display:flex;align-items:center;gap:8px">
                  <select id="inventory_item_name" name="item_name" required>
                  <option value="">-- select --</option>
                  <option>Dextrose</option>
                  <option>Abocath</option>
                  <option>Syringe</option>
                  <option>Gloves</option>
                  <option>Oxygen</option>
                  <option>Cannula Adult</option>
                  <option>Pedia Under Pads</option>
                  <option>Sanitex</option>
                  <option>Adult Diapers</option>
                  <option>Medicine</option>
                  </select>
                  <button type="button" id="toggleNewItemBtn" class="btn ghost" title="Add new item">Add</button>
                </div>
                <input id="inventory_item_name_text" name="item_name_custom" type="text" placeholder="New item name" style="display:none;margin-top:8px;padding:8px;border-radius:8px;border:1px solid #eee;width:100%;max-width:420px">
              </div>
              <div class="form-row"><label for="inventory_quantity">Quantity</label><input id="inventory_quantity" type="number" step="1" name="quantity" required></div>
              <div class="form-row"><label for="inventory_notes">Notes</label><input id="inventory_notes" name="notes"></div>
            </div>
            <div class="form-actions">
              <button type="button" class="btn-pill" id="saveInventoryBtn">Save Inventory</button>
              <button type="button" class="btn-pill ghost" id="cancelInventoryBtn">Cancel</button>
            </div>
          </form>
        </div>

        <table id="inventoryTable">
          <thead>
            <tr>
              <th>Item</th>
              <th>Quantity</th>
              <th>Notes</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="3" style="color:var(--muted);padding:20px;text-align:center">Loading inventory…</td></tr>
          </tbody>
        </table>
      </section>

      <section id="panel-manage" class="panel" style="display:none">
        <h2 style="margin:0">All Appointments</h2>
        <div class="small-muted">View and search all appointment bookings.</div>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-top:12px">
          <div style="flex:1;margin-right:12px"></div>
          <div style="flex:0;display:flex;align-items:center;gap:8px">
            <input id="manageSearch" placeholder="Search appointments by patient, service, date or ID" style="padding:8px;border-radius:8px;border:1px solid #eee;min-width:220px;width:360px">
            <button id="manageSearchClear" class="btn-pill ghost" type="button">Clear</button>
          </div>
        </div>
        <div style="margin-top:12px">
          <table id="manageTable">
            <thead>
              <tr>
                <th class="col-patient">Patient Name</th>
                <th class="col-service">Service</th>
                <th class="col-schedule">Date</th>
                <th>Time</th>
                <th>Handled By</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Loading appointments…</td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <section id="panel-payments" class="panel" style="display:none">
        <h2>Payments / Receipts</h2>
        <p class="muted">View uploaded payment receipts from the patient portal.</p>

        <table id="paymentsTable">
          <thead>
            <tr>
              <th>No.</th>
              <th>Receipt No.</th>
              <th>Date Uploaded</th>
              <th>Patient Name</th>
              <th>Service / Description</th>
              <th>GCash Ref No.</th>
              <th>Screenshot (File)</th>
              <th>Uploaded By</th>
              <th>Payment Status</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="10" style="color:var(--muted);padding:20px;text-align:center">Loading payments…</td></tr>
          </tbody>
        </table>

        <script>
        async function loadPayments(){
          const tbody = document.querySelector('#paymentsTable tbody');
          if(!tbody) return;
          tbody.innerHTML = `<tr><td colspan="10" style="color:var(--muted);padding:20px;text-align:center">Loading payments…</td></tr>`;
          try {
            const res = await fetch('get_payments.php', { credentials: 'same-origin' });
            const j = await res.json();
            if(!j.success || !Array.isArray(j.payments)) {
              tbody.innerHTML = `<tr><td colspan="10" style="color:var(--muted);padding:20px;text-align:center">No payments.</td></tr>`;
              return;
            }
            const payments = (Array.isArray(j.payments) ? j.payments : []).filter(p => {
              const nm = (p.patient_name || p.name || '') + '';
              return nm.trim().length > 0;
            });
            if(payments.length === 0){
              tbody.innerHTML = `<tr><td colspan="10" style="color:var(--muted);padding:20px;text-align:center">No payments.</td></tr>`;
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
                <td style="white-space:nowrap">${gcash}</td>
                <td style="white-space:nowrap">${fileBtn}</td>
                <td>${uploadedBy}</td>
                <td>${isPaid ? paidBtn : (status ? status + ' ' + paidBtn : paidBtn)}</td>
                <td style="white-space:nowrap">${remarks ? remarks : verifyBtn}</td>
              </tr>`;
            }).join('');
            tbody.innerHTML = rowsHtml;

            document.querySelectorAll('#paymentsTable .btn-view-file').forEach(btn=>{
              btn.addEventListener('click', function(e){
                const url = this.dataset.url;
                const fname = this.dataset.fname || '';
                viewMedicalFile(url, fname);
              });
            });

            document.querySelectorAll('#paymentsTable .btn-verify').forEach(btn=>{
              btn.addEventListener('click', function(e){
                const id = this.dataset.id;
                if(!id) return;
                // ensure verify modal exists and set the id then show it
                const hid = document.getElementById('verify_modal_payment_id');
                if(hid) hid.value = id;
                const modal = document.getElementById('verifyPaymentModal');
                if(modal){ modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false'); const close = document.getElementById('verifyPaymentClose'); if(close) close.focus(); }
              });
            });

            document.querySelectorAll('#paymentsTable .btn-paid').forEach(btn=>{
              btn.addEventListener('click', function(e){
                const id = this.dataset.id;
                if(!id) return;
                // open the paid modal and set the id (use modal flow instead of native confirm)
                const hid = document.getElementById('paid_modal_payment_id');
                if(hid) hid.value = id;
                const modal = document.getElementById('paidPaymentModal');
                if(modal){ modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false'); const close = document.getElementById('paidPaymentClose'); if(close) close.focus(); }
              });
            });
          } catch(err){
            console.error(err);
            tbody.innerHTML = `<tr><td colspan="11" style="color:var(--muted);padding:20px;text-align:center">Failed to load payments.</td></tr>`;
          }
        }

        // create file preview modal if missing
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
            function _hideModal(m){ try{ if(!m) return; m.style.display = 'none'; m.setAttribute('aria-hidden','true'); const c = document.getElementById('filePreviewContent'); if(c) c.innerHTML=''; }catch(e){ console.error(e); } }
            function _showModal(m){ try{ if(!m) return; m.style.display = 'flex'; m.setAttribute('aria-hidden','false'); }catch(e){ console.error(e); } }
            closeBtn.addEventListener('click', ()=>{ _hideModal(modal); });
            modal.addEventListener('click', function(e){ if(e.target === modal){ _hideModal(modal); } });
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ if(modal.style.display === 'flex' || modal.style.display === 'block'){ _hideModal(modal); } } });
          }
        }catch(e){ console.error('Failed to initialize file preview modal', e); }

        // create verify and paid modals (same UI as midwife portal)
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
            function _hideVerify(m){ try{ if(!m) return; m.style.display = 'none'; m.setAttribute('aria-hidden','true'); }catch(e){ console.error(e); } }
            document.getElementById('verifyPaymentClose').addEventListener('click', ()=>{ _hideVerify(modal); });
            document.getElementById('verifyModalCancel').addEventListener('click', ()=>{ _hideVerify(modal); });
            modal.addEventListener('click', function(e){ if(e.target === modal){ _hideVerify(modal); } });
            document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ if(modal.style.display === 'flex' || modal.style.display === 'block'){ _hideVerify(modal); } } });

            document.getElementById('verifyModalSubmit').addEventListener('click', async function(){
              const modalEl = document.getElementById('verifyPaymentModal');
              const id = document.getElementById('verify_modal_payment_id')?.value || '';
              if(!id){ alert('No payment selected for verification'); return; }
              try{
                const res = await fetch('admin_verify_payment.php', {
                  method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/json'},
                  body: JSON.stringify({ id: id, verified: 1 })
                });
                if(!res.ok){ if(res.status === 403){ showToast('Access denied. You do not have permission to verify payments.', 'error'); return; } const t = await res.text().catch(()=>'(no body)'); console.error('admin_verify_payment.php error', res.status, t); alert('Server error while verifying payment.'); return; }
                const j = await res.json().catch(()=>null);
                if(j && j.success){ showToast('Payment verified', 'success'); _hideVerify(modalEl); await loadPayments(); } else { alert('Verify failed: ' + (j && j.message ? j.message : 'server error')); }
              }catch(err){ console.error('Failed verifying payment', err); alert('Network error while verifying payment.'); }
            });
          }
        }catch(e){ console.error('Failed to initialize verify modal', e); }

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
            function _hidePaid(m){ try{ if(!m) return; m.style.display='none'; m.setAttribute('aria-hidden','true'); }catch(e){ console.error(e); } }
            function _showPaid(m){ try{ m.style.display='flex'; m.setAttribute('aria-hidden','false'); }catch(e){} }
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
                if(!res.ok){ if(res.status === 403){ showToast('Access denied. You do not have permission to mark payments as paid.', 'error'); return; } const t = await res.text().catch(()=>'(no body)'); console.error('midwife_mark_paid.php error', res.status, t); alert('Server error while marking payment as paid. See Console.'); return; }
                const j = await res.json().catch(()=>null);
                if(j && j.success){ showToast('<span class="status-badge status-confirmed">Paid</span>', 'html', 1800); _hidePaid(modalEl); await loadPayments(); } else { alert('Failed: ' + (j && j.message ? j.message : 'server error')); }
              }catch(err){ console.error('Failed marking paid', err); alert('Network error while marking payment as paid.'); }
            });
          }
        }catch(e){ console.error('Failed to initialize paid modal', e); }

        // helper to render file preview (supports images & pdf)
        function viewMedicalFile(url, fname){
          try{
            if(!url) return showToast('No file URL');
            const modal = document.getElementById('filePreviewModal');
            const content = document.getElementById('filePreviewContent');
            const title = document.getElementById('filePreviewTitle');
            if(!modal || !content) return window.open(url, '_blank');
            content.innerHTML = '';
            title.textContent = fname || 'Preview';
            if(/\.pdf$/i.test(url)){
              const iframe = document.createElement('iframe'); iframe.src = url; iframe.style.width = '100%'; iframe.style.height = '100%'; iframe.style.border = '0'; content.appendChild(iframe);
            } else {
              const img = document.createElement('img'); img.src = url; img.alt = fname || 'file'; img.style.maxWidth = '100%'; img.style.maxHeight = '100%'; img.style.borderRadius = '6px'; content.appendChild(img);
            }
            modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
          }catch(e){ console.error('viewMedicalFile error', e); window.open(url, '_blank'); }
        }

        </script>
      </section>

      <section id="panel-approved" class="panel" style="display:none">
        <h2 style="margin:0">Approved Appointments</h2>
        <div class="small-muted">Recently approved appointments.</div>
        <div id="approvedList" style="margin-top:12px"></div>
      </section>

      <section id="panel-reports" class="panel" style="display:none">
        <h2 style="margin:0">Reports</h2>
        <div class="small-muted">Simple reports for clerk activities (coming soon).</div>
      </section>
    </main>
  </div>

  <!-- Action confirm modal (used by showConfirmDialog) -->
  <div id="actionConfirmModal" class="confirm-modal" aria-hidden="true" style="display:none;position:fixed;inset:0;align-items:center;justify-content:center;background:rgba(0,0,0,0.45);z-index:1500">
    <div class="dialog" role="dialog" aria-modal="true" style="max-width:460px;width:92%;background:#fff;padding:16px;border-radius:10px;box-shadow:0 18px 60px rgba(0,0,0,0.22)">
      <header style="display:flex;justify-content:space-between;align-items:center">
        <h4 id="actionConfirmTitle" style="margin:0;color:var(--lav-4)">Confirm</h4>
        <button class="btn-cancel" type="button" id="actionConfirmClose">Close</button>
      </header>
      <div style="margin-top:12px">
        <p id="actionConfirmMessage" style="color:var(--muted);white-space:pre-wrap">Are you sure?</p>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
          <button id="actionConfirmCancelBtn" class="btn-cancel">Cancel</button>
          <button id="actionConfirmOkBtn" class="btn-pill">Confirm</button>
        </div>
      </div>
    </div>
  </div>

  <div id="toast" class="toast" role="status" aria-live="polite"></div>

  <style>
  /* Toast styles */
  .toast{position:fixed;right:20px;bottom:20px;display:none;align-items:center;padding:10px 14px;border-radius:10px;box-shadow:0 8px 28px rgba(20,10,60,0.12);z-index:1600;font-weight:700;background:linear-gradient(90deg,#ffffff,#fafafa);color:var(--lav-4)}
  .toast.success{background:linear-gradient(90deg,#eaf7ee,#dff0df);color:#1b5e20}
  .toast.error{background:linear-gradient(90deg,#fff0f0,#ffecec);color:#8b1e1e}
  .toast .toast-inner{display:flex;align-items:center;gap:8px}
  </style>

  <style>
  /* Inventory panel specific layout and table styling (scoped) */
  #panel-inventory .form-grid{display:grid;grid-template-columns:160px 1fr;gap:10px 18px;align-items:center}
  #panel-inventory .form-row label{font-weight:700;color:var(--muted);display:block;margin-bottom:6px}
  #panel-inventory .form-row input[type="number"],
  #panel-inventory .form-row input[type="text"],
  #panel-inventory .form-row select{padding:8px;border-radius:8px;border:1px solid #eee;width:100%;max-width:420px}
  #panel-inventory .form-actions{margin-top:12px;display:flex;gap:10px;align-items:center}

  /* Make inventory table readable and responsive */
  #inventoryTable{width:100%;border-collapse:collapse;margin-top:12px;font-size:0.95rem}
  #inventoryTable thead th{background:linear-gradient(90deg,var(--lav-2),#fff);color:var(--lav-4);padding:12px 10px;text-align:left;font-weight:700;border-bottom:1px solid rgba(0,0,0,0.06)}
  #inventoryTable tbody td{padding:12px 10px;border-bottom:1px solid rgba(0,0,0,0.04);vertical-align:middle}
  #inventoryTable tbody tr:hover{background:rgba(156,125,232,0.03)}
  #inventoryTable td:nth-child(2){width:110px;white-space:nowrap}
  #inventoryTable td:nth-child(4){white-space:nowrap}

  /* Edit button styling inside inventory rows */
  #inventoryTable .btn[data-action="edit-inv"]{padding:6px 10px;border-radius:8px;background:#fff;border:1px solid rgba(0,0,0,0.06);box-shadow:0 6px 18px rgba(156,125,232,0.04);color:var(--lav-4);font-weight:800}

  /* Small screens: make the table scroll horizontally rather than collapse rows */
  @media (max-width:780px){
    #panel-inventory .form-grid{grid-template-columns:1fr;}
    #inventoryTable{display:block;overflow:auto;white-space:nowrap}
    #inventoryTable thead{display:none}
    #inventoryTable tbody tr{display:inline-block;vertical-align:top;min-width:320px;margin-right:12px;background:#fff;border-radius:8px;padding:8px}
    #inventoryTable tbody td{display:block;padding:6px 10px;border-bottom:0}
  }

  /* Toggle Add button (small pill) */
  #toggleNewItemBtn{
    padding:6px 12px;border-radius:18px;font-weight:800;background:var(--accent);color:#fff;border:none;cursor:pointer;box-shadow:0 8px 20px rgba(156,125,232,0.14);font-size:0.95rem
  }
  #toggleNewItemBtn:focus{outline:3px solid rgba(156,125,232,0.14)}
  #toggleNewItemBtn.ghost{background:transparent;color:var(--lav-4);border:1px solid rgba(0,0,0,0.06);box-shadow:none}
  </style>

  <script>
    // Reset interactive UI state when switching panels
    function resetPanelStates(){
      try{
        const invForm = document.getElementById('inventoryForm'); if(invForm) invForm.style.display = 'none';
        const txt = document.getElementById('inventory_item_name_text'); if(txt){ txt.style.display = 'none'; txt.value = ''; }
        const sel = document.getElementById('inventory_item_name'); if(sel) sel.style.display = '';
        const toggleBtn = document.getElementById('toggleNewItemBtn'); if(toggleBtn) toggleBtn.textContent = 'Add';
        const idf = document.getElementById('inventory_item_id'); if(idf) idf.value = '';

        const ms = document.getElementById('manageSearch'); if(ms){ ms.value = ''; try{ applyManageFilter(); }catch(e){} }

        // Close commonly created modals if open
        ['filePreviewModal','verifyPaymentModal','paidPaymentModal','formModal','actionConfirmModal','patientInfoModal'].forEach(id=>{
          const m = document.getElementById(id); if(m){ m.style.display = 'none'; try{ m.setAttribute('aria-hidden','true'); }catch(e){} }
        });

        // reset scroll to top of content area
        const main = document.querySelector('main.content-area'); if(main){ main.scrollTop = 0; }
        try{ window.scrollTo({ top: 0, left: 0, behavior: 'instant' }); }catch(e){ window.scrollTo(0,0); }
      }catch(e){ console.error('resetPanelStates error', e); }
    }

    // Sidebar nav switching
    document.querySelectorAll('nav.sidebar .nav-item').forEach(el=> el.addEventListener('click', function(){
      // reset any transient UI before switching
      resetPanelStates();
      document.querySelectorAll('nav.sidebar .nav-item').forEach(n=> n.classList.remove('active'));
      this.classList.add('active');
      const panel = this.dataset.panel;
      document.querySelectorAll('main.content-area section.panel').forEach(p=> p.style.display = (p.id === 'panel-'+panel) ? 'block' : 'none');
      if(panel === 'pending') loadPending();
      if(panel === 'manage') loadManageAppointments();
      if(panel === 'approved') loadApproved();
      if(panel === 'payments') loadPayments();
      if(panel === 'inventory') { try{ if(typeof loadInventory === 'function') loadInventory(); }catch(e){} }
    }));

    /* Promise-based confirmation dialog (uses actionConfirmModal if present, falls back to native confirm) */
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
            const ok = confirm(message || (title || 'Are you sure?'));
            resolve(!!ok);
            return;
          }
          titleEl && (titleEl.textContent = title || 'Confirm');
          msgEl && (msgEl.textContent = message || 'Are you sure?');
          modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');

          function cleanup(){
            try{ modal.style.display = 'none'; modal.setAttribute('aria-hidden','true'); }catch(_){ }
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
      const s = String(dt).trim();
      let datePart = s, timePart = '';
      if(s.indexOf('T') !== -1){ [datePart, timePart] = s.split('T'); }
      else if(s.indexOf(' ') !== -1){ [datePart, timePart] = s.split(' '); }
      else { datePart = s; timePart = ''; }
      const dParts = datePart.split('-'); if(dParts.length !== 3) return s;
      const yyyy = Number(dParts[0]) || 0; const mm = Number(dParts[1]) - 1; const dd = Number(dParts[2]) || 0;
      let hh = 0, min = 0, sec = 0;
      if(timePart){ const t = timePart.split(':'); hh = Number(t[0]) || 0; min = Number(t[1]) || 0; sec = Number(t[2]) || 0; }
      const dtObj = new Date(yyyy, mm, dd, hh, min, sec); if(isNaN(dtObj.getTime())) return s;
      const hours = dtObj.getHours(); const minutes = String(dtObj.getMinutes()).padStart(2,'0'); const ampm = hours >= 12 ? 'PM' : 'AM'; const hour12 = ((hours + 11) % 12) + 1;
      const dateFmt = `${String(dd).padStart(2,'0')}/${String(dParts[1])}/${String(yyyy)}`;
      return `${dateFmt} ${hour12}:${minutes} ${ampm}`;
    } catch(e){ return dt; } }

    /* Format a time string like "HH:MM" or "HH:MM:SS" into "h:MM AM/PM" */
    function formatTimeToDisplay(t){ if(!t) return ''; try{ const s = String(t).trim(); const parts = s.split(':'); if(parts.length < 1) return s; const hh = Number(parts[0]) || 0; const mm = parts.length > 1 ? Number(parts[1]) : 0; const ampm = hh >= 12 ? 'PM' : 'AM'; const hour12 = ((hh + 11) % 12) + 1; return `${hour12}:${String(mm).padStart(2,'0')} ${ampm}`; }catch(e){ return t; } }

    /* Helper: today's date in YYYY-MM-DD */
    function todayDateString(){ const t = new Date(); const yyyy = t.getFullYear(); const mm = String(t.getMonth()+1).padStart(2,'0'); const dd = String(t.getDate()).padStart(2,'0'); return `${yyyy}-${mm}-${dd}`; }

    /* Load manage appointments (feature parity with midwife portal) */
    async function loadManageAppointments(userId){
      const tbody = document.querySelector('#manageTable tbody');
      if(!tbody) return;
      tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Loading appointments…</td></tr>`;
      try{
        let url = 'get_manage_appointments.php'; if(userId) url += '?user_id=' + encodeURIComponent(userId);
        const res = await fetch(url, { credentials: 'include' });
        if(!res.ok){ const body = await res.text().catch(()=>'(no body)'); console.error('get_manage_appointments.php returned non-OK', res.status, res.statusText, body); if(res.status===401){ tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Not authenticated — please log in.</td></tr>`; return; } if(res.status===403){ tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Permission denied.</td></tr>`; return; } tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Failed to load appointments (server error).</td></tr>`; return; }
        let data; try{ data = await res.json(); } catch(parseErr){ const txt = await res.text().catch(()=>'(no body)'); console.error('Failed parsing JSON from get_manage_appointments.php:', parseErr, txt); tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Invalid server response while loading appointments.</td></tr>`; return; }
        if(!data.success || !Array.isArray(data.appointments)){ tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">No appointments found.</td></tr>`; return; }

        // convert and sort similar to midwife logic
        let rows = data.appointments.slice().sort((a,b)=>{
          const todayNow = (typeof todayDateString === 'function') ? todayDateString() : null;
          const aAptDate = a.date || a.appointment_date || '';
          const bAptDate = b.date || b.appointment_date || '';
          const aStatus = String(a.status || '').toLowerCase();
          const bStatus = String(b.status || '').toLowerCase();
          const aCancelled = !!(a.cancelled_by || a.cancelled_at || (aStatus && /cancel/i.test(aStatus)));
          const bCancelled = !!(b.cancelled_by || b.cancelled_at || (bStatus && /cancel/i.test(bStatus)));
          const aCompleted = (!aCancelled && aAptDate && todayNow && aAptDate < todayNow) || aStatus.includes('completed');
          const bCompleted = (!bCancelled && bAptDate && todayNow && bAptDate < todayNow) || bStatus.includes('completed');
          if(aCompleted && !bCompleted) return 1; if(!aCompleted && bCompleted) return -1;
          const aBooked = a.booked_at || a.created_at || a.created || a.booked || '';
          const bBooked = b.booked_at || b.created_at || b.created || b.booked || '';
          try{ if(aBooked && bBooked){ const ta = Date.parse(String(aBooked)) || 0; const tb = Date.parse(String(bBooked)) || 0; if(ta !== tb) return tb - ta; } else if(aBooked && !bBooked) return -1; else if(!aBooked && bBooked) return 1; } catch(e){}
          const aDate = (a.date || a.appointment_date || '') + ' ' + (a.time || a.appointment_time || ''); const bDate = (b.date || b.appointment_date || '') + ' ' + (b.time || b.appointment_time || ''); const pa = Date.parse(aDate) || 0; const pb = Date.parse(bDate) || 0; if(pa !== pb) return pb - pa; return String(bDate).localeCompare(String(aDate));
        });

        // remove unknown patients
        rows = rows.filter(a => { const name = a.patient_name || (a.patient && a.patient.name) || a.patient || ''; if(!name) return false; const s = String(name).trim().toLowerCase(); if(!s) return false; if(s === 'unknown' || s === 'n/a' || s === 'anonymous') return false; return true; });
        if(rows.length === 0){ tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">No appointments available.</td></tr>`; return; }

        tbody.innerHTML = rows.map(a=>{
          const id = escapeHtml(a.id || '');
          const patientName = escapeHtml(a.patient_name || a.patient || 'Unknown');
          const phone = escapeHtml(a.mobile_number || a.cellphone || '');
          const service = escapeHtml(a.service || '—');
          const dateText = escapeHtml(a.date || a.appointment_date || '');
          const timeRaw = (a.time || a.appointment_time || '');
          const timeText = escapeHtml(formatTimeToDisplay(timeRaw));
          const handledBy = escapeHtml(a.assigned_midwife || a.assigned_provider || a.handled_by || '—');
          const rawStatus = a.status || 'pending';
          const status = String(rawStatus).toLowerCase();
          const aptDate = (a.date || a.appointment_date || '');
          const todayNow = (typeof todayDateString === 'function') ? todayDateString() : null;
          const isCancelledByPatient = ((a.cancelled_by !== undefined && a.cancelled_by !== null && String(a.cancelled_by) !== '') || (a.cancelled_at !== undefined && a.cancelled_at !== null && String(a.cancelled_at).trim() !== '') || (status.includes('cancel') && status.includes('patient')));
          const statusForChecks = isCancelledByPatient ? 'cancelled' : status;
          const isCompleted = (statusForChecks.indexOf('completed') !== -1) || (aptDate && todayNow && aptDate < todayNow && statusForChecks !== 'cancelled');
          const isConfirmed = statusForChecks.includes('confirm');
          let statusHtml = '';
          if(isCancelledByPatient) statusHtml = `<span class="status-badge status-cancelled">Cancelled by patient</span>`;
          else if(isCompleted) statusHtml = `<span class="status-badge status-completed">Completed</span>`;
          else statusHtml = isConfirmed ? `<span class="status-badge status-confirmed">${escapeHtml(rawStatus)}</span>` : (statusForChecks === 'cancelled' ? `<span class="status-badge status-cancelled">${escapeHtml(rawStatus)}</span>` : `<span class="status-badge status-pending">${escapeHtml(rawStatus)}</span>`);

          const showActions = !isCompleted && !isConfirmed && !isCancelledByPatient && (statusForChecks.includes('pending') || statusForChecks.includes('reschedule') || a.requested_date);
          const approveBtn = showActions ? `<button class="btn btn-approve" data-action="confirm" data-id="${id}">Confirm</button>` : '';
          const approveRescheduleBtn = showActions && a.requested_date ? `<button class="btn" data-action="approve_reschedule" data-id="${id}">Approve Reschedule</button>` : '';
          const cancelBtn  = showActions ? `<button class="btn btn-decline" data-action="cancel" data-id="${id}">Cancel</button>` : '';

          const phoneHtml = phone ? `<div style="color:var(--muted);font-size:0.85rem">${phone}</div>` : '';
          // When actions are available show buttons; otherwise show the status badge (same as midwife portal)
          const actionHtml = showActions ? `${approveBtn} ${approveRescheduleBtn} ${cancelBtn}` : `${statusHtml}`;
          const rowClass = isCancelledByPatient ? 'cancelled-by-patient' : '';
          return `<tr data-id="${id}" class="${rowClass}"><td>${patientName}${phoneHtml}</td><td>${service}</td><td>${dateText}</td><td>${timeText}</td><td>${handledBy}</td><td style="white-space:nowrap">${actionHtml}</td></tr>`;
        }).join('');

        // wire action buttons
        document.querySelectorAll('#manageTable button').forEach(btn=>{ btn.addEventListener('click', onManageAction); });
        // apply active search filter if present
        try{ if(typeof applyManageFilter === 'function') applyManageFilter(); }catch(e){}
      } catch(err){ console.error('Failed loading manage appointments', err); tbody.innerHTML = `<tr><td colspan="6" style="color:var(--muted);padding:20px;text-align:center">Failed to load appointments.</td></tr>`; }
    }

    /* Handler for manage actions (confirm/approve_reschedule/cancel/view) */
    async function onManageAction(e){
      const btn = e.currentTarget;
      const action = btn.dataset.action;
      const apptId = btn.dataset.id;
      if(!apptId) return;
      if(action === 'view'){
        try{
          const res = await fetch(`get_appointment_details.php?id=${encodeURIComponent(apptId)}`, { credentials: 'same-origin' });
          if(res.ok){
            const data = await res.json().catch(()=>null);
            if(data && data.success && data.appointment){
              const appt = data.appointment;
              const modal = document.getElementById('patientInfoModal');
              if(modal){
                try{ document.getElementById('mi_name').textContent = appt.name || appt.patient_name || '-'; }catch(e){}
                try{ document.getElementById('mi_age').textContent = appt.age || '-'; }catch(e){}
                try{ document.getElementById('mi_address').textContent = appt.address || '-'; }catch(e){}
                try{ document.getElementById('mi_bday').textContent = appt.birthday || appt.bday || '-'; }catch(e){}
                try{ document.getElementById('mi_mobile').textContent = appt.mobile_number || appt.cellphone || '-'; }catch(e){}
                try{ document.getElementById('mi_email').textContent = appt.email || '-'; }catch(e){}
                try{ document.getElementById('mi_obstetric').textContent = appt.obstetric_history || appt.obstetric || '-'; }catch(e){}
                try{ const _mi_notes = document.getElementById('mi_notes'); if(_mi_notes) _mi_notes.textContent = appt.notes || '-'; }catch(e){}
                try{ window.__lastFocusedBeforePatientModal = document.activeElement; }catch(e){}
                modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
                try{ const cb = document.getElementById('patientInfoClose'); if(cb) cb.focus(); }catch(e){}
              }
              return;
            }
          }
        } catch(err){ console.error('Failed fetching appointment details', err); }
        alert('Unable to load full appointment details.');
        return;
      }

      const confirmText = action === 'confirm' ? 'Are you sure you want to mark this appointment as Confirmed?' : 'Are you sure you want to cancel this appointment?';
      const proceed = await showConfirmDialog('Confirm', confirmText);
      if(!proceed) return;
      try{
        if(action === 'approve_reschedule'){
          const res = await fetch('update_appointment_status.php', { method: 'POST', credentials: 'include', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ appointment_id: apptId, action: 'approve_reschedule' }) });
          if(!res.ok){ const body = await res.text().catch(()=>'(no body)'); console.error('approve_reschedule failed', res.status, res.statusText, body); alert('Server error while approving reschedule.'); return; }
          const result = await res.json().catch(()=>null);
          if(result && result.success){ await loadManageAppointments(); await loadPending(); return; } else { alert('Failed: ' + (result && result.message ? result.message : 'server error')); return; }
        }
        const newStatus = action === 'confirm' ? 'confirmed' : 'cancelled';
        const res = await fetch('update_appointment_status.php', { method:'POST', credentials:'include', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ appointment_id: apptId, status: newStatus }) });
        if(!res.ok){ const body = await res.text().catch(()=>'(no body)'); console.error('update_appointment_status.php returned non-OK', res.status, res.statusText, body); if(res.status===401){ alert('You are not authenticated.'); return; } if(res.status===403){ alert('You do not have permission to perform this action.'); return; } alert('Server error while updating appointment.'); return; }
        const result = await res.json().catch(()=>null);
        if(result && result.success){ await loadManageAppointments(); await loadPending(); } else { showToast('Failed to update appointment: ' + (result && result.message ? result.message : 'server error'), 'error'); }
      } catch(err){ console.error('Failed updating appointment status', err); alert('Network error while updating appointment status.'); }
    }

    function showToast(content, typeOrTimeout=3000, optTimeout){
      const t = document.getElementById('toast'); if(!t) return;
      clearTimeout(t._t);
      // reset classes
      t.className = 'toast';
      let isHtml = false; let delay = 3000; let type = '';
      if(typeof typeOrTimeout === 'number'){ delay = typeOrTimeout; }
      else if(typeof typeOrTimeout === 'string'){
        if(typeOrTimeout === 'html'){ isHtml = true; delay = (typeof optTimeout === 'number') ? optTimeout : 3000; }
        else if(['success','error','info'].includes(typeOrTimeout)){ type = typeOrTimeout; delay = (typeof optTimeout === 'number') ? optTimeout : 3000; }
        else { delay = (typeof optTimeout === 'number') ? optTimeout : 3000; }
      }
      if(type) t.classList.add(type);
      if(isHtml){ t.innerHTML = '<div class="toast-inner">'+content+'</div>'; }
      else { t.textContent = content; }
      t.style.display = 'flex';
      t._t = setTimeout(()=>{ t.style.display='none'; t.className='toast'; }, delay);
    }

    async function loadPending(){
      const list = document.getElementById('list'); list.innerHTML = '<div class="small-muted">Loading…</div>';
      try{
        const res = await fetch('get_appointments.php', { credentials: 'same-origin' });
        if(!res.ok) throw new Error('Network');
        const j = await res.json();
        if(!j.success || !Array.isArray(j.appointments)) throw new Error('Invalid response');
        const pending = j.appointments.filter(a=>{ const s=String(a.status||'').toLowerCase(); return s==='pending' || s.indexOf('pending')!==-1; });
        if(pending.length===0){ list.innerHTML = '<div class="small-muted">No pending appointments.</div>'; return; }
        list.innerHTML = '';
        pending.forEach(a=>{
          const div = document.createElement('div'); div.className='item';
          const meta = document.createElement('div'); meta.innerHTML = `<div style="font-weight:700">${escapeHtml(a.service||'')}</div><div class="small-muted">${escapeHtml(a.appointment_date||'')} ${escapeHtml(a.appointment_time||'')}</div><div class="small-muted">Patient: ${escapeHtml(a.patient_name||a.name||a.mobile_number||'—')}</div>`;
          const actions = document.createElement('div'); actions.className='appointment-actions';
          const approve = document.createElement('button'); approve.className='btn'; approve.textContent='Approve'; approve.onclick = ()=> actionConfirm(a.id, 'Confirmed');
          const reject = document.createElement('button'); reject.className='btn ghost'; reject.style.marginLeft='8px'; reject.textContent='Reject'; reject.onclick = ()=> actionConfirm(a.id, 'Cancelled by clinic');
          actions.appendChild(approve); actions.appendChild(reject);
          div.appendChild(meta); div.appendChild(actions);
          list.appendChild(div);
        });
      }catch(e){ console.error(e); list.innerHTML = '<div class="small-muted">Failed to load pending appointments.</div>'; }
    }

    async function actionConfirm(id, status){
      // Use the modal-based confirm dialog (falls back to native confirm if modal missing)
      try{
        const proceed = await showConfirmDialog('Confirm', 'Mark appointment #' + id + ' as "' + status + '"?');
        if(!proceed) return;
      } catch(err){ console.error('Confirm dialog error', err); return; }
      try{
        const res = await fetch('update_appointment.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: id, status: status }) });
        if(!res.ok){ const body = await res.text().catch(()=>'(no body)'); console.error('update_appointment.php returned non-OK', res.status, res.statusText, body); showToast('Server error while updating appointment.', 'error'); return; }
        const j = await res.json().catch(()=>null);
        if(j && j.success){ showToast('Appointment updated', 'success'); try{ loadPending(); }catch(e){} try{ loadManageAppointments(); }catch(e){} } else { showToast('Failed: ' + (j && j.message ? j.message : 'server error'), 'error'); }
      }catch(e){ console.error(e); showToast('Network error', 'error'); }
    }

    async function loadManage(){
      const tbody = document.querySelector('#manageTable tbody'); if(!tbody) return; tbody.innerHTML = '<tr><td colspan="6" style="padding:12px;color:var(--muted)">Loading…</td></tr>';
      try{
        const res = await fetch('get_manage_appointments.php', { credentials: 'same-origin' }); if(!res.ok) throw new Error('Network'); const j = await res.json(); const appts = j.appointments || j.data || [];
        if(!Array.isArray(appts) || appts.length===0){ tbody.innerHTML = '<tr><td colspan="6" style="padding:12px;color:var(--muted)">No appointments found.</td></tr>'; return; }
        tbody.innerHTML = appts.map(a=>{
          const patient = escapeHtml(a.patient_name || a.name || a.mobile_number || '—');
          const svc = escapeHtml(a.service || a.appointment_service || '');
          const date = escapeHtml(a.appointment_date || a.date || '');
          const time = escapeHtml(a.appointment_time || a.time || '');
          const handledBy = escapeHtml(a.assigned_midwife || a.assigned_provider || a.handled_by || '—');
          // show status badge when no actions available (keep parity with midwife portal)
          const rawStatus = a.status || 'pending';
          const s = String(rawStatus).toLowerCase();
          let statusHtml = '';
          if(String(a.cancelled_by || '').trim() || String(a.cancelled_at || '').trim() || (s.includes('cancel') && s.includes('patient'))) statusHtml = `<span class="status-badge status-cancelled">Cancelled by patient</span>`;
          else if(s.indexOf('completed') !== -1) statusHtml = `<span class="status-badge status-completed">Completed</span>`;
          else statusHtml = (s.indexOf('confirm') !== -1) ? `<span class="status-badge status-confirmed">${escapeHtml(rawStatus)}</span>` : `<span class="status-badge status-pending">${escapeHtml(rawStatus)}</span>`;
          const actions = (s.indexOf('pending') !== -1 || s.indexOf('reschedule') !== -1 || a.requested_date) ? `<button class="btn btn-approve" onclick="actionConfirm(${JSON.stringify(a.id)}, 'Confirmed')">Approve</button> <button class="btn ghost" onclick="actionConfirm(${JSON.stringify(a.id)}, 'Cancelled by clinic')">Reject</button>` : statusHtml;
          const rowClass = (String(a.cancelled_by || '').trim() || String(a.cancelled_at || '').trim() || (s.includes('cancel') && s.includes('patient'))) ? 'cancelled-by-patient' : '';
          return `<tr class="${rowClass}"><td>${patient}</td><td>${svc}</td><td>${date}</td><td>${time}</td><td>${handledBy}</td><td>${actions}</td></tr>`;
        }).join('');
        try{ if(typeof applyManageFilter === 'function') applyManageFilter(); }catch(e){}
      }catch(e){ console.error(e); tbody.innerHTML = '<tr><td colspan="6" style="padding:12px;color:var(--muted)">Failed to load appointments.</td></tr>'; }
    }

    async function loadApproved(){
      const wrap = document.getElementById('approvedList'); if(!wrap) return; wrap.innerHTML = '<div class="small-muted">Loading…</div>';
      try{ const res = await fetch('get_appointments.php', { credentials: 'same-origin' }); if(!res.ok) throw new Error('Network'); const j = await res.json(); const appts = j.appointments || []; const approved = appts.filter(a=> String(a.status||'').toLowerCase().indexOf('confirm')!==-1 || String(a.status||'').toLowerCase().indexOf('approved')!==-1 ); if(approved.length===0){ wrap.innerHTML = '<div class="small-muted">No approved appointments.</div>'; return; } wrap.innerHTML = approved.map(a=>`<div class="item"><div><div style="font-weight:700">${escapeHtml(a.service||'')}</div><div class="small-muted">${escapeHtml(a.appointment_date||'')} ${escapeHtml(a.appointment_time||'')}</div><div class="small-muted">Patient: ${escapeHtml(a.patient_name||a.name||'—')}</div></div></div>`).join(''); }catch(e){ console.error(e); wrap.innerHTML = '<div class="small-muted">Failed to load approved appointments.</div>'; }
    }

    function escapeHtml(s){ if(s===null||s===undefined) return ''; return String(s).replace(/[&<>'"`]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":"&#39;",'"':'&quot;','`':'&#96;'}[c])); }

    document.getElementById('refreshBtn').addEventListener('click', ()=>{ const active = document.querySelector('nav.sidebar .nav-item.active'); if(active) active.click(); else loadManageAppointments(); });
    // wire manage table search/filter
    function applyManageFilter(){ const inp = document.getElementById('manageSearch'); if(!inp) return; const q = String(inp.value || '').trim().toLowerCase(); const tbody = document.querySelector('#manageTable tbody'); if(!tbody) return; const rows = Array.from(tbody.querySelectorAll('tr')); rows.forEach(r=>{ // keep loading / empty message rows visible
      const tds = r.querySelectorAll('td'); if(tds.length === 1 && tds[0].hasAttribute('colspan')){ if(q) r.style.display = ''; else r.style.display = ''; return; }
      const text = (r.textContent || '').toLowerCase(); if(!q) { r.style.display = ''; } else { r.style.display = (text.indexOf(q) !== -1) ? '' : 'none'; } }); }

    document.getElementById('manageSearch')?.addEventListener('input', function(){ applyManageFilter(); });
    document.getElementById('manageSearchClear')?.addEventListener('click', function(){ const inp = document.getElementById('manageSearch'); if(inp) inp.value = ''; applyManageFilter(); });

    // initial load
    // Inventory handlers (clerk)
    document.getElementById('newInventoryBtn')?.addEventListener('click', ()=>{
      const formWrap = document.getElementById('inventoryForm'); if(formWrap) formWrap.style.display = 'block';
      const saveBtn = document.getElementById('saveInventoryBtn'); if(saveBtn) saveBtn.textContent = 'Save Inventory';
      // clear hidden id
      const idf = document.getElementById('inventory_item_id'); if(idf) idf.value = '';
      // reset fields
      try{ document.getElementById('inventory_item_name').value = ''; document.getElementById('inventory_quantity').value=''; document.getElementById('inventory_notes').value=''; const txt = document.getElementById('inventory_item_name_text'); if(txt){ txt.style.display='none'; txt.value=''; } const tbtn = document.getElementById('toggleNewItemBtn'); if(tbtn) tbtn.textContent='Add'; }catch(e){}
    });
    // Toggle for adding custom/new item name
    document.getElementById('toggleNewItemBtn')?.addEventListener('click', function(){
      const txt = document.getElementById('inventory_item_name_text');
      const sel = document.getElementById('inventory_item_name');
      if(!txt || !sel) return;
      const showing = txt.style.display !== 'none' && txt.style.display !== '';
      if(showing){ // hide text input, show select
        txt.style.display = 'none';
        txt.value = '';
        sel.style.display = '';
        this.textContent = 'Add';
      } else {
        txt.style.display = 'block';
        txt.focus();
        sel.style.display = 'none';
        this.textContent = 'Cancel';
      }
    });
    document.getElementById('cancelInventoryBtn')?.addEventListener('click', ()=>{ const f = document.getElementById('inventoryForm'); if(f) f.style.display = 'none'; });
    document.getElementById('saveInventoryBtn')?.addEventListener('click', async ()=>{
      const form = document.getElementById('formInventory'); if(!form) return;
      const fd = new FormData(form);
      const data = Object.fromEntries(fd.entries());
      // if custom item name is visible/useful, prefer it over select
      const txtEl = document.getElementById('inventory_item_name_text');
      if(txtEl && txtEl.style.display !== 'none' && String(txtEl.value || '').trim().length){ data.item_name = String(txtEl.value).trim(); }
      try{
        const res = await fetch('save_inventory.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify(data) });
        const j = await res.json().catch(()=>null);
        if(j && j.success){ form.reset(); const idf = document.getElementById('inventory_item_id'); if(idf) idf.value = ''; const saveBtn = document.getElementById('saveInventoryBtn'); if(saveBtn) saveBtn.textContent = 'Save Inventory'; // ensure custom input hidden after save
          const txtEl = document.getElementById('inventory_item_name_text'); if(txtEl){ txtEl.style.display = 'none'; txtEl.value = ''; }
          const sel = document.getElementById('inventory_item_name'); if(sel) sel.style.display = '';
          const toggleBtn = document.getElementById('toggleNewItemBtn'); if(toggleBtn) toggleBtn.textContent = 'Add';
          document.getElementById('inventoryForm').style.display = 'none'; await loadInventory(); showToast('Saved', 'success'); }
        else { showToast('Save failed: ' + (j && j.message ? j.message : 'server error'), 'error'); }
      }catch(err){ console.error(err); showToast('Network error', 'error'); }
    });

    // populate/edit helpers
    function populateInventoryForm(rec){
      const wrap = document.getElementById('inventoryForm'); if(wrap) wrap.style.display = 'block';
      const idField = document.getElementById('inventory_item_id'); if(idField) idField.value = rec.id || '';
      // set item name: prefer matching select option, otherwise show custom input
      const sel = document.getElementById('inventory_item_name');
      const txt = document.getElementById('inventory_item_name_text');
      const toggleBtn = document.getElementById('toggleNewItemBtn');
      const nameVal = (rec.item_name || '') + '';
      if(sel){
        const optExists = Array.from(sel.options).some(o=>String(o.value||o.text||'').trim().toLowerCase() === String(nameVal).trim().toLowerCase());
        if(optExists){ sel.value = nameVal || ''; if(txt){ txt.style.display='none'; txt.value=''; sel.style.display=''; } if(toggleBtn) toggleBtn.textContent='Add'; }
        else { if(txt){ txt.style.display='block'; txt.value = nameVal || ''; sel.style.display='none'; } if(toggleBtn) toggleBtn.textContent='Cancel'; }
      }
      // rest of fields
      const set = (name,val)=>{ const el = document.querySelector('#formInventory [name="'+name+'"]'); if(el) el.value = val || ''; };
      set('quantity', rec.quantity || ''); set('notes', rec.notes || '');
      const saveBtn = document.getElementById('saveInventoryBtn'); if(saveBtn) saveBtn.textContent = 'Update Inventory';
    }

    async function loadInventory(){
      const tbody = document.querySelector('#inventoryTable tbody'); if(!tbody) return;
      tbody.innerHTML = `<tr><td colspan="3" style="color:var(--muted);padding:20px;text-align:center">Loading inventory…</td></tr>`;
      try{
        const res = await fetch('get_inventory.php', { credentials: 'same-origin' });
        if(!res.ok){ tbody.innerHTML = `<tr><td colspan="3" style="color:var(--muted);padding:20px;text-align:center">Failed to load inventory.</td></tr>`; return; }
        const j = await res.json().catch(()=>null);
        if(!j || !j.success || !Array.isArray(j.inventory)){ tbody.innerHTML = `<tr><td colspan="3" style="color:var(--muted);padding:20px;text-align:center">No inventory.</td></tr>`; return; }
        const invMap = {};
        tbody.innerHTML = j.inventory.map(i=>{ const id = i.id || ''; invMap[id] = i; const editBtn = `<button class="btn" data-action="edit-inv" data-id="${escapeHtml(id)}">Edit</button>`; return `<tr data-id="${escapeHtml(id)}"><td>${escapeHtml(i.item_name||'')}</td><td>${escapeHtml(String(i.quantity||''))}</td><td>${escapeHtml(i.notes||'')}</td><td style="white-space:nowrap">${editBtn}</td></tr>`; }).join('');
        document.querySelectorAll('#inventoryTable button[data-action="edit-inv"]').forEach(btn=>{ btn.addEventListener('click',(e)=>{ const id = e.currentTarget.dataset.id; const rec = invMap[id]; if(!rec){ alert('Record not found'); return; } populateInventoryForm(rec); }); });
      }catch(err){ console.error(err); tbody.innerHTML = `<tr><td colspan="3" style="color:var(--muted);padding:20px;text-align:center">Failed to load inventory.</td></tr>`; }
    }

    // initial: load manage appointments and inventory only when needed
    loadManageAppointments();
  </script>
</body>
</html>
