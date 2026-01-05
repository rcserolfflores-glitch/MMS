<?php
session_start();

// If user is logged in, redirect to appropriate portal
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_type']) {
        case 'patient':
            header("Location: patient_portal.php");
            break;
        case 'doctor':
            header("Location: doctor_portal.php");
            break;
        case 'midwife':
          header("Location: midwife_portal.php");
          break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Androcie Bagtas Lying-in Clinic</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Hero slider
      let index = 0;
      const slides = document.querySelectorAll('.hero-slide');
      const total = slides.length;
      setInterval(() => {
        slides[index].classList.remove('active');
        index = (index + 1) % total;
        slides[index].classList.add('active');
      }, 5000);
    });
  </script>

  <style>
    :root {
      --color-primary: #18578f;   /* Blue */
      --color-accent: #c2185b;    /* Pink */
      --color-light: #f8f9fb;
      --color-dark: #222;
      --font-family: 'Poppins', sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--font-family);
      background: var(--color-light);
      color: var(--color-dark);
      overflow-x: hidden;
    }
:root {
  --color-primary: #ffffff;  /* header text color (white for contrast) */
  --color-accent: #9c7de8;   /* Lighter lavender for hover/buttons */
  --header-bg: linear-gradient(90deg,#2b1b4f,#3b2c65); /* darker gradient */
}

/* HEADER STYLES */
header {
  position: fixed;
  top: 0; left: 0; right: 0;
  background: var(--header-bg);
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2.5rem;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
  z-index: 1000;
  flex-wrap: wrap;
}

header { color: var(--color-primary); }

/* LEFT SIDE: LOGO & TEXT */
.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo img {
  width: 110px; /* Larger logo */
  height: 110px;
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.logo img:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* Text block next to logo */
.clinic-text {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* Main clinic name */
.clinic-name {
  color: #fff;
  font-size: 1.9rem;
  font-weight: 700;
  line-height: 1.2;
}

/* Subtext */
.clinic-subtext {
  color: rgba(255,255,255,0.9);
  font-size: 0.95rem;
  font-weight: 500;
  opacity: 0.8;
  margin-top: 4px;
}

/* NAVIGATION */
nav {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn-nav {
  text-decoration: none;
  color: #fff;
  font-weight: 600;
  padding: 0.45rem 0.9rem;
  border-radius: 8px;
  transition: background 0.25s ease, transform 0.15s ease;
}

.btn-nav:hover {
  background: rgba(255,255,255,0.08);
  color: #fff;
}

/* RIGHT SIDE: BUTTON */
.header-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.book-now-btn {
  display: inline-block;
  background: linear-gradient(180deg,#9c7de8,#7c5ee0);
  color: #fff;
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  padding: 10px 26px;
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 28px;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.2s ease;
  box-shadow: 0 6px 18px rgba(124,94,224,0.18);
}

.book-now-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 22px rgba(124,94,224,0.22);
}


/* RESPONSIVE */
@media(max-width:900px){
  header {
    flex-direction: column;
    padding: 1rem 1.5rem;
    gap: 1rem;
  }

  .header-left {
    justify-content: center;
    gap: 0.5rem;
  }

  nav {
    justify-content: center;
    gap: 0.5rem;
  }

  .header-right {
    justify-content: center;
  }

  .logo img {
    width: 90px;
    height: 90px;
  }

  .clinic-name {
    font-size: 1.6rem;
  }

  .clinic-subtext {
    font-size: 0.85rem;
  }
}


    /* Ensure Book Now keeps visible text on hover/focus (remove conflicting rule) */
    .book-now-btn:focus,
    .book-now-btn:hover {
      color: #fff;
      background: linear-gradient(180deg,#8e6de0,#6f50c9);
      transform: translateY(-2px);
      box-shadow: 0 10px 22px rgba(111,80,201,0.22);
    }

    /* HERO SECTION */
    .hero {
      position: relative;
      width: 100%;
      height: 85vh;
      overflow: hidden;
      margin-top: 80px;
    }
    .hero-slide {
      position: absolute;
      width: 100%; height: 100%;
      object-fit: cover;
      opacity: 0;
      transition: opacity 1.2s ease-in-out;
    }
    .hero-slide.active { opacity: 1; }
    .hero-overlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.35);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      color: white;
      padding: 1rem;
    }
    .hero-overlay h1 { font-size: 2.5rem; max-width: 700px; margin-bottom: 1rem; }
    .hero-overlay p { max-width: 600px; margin-bottom: 1.5rem; font-size: 1.2rem; }
    .hero-overlay .cta-btn {
      display: inline-block;
      background: linear-gradient(180deg,#9c7de8,#7c5ee0);
      color: #fff;
      text-decoration: none;
      padding: 0.75rem 1.5rem;
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 28px;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: transform 0.15s ease, box-shadow 0.2s ease, background 0.2s ease;
      box-shadow: 0 6px 18px rgba(124,94,224,0.18);
    }
    .hero-overlay .cta-btn:focus,
    .hero-overlay .cta-btn:hover {
      color: #fff;
      background: linear-gradient(180deg,#8e6de0,#6f50c9);
      transform: translateY(-2px);
      box-shadow: 0 10px 22px rgba(111,80,201,0.22);
    }

    /* SECTIONS */
    section {
      max-width: 1100px;
      margin: 4rem auto;
      padding: 0 1rem;
      text-align: center;
    }
    section h2 {
      color: var(--color-primary);
      font-size: 2rem;
      margin-bottom: 1.5rem;
    }
    section p {
      max-width: 800px;
      margin: 0 auto;
      line-height: 1.7;
      font-size: 1rem;
    }

    /* FEATURE CARDS */
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-top: 2rem;
    }
    .feature-card {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      transition: 0.3s ease;
    }
    .feature-card:hover { transform: translateY(-5px); }
    .feature-card h4 { margin-top: 1rem; color: var(--color-accent); }
   /* About Section */
.about-section {
  background: linear-gradient(135deg, #f5effc, #ede4f9);  /* Soft lavender gradient */
  padding: 6rem 2rem;
  color: #3b2c65;
  text-align: center;
}

.about-section .container {
  max-width: 1000px;
  margin: 0 auto;
}

.about-section h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #4b2c91;
}

.about-section p {
  font-size: 1.1rem;
  line-height: 1.8;
  margin-bottom: 2rem;
  color: #5c4a9e;
}

    /* Verse styling: dark blue for the requested quote */
    .verse {
      color: #0b3d91; /* dark blue */
      font-style: italic;
      font-weight: 600;
      margin-top: 0.75rem;
      font-size: 1.05rem;
      line-height: 1.5;
    }

.about-cards {
  display: flex;
  justify-content: center;
  gap: 2rem;
  flex-wrap: wrap;
  margin-top: 2rem;
}

.about-card {
  background: white;
  padding: 2rem;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  flex: 1 1 300px;
  max-width: 450px;
  transition: transform 0.4s ease, box-shadow 0.4s ease, background 0.4s ease;
}

.about-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 28px rgba(0,0,0,0.15);
  background: #f7f0fc;  /* Slightly tinted background on hover */
}

.about-card .icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}

.about-card h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  color: #4b2c91;
}

.about-card p {
  font-size: 1rem;
  line-height: 1.6;
  color: #5c4a9e;
}

/* Responsive */
@media(max-width:768px) {
  .about-cards {
    flex-direction: column;
    gap: 1.5rem;
  }
}

    /* Additional responsive tweaks for laptops and mobile */
    @media (max-width: 1200px) {
      body { font-size: 15px; }
      header { padding: 0.9rem 1.5rem; }
      .clinic-name { font-size: 1.6rem; }
      .hero-overlay h1 { font-size: 2.1rem; }
      .hero-overlay p { font-size: 1.05rem; }
      .book-now-btn { padding: 12px 26px; font-size: 16px; }
      section { margin: 3rem auto; }
      .schedule-grid { gap: 1rem; }
    }

    @media (max-width: 900px) {
      .hero { height: 60vh; margin-top: 120px; }
      .hero-overlay { padding: 1rem 1.25rem; }
      .hero-overlay h1 { font-size: 1.6rem; line-height:1.2; }
      .hero-overlay p { font-size: 1rem; }
      header { padding: 0.8rem 1rem; }
      .logo img { width: 76px; height: 76px; }
      .clinic-name { font-size: 1.2rem; }
      nav { order: 3; width: 100%; justify-content: center; }
      .header-right { order: 2; }
      .book-now-btn { padding: 10px 18px; font-size: 15px; }
      section { margin: 2rem auto; padding: 0 0.75rem; }
      .clinic-card iframe { height: 240px; }
      .clinic-card { padding: 18px; }
      .about-section { padding: 3.5rem 1rem; }
      #contact { padding: 40px 12px; }
    }

    @media (max-width: 480px) {
      .hero { height: 50vh; margin-top: 110px; }
      .hero-overlay h1 { font-size: 1.25rem; }
      .hero-overlay p { font-size: 0.95rem; }
      .clinic-name { font-size: 1.05rem; }
      .clinic-subtext { font-size: 0.8rem; }
      .logo img { width: 64px; height: 64px; }
      .book-now-btn { padding: 9px 14px; font-size: 14px; border-radius: 24px; }
      .schedule-grid { grid-template-columns: 1fr; gap: 0.85rem; }
      .feature-card { padding: 1.25rem; }
      footer { padding: 16px 8px; font-size: 13px; }
      .back-to-top { right: 12px; bottom: 12px; padding: 10px 16px; }
    }
 

/* Schedules Section */
.schedules-section {
  background: linear-gradient(135deg, #f8f5fc, #ece1f9); /* soft lavender */
  padding: 6rem 2rem;
  color: #3b2c65;
  text-align: center;
}

.schedules-section .container {
  max-width: 1400px;
  margin: 0 auto;
}

.schedules-section h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #4b2c91;
}

.schedules-section p {
  font-size: 1.1rem;
  margin-bottom: 3rem;
  color: #5c4a9e;
}

/* Schedule Grid */
.schedule-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr); /* 4 columns */
  gap: 2rem;
  justify-items: center; /* center the cards horizontally */
}

