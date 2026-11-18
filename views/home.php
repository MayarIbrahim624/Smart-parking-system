<?php
// views/home.php - Landing page
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ParkSmart – Smart Urban Parking</title>
<link rel="icon" href="https://img.icons8.com/color/48/000000/parking.png">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0a0e1a;
    --surface: #111827;
    --card: #161d2e;
    --accent: #00d4ff;
    --accent2: #7c3aed;
    --green: #10b981;
    --orange: #f59e0b;
    --red: #ef4444;
    --text: #f1f5f9;
    --muted: #64748b;
    --border: rgba(255,255,255,0.07);
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    overflow-x: hidden;
  }

  /* ── ANIMATED BACKGROUND ── */
  .bg-grid {
    position: fixed; inset: 0; z-index: 0;
    background-image:
      linear-gradient(rgba(0,212,255,0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,212,255,0.03) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
  }
  .bg-glow {
    position: fixed; z-index: 0; pointer-events: none;
    border-radius: 50%; filter: blur(120px); opacity: 0.15;
  }
  .glow1 { width: 600px; height: 600px; background: var(--accent); top: -200px; left: -100px; animation: driftA 12s ease-in-out infinite alternate; }
  .glow2 { width: 400px; height: 400px; background: var(--accent2); bottom: 10%; right: -100px; animation: driftB 10s ease-in-out infinite alternate; }
  @keyframes driftA { to { transform: translate(80px, 80px); } }
  @keyframes driftB { to { transform: translate(-60px, -60px); } }

  /* ── NAVBAR ── */
  nav {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.2rem 4rem;
    background: rgba(10,14,26,0.85);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border);
  }
  .logo {
    font-family: 'Syne', sans-serif;
    font-size: 1.6rem; font-weight: 800;
    color: var(--accent);
    letter-spacing: -0.5px;
    display: flex; align-items: center; gap: 0.5rem;
    text-decoration: none;
  }
  .logo a { color: inherit; text-decoration: none; }
  .logo-icon { font-size: 1.4rem; }
  .nav-links { display: flex; align-items: center; gap: 2.5rem; }
  .nav-links a {
    color: var(--muted); text-decoration: none;
    font-size: 0.9rem; font-weight: 500;
    transition: color 0.2s;
  }
  .nav-links a:hover { color: var(--text); }
  .nav-cta {
    display: flex; gap: 0.8rem;
  }
  .btn {
    padding: 0.6rem 1.4rem; border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem; font-weight: 500;
    cursor: pointer; text-decoration: none;
    transition: all 0.2s; border: none;
    display: inline-flex; align-items: center; gap: 0.4rem;
  }
  .btn-ghost {
    background: transparent;
    color: var(--muted);
    border: 1px solid var(--border);
  }
  .btn-ghost:hover { color: var(--text); border-color: rgba(255,255,255,0.2); }
  .btn-primary {
    background: var(--accent);
    color: var(--bg);
    font-weight: 700;
  }
  .btn-primary:hover { background: #33deff; transform: translateY(-1px); box-shadow: 0 8px 25px rgba(0,212,255,0.3); }
  .btn-lg { padding: 0.9rem 2.2rem; font-size: 1rem; border-radius: 10px; }
  .btn-outline-accent {
    background: transparent;
    color: var(--accent);
    border: 1.5px solid var(--accent);
  }
  .btn-outline-accent:hover { background: rgba(0,212,255,0.1); }

  /* ── HERO ── */
  .hero {
    position: relative; z-index: 1;
    min-height: 100vh;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center;
    padding: 8rem 2rem 4rem;
  }
  .hero-badge {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: rgba(0,212,255,0.08);
    border: 1px solid rgba(0,212,255,0.2);
    padding: 0.45rem 1.1rem; border-radius: 50px;
    font-size: 0.8rem; color: var(--accent);
    margin-bottom: 2rem;
    animation: fadeUp 0.6s ease both;
  }
  .hero-badge span { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; animation: pulse 2s infinite; }
  @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(1.4)} }

  .hero h1 {
    font-family: 'Syne', sans-serif;
    font-size: clamp(3rem, 7vw, 6rem);
    font-weight: 800; line-height: 1.05;
    letter-spacing: -2px;
    animation: fadeUp 0.6s 0.1s ease both;
  }
  .hero h1 .accent { color: var(--accent); }
  .hero p {
    max-width: 560px; margin: 1.5rem auto 2.5rem;
    color: var(--muted); font-size: 1.1rem; line-height: 1.7;
    animation: fadeUp 0.6s 0.2s ease both;
  }
  .hero-cta {
    display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;
    animation: fadeUp 0.6s 0.3s ease both;
  }
  @keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

  /* ── SEARCH BAR ── */
  .search-section {
    position: relative; z-index: 1;
    max-width: 800px; margin: 0 auto;
    padding: 0 2rem 6rem;
    animation: fadeUp 0.6s 0.4s ease both;
  }
  .search-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px; padding: 2rem;
    box-shadow: 0 40px 80px rgba(0,0,0,0.4);
  }
  .search-tabs {
    display: flex; gap: 0.5rem; margin-bottom: 1.5rem;
  }
  .tab {
    padding: 0.5rem 1.2rem; border-radius: 8px;
    font-size: 0.88rem; font-weight: 500; cursor: pointer;
    color: var(--muted); background: transparent;
    border: 1px solid transparent; transition: all 0.2s;
  }
  .tab.active { background: var(--accent); color: var(--bg); font-weight: 700; }
  .tab:not(.active):hover { color: var(--text); border-color: var(--border); }
  .search-row {
    display: grid; grid-template-columns: 1fr 1fr 1fr auto;
    gap: 0.8rem; align-items: end;
  }
  .field label {
    display: block; font-size: 0.75rem; color: var(--muted);
    margin-bottom: 0.4rem; font-weight: 500; letter-spacing: 0.05em;
    text-transform: uppercase;
  }
  .field input, .field select {
    width: 100%; padding: 0.75rem 1rem;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 10px; color: var(--text);
    font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
    outline: none; transition: border-color 0.2s;
  }
  .field input:focus, .field select:focus { border-color: var(--accent); }
  .field input::placeholder { color: var(--muted); }
  .field select option { background: var(--surface); }

  /* ── STATS ── */
  .stats {
    position: relative; z-index: 1;
    display: flex; justify-content: center; gap: 4rem; flex-wrap: wrap;
    padding: 3rem 2rem 5rem;
    border-top: 1px solid var(--border);
  }
  .stat { text-align: center; }
  .stat-num {
    font-family: 'Syne', sans-serif;
    font-size: 2.8rem; font-weight: 800;
    color: var(--accent); line-height: 1;
  }
  .stat-label { color: var(--muted); font-size: 0.85rem; margin-top: 0.3rem; }

  /* ── AVAILABILITY GRID ── */
  .section { position: relative; z-index: 1; padding: 5rem 4rem; }
  .section-tag {
    font-size: 0.75rem; font-weight: 600; color: var(--accent);
    letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.8rem;
  }
  .section-title {
    font-family: 'Syne', sans-serif;
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800; line-height: 1.1;
    letter-spacing: -1px; margin-bottom: 1rem;
  }
  .section-sub { color: var(--muted); font-size: 1rem; max-width: 500px; line-height: 1.7; }

  .availability-layout {
    display: grid; grid-template-columns: 1fr 1.5fr;
    gap: 3rem; align-items: start; margin-top: 3rem;
  }
  .parking-map {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 16px; padding: 1.5rem; position: relative; overflow: hidden;
  }
  .map-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 1.2rem;
  }
  .map-title { font-family: 'Syne', sans-serif; font-size: 1rem; font-weight: 700; }
  .live-badge {
    display: flex; align-items: center; gap: 0.4rem;
    background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3);
    padding: 0.3rem 0.8rem; border-radius: 50px;
    font-size: 0.75rem; color: var(--green);
  }
  .live-dot { width:6px; height:6px; background:var(--green); border-radius:50%; animation:pulse 2s infinite; }
  .spot-grid {
    display: grid; grid-template-columns: repeat(6, 1fr); gap: 6px;
  }
  .spot {
    aspect-ratio: 1; border-radius: 6px; cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.6rem; font-weight: 600;
  }
  .spot:hover { transform: scale(1.1); }
  .spot.free { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: var(--green); }
  .spot.occupied { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: var(--red); }
  .spot.reserved { background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3); color: var(--orange); }
  .spot.vip { background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.3); color: var(--accent2); }
  .spot.emergency { background: rgba(0,212,255,0.15); border: 1px solid rgba(0,212,255,0.3); color: var(--accent); }
  .map-legend { display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap; }
  .legend-item { display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; color: var(--muted); }
  .legend-dot { width:10px; height:10px; border-radius:3px; }

  .spots-list { display: flex; flex-direction: column; gap: 0.8rem; }
  .spot-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 14px; padding: 1.2rem 1.5rem;
    display: flex; align-items: center; justify-content: space-between;
    transition: border-color 0.2s, transform 0.2s;
    cursor: pointer;
  }
  .spot-card:hover { border-color: rgba(0,212,255,0.3); transform: translateX(4px); }
  .spot-info h4 { font-family:'Syne',sans-serif; font-weight:700; font-size:0.95rem; }
  .spot-info p { color:var(--muted); font-size:0.82rem; margin-top:0.2rem; }
  .spot-meta { display:flex; align-items:center; gap:1rem; }
  .spot-avail { font-size:0.82rem; color:var(--green); font-weight:600; }
  .spot-price { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:800; color:var(--text); }
  .spot-price span { font-size:0.7rem; color:var(--muted); font-weight:400; font-family:'DM Sans',sans-serif; }
  .badge-small {
    padding:0.2rem 0.7rem; border-radius:50px; font-size:0.72rem; font-weight:600;
  }
  .badge-free { background:rgba(16,185,129,0.12); color:var(--green); border:1px solid rgba(16,185,129,0.25); }
  .badge-full { background:rgba(239,68,68,0.12); color:var(--red); border:1px solid rgba(239,68,68,0.25); }
  .badge-limited { background:rgba(245,158,11,0.12); color:var(--orange); border:1px solid rgba(245,158,11,0.25); }

  /* ── FEATURES ── */
  .features-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 1.2rem; margin-top: 3rem;
  }
  .feature-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 16px; padding: 2rem;
    transition: border-color 0.2s, transform 0.2s;
    position: relative; overflow: hidden;
  }
  .feature-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:2px;
    background: linear-gradient(90deg, transparent, var(--accent), transparent);
    opacity:0; transition:opacity 0.2s;
  }
  .feature-card:hover { border-color:rgba(0,212,255,0.2); transform:translateY(-4px); }
  .feature-card:hover::before { opacity:1; }
  .feature-icon { font-size:2rem; margin-bottom:1rem; }
  .feature-card h3 { font-family:'Syne',sans-serif; font-size:1.05rem; font-weight:700; margin-bottom:0.5rem; }
  .feature-card p { color:var(--muted); font-size:0.88rem; line-height:1.6; }

  /* ── ROLES ── */
  .roles-section { background: var(--surface); border-top:1px solid var(--border); border-bottom:1px solid var(--border); }
  .roles-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1.2rem; margin-top:3rem; }
  .role-card {
    border-radius:16px; padding:2rem 1.5rem; text-align:center;
    cursor:pointer; text-decoration:none; color:var(--text);
    border:1px solid var(--border); transition:all 0.25s;
    display:block;
  }
  .role-card:hover { transform:translateY(-6px); box-shadow:0 20px 50px rgba(0,0,0,0.3); }
  .role-card.driver { background:linear-gradient(135deg,rgba(0,212,255,0.08),rgba(0,212,255,0.02)); }
  .role-card.driver:hover { border-color:rgba(0,212,255,0.4); box-shadow:0 20px 50px rgba(0,212,255,0.1); }
  .role-card.officer { background:linear-gradient(135deg,rgba(245,158,11,0.08),rgba(245,158,11,0.02)); }
  .role-card.officer:hover { border-color:rgba(245,158,11,0.4); }
  .role-card.admin { background:linear-gradient(135deg,rgba(239,68,68,0.08),rgba(239,68,68,0.02)); }
  .role-card.admin:hover { border-color:rgba(239,68,68,0.4); }
  .role-card.owner { background:linear-gradient(135deg,rgba(124,58,237,0.08),rgba(124,58,237,0.02)); }
  .role-card.owner:hover { border-color:rgba(124,58,237,0.4); }
  .role-icon { font-size:2.5rem; margin-bottom:1rem; }
  .role-card h3 { font-family:'Syne',sans-serif; font-weight:700; margin-bottom:0.4rem; }
  .role-card p { font-size:0.82rem; color:var(--muted); line-height:1.5; }
  .role-arrow { margin-top:1.2rem; color:var(--muted); font-size:1.2rem; transition:transform 0.2s; }
  .role-card:hover .role-arrow { transform:translateX(4px); color:var(--accent); }

  /* ── QR SECTION ── */
  .qr-section { background: linear-gradient(135deg,rgba(0,212,255,0.05),rgba(124,58,237,0.05)); }
  .qr-layout { display:grid; grid-template-columns:1fr 1fr; gap:4rem; align-items:center; margin-top:3rem; }
  .qr-visual {
    background:var(--card); border:1px solid var(--border);
    border-radius:20px; padding:2.5rem; text-align:center;
  }
  .qr-code {
    width:180px; height:180px; margin:0 auto 1.5rem;
    background:white; border-radius:12px; padding:12px;
    display:grid; grid-template-columns:repeat(7,1fr); gap:3px;
  }
  .qr-cell { border-radius:2px; }
  .qr-title { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:700; margin-bottom:0.4rem; }
  .qr-sub { color:var(--muted); font-size:0.85rem; }
  .qr-steps { display:flex; flex-direction:column; gap:1.5rem; }
  .qr-step { display:flex; gap:1.2rem; align-items:flex-start; }
  .step-num {
    width:36px; height:36px; border-radius:50%;
    background:rgba(0,212,255,0.1); border:1px solid rgba(0,212,255,0.3);
    color:var(--accent); font-weight:700; font-family:'Syne',sans-serif;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; font-size:0.9rem;
  }
  .step-content h4 { font-weight:600; margin-bottom:0.2rem; }
  .step-content p { color:var(--muted); font-size:0.88rem; line-height:1.5; }

  /* ── PRICING ── */
  .pricing-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1.5rem; margin-top:3rem; }
  .price-card {
    background:var(--card); border:1px solid var(--border);
    border-radius:18px; padding:2rem; position:relative; overflow:hidden;
  }
  .price-card.popular { border-color:rgba(0,212,255,0.4); }
  .price-card.popular::before { content:'POPULAR'; position:absolute; top:1rem; right:1rem; font-size:0.65rem; font-weight:700; letter-spacing:0.1em; color:var(--accent); background:rgba(0,212,255,0.1); padding:0.2rem 0.6rem; border-radius:50px; border:1px solid rgba(0,212,255,0.2); }
  .price-label { font-size:0.8rem; color:var(--muted); font-weight:500; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:0.8rem; }
  .price-amount { font-family:'Syne',sans-serif; font-size:3rem; font-weight:800; line-height:1; }
  .price-amount sup { font-size:1.2rem; vertical-align:top; margin-top:0.8rem; display:inline-block; }
  .price-amount sub { font-size:0.9rem; color:var(--muted); font-weight:400; font-family:'DM Sans',sans-serif; }
  .price-desc { color:var(--muted); font-size:0.85rem; margin:0.8rem 0 1.5rem; }
  .price-features { list-style:none; display:flex; flex-direction:column; gap:0.6rem; margin-bottom:1.8rem; }
  .price-features li { display:flex; align-items:center; gap:0.6rem; font-size:0.88rem; }
  .price-features li::before { content:'✓'; color:var(--green); font-weight:700; }
  .price-features li.no::before { content:'✕'; color:var(--red); }
  .price-features li.no { color:var(--muted); }

  /* ── FOOTER ── */
  footer {
    position:relative; z-index:1;
    border-top:1px solid var(--border);
    padding:3rem 4rem 2rem;
  }
  .footer-grid { display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:3rem; margin-bottom:2.5rem; }
  .footer-brand .logo { font-size:1.4rem; margin-bottom:0.8rem; }
  .footer-brand p { color:var(--muted); font-size:0.88rem; line-height:1.6; max-width:280px; }
  .footer-col h4 { font-family:'Syne',sans-serif; font-weight:700; font-size:0.9rem; margin-bottom:1rem; }
  .footer-col a { display:block; color:var(--muted); text-decoration:none; font-size:0.85rem; margin-bottom:0.5rem; transition:color 0.2s; }
  .footer-col a:hover { color:var(--text); }
  .footer-bottom { display:flex; justify-content:space-between; align-items:center; padding-top:2rem; border-top:1px solid var(--border); }
  .footer-bottom p { color:var(--muted); font-size:0.82rem; }

  /* ── NOTIFICATION TOAST ── */
  .toast {
    position:fixed; bottom:2rem; right:2rem; z-index:999;
    background:var(--card); border:1px solid rgba(0,212,255,0.3);
    border-radius:12px; padding:1rem 1.5rem;
    display:flex; align-items:center; gap:0.8rem;
    box-shadow:0 20px 60px rgba(0,0,0,0.5);
    animation:slideIn 0.4s ease, fadeOut 0.4s 4s ease forwards;
    max-width:320px;
  }
  .toast-icon { font-size:1.4rem; }
  .toast-text strong { display:block; font-size:0.88rem; margin-bottom:0.2rem; }
  .toast-text span { font-size:0.78rem; color:var(--muted); }
  @keyframes slideIn { from{transform:translateX(100px);opacity:0} to{transform:translateX(0);opacity:1} }
  @keyframes fadeOut { to{opacity:0;transform:translateX(20px)} }

  /* ── SCROLLBAR ── */
  ::-webkit-scrollbar { width:6px; }
  ::-webkit-scrollbar-track { background:var(--bg); }
  ::-webkit-scrollbar-thumb { background:var(--border); border-radius:3px; }
  ::-webkit-scrollbar-thumb:hover { background:var(--muted); }
