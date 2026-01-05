<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Patient Dashboard — Drea Lying-In Clinic</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

<style>
  /* ========== Base / Theme similar to sign-up ========== */
  :root{
    --lav-1: #f6f2fc;
    --lav-2: #dcd0f9;
    --lav-3: #a48de7;
    --lav-4: #9077d1;
    --text-dark: #2b2450;
    --muted: #6b607f;
    --card-bg: #fff;
    --accent: #9c7de8;
  }

  /* Sidebar dark mode styles (applies when nav.sidebar has class 'sidebar-dark') */
  nav.sidebar.sidebar-dark{
    background: linear-gradient(180deg,#071025,#0b1222);
    box-shadow: none;
    border-radius:14px;
  }
  nav.sidebar.sidebar-dark .nav-item{ color: var(--muted); }
  nav.sidebar.sidebar-dark .nav-item.active{ background: linear-gradient(90deg,#6d28d9,#7c3aed); color:#fff }
  nav.sidebar.sidebar-dark .sidebar-name{ color:#e8eefb }
  nav.sidebar.sidebar-dark .sidebar-footer{ border-top-color: rgba(255,255,255,0.06); }
  nav.sidebar.sidebar-dark .sidebar-avatar{ border-color: rgba(255,255,255,0.08); }
  nav.sidebar.sidebar-dark{ padding-bottom:18px; }
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
  /* Header (top) — updated to use homepage header gradient */
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
    color:#fff;
  }
  .header-left{display:flex;align-items:center;gap:1rem}
  .logo img{width:86px;height:86px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,0.12);box-shadow:0 6px 18px rgba(0,0,0,0.12)}
  .clinic-name{font-family:'Poppins',sans-serif;color:#fff;font-weight:700;font-size:1.35rem}
  .clinic-sub{font-size:0.85rem;color:rgba(255,255,255,0.9);margin-top:4px}
  .header-actions{display:flex;align-items:center;gap:0.5rem;color:#fff}
  .btn-pill{
    background:var(--accent);color:#fff;border:none;padding:10px 18px;border-radius:28px;font-weight:600;cursor:pointer;
    box-shadow:0 6px 18px rgba(0,0,0,0.12);
  }
  .btn-pill.ghost{background:transparent;color:var(--accent);border:1px solid var(--accent);box-shadow:none}
  /* keep header ghost buttons white for contrast against dark header */
  header.site-top .btn-pill.ghost{ color:#fff; border:1px solid rgba(255,255,255,0.12); }
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

  /* Sidebar nav */
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

  /* Avatar upload in sidebar */
  .sidebar-avatar-wrapper{position:relative;width:46px;height:46px}
  .sidebar-avatar-wrapper img{width:46px;height:46px;border-radius:50%;object-fit:cover;border:3px solid rgba(156,125,232,0.06);display:block}
  .avatar-upload-btn{position:absolute;right:-6px;bottom:-6px;background:var(--accent);color:#fff;border-radius:50%;width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border:3px solid #fff;cursor:pointer;box-shadow:0 6px 18px rgba(124,58,237,0.12);font-weight:700}
  .avatar-upload-input{display:none}

  /* Adjust main layout so content clears the fixed sidebar */
  .layout.has-fixed-sidebar{ margin-left: 260px; max-width: calc(100% - 260px); }

  /* Content area */
  main.content-area{flex:1;min-height:600px}
  section.panel{
    background:var(--card-bg);border-radius:12px;padding:20px;box-shadow:0 6px 24px rgba(0,0,0,0.04);margin-bottom:20px;
  }
  .panel h2{color:var(--lav-4);margin-bottom:12px;font-family:'Poppins',sans-serif}
  .muted{color:var(--muted);font-size:0.95rem;margin-bottom:14px}

  /* Profile card (patient info display) */
  .profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .profile-row{display:flex;justify-content:space-between;gap:12px;padding:10px;border-radius:8px;background:#fbf8ff;border:1px solid rgba(156,125,232,0.06)}
  .profile-row b{color:var(--lav-4)}
  .profile-actions{display:flex;gap:10px;margin-top:12px}

  /* Reserve (booking) area */
  .reserve-slot{display:flex;flex-direction:column;gap:12px}
  .services-row{display:flex;flex-wrap:wrap;gap:10px}
  /* Service cards: styled as white cards with green left accent and a Select pill */
  .services-row{display:flex;flex-wrap:wrap;gap:18px}
  .service-btn{
    background:#fff;
    border:1px solid rgba(0,0,0,0.04);
    border-radius:12px;
    padding:22px 22px 20px 40px; /* room for left accent */
    width:100%;
    max-width:340px;
    min-width:240px;
    min-height:280px; /* taller card to fit avatar + button */
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    align-items:stretch;
    position:relative;
    box-shadow:0 8px 30px rgba(0,0,0,0.04);
    cursor:pointer;
    transition:transform .12s ease, box-shadow .12s ease;
    overflow:hidden;
  }
  .service-btn:hover{ transform:translateY(-6px); box-shadow:0 18px 40px rgba(0,0,0,0.06); }
  .service-btn::before{content:'';position:absolute;left:12px;top:14px;bottom:14px;width:6px;border-radius:6px;background:linear-gradient(180deg,var(--accent),var(--lav-4))}
  .service-btn .svc-avatar{width:72px;height:72px;border-radius:50%;object-fit:cover;border:4px solid #fff;margin:0 auto;box-shadow:0 6px 18px rgba(0,0,0,0.06);transform:translateY(-6px)}
  .service-btn .svc-title{font-weight:800;color:var(--lav-4);font-size:1.12rem;margin:6px 0 6px 0;padding-top:0;text-align:center}
  .service-btn .svc-desc{color:var(--muted);font-size:0.98rem;margin:6px 0 8px 0;text-align:center;line-height:1.35;max-height:6.2rem;overflow:hidden}
  .service-btn .svc-readmore{display:block;text-align:center;color:var(--lav-4);font-weight:700;margin-top:6px;font-size:0.92rem}
  /* content area centers title+desc vertically */
  .service-btn .svc-content{display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding-right:12px;padding-left:6px}
  .service-btn .svc-meta{display:flex;align-items:center;justify-content:flex-end;width:100%;gap:8px;padding-top:8px}
  .service-btn .svc-price{font-size:1rem;color:var(--lav-4);font-weight:800;text-align:right;min-width:110px}
  /* full-width select button at bottom */
  .service-btn .select-pill{background:var(--accent);color:#fff;border:none;padding:12px 18px;border-radius:999px;font-weight:800;cursor:pointer;display:block;width:100%;margin-top:14px}
  /* selected card visual */
  .service-btn.selected{
    background:linear-gradient(90deg,var(--accent),var(--lav-4));
    color:#fff;border:none;box-shadow:0 12px 30px rgba(75,44,145,0.18);
  }
  /* ensure text inside selected card is high-contrast and legible */
  .service-btn.selected .svc-title{color:rgba(255,255,255,0.98);font-weight:800}
  .service-btn.selected .svc-desc{color:rgba(255,255,255,0.95);font-weight:600}
  .service-btn.selected .svc-price{color:rgba(255,255,255,0.95);font-weight:800}
  /* keep the left accent visible; slightly brighter when selected */
  .service-btn.selected::before{background:linear-gradient(180deg,var(--accent),var(--lav-4))}
  /* invert select pill on selected card for clear affordance */
  .service-btn.selected .select-pill{background:#fff;color:var(--lav-4);box-shadow:0 6px 18px rgba(0,0,0,0.08)}
  /* calendar root */
  .calendar-root{background:#fff;border-radius:12px;padding:14px;border:1px solid rgba(0,0,0,0.03);margin-top:6px}
  .calendar-controls{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
  .calendar-title{font-weight:700;color:var(--lav-4)}
  .calendar-nav button{background:#f4eefb;border:none;padding:8px 10px;border-radius:8px;cursor:pointer}
  .calendar-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:6px}
  .calendar-weekday{font-size:12px;text-align:center;color:var(--muted)}
  .calendar-day{padding:10px;border-radius:8px;text-align:center;border:1px solid transparent;min-height:46px;display:flex;align-items:center;justify-content:center}
  .calendar-day.disabled{background:#fafafa;color:#b6b0be;cursor:not-allowed}
  .calendar-day.available{background:#fbfffb;border:1px solid #e6f6ea;color:#225e2d;cursor:pointer}
  .calendar-day.selected{background:linear-gradient(90deg,var(--accent),var(--lav-4));color:#fff;box-shadow:0 8px 20px rgba(156,125,232,0.16)}
  .calendar-day:focus{outline:3px solid rgba(156,125,232,0.16);outline-offset:3px}

  /* time slots */
  #timeSlots{display:none;grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));gap:12px;margin-top:12px}
  .time-slot{padding:10px 14px;border-radius:999px;border:1px solid rgba(0,0,0,0.06);background:#fff;cursor:pointer;text-align:center;font-weight:700;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 6px 18px rgba(0,0,0,0.04);transition:all .12s ease}
  .time-slot:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(0,0,0,0.06)}
  .time-slot.selected{background:linear-gradient(90deg,var(--accent),var(--lav-4));color:#fff;border:none;box-shadow:0 12px 30px rgba(75,44,145,0.18)}
  .time-slot.disabled{opacity:0.55;cursor:not-allowed;box-shadow:none;border-color:#eee;background:#fafafa;color:#b6b0be;transform:none}

  .book-now-wrapper{display:flex;justify-content:flex-end;margin-top:12px}
  .book-now-btn{padding:12px 20px;border-radius:28px;background:var(--accent);color:#fff;border:none;font-weight:700;cursor:pointer}
  .book-now-btn:disabled{opacity:0.6;cursor:not-allowed}

  /* Appointments list */
  .appointments-list{display:flex;flex-direction:column;gap:10px}
  .appointment-card{padding:18px 18px 20px 22px;border-radius:12px;background:#fff;border-left:6px solid var(--accent);box-shadow:0 12px 30px rgba(15,30,60,0.06);margin-bottom:18px}
  .appointment-card.booked{border-left-color:var(--accent)}
  .appointment-header{display:flex;justify-content:space-between;align-items:center;gap:8px}
  .appointment-status{padding:6px 10px;border-radius:16px;font-weight:700}
  .appointment-actions{display:flex;gap:8px;margin-top:8px}
  .appointment-action-btn{padding:8px 10px;border-radius:10px;border:none;cursor:pointer;font-weight:600}

  /* Enhanced appointment card layout */
  .appointment-card .appt-avatar{width:72px;height:72px;border-radius:50%;object-fit:cover;margin:-36px auto 8px auto;border:4px solid #fff;box-shadow:0 10px 28px rgba(15,30,60,0.08);background:#fff}
  .appointment-card .appt-title{font-weight:800;color:var(--lav-4);text-align:left;margin:6px 0 14px 0;padding-left:6px}
  .appointment-card .appt-row{display:flex;justify-content:space-between;align-items:center;padding:10px 6px;border-top:1px solid rgba(0,0,0,0.03)}
  .appointment-card .appt-row .label{color:var(--muted);font-weight:700}
  .appointment-card .appt-row .value{font-weight:700;color:var(--text-dark)}
  /* removed purchase details and sales order display per UI request */
  .appointment-card .appt-actions{display:flex;flex-direction:column;gap:10px;margin-top:14px;padding:0 18px}
  .appointment-card .appt-actions .btn-pill{width:160px;margin:0 auto;border-radius:999px;padding:10px 20px}
  .appointment-card .appointment-action-btn.cancel{background:#1583d6;color:#fff;border:none;width:160px;margin:0 auto;border-radius:999px;padding:10px 20px}
  .appointment-card .add-to-calendar{color:var(--lav-4);font-weight:700;text-align:center;display:block;margin-top:10px}

  /* Interaction styles for appointment action buttons */
  .appointment-card .appt-actions .btn-pill,
  .appointment-card .appointment-action-btn.cancel {
    transition: transform .08s ease, box-shadow .12s ease, opacity .08s ease;
  }
  .appointment-card .appt-actions .btn-pill:hover{ transform: translateY(-3px); box-shadow:0 12px 28px rgba(124,58,237,0.12); }
  .appointment-card .appt-actions .btn-pill:active{ transform: translateY(0px) scale(.995); box-shadow:0 6px 14px rgba(124,58,237,0.08); }
  .appointment-card .appointment-action-btn.cancel:hover{ transform: translateY(-2px); box-shadow:0 10px 20px rgba(240,92,92,0.08); }
  .appointment-card .appointment-action-btn.cancel:active{ transform: translateY(0) scale(.995); box-shadow:0 6px 12px rgba(240,92,92,0.06); }
  .appointment-card .appt-actions .btn-pill:focus, .appointment-card .appointment-action-btn.cancel:focus{ outline:3px solid rgba(62,126,246,0.12); outline-offset:3px; }

  .appointment-action-btn.cancel{background:#fff;border:1px solid #f2c0c0;color:#c0392b}
  .appointment-action-btn.reschedule{background:linear-gradient(90deg,var(--accent),var(--lav-4));color:#fff;border:none}

  /* Newborn view (read-only) */
  .newborn-list{display:grid;grid-template-columns:1fr;gap:12px}
  .newborn-card{
    padding:18px 20px;
    border-radius:12px;
    background:#fff;
    border:1px solid rgba(156,125,232,0.06);
    box-shadow:0 8px 30px rgba(124,58,237,0.04);
  }
  .newborn-row{display:flex;gap:8px;margin-top:10px;align-items:center;padding:6px 0;border-bottom:1px solid rgba(156,125,232,0.03)}
  .newborn-row:last-child{border-bottom:none}
  .newborn-label{font-weight:700;color:var(--lav-4);min-width:180px}
  .newborn-value{color:var(--text-dark)}

  /* Medical Records (read-only) */
  .medical-list{display:grid;grid-template-columns:1fr;gap:12px}
  .medical-card{padding:12px;border-radius:8px;background:#fff;border:1px solid rgba(0,0,0,0.04)}
  .medical-row{display:flex;gap:8px;margin-top:6px}
  .medical-label{font-weight:700;color:var(--lav-4);min-width:200px}
  .medical-value{color:var(--muted);}

  /* Prescriptions (read-only) */
  .rx-list{display:grid;grid-template-columns:1fr;gap:12px}
  .rx-card{padding:12px;border-radius:8px;background:#fff;border:1px solid rgba(0,0,0,0.04)}
  .rx-title{font-weight:700;color:var(--lav-4)}
  .rx-meta{font-size:0.9rem;color:var(--muted);margin-top:6px}

  /* Newborn form removed for patients; instruction */
  .note {font-size:0.95rem;color:var(--muted);margin-top:8px}

  /* Newborn section reuse form-grid styles */
  .form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
  label{display:block;font-weight:600;margin-bottom:6px;color:var(--lav-4)}
  /* make form controls inside modal and panels fill available space and look consistent */
  input[type="text"], input[type="email"], input[type="number"], input[type="date"], input[type="time"], select, textarea{
    box-sizing: border-box;
    display: block;
    width: 100%;
    min-width: 0;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #e6e1f6;
    background: #fff;
    font-size: 0.95rem;
    color: var(--text-dark);
    line-height: 1.2;
  }
  textarea{min-height:90px;resize:vertical}

  /* Modal */
  .modal{display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.35);align-items:center;justify-content:center;z-index:9999}
  .modal .dialog{background:#fff;padding:18px;border-radius:12px;max-width:820px;width:94%;box-shadow:0 12px 40px rgba(0,0,0,0.12)}
  .modal .dialog header{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
  .close-btn{background:#fff;border:1px solid #eee;padding:8px 10px;border-radius:8px;cursor:pointer}

/* Make patient form scrollable inside modal */
#formModal .dialog {
  max-height: 90vh; /* limit modal height to viewport */
  overflow-y: auto; /* enables scroll bar only inside form modal */
  scrollbar-width: thin; /* for Firefox */
  scrollbar-color: var(--lav-3) #f0e9ff;
}

/* Optional: style the scrollbar for WebKit browsers */
#formModal .dialog::-webkit-scrollbar {
  width: 8px;
}
#formModal .dialog::-webkit-scrollbar-track {
  background: #f0e9ff;
  border-radius: 10px;
}
#formModal .dialog::-webkit-scrollbar-thumb {
  background: var(--lav-3);
  border-radius: 10px;
}
#formModal .dialog::-webkit-scrollbar-thumb:hover {
  background: var(--lav-4);
}

/* Cancel confirmation modal (custom) */
.confirm-modal{display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;z-index:10050}
.confirm-modal .dialog{background:#fff;padding:18px;border-radius:12px;max-width:520px;width:92%;box-shadow:0 12px 40px rgba(0,0,0,0.18);text-align:left}
.confirm-modal .dialog h4{margin:0 0 8px 0;color:var(--lav-4);font-size:1.05rem}
.confirm-modal .dialog p{color:var(--muted);margin-bottom:18px}
.confirm-modal .dialog .actions{display:flex;gap:12px;justify-content:flex-end}
.confirm-modal .dialog .actions .btn-ok{background:#155d2f;color:#fff;padding:10px 22px;border-radius:999px;border:3px solid rgba(255,255,255,0.08);font-weight:700}
.confirm-modal .dialog .actions .btn-cancel{background:#dff6e6;color:#155d2f;padding:10px 16px;border-radius:999px;border:none;font-weight:700}

/* Payment panel styles */
.payment-root{display:flex;gap:18px;flex-wrap:wrap;align-items:flex-start}
.payment-qr{min-width:240px;background:#fff;padding:16px;border-radius:12px;border:1px solid rgba(0,0,0,0.03);text-align:center}
.payment-qr img{max-width:120px;height:auto;border-radius:8px} /* reduced QR size */
.upload-box{flex:1;min-width:300px;background:#fff;padding:16px;border-radius:12px;border:1px solid rgba(0,0,0,0.03)}
.btn-pill.small{font-size:0.9rem;padding:4px 12px}
.btn-pill.small.delete{background-color:#fff;color:#ff4444;border:1px solid #ffdddd}
.btn-pill.small.delete:hover{background-color:#fff4f4}
#receiptViewModal .dialog{max-width:800px;width:90%}
.prices{background:#fff;border-radius:12px;padding:16px;border:1px solid rgba(0,0,0,0.03);width:100%;max-width:920px}
.prices h4{margin:0 0 8px 0;color:var(--lav-4)}
.prices .muted-small{font-weight:400;color:var(--muted);font-size:0.95rem;margin-bottom:8px}
.prices ul{list-style:none;padding-left:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:8px}
.prices li{padding:10px;border-radius:8px;border:1px solid #f0ecfb;background:#fbf8ff;font-weight:600}
.prices li .desc{display:block;font-weight:400;color:var(--muted);margin-top:6px;font-size:0.95rem}

/* small screens */
  @media(max-width:980px){
    .layout{padding:20px}
    nav.sidebar{position:static;display:none}
    .profile-grid, .form-grid{grid-template-columns:1fr}
    .calendar-grid{grid-template-columns:repeat(7,1fr);font-size:12px}
    .payment-root{flex-direction:column}
  }

  /* NOTE: Theme toggle now toggles sidebar-only dark mode (class 'sidebar-dark' on nav.sidebar) */

  /* Profile dropdown styles */
  .profile-menu .profile-btn{display:inline-flex;align-items:center;gap:8px}
  /* theme popover (Light / Dark / Auto) */
  .theme-popover{position:absolute;right:0;top:44px;min-width:160px;background:#fff;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,0.12);overflow:hidden;z-index:250;border:1px solid rgba(0,0,0,0.06)}
  .theme-popover .tp-item{padding:10px 14px;cursor:pointer;display:flex;align-items:center;justify-content:space-between;color:var(--text-dark);font-weight:700}
  .theme-popover .tp-item .label{display:flex;align-items:center;gap:10px}
  .theme-popover .tp-item:hover{background:#fbf8ff}
  .theme-popover .tp-item .check{color:var(--lav-4);display:none}
  .theme-popover .tp-item.active .check{display:inline-block}

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
</style>
<style>
  /* Responsive patient portal tweaks: laptop / tablet / mobile */
  @media (max-width:1200px){
    /* slightly reduced fonts/padding, hero and CTA sizing, grid gaps */
    body{font-size:15px}
    .layout{padding:20px 22px;gap:16px}
    .clinic-name{font-size:1.15rem}
    .logo img{width:72px;height:72px}
    .layout.has-fixed-sidebar{ margin-left:220px; max-width: calc(100% - 220px); }
    .service-btn{min-height:240px;padding:18px 18px 16px 36px}
    .panel{padding:16px}
    .promo-thumb{width:64px;height:44px}
  }

  @media (max-width:900px){
    /* stack header, reduce hero height, scale logo/text, tighten paddings */
    header.site-top{flex-direction:column;align-items:flex-start;padding:12px 16px;gap:10px}
    .header-left{align-items:center}
    .logo img{width:64px;height:64px}
    .clinic-name{font-size:1rem}
    .clinic-sub{font-size:0.8rem}
    .layout{padding:16px;margin:12px auto;max-width:920px}

    /* make nav full-width, centered and horizontal */
    nav.sidebar{position:static;display:flex;width:100%;flex-direction:row;border-radius:8px;padding:8px 10px;gap:6px;overflow-x:auto;justify-content:center;box-shadow:none;max-width:100%}
    nav.sidebar .nav-item{white-space:nowrap;padding:8px 10px;border-radius:8px;font-size:0.92rem}
    .layout.has-fixed-sidebar{ margin-left:0; max-width:100% }

    /* reduce hero/promo and card sizes */
    #homeCard > div:first-child{padding:14px;font-size:0.95rem}
    .promo-thumb{width:60px;height:40px}
    .service-btn{min-height:200px;max-width:280px}
    .payment-card .qr-wrap{width:140px;height:140px}
    .modal .dialog{max-width:92%}
  }

  @media (max-width:480px){
    /* mobile: further reduce hero height, fonts, buttons and spacing */
    body{font-size:14px}
    header.site-top{padding:10px 12px}
    .logo img{width:52px;height:52px}
    .clinic-name{font-size:0.95rem}
    .header-actions .btn-pill{padding:8px 12px;font-size:0.9rem}
    nav.sidebar{gap:4px;padding:6px}
    nav.sidebar .nav-item{padding:8px 10px;font-size:0.88rem}
    .layout{padding:12px;gap:12px}
    .service-btn{min-height:170px;padding:14px}
    .service-btn .svc-avatar{width:56px;height:56px}
    .book-now-btn{padding:10px 14px;font-size:0.95rem}
    .modal .dialog{width:96%;padding:12px}
    .panel{padding:12px}
    #patientAnnBanner{font-size:0.95rem;padding:10px}
  }
</style>
</head>
<body>

<!-- TOP HEADER -->
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
        <img src="assets/images/logodrea.jpg" alt="avatar" class="profile-avatar" style="width:30px;height:30px;border-radius:50%;object-fit:cover;margin-right:8px;border:2px solid rgba(255,255,255,0.6)">
        <span class="profile-name">Profile</span>
      </button>

      <div class="profile-dropdown" id="profileDropdown" aria-hidden="true">
        <div class="pd-header">
          <img src="assets/images/logodrea.jpg" id="hdrAvatarSmall" alt="avatar">
          <div style="flex:1">
            <div class="pd-name" id="hdrName">Profile</div>
            <div class="pd-sub">Manage your account</div>
          </div>
        </div>
        <div class="pd-sep"></div>

        <div class="pd-group">
          <button class="pd-item" type="button" onclick="openFormModal('edit'); toggleProfileDropdown(false)">
            <span class="icon">⚙️</span>
            <span>Customize Profile</span>
          </button>

          <a class="pd-item" href="patient_info.php" role="button" onclick="toggleProfileDropdown(false)">
            <span class="icon">👤</span>
            <span>Patient Info</span>
          </a>

          <a class="pd-item" href="change_password.php" role="button" onclick="toggleProfileDropdown(false)">
            <span class="icon">🔒</span>
            <span>Change Password</span>
          </a>
        </div>

        <div class="pd-sep"></div>

        <div class="pd-group">
          <form action="logout.php" method="POST" style="margin:0">
            <button type="submit" class="pd-item logout" style="width:100%;text-align:left;border:none;background:transparent;cursor:pointer;">
              <span class="icon">⎋</span>
              <span>Log Out</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>

<div class="layout">
  <!-- NAV -->
  <nav class="sidebar" aria-label="Main navigation">
    <a class="nav-item" href="patient_home.php">Home</a>
    <a class="nav-item" href="patient_reserve.php">Book Appointment</a>
    <a class="nav-item" href="#" data-panel="photoshoot" onclick="switchPanel('photoshoot')">Free Photoshoot</a>
    <a class="nav-item" href="patient_appointments.php">My Appointments</a>
    <a class="nav-item" href="patient_medical.php">Medical Records</a>
    <a class="nav-item" href="patient_prescriptions.php">Prescriptions</a>
    <a class="nav-item" href="patient_newborn.php">Newborn's Record</a>
    <a class="nav-item" href="patient_payment.php">Payment</a>
    <a class="nav-item" href="patient_soa.php">Statement of Account</a>

    <div class="sidebar-footer" id="sidebarFooter">
      <div class="sidebar-avatar-wrapper" style="position:relative;display:flex;align-items:center;gap:10px">
        <div style="position:relative;display:inline-block">
          <img src="assets/images/logodrea.jpg" alt="avatar" class="sidebar-avatar" id="sidebarAvatar" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid rgba(0,0,0,0.06)">
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

  <!-- MAIN -->

        <!-- Photoshoot gallery modal -->
        <div id="photoshootModal" class="modal" aria-hidden="true" style="display:none">
          <div class="dialog" role="dialog" aria-modal="true" style="max-width:960px;width:94%;text-align:center">
            <header>
              <h3 style="margin:0;color:var(--lav-4)">Photoshoot</h3>
              <button class="close-btn" onclick="closePhotoshootModal()">Close</button>
            </header>
            <div class="content" style="position:relative;display:flex;align-items:center;justify-content:center;padding:12px">
              <button class="close-btn" style="position:absolute;left:8px;top:50%;transform:translateY(-50%);font-size:28px;background:transparent;border:0;color:var(--lav-4)" onclick="prevPhotoshootImage()">‹</button>
              <img id="photoshootImg" src="" alt="Photoshoot" style="max-width:100%;max-height:70vh;border-radius:8px">
              <button class="close-btn" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);font-size:28px;background:transparent;border:0;color:var(--lav-4)" onclick="nextPhotoshootImage()">›</button>
            </div>
          </div>
        </div>
  <main class="content-area">

    <!-- HOME PANEL (replaces previous Profile / Patient Info) -->
    <section id="panel-profile" class="panel panel-home" tabindex="-1">
      <h2>Home</h2>
      <p class="muted">Your quick summary and next steps.</p>

      <!-- Announcement banner for patients (populated from admin_announcements.php) -->
      <div id="patientAnnBanner" style="display:none;border-radius:8px;padding:12px;margin-bottom:12px;background:#fff8ff;border:1px solid #f0e7ff;color:var(--lav-4)"></div>

      <div id="homeCard" style="display:grid;gap:10px;align-items:start">
        <div style="background:linear-gradient(90deg,var(--accent),var(--lav-4));color:#fff;padding:18px;border-radius:12px;box-shadow:0 12px 30px rgba(124,58,237,0.08)">
          <div id="homeGreeting" style="font-size:1.2rem;font-weight:800">👋 Welcome back, <span id="homeName">Patient</span>!</div>
          <!-- Pregnancy summary hidden (kept for JS compatibility) -->
          <div id="homePregnancy" style="display:none;margin-top:8px;font-weight:600">You are <span id="homeWeeks">-</span> weeks pregnant (<span id="homeTrimester">-</span>)</div>
          <div id="homeNextCheck" style="display:none;margin-top:6px;color:rgba(255,255,255,0.95)">Next check-up: <span id="homeNextCheckDate">Not scheduled</span></div>

          <!-- Maternity Care Tips (replaces visible pregnancy info) -->
          <div id="maternityTips" style="margin-top:12px;background:rgba(255,255,255,0.08);padding:12px;border-radius:8px;color:#fff"> 
            <div style="font-weight:700;margin-bottom:8px">Maternity Care Tips</div>
            <ul style="margin:0 0 0 18px;line-height:1.6">
              <li>Attend all prenatal and postnatal checkups.</li>
              <li>Take prescribed vitamins and medications only.</li>
              <li>Eat healthy, balanced meals and drink enough water.</li>
              <li>Get adequate rest and manage stress.</li>
              <li>Avoid smoking, alcohol, and drugs.</li>
              <li>Perform light exercise if approved by a healthcare provider.</li>
            </ul>
          </div>
        </div>

        <!-- Home action buttons removed as requested -->
        
        <!-- Lavender promo card: Complimentary services on 2nd midwife follow-up -->
        <div style="margin-top:12px">
          <div style="background:linear-gradient(180deg,#f7f3ff,#fbf8ff);border-radius:12px;padding:18px;border:1px solid rgba(156,125,232,0.06);box-shadow:0 8px 22px rgba(124,58,237,0.06);display:flex;gap:16px;align-items:center">
            <div style="flex:1">
              <div style="display:inline-block;background:rgba(123,70,255,0.08);color:var(--lav-4);padding:6px 10px;border-radius:999px;font-size:0.85rem;font-weight:700;margin-bottom:8px">Good News for Expecting Moms</div>
              <h3 style="margin:6px 0 8px 0;color:#2b2240;font-size:1.05rem">FREE PHOTOSHOOT (SOFT COPY + LAMINATED HARD COPY)</h3>
              <p class="muted" style="margin:0 0 10px 0">Get a complimentary photoshoot (soft copy and laminated hard copy) when you attend your 2nd midwife follow-up.</p>
              <ul style="margin:8px 0 12px 18px;color:#2b2240">
                <li>✓ <strong>Free</strong> 2nd follow-up check-up with the <strong>midwife</strong></li>
                <li>✓ <strong>Free photoshoot</strong> — soft copy + laminated hard copy</li>
                <li>✓ <strong>OB follow-up:</strong> ₱350 only</li>
              </ul>
              <!-- Book Midwife Follow-Up button removed -->
              <div style="margin-top:8px;font-size:0.9rem;color:var(--muted)">Tip: Show this page or your appointment confirmation at the front desk to avail the free photoshoot.</div>
            </div>
            <div style="flex:0 0 260px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px">
              <div style="width:240px;height:160px;border-radius:8px;overflow:hidden;border:1px solid rgba(0,0,0,0.04)">
                <img id="promoMainImg" src="assets/images/pic3.jpg" alt="Photoshoot Sample" style="width:100%;height:100%;object-fit:cover;cursor:pointer" onclick="openPhotoshootModal(2)">
              </div>
              <div style="display:flex;gap:8px;margin-top:4px">
                <img class="promo-thumb" src="assets/images/pic1.jpg" alt="Sample 1" style="width:72px;height:48px;object-fit:cover;border-radius:6px;cursor:pointer;border:1px solid rgba(0,0,0,0.04)" onclick="openPhotoshootModal(0)">
                <img class="promo-thumb" src="assets/images/pic2.jpg" alt="Sample 2" style="width:72px;height:48px;object-fit:cover;border-radius:6px;cursor:pointer;border:1px solid rgba(0,0,0,0.04)" onclick="openPhotoshootModal(1)">
                <img class="promo-thumb" src="assets/images/pic3.jpg" alt="Sample 3" style="width:72px;height:48px;object-fit:cover;border-radius:6px;cursor:pointer;border:1px solid rgba(0,0,0,0.04)" onclick="openPhotoshootModal(2)">
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- PATIENT INFO PANEL (restored, accessible from profile dropdown) -->
    <section id="panel-patientinfo" class="panel" hidden>
      <h2>Patient Info</h2>
      <p class="muted">Your detailed profile information.</p>

      <div class="profile-grid">
        <div class="profile-row"><div><b>Full name</b></div><div id="profile-name">-</div></div>
        <div class="profile-row"><div><b>Age</b></div><div id="profile-age">-</div></div>
        <div class="profile-row"><div><b>Address</b></div><div id="profile-address">-</div></div>
        <div class="profile-row"><div><b>Birthday</b></div><div id="profile-bday">-</div></div>
        <div class="profile-row"><div><b>Contact Number</b></div><div id="profile-mobile">-</div></div>
        <div class="profile-row"><div><b>Civil Status</b></div><div id="profile-status">-</div></div>
        <div class="profile-row"><div><b>Nationality</b></div><div id="profile-nationality">-</div></div>
        <div class="profile-row"><div><b>Email</b></div><div id="profile-email">-</div></div>
        <div class="profile-row"><div><b>Religion</b></div><div id="profile-religion">-</div></div>
        <div class="profile-row"><div><b>Blood Type</b></div><div id="profile-blood">-</div></div>
        <div class="profile-row"><div><b>Allergies</b></div><div id="profile-allergies">-</div></div>
        <div class="profile-row"><div><b>Past Medical Conditions</b></div><div id="profile-medical">-</div></div>
        <div class="profile-row"><div><b>Current Medications</b></div><div id="profile-medications">-</div></div>
        <div class="profile-row"><div><b>Obstetric History</b></div><div id="profile-obstetric">-</div></div>
        <div class="profile-row"><div><b>Number of Pregnancies</b></div><div id="profile-pregnancies">-</div></div>
        <div class="profile-row"><div><b>Number of Deliveries</b></div><div id="profile-deliveries">-</div></div>
        <div class="profile-row"><div><b>Last Menstrual Period</b></div><div id="profile-lmp">-</div></div>
        <div class="profile-row"><div><b>Expected Delivery Date</b></div><div id="profile-edd">-</div></div>
        <div class="profile-row"><div><b>Previous Pregnancy Complication</b></div><div id="profile-complications">-</div></div>
      </div>

      <div class="profile-actions">
        <button class="btn-pill" onclick="openFormModal('edit')">Edit Profile</button>
        <button class="btn-pill ghost" onclick="switchPanel('profile')">Back to Home</button>
      </div>
    </section>

    <!-- FREE PHOTOSHOOT PANEL -->
    <section id="panel-photoshoot" class="panel" hidden>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap">
        <div style="flex:1;min-width:280px">
          <h2 style="display:flex;align-items:center;gap:10px">
            <span style="font-size:1.1rem">📷</span>
            <span>Free Maternity Photoshoot</span>
          </h2>
          <p class="muted">We celebrate your pregnancy journey with a complimentary photoshoot.</p>
        </div>
        <div style="flex:0 0 auto">
          <div style="background:#f3eaff;color:var(--lav-4);padding:6px 10px;border-radius:999px;font-weight:700;font-size:0.8rem">Limited Offer</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:18px">
        <div style="background:#fff;border-radius:10px;padding:14px;border:1px solid rgba(156,125,232,0.06)">
          <h4 style="margin:0 0 8px 0;color:var(--lav-4)">What's Included</h4>
          <ul style="margin-top:8px;line-height:1.6;color:var(--text-dark)">
            <li>✅ Free photoshoot session</li>
            <li>✅ Soft copy (digital photos)</li>
            <li>✅ 1 laminated hard copy photo</li>
            <li>✅ Professional maternity theme</li>
          </ul>
        </div>

        <div style="background:#fff;border-radius:10px;padding:14px;border:1px solid rgba(156,125,232,0.06)">
          <h4 style="margin:0 0 8px 0;color:var(--lav-4)">When Can You Avail?</h4>
          <ul style="margin-top:8px;line-height:1.6;color:var(--text-dark)">
            <li>✔ During your 2nd follow-up check-up</li>
            <li>✔ With the Midwife — FREE</li>
            <li>✔ With OB follow-up — ₱350 only</li>
          </ul>
        </div>

        <div style="background:#fff;border-radius:10px;padding:14px;border:1px solid rgba(156,125,232,0.06)">
          <h4 style="margin:0 0 8px 0;color:var(--lav-4)">Important Notes</h4>
          <ul style="margin-top:8px;line-height:1.6;color:var(--text-dark)">
            <li>📌 Appointment required</li>
            <li>📌 Bring your maternity booklet</li>
            <li>📌 Photos released after 3–5 days</li>
          </ul>
        </div>
      </div>

      <div style="display:flex;gap:12px;margin-top:18px">
        <button id="bookPhotoshootBtn" class="btn-pill gift-btn" type="button" onclick="bookPhotoshootHandler()">Click Your Gift</button>
        <button id="viewSamplePhotosBtn" class="btn-pill ghost" type="button" onclick="openPhotoshootModal(0)">View Sample Photos</button>
      </div>

      <div style="margin-top:12px;font-size:0.92rem;color:var(--muted);font-style:italic">*This promo is exclusive to registered maternity patients of the clinic.</div>
    </section>

    <!-- Photoshoot uploads placeholder moved into Newborn panel so it appears below screenings when a newborn is selected -->

    <style>
      /* Gift-wrapped photoshoot button */
      .gift-btn{
        position:relative;
        background:linear-gradient(180deg,#7c3aed,#6b21a8);
        color:#fff;padding:12px 20px;border-radius:12px;font-weight:800;border:none;cursor:pointer;box-shadow:0 10px 30px rgba(123,63,232,0.14);
      }
      .gift-btn:before, .gift-btn:after{
        content:'';position:absolute;left:50%;transform:translateX(-50%);pointer-events:none;}
      .gift-btn:before{ /* ribbon horizontal */
        top:10px;width:120%;height:10px;background:rgba(255,255,255,0.12);border-radius:6px;}
      .gift-btn:after{ /* knot */
        top:-6px;width:18px;height:18px;background:#fff;border-radius:50%;box-shadow:inset 0 -3px 6px rgba(0,0,0,0.08);}
      .gift-btn.booked{background:linear-gradient(180deg,#2b6b2b,#1f4e1f);}
    </style>

    <script>
      async function bookPhotoshootHandler(){
        const btn = document.getElementById('bookPhotoshootBtn');
        if(!btn || btn.disabled) return;
        try{
          btn.disabled = true; btn.classList.add('booked'); btn.textContent = 'Gift Claimed — Loading photos';
          let pid = (window.patientDetails && (window.patientDetails.user_id || window.patientDetails.id || window.patientDetails.patient_user_id || window.patientDetails.patient_id)) || null;
          if(!pid){ const pd = await loadPatientDetails().catch(()=>null); if(pd) pid = pd.user_id || pd.id || pd.patient_user_id || pd.patient_id || null; }
          if(pid){ await loadPatientPhotos(pid); }
          const placeholder = document.getElementById('photoshootUploadsPlaceholder');
          if(placeholder){ placeholder.style.display = 'block'; placeholder.scrollIntoView({behavior:'smooth', block:'center'}); }
          showToast('Photoshoot for your baby is free. If photos are available they are now shown.', 'success', 4500);
          btn.textContent = 'Gift Claimed';
        }catch(e){ console.error('bookPhotoshootHandler error', e); showToast('Could not book photoshoot right now', 'error'); btn.disabled = false; btn.classList.remove('booked'); btn.textContent = 'Click Your Gift'; }
      }
    </script>

    <!-- RESERVE PANEL -->
    <section id="panel-reserve" class="panel" hidden>
      <h2>Book Appointment</h2>
      <p class="muted">Choose the checkup type, pick an available date, then select a time slot.</p>

      <div class="reserve-slot">
        <div class="services-row" role="list">
          <!-- list of service buttons (Laboratory added) -->
          <!-- NST service removed per request -->
          <button class="service-btn" data-service="Midwife Checkup">Midwife Checkup</button>
          <button class="service-btn" data-service="OB-GYN Consultation">OB-GYN Consultation</button>
          <button class="service-btn" data-service="Pedia Checkup">Pedia Checkup</button>
          <button class="service-btn" data-service="Ultrasound">Ultrasound</button>
          <!-- 'Trans V. Ultrasound' service removed per request -->
          <!-- 'Pelvic' and 'BPS' services removed per request -->
          <button class="service-btn" data-service="Ear Piercing and Pregnancy Test">Ear Piercing / Pregnancy Test</button>
          <button class="service-btn" data-service="Newborn Screening">Newborn Screening</button>
          <button class="service-btn" data-service="Family Planning">Family Planning</button>
          <button class="service-btn" data-service="Laboratory">Laboratory</button>
        </div>

        <!-- Calendar Root (injected next to clicked service) -->
        <div id="calendarRoot" class="calendar-root" style="display:none">
          <div class="calendar-controls">
            <div class="calendar-title">Month Year</div>
            <div class="calendar-nav">
              <button type="button" onclick="previousMonth()">◀</button>
              <button type="button" onclick="nextMonth()">▶</button>
            </div>
          </div>
          <div id="calendarGrid" class="calendar-grid"></div>
        </div>

        <!-- Time slots -->
        <div id="timeSlots" style="display:none" class="calendar-root">
          <h3 style="margin:0 0 8px 0;color:var(--lav-4)">Available start times</h3>
          <div id="timeSlotGrid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px"></div>
        </div>

        <div class="book-now-wrapper">
          <button class="book-now-btn" disabled>Request Appointment</button>
        </div>
      </div>
    </section>

    <!-- APPOINTMENTS PANEL (Delete option removed) -->
    <section id="panel-appointments" class="panel" hidden>
      <h2>My Appointments</h2>
      <p class="muted">Your scheduled appointments appear below. You may cancel or reschedule necessary appointments.</p>

      <div style="display:flex;gap:10px;align-items:center;margin-bottom:12px">
        <label style="font-weight:600;color:var(--muted)">Filter:</label>
        <select id="statusFilter" onchange="filterAppointments()" style="padding:8px;border-radius:8px;border:1px solid #eee">
          <option value="all">All</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="cancelled">Cancelled</option>
          <option value="completed">Completed</option>
        </select>
      </div>

      <div id="appointmentsList" class="appointments-list">
        <!-- loaded by JS -->
      </div>
    </section>
    

    <!-- MEDICAL RECORDS PANEL (read-only) -->
    <section id="panel-medical" class="panel" hidden>
      <h2>Medical Records</h2>
      <p class="muted">This area shows clinical measurements and pregnancy information recorded by your midwife or doctor. These entries are view-only.</p>

      <style>
          /* Use a single flexible column so the records table fills the available panel width
            (the right preview column was removed earlier and left empty). */
          .medical-root{ display:grid; grid-template-columns: 1fr; gap:18px; align-items:start }
        .medical-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:16px }
        .filter-group{ display:flex; align-items:center; gap:8px }
        .filter-label{ color:var(--muted); font-size:0.95rem }
        .filter-select{ padding:6px 12px; border-radius:8px; border:1px solid rgba(0,0,0,0.1); min-width:120px; font-size:0.95rem }
        .filter-select:hover{ border-color:rgba(124,58,237,0.5) }
        .medical-list-box{ background:#fff;border-radius:12px;padding:12px;border:1px solid rgba(0,0,0,0.04); box-shadow:0 6px 20px rgba(20,8,40,0.03)}
        .medical-table{ width:100%; border-collapse:separate; border-spacing:0 10px; font-size:0.95rem }
        .medical-table th, .medical-table td{ padding:14px 18px; text-align:left; border-bottom:none; vertical-align:middle }
        .medical-table tbody tr{ background:transparent; transition:transform 0.12s ease, box-shadow 0.12s ease }
        .medical-table tbody tr:hover{ transform:translateY(-4px) }
        .medical-table th{ font-weight:700; color:var(--muted); font-size:0.9rem }
        /* render each row as a soft card using shadow on the row container */
          /* Use traditional table layout for medical records to match doctor portal results appearance */
          .medical-table tbody tr{ background:#fff }
          .medical-table tbody tr:nth-child(odd){ background:rgba(124,58,237,0.03) }
          .medical-table tbody tr:hover{ background:rgba(124,58,237,0.04) }
          .medical-table tbody tr td{ background:transparent; padding:12px 14px; vertical-align:middle; border-bottom:1px solid rgba(0,0,0,0.06); }
          .medical-table tbody tr td:first-child{ color:var(--muted); font-weight:600; width:140px; }
          .medical-table tbody tr td:nth-child(2){ font-weight:700; color:var(--lav-4); min-width:160px }
          .medical-table tbody tr td:nth-child(3){ min-width:220px; text-align:left }
          .medical-table tbody tr td:nth-child(4){ min-width:260px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap }
          .medical-table tbody tr td:nth-child(5){ width:140px; text-align:right; white-space:nowrap }

          /* Keep header as table row for correct alignment */
          .medical-table thead tr{ display:table-row }
          @media(max-width:980px){
            .medical-table thead th, .medical-table tbody td { padding:10px; }
            .medical-table tbody tr td:nth-child(4){ white-space:normal }
          }
        .note-badge{ display:inline-block; margin-left:10px; background:linear-gradient(90deg,var(--accent),var(--lav-4)); color:#fff; padding:8px 12px; border-radius:999px; font-weight:800; font-size:0.88rem; box-shadow:0 6px 18px rgba(124,58,237,0.08) }
        .medical-table tr:hover{ background:linear-gradient(90deg, rgba(124,58,237,0.03), rgba(124,58,237,0.02)) }
        .medical-table{ width:100%; border-collapse:collapse; }
        .medical-table th, .medical-table td{ padding:12px 14px; border-bottom:1px solid rgba(0,0,0,0.06); text-align:left; vertical-align:top }
        .medical-table thead tr{ display:table-row; padding:0 }
        .medical-table thead th{ font-weight:700; background:#f9f6ff; color:var(--lav-4); padding:12px 14px; text-align:left }
        .medical-table thead th:first-child{ width:140px }
        .medical-table thead th:nth-child(2){ min-width:160px }
        .medical-table thead th:nth-child(3){ width:220px; text-align:left }
        .medical-table thead th:nth-child(4){ min-width:260px }
        .medical-table thead th:nth-child(5){ width:140px; text-align:right }
        .medical-preview{ background:#fff;border-radius:12px;padding:12px;border:1px solid rgba(0,0,0,0.04); min-height:360px; display:flex; flex-direction:column }
        .medical-preview .meta{ display:flex; justify-content:space-between; align-items:center; gap:12px }
        .medical-preview .preview-frame{ margin-top:12px; flex:1; border-radius:8px; overflow:hidden; border:1px solid rgba(0,0,0,0.06) }
        .mr-actions button{ background:transparent;border:0;padding:6px 8px;border-radius:8px; cursor:pointer }
        .mr-actions .icon{ font-weight:700; color:var(--lav-4) }
        /* action buttons inside the medical list (view/download) */
        .medical-table .action-btn, .medical-list-box .btn-pill.small{ background: linear-gradient(90deg,var(--accent),var(--lav-4)); color:#fff; padding:8px 12px; border-radius:999px; border:none; box-shadow:0 8px 18px rgba(124,58,237,0.08); cursor:pointer; font-weight:700 }
        .medical-table .action-btn svg{ vertical-align:middle }
        .medical-list-box .btn-pill.small.delete{ background:#fff;color:#ff4444;border:1px solid #ffdddd }
        .medical-empty{ color:var(--muted); padding:20px }
        @media(max-width:980px){ .medical-root{ grid-template-columns:1fr; } .medical-preview{ order:2 } }
      </style>

      <div class="medical-root">
        <div class="medical-list-box">
          <div class="medical-header">
            <div style="font-weight:700">Records</div>
          </div>
          <div style="overflow:auto;max-height:520px">
            <table class="medical-table" id="medicalRecordsTable" aria-live="polite">
              <thead>
                <tr>
                  <th style="width:140px">Date</th>
                  <th style="min-width:160px">Patient</th>
                  <th style="min-width:260px">Notes</th>
                  <th style="min-width:140px">File</th>
                </tr>
              </thead>
              <tbody id="medicalRecordsTableBody">
                <!-- rows inserted here (now sourced from results uploaded by clinic staff) -->
              </tbody>
            </table>

            <!-- Patient-facing inline lab results removed (now shown in main records table) -->
          </div>
        </div>

        <!-- Preview removed per request (patient_portal) -->
      </div>

      <div id="noMedicalMessage" class="muted" style="display:none;margin-top:12px">No medical records found.</div>
    </section>

    <!-- PRESCRIPTIONS PANEL (read-only) -->
    <section id="panel-prescriptions" class="panel" hidden>
      <h2>Prescriptions</h2>
      <p class="muted">Prescriptions written by clinic staff appear below. Follow the directions and contact the clinic if you have questions.</p>

      <div class="rx-table-wrapper" style="overflow-x:auto;background:#fff;border-radius:12px;padding:24px;margin-top:24px;max-width:100%">
        <table class="rx-table" style="width:100%;border-collapse:collapse">
          <thead>
            <tr>
              <th style="width:120px">Date</th>
              <th style="width:140px">File</th>
            </tr>
          </thead>
          <tbody id="prescriptionsTableBody">
            <!-- rows inserted by JS -->
          </tbody>
        </table>
      </div>
      <div id="noRxMessage" class="muted" style="display:none;margin-top:12px">No prescriptions found.</div>

      <style>
        .rx-table-wrapper{ background:linear-gradient(180deg,#fff,#fbf8ff); padding:18px; border-radius:14px; box-shadow:0 12px 36px rgba(124,58,237,0.04); }
        .rx-table { background:transparent }
        .rx-table th, .rx-table td { padding:16px 18px; text-align:left; border-bottom:1px solid rgba(156,125,232,0.06); overflow:hidden; text-overflow:ellipsis }
        .rx-table th { font-weight:600; color:var(--lav-4); font-size:0.95rem; position:sticky; top:0; background:transparent }
        .rx-table tbody tr { background:#fff; transition:background-color 0.18s }
        .rx-table tbody tr:nth-child(odd) { background:rgba(124,58,237,0.03) }
        .rx-table tbody tr:hover { background:rgba(124,58,237,0.04) }
        .rx-table tbody td { color:var(--text-dark); font-size:0.95rem }
        .rx-table td:first-child { font-weight:600; color:var(--lav-4) }
        .rx-table td.frequency { white-space:normal; line-height:1.5 }
        @media(max-width:980px){ .rx-table th, .rx-table td { padding:12px } }
      </style>
    </section>

    <!-- NEWBORN PANEL (read-only for patients) -->
    <section id="panel-newborn" class="panel" hidden>
      <h2>Newborn's Record</h2>
      <p class="muted">This section shows newborn records uploaded by clinic staff (midwife). You cannot edit these entries from your patient account. If something is missing, please contact the clinic or your midwife.</p>

      <div id="newbornRecordsContainer">
        <div id="newbornRecordsList" class="newborn-list">
          <!-- newborn cards inserted here by JS -->
        </div>
        <div id="noNewbornsMessage" class="muted" style="display:none;margin-top:12px">No newborn records found.</div>
      </div>

      <!-- Patient-facing Newborn Screenings table -->
      <div id="patientScreeningsContainer" style="margin-top:18px">
        <h3 style="margin:0 0 8px 0;color:var(--lav-4)">Newborn Screenings</h3>
        <p class="muted">View screening records performed for your newborns.</p>
        <div class="rx-table-wrapper" style="overflow:auto;max-height:320px;border-radius:12px;padding:12px;border:1px solid rgba(0,0,0,0.04);background:transparent">
          <table class="rx-table" id="patientScreeningsTable" style="width:100%;border-collapse:collapse">
            <thead>
              <tr>
                  <th style="text-align:left;min-width:180px">Mother</th>
                  <th style="width:140px;text-align:center">Result</th>
                  <th style="width:140px;text-align:center">File</th>
                  <th style="width:120px;text-align:center">Summary</th>
                </tr>
            </thead>
            <tbody id="patientScreeningsTableBody">
              <!-- rows injected by JS -->
            </tbody>
          </table>
          <div id="noScreeningsMessage" class="muted" style="display:none;margin-top:12px">No screening records found.</div>
        </div>
      </div>

      <!-- Photoshoot uploads table (appears when a newborn is selected) -->
      <div id="photoshootUploadsPlaceholder" style="margin-top:12px;background:#fff;border-radius:10px;padding:12px;border:1px solid rgba(0,0,0,0.04);display:none">
        <div class="photoshoot-heading" style="font-weight:700;color:var(--lav-4);margin-bottom:8px">Photoshoot Uploads</div>
        <div class="muted" style="margin-bottom:8px">Photos uploaded by clinic staff for your photoshoot sessions will appear here.</div>
        <div style="overflow:auto;max-height:240px">
          <table style="width:100%;border-collapse:collapse" id="photoshootUploadsPlaceholderTable">
            <thead>
              <tr>
                <th style="text-align:left;min-width:180px">File Name</th>
                <th style="text-align:left;min-width:120px">Date Uploaded</th>
                <th style="width:120px;text-align:center">View</th>
                <th style="width:120px;text-align:center">Download</th>
              </tr>
            </thead>
            <tbody id="photoshootUploadsPlaceholderBody">
              <tr><td colspan="4" class="muted" style="padding:12px">No photos uploaded yet.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div id="photoshootDebug" style="display:none;background:#fff;border-radius:8px;padding:12px;margin-top:12px;border:1px dashed rgba(0,0,0,0.06);white-space:pre-wrap;font-family:monospace;font-size:12px;color:#333"></div>

      <!-- Midwife photos: displayed below the Newborn Screenings table -->
      <div id="midwifePhotosBelowScreenings" style="margin-top:14px;display:none">
        <h3 style="margin:0 0 8px 0;color:var(--lav-4);font-size:1rem">Photos uploaded by your midwife</h3>
        <div class="muted" style="margin-bottom:8px">These images were uploaded by clinic staff for your newborns.</div>
        <div id="midwifePhotosContainer" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start"></div>
      </div>

      <!-- Note removed per request: midwives/admin instruction omitted from patient-facing view -->
    </section>
    <!-- STATEMENT OF ACCOUNT (SOA) PANEL -->
    <section id="panel-soa" class="panel" hidden>
      <h2>Statement of Account</h2>
      <p class="muted">Your invoice and payment history. Use <strong>Upload Receipt</strong> to attach payment proof.</p>

      <div style="background:#fff;border-radius:12px;padding:18px;border:1px solid rgba(0,0,0,0.04);box-shadow:0 8px 26px rgba(20,8,40,0.03)">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px">
          <div>
              <div style="display:flex;align-items:center;gap:12px">
              <div>
                <img src="assets/images/logodrea.jpg" alt="Drea Lying-In Clinic" style="width:56px;height:56px;border-radius:10px;object-fit:cover;border:1px solid rgba(0,0,0,0.04)">
              </div>
              <div>
                <div style="font-weight:800">Drea Lying-In Clinic</div>
                <div class="muted">160 Brgy. Biga, Tanza, Cavite · 09512185146 · drealyinginclinic@gmail.com</div>
              </div>
            </div>
          </div>

          <div style="text-align:right">
            <div style="font-size:0.9rem;color:var(--muted)">SOA · Invoice <span id="soaNumber">#—</span></div>
            <div style="font-weight:800;font-size:1.15rem;margin-top:6px" id="soaStatus">Unpaid</div>
            <div class="muted" style="margin-top:6px;font-size:0.95rem">Date Issued: <span id="soaIssued">-</span><br>Due Date: <span id="soaDue">-</span></div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr;gap:18px;margin-top:18px">
          <div>
            <div style="font-weight:700;margin-bottom:8px">Patient:</div>
            <div id="soaPatientInfo" class="muted-small">Loading patient info…</div>

            <div style="margin-top:12px;overflow:auto;background:transparent;border-radius:8px">
              <table style="width:100%;border-collapse:collapse;font-size:0.95rem" id="soaItemsTable">
                <thead>
                  <tr style="text-align:left;color:var(--muted);background:transparent">
                    <th style="padding:8px 10px">Description</th>
                    <th style="width:80px;padding:8px 10px">Qty</th>
                    <th style="width:120px;padding:8px 10px;text-align:right">Amount</th>
                    <th style="width:120px;padding:8px 10px;text-align:right">Paid</th>
                    <th style="width:120px;padding:8px 10px;text-align:right">Balance</th>
                  </tr>
                </thead>
                <tbody id="soaItemsBody">
                  <tr><td colspan="5" class="muted" style="padding:12px">Loading payment history…</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div style="background:linear-gradient(180deg,#fbf6ff,#f6f0ff);border-radius:12px;padding:14px;border:1px solid rgba(156,125,232,0.04)">
            <div style="font-weight:700;color:var(--lav-4)">Amount due breakdown</div>
            <div style="margin-top:12px;display:flex;justify-content:space-between"><div>Subtotal</div><div id="soaSubtotal">₱0.00</div></div>
            <div style="margin-top:6px;display:flex;justify-content:space-between"><div>Discount</div><div id="soaDiscount">₱0.00</div></div>
            <!-- Tax removed per request -->
            <div style="margin-top:6px;display:flex;justify-content:space-between"><div>Payments</div><div id="soaPayments">₱0.00</div></div>
            <div style="margin-top:12px;border-top:1px dashed rgba(0,0,0,0.06);padding-top:12px;display:flex;justify-content:space-between;font-weight:800;font-size:1.15rem">
              <div>Total Due</div><div id="soaTotalDue">₱0.00</div>
            </div>

            <!-- SOA payment buttons removed from patient UI per request -->
            <!-- Download PDF button removed -->
          </div>
        </div>
      </div>
    </section>

    <!-- PAYMENT PANEL (Service Prices first, GCASH QR below prices) -->
    <section id="panel-payment" class="panel" hidden>
      <h2>Payment</h2>
      <p class="muted">Below are our service prices. If you will use PhilHealth, please bring your PhilHealth ID/documents to the clinic for verification. After paying via GCASH, upload your payment receipt so we can confirm it.</p>

      <div class="payment-root" style="flex-direction:column;gap:14px">
          <div class="prices" aria-live="polite">
            <h4>Service Prices</h4>
            <div class="muted-small">If using PhilHealth, please bring your documents to the clinic for further processing. Thank you — God bless.</div>

            <div id="servicesContainer">
              <div class="service-group">
                <h4 style="margin-top:12px">Delivery</h4>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;align-items:start">
                  <div class="service-card" data-name="Normal spontaneous delivery (with PhilHealth, midwife handled)" data-price="1500-3500" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">Normal spontaneous delivery (with PhilHealth, midwife handled) — <span style="font-weight:800;color:var(--lav-4)">₱1500.00</span></div>
                    <div class="muted" style="margin-top:8px">PhilHealth + midwife-managed pricing; estimate ₱1,500 to ₱3,500. Bring PhilHealth documents for processing.</div>
                  </div>

                  <div class="service-card" data-name="Normal spontaneous delivery (without PhilHealth, midwife handled)" data-price="8500" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">Normal spontaneous delivery (without PhilHealth, midwife handled) — <span style="font-weight:800;color:var(--lav-4)">₱8500.00</span></div>
                    <div class="muted" style="margin-top:8px">Full-pay estimate for midwife-managed delivery.</div>
                  </div>

                  <div class="service-card" data-name="Normal spontaneous delivery (without PhilHealth, OB handled)" data-price="22000" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">Normal spontaneous delivery (without PhilHealth, OB handled) — <span style="font-weight:800;color:var(--lav-4)">₱22000.00</span></div>
                    <div class="muted" style="margin-top:8px">Full-pay estimate without PhilHealth benefits.</div>
                  </div>

                  <div class="service-card" data-name="Normal spontaneous delivery (with PhilHealth, OB handled)" data-price="10000" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">Normal spontaneous delivery (with PhilHealth, OB handled) — <span style="font-weight:800;color:var(--lav-4)">₱10000.00</span></div>
                    <div class="muted" style="margin-top:8px">PhilHealth-covered pricing; bring your PhilHealth documents for processing.</div>
                  </div>
                </div>
              </div>

              <div class="service-group">
                <h4 style="margin-top:18px">Consultation</h4>
                <div style="display:flex;gap:12px;flex-wrap:wrap">
                  <div class="service-card" data-name="OB-GYN Consultation" data-price="500" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">OB-GYN Consultation — <span style="font-weight:800;color:var(--lav-4)">₱500.00</span></div>
                    <div class="muted" style="margin-top:8px">Expert gynecological and pregnancy care from a certified OB-GYN.</div>
                  </div>

                  <div class="service-card" data-name="Pediatric Consultation" data-price="500" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">Pediatric Consultation — <span style="font-weight:800;color:var(--lav-4)">₱500.00</span> <span style="font-size:0.9rem;color:var(--muted);margin-left:8px">(follow-up ₱350.00)</span></div>
                    <div class="muted" style="margin-top:8px">Consultation services for infants and children — available via OB-GYN and midwife sessions. Follow-up visits ₱350.00.</div>
                  </div>

                  <div class="service-card" data-name="Midwife Consultation" data-price="200" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">Midwife Consultation — <span style="font-weight:800;color:var(--lav-4)">₱200.00</span></div>
                    <div class="muted" style="margin-top:8px">Routine prenatal and postnatal consultations with a licensed midwife.</div>
                  </div>

                  <!-- Duplicate pediatric card removed; single unified Pediatric Consultation card above -->
                </div>
              </div>

              <div class="service-group">
                <h4 style="margin-top:18px">Family Planning</h4>
                <div style="display:flex;gap:12px;flex-wrap:wrap">
                  <div class="service-card" data-name="Implant (3 years)" data-price="3000" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">Implant (3 years) — <span style="font-weight:800;color:var(--lav-4)">₱3000.00</span></div>
                    <div class="muted" style="margin-top:8px">Long-acting reversible contraception (implant insertion fee).</div>
                  </div>

                  <div class="service-card" data-name="Lyndavel (3 months)" data-price="200" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">Lyndavel (3 months) — <span style="font-weight:800;color:var(--lav-4)">₱200.00</span></div>
                    <div class="muted" style="margin-top:8px">Three-month injectable contraceptive.</div>
                  </div>

                  <div class="service-card" data-name="Norifam (1 month)" data-price="350" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">Norifam (1 month) — <span style="font-weight:800;color:var(--lav-4)">₱350.00</span></div>
                    <div class="muted" style="margin-top:8px">Monthly oral contraceptive pill.</div>
                  </div>
                </div>
              </div>

              <div class="service-group">
                <h4 style="margin-top:18px">Laboratory</h4>
                <div style="display:flex;gap:12px;flex-wrap:wrap">
                  <div class="service-card" data-name="Pap Smear" data-price="500" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">Pap Smear — <span style="font-weight:800;color:var(--lav-4)">₱500.00</span></div>
                    <div class="muted" style="margin-top:8px">Cervical screening test.</div>
                  </div>

                  <div class="service-card" data-name="OGTT (Oral Glucose Tolerance Test)" data-price="800" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">OGTT (Oral Glucose Tolerance Test) — <span style="font-weight:800;color:var(--lav-4)">₱800.00</span></div>
                    <div class="muted" style="margin-top:8px">Diabetes screening test, often used in pregnancy.</div>
                  </div>

                  <div class="service-card" data-name="BPS (Bedside Service)" data-price="800" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">BPS (Bedside Service) — <span style="font-weight:800;color:var(--lav-4)">₱800.00</span></div>
                    <div class="muted" style="margin-top:8px">Comprehensive bedside assessment; ask staff for details.</div>
                  </div>

                  <div class="service-card" data-name="CBC (Complete Blood Count)" data-price="165" style="flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)">
                    <div style="font-weight:700">CBC (Complete Blood Count) — <span style="font-weight:800;color:var(--lav-4)">₱165.00</span></div>
                    <div class="muted" style="margin-top:8px">Standard blood panel for overall health monitoring.</div>
                  </div>
                </div>
              </div>

            </div>

            <script>
              // Load services from DB and render service cards; fall back to existing static cards on failure
              (async function(){
                const container = document.getElementById('servicesContainer');

                function fmtPrice(val){ if(val===null||val===undefined||val==='') return ''; const s=String(val).trim(); if(s.indexOf('-')!==-1){ const parts=s.split('-').map(p=>p.trim()); if(parts.length===2){ try{ const fmt=new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}); const a=Number(parts[0]); const b=Number(parts[1]); if(!Number.isNaN(a)&&!Number.isNaN(b)) return fmt.format(a) + ' to ' + fmt.format(b); }catch(e){} return '₱' + parts.join(' - ₱'); } } const n=Number(s); if(Number.isNaN(n)) return s; try{ return new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(n); }catch(e){ return '₱' + s; } }

                function makeCard(s){
                  const wrapper = document.createElement('div');
                  wrapper.className = 'service-card';
                  wrapper.dataset.name = s.name || '';
                  wrapper.dataset.price = (s.price !== null && s.price !== undefined) ? String(s.price) : '';
                  wrapper.style.cssText = 'flex:1;min-width:260px;background:#fff;padding:12px;border-radius:8px;border:1px solid rgba(0,0,0,0.04)';
                  const title = document.createElement('div'); title.style.fontWeight = '700';
                  const priceText = (s.price === null || s.price === '' ) ? '' : fmtPrice(s.price);
                  title.innerHTML = `${escapeHtml(s.name || '')} — <span style="font-weight:800;color:var(--lav-4)">${escapeHtml(priceText)}</span>`;
                  wrapper.appendChild(title);
                  if(s.description){ const desc = document.createElement('div'); desc.className = 'muted'; desc.style.marginTop = '8px'; desc.textContent = s.description; wrapper.appendChild(desc); }
                  return wrapper;
                }

                function escapeHtml(s){ if(s===null||s===undefined) return ''; return String(s).replace(/[&<>'"`]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;','`':'&#96;'}[c])); }

                async function renderFromDb(){
                  try{
                    const res = await fetch('get_services.php', { credentials: 'same-origin' });
                    if(!res.ok) throw new Error('Network');
                    const j = await res.json();
                    if(!j.success || !Array.isArray(j.services) || j.services.length === 0) throw new Error('No services');

                    // group services by category
                    const groups = {};
                    j.services.forEach(s => {
                      const cat = (s.category || 'General').trim() || 'General';
                      groups[cat] = groups[cat] || [];
                      groups[cat].push(s);
                    });
                    // ensure delivery-related services are listed under Delivery (DB might have them mis-categorized)
                    j.services.forEach(s => {
                      try{
                        const name = (s.name||'').toLowerCase();
                        if(name.indexOf('delivery') !== -1 || name.indexOf('birth') !== -1){
                          // remove from any other group
                          Object.keys(groups).forEach(k => { if(k !== 'Delivery') groups[k] = groups[k].filter(x => (x.name||'') !== (s.name||'')); });
                          groups['Delivery'] = groups['Delivery'] || [];
                          // avoid duplicates
                          if(!groups['Delivery'].some(x => (x.name||'') === (s.name||''))) groups['Delivery'].push(s);
                        }
                      }catch(e){}
                    });

                    // ensure Delivery appears first
                    const order = [];
                    if(groups['Delivery']) order.push('Delivery');
                    Object.keys(groups).forEach(c => { if(c !== 'Delivery') order.push(c); });

                    // build DOM
                    const frag = document.createDocumentFragment();
                    order.forEach(cat => {
                      const h = document.createElement('h4'); h.style.marginTop = '12px'; h.textContent = cat; frag.appendChild(h);
                      const row = document.createElement('div'); row.style.display = 'flex'; row.style.gap = '12px'; row.style.flexWrap = 'wrap';
                      groups[cat].forEach(s => row.appendChild(makeCard(s)));
                      frag.appendChild(row);
                    });

                    // replace container content
                    container.innerHTML = '';
                    container.appendChild(frag);
                    attachHandlers();
                    return true;
                  }catch(err){ console.warn('Could not load services from DB, using static markup', err); return false; }
                }

                function attachHandlers(){
                  document.querySelectorAll('#servicesContainer .service-card').forEach(el => {
                    el.style.cursor = 'pointer';
                    el.addEventListener('click', function(){
                      const name = this.dataset.name || '';
                      const price = this.dataset.price || '';
                      const svcField = document.getElementById('receipt_service');
                      const amtField = document.getElementById('receipt_amount');
                      if(svcField) svcField.value = name;
                      if(amtField) amtField.value = price ? fmtPrice(price) : '';
                      try{ switchPanel('payment'); }catch(e){}
                        // scroll to upload area and focus the submit controls
                        setTimeout(()=>{
                          try{
                            const uploadCard = document.querySelector('.upload-card');
                            if(uploadCard) uploadCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            const choose = document.getElementById('chooseFileBtn');
                            const submit = document.getElementById('submitPaymentBtn');
                            if(choose) { try{ choose.focus(); }catch(e){} }
                            // also ensure submit is focusable for keyboard users
                            if(submit) { try{ submit.focus(); }catch(e){} }
                          }catch(e){}
                        }, 240);
                    });
                  });
                }

                const ok = await renderFromDb();
                if(!ok){ /* fall back to static markup */ attachHandlers(); }
              })();
            </script>
          </div>

        <style>
          .payment-grid { display:grid; grid-template-columns:1fr; gap:18px; align-items:start; padding:20px; box-sizing:border-box; }
          .payment-card{ background:linear-gradient(180deg,#fbf6ff,#f6f0ff); border-radius:14px; padding:18px; box-shadow:0 6px 18px rgba(0,0,0,0.04); }
          .payment-card .qr-wrap{ background:#fff;padding:12px;border-radius:10px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(0,0,0,0.04);width:160px;height:160px;margin:8px auto }
          .upload-card{ background:#fff;border-radius:14px;padding:18px;border:1px solid rgba(156,125,232,0.04); box-shadow:none }
          .upload-card .row { display:flex; gap:8px; align-items:center }
          .upload-card input[type=text], .upload-card select { padding:10px;border-radius:10px;border:1px solid #eee; width:100%; }
          .btn-submit-payment{ background:linear-gradient(90deg,#7c3aed,#8b5cf6); color:#fff; border:0;padding:12px 16px;border-radius:10px;font-weight:700;width:100%; box-shadow:0 6px 18px rgba(124,58,237,0.12); }
           /* stacked layout: GCash card appears above the upload card so the receipts table
             can be rendered full-width below. */
           @media(max-width:880px){ .payment-grid{ grid-template-columns:1fr; } .payment-card{ order:0 } }
        </style>

        <div class="payment-grid">
          <div class="payment-card">
            <div style="font-weight:800;color:var(--lav-4);text-align:center">GCash Payment</div>
            <div style="margin-top:8px;color:var(--muted);text-align:center">Account name: <strong>Drea Lying-In Clinic</strong><br>GCash #: <strong>0951 218 5146</strong></div>
            <div class="qr-wrap" style="margin-top:14px">
              <img src="assets/images/gcashqr.jpg" alt="GCASH QR Code" id="gcashQrImg" style="max-width:100%;max-height:100%;display:block">
            </div>
            <div style="text-align:center;margin-top:12px"><button class="btn-pill ghost" id="openGcashBtn" type="button">Open QR</button></div>
          </div>

          <div class="upload-card">
            <div style="font-weight:700;color:var(--lav-4)">Upload Payment Receipt</div>
            <div class="muted" style="margin-top:6px">Accepted: JPG, PNG. Max size: 5MB. Please upload a screenshot of your GCASH payment confirmation.</div>

            <div style="margin-top:12px;display:flex;align-items:center;gap:12px">
              <input type="file" id="receiptInput" accept="image/*" style="display:none">
              <button type="button" id="chooseFileBtn" class="btn-pill ghost small">Choose File</button>
              <span id="chosenFileName" style="color:var(--muted)">No file chosen</span>
            </div>

            <div style="margin-top:12px;" class="row">
              <input type="text" id="receipt_service" placeholder="Service / Description">
              <input type="text" id="receipt_amount" placeholder="Amount">
              <input type="text" id="receipt_gcash_ref" placeholder="GCash Ref No.">
            </div>

            <div class="upload-preview" id="uploadPreview" style="margin-top:12px;display:none;align-items:flex-start">
              <img src="" alt="preview" class="preview-thumb" id="previewImg" style="width:72px;height:72px;object-fit:cover;border-radius:8px;margin-right:12px;border:1px solid rgba(0,0,0,0.06)" />
              <div style="flex:1">
                <div id="previewName" style="font-weight:700"></div>
                <div id="previewInfo" class="muted" style="margin-top:6px"></div>
                <div class="upload-controls" style="margin-top:8px;display:flex;gap:8px">
                  <button class="btn-pill ghost" id="clearPreviewBtn">Remove</button>
                </div>
              </div>
            </div>

            <div id="uploadMessage" style="margin-top:12px;color:var(--muted)"></div>

            <div style="margin-top:18px">
              <button id="submitPaymentBtn" class="btn-submit-payment">Submit Payment</button>
            </div>
          </div>
        </div>
      </div>

      <!-- My Uploaded Receipts (moved out to full-width below payment controls) -->
      <div style="margin-top:18px">
        <div style="font-weight:700;color:var(--lav-4)">My Uploaded Receipts</div>
        <div class="muted" style="margin-top:6px">These screenshots are private and only visible to you and clinic staff for verification.</div>
        <div id="receiptsList" class="upload-list" style="margin-top:12px"></div>
      </div>
    </section>

  </main>
</div>

<!-- Receipt View Modal -->
<div id="receiptViewModal" class="modal" aria-hidden="true">
  <div class="dialog" role="dialog" aria-modal="true">
    <header>
      <h3 style="margin:0;color:var(--lav-4)">Payment Receipt</h3>
      <button class="close-btn" onclick="closeReceiptModal()">Close</button>
    </header>
    <div class="content" style="text-align:center">
      <img id="modalReceiptImage" src="" alt="Receipt" style="max-width:100%;max-height:70vh">
    </div>
  </div>
</div>

<!-- Photoshoot Gallery Modal -->
<div id="photoshootGalleryModal" class="confirm-modal" aria-hidden="true" style="display:none">
  <div class="dialog" role="dialog" aria-modal="true" style="max-width:960px;width:94%">
    <header style="display:flex;justify-content:space-between;align-items:center">
      <h4 style="margin:0;color:var(--lav-4)">Photoshoot Gallery</h4>
      <div style="display:flex;gap:8px;align-items:center">
        <button id="galleryPrevBtn" class="btn-pill small">Prev</button>
        <button id="galleryNextBtn" class="btn-pill small">Next</button>
        <button class="lab-view-close btn-cancel" onclick="closePhotoshootGallery()">Close</button>
      </div>
    </header>
    <div id="photoshootGalleryContent" style="margin-top:12px;min-height:320px;text-align:center;display:flex;align-items:center;justify-content:center;gap:12px;flex-direction:column">
      <img id="photoshootGalleryImg" src="" alt="photo" style="max-width:100%;max-height:72vh;border-radius:8px;object-fit:contain">
      <div id="photoshootGalleryCaption" style="color:var(--muted)"></div>
    </div>
  </div>
</div>

<!-- PATIENT FORM MODAL (unchanged except added obstetric_history) -->
<div id="formModal" class="modal" aria-hidden="true">
  <div class="dialog" role="dialog" aria-modal="true">
    <header>
      <h3 style="margin:0;color:var(--lav-4)">Patient Information Form</h3>
      <button class="close-btn" onclick="closeFormModal()">Close</button>
    </header>

    <form id="patientForm" method="POST" data-mode="new">
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px">
        <div>
          <label for="name">Full name</label>
          <input name="name" id="name" type="text" required autocomplete="name">
        </div>
        <div>
          <label for="age">Age</label>
          <input name="age" id="age" type="text">
        </div>
        <div>
          <label for="address">Address</label>
          <input name="address" id="address" type="text" autocomplete="street-address">
        </div>
        <div>
          <label for="birthday">Birthday</label>
          <input name="birthday" id="birthday" type="date" autocomplete="bday">
        </div>
        <div>
          <label for="mobile_number">Contact Number</label>
          <input name="mobile_number" id="mobile_number" type="text" autocomplete="tel">
        </div>
        <div>
          <label for="civil_status">Civil Status</label>
          <input name="civil_status" id="civil_status" type="text">
        </div>
        <div>
          <label for="nationality">Nationality</label>
          <input name="nationality" id="nationality" type="text" autocomplete="country-name">
        </div>
        <div>
          <label for="email">Email Address</label>
          <input name="email" id="email" type="email" autocomplete="email">
        </div>
        <div>
          <label for="religion">Religion</label>
          <input name="religion" id="religion" type="text">
        </div>
        <div>
          <label for="blood_type">Blood Type</label>
          <input name="blood_type" id="blood_type" type="text">
        </div>
        <div style="grid-column:1 / -1">
          <label for="allergies">Allergies</label>
          <input name="allergies" id="allergies" type="text">
        </div>
        <div style="grid-column:1 / -1">
          <label for="past_medical_condition">Past Medical Conditions</label>
          <input name="past_medical_condition" id="past_medical_condition" type="text">
        </div>
        <div style="grid-column:1 / -1">
          <label for="current_medication">Current Medications</label>
          <input name="current_medication" id="current_medication" type="text">
        </div>

        <!-- Obstetric history added so server and front-end fields match -->
        <div style="grid-column:1 / -1">
          <label for="obstetric_history">Obstetric History</label>
          <textarea name="obstetric_history" id="obstetric_history" rows="3"></textarea>
        </div>

        <div>
          <label for="number_of_pregnancies">Number of Pregnancies</label>
          <input name="number_of_pregnancies" id="number_of_pregnancies" type="text">
        </div>
        <div>
          <label for="number_of_deliveries">Number of Deliveries</label>
          <input name="number_of_deliveries" id="number_of_deliveries" type="text">
        </div>
        <div>
          <label for="last_menstrual_period">Last Menstrual Period</label>
          <input name="last_menstrual_period" id="last_menstrual_period" type="date">
        </div>
        <div>
          <label for="expected_delivery_date">Expected Delivery Date</label>
          <input name="expected_delivery_date" id="expected_delivery_date" type="date">
        </div>
        <div style="grid-column:1 / -1">
          <label for="previous_pregnancy_complication">Previous Pregnancy Complication</label>
          <input name="previous_pregnancy_complication" id="previous_pregnancy_complication" type="text">
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;margin-top:12px;gap:10px">
        <button type="button" class="close-btn" onclick="closeFormModal()">Cancel</button>
        <button type="submit" class="btn-pill submit-form">Submit Form</button>
      </div>
    </form>
  </div>
</div>

<!-- Cancel confirmation modal -->
<style>
  /* small toast + confirm modal styles */
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
  .dialog .btn-cancel {
    color: var(--lav-4) !important;
    border: 1px solid rgba(0,0,0,0.06) !important;
    background: transparent !important;
    box-shadow: none !important;
  }

  .toast{ position:fixed; right:18px; bottom:18px; z-index:99999; display:flex; align-items:center; min-width:240px; max-width:420px }
  .toast .toast-inner{ background:#fff;border-radius:10px;padding:14px 16px;box-shadow:0 8px 24px rgba(0,0,0,0.12); display:flex; align-items:center; gap:12px }
  .toast.success .toast-inner{ border-left:6px solid #28a745 }
  .toast.error .toast-inner{ border-left:6px solid #e11d48 }
  .toast .toast-msg{ flex:1; color:#0f172a }
  .toast .toast-close{ background:transparent;border:0;padding:6px 10px;border-radius:8px;cursor:pointer }
</style>

<!-- Cancel confirmation modal -->
<div id="confirmModal" class="confirm-modal" aria-hidden="true">
  <div class="dialog" role="dialog" aria-modal="true">
    <header>
      <h4 id="confirmTitle">Confirm action</h4>
    </header>
    <div id="confirmMessage">Are you sure?</div>
    <div class="actions" style="margin-top:12px">
      <button id="confirmCancel" class="btn-cancel">Cancel</button>
      <button id="confirmOk" class="btn-ok">OK</button>
    </div>
  </div>
</div>

<!-- Lab result viewer modal -->
<div id="labViewModal" class="confirm-modal" aria-hidden="true" style="display:none">
  <div class="dialog" role="dialog" aria-modal="true" style="max-width:960px;width:94%">
    <header style="display:flex;justify-content:space-between;align-items:center">
      <h4 style="margin:0;color:var(--lav-4)">View Result</h4>
      <button class="lab-view-close btn-cancel" onclick="(function(){const m=document.getElementById('labViewModal'); if(m){ m.style.display='none'; m.setAttribute('aria-hidden','true'); const c=document.getElementById('labViewContent'); if(c) c.innerHTML=''; } })()">Close</button>
    </header>
    <div id="labViewContent" style="margin-top:12px;min-height:240px"></div>
  </div>
</div>

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

  <!-- Prescription / Document viewer modal (matches doctor portal) -->
  <div id="prescriptionViewModal" class="confirm-modal" aria-hidden="true" style="display:none">
    <div class="dialog" role="dialog" aria-modal="true" style="max-width:900px;width:96%">
      <header style="display:flex;justify-content:space-between;align-items:center">
        <h4 style="margin:0;color:var(--lav-4)">View Document</h4>
        <button id="prescriptionViewClose" class="btn-cancel" type="button">Close</button>
      </header>
      <div id="prescriptionViewBody" style="margin-top:12px;height:72vh;overflow:auto;display:flex;align-items:center;justify-content:center;background:#fff;padding:8px;border-radius:8px"></div>
      <footer style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
        <a id="prescriptionDownloadLink" class="btn-pill small" href="#" download>Download</a>
        <button id="prescriptionViewDone" class="btn-pill">Done</button>
      </footer>
    </div>
  </div>

<!-- Toast container -->
<div id="toast" class="toast" aria-hidden="true" style="display:none">
  <div class="toast-inner">
    <div id="toastMsg" class="toast-msg">Message</div>
    <button id="toastClose" class="toast-close">OK</button>
  </div>
</div>

<!-- small script area: wiring, calendar, AJAX calls -->
<script>
/* ========== Panel switching ========== */
const navItems = Array.from(document.querySelectorAll('nav.sidebar .nav-item'));
const panels = {
  profile: document.getElementById('panel-profile'),
  patientinfo: document.getElementById('panel-patientinfo'),
  reserve: document.getElementById('panel-reserve'),
  appointments: document.getElementById('panel-appointments'),
  medical: document.getElementById('panel-medical'),
  prescriptions: document.getElementById('panel-prescriptions'),
  newborn: document.getElementById('panel-newborn'),
  photoshoot: document.getElementById('panel-photoshoot'),
    payment: document.getElementById('panel-payment'),
    soa: document.getElementById('panel-soa')
};
function switchPanel(panelId){
  // mark active sidebar link by matching the link href to the panel
  try{
    const hrefMap = {
      'patient_home.php': 'profile',
      'patient_reserve.php': 'reserve',
      'patient_appointments.php': 'appointments',
      'patient_medical.php': 'medical',
      'patient_prescriptions.php': 'prescriptions',
      'patient_newborn.php': 'newborn',
      'patient_payment.php': 'payment',
      'patient_soa.php': 'soa'
    };
    navItems.forEach(n => {
      try{
        const href = (n.getAttribute('href') || '').split('/').pop();
        const dataPanel = (n.getAttribute('data-panel') || '').trim() || null;
        const mapped = hrefMap[href] || dataPanel || null;
        n.classList.toggle('active', mapped === panelId);
      }catch(e){}
    });
  }catch(e){}
  Object.keys(panels).forEach(k => panels[k].hidden = (k !== panelId));
  // load relevant data when panels open
  if(panelId === 'appointments') loadAppointments();
  if(panelId === 'payment') loadReceipts();
  if(panelId === 'soa') loadSOA();
  if(panelId === 'newborn'){
    loadNewbornRecords();
    loadPatientNewbornScreenings();
  }
  if(panelId === 'photoshoot'){
    // ensure photos uploaded by midwife are shown for this patient when opening photoshoot panel
    (async ()=>{
      try{
        let pid = (window.patientDetails && (window.patientDetails.user_id || window.patientDetails.id || window.patientDetails.patient_user_id || window.patientDetails.patient_id)) || null;
        if(!pid){ const pd = await loadPatientDetails().catch(()=>null); if(pd) pid = pd.user_id || pd.id || pd.patient_user_id || pd.patient_id || null; }
        if(pid) loadPatientPhotos(pid);
      }catch(e){ console.error('Failed loading patient photos on photoshoot panel open', e); }
    })();
  }
  if(panelId === 'medical'){
    // ensure patient sees all their records immediately when opening the panel
    try{ const mf = document.getElementById('medicalFilter'); if(mf) mf.value = 'all'; }catch(e){}
    try{ const table = document.getElementById('medicalRecordsTable'); if(table) table.style.display = 'table'; }catch(e){}
    // hide any stale "no records" message and load records
    try{ const noEl = document.getElementById('noMedicalMessage'); if(noEl) noEl.style.display = 'none'; }catch(e){}
    loadMedicalRecords();
    startMedicalPolling();
  } else {
    // stop polling when leaving medical panel
    stopMedicalPolling();
  }
  if(panelId === 'prescriptions') loadPrescriptions();
}
// Sidebar navigation changed to independent pages. Determine initial panel
// from query param provided by wrapper pages (e.g. patient_home.php sets ?panel=profile)
const initialPanel = <?php echo json_encode($_GET['panel'] ?? 'profile'); ?>;
// mark the corresponding nav link as active
try{
  const panelMap = {
    profile: 'patient_home.php',
    reserve: 'patient_reserve.php',
    appointments: 'patient_appointments.php',
    medical: 'patient_medical.php',
    prescriptions: 'patient_prescriptions.php',
    newborn: 'patient_newborn.php',
    payment: 'patient_payment.php',
    soa: 'patient_soa.php'
  };
  const links = Array.from(document.querySelectorAll('nav.sidebar .nav-item'));
  links.forEach(a => a.classList.remove('active'));
  // try to find by href mapping first
  const targetHref = panelMap[initialPanel] || panelMap['profile'];
  let activeLink = links.find(a => (a.getAttribute('href')||'').indexOf(targetHref) !== -1);
  // fallback: find nav item with matching data-panel attribute
  if(!activeLink){ activeLink = links.find(a => (a.getAttribute('data-panel') || '') === initialPanel); }
  if(activeLink) activeLink.classList.add('active');
}catch(e){/* ignore */}
// show the requested panel when the page is loaded
try{ switchPanel(initialPanel); }catch(e){ switchPanel('profile'); }

// Ensure photos are loaded automatically when page loads (especially when opening newborn panel directly)
(async function autoLoadPhotosOnReady(){
  try{
    // only act if newborn panel is the initial panel or visible
    const visibleNewborn = (initialPanel === 'newborn') || (document.getElementById('panel-newborn') && !document.getElementById('panel-newborn').hidden);
    if(!visibleNewborn) return;

    // resolve patient id if available, otherwise fetch details
    let pid = (window.patientDetails && (window.patientDetails.user_id || window.patientDetails.id || window.patientDetails.patient_user_id || window.patientDetails.patient_id)) || null;
    if(!pid && typeof loadPatientDetails === 'function'){
      const pd = await loadPatientDetails().catch(()=>null);
      if(pd) pid = pd.user_id || pd.id || pd.patient_user_id || pd.patient_id || null;
    }

    // wait briefly for newborn cards to render (poll up to 3s)
    const waitForNewborns = async (timeoutMs = 3000) => {
      const start = Date.now();
      while(Date.now() - start < timeoutMs){
        const list = document.getElementById('newbornRecordsList');
        if(list && list.children && list.children.length > 0) return true;
        await new Promise(r => setTimeout(r, 120));
      }
      return false;
    };

    await waitForNewborns(3000);
    // call loadPatientPhotos with resolved pid (or without param to rely on session)
    if(typeof loadPatientPhotos === 'function'){
      if(pid) await loadPatientPhotos(pid);
      else await loadPatientPhotos();
    }
  }catch(e){ console.error('autoLoadPhotosOnReady error', e); }
})();

/* ========== UI helpers: toast notifications ========== */
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
    // clear any existing timer
    if(t._timer) clearTimeout(t._timer);
    t._timer = setTimeout(()=>{
      try{
        // If a descendant of the toast currently has focus, move focus away first to avoid aria-hidden on focused element
        const active = document.activeElement;
        if(active && t.contains(active)){
          try{ active.blur(); }catch(e){}
          try{ (document.querySelector('.book-now-btn') || document.body).focus(); }catch(e){}
        }
      }catch(e){}
      t.style.display='none';
      t.setAttribute('aria-hidden','true');
      t._timer = null;
    }, timeout);
    close.onclick = function(){
      try{ if(t._timer) clearTimeout(t._timer); }catch(e){}
      try{
        const active = document.activeElement;
        if(active && t.contains(active)){
          try{ active.blur(); }catch(e){}
          try{ (document.querySelector('.book-now-btn') || document.body).focus(); }catch(e){}
        }
      }catch(e){}
      t.style.display='none';
      t.setAttribute('aria-hidden','true');
    };
  }catch(e){ try{ alert(message); }catch(_){} }
}

// Polling: refresh medical records automatically when patient is viewing Medical panel
window._medicalPollInterval = null;
window._lastMedicalLatest = null; // ISO timestamp of latest record seen

function startMedicalPolling(){
  // avoid multiple intervals
  stopMedicalPolling();
  // immediate check is done by loadMedicalRecords(); set interval to poll for changes
  window._medicalPollInterval = setInterval(async ()=>{
    try{
      const res = await fetch('get_medical_records.php?exclude_staff=1', { credentials: 'same-origin' });
      if(!res.ok) return;
      const j = await res.json();
      if(!j.success || !Array.isArray(j.records) || j.records.length===0) return;
      const latest = j.records[0].created_at || j.records[0].uploaded_at || j.records[0].date || null;
      if(!latest) return;
      if(window._lastMedicalLatest && window._lastMedicalLatest !== latest){
        // new record(s) appeared — reload UI
        await loadMedicalRecords();
      }
      // update last seen
      window._lastMedicalLatest = latest;
    }catch(e){ /* ignore errors silently */ }
  }, 15000);
}

function stopMedicalPolling(){ if(window._medicalPollInterval){ clearInterval(window._medicalPollInterval); window._medicalPollInterval = null; } }

/* ========== Modal: patient form ========== */
function openFormModal(mode='new'){
  const modal = document.getElementById('formModal');
  const form = document.getElementById('patientForm');
  form.dataset.mode = mode;

  // If edit, fill fields from window.patientDetails; fallback to serverPatientData if patientDetails not set yet.
  if(mode === 'edit'){
    const source = window.patientDetails && Object.keys(window.patientDetails).length ? window.patientDetails
                 : (window.serverPatientData && Object.keys(window.serverPatientData).length ? window.serverPatientData : null);

    if (source) {
      Object.keys(source).forEach(k=>{
        const el = form.elements.namedItem(k);
        if(el){
          // textarea or input - set value (handle null/undefined)
          el.value = source[k] !== null && source[k] !== undefined ? source[k] : '';
        }
      });
    } else {
      // no source available yet — clear then load in background
      form.reset();
      // attempt to load from backend
      if (typeof loadPatientDetails === 'function') {
        loadPatientDetails().then(()=> {
          if (window.patientDetails) {
            Object.keys(window.patientDetails).forEach(k=>{
              const el = form.elements.namedItem(k);
              if(el) el.value = window.patientDetails[k] ?? '';
            });
          }
        }).catch(()=>{/* ignore */});
      }
    }
  } else {
    form.reset();
  }

  modal.style.display = 'flex';
  modal.setAttribute('aria-hidden','false');
}
function closeFormModal(){
  const modal = document.getElementById('formModal');
  modal.style.display = 'none';
  modal.setAttribute('aria-hidden','true');
}

/* Close modal on outside click */
window.addEventListener('click', function(e){
  const modal = document.getElementById('formModal');
  if(modal && e.target === modal){ closeFormModal(); }
});

/* ========== Patient Form submit -> save_patient_details.php ========= */
/* Replaces the previous stub: sends FormData to save_patient_details.php and reloads profile */
(function(){
  const formEl = document.getElementById('patientForm');
  if(!formEl) return;
  formEl.addEventListener('submit', async function(e){
    e.preventDefault();
    const submitBtn = formEl.querySelector('.submit-form');
    if(submitBtn) submitBtn.disabled = true;

    const formData = new FormData(formEl);

    try {
      const resp = await fetch('save_patient_details.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const text = await resp.text();
      let data = {};
      try { data = text ? JSON.parse(text) : {}; } catch(err){
        console.error('Non-JSON response from save_patient_details.php:', text);
        showToast('Server returned an unexpected response. Check server logs and browser console.', 'error');
        return;
      }

      if (!resp.ok) {
        console.error('Server error while saving patient details:', resp.status, data);
        showToast('Server error: ' + (data.message || resp.status), 'error');
        return;
      }

      if (data.success) {
        showToast(data.message || 'Profile saved successfully', 'success');
        // use returned data if present to immediately update UI
        if (data.data) {
          populateProfile(data.data);
        } else {
          // fetch fresh profile
          await loadPatientDetails();
        }
        closeFormModal();
      } else {
        showToast('Error: ' + (data.message || 'Could not save'), 'error');
      }
    } catch (err) {
      console.error('Network/fetch error while saving patient details:', err);
      showToast('Network error: ' + (err && err.message ? err.message : 'Failed to send request'), 'error');
    } finally {
      if(submitBtn) submitBtn.disabled = false;
    }
  });
})();

/* ========== Load patient details ========== */
/* Fetches get_patient_details.php to populate the profile area (returns JSON) */
async function loadPatientDetails(){
  try {
    const resp = await fetch('get_patient_details.php', { credentials: 'same-origin' });
    const text = await resp.text();
    let data = {};
    try { data = text ? JSON.parse(text) : {}; } catch(err){
      console.error('Non-JSON response from get_patient_details.php:', text);
      return;
    }

    if (!resp.ok) {
      console.warn('get_patient_details returned non-OK:', resp.status, data);
      return;
    }

    if (data.success && data.data) {
      populateProfile(data.data);
      window.patientDetails = data.data;
      return data.data;
    } else {
      // no profile found or success=false
      window.patientDetails = {};
      return null;
    }
  } catch (err) {
    console.error('Failed loading patient details', err);
    window.patientDetails = {};
    return null;
  }
}

function populateProfile(d){
  if (!d) return;
  document.getElementById('profile-name').textContent = d.name || '-';
  document.getElementById('profile-age').textContent = d.age || '-';
  document.getElementById('profile-address').textContent = d.address || '-';
  document.getElementById('profile-bday').textContent = d.birthday || '-';
  document.getElementById('profile-mobile').textContent = d.mobile_number || '-';
  document.getElementById('profile-status').textContent = d.civil_status || '-';
  document.getElementById('profile-nationality').textContent = d.nationality || '-';
  document.getElementById('profile-email').textContent = d.email || '-';
  document.getElementById('profile-religion').textContent = d.religion || '-';
  document.getElementById('profile-blood').textContent = d.blood_type || '-';
  document.getElementById('profile-allergies').textContent = d.allergies || '-';
  document.getElementById('profile-medical').textContent = d.past_medical_condition || '-';
  document.getElementById('profile-medications').textContent = d.current_medication || '-';
  document.getElementById('profile-obstetric').textContent = d.obstetric_history || '-';
  document.getElementById('profile-pregnancies').textContent = d.number_of_pregnancies || '-';
  document.getElementById('profile-deliveries').textContent = d.number_of_deliveries || '-';
  document.getElementById('profile-lmp').textContent = d.last_menstrual_period || '-';
  document.getElementById('profile-edd').textContent = d.expected_delivery_date || '-';
  document.getElementById('profile-complications').textContent = d.previous_pregnancy_complication || '-';

  // keep a copy for the modal editing
  window.patientDetails = d;
  try{
    if(typeof loadPatientPhotos === 'function'){
      const pid = d.user_id || d.id || d.patient_user_id || d.patient_id || null;
      if(pid){ setTimeout(()=>{ try{ loadPatientPhotos(pid); }catch(e){} }, 120); }
    }
  }catch(e){}
  // update sidebar name/avatar if present
  try{
    var sn = document.getElementById('sidebarName');
    if(sn) sn.textContent = d.name || 'Profile';
    var sa = document.getElementById('sidebarAvatar');
    if(sa && (d.avatar_url || d.photo)){
      sa.src = d.avatar_url || d.photo;
    }
    // also update header/profile button avatar and name
    try{
      const headerName = document.querySelector('.profile-name');
      if(headerName) headerName.textContent = d.name || 'Profile';
      const headerAvatar = document.querySelector('.profile-avatar');
      if(headerAvatar && (d.avatar_url || d.photo)) headerAvatar.src = d.avatar_url || d.photo;
        // update small header dropdown avatar and name
        try{ const hdr = document.getElementById('hdrAvatarSmall'); if(hdr && (d.avatar_url || d.photo)) hdr.src = d.avatar_url || d.photo; const hn = document.getElementById('hdrName'); if(hn) hn.textContent = d.name || 'Profile'; }catch(e){}
    }catch(e){}
  }catch(e){/* ignore silently */}
  // also update Home panel summary if present
  try{ if(typeof updateHomePanel === 'function') updateHomePanel(d); }catch(e){/* ignore */}
}

/* Update Home panel summary with pregnancy weeks/trimester and next checkup */
async function updateHomePanel(d){
  try{
    const nameEl = document.getElementById('homeName');
    const weeksEl = document.getElementById('homeWeeks');
    const triEl = document.getElementById('homeTrimester');
    const nextEl = document.getElementById('homeNextCheckDate');
    if(!nameEl) return;
    const name = d.name || d.patient_name || 'Patient';
    nameEl.textContent = name;

    // compute pregnancy weeks using LMP or EDD if available
    let weeks = null;
    const today = new Date();
    function safeParse(dateStr){ if(!dateStr) return null; const s = String(dateStr).trim(); if(!s) return null; const t = new Date(s); if(isNaN(t)){
        // try yyyy-mm-dd extraction
        const m = s.match(/(\d{4})-(\d{1,2})-(\d{1,2})/);
        if(m){ return new Date(Number(m[1]), Number(m[2])-1, Number(m[3])); }
        return null;
    } return t; }

    const lmp = safeParse(d.last_menstrual_period || d.lmp || d.lmp_date || d.last_menstrual);
    const edd = safeParse(d.expected_delivery_date || d.edd || d.estimated_delivery_date);
    if(lmp){ const diff = today - lmp; weeks = Math.floor(diff / (1000*60*60*24*7)); }
    else if(edd){ const diffDays = Math.floor((edd - today) / (1000*60*60*24)); weeks = 40 - Math.floor(diffDays/7); }

    if(weeks === null || isNaN(weeks) || weeks < 0) { weeksEl.textContent = '-'; triEl.textContent = '-'; }
    else {
      weeksEl.textContent = String(weeks);
      let trimester = 'Unknown';
      if(weeks <= 13) trimester = 'First Trimester';
      else if(weeks <= 27) trimester = 'Second Trimester';
      else trimester = 'Third Trimester';
      triEl.textContent = trimester;
    }

    // find next appointment for this patient (nearest future appointment)
    try{
      const res = await fetch('get_appointments.php', { credentials: 'same-origin' });
      if(res.ok){
        const j = await res.json().catch(()=>null);
        if(j && j.success && Array.isArray(j.appointments)){
          const appts = j.appointments.map(a => {
            const dateStr = a.appointment_date || a.date || a.appointmentDate || a.date_uploaded || a.uploaded_at || null;
            const timeStr = a.appointment_time || a.time || '';
            const dt = dateStr ? (new Date((timeStr? (dateStr + 'T' + timeStr) : dateStr))) : null;
            return { raw:a, dt: dt };
          }).filter(x => x.dt && !isNaN(x.dt) && x.dt >= new Date());
          appts.sort((A,B)=> A.dt - B.dt);
          if(appts.length > 0){
            const next = appts[0].dt;
            const opts = { year:'numeric', month:'long', day:'numeric' };
            try{ nextEl.textContent = new Date(next).toLocaleDateString(undefined, opts); }
            catch(e){ nextEl.textContent = String(next); }
            return;
          }
        }
      }
    } catch(e){ /* ignore fetch errors */ }

    // fallback when none found
    nextEl.textContent = 'Not scheduled';
  }catch(e){ console.error('updateHomePanel failed', e); }
}
  // initial load
  loadPatientDetails();

  // Ensure photos are fetched after patient details are available (fallback for panels loaded before profile)
  (async function(){
    try{
      const pd = await loadPatientDetails().catch(()=>null);
      const pid = pd ? (pd.user_id || pd.id || pd.patient_user_id || pd.patient_id) : (window.patientDetails && (window.patientDetails.user_id || window.patientDetails.id || window.patientDetails.patient_user_id || window.patientDetails.patient_id));
      if(pid){
        // small delay to allow newborn cards to render on pages that load panels dynamically
        setTimeout(()=>{ try{ loadPatientPhotos(pid); }catch(e){ console.error('fallback loadPatientPhotos failed', e); } }, 120);
      }
    }catch(e){ console.error('fallback photos loader failed', e); }
  })();

  // Load patient-facing announcement banner
  async function loadPatientAnnouncementsBanner(){
    try{
      const res = await fetch('admin_announcements.php');
      if(!res.ok) return;
      const j = await res.json().catch(()=>null);
      if(!j || !Array.isArray(j.announcements) || j.announcements.length===0) return;
      const now = new Date();
      // find first announcement that is active, published, not expired, and targeted at patients or all
      let pick = null;
      for(const a of j.announcements){
        if(!a.is_active || Number(a.is_active) === 0) continue;
        // parse published date
        if(a.published_at){ const p = new Date(a.published_at); if(isNaN(p) || p > now) continue; }
        // expires_at is a DATE (no time)
        if(a.expires_at){ const ex = new Date(a.expires_at + 'T23:59:59'); if(ex < now) continue; }
        const aud = (String(a.audience||'all')||'all').toLowerCase();
        if(aud !== 'all' && aud !== 'patients') continue;
        pick = a; break;
      }
      if(!pick) return;
      const banner = document.getElementById('patientAnnBanner');
      if(!banner) return;
      banner.innerHTML = `<div style="display:flex;align-items:start;gap:12px"><div style="flex:1"><div style="font-weight:700;color:var(--lav-4);font-size:1.05rem">${escapeHtml(pick.title||'Announcement')}</div><div style="color:var(--muted);margin-top:6px">${escapeHtml(pick.message||'')}</div></div><div><button id="btnDismissPatientAnn" class="btn-pill ghost" data-ann-id="${escapeHtml(pick.id||'')}">Dismiss</button></div></div>`;
      banner.style.display = 'block';
      const btn = document.getElementById('btnDismissPatientAnn');
      if(btn){
        btn.addEventListener('click', async ()=>{
          try{
            // hide immediately for UX
            banner.style.display = 'none';
            const aid = btn.getAttribute('data-ann-id');
            if(!aid) return;
            await fetch('ack_announcement.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ announcement_id: aid }) });
            // ignore response; announcement is now dismissed for this user
          }catch(e){ console.error('Failed to ack announcement', e); }
        });
      }
    }catch(e){ console.error('Failed to load patient announcements', e); }
  }

  // show announcements banner after patient details load
  try{ loadPatientAnnouncementsBanner(); }catch(e){}

  /* ========== Sidebar avatar upload handlers ========== */
  (function(){
    const avatarBtn = document.getElementById('sidebarAvatarBtn');
    const avatarInput = document.getElementById('sidebarAvatarInput');
    const avatarImg = document.getElementById('sidebarAvatar');
    const avatarMenu = document.getElementById('sidebarAvatarMenu');
    const avatarMenuView = document.getElementById('avatarMenuView');
    const avatarMenuChoose = document.getElementById('avatarMenuChoose');
    if(!avatarBtn || !avatarInput || !avatarImg || !avatarMenu) return;
    let prevSrc = avatarImg.src;

    // Toggle the small menu when clicking the camera overlay.
    // Position the popup so it stays visible: prefer right-of-avatar, but open to the left when needed.
    avatarBtn.addEventListener('click', (ev)=>{
      ev.stopPropagation();
      const shown = avatarMenu.style.display === 'block';
      document.querySelectorAll('#sidebarAvatarMenu').forEach(m=> m.style.display='none');
      if(shown){ avatarMenu.style.display = 'none'; return; }

      // show temporarily to measure size
      avatarMenu.style.display = 'block';
      avatarMenu.style.position = 'fixed';
      avatarMenu.style.transform = 'none';
      avatarMenu.style.zIndex = 9999;
      avatarMenu.style.visibility = 'hidden';

      // measure
      const rect = avatarBtn.getBoundingClientRect();
      const mw = avatarMenu.offsetWidth || 200;
      const mh = avatarMenu.offsetHeight || 90;

      // compute top (below the avatar) and left such that menu stays on-screen
      let top = rect.bottom + 8; // 8px gap
      let left = rect.left; // align to avatar left by default

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
      if(f.size > 3 * 1024 * 1024){ showToast('Image too large (max 3MB).', 'error'); this.value=''; return; }

      const objectUrl = URL.createObjectURL(f);
      prevSrc = avatarImg.src;
      avatarImg.src = objectUrl;

      const fd = new FormData();
      fd.append('avatar', f);
      const pid = (window.patientDetails && (window.patientDetails.user_id || window.patientDetails.id)) ? (window.patientDetails.user_id || window.patientDetails.id) : null;
      if(pid) fd.append('patient_user_id', pid);

      try{
        showToast('Uploading photo...', 'info', 1800);
        const res = await fetch('save_patient_details.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        const txt = await res.text();
        let data = {};
        try{ data = txt ? JSON.parse(txt) : {}; } catch(e){ throw new Error('Invalid server response'); }
        if(!res.ok || !data.success){ throw new Error(data.message || ('Upload failed: ' + res.status)); }
        showToast(data.message || 'Profile photo updated', 'success');
        if(data.data){
          window.patientDetails = Object.assign({}, window.patientDetails || {}, data.data);
          // update the rest of the UI (sidebar, header, home panel, patient info) immediately
          try{ populateProfile(window.patientDetails); } catch(e) { /* fallback to manual update */ }
          if(window.patientDetails.avatar_url || data.data.avatar_url) avatarImg.src = window.patientDetails.avatar_url || data.data.avatar_url;
        }
      }catch(err){
        console.error('Avatar upload failed', err);
        showToast('Failed to upload photo', 'error');
        avatarImg.src = prevSrc;
      } finally {
        try{ URL.revokeObjectURL(objectUrl); }catch(e){}
        avatarInput.value = '';
      }
    });
  })();


/* ========== Newborn records (client-only placeholders) ========== */
async function loadNewbornRecords(){
  const listEl = document.getElementById('newbornRecordsList');
  const noEl = document.getElementById('noNewbornsMessage');
  listEl.innerHTML = '';
  noEl.style.display = 'none';
  try{
    const res = await fetch('get_newborns.php', { credentials: 'same-origin' });
    if(!res.ok){ console.error('get_newborns.php returned', res.status); noEl.style.display = 'block'; noEl.textContent = 'Failed to load newborn records.'; return; }
    const j = await res.json();
    if(!j.success || !Array.isArray(j.newborns) || j.newborns.length === 0){ noEl.style.display = 'block'; listEl.innerHTML = ''; return; }
    listEl.innerHTML = j.newborns.map(n => newbornCardHtml(n)).join('');
    noEl.style.display = 'none';
    // load photos uploaded by midwives for this patient and inject into newborn cards
    try{
      let pid = (window.patientDetails && (window.patientDetails.user_id || window.patientDetails.id)) ? (window.patientDetails.user_id || window.patientDetails.id) : null;
      if(!pid && typeof loadPatientDetails === 'function'){
        const pd = await loadPatientDetails().catch(()=>null);
        if(pd) pid = pd.user_id || pd.id || pd.patient_user_id || pd.patient_id || null;
      }
      if(pid){ await loadPatientPhotos(pid); }
    }catch(e){ console.error('loadPatientPhotos call failed', e); }
  } catch(err){ console.error('Failed loading newborn records', err); noEl.style.display = 'block'; noEl.textContent = 'Failed to load newborn records.'; }
}

function newbornCardHtml(r){
  // prefer canonical keys from backend: child_name, date_of_birth, time_of_birth, blood_type, weight, notes
  const name = r.child_name || r.baby_name || r.child || 'Unnamed';
  const dobRaw = r.date_of_birth || r.date_delivery || r.dob || '';
  const time = r.time_of_birth || r.time_delivery || r.time || '';
  const blood = r.blood_type || r.blood || '';
  const weight = r.weight || r.weight_kg || '';
  const gender = r.gender || '';
  const notes = r.notes || r.medical_injection || '';

  function fmtDate(d){ if(!d) return '-'; try{ const parts = String(d).split('-'); if(parts.length===3) return `${parts[2]}/${parts[1]}/${parts[0]}`; return d; } catch(e){ return d; } }

  // gather possible photo fields (backend may return different keys)
  const possiblePhotos = [].concat(
    r.photos || r.photoshoot || [],
    r.photo ? [r.photo] : [],
    r.photo_url ? [r.photo_url] : [],
    r.photo1 ? [r.photo1] : [],
    r.photo2 ? [r.photo2] : [],
    r.photo3 ? [r.photo3] : [],
    r.file_url ? [r.file_url] : [],
    r.url ? [r.url] : []
  ).filter(Boolean);
  const mainImg = possiblePhotos.length ? possiblePhotos[0] : 'assets/images/baby-placeholder.png';
  const thumbsHtml = possiblePhotos.slice(0,3).map(p => `<img src="${escapeHtml(p)}" alt="thumb" style="width:40px;height:34px;object-fit:cover;border-radius:6px;cursor:pointer" onclick="showLabResult('${escapeHtml(p)}')">`).join('');
  const downloadBtn = possiblePhotos.length ? `<a class="btn-pill small" href="${escapeHtml(possiblePhotos[0])}" download>Download</a>` : '';

  return `
    <div class="newborn-card" onclick="openNewbornDetails('${escapeHtml(name)}','${escapeHtml(r.baby_id || r.newborn_id || r.id || '')}')" style="display:flex;gap:12px;align-items:flex-start">
      <div style="flex:1">
        <div class="newborn-row"><div class="newborn-label">Child Name</div><div class="newborn-value">${escapeHtml(name)}</div></div>
        <div class="newborn-row"><div class="newborn-label">Gender</div><div class="newborn-value">${escapeHtml(gender || '-')}</div></div>
        <div class="newborn-row"><div class="newborn-label">Date of Birth</div><div class="newborn-value">${escapeHtml(fmtDate(dobRaw))}</div></div>
        <div class="newborn-row"><div class="newborn-label">Time of Birth</div><div class="newborn-value">${escapeHtml(time || '-')}</div></div>
        <div class="newborn-row"><div class="newborn-label">Blood Type</div><div class="newborn-value">${escapeHtml(blood || '-')}</div></div>
        <div class="newborn-row"><div class="newborn-label">Weight (kg)</div><div class="newborn-value">${escapeHtml(weight || '-')}</div></div>
        <div class="newborn-row"><div class="newborn-label">Notes</div><div class="newborn-value">${escapeHtml(notes || '-')}</div></div>
      </div>
      <div style="width:160px;display:flex;flex-direction:column;align-items:center">
        <div class="newborn-photos" data-baby-id="${escapeHtml(r.baby_id || r.newborn_id || r.id || '')}" style="display:flex;flex-direction:column;align-items:center;gap:6px"></div>
      </div>
    </div>
  `;
}

// Fetch photos uploaded by midwives for the given patient and inject thumbnails into .newborn-photos containers
async function loadPatientPhotos(patientUserId){
  // allow calling without patientUserId — endpoint will default to session patient where allowed
  try{
    console.log('loadPatientPhotos: fetching for patient ID', patientUserId);
    // Try fetching with patient_user_id param first; if access denied, retry without param (session-based)
    let url = patientUserId ? ('get_photoshoot_uploads.php?patient_user_id=' + encodeURIComponent(patientUserId)) : 'get_photoshoot_uploads.php';
    let res = await fetch(url, { credentials: 'same-origin' });
    if(!res.ok){
      console.warn('get_photoshoot_uploads request failed', res.status);
      if(res.status === 403 && patientUserId){
        try{ res = await fetch('get_photoshoot_uploads.php', { credentials: 'same-origin' }); }
        catch(e){ console.error('retry without param failed', e); showToast('Failed to load photos: network error', 'error'); return; }
        if(!res.ok){ console.error('get_photoshoot_uploads failed on retry', res.status); showToast('Failed to load photos: access denied or server error', 'error'); return; }
      } else {
        showToast('Failed to load photos: server returned ' + res.status, 'error');
        return;
      }
    }

    const j = await res.json().catch(()=>({}));
    console.log('loadPatientPhotos: response', j);
    // debug output removed: do not dump raw JSON into the page

    const rows = Array.isArray(j.photos) ? j.photos : (Array.isArray(j.photoshoot_uploads) ? j.photoshoot_uploads : (Array.isArray(j.data) ? j.data : []));

    // If no rows returned, hide containers
    if(!rows || rows.length === 0) {
      const primary = document.getElementById('photoshootUploadsContainer'); if(primary) primary.style.display = 'none';
      const placeholder = document.getElementById('photoshootUploadsPlaceholder'); if(placeholder) placeholder.style.display = 'none';
      const panel = document.getElementById('midwifePhotosBelowScreenings'); if(panel) panel.style.display = 'none';
      return;
    }

    // Prepare table rows for uploaded photoshoots (use DB fields when present)
    const tableBody = document.getElementById('photoshootUploadsTableBody');
    const placeholderBody = document.getElementById('photoshootUploadsPlaceholderBody');
    const noMsg = document.getElementById('noPhotoshootUploadsMessage');
    if(tableBody) tableBody.innerHTML = '';
    if(placeholderBody) placeholderBody.innerHTML = '';
    if(noMsg) noMsg.style.display = 'none';

    let hasRows = false;
    const thumbs = [];

    rows.forEach(r => {
      // prefer DB-returned fields
      const url = (r.url || r.path || (Array.isArray(r.files) && r.files[0]) || (r.stored_filename ? ('assets/uploads/photoshoots/' + r.stored_filename) : '')) || '';
      const filename = (r.original_filename || r.filename || r.stored_filename || (url ? url.split('/').pop() : '')) || '';
      const uploaded = r.created_at || r.uploaded_at || '';

      if(url && filename) {
        hasRows = true;
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td style="font-weight:600;color:var(--lav-4)">${escapeHtml(filename)}</td>
          <td>${escapeHtml(uploaded ? (String(uploaded).split(' ')[0] || uploaded) : '-')}</td>
          <td style="text-align:center"><button class="btn-pill small" onclick="showLabResult('${escapeHtml(url)}')">View</button></td>
          <td style="text-align:center"><a class="btn-pill small" href="${escapeHtml(url)}" target="_blank" download>Download</a></td>
        `;
        // append to whichever table exists (original or placeholder)
        if(tableBody) tableBody.appendChild(tr);
        else if(placeholderBody) placeholderBody.appendChild(tr);
        // collect thumbnail/url for midwife photos panel
        thumbs.push(url);
      }
    });

    if(!hasRows && noMsg) noMsg.style.display = 'block';
    // show either the original uploads container or the new placeholder
    const primary = document.getElementById('photoshootUploadsContainer');
    const placeholder = document.getElementById('photoshootUploadsPlaceholder');
    if(primary) primary.style.display = hasRows ? 'block' : 'none';
    if(placeholder) placeholder.style.display = hasRows ? 'block' : 'none';

    // Also show thumbnails below screenings (max 6)
    const panel = document.getElementById('midwifePhotosBelowScreenings');
    const container = document.getElementById('midwifePhotosContainer');
    if(panel && container && thumbs.length > 0){
      panel.style.display = 'block'; container.innerHTML = '';
      // store for gallery view and render clickable thumbnails
      window._patientPhotos = thumbs.slice();
      thumbs.slice(0,6).forEach((u, i) => {
        const img = document.createElement('img'); img.src = u; img.alt = 'photo';
        img.dataset.index = String(i);
        img.style.width = '140px'; img.style.height = '120px'; img.style.objectFit = 'cover'; img.style.borderRadius = '8px'; img.style.cursor = 'pointer';
        img.addEventListener('click', ()=>{ try{ openPhotoshootGallery(Number(img.dataset.index||0)); }catch(e){ try{ showLabResult(u); }catch(_){ window.open(u,'_blank'); } } });
        container.appendChild(img);
      });

      // add 'Open Gallery' button if not present
      try{
        let galleryBtn = document.getElementById('openPhotosGalleryBtn');
        if(!galleryBtn){
          galleryBtn = document.createElement('button');
          galleryBtn.id = 'openPhotosGalleryBtn';
          galleryBtn.className = 'btn-pill ghost small';
          galleryBtn.style.marginTop = '8px';
          galleryBtn.textContent = 'Open Gallery';
          galleryBtn.addEventListener('click', ()=>{ try{ openPhotoshootGallery(0); }catch(e){} });
          panel.appendChild(galleryBtn);
        }
        galleryBtn.style.display = 'inline-block';
      }catch(e){}
    } else if(panel){ panel.style.display = 'none'; }

      // Also inject patient photos into any newborn record cards on the page
      try{
        const newbornContainers = Array.from(document.querySelectorAll('.newborn-photos'));
        if(newbornContainers.length > 0){
          newbornContainers.forEach(nc => {
            try{
              nc.innerHTML = '';
              if(!thumbs || thumbs.length === 0){ nc.style.display = 'none'; return; }
              nc.style.display = 'flex';
              nc.style.flexDirection = 'column';
              nc.style.alignItems = 'center';
              // newborn card photos area intentionally left empty (no thumbnails or view button)
            }catch(e){ console.error('failed to populate newborn photos', e); }
          });
        }
      }catch(e){ console.error('inject newborn photos error', e); }
  }catch(e){ console.error('loadPatientPhotos error', e); const dbg = document.getElementById('photoshootDebug'); if(dbg){ dbg.style.display='block'; dbg.textContent = 'Error: '+(e.message||e); } }
}

// Show the photoshoot uploads placeholder for a selected newborn and scroll into view
function openNewbornDetails(babyName, babyId){
  try{
    window._selectedNewborn = { id: babyId || null, name: babyName || '' };
    const placeholder = document.getElementById('photoshootUploadsPlaceholder');
    if(!placeholder) return;
    const heading = placeholder.querySelector('.photoshoot-heading');
    if(heading) heading.textContent = 'Photoshoot Uploads' + (babyName ? (' — ' + babyName) : '');
    // ensure photos are loaded (use patient id if available)
    try{
      let pid = (window.patientDetails && (window.patientDetails.user_id || window.patientDetails.id)) || null;
      if(pid) loadPatientPhotos(pid).catch(()=>{});
      else loadPatientPhotos().catch(()=>{});
    }catch(e){}
    placeholder.style.display = 'block';
    placeholder.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }catch(e){ console.error('openNewbornDetails error', e); }
}

/* ========== Patient newborn screenings: load and view functions ========== */
async function loadPatientNewbornScreenings(patient_id){
  const tbody = document.getElementById('patientScreeningsTableBody');
  const noEl = document.getElementById('noScreeningsMessage');
  if(tbody) tbody.innerHTML = '';
  if(noEl) noEl.style.display = 'none';
  try{
    // determine patient id from parameter, window.patientDetails, or by fetching details
    let pid = patient_id || (window.patientDetails && (window.patientDetails.user_id || window.patientDetails.id || window.patientDetails.patient_user_id || window.patientDetails.patient_id)) || null;
    if(!pid){
      const pd = await loadPatientDetails().catch(()=>null);
      if(pd) pid = pd.user_id || pd.id || pd.patient_user_id || pd.patient_id || null;
    }
    // if we couldn't resolve pid from JS, fall back to session-based endpoint
    let url = 'get_patient_newborn_screenings.php';
    if(pid){ url = 'get_patient_newborn_screenings.php?patient_id=' + encodeURIComponent(pid); }
    const res = await fetch(url, { credentials: 'same-origin' });
    if(!res.ok){ console.error('get_patient_newborn_screenings.php returned', res.status); if(noEl){ noEl.style.display='block'; noEl.textContent='Failed to load screening records.';} return; }
    const text = await res.text();
    let j = null;
    try { j = text ? JSON.parse(text) : {}; }
    catch(parseErr){ console.error('Invalid JSON from get_patient_newborn_screenings.php:', text); if(noEl){ noEl.style.display='block'; noEl.textContent = 'Failed to load screening records (server error).'; } return; }
    if(!j || !j.success || !Array.isArray(j.records) || j.records.length === 0){ if(noEl) noEl.style.display='block'; return; }

    const rows = j.records.map(r => {
      const babyId = r.baby_id || '';
      const babyName = r.child_name || r.baby_name || r.child || '-';
      const motherName = r.patient_name || r.mother_name || r.mother || (window.patientDetails && (window.patientDetails.name || window.patientDetails.patient_name)) || '-';
      const vitk = r.vit_k ? 'Vit K' : '';
      const hepa = r.hepa_b ? 'Hepa B' : '';
      const bcg = r.bcg ? 'BCG' : '';
      const nbs = r.newborn_screening ? 'Newborn Screening' : '';
    const hearing = r.hearing_taken ? (r.hearing_result ? (r.hearing_result) : 'Taken') : 'Not taken';
      const viewBtn = `<button class="btn-pill small" onclick="viewScreening(${r.newborn_id? r.newborn_id : 'null'}, '${escapeHtml(babyId)}')">View</button>`;
      // prefer possible file fields returned by backend
      const fileUrl = r.result_file_url || r.result_file || r.url || r.file_url || r.document_url || '';
      const fileCell = fileUrl ? (`<div style="display:flex;justify-content:center"><a class="btn-pill small screening-file-link" href="${escapeHtml(fileUrl)}" data-file="${escapeHtml(fileUrl)}">View PDF</a></div>`) : '-';
      return `<tr data-id="${escapeHtml(babyId)}">
        <td style="min-width:180px">${escapeHtml(motherName)}</td>
        <td style="text-align:center;vertical-align:middle">${escapeHtml(hearing)}</td>
        <td style="text-align:center;vertical-align:middle">${fileCell}</td>
        <td style="white-space:nowrap;text-align:center;vertical-align:middle">${viewBtn}</td>
      </tr>`;
    }).join('');

    if(tbody) tbody.innerHTML = rows;
  } catch(err){ console.error('Failed loading patient newborn screenings', err); if(noEl){ noEl.style.display='block'; noEl.textContent='Failed to load screening records.'; } }
}

function showScreeningModal(html){
  // reuse labViewModal for display
  const modal = document.getElementById('labViewModal');
  const content = document.getElementById('labViewContent');
  if(!modal || !content) return;
  content.innerHTML = html;
  modal.style.display = 'flex';
  modal.setAttribute('aria-hidden','false');
}

/* Photoshoot gallery helpers */
function openPhotoshootGallery(startIndex){
  try{
    const arr = Array.isArray(window._patientPhotos) ? window._patientPhotos : [];
    if(!arr || arr.length === 0) { showToast('No photos available', 'error'); return; }
    let idx = Number(startIndex) || 0; if(idx < 0) idx = 0; if(idx >= arr.length) idx = arr.length - 1;
    const modal = document.getElementById('photoshootGalleryModal');
    const img = document.getElementById('photoshootGalleryImg');
    const cap = document.getElementById('photoshootGalleryCaption');
    if(!modal || !img) return;
    modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
    img.src = arr[idx];
    cap.textContent = `${idx+1} of ${arr.length}`;
    modal._idx = idx;
    // wire prev/next
    document.getElementById('galleryPrevBtn').onclick = function(){ try{ navigateGallery(-1); }catch(e){} };
    document.getElementById('galleryNextBtn').onclick = function(){ try{ navigateGallery(1); }catch(e){} };
    // keyboard handlers
    modal._keyHandler = function(e){ if(e.key === 'ArrowLeft') navigateGallery(-1); else if(e.key === 'ArrowRight') navigateGallery(1); else if(e.key === 'Escape') closePhotoshootGallery(); };
    document.addEventListener('keydown', modal._keyHandler);
  }catch(e){ console.error('openPhotoshootGallery', e); }
}
function navigateGallery(dir){
  try{
    const modal = document.getElementById('photoshootGalleryModal'); if(!modal) return;
    const arr = Array.isArray(window._patientPhotos) ? window._patientPhotos : [];
    let idx = Number(modal._idx || 0) + Number(dir || 0);
    if(idx < 0) idx = 0; if(idx >= arr.length) idx = arr.length - 1;
    const img = document.getElementById('photoshootGalleryImg'); const cap = document.getElementById('photoshootGalleryCaption');
    if(img) img.src = arr[idx]; if(cap) cap.textContent = `${idx+1} of ${arr.length}`;
    modal._idx = idx;
  }catch(e){ console.error('navigateGallery', e); }
}
function closePhotoshootGallery(){
  try{
    const modal = document.getElementById('photoshootGalleryModal'); if(!modal) return;
    modal.style.display = 'none'; modal.setAttribute('aria-hidden','true');
    const img = document.getElementById('photoshootGalleryImg'); if(img) img.src = '';
    if(modal._keyHandler) { document.removeEventListener('keydown', modal._keyHandler); modal._keyHandler = null; }
  }catch(e){ console.error('closePhotoshootGallery', e); }
}

// Open gallery for the current patient; ensure photos are loaded first
async function openGalleryForPatient(){
  try{
    // Resolve patient id from window.patientDetails or by fetching
    let pid = (window.patientDetails && (window.patientDetails.user_id || window.patientDetails.id || window.patientDetails.patient_user_id || window.patientDetails.patient_id)) || null;
    if(!pid && typeof loadPatientDetails === 'function'){
      const pd = await loadPatientDetails().catch(()=>null);
      if(pd) pid = pd.user_id || pd.id || pd.patient_user_id || pd.patient_id || null;
    }

    if(!pid){ showToast('Could not determine patient id', 'error'); return; }

    // If photos already loaded, open immediately
    if(Array.isArray(window._patientPhotos) && window._patientPhotos.length > 0){
      openPhotoshootGallery(0); return;
    }

    // Load photos then open gallery
    try{ await loadPatientPhotos(pid); }catch(e){ console.error('Failed loading photos before gallery', e); }
    if(Array.isArray(window._patientPhotos) && window._patientPhotos.length > 0){
      openPhotoshootGallery(0);
    } else {
      showToast('No photos available', 'error');
    }
  }catch(e){ console.error('openGalleryForPatient error', e); showToast('Failed to open gallery', 'error'); }
}

async function viewScreening(newborn_id, baby_id){
  try{
    let url = 'get_newborn_screening.php';
    if(baby_id){ url += '?baby_id=' + encodeURIComponent(baby_id); }
    else if(newborn_id){ url += '?newborn_id=' + encodeURIComponent(newborn_id); }
    else { showToast('No identifier for this screening', 'error'); return; }

    const res = await fetch(url, { credentials: 'same-origin' });
    if(!res.ok){ showToast('Failed to load screening', 'error'); return; }
    const j = await res.json();
    if(!j.success || !j.record){ showToast('No screening record found', 'error'); return; }
    const r = j.record;
    const parts = [];
    parts.push(`<div style="text-align:left;padding:6px"><div><strong>Baby ID:</strong> ${escapeHtml(r.baby_id || '')}</div>`);
    parts.push(`<div><strong>Newborn ID:</strong> ${escapeHtml(r.newborn_id || '')}</div>`);
    parts.push(`<div style="margin-top:8px"><strong>Screenings:</strong></div><ul style="margin:6px 0 0 18px">`);
    parts.push(`<li>Vit K: ${r.vit_k? 'Yes':'No'}</li>`);
    parts.push(`<li>Hepa B: ${r.hepa_b? 'Yes':'No'}</li>`);
    parts.push(`<li>BCG: ${r.bcg? 'Yes':'No'}</li>`);
    parts.push(`<li>Newborn Screening: ${r.newborn_screening? 'Yes':'No'}</li>`);
    parts.push(`</ul>`);
    parts.push(`<div style="margin-top:8px"><strong>Hearing Test:</strong> ${r.hearing_taken? 'Taken' : 'Not taken'}</div>`);
    if(r.hearing_taken){ parts.push(`<div><strong>Result:</strong> ${escapeHtml(r.hearing_result || '')}</div>`); }
    if(r.notes){ parts.push(`<div style="margin-top:8px"><strong>Notes:</strong><div class="muted">${escapeHtml(r.notes||'')}</div></div>`); }
    parts.push(`</div>`);
    showScreeningModal(parts.join(''));
  }catch(e){ console.error('viewScreening error', e); showToast('Failed to load screening', 'error'); }
}

/* ========== Medical Records: table + preview ======== */
async function loadMedicalRecords(){
  // New: medical records panel now shows lab results uploaded by clinic staff.
  const tbody = document.getElementById('medicalRecordsTableBody');
  const noEl = document.getElementById('noMedicalMessage');
  const labWrap = document.getElementById('medicalLabResults');
  if(tbody) tbody.innerHTML = '';
  if(noEl) noEl.style.display = 'none';
  if(labWrap) labWrap.style.display = 'none';

  try{
    const res = await fetch('get_results_upload.php', { credentials: 'same-origin' });
    if(!res.ok){ console.error('get_results_upload.php returned', res.status); if(noEl){ noEl.style.display = 'block'; noEl.textContent = 'Failed to load laboratory records.'; } return; }
    const j = await res.json();
    const results = Array.isArray(j.results_uploaded) ? j.results_uploaded : (Array.isArray(j.lab_results) ? j.lab_results : []);
    if(!j.success || !Array.isArray(results) || results.length === 0){ if(noEl) noEl.style.display = 'block'; return; }

    // store for potential later use
    window._medicalRecords = results;

    function formatDateTime(dt){ if(!dt) return '-'; try{ const s = String(dt).trim(); let datePart = s, timePart = ''; if(s.indexOf(' ') !== -1){ [datePart, timePart] = s.split(' '); } const dp = datePart.split('-'); if(dp.length===3){ const yyyy = dp[0], mm = dp[1], dd = dp[2]; if(timePart){ const t = timePart.split(':'); const hh = Number(t[0])||0; const min = String(t[1]||'00').padStart(2,'0'); const am = hh>=12 ? 'PM' : 'AM'; const h12 = ((hh+11)%12)+1; return `${dd}/${mm}/${yyyy} ${h12}:${min} ${am}`; } return `${dd}/${mm}/${yyyy}`; } return s; }catch(e){ return dt; } }

    const rows = results.map((r, idx) => {
      const uploaded = r.uploaded_at || r.created_at || r.uploadedAt || '';
      const dateDisplay = formatDateTime(uploaded);
      const patient = r.patient_name || r.patient || '';
      const appointment = r.appointment_service || r.service || (r.appointment_id ? ('#' + r.appointment_id) : (r.appointment_ref || ''));
      const notes = r.notes || r.note || '';
      const url = r.url || r.file_url || r.document_url || '';
      const filename = r.filename || r.file_name || (url ? url.split('/').pop() : '');

      const fileActions = url ? (`<button class="btn-pill small" onclick="showLabResult('${escapeHtml(url)}')">View</button> <a class="btn-pill small" href="${escapeHtml(url)}" target="_blank" download>Download</a>`) : '-';

      return `<tr data-idx="${idx}">
        <td style="padding:10px 12px;width:140px">${escapeHtml(dateDisplay)}</td>
        <td style="padding:10px 12px;min-width:160px">${escapeHtml(patient)}</td>
        <td style="padding:10px 12px;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escapeHtml(notes)}</td>
        <td style="padding:10px 12px;white-space:nowrap">${fileActions}</td>
      </tr>`;
    }).join('');

    if(tbody) tbody.innerHTML = rows;

    // wire up row clicks to open attached file if present
    setTimeout(()=>{
      document.querySelectorAll('#medicalRecordsTableBody tr').forEach(tr => {
        tr.addEventListener('click', function(e){
          // prefer click on File action to handle file; otherwise open first file URL if present
          const idx = Number(this.dataset.idx);
          const rec = window._medicalRecords && window._medicalRecords[idx];
          if(!rec) return;
          const fileUrl = rec.file_url || rec.url || rec.document_url || '';
          if(fileUrl) showLabResult(fileUrl);
        });
      });
    }, 50);

  } catch(err){ console.error('Failed loading medical records (lab results)', err); if(noEl){ noEl.style.display = 'block'; noEl.textContent = 'Failed to load laboratory records.'; } }
}

async function filterMedicalRecords(){
  // Filtering disabled — always show lab results in the table.
  try{
    await loadMedicalRecords();
  }catch(e){ console.error('filterMedicalRecords: failed to reload medical records', e); }
}

// Preview removed; rows now open attached files in a new tab instead of showing inline preview.

function doMedicalPrint(rec){
  const fileUrl = rec.file_url || rec.url || rec.attachment || rec.pdf_url || rec.document_url || '';
  if(fileUrl){
    const w = window.open(fileUrl, '_blank');
    // attempt to call print after load; some browsers block
    setTimeout(()=>{ try{ w.print(); }catch(e){} }, 800);
  } else {
    // create printable window with notes
    const html = `<!doctype html><html><head><meta charset="utf-8"><title>Print</title></head><body><pre>${escapeHtml(rec.notes||rec.clinical_notes||'')}</pre></body></html>`;
    const w = window.open('about:blank'); w.document.write(html); w.document.close(); setTimeout(()=>{ try{ w.print(); }catch(e){} }, 300);
  }
}

/* ========== Prescriptions (client-only placeholders) ========== */
async function loadPrescriptions(){
  const tbody = document.getElementById('prescriptionsTableBody');
  const noEl = document.getElementById('noRxMessage');
  if(tbody) tbody.innerHTML = '';
  if(noEl) noEl.style.display = 'none';
  try{
    const res = await fetch('get_prescriptions.php', { credentials: 'same-origin' });
    if(!res.ok){ console.error('get_prescriptions.php returned', res.status); if(noEl){ noEl.style.display = 'block'; noEl.textContent = 'Failed to load prescriptions.'; } return; }
    const j = await res.json();
    if(!j.success || !Array.isArray(j.prescriptions) || j.prescriptions.length === 0){ if(noEl){ noEl.style.display = 'block'; } if(tbody) tbody.innerHTML = ''; return; }

    function formatDateToDisplay(d){ if(!d) return '-'; try{ const parts = String(d).split(' ')[0].split('-'); if(parts.length===3) return `${parts[2]}/${parts[1]}/${parts[0]}`; return d; }catch(e){ return d; } }

      const rows = j.prescriptions.map(p => {
      const dateRaw = p.prescribed_date || p.date || p.created_at || p.prescribed || '';
      const date = formatDateToDisplay(dateRaw);

      // possible file fields returned by backend
      const fileUrl = p.url || p.file_url || p.document_url || p.prescription_url || p.prescription_file || '';
      const filename = p.filename || p.file_name || p.prescription_filename || 'prescription';

      const fileCell = fileUrl ?
        (`<a class="btn-pill small prescription-file-link" href="${escapeHtml(fileUrl)}" data-file="${escapeHtml(fileUrl)}">View</a>` +
         ` <a class="btn-pill small" href="${escapeHtml(fileUrl)}" download="${escapeHtml(filename)}">Download</a>`) :
        '-';

      return `
        <tr>
          <td>${escapeHtml(date)}</td>
          <td style="white-space:nowrap">${fileCell}</td>
        </tr>`;
    }).join('');

    if(tbody) tbody.innerHTML = rows;
    if(noEl) noEl.style.display = 'none';
  } catch(err){ console.error('Failed loading prescriptions', err); if(noEl){ noEl.style.display = 'block'; noEl.textContent = 'Failed to load prescriptions.'; } }
}

function prescriptionCardHtml(r){
  // Format dates nicely
  function formatDate(d) {
    if (!d) return '-';
    try {
      const [y,m,d] = d.split('-');
      return `${m}/${d}/${y}`;
    } catch(e) { return d; }
  }

  // Get values with fallbacks
  const medication = r.medication_name || r.drugs || '';
  const dose = r.dose || r.dosage || '';
  const frequency = r.frequency || r.instruction || '';
  const quantity = r.quantity || '-';
  const refills = r.refills || '-';
  const condition = r.condition || r.diagnosis || '';
  const provider = r.provider || r.prescriber || r.created_by || '';
  const prescribed = formatDate(r.prescribed_date || r.date || r.created_at || '');
  const renewBy = formatDate(r.renew_by || r.expiry_date || '');

  // Prefer file/url fields if present so patients can view/download PDF prescriptions
  const fileUrl = r.url || r.file_url || r.document_url || r.prescription_url || r.prescription_file || '';
  const filename = r.filename || r.file_name || r.prescription_filename || `${(medication||'prescription').replace(/\s+/g,'_')}`;
  const date = prescribed || '';
  const viewBtn = fileUrl ? `<button class="btn-pill small" onclick="showLabResult('${escapeHtml(fileUrl)}')">View result</button>` : '';
  const downloadBtn = fileUrl ? `<a class="btn-pill small" href="${escapeHtml(fileUrl)}" target="_blank" download="${escapeHtml(filename)}">Download PDF</a>` : '';

  return `
    <div style="padding:10px;border-radius:8px;background:#fff;border:1px solid rgba(0,0,0,0.04);display:flex;align-items:center;gap:12px">
      <div style="width:120px;color:var(--muted);font-size:0.95rem">${escapeHtml(date)}</div>
      <div style="flex:1;min-width:0;font-weight:700;color:var(--lav-4)">${escapeHtml(medication || 'Prescription')}</div>
      <div style="white-space:nowrap;display:flex;gap:8px">${viewBtn}${downloadBtn}</div>
    </div>
  `;
}

/* ========== Booking logic (calendar & services) ========== */
/* Laboratory added: available Monday–Sunday 09:00–17:00 */
const serviceSchedules = {
  'NST (Normal Spontaneous Delivery)': { days: [0,1,2,3,4,5,6], start: '00:00', end: '23:59' },
  'Midwife Checkup': { days: [0,1,2,3,4,5,6], start: '09:00', end: '17:00' },
  'OB-GYN Consultation': { days: [1], start: '11:00', end: '13:00' },
  'Pedia Checkup': { days: [2], start: '15:00', end: '17:00' },
  'Ultrasound': { days: [1,2,3,4], start: '07:00', end: '15:00' },
  /* 'Pelvic' and 'BPS' schedule entries removed per request */
  'Ear Piercing and Pregnancy Test': { days: [0,1,2,3,4,5,6], start: '09:00', end: '17:00' },
  'Newborn Screening': { days: [0,1,2,3,4,5,6], start: '00:00', end: '23:59' },
  'Family Planning': { days: [0,1,2,3,4,5,6], start: '09:00', end: '17:00' },
  'Laboratory': { days: [0,1,2,3,4,5,6], start: '09:00', end: '17:00' }
};

let selectedService = null;
let calendarYear = new Date().getFullYear();
let calendarMonth = new Date().getMonth();
let selectedDate = null;
let selectedMidwife = null; // stores assigned midwife username for privacy
let selectedProvider = null; // stores assigned doctor/provider username for privacy

// transform simple buttons into card layout (add title/desc/select pill)
Array.from(document.querySelectorAll('.service-btn')).forEach(btn => {
  const svcName = (btn.dataset.service || btn.textContent || '').trim();
  // descriptions map for specific services
  const svcDescriptions = {
    'NST (Normal Spontaneous Delivery)': 'Safe and natural childbirth assisted by skilled midwives.',
    'Midwife Checkup': 'Routine prenatal and postnatal consultations with licensed midwives.',
    'OB-GYN Consultation': 'Expert gynecological and pregnancy care from a certified OB-GYN.',
    'Pedia Checkup': 'Regular health monitoring and medical care for infants and children ₱350.00 for followup.',
    'Ear Piercing and Pregnancy Test': 'Quick and hygienic ear piercing and accurate pregnancy testing.',
    'Ear Piercing / Pregnancy Test': 'Quick and hygienic ear piercing and accurate pregnancy testing.',
    'Newborn Screening': 'Early detection of metabolic and genetic disorders in newborns.',
    'Family Planning': 'Counseling and services to support safe and responsible parenthood.',
    'Ultrasound': 'We offer safe and accurate imaging to monitor pregnancy and assess reproductive health.',
    'Laboratory': 'Our laboratory provides reliable diagnostic tests to support your overall health and pregnancy care.'
  };

  // prices map (HTML allowed for line breaks)
  const svcPrices = {
    'NST (Normal Spontaneous Delivery)': 'With PhilHealth (OB handled): ₱10,000 to ₱12,000<br>Without PhilHealth (OB handled): ₱22,000 to ₱25,000',
    'Family Planning': 'Norifam (1 month) — ₱350<br><span style="font-weight:600">Monthly oral contraceptive pill.</span><br>Lyndavel (3 months) — ₱200<br><span style="font-weight:600">Three-month injectable contraceptive.</span><br>Implant (3 years) — ₱3,000<br><span style="font-weight:600">Long-acting reversible contraception (implant insertion fee).</span>',
    'Midwife Checkup': '₱200<br><span style="font-weight:600">Routine follow-up visits after the first check are free.</span>',
    'OB-GYN Consultation': '₱500<br><span style="font-weight:600">Follow-up visits are ₱350.</span>',
    'Pedia Checkup': '₱500<br><span style="font-weight:600">Follow-up visits are ₱350.</span>',
    'Ultrasound': 'Transvaginal Ultrasound (₱700): High-resolution internal scan for early pregnancy and specific reproductive assessments.<br>Pelvic Ultrasound (₱400): Standard imaging for pelvic organs and pregnancy evaluation.'
    , 'Laboratory': 'Urinalysis (₱100): Routine urine test for general health and infection screening.<br>Buntis Package (₱500): Basic antenatal laboratory package for expectant mothers.<br>FBS (₱165): Fasting blood sugar test to check glucose levels after fasting.<br>CBC (₱165): Complete blood count to assess overall health and detect possible conditions.<br>OGTT (₱800): Oral glucose tolerance test for diabetes screening, especially during pregnancy.<br>Pap Smear (₱500): Cervical screening test for early detection of abnormalities.'
  };

  // Add newborn screening and ear/pregnancy prices
  svcPrices['Newborn Screening'] = 'Expanded Newborn Screening (ENBS): ₱1,750<br>Basic screening (6 disorders): ₱550';
  svcPrices['Ear Piercing and Pregnancy Test'] = 'Ear piercing (studio): ₱600–₱1,000+ (depends on jewellery & method)<br>Pregnancy test (lab/hospital): ₱450';
  svcPrices['Ear Piercing / Pregnancy Test'] = svcPrices['Ear Piercing and Pregnancy Test'];

  const desc = svcDescriptions[svcName] || '';
  const priceHtml = svcPrices[svcName] || '';

  // build inner structure matching the visual spec: avatar, title, desc, read-more, duration+price, big select
  btn.innerHTML = `
    <div class="svc-content">
      <div class="svc-title">${svcName}</div>
      ${desc ? `<div class="svc-desc">${desc}</div>` : ``}
      ${desc && desc.length>120 ? `<a href="#" class="svc-readmore" onclick="event.preventDefault(); this.previousElementSibling.style.maxHeight='none'; this.style.display='none';">read more</a>` : ''}
    </div>
    <div class="svc-meta">
      <div class="svc-price">${priceHtml}</div>
    </div>
    <button type="button" class="select-pill">Select</button>
  `;

  // ensure the dataset.service remains
  btn.dataset.service = svcName;

  // clicking the whole card should select the service
  btn.addEventListener('click', () => {
    Array.from(document.querySelectorAll('.service-btn')).forEach(b=>b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedService = btn.dataset.service;
    // If the selected service requires a midwife assignment, show selector
    const requiresMidwife = ['Midwife Checkup','Ear Piercing and Pregnancy Test','Ear Piercing / Pregnancy Test','Family Planning'];
    if(requiresMidwife.includes(selectedService)){
      showMidwifeSelector();
    } else {
      // clear any previously selected midwife for other services
      selectedMidwife = null;
      hideMidwifeSelector();
    }
    // If the selected service requires a specific doctor/provider assignment, show selector
    const requiresProvider = ['OB-GYN Consultation','Ultrasound','Laboratory','Pedia Checkup','Newborn Screening'];
    if(requiresProvider.includes(selectedService)){
      showProviderSelector();
    } else {
      selectedProvider = null;
      hideProviderSelector();
    }
    document.getElementById('calendarRoot').style.display = 'block';
    document.getElementById('timeSlots').style.display = 'none';
    selectedDate = null;
    generateCalendar();
    // Auto-scroll to calendar and focus first available date (improves UX: jump to date/time selection)
    setTimeout(()=>{
      const calRoot = document.getElementById('calendarRoot');
      if(calRoot){
        // center the calendar in view smoothly
        calRoot.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      // focus the first available day for keyboard users
      const firstAvail = document.querySelector('#calendarGrid .calendar-day.available');
      if(firstAvail){
        try{ firstAvail.focus(); } catch(e){}
      }
    }, 260);
  });

  // select-pill delegates to parent card click
  const pill = btn.querySelector('.select-pill');
  if(pill){ pill.addEventListener('click', (ev)=>{ ev.stopPropagation(); btn.click(); }); }
});

// Midwife selector UI helpers
function showMidwifeSelector(){
  let root = document.getElementById('midwifeSelector');
  if(!root){
    // create selector node above calendarRoot
    root = document.createElement('div');
    root.id = 'midwifeSelector';
    root.style.marginTop = '8px';
    root.style.display = 'flex';
    root.style.gap = '8px';
    root.innerHTML = `<div style="font-weight:700;color:var(--lav-4);margin-right:8px">Assign to midwife:</div>`;
    // Default buttons - adjust usernames to match your midwife accounts
    const opts = [ {u:'midwife1', label:'Midwife 1'}, {u:'midwife2', label:'Midwife 2'} ];
    opts.forEach(o => {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'btn-pill';
      b.textContent = o.label;
      b.dataset.midwife = o.u;
      b.onclick = function(){
        // visually mark selected
        Array.from(root.querySelectorAll('button')).forEach(x=>x.classList.remove('selected'));
        this.classList.add('selected');
        selectedMidwife = this.dataset.midwife;
        showToast('Assigned to ' + this.textContent, 'success', 1600);
      };
      root.appendChild(b);
    });
    const calRoot = document.getElementById('calendarRoot');
    calRoot.parentNode.insertBefore(root, calRoot);
  }
  root.style.display = 'flex';
}

function hideMidwifeSelector(){
  const root = document.getElementById('midwifeSelector');
  if(root) root.style.display = 'none';
}

// Provider selector UI helpers (for doctors/OB-GYN/lab)
function showProviderSelector(){
  let root = document.getElementById('providerSelector');
  if(!root){
    root = document.createElement('div');
    root.id = 'providerSelector';
    root.style.marginTop = '8px';
    root.style.display = 'flex';
    root.style.gap = '8px';
    root.innerHTML = `<div style="font-weight:700;color:var(--lav-4);margin-right:8px">Assign to provider:</div>`;
    // Default provider buttons - adjust usernames to match your doctor accounts
    const opts = [
      {u:'Dr.Juan.DelaCruz_obgyn', label:'Dr. Juan Dela Cruz (OB-GYN)'},
      {u:'Dr.Jane.Doe_pediatrician', label:'Dr. Jane Doe (Pediatrician)'}
    ];
    opts.forEach(o => {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'btn-pill';
      b.textContent = o.label;
      b.dataset.provider = o.u;
      b.onclick = function(){
        Array.from(root.querySelectorAll('button')).forEach(x=>x.classList.remove('selected'));
        this.classList.add('selected');
        selectedProvider = this.dataset.provider;
        showToast('Assigned to ' + this.textContent, 'success', 1600);
      };
      root.appendChild(b);
    });
    const calRoot = document.getElementById('calendarRoot');
    calRoot.parentNode.insertBefore(root, calRoot);
  }
  root.style.display = 'flex';
}

function hideProviderSelector(){
  const root = document.getElementById('providerSelector');
  if(root) root.style.display = 'none';
}

function pad(n){ return String(n).padStart(2,'0'); }

function renderCalendarTitle(){
  const titleEl = document.querySelector('#calendarRoot .calendar-title');
  titleEl.textContent = new Date(calendarYear, calendarMonth)
    .toLocaleString(undefined,{ month: 'long', year:'numeric'});
}

function generateCalendar(){
  const grid = document.getElementById('calendarGrid');
  if(!grid) return;
  renderCalendarTitle();
  const weekdays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
  let html = weekdays.map(w=>`<div class="calendar-weekday">${w}</div>`).join('');
  const firstDay = new Date(calendarYear, calendarMonth, 1).getDay();
  const daysInMonth = new Date(calendarYear, calendarMonth+1,0).getDate();

  for(let i=0;i<firstDay;i++) html += `<div class="calendar-day disabled"></div>`;
  for(let d=1; d<=daysInMonth; d++){
    const dateObj = new Date(calendarYear, calendarMonth, d);
    const todayZero = new Date(); todayZero.setHours(0,0,0,0);
    const isPast = dateObj < todayZero;
    let isAvailable = !isPast;

    if(selectedService && serviceSchedules[selectedService]){
      const sched = serviceSchedules[selectedService];
      if(!sched.days.includes(dateObj.getDay())) isAvailable = false;
    }

    const cls = isAvailable ? 'calendar-day available' : 'calendar-day disabled';
    const dataDate = `${dateObj.getFullYear()}-${pad(dateObj.getMonth()+1)}-${pad(d)}`;
    // make available days focusable for keyboard users
    if(isAvailable){
      html += `<div class="${cls}" data-date="${dataDate}" role="button" tabindex="0" aria-disabled="false" onclick="onSelectDate(this)">${d}</div>`;
    } else {
      html += `<div class="${cls}" data-date="${dataDate}" role="button" tabindex="-1" aria-disabled="true">${d}</div>`;
    }
  }
  grid.innerHTML = html;
}

function previousMonth(){
  calendarMonth--;
  if(calendarMonth<0){ calendarMonth=11; calendarYear--; }
  generateCalendar();
}

function nextMonth(){
  calendarMonth++;
  if(calendarMonth>11){ calendarMonth=0; calendarYear++; }
  generateCalendar();
}

function onSelectDate(el){
  if(!el.classList.contains('available')) return;
  document.querySelectorAll('.calendar-day.selected').forEach(x=>x.classList.remove('selected'));
  el.classList.add('selected');
  selectedDate = new Date(el.getAttribute('data-date'));
  showTimeSlotsForSelected();
}

async function showTimeSlotsForSelected(){
  if(window._slotUpdating) return;
  // If the user just manually selected a time, skip refresh for a short window
  try{ if(window._lastManualSelect && (Date.now() - window._lastManualSelect) < 1500) { return; } }catch(e){}
  window._slotUpdating = true;
  try{
      const timeSlotsRoot = document.getElementById('timeSlotGrid');
      const container = document.getElementById('timeSlots');
      // preserve a previously selected time so we can restore it after re-render
      const prevSelectedTime = (document.querySelector('.time-slot.selected') || {}).dataset?.time || null;
      timeSlotsRoot.innerHTML = '';
      container.style.display = 'none';

    if(!selectedService || !selectedDate) return;

    const sched = serviceSchedules[selectedService];
    if(!sched){
      container.innerHTML = '<div style="padding:10px">No schedule available</div>';
      container.style.display='block';
      return;
    }

  // fetch confirmed appointments for the selected date/service so we can mark those
  // time slots as unavailable to other patients
  const confirmedSlots = new Set();
  try{
    const selDateStr = `${selectedDate.getFullYear()}-${String(selectedDate.getMonth()+1).padStart(2,'0')}-${String(selectedDate.getDate()).padStart(2,'0')}`;
    const resp = await fetch('get_appointments.php?public=1&date=' + encodeURIComponent(selDateStr), { credentials: 'same-origin' });
    if(resp && resp.ok){
      const j = await resp.json().catch(()=>null);
      if(j && Array.isArray(j.appointments)){
        function normalizeServiceName(s){ return String(s || '').toLowerCase().replace(/[^a-z0-9]+/g,' ').trim(); }
        const selServiceStr = normalizeServiceName(selectedService || '');
        j.appointments.forEach(a => {
          const aDate = a.appointment_date || a.date || selDateStr;
          let aTimeRaw = (a.appointment_time || a.time || '').toString().trim();
          // normalize time to HH:MM (drop seconds if present)
          if(aTimeRaw.indexOf(':')>=0) aTimeRaw = aTimeRaw.split(':').slice(0,2).join(':').padStart(5,'0');
          const status = String(a.status || '').toLowerCase();
          // normalize time to HH:MM (24-hour). Handle formats like "13:00:00", "1:00 PM", "01:00", etc.
          (function(){
            const s = String(aTimeRaw || '').trim();
            // match hh:mm with optional seconds and optional AM/PM
            const m = s.match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*([ap]m)?$/i);
            if(m){
              let hh = Number(m[1]);
              const mm = String(m[2]).padStart(2,'0');
              const ampm = m[3] ? String(m[3]).toLowerCase() : null;
              if(ampm){
                if(ampm === 'pm' && hh < 12) hh = hh + 12;
                if(ampm === 'am' && hh === 12) hh = 0;
              }
              aTimeRaw = `${String(hh).padStart(2,'0')}:${mm}`;
              return;
            }
            // fallback: find first HH:MM anywhere and assume 24-hour
            const m2 = s.match(/(\d{1,2}):(\d{2})/);
            if(m2){ aTimeRaw = `${String(Number(m2[1])).padStart(2,'0')}:${String(m2[2]).padStart(2,'0')}`; }
          })();
          // consider confirmed bookings as those containing 'confirm' / 'confirmed'
          // only mark slots unavailable when the appointment service matches the currently selected service
          // treat any non-cancelled status as a booked/confirmed slot so it becomes unavailable
          if(aDate === selDateStr && aTimeRaw && !(status.includes('cancel') || status === '' || status === null)){
            const aServiceRaw = String(a.service || a.appointment_service || a.service_id || a.serviceId || '').trim();
            const aServiceNorm = normalizeServiceName(aServiceRaw);
            // fuzzy match: equal, contains, or contained
            const serviceMatches = !selServiceStr || (aServiceNorm && (aServiceNorm === selServiceStr || aServiceNorm.includes(selServiceStr) || selServiceStr.includes(aServiceNorm)));
            if(serviceMatches){
              try{ confirmedSlots.add(aTimeRaw); }catch(e){ /* ignore */ }
            }
            // debug each candidate
            try{ console.debug('appt candidate', {date:aDate, time:aTimeRaw, service:aServiceRaw, sel:selServiceStr, status}); }catch(e){}
          }
        });
      }
    }
    // also fetch this patient's own appointments for the selected date so we can block booking the same time
    const ownSlots = new Set();
    try{
      const ownResp = await fetch('get_appointments.php', { credentials: 'same-origin' });
      if(ownResp && ownResp.ok){
        const ownJ = await ownResp.json().catch(()=>null);
        if(ownJ && Array.isArray(ownJ.appointments)){
          ownJ.appointments.forEach(a => {
            const aDate = a.appointment_date || a.date || '';
            let aTimeRaw = (a.appointment_time || a.time || '').toString().trim();
            // normalize time to HH:MM
            (function(){
              const s = String(aTimeRaw || '').trim();
              const m = s.match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*([ap]m)?$/i);
              if(m){
                let hh = Number(m[1]);
                const mm = String(m[2]).padStart(2,'0');
                const ampm = m[3] ? String(m[3]).toLowerCase() : null;
                if(ampm){ if(ampm === 'pm' && hh < 12) hh += 12; if(ampm === 'am' && hh === 12) hh = 0; }
                aTimeRaw = `${String(hh).padStart(2,'0')}:${mm}`;
                return;
              }
              const m2 = s.match(/(\d{1,2}):(\d{2})/);
              if(m2){ aTimeRaw = `${String(Number(m2[1])).padStart(2,'0')}:${String(m2[2]).padStart(2,'0')}`; }
            })();
            const status = String(a.status || '').toLowerCase();
            if(aDate === selDateStr && aTimeRaw && !status.includes('cancel')){
              ownSlots.add(aTimeRaw);
            }
          });
        }
      }
    }catch(e){ console.error('Failed to load patient own appointments for slot guard', e); }
    try{ window._ownSlotsForSelected = ownSlots; }catch(e){}
  }catch(e){ console.error('Failed to load appointments for slot availability', e); }
  // debug: list confirmed slots
  try{ console.debug('Confirmed unavailable slots for', selDateStr, Array.from(confirmedSlots)); }catch(e){}
  // expose confirmed slots for other handlers (selection guard)
  try{ window._confirmedSlotsForSelected = confirmedSlots; }catch(e){}

  const [sh, sm] = sched.start.split(':').map(Number);
  const [eh, em] = sched.end.split(':').map(Number);
  const start = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(), sh, sm);
  const end = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(), eh, em);

  function fmtRange(d){
    const sH = d.getHours();
    const sM = d.getMinutes();
    const r = new Date(d);
    r.setHours(r.getHours()+1);
    const eH = r.getHours();
    const eM = r.getMinutes();
    function fmt(h,m){
      const ampm = h>=12? 'PM':'AM';
      const hh = ((h+11)%12+1);
      const mmStr = String(m).padStart(2,'0');
      return `${hh}:${mmStr} ${ampm}`;
    }
    return `${fmt(sH,sM)} - ${fmt(eH,eM)}`;
  }

  const slots = [];
  const tmp = new Date(start);
  while(tmp <= end){
    // only include starts that allow a full slot (end > start)
    const slotEnd = new Date(tmp); slotEnd.setHours(slotEnd.getHours()+1);
    if(slotEnd <= end){
      slots.push(new Date(tmp));
    }
    tmp.setHours(tmp.getHours()+1);
  }

  slots.forEach(d => {
    const btn = document.createElement('div');
    // determine if this slot is in the past relative to now
    const now = new Date();
    const isPast = d.getTime() <= now.getTime();
    // normalized time string for matching confirmed appointments
    const slotTimeStr = `${d.getHours().toString().padStart(2,'0')}:${d.getMinutes().toString().padStart(2,'0')}`;
    const isOwn = (window._ownSlotsForSelected && typeof window._ownSlotsForSelected.has === 'function' && window._ownSlotsForSelected.has(slotTimeStr));
    const isUnavailable = (typeof confirmedSlots !== 'undefined' && confirmedSlots.has && confirmedSlots.has(slotTimeStr)) || isOwn;

    btn.className = 'time-slot' + ((isPast || isUnavailable) ? ' disabled' : '');
    if(isUnavailable) btn.className += ' unavailable';
    btn.innerHTML = fmtRange(d);
    // store start time (HH:MM) for booking
    btn.dataset.time = slotTimeStr;
    if(isPast || isUnavailable){
      btn.setAttribute('aria-disabled','true');
      btn.setAttribute('tabindex','-1');
      btn.setAttribute('role','presentation');
      btn.style.pointerEvents = 'none';
      btn.style.cursor = 'default';
      if(isOwn) btn.title = 'You already have an appointment at this date/time.';
      else if(isUnavailable) btn.title = 'Unavailable — this time has already been booked.';
    } else {
      btn.setAttribute('aria-disabled','false');
      btn.setAttribute('tabindex','0');
      btn.setAttribute('role','button');
      btn.style.pointerEvents = '';
      btn.style.cursor = 'pointer';
      btn.addEventListener('click', () => selectTimeSlot(btn));
      // if this slot matches the previously-selected time, restore selection
      if(prevSelectedTime && prevSelectedTime === slotTimeStr){
        // ensure it's not disabled/unavailable before restoring
        btn.classList.add('selected');
        try{ document.querySelector('.book-now-btn').disabled = false; }catch(e){}
      }
    }
    timeSlotsRoot.appendChild(btn);
  });

  // attach delegated handler once to show a toast when users click disabled/unavailable slots
  try{
    if(!timeSlotsRoot._disabledClickAdded){
      timeSlotsRoot.addEventListener('click', function(ev){
        const btn = ev.target.closest && ev.target.closest('.time-slot');
        if(!btn) return;
        const isDisabled = btn.classList.contains('disabled') || btn.getAttribute('aria-disabled') === 'true';
        if(isDisabled){
          // Decide message: if this patient owns the slot show own message, otherwise show generic unavailable message
          const t = (btn.dataset && btn.dataset.time) ? btn.dataset.time : null;
          const own = (window._ownSlotsForSelected && typeof window._ownSlotsForSelected.has === 'function' && t && window._ownSlotsForSelected.has(t));
          const other = (window._confirmedSlotsForSelected && typeof window._confirmedSlotsForSelected.has === 'function' && t && window._confirmedSlotsForSelected.has(t));
          try{
            if(own) showToast('You already have an appointment at that date/time.', 'error', 4000);
            else if(other) showToast('Unavailable — this time has already been booked by another patient.', 'error', 4000);
            else showToast('This time slot is not available.', 'error', 3000);
          }catch(e){
            try{ alert(own ? 'You already have an appointment at that date/time.' : (other ? 'Unavailable — booked by another patient.' : 'This time slot is not available.')); }catch(_){ }
          }
          ev.preventDefault();
          ev.stopPropagation();
        }
      });
      timeSlotsRoot._disabledClickAdded = true;
    }
  }catch(e){ /* ignore */ }

    container.style.display = 'grid';
  } catch(e){ console.error('showTimeSlotsForSelected failed', e); }
  finally{ try{ window._slotUpdating = false; }catch(e){} }
}

function selectTimeSlot(el){
  // ignore clicks on disabled slots or slots marked unavailable by server
  if(!el) return;
  const time = el.dataset.time || '';
  try{
    const cs = window._confirmedSlotsForSelected;
    if(cs && typeof cs.has === 'function' && cs.has(time)){
      try{ showToast('Unavailable — this time has already been booked by another patient.', 'error', 4000); }catch(e){ /* ignore */ }
      return;
    }
    const os = window._ownSlotsForSelected;
    if(os && typeof os.has === 'function' && os.has(time)){
      try{ showToast('You already have an appointment at that date/time.', 'error', 4000); }catch(e){ /* ignore */ }
      return;
    }
  }catch(e){}
  if(el.classList.contains('disabled') || el.getAttribute('aria-disabled') === 'true') return;
  document.querySelectorAll('.time-slot.selected').forEach(x=>x.classList.remove('selected'));
  el.classList.add('selected');
  document.querySelector('.book-now-btn').disabled = false;
  // record manual selection time to avoid immediate refresh flicker
  try{ window._lastManualSelect = Date.now(); }catch(e){}
}

async function bookAppointment() {
  const selectedTime = document.querySelector('.time-slot.selected')?.dataset.time;
  if (!selectedService || !selectedDate || !selectedTime) {
    showToast('Please select a service, date, and time.', 'error');
    return;
  }

  // If the service requires midwife assignment, ensure one is selected
  const requiresMidwife = ['Midwife Checkup','Ear Piercing and Pregnancy Test','Ear Piercing / Pregnancy Test','Family Planning'];
  if(requiresMidwife.includes(selectedService) && !selectedMidwife){
    showToast('Please select a midwife to handle your appointment.', 'error');
    return;
  }
  // If the service requires provider assignment, ensure one is selected
  const requiresProvider = ['OB-GYN Consultation','Ultrasound','Laboratory','Pedia Checkup','Newborn Screening'];
  if(requiresProvider.includes(selectedService) && !selectedProvider){
    showToast('Please select a provider to handle your appointment.', 'error');
    return;
  }

  const bookingData = {
    service: selectedService,
    appointment_date: selectedDate.toISOString().split('T')[0],
    appointment_time: selectedTime
  };
  if(selectedMidwife) bookingData.assigned_midwife = selectedMidwife;
  if(selectedProvider) bookingData.assigned_provider = selectedProvider;

  try {
    const res = await fetch('book_appointment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(bookingData)
    });

    const data = await res.json();

    if (data.success) {
      showToast('Appointment booked successfully!', 'success');
      loadAppointments();
      clearReserveSelection();
    } else {
      showToast('Failed: ' + data.message, 'error');
    }

  } catch (err) {
    console.error('Booking error:', err);
    showToast('Network or server error. Please try again.', 'error');
  }
}
// attach only ONE listener
document.querySelector('.book-now-btn').addEventListener('click', bookAppointment);

function loadAppointments() {
  fetch('get_appointments.php', { credentials: 'same-origin' })
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById('appointmentsList');
      if (!container) return;

      container.innerHTML = '';

      if (data.success && Array.isArray(data.appointments) && data.appointments.length > 0) {
        // Sort appointments: keep non-completed items first (most recent dates first),
        // and push completed appointments to the bottom.
        const apps = (data.appointments || []).slice();
        function parseDt(a){
          const dateVal = a.appointment_date || a.date || a.appointmentDate || '';
          const timeVal = a.appointment_time || a.time || a.appointmentTime || '00:00';
          const dt = new Date(String(dateVal) + 'T' + String(timeVal) + ':00');
          return isNaN(dt) ? null : dt.getTime();
        }
        function isCompleted(a){ const s = String(a.status||a.state||'').toLowerCase(); return s.includes('completed'); }
        // parse the booking timestamp (when they booked) - prefer created_at/createdAt
        function parseBooked(a){
          const b = a.created_at || a.createdAt || a.booked_at || a.bookedAt || null;
          if(b){
            try{
              let s = String(b).trim();
              // normalize common MySQL datetime 'YYYY-MM-DD HH:MM:SS' to ISO-like 'YYYY-MM-DDTHH:MM:SS'
              if(s.indexOf(' ')>0 && s.indexOf('T')===-1) s = s.replace(' ', 'T');
              const dt = new Date(s);
              if(!isNaN(dt)) return dt.getTime();
            }catch(e){/* fallthrough to fallback */}
          }
          return parseDt(a) || 0;
        }
        apps.sort((A,B)=>{
          const aBooked = parseBooked(A);
          const bBooked = parseBooked(B);
          // most recent booking first
          return bBooked - aBooked;
        });
        apps.forEach(app => {
          // normalize date/time naming (server may return date/time or appointment_date/appointment_time)
          const dateVal = app.appointment_date || app.date || app.appointmentDate || '';
          const timeVal = app.appointment_time || app.time || app.appointmentTime || '';
          // helper formatters
          function fmtDateISO(d){ if(!d) return '-'; try{ const parts = String(d).split('-'); if(parts.length===3) return `${parts[2]}-${parts[1]}-${parts[0]}`; return d; } catch(e){return d;} }
          function fmtDateHuman(d){ if(!d) return '-'; try{ const parts = String(d).split('-'); if(parts.length===3) return `${parts[2]}/${parts[1]}/${parts[0]}`; return d; } catch(e){return d;} }
          function fmtTimeHM(t){ if(!t) return '-'; try{ const [hh,mm] = String(t).split(':').map(Number); const am = hh>=12; const h = ((hh+11)%12)+1; const mmStr = String(mm).padStart(2,'0'); return `${h}:${mmStr} ${am? 'PM':'AM'}`; } catch(e){ return t; } }
          function addHoursToTime(t, hrs){ try{ const [hh,mm] = String(t).split(':').map(Number); const dt = new Date(dateVal+'T00:00:00'); dt.setHours(hh); dt.setMinutes(mm); dt.setHours(dt.getHours()+hrs); const h = dt.getHours(); const m = dt.getMinutes(); const am = h>=12; const hh12 = ((h+11)%12)+1; return `${hh12}:${String(m).padStart(2,'0')} ${am? 'PM':'AM'}` } catch(e){ return t; } }
          const statusText = app.status || 'Pending';

          // determine if appointment is in the past and auto-mark completed for confirmed bookings
          let computedStatusText = statusText;
          // If this appointment was cancelled by staff (or the status indicates a non-patient
          // cancellation), show a friendly rebooking message instead of a plain "Cancelled" label.
          const statusLower = String(statusText || '').toLowerCase();
          const isCancelledByStaff = (
            (app.cancelled_by && String(app.cancelled_by) !== String(app.user_id)) ||
            (statusLower.includes('cancel') && !statusLower.includes('patient'))
          );
          if (isCancelledByStaff) {
            computedStatusText = 'This time slot is unavailable. The booking has been confirmed by another patient. Please rebook and select another time.';
          }
          try{
            if(dateVal){
              // build start datetime (assume local timezone)
              const timePart = (timeVal && String(timeVal).trim()) ? String(timeVal).trim() : '00:00';
              const startDt = new Date(dateVal + 'T' + timePart + ':00');
              if(!isNaN(startDt)){
                const endDt = new Date(startDt); endDt.setHours(endDt.getHours()+1);
                const nowDt = new Date();
                const stLower = String(statusText||'').toLowerCase();
                // If booking was confirmed and end time already passed, mark completed
                if((stLower.includes('confirm') || stLower.includes('confirmed')) && endDt < nowDt){
                  computedStatusText = 'Completed';
                  // update server in background (non-blocking)
                  (async function(){
                    try{
                      await fetch('update_appointment.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: app.id, status: 'Completed' })
                      });
                    }catch(e){ /* ignore network errors silently */ }
                  })();
                }
              }
            }
          }catch(e){ /* ignore parsing errors */ }

          const card = document.createElement('div');
          card.className = 'appointment-card booked';
          const bookingCode = app.booking_code || ('#'+(app.id||''));
          const provider = 'Drea Lying-in Clinic';
          const endsAt = timeVal ? addHoursToTime(timeVal, 1) : '';
          card.innerHTML = `
            <div class="appt-title">${escapeHtml(app.service || '')}</div>
            <div class="appointment-status">${escapeHtml(computedStatusText)}</div>
            <div class="appt-row"><div class="label">Date:</div><div class="value">${escapeHtml(fmtDateHuman(dateVal))}</div></div>
            <div class="appt-row"><div class="label">Starts at:</div><div class="value">${escapeHtml(fmtTimeHM(timeVal))}</div></div>
            <div class="appt-row"><div class="label">Ends at:</div><div class="value">${escapeHtml(endsAt)}</div></div>
            <div class="appt-row"><div class="label">Provider:</div><div class="value">${escapeHtml(provider)}</div></div>
            <!-- booking code and purchase details removed -->
            <div class="appt-actions">
              <button class="btn-pill" data-id="${escapeHtml(app.id)}">Book More</button>
              <button class="appointment-action-btn cancel" data-id="${escapeHtml(app.id)}">Cancel</button>
            </div>
            
            `;

          // status color and badge styling
          const statusEl = card.querySelector('.appointment-status');
          if (statusEl) {
            const st = (computedStatusText || '').toLowerCase();
            if (st.includes('pending')) {
              statusEl.style.background = '#ffdca8';
              statusEl.style.color = '#2b2b2b';
            } else if (st.includes('confirm') || st.includes('confirmed')) {
              statusEl.style.background = '#c6f6d5';
              statusEl.style.color = '#163b12';
            } else if (st.includes('cancel')) {
              statusEl.style.background = '#ffd6d6';
              statusEl.style.color = '#7a1919';
            } else if (st.includes('completed')) {
              // blue gradient for completed
              statusEl.style.background = 'linear-gradient(90deg,#3b82f6,#2563eb)';
              statusEl.style.color = '#fff';
            } else {
              statusEl.style.background = '#f0f0f0';
              statusEl.style.color = '#222';
            }
            // Ensure provider-cancelled items use the cancel styling even though the message is custom
            if (isCancelledByStaff) {
              statusEl.style.background = '#ffd6d6';
              statusEl.style.color = '#7a1919';
            }
            statusEl.style.padding = '6px 10px';
            statusEl.style.borderRadius = '14px';
            statusEl.style.fontWeight = '700';
          }

          // remove cancel button if appointment is completed or already cancelled
          const _stNorm = String(computedStatusText || '').toLowerCase();
          if(_stNorm.includes('completed') || _stNorm.includes('cancel') || isCancelledByStaff){
            const cb = card.querySelector('.appointment-action-btn.cancel'); if(cb) cb.remove();
          }

          // wire up cancel/book-more/add-to-calendar/purchase toggle
          const cancelBtn = card.querySelector('.appointment-action-btn.cancel');
          if (cancelBtn) {
            cancelBtn.addEventListener('click', async () => {
              const ok = await showConfirmModal('Are you sure you want to cancel this appointment?', 'Cancel appointment');
              if (!ok) return;
              try {
                const res = await fetch('update_appointment.php', {
                  method: 'POST',
                  credentials: 'same-origin',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: cancelBtn.dataset.id, status: 'Cancelled by patient' })
                });
                const r = await res.json();
                if (r.success) {
                  // refresh the list so updated status is shown (server is source of truth)
                  loadAppointments();
                } else {
                  showToast('Failed to cancel: ' + (r.message || 'error'), 'error');
                }
              } catch (err) {
                console.error(err);
                showToast('Network error while cancelling appointment.', 'error');
              }
            });
          }

          const bookMoreBtn = card.querySelector('.btn-pill');
          if (bookMoreBtn){
            bookMoreBtn.addEventListener('click', ()=>{
              switchPanel('reserve');
              setTimeout(()=>{ const svc = document.querySelector('.services-row'); if(svc) svc.scrollIntoView({behavior:'smooth', block:'center'}); },200);
            });
          }

          const purchaseToggle = card.querySelector('.purchase-toggle');
          const purchaseDetails = card.querySelector('.purchase-details');
          if(purchaseToggle && purchaseDetails){
            purchaseToggle.addEventListener('click', ()=>{
              const open = purchaseDetails.style.display !== 'none';
              purchaseDetails.style.display = open ? 'none' : 'block';
              purchaseToggle.querySelector('.chev').textContent = open ? '▾' : '▴';
            });
          }

          

          container.appendChild(card);
        });
      } else {
        container.innerHTML = '<p>No appointments yet.</p>';
      }
    })
    .catch(err => console.error('Error loading appointments:', err));
}

// Automatic polling for appointments and time slots removed to avoid UI refresh flicker.
// If you want polling re-enabled later, call `loadAppointments()` or `showTimeSlotsForSelected()` from UI event handlers.

function clearReserveSelection(){
  selectedService = null;
  selectedDate = null;
  // clear selected state from service cards (query fresh in case DOM changed)
  try{
    Array.from(document.querySelectorAll('.service-btn')).forEach(b=>b.classList.remove('selected'));
  }catch(e){ /* ignore if selector not found */ }
  document.getElementById('calendarRoot').style.display = 'none';
  document.getElementById('timeSlots').style.display = 'none';
  document.querySelector('.book-now-btn').disabled = true;
}




/* cancel/reschedule placeholders */
/* cancel/reschedule helpers (kept for compatibility) */
// confirmation modal helper
let _confirmResolve = null;
function showConfirmModal(message, title){
  const modal = document.getElementById('confirmModal');
  const msg = document.getElementById('confirmMessage');
  const ttl = document.getElementById('confirmTitle');
  const btnOk = document.getElementById('confirmOk');
  const btnCancel = document.getElementById('confirmCancel');
  if(!modal || !btnOk || !btnCancel || !msg) return Promise.resolve(confirm(message));
  msg.textContent = message || 'Are you sure?';
  if(ttl) ttl.textContent = title || 'Confirm action';
  modal.style.display = 'flex';
  modal.setAttribute('aria-hidden','false');
  // ensure only one outstanding promise
  if(_confirmResolve) { _confirmResolve(false); _confirmResolve = null; }
  return new Promise((resolve)=>{
    _confirmResolve = (v)=>{ try{ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); }catch(e){} resolve(v); _confirmResolve = null; };
    btnOk.focus();
    // handlers
    const onOk = function(){ if(_confirmResolve) _confirmResolve(true); cleanup(); };
    const onCancel = function(){ if(_confirmResolve) _confirmResolve(false); cleanup(); };
    const onKey = function(e){ if(e.key === 'Escape'){ if(_confirmResolve) _confirmResolve(false); cleanup(); } };
    function cleanup(){ btnOk.removeEventListener('click', onOk); btnCancel.removeEventListener('click', onCancel); document.removeEventListener('keydown', onKey); }
    btnOk.addEventListener('click', onOk);
    btnCancel.addEventListener('click', onCancel);
    document.addEventListener('keydown', onKey);
  });
}

async function cancelAppointment(id){
  const ok = await showConfirmModal('Are you sure you want to cancel this appointment?', 'Cancel appointment');
  if (!ok) return;
  try {
    const res = await fetch('update_appointment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id, status: 'Cancelled by patient' })
    });
    const r = await res.json();
    if (r.success) {
      loadAppointments();
    } else {
      showToast('Failed to cancel: ' + (r.message || 'error'), 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('Network error while cancelling appointment.', 'error');
  }
}

function rescheduleAppointment(id){
  showToast('Reschedule flow not implemented. Please remove and rebook or contact the clinic.', 'error');
}

/* helper */
function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"'`]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'}[c])); }
function filterAppointments(){ const status = document.getElementById('statusFilter').value; const cards = Array.from(document.querySelectorAll('.appointment-card')); cards.forEach(c => { if(status==='all'){ c.style.display='block'; } else { c.style.display = (c.querySelector('.appointment-status') && c.querySelector('.appointment-status').textContent.toLowerCase().includes(status)) ? 'block' : 'none'; } }); }

/* keyboard accessibility */
document.addEventListener('keydown', function(e){
  if((e.key==='Enter' || e.key===' ') && document.activeElement && document.activeElement.classList.contains('calendar-day') ){
    document.activeElement.click();
    e.preventDefault();
  }
});
generateCalendar();

/* ========== Laboratory Results (patient view) ========== */
/* Laboratory dashboard removed — inline lab loader `resultsUploadByDoctor()` retained. */

// Load lab results into a specific container (used by Medical Records filter)
// Renamed to `resultsUploadByDoctor` — this endpoint/listing is intended to show results uploaded by clinic staff
async function resultsUploadByDoctor(listId, noId){
  const wrap = document.getElementById(listId);
  const noEl = document.getElementById(noId);
  if(!wrap) return;
  wrap.innerHTML = '';
  if(noEl) noEl.style.display = 'none';
  try{
    const res = await fetch('get_results_upload.php', { credentials: 'same-origin' });
    if(!res.ok){ if(noEl){ noEl.style.display = 'block'; noEl.textContent = 'Failed to load laboratory results.'; } return; }
    const j = await res.json();
    const results = Array.isArray(j.results_uploaded) ? j.results_uploaded : (Array.isArray(j.lab_results) ? j.lab_results : []);
    if(!j.success || !Array.isArray(results) || results.length === 0){ if(noEl){ noEl.style.display = 'block'; } return; }

    // render as a table: Date | Patient | Appointment | Notes | File
    const formatDateTime = function(dt){ if(!dt) return ''; try{ const s = String(dt).trim(); let datePart = s, timePart = ''; if(s.indexOf(' ') !== -1){ [datePart, timePart] = s.split(' '); } const dp = datePart.split('-'); if(dp.length===3){ const yyyy = dp[0], mm = dp[1], dd = dp[2]; if(timePart){ const t = timePart.split(':'); const hh = Number(t[0])||0; const min = String(t[1]||'00').padStart(2,'0'); const am = hh>=12 ? 'PM' : 'AM'; const h12 = ((hh+11)%12)+1; return `${dd}/${mm}/${yyyy} ${h12}:${min} ${am}`; } return `${dd}/${mm}/${yyyy}`; } return s; }catch(e){ return dt; } };

    const rowsHtml = results.map(r => {
      const url = r.url || r.file_url || r.document_url || '';
      const uploaded = r.uploaded_at || r.uploadedAt || r.created_at || '';
      const dateDisplay = formatDateTime(uploaded);
      const patient = r.patient_name || r.patient || '';
      const appointment = r.appointment_service || r.service || (r.appointment_id ? ('#' + r.appointment_id) : (r.appointment_ref || ''));
      const notes = r.notes || r.note || '';
      const filename = r.filename || r.file_name || r.url || '';
      const viewBtn = url ? `<button class="btn-pill small" onclick="showLabResult('${escapeHtml(url)}')">View</button>` : '';
      const downloadBtn = url ? `<a class="btn-pill small" href="${escapeHtml(url)}" target="_blank" download="${escapeHtml(filename||'result')}">Download</a>` : '';
      const fileCell = url ? (viewBtn + ' ' + downloadBtn + ' <span style="margin-left:8px;color:var(--muted);font-size:0.95rem">' + escapeHtml(filename.replace(/^.*\//,'')) + '</span>') : '-';
      return `<tr data-id="${escapeHtml(r.id||'')}">
        <td style="padding:10px 12px;width:180px">${escapeHtml(dateDisplay)}</td>
        <td style="padding:10px 12px;min-width:140px">${escapeHtml(patient)}</td>
        <td style="padding:10px 12px;min-width:220px">${escapeHtml(appointment)}</td>
        <td style="padding:10px 12px;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escapeHtml(notes)}</td>
        <td style="padding:10px 12px;white-space:nowrap">${fileCell}</td>
      </tr>`;
    }).join('');

    if(!rowsHtml){ if(noEl) noEl.style.display = 'block'; wrap.innerHTML = ''; }
    else {
      wrap.innerHTML = `
        <div style="overflow:auto;max-height:520px;background:#fff;border-radius:12px;padding:8px;border:1px solid rgba(0,0,0,0.04)">
          <table style="width:100%;border-collapse:collapse;font-size:0.95rem">
            <thead>
              <tr style="background:linear-gradient(90deg,#f6f2fc,#f0e9ff);color:var(--lav-4);text-align:left">
                <th style="padding:12px 14px;width:180px">Date</th>
                <th style="padding:12px 14px;min-width:140px">Patient</th>
                <th style="padding:12px 14px;min-width:220px">Appointment</th>
                <th style="padding:12px 14px;min-width:220px">Notes</th>
                <th style="padding:12px 14px;min-width:140px">File</th>
              </tr>
            </thead>
            <tbody>
              ${rowsHtml}
            </tbody>
          </table>
        </div>`;
      if(noEl) noEl.style.display = 'none';
    }
    // return the lab results array for callers who want to know if labs exist
    return results || [];
  } catch(err){ console.error('Failed loading lab results (inline)', err); if(noEl) { noEl.style.display = 'block'; noEl.textContent = 'Failed to load laboratory results.'; } }

        
  return [];
}

// Delegate click for lab result links to open modal viewer
document.addEventListener('click', function(e){
  const a = e.target.closest && e.target.closest('.lab-view-link');
  if(!a) return;
  e.preventDefault();
  const url = a.dataset.url;
  if(url) showLabResult(url);
});

function showLabResult(url){
  try{
    const modal = document.getElementById('labViewModal');
    const content = document.getElementById('labViewContent');
    if(!modal || !content){ window.open(url, '_blank'); return; }
    // clear previous
    content.innerHTML = '';
    const lower = String(url).toLowerCase();
    if(lower.endsWith('.pdf')){
      const iframe = document.createElement('iframe'); iframe.src = url; iframe.style.width='100%'; iframe.style.height='80vh'; iframe.style.border='0'; content.appendChild(iframe);
    } else if(lower.match(/\.(jpg|jpeg|png|gif|bmp)$/)){
      const img = document.createElement('img'); img.src = url; img.style.maxWidth='100%'; img.style.maxHeight='80vh'; img.style.display='block'; img.style.margin='0 auto'; content.appendChild(img);
    } else {
      const a = document.createElement('a'); a.href = url; a.target = '_blank'; a.textContent = 'Open attached document'; content.appendChild(a);
    }
    modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
    // focus close for accessibility
    const close = modal.querySelector('.lab-view-close'); if(close) close.focus();
  }catch(e){ window.open(url, '_blank'); }
}

// Prescription viewer: reuse modal UI and behavior from doctor portal
function showPrescriptionModal(fileUrl){
  try{
    const modal = document.getElementById('prescriptionViewModal');
    const body = document.getElementById('prescriptionViewBody');
    const download = document.getElementById('prescriptionDownloadLink');
    if(!modal || !body) { window.open(fileUrl, '_blank'); return; }
    body.innerHTML = '';
    download.href = fileUrl || '#';
    const lower = (fileUrl || '').toLowerCase();
    if(lower.endsWith('.pdf')){
      const iframe = document.createElement('iframe'); iframe.src = fileUrl; iframe.style.width = '100%'; iframe.style.height = '100%'; iframe.style.border = '0'; body.appendChild(iframe);
    } else if(lower.match(/\.(png|jpe?g|gif|bmp|webp)$/)){
      const img = document.createElement('img'); img.src = fileUrl; img.style.maxWidth = '100%'; img.style.maxHeight = '100%'; img.style.objectFit = 'contain'; body.appendChild(img);
    } else {
      const a = document.createElement('a'); a.href = fileUrl; a.target = '_blank'; a.textContent = 'Open file in new tab / download'; body.appendChild(a);
    }
    modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false');
  }catch(e){ console.error('showPrescriptionModal error', e); window.open(fileUrl, '_blank'); }
}

function closePrescriptionModal(){
  const modal = document.getElementById('prescriptionViewModal');
  const body = document.getElementById('prescriptionViewBody');
  if(modal){ modal.style.display = 'none'; modal.setAttribute('aria-hidden','true'); }
  if(body) body.innerHTML = '';
}

document.getElementById('prescriptionViewClose')?.addEventListener('click', ()=> closePrescriptionModal());
document.getElementById('prescriptionViewDone')?.addEventListener('click', ()=> closePrescriptionModal());

// Delegate clicks on prescription/screening links to open the modal
document.addEventListener('click', function(e){
  const a = e.target.closest && (e.target.closest('a.prescription-file-link') || e.target.closest('a.screening-file-link'));
  if(!a) return;
  try{ e.preventDefault(); const file = a.dataset.file || a.getAttribute('href'); if(file) showPrescriptionModal(file); } catch(err){ console.error(err); }
});

function showProfilePic(url){
  try{
    const modal = document.getElementById('profilePicModal');
    const img = document.getElementById('profilePicImg');
    if(!modal || !img){ window.open(url, '_blank'); return; }
    img.src = url;
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    // focus close for accessibility
    const close = modal.querySelector('.profile-pic-close'); if(close) close.focus();
  }catch(e){ window.open(url, '_blank'); }
}
 
/* Dynamic services loader removed — using static service cards above */

/* ========== Payment: upload receipt handling (client-only) ========== */
const receiptInput = document.getElementById('receiptInput');
const previewImg = document.getElementById('previewImg');
const previewName = document.getElementById('previewName');
const previewInfo = document.getElementById('previewInfo');
const uploadPreview = document.getElementById('uploadPreview');
const submitPaymentBtn = document.getElementById('submitPaymentBtn');
const clearPreviewBtn = document.getElementById('clearPreviewBtn');
const uploadMessage = document.getElementById('uploadMessage');
const receiptsList = document.getElementById('receiptsList');
const chooseFileBtn = document.getElementById('chooseFileBtn');
const chosenFileName = document.getElementById('chosenFileName');
const openGcashBtn = document.getElementById('openGcashBtn');

let currentFile = null;
let currentPreviewUrl = null; // stores object URL for the chosen file preview

  if (receiptInput) {
  receiptInput.addEventListener('change', function(e){
    const f = this.files[0];
    if(!f) return clearPreview();
    if(f.size > 5 * 1024 * 1024){ uploadMessage.textContent = 'File too large (max 5MB).'; this.value=''; return; }
    if(!f.type.startsWith('image/')) { uploadMessage.textContent = 'Invalid file type. Please upload an image.'; this.value=''; return; }
    currentFile = f;
    // revoke previous preview URL if present
    try{ if(currentPreviewUrl) { URL.revokeObjectURL(currentPreviewUrl); currentPreviewUrl = null; } }catch(e){}
    currentPreviewUrl = URL.createObjectURL(f);
    previewImg.src = currentPreviewUrl;
    previewName.textContent = f.name;
    if(chosenFileName) chosenFileName.textContent = f.name;
    previewInfo.textContent = `Size: ${(f.size/1024/1024).toFixed(2)} MB • Type: ${f.type}`;
    // make thumbnail clickable to open full preview
    try{ previewImg.style.cursor = 'pointer'; previewImg.onclick = ()=> { if(currentPreviewUrl) viewReceipt(currentPreviewUrl); }; }catch(e){}
    uploadPreview.style.display = 'flex';
    uploadMessage.textContent = '';
  });
}

// wire choose file button to native input
if(chooseFileBtn && receiptInput){
  chooseFileBtn.addEventListener('click', ()=> receiptInput.click());
}

// wire open QR button to reuse receipt modal viewer
if(openGcashBtn){
  openGcashBtn.addEventListener('click', ()=>{
    const img = document.getElementById('gcashQrImg');
    if(img && img.src) viewReceipt(img.src);
  });
}

if (clearPreviewBtn) {
  clearPreviewBtn.addEventListener('click', function(){
    clearPreview();
  });
}

function clearPreview(){
  if (receiptInput) receiptInput.value = '';
  if (previewImg) { previewImg.src = ''; try{ previewImg.onclick = null; previewImg.style.cursor = ''; }catch(e){} }
  if (previewName) previewName.textContent = '';
  if (previewInfo) previewInfo.textContent = '';
  if (uploadPreview) uploadPreview.style.display = 'none';
  currentFile = null;
  // revoke object URL to avoid memory leak
  try{ if(currentPreviewUrl){ URL.revokeObjectURL(currentPreviewUrl); currentPreviewUrl = null; } }catch(e){}
  if (uploadMessage) uploadMessage.textContent = '';
  if (typeof chosenFileName !== 'undefined' && chosenFileName) chosenFileName.textContent = 'No file chosen';
}

if (submitPaymentBtn) {
  submitPaymentBtn.addEventListener('click', async function(){
    if(!currentFile) { uploadMessage.textContent = 'Please choose an image first.'; return; }
    submitPaymentBtn.disabled = true;
    uploadMessage.textContent = 'Uploading...';
    try{
      const fd = new FormData();
      fd.append('receipt', currentFile);
      const pid = (window.patientDetails && window.patientDetails.user_id) ? window.patientDetails.user_id : null;
      const pname = (window.patientDetails && window.patientDetails.name) ? window.patientDetails.name : (document.getElementById('profile-name')?.textContent || '');
      if(pid) fd.append('patient_user_id', pid);
      if(pname) fd.append('patient_name', pname);
      const svc = document.getElementById('receipt_service')?.value || '';
      const amt = document.getElementById('receipt_amount')?.value || '';
      const ref = document.getElementById('receipt_gcash_ref')?.value || '';
      if(svc) fd.append('service', svc);
      if(amt) fd.append('amount', amt);
      if(ref) fd.append('gcash_ref_no', ref);

      const res = await fetch('save_payment_receipt.php', { method: 'POST', body: fd, credentials: 'same-origin' });
      const txt = await res.text();
      let j = {};
      try{ j = txt ? JSON.parse(txt) : {}; } catch(e){ console.error('Invalid JSON', txt); uploadMessage.textContent = 'Server error'; submitPaymentBtn.disabled = false; return; }
      if(!res.ok || !j.success){ uploadMessage.textContent = 'Upload failed: ' + (j.message || res.status); submitPaymentBtn.disabled = false; return; }
      uploadMessage.textContent = 'Uploaded';
      await loadReceipts();
      clearPreview();
    } catch(err){ console.error(err); uploadMessage.textContent = 'Network error'; }
    finally{ submitPaymentBtn.disabled = false; }
  });
}

async function loadReceipts(){
  const receiptsListEl = document.getElementById('receiptsList');
  if(!receiptsListEl) return;
  receiptsListEl.innerHTML = '';
  try{
    const res = await fetch('get_payments.php', { credentials: 'same-origin' });
    if(!res.ok){ receiptsListEl.innerHTML = '<div style="color:var(--muted)">Failed to load receipts.</div>'; return; }
    const j = await res.json();
    if(!j.success || !Array.isArray(j.payments) || j.payments.length === 0){ receiptsListEl.innerHTML = '<div style="color:var(--muted)">No receipts found.</div>'; return; }

    const rows = j.payments.map((p, idx) => {
      const no = idx + 1;
      const uploaded = escapeHtml((p.uploaded_at || p.date_uploaded || p.date_received || p.uploaded || '').split ? (String(p.uploaded_at || p.date_uploaded || p.date_received || p.uploaded || '').split(' ')[0]) : (p.uploaded_at || p.date_uploaded || p.date_received || ''));
      const service = escapeHtml(p.service || p.description || p.notes || '');
      const amount = escapeHtml(p.amount || p.amount_paid || p.paid || '');
      const gcash = escapeHtml(p.gcash_ref_no || p.reference_no || p.gcash_ref || p.gcash || '');
      const fileUrl = p.url || p.file_url || p.path || '';
      const fileBtn = fileUrl ? `<button class="btn-pill small" onclick="viewReceipt('${escapeHtml(fileUrl)}')">View</button>` : '—';
      const rawStatus = (p.status || p.payment_status || p.paymentStatus || '').toString() || '';
      // hide explicit "Paid" label from the patient receipts table per request
      const status = (String(rawStatus).toLowerCase().indexOf('paid') !== -1) ? '' : escapeHtml(rawStatus);
      const isVerified = p.verified && Number(p.verified) === 1;
      const verifiedHtml = isVerified ? `<span class="status-badge status-confirmed" style="margin-left:6px;background:linear-gradient(90deg,#2ecc71,#27ae60);color:#fff;padding:6px 8px;border-radius:8px;font-weight:700">Verified</span>` : '';

      return `<tr>
        <td style="white-space:nowrap;padding:10px 12px">${uploaded}</td>
        <td style="padding:10px 12px">${service}</td>
        <td style="white-space:nowrap;padding:10px 12px">${amount}</td>
        <td style="white-space:nowrap;padding:10px 12px">${gcash}</td>
        <td style="white-space:nowrap;padding:10px 12px">${fileBtn}</td>
        <td style="white-space:nowrap;padding:10px 12px">${status} ${verifiedHtml}</td>
      </tr>`;
    }).join('');

    receiptsListEl.innerHTML = `
      <div style="overflow:auto;width:100%;background:#fff;border-radius:12px;padding:12px;border:1px solid rgba(156,125,232,0.04)">
        <table id="receiptsTable" style="width:100%;border-collapse:collapse;font-size:0.95rem">
          <thead>
            <tr style="background:linear-gradient(90deg,#f6f2fc,#f0e9ff);color:var(--lav-4);text-align:left">
              <th style="padding:12px 14px;min-width:140px">Date Uploaded</th>
              <th style="padding:12px 14px;min-width:180px">Service / Description</th>
              <th style="padding:12px 14px;min-width:100px">Amount Paid</th>
              <th style="padding:12px 14px;min-width:120px">GCash Ref No.</th>
              <th style="padding:12px 14px;min-width:120px">Screenshot (File)</th>
              <th style="padding:12px 14px;min-width:140px">Payment Status</th>
            </tr>
          </thead>
          <tbody>
            ${rows}
          </tbody>
        </table>
      </div>
    `;
  } catch(err){ console.error(err); receiptsListEl.innerHTML = '<div style="color:var(--muted)">Failed to load receipts.</div>'; }
}

/* ========== SOA: Statement of Account loader and actions ==========
   Loads payment history and renders a simple invoice-style statement. */
async function loadSOA(){
  try{
    // populate patient header
    const patient = window.patientDetails || await loadPatientDetails() || {};
    const patientInfoEl = document.getElementById('soaPatientInfo');
    if(patientInfoEl){
      const lines = [];
      if(patient.name) lines.push(`<strong>${escapeHtml(patient.name)}</strong>`);
      if(patient.patient_id) lines.push(`Patient ID: ${escapeHtml(patient.patient_id)}`);
      if(patient.address) lines.push(escapeHtml(patient.address));
      if(patient.attending_midwife) lines.push(`Attending Midwife: ${escapeHtml(patient.attending_midwife)}`);
      patientInfoEl.innerHTML = lines.join('<br>');
    }

    const res = await fetch('get_payments.php', { credentials: 'same-origin' });
    if(!res.ok){ document.getElementById('soaItemsBody').innerHTML = '<tr><td colspan="5" class="muted">Failed to load payments.</td></tr>'; return; }
    const j = await res.json();
    // Only include payments that have been verified — unverified receipts shouldn't affect the SOA
    const payments = (Array.isArray(j.payments) ? j.payments : []).filter(p => Number(p.verified) === 1);

    const tbody = document.getElementById('soaItemsBody');
    if(!tbody) return;
    if(payments.length === 0){ tbody.innerHTML = '<tr><td colspan="5" class="muted">No verified payments found.</td></tr>'; updateSOATotals([]); return; }

    // Build rows: use uploaded receipts as line items
    let rows = '';
    const items = [];
    for(const p of payments){
      const d = (p.uploaded_at || p.date_uploaded || p.uploaded || '').split ? (String(p.uploaded_at||p.date_uploaded||p.uploaded||'').split(' ')[0]) : (p.uploaded_at||p.date_uploaded||'');
      const desc = escapeHtml(p.service || p.description || p.notes || 'Payment');
      const amt = Number(String(p.amount || p.amount_paid || p.paid || '0').replace(/[^0-9\.\-]/g,'')) || 0;
      const paid = amt; // receipts represent paid amounts
      const balance = 0; // per-line balance not available from payments — show 0
      rows += `<tr><td style="padding:8px 10px">${d}<div style="color:var(--muted);font-size:0.92rem;margin-top:6px">${desc}</div></td><td style="padding:8px 10px">1</td><td style="padding:8px 10px;text-align:right">₱${amt.toFixed(2)}</td><td style="padding:8px 10px;text-align:right">₱${paid.toFixed(2)}</td><td style="padding:8px 10px;text-align:right">₱${balance.toFixed(2)}</td></tr>`;
      items.push({amount:amt, paid:paid});
    }
    tbody.innerHTML = rows;
    updateSOATotals(items);

    // fill meta (simple placeholders)
    // remove 'P-' prefix: show explicit soa_number if provided, otherwise patient id
    try{
      const soaNum = (payments[0] && payments[0].soa_number) ? payments[0].soa_number : (patient.patient_id ? String(patient.patient_id) : '');
      document.getElementById('soaNumber').textContent = soaNum;
    }catch(e){ /* ignore */ }
    document.getElementById('soaIssued').textContent = new Date().toLocaleDateString();
    const dueEl = document.getElementById('soaDue'); if(dueEl) dueEl.textContent = 'Dec 02, 2025';
    // Determine SOA status: if patient has relevant appointments and all are covered by a paid+verified receipt, mark Paid
    try{
      const soaStatusEl = document.getElementById('soaStatus');
      let soaPaid = false;
      try{
        const apptRes = await fetch('get_appointments.php', { credentials: 'same-origin' });
        if(apptRes.ok){
          const aj = await apptRes.json().catch(()=>null);
          if(aj && aj.success && Array.isArray(aj.appointments)){
            const appts = aj.appointments.filter(a => (typeof a.visible === 'undefined' || Number(a.visible) === 1) && !(String(a.status||'').toLowerCase().includes('cancel')));
            if(appts.length > 0){
              const allPaid = appts.every(a => {
                const aid = String(a.id || a.appointment_id || a.appointmentId || '');
                if(!aid) return false;
                return payments.some(p => (String(p.appointment_id || p.appointmentId || '') === aid && Number(p.paid) === 1 && Number(p.verified) === 1));
              });
              soaPaid = allPaid;
            }
          }
        }
      }catch(e){ console.error('SOA appointment check failed', e); }
      // Also consider receipts: if there are payments and all receipts are paid+verified, mark Paid
      try{
        const receiptsAllVerifiedPaid = (Array.isArray(payments) && payments.length > 0 && payments.every(p => Number(p.paid) === 1 && Number(p.verified) === 1));
        // also compute totals: if totalDue <= 0, consider Paid
        const subtotal = items.reduce((s,i)=> s + (i.amount||0), 0);
        const paymentsSum = items.reduce((s,i)=> s + (i.paid||0), 0);
        const totalDue = +(subtotal - paymentsSum).toFixed(2);
        if(receiptsAllVerifiedPaid || totalDue <= 0) soaPaid = true;
      }catch(e){ /* ignore */ }
      if(soaPaid) soaStatusEl.textContent = 'Paid'; else soaStatusEl.textContent = 'Unpaid';
    }catch(e){ try{ document.getElementById('soaStatus').textContent = 'Unpaid'; }catch(_){} }

    // wire buttons (guarded) — buttons may be removed from UI
    const soaUploadBtnEl = document.getElementById('soaUploadBtn');
    if(soaUploadBtnEl){
      soaUploadBtnEl.onclick = ()=>{ switchPanel('payment'); setTimeout(()=>{ try{ document.getElementById('chooseFileBtn')?.focus(); }catch(e){} },120); };
    }
    const soaPayBtnEl = document.getElementById('soaPayBtn');
    if(soaPayBtnEl){
      soaPayBtnEl.onclick = ()=>{ // open QR in payment panel
        switchPanel('payment'); setTimeout(()=>{ document.getElementById('openGcashBtn')?.click(); },200);
      };
    }
    // PDF download button removed; no-op left intentionally
  }catch(err){ console.error('loadSOA failed', err); }
}

function updateSOATotals(items){
  const subtotal = items.reduce((s,i)=> s + (i.amount||0), 0);
  const payments = items.reduce((s,i)=> s + (i.paid||0), 0);
  const discount = 0;
  const tax = 0;
  const totalDue = +(subtotal - payments - discount).toFixed(2);
  const fmt = v => '₱' + Number(v||0).toFixed(2);
  document.getElementById('soaSubtotal').textContent = fmt(subtotal);
  document.getElementById('soaDiscount').textContent = fmt(discount);
  // soaTax element removed; update if present
  const taxEl = document.getElementById('soaTax'); if(taxEl) taxEl.textContent = fmt(tax);
  document.getElementById('soaPayments').textContent = fmt(payments);
  document.getElementById('soaTotalDue').textContent = fmt(totalDue);
}

function viewReceipt(url) {
  // Show receipt preview modal (or open in new tab if modal not present)
  try{
    const modal = document.getElementById('receiptViewModal');
    const img = document.getElementById('modalReceiptImage');
    const dl = document.getElementById('receiptDownloadLink');
    if(!modal || !img){ window.open(url, '_blank'); return; }

    // Best-effort normalize bare filenames into uploads path
    try{
      const s = String(url || '');
      const isAbsolute = /^(https?:)?\/\//i.test(s) || s.startsWith('/');
      const looksLikeBare = !!s && !isAbsolute && s.indexOf('/') === -1;
      if(looksLikeBare){
        const scriptDir = (window.location && window.location.pathname) ? window.location.pathname.replace(/\/[^/]*$/, '') : '';
        url = (scriptDir ? scriptDir + '/' : '/') + 'assets/uploads/results_uploaded/' + s;
        url = url.replace(/\/+/g,'/');
      }
    }catch(_){ }

    img.src = url;
    if(dl){ dl.href = url; dl.setAttribute('download', ''); }
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    try{ const btn = document.getElementById('receiptViewDone'); if(btn) btn.focus(); }catch(e){}
  }catch(e){ window.open(url, '_blank'); }
}

function closeReceiptModal() {
  const modal = document.getElementById('receiptViewModal');
  if (!modal) return;
  
  modal.style.display = 'none';
  modal.setAttribute('aria-hidden', 'true');
  document.getElementById('modalReceiptImage').src = '';
}

async function deleteReceipt(id) {
  if (!confirm('Are you sure you want to delete this receipt? This cannot be undone.')) return;
  
  try {
    const fd = new FormData();
    fd.append('id', id);
    
    const res = await fetch('delete_payment_receipt.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    });
    
    const data = await res.json();
    if (!data.success) {
      throw new Error(data.message || 'Failed to delete receipt');
    }
    
    // Refresh the receipts list
    await loadReceipts();
  } catch (err) {
    console.error('Error deleting receipt:', err);
    alert('Failed to delete receipt: ' + (err.message || 'Unknown error'));
  }
}

// load receipts initially
loadReceipts();

/* ensure appointments load when page opens on appointments panel via nav click */
document.querySelectorAll('[data-panel="appointments"]').forEach(n => n.addEventListener('click', loadAppointments));

/* client-side placeholder for preloaded data (no server injection) */
window.serverPatientData = {};

</script>
<script>
// Profile dropdown toggle and outside-click handling
const profileBtn = document.getElementById('profileBtn');
const profileDropdown = document.getElementById('profileDropdown');
function toggleProfileDropdown(show){
  const doShow = (typeof show === 'boolean') ? show : (profileDropdown.style.display === 'none' || !profileDropdown.style.display);
  profileDropdown.style.display = doShow ? 'block' : 'none';
  profileBtn.setAttribute('aria-expanded', doShow ? 'true' : 'false');
  profileDropdown.setAttribute('aria-hidden', doShow ? 'false' : 'true');

  // When opening the dropdown, fetch the latest patient info and populate header
  if(doShow){
    try{
      if(typeof loadPatientDetails === 'function'){
        loadPatientDetails().then(d => {
          if(!d) return;
          try{
            const hdrName = document.getElementById('hdrName');
            const hdrAvatar = document.getElementById('hdrAvatarSmall');
            const pdSub = document.querySelector('.profile-dropdown .pd-sub');
            if(hdrName) hdrName.textContent = d.name || (d.patient_name || 'Profile');
            if(hdrAvatar && (d.avatar_url || d.photo)) hdrAvatar.src = d.avatar_url || d.photo;
            if(pdSub) pdSub.textContent = d.email || d.mobile_number || d.cellphone || 'Manage your account';
          }catch(e){}
        }).catch(()=>{/* ignore load errors */});
      }
    }catch(e){}
  }
}
if(profileBtn){
  profileBtn.addEventListener('click', function(e){ e.stopPropagation(); toggleProfileDropdown(); });
}
// close dropdown when clicking outside
document.addEventListener('click', function(e){
  const tgt = e.target;
  if(!profileDropdown) return;
  if(profileDropdown.style.display === 'block'){
    if(!profileDropdown.contains(tgt) && !profileBtn.contains(tgt)){
      toggleProfileDropdown(false);
    }
  }
});
// close on escape
document.addEventListener('keydown', function(e){ if(e.key === 'Escape'){ toggleProfileDropdown(false); } });
</script>
<script>
// Theme popover + sidebar-only theme handling (supports 'light' | 'dark' | 'auto')
const themeToggle = document.getElementById('themeToggle');
const themePopover = document.getElementById('themePopover');
let _sidebarPrefListener = null;
function applySidebarTheme(mode){
  try{
    // mode: 'light' | 'dark' | 'auto'
    const sidebar = document.querySelector('nav.sidebar');
    if(!sidebar) return;
    // cleanup previous listener
    if(_sidebarPrefListener && window.matchMedia){
      window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', _sidebarPrefListener);
      _sidebarPrefListener = null;
    }

    function setByPref(prefIsDark){
      if(prefIsDark) sidebar.classList.add('sidebar-dark'); else sidebar.classList.remove('sidebar-dark');
    }

    if(mode === 'dark'){
      sidebar.classList.add('sidebar-dark');
      themeToggle.textContent = '☀️';
    } else if(mode === 'light'){
      sidebar.classList.remove('sidebar-dark');
      themeToggle.textContent = '🌙';
    } else { // auto
      const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      setByPref(prefersDark);
      themeToggle.textContent = '◐';
      if(window.matchMedia){
        _sidebarPrefListener = function(e){ setByPref(e.matches); };
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', _sidebarPrefListener);
      }
    }

    // update popover active state
    try{
      if(themePopover){
        Array.from(themePopover.querySelectorAll('.tp-item')).forEach(it => {
          it.classList.toggle('active', it.dataset.mode === mode);
        });
        themePopover.setAttribute('aria-hidden', themePopover.style.display === 'none' ? 'true' : 'false');
      }
    }catch(e){}

    // ensure layout margin accounts for fixed sidebar on wider screens
    const layout = document.querySelector('.layout');
    if(layout){
      if(window.innerWidth > 980){ layout.classList.add('has-fixed-sidebar'); }
      else { layout.classList.remove('has-fixed-sidebar'); }
    }
  }catch(e){ console.error(e); }
}

// initialize from localStorage (supports 'light'|'dark'|'auto')
try{
  const saved = localStorage.getItem('drea_sidebar_theme') || 'auto';
  applySidebarTheme(saved);
}catch(e){ applySidebarTheme('auto'); }

// show/hide popover on toggle click
if(themeToggle){
  themeToggle.addEventListener('click', function(e){
    if(!themePopover) return;
    const visible = themePopover.style.display === 'block';
    themePopover.style.display = visible ? 'none' : 'block';
    themePopover.setAttribute('aria-hidden', visible ? 'true' : 'false');
    e.stopPropagation();
  });
}

// click handler for popover items
if(themePopover){
  themePopover.addEventListener('click', function(e){
    const el = e.target.closest('.tp-item');
    if(!el) return;
    const mode = el.dataset.mode || 'auto';
    try{ localStorage.setItem('drea_sidebar_theme', mode); }catch(err){}
    applySidebarTheme(mode);
    // hide popover after selection
    themePopover.style.display = 'none';
    themePopover.setAttribute('aria-hidden','true');
  });
}

// close popover when clicking outside
document.addEventListener('click', function(e){
  if(!themePopover) return;
  if(themePopover.style.display === 'block'){
    if(!themePopover.contains(e.target) && e.target !== themeToggle){
      themePopover.style.display = 'none';
      themePopover.setAttribute('aria-hidden','true');
    }
  }
});

// keep layout margin in sync when resizing
window.addEventListener('resize', function(){ const layout=document.querySelector('.layout'); if(layout){ if(window.innerWidth>980) layout.classList.add('has-fixed-sidebar'); else layout.classList.remove('has-fixed-sidebar'); } });
</script>
<!-- html2pdf (client-side PDF generator) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- Receipt preview modal -->
<div id="receiptViewModal" class="confirm-modal" aria-hidden="true" style="display:none">
  <div class="dialog" role="dialog" aria-modal="true" style="max-width:900px;width:92%;height:80%;">
    <header style="display:flex;justify-content:space-between;align-items:center">
      <h4 style="margin:0;color:var(--lav-4)">Receipt Preview</h4>
      <button class="btn-cancel" type="button" onclick="closeReceiptModal()">Close</button>
    </header>
    <div style="margin-top:12px;height:calc(100% - 110px);display:flex;align-items:center;justify-content:center;background:#fff;padding:8px;border-radius:8px;">
      <img id="modalReceiptImage" src="" alt="Receipt" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:6px;" />
    </div>
    <footer style="display:flex;justify-content:flex-end;gap:8px;margin-top:12px">
      <a id="receiptDownloadLink" class="btn-pill small" href="#" download>Download</a>
      <button id="receiptViewDone" class="btn" type="button" onclick="closeReceiptModal()">Done</button>
    </footer>
  </div>
</div>

<script>
// Photoshoot gallery JS helpers
(function(){
  const images = ['assets/images/pic1.jpg','assets/images/pic2.jpg','assets/images/pic3.jpg'];
  let idx = 0;
  function open(index){ try{ idx = Number(index) || 0; const modal = document.getElementById('photoshootModal'); const img = document.getElementById('photoshootImg'); if(!modal || !img) return; img.src = images[idx] || ''; modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false'); }catch(e){console.error(e);} }
  function close(){ try{ const modal = document.getElementById('photoshootModal'); const img = document.getElementById('photoshootImg'); if(modal){ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); } if(img) img.src=''; }catch(e){console.error(e);} }
  function next(){ try{ idx = (idx+1) % images.length; const img = document.getElementById('photoshootImg'); if(img) img.src = images[idx]; }catch(e){console.error(e);} }
  function prev(){ try{ idx = (idx-1+images.length) % images.length; const img = document.getElementById('photoshootImg'); if(img) img.src = images[idx]; }catch(e){console.error(e);} }

  window.openPhotoshootModal = open;
  window.closePhotoshootModal = close;
  window.nextPhotoshootImage = next;
  window.prevPhotoshootImage = prev;

  // close on outside click
  document.addEventListener('click', function(e){ const modal = document.getElementById('photoshootModal'); if(modal && modal.style.display === 'flex' && e.target === modal) close(); });
  // keyboard navigation
  document.addEventListener('keydown', function(e){ const modal = document.getElementById('photoshootModal'); if(!modal || modal.style.display !== 'flex') return; if(e.key === 'Escape') close(); if(e.key === 'ArrowRight') next(); if(e.key === 'ArrowLeft') prev(); });
})();
</script>

</body>
</html>