.schedule-card {
  background: white;
  padding: 2rem 1.5rem;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  transition: transform 0.4s ease, box-shadow 0.4s ease, background 0.4s ease;
  text-align: center; /* center text inside card */
  width: 100%;
}

.schedule-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 28px rgba(0,0,0,0.15);
  background: #f7f0fc;
}

.schedule-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  color: #4b2c91;
}

.schedule-card p {
  font-size: 1rem;
  line-height: 1.6;
  color: #5c4a9e;
}

/* Responsive */
@media(max-width:1200px) {
  .schedule-grid {
    grid-template-columns: repeat(2, 1fr); /* 2 columns for medium screens */
  }
}

@media(max-width:768px) {
  .schedule-grid {
    grid-template-columns: 1fr; /* single column for small screens */
  }
}
/* FAQs Section */
#faqs {
  background: #f4f0fa; /* soft lavender background */
  padding: 6rem 2rem;
  max-width: 900px;
  margin: 0 auto;
  font-family: 'Inter', sans-serif;
}

#faqs h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 3rem;
  text-align: center;
  color: #4b2c91;
  letter-spacing: 1px;
}

/* FAQ Items */
.faq-item {
  background: #ffffff;
  margin-bottom: 1rem;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.07);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.faq-item:nth-child(even) {
  background: #f9f5fc; /* alternating background */
}