</style>
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-glow glow1"></div>
<div class="bg-glow glow2"></div>

<!-- NAVBAR -->
<nav>
  <div class="logo"><span class="logo-icon">🅿<a href="index.php"> ParkSmart</a></span></div>
  <div class="nav-links">
    <a href="#availability">Availability</a>
    <a href="#features">Features</a>
    <a href="#qr">QR System</a>
    <a href="#pricing">Pricing</a>
  </div>
  <div class="nav-cta">
    <a href="index.php?controller=auth&action=login" class="btn btn-ghost">Log In</a>
    <a href="index.php?controller=auth&action=register" class="btn btn-primary">Get Started →</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge"><span></span> Smart Urban Parking System — Project 11</div>
  <h1>Park Smarter.<br>Drive <span class="accent">Faster.</span></h1>
  <p>Real-time parking availability, QR check-in, automated fines, and intelligent reservation — all in one platform built for modern cities.</p>
  <div class="hero-cta">
    <a href="index.php?controller=driver&action=search" class="btn btn-primary btn-lg">🚗 Find Parking Now</a>
    <a href="#features" class="btn btn-outline-accent btn-lg">Explore Features</a>
  </div>
</section>

<!-- SEARCH -->
<div class="search-section">
  <div class="search-card">
    <form method="get" action="index.php">
      <input type="hidden" name="controller" value="driver">
      <input type="hidden" name="action" value="search">
      <div class="search-tabs">
        <button type="button" class="tab active" onclick="switchTab(this,'daily')">Daily</button>
        <button type="button" class="tab" onclick="switchTab(this,'monthly')">Monthly</button>
        <button type="button" class="tab" onclick="switchTab(this,'reservation')">Reserve Spot</button>
      </div>
      <div class="search-row">
        <div class="field">
          <label>📍 Location</label>
          <input type="text" name="location" placeholder="Enter address or area...">
        </div>
        <div class="field">
          <label>📅 Date & Time In</label>
          <input type="datetime-local" name="start_time">
        </div>
        <div class="field">
          <label>🕐 Exit Time</label>
          <input type="datetime-local" name="end_time">
        </div>
        <button type="submit" class="btn btn-primary" style="padding:0.75rem 1.5rem;white-space:nowrap;">Search →</button>
      </div>
    </form>
  </div>
</div>

<!-- STATS -->
<div class="stats">
  <div class="stat"><div class="stat-num" id="s1">0</div><div class="stat-label">Parking Spots</div></div>
  <div class="stat"><div class="stat-num" id="s2">0</div><div class="stat-label">Active Drivers</div></div>
  <div class="stat"><div class="stat-num" id="s3">0</div><div class="stat-label">Cities Covered</div></div>
  <div class="stat"><div class="stat-num" id="s4">0%</div><div class="stat-label">Uptime</div></div>
</div>

<!-- AVAILABILITY -->
<section class="section" id="availability">
  <div class="section-tag">● Live Status</div>
  <div class="section-title">Real-Time<br>Parking Availability</div>
  <div class="section-sub">See which spots are free, occupied, or reserved — updated every 30 seconds via IoT sensors.</div>

  <div class="availability-layout">
    <div class="parking-map">
      <div class="map-header">
        <div class="map-title">Zone A – Downtown</div>
        <div class="live-badge"><div class="live-dot"></div> Live</div>
      </div>
      <div class="spot-grid" id="spotGrid"></div>
      <div class="map-legend">
        <div class="legend-item"><div class="legend-dot" style="background:rgba(16,185,129,0.4)"></div> Free</div>
        <div class="legend-item"><div class="legend-dot" style="background:rgba(239,68,68,0.4)"></div> Occupied</div>
        <div class="legend-item"><div class="legend-dot" style="background:rgba(245,158,11,0.4)"></div> Reserved</div>
        <div class="legend-item"><div class="legend-dot" style="background:rgba(124,58,237,0.4)"></div> VIP</div>
        <div class="legend-item"><div class="legend-dot" style="background:rgba(0,212,255,0.4)"></div> Emergency</div>
      </div>
    </div>

    <div class="spots-list">
      <div class="spot-card" onclick="window.location='index.php?controller=driver&action=search'">
        <div class="spot-info">
          <h4>🏢 Central Mall Parking</h4>
          <p>📍 Downtown — Floor B2 · 200m walk</p>
        </div>
        <div class="spot-meta">
          <span class="spot-avail">18 free</span>
          <div class="spot-price">$3<span>/hr</span></div>
          <span class="badge-small badge-free">Available</span>
        </div>
      </div>
      <div class="spot-card" onclick="window.location='index.php?controller=driver&action=search'">
        <div class="spot-info">
          <h4>🏛️ City Hall Garage</h4>
          <p>📍 Civic Center — Open Air · 50m walk</p>
        </div>
        <div class="spot-meta">
          <span class="spot-avail">3 free</span>
          <div class="spot-price">$2<span>/hr</span></div>
          <span class="badge-small badge-limited">Limited</span>
        </div>
      </div>
      <div class="spot-card" onclick="window.location='index.php?controller=driver&action=search'">
        <div class="spot-info">
          <h4>🌆 Tower Block P7</h4>
          <p>📍 Business District — Covered · 400m walk</p>
        </div>
        <div class="spot-meta">
          <span class="spot-avail">0 free</span>
          <div class="spot-price">$5<span>/hr</span></div>
          <span class="badge-small badge-full">Full</span>
        </div>
      </div>
      <div class="spot-card" onclick="window.location='index.php?controller=driver&action=search'">
        <div class="spot-info">
          <h4>🎭 Arts Quarter P2</h4>
          <p>📍 Cultural District — Smart Barrier · 120m walk</p>
        </div>
        <div class="spot-meta">
          <span class="spot-avail">27 free</span>
          <div class="spot-price">$2.5<span>/hr</span></div>
          <span class="badge-small badge-free">Available</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section" id="features" style="background:var(--surface);border-top:1px solid var(--border)">
  <div class="section-tag">● System Features</div>
  <div class="section-title">Everything You Need<br>In One Platform</div>
  <div class="section-sub">46 use cases fully covered — from reservation to automated fines.</div>

  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">📡</div>
      <h3>IoT QR Check-In/Out</h3>
      <p>Scan QR codes to check in and out automatically. Smart barriers lock/unlock based on your booking status.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📅</div>
      <h3>Smart Reservation</h3>
      <p>Reserve spots in advance, get alternative locations, extend your session, or add buffer time — all in real-time.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">💳</div>
      <h3>Escrow Payment</h3>
      <p>Funds are held securely in escrow and released after checkout. Cancel anytime with automatic refund processing.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🚨</div>
      <h3>Automated Fines</h3>
      <p>Overstay detection generates fines automatically. Officers are assigned zones and violations are tracked instantly.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🚑</div>
      <h3>Emergency Override</h3>
      <p>Emergency vehicles get instant access override. Admin can grant emergency permissions and manage blacklisted users.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📊</div>
      <h3>Dynamic Pricing</h3>
      <p>Owners set peak/off-peak rates automatically. Revenue analytics, promotions, and spot difficulty ratings included.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">⭐</div>
      <h3>Loyalty & Subscriptions</h3>
      <p>Earn points on every booking. Redeem loyalty discounts, validate promo codes, and manage monthly subscriptions.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🗺️</div>
      <h3>Waitlist Automation</h3>
      <p>When a spot frees up, the next driver on the waitlist is automatically notified and offered the spot.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📱</div>
      <h3>Real-Time Tracking</h3>
      <p>Track your reservation status, booking updates, session timer, and fine details all from your dashboard.</p>
    </div>
  </div>