.faq-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(0,0,0,0.12);
}

/* Hide checkboxes */
.faq-item input[type="checkbox"] {
  display: none;
}

/* Question Label */
.faq-item label {
  display: block;
  padding: 1.5rem 2rem;
  font-weight: 600;
  font-size: 1.15rem;
  cursor: pointer;
  color: #4b2c91;
  line-height: 1.4;
  text-align: left; /* align left */
  position: relative;
  transition: background 0.3s ease;
}

.faq-item label:hover {
  background: #ede6f8;
}

/* Arrow Indicator */
.faq-item label::after {
  content: "▸";
  position: absolute;
  right: 2rem;
  font-size: 1.2rem;
  transition: transform 0.3s ease, color 0.3s ease;
}

/* Rotate arrow when checked */
.faq-item input[type="checkbox"]:checked + label::after {
  transform: rotate(90deg);
  color: #9b59b6;
}

/* Answer hidden by default */
.faq-item p {
  max-height: 0;
  overflow: hidden;
  padding: 0 2rem;
  margin: 0;
  font-size: 1rem;
  line-height: 1.8;
  color: #5c4a9e;
  text-align: left; /* align text left */
  transition: max-height 0.4s ease, padding 0.4s ease;
}

/* Show answer when checked */
.faq-item input[type="checkbox"]:checked ~ p {
  max-height: 500px;
  padding: 1rem 2rem 1.5rem;
}