</section>

<!-- ROLES -->
<section class="section roles-section">
  <div class="section-tag">● User Roles</div>
  <div class="section-title">Built For Everyone</div>
  <div class="section-sub">Different dashboards tailored for every role in the system.</div>

  <div class="roles-grid">
    <a href="index.php?controller=driver&action=dashboard" class="role-card driver">
      <div class="role-icon">🚗</div>
      <h3>Driver</h3>
      <p>Find spots, make reservations, pay, extend sessions, and track loyalty points.</p>
      <div class="role-arrow">→</div>
    </a>
    <a href="index.php?controller=admin&action=dashboard" class="role-card admin">
      <div class="role-icon">⚙️</div>
      <h3>Municipal Admin</h3>
      <p>Manage blacklist, emergency access, override rules, waitlists, and city revenue.</p>
      <div class="role-arrow">→</div>
    </a>
    <a href="index.php?controller=owner&action=dashboard" class="role-card owner">
      <div class="role-icon">🏢</div>
      <h3>Parking Owner</h3>
      <p>Set dynamic pricing, manage spots, apply promotions, and view earnings.</p>
      <div class="role-arrow">→</div>
    </a>
  </div>
</section>

<!-- QR SYSTEM -->
<section class="section qr-section" id="qr">
  <div class="section-tag">● IoT Integration</div>
  <div class="section-title">QR-Powered<br>Entry & Exit</div>

  <div class="qr-layout">
    <div class="qr-steps">
      <div class="qr-step">
        <div class="step-num">1</div>
        <div class="step-content">
          <h4>Book Your Spot</h4>
          <p>Reserve a parking spot and receive a unique QR code linked to your booking and vehicle.</p>
        </div>
      </div>
      <div class="qr-step">
        <div class="step-num">2</div>
        <div class="step-content">
          <h4>Scan to Check In</h4>
          <p>On arrival, scan your QR at the barrier. The IoT system verifies and unlocks your spot automatically.</p>
        </div>
      </div>
      <div class="qr-step">
        <div class="step-num">3</div>
        <div class="step-content">
          <h4>Park & Relax</h4>
          <p>Your session is live. Get alerts before your time expires. Extend if needed from the app.</p>
        </div>
      </div>
      <div class="qr-step">
        <div class="step-num">4</div>
        <div class="step-content">
          <h4>Scan to Check Out</h4>
          <p>Scan again to leave. Payment is processed automatically from escrow. Barrier unlocks. Done.</p>
        </div>
      </div>
    </div>

    <div class="qr-visual">
      <div class="qr-code" id="qrDisplay"></div>
      <div class="qr-title">Spot A-14 · Downtown Mall</div>
      <div class="qr-sub" style="margin-bottom:1.5rem">Valid until 3:30 PM · Session Active</div>
      <div style="display:flex;gap:0.8rem;justify-content:center">
        <a href="index.php?controller=qr&action=scanner" class="btn btn-primary">🔓 Check In</a>
        <a href="index.php?controller=driver&action=dashboard" class="btn btn-ghost">View Details</a>
      </div>
    </div>
  </div>