/* Responsive */
@media(max-width:768px) {
  #faqs h2 {
    font-size: 2rem;
  }
  .faq-item label {
    font-size: 1.1rem;
    padding: 1rem 1.5rem;
  }
  .faq-item p {
    padding: 0 1.5rem;
  }
}


/* Contact Section - Lavender Theme */
#contact {
  padding: 60px 20px;
  background-color: #f4efff; /* soft lavender background */
  text-align: center;
}

#contact h2 {
  font-size: 2.2rem;
  margin-bottom: 30px;
  color: #6a4caf; /* deep lavender text */
  letter-spacing: 1px;
  font-weight: 700;
}

.clinic-card {
  background: #ffffff;
  max-width: 850px;
  margin: 20px auto;
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(106, 76, 175, 0.2);
  text-align: left;
  transition: transform 0.2s ease, box-shadow 0.3s ease;
}

.clinic-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 16px rgba(106, 76, 175, 0.25);
}

.clinic-card h3 {
  font-size: 1.6rem;
  color: #6a4caf;
  margin-bottom: 8px;
}

.clinic-card .branch-label {
  font-size: 0.9rem;
  color: #8577b6;
  font-weight: 500;
}

.clinic-card p {
  font-size: 1rem;
  color: #333;
  margin: 8px 0;
}

.clinic-card a {
  color: #8b5cf6; /* bright lavender accent */
  text-decoration: none;
  font-weight: 600;
}

.clinic-card a:hover {
  text-decoration: underline;
  color: #7c3aed;
}

/* Map Styling */
.clinic-card iframe {
  margin-top: 15px;
  border-radius: 12px;
  height: 350px;
  width: 100%;
  border: 2px solid #d8c9ff;
}


/* Back to Top Button - matching Book Now style */
.back-to-top {
  position: fixed;
  bottom: 25px;
  right: 25px;
  background-color: #9c7de8;       /* same theme color as Book Now */
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  padding: 12px 25px;
  border: none;
  border-radius: 30px;            /* pill shape */
  text-decoration: none;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
  transition: all 0.3s ease;
  cursor: pointer;
}

.back-to-top:hover {
  background-color: #9c7de8;     /* darker on hover */
  transform: translateY(-3px);    /* smooth lift */
}

.back-to-top:active {
  transform: translateY(1px);
}

    /* Skip link (accessibility) */
    .skip-link {
      position: absolute;
      left: -999px;
      top: auto;
      width: 1px;
      height: 1px;
      overflow: hidden;
    }
    .skip-link:focus {
      left: 10px;
      top: 10px;
      width: auto;
      height: auto;
      padding: 8px 12px;
      background: #fff;
      color: var(--color-primary);
      border-radius: 6px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.12);
      z-index: 2000;
    }

    /* Focus-visible for interactive elements */
    a:focus, button:focus {
      outline: 3px solid rgba(148,109,246,0.25);
      outline-offset: 3px;
    }

    /* Simple fade-in animation used by cards */
    .fade-in {
      animation: fadeInUp 0.7s ease both;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

/* Footer */
footer {
  background: var(--header-bg);
  text-align: center;
  padding: 20px 10px;
  font-size: 14px;
  color: #fff;
  font-family: 'Inter', sans-serif;
  box-shadow: 0 -6px 20px rgba(0,0,0,0.25);
}
footer i {
  color: #fff;
  margin-right: 5px;
}


  </style>
</head>

<body>
  <a class="skip-link" href="#main">Skip to content</a>
  <a id="top"></a>
  
<!-- HEADER -->
<header>
  <div class="header-left">
    <div class="logo">
      <img src="assets/images/logodrea.jpg" alt="Clinic Logo">
    </div>
    <div class="clinic-text">
      <div class="clinic-name">DREA LYING-IN CLINIC</div>
      <div class="clinic-subtext">Maternity Management System</div>
    </div>
  </div>

  <nav>
    <a class="btn-nav" href="#hero">Home</a>
    <a class="btn-nav" href="#about">About Us</a>
    <a class="btn-nav" href="#schedules">Services & Schedule</a>
    <a class="btn-nav" href="#faqs">FAQs</a>
   
    <a class="btn-nav" href="#contact">Contact Us</a>
  </nav>

  <div class="header-right">
    <a class="book-now-btn" href="sign-up_process.php">Book Now</a>
  </div>
</header>

<main id="main" tabindex="-1">


  <!-- HERO -->
  <div class="hero" id="hero">
    <img src="assets/images/carousel3.jpeg" class="hero-slide active" alt="Clinic 1">
    <img src="assets/images/carousel4.jpg" class="hero-slide" alt="Clinic 2">
    <img src="assets/images/carousel5.jpg" class="hero-slide" alt="Clinic 3">
    <div class="hero-overlay">
      <h1>Trusted, Compassionate Maternal Care</h1>
      <p>We make childbirth a safe and empowering experience for every mother.</p>
      <button class="cta-btn" onclick="window.location.href='sign-up_process.php'">Book an Appointment</button>
    </div>
  </div>

<!-- About Us Section -->
<section id="about" class="about-section">
  <div class="container">
    <h2>About Us</h2>
    <p>
      Welcome to <strong>Drea Lying-in Clinic</strong>, founded in September 2024 by Andrea Aguirre, your trusted partner in maternity and newborn care located in Biga, Tanza, Cavite. Our clinic is equipped with modern facilities and staffed by experienced midwives, nurses, and healthcare professionals dedicated to ensuring safe deliveries and compassionate care.
    </p>
    <p class="verse">
      But because of his great love for us, God, who is rich in MERCY, made us alive with Christ even when we were dead in transgressions— it is by grace you have been saved.
      <br>&ndash; Ephesians 2:4
    </p>

    <div class="about-cards">
      <div class="about-card fade-in">
        <div class="icon">🎯</div>
        <h3>Our Mission</h3>
        <p>
          Our mission is to offer exceptional prenatal and children services with safe, comfortable, and personalized care for every patient we serve.
        </p>
      </div>
      <div class="about-card fade-in">
        <div class="icon">🌟</div>
        <h3>Our Vision</h3>
        <p>
          Our vision is to be the leading provider of compassionate and comprehensive prenatal care, ensuring safe and joyful childbirth experiences for all families.
        </p>
      </div>
    </div>
  </div>
</section>


<!-- SCHEDULES SECTION -->
<section id="schedules" class="schedules-section">
  <div class="container">
    <h2>Clinic Schedules</h2>
    <p>Check our services and their available days and hours.</p>

    <div class="schedule-grid">
      <div class="schedule-card">
        <h3>NST (Normal Spontaneous Delivery)</h3>
        <p>Monday to Sunday: 24 hours</p>
      </div>
      <div class="schedule-card">
        <h3>Midwife Checkup</h3>
        <p>Monday to Sunday: 9 a.m. – 5 p.m.</p>
      </div>
      <div class="schedule-card">
        <h3>OB-GYN Consultation</h3>
        <p>Monday only: 11 a.m. – 1 p.m.</p>
      </div>
      <div class="schedule-card">
        <h3>Pedia Checkup</h3>
        <p>Tuesday only: 3 p.m. – 5 p.m.</p>
      </div>
      <div class="schedule-card">
        <h3>Ultrasound</h3>
        <p>Monday to Thursday: 7 a.m. – 3 p.m.</p>
      </div>
      <div class="schedule-card">
        <h3>Ear Piercing to Pregnancy Test</h3>
        <p>Monday to Sunday: 9 a.m. – 5 p.m.</p>
      </div>
      <div class="schedule-card">
        <h3>Newborn Screening</h3>
        <p>Monday to Sunday: 24 hours (Processing at birth)</p>
      </div>
      <div class="schedule-card">
        <h3>Family Planning</h3>
        <p>Monday to Sunday: 9 a.m. – 5 p.m.</p>
      </div>
    </div>
  </div>
</section>


<!-- FAQs Section -->
<section id="faqs">
  <h2>Frequently Asked Questions</h2>

  <!-- FAQ 1 -->
  <div class="faq-item">
    <input type="checkbox" id="faq1">
    <label for="faq1">1. Do I need to book an appointment before coming to the clinic?</label>
    <p>
      We highly recommend booking an appointment for checkups and consultations. Walk-in patients are also accommodated based on availability.
    </p>
  </div>

  <!-- FAQ 2 -->
  <div class="faq-item">
    <input type="checkbox" id="faq2">
    <label for="faq2">2. Which services are available 24 hours?</label>
    <p>
      Our Non-Stress Test (NST) and Newborn Screening services are available 24 hours a day for your convenience.
    </p>
  </div>

  <!-- FAQ 3 -->
  <div class="faq-item">
    <input type="checkbox" id="faq3">
    <label for="faq3">3. Does the clinic accept PhilHealth benefits?</label>
    <p>
      Yes, Drea Lying-in Clinic accepts PhilHealth maternity benefits. Our staff will guide you through the required documentation.
    </p>
  </div>

  <!-- FAQ 4 -->
  <div class="faq-item">
    <input type="checkbox" id="faq4">
    <label for="faq4">4. Where is the clinic located?</label>
    <p>
      We are located in Biga, Tanza, Cavite, easily accessible for residents of surrounding communities.
    </p>
  </div>

  <!-- FAQ 5 -->
  <div class="faq-item">
    <input type="checkbox" id="faq5">
    <label for="faq5">5. What should I bring for my first prenatal visit?</label>
    <p>
      Please bring your valid ID, any relevant medical records, and your PhilHealth card if applicable.
    </p>
  </div>

</section>



<!-- Contact Us Section -->
<section id="contact">
  <h2>Contact Us</h2>

  <!-- Main Branch -->
  <div class="clinic-card">
    <h3>Drea Lying-In Clinic</h3>
    <p>📍 Biga, Tanza, Cavite</p>
    <p>📞 Contact: 0947-654-7123</p>
    <p>🌐 Facebook: 
      <a href="https://www.facebook.com/people/Drea-Lying-In/61577127400407/"
         target="_blank">facebook.com/drea-lying-in</a>
    </p>
    <iframe
      src="https://www.google.com/maps?q=Drea+Lying-In+Clinic+Biga+Tanza+Cavite&output=embed"
      width="100%"
      height="300"
      style="border:0;"
      allowfullscreen=""
      loading="lazy">
    </iframe>
  </div>

  <!-- Branch -->
  <div class="clinic-card branch">
    <h3>Androcie Bagtas Lying-In Clinic <span class="branch-label">(Branch)</span></h3>
    <p>📍 Bagtas, Tanza, Cavite</p>
    <p>📞 Contact: 0921-887-0900</p>
    <p>🌐 Facebook: 
      <a href="https://www.facebook.com/androcie.bagtas.lying.in/"
         target="_blank">facebook.com/androcie.bagtas.lying.in</a>
    </p>
    <iframe
  src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d772.2164269605576!2d120.8507886!3d14.3354634!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33962ba76b19a2b5%3A0xcc22fe3e48f43b33!2sANDROCIE+Bagtas+Lying-In+Clinic!5e0!3m2!1sen!2sph!4v1739712456829!5m2!1sen!2sph"
  width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy">
</iframe>
  </div>
</section>

<!-- Back to Top Button -->
<a href="#top" class="back-to-top">Back to Top</a>


<!-- FOOTER -->
  </main>

  <footer>
  <i class="fa-regular fa-copyright"></i> 
  2025 Drea Lying-In Clinic | All Rights Reserved
</footer>

<!-- Font Awesome for footer icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