</section>

<!-- PRICING -->
<section class="section" id="pricing" style="background:var(--surface);border-top:1px solid var(--border)">
  <div class="section-tag">● Subscription Plans</div>
  <div class="section-title">Simple Pricing</div>
  <div class="section-sub">Choose a plan that works for your parking needs.</div>

  <div class="pricing-grid">
    <div class="price-card">
      <div class="price-label">Basic</div>
      <div class="price-amount"><sup>$</sup>0<sub>/month</sub></div>
      <div class="price-desc">Pay as you go. No commitment.</div>
      <ul class="price-features">
        <li>Spot search & availability</li>
        <li>QR check-in/out</li>
        <li>Basic reservations</li>
        <li class="no">Loyalty points</li>
        <li class="no">Priority booking</li>
        <li class="no">Extended sessions</li>
      </ul>
      <a href="index.php?controller=auth&action=register" class="btn btn-ghost" style="width:100%;justify-content:center">Get Started</a>
    </div>
    <div class="price-card popular">
      <div class="price-label">Pro Driver</div>
      <div class="price-amount" style="color:var(--accent)"><sup>$</sup>15<sub>/month</sub></div>
      <div class="price-desc">Best for frequent parkers.</div>
      <ul class="price-features">
        <li>Everything in Basic</li>
        <li>Loyalty points & rewards</li>
        <li>Priority spot booking</li>
        <li>Extend sessions anytime</li>
        <li>Promo code access</li>
        <li class="no">VIP spots</li>
      </ul>
      <a href="index.php?controller=auth&action=register" class="btn btn-primary" style="width:100%;justify-content:center">Subscribe Now</a>
    </div>
    <div class="price-card">
      <div class="price-label">Premium</div>
      <div class="price-amount"><sup>$</sup>35<sub>/month</sub></div>
      <div class="price-desc">For power users & businesses.</div>
      <ul class="price-features">
        <li>Everything in Pro</li>
        <li>VIP spot access</li>
        <li>Waived booking fees</li>
        <li>Monthly invoicing</li>
        <li>Multi-vehicle account</li>
        <li>Dedicated support</li>
      </ul>
      <a href="index.php?controller=auth&action=register" class="btn btn-ghost" style="width:100%;justify-content:center">Go Premium</a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="logo"><span>🅿</span> ParkSmart</div>
      <p>Smart Urban Parking Management System — CS251 Software Engineering 1, Spring 2025-2026. Capital University (formerly Helwan University).</p>
    </div>
    <div class="footer-col">
      <h4>For Drivers</h4>
      <a href="index.php?controller=driver&action=search">Find Parking</a>
      <a href="index.php?controller=driver&action=history">My Reservations</a>
      <a href="index.php?controller=driver&action=loyalty">Loyalty Points</a>
      <a href="index.php?controller=driver&action=payment">Subscriptions</a>
    </div>
    <div class="footer-col">
      <h4>Management</h4>
      <a href="index.php?controller=admin&action=dashboard">Admin Panel</a>
      <a href="index.php?controller=owner&action=dashboard">Owner Panel</a>
      <a href="index.php?controller=qr&action=scanner">QR Scanner</a>
    </div>
    <div class="footer-col">
      <h4>System</h4>
      <a href="index.php?controller=auth&action=login">Login</a>
      <a href="index.php?controller=auth&action=register">Register</a>
      <a href="#">API Docs</a>
      <a href="#">Support</a>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2026 ParkSmart — CS251 Project 11 · All rights reserved.</p>
    <p style="color:var(--muted);font-size:0.8rem">Built with HTML · Bootstrap · PHP · MySQL</p>
  </div>
</footer>

<!-- TOAST -->
<div class="toast" id="toast">
  <div class="toast-icon">🚗</div>
  <div class="toast-text">
    <strong>Spot A-14 just freed up!</strong>
    <span>Downtown Mall · Available now</span>
  </div>
</div>

<script>
// ── Spot Grid ──
const spotGrid = document.getElementById('spotGrid');
const types = ['free','free','free','occupied','occupied','reserved','vip','emergency','free','occupied','free','free','reserved','occupied','free','free','occupied','free','vip','free','free','occupied','free','reserved','free','occupied','free','free','free','occupied'];
const labels = {'free':'P','occupied':'X','reserved':'R','vip':'V','emergency':'E'};
types.forEach((t,i) => {
  const d = document.createElement('div');
  d.className = `spot ${t}`;
  d.textContent = labels[t];
  d.title = `Spot ${i+1} — ${t}`;
  d.onclick = () => { if(t==='free') window.location='index.php?controller=driver&action=search'; };
  spotGrid.appendChild(d);
});

// ── QR Code Visual ──
const qr = document.getElementById('qrDisplay');
for(let i=0;i<49;i++){
  const c = document.createElement('div');
  c.className='qr-cell';
  const corner = [0,1,2,3,4,5,6,7,13,14,20,21,27,28,35,42,43,44,45,46,47,48];
  c.style.background = corner.includes(i) || Math.random()>0.5 ? '#000' : '#fff';
  qr.appendChild(c);
}

// ── Counter Animations ──
function animateCounter(id, end, suffix='', duration=2000) {
  const el = document.getElementById(id);
  let start=0; const step = end/60;
  const timer = setInterval(()=>{
    start = Math.min(start+step, end);
    el.textContent = Math.floor(start).toLocaleString() + suffix;
    if(start>=end) clearInterval(timer);
  }, duration/60);
}
const observer = new IntersectionObserver(entries => {
  if(entries[0].isIntersecting){
    animateCounter('s1',1240); animateCounter('s2',8500);
    animateCounter('s3',12); animateCounter('s4',99,'%');
    observer.disconnect();
  }
});
observer.observe(document.querySelector('.stats'));

// ── Tab Switcher ──
function switchTab(el, type) {
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
}

// ── Remove toast after animation ──
setTimeout(()=>{ const t=document.getElementById('toast'); if(t) t.remove(); }, 5000);
</script>
</body>
</html>