<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: index.php?controller=auth&action=login&error=Please+login+as+owner');
    exit;
}
$page = 'dashboard';
$pageTitle = 'Owner Dashboard';
$ownerName = $_SESSION['name'] ?? 'Owner';
// البيانات من OwnerController
$totalSpots = $totalSpots ?? 0;
$availableSpots = $availableSpots ?? 0;
$revenue = $revenue ?? ['total' => 0, 'count' => 0];
$pendingPayout = $pendingPayout ?? 0;
$activePromos = $activePromos ?? 0;
$trustScore = 8.6; // يمكن جعله ديناميكياً لاحقاً
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Owner Dashboard - ParkSmart</title>
  <link rel="icon" href="https://img.icons8.com/color/48/000000/parking.png">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {--bg: #0a0e1a; --surface: #111827; --card: #161d2e; --accent: #7c3aed; --accent2: #00d4ff; --green: #10b981; --orange: #f59e0b; --red: #ef4444; --text: #f1f5f9; --muted: #64748b; --border: rgba(255,255,255,0.07);}
    body.light-mode {--bg: #f8fafc; --surface: #ffffff; --card: #ffffff; --text: #0f172a; --muted: #475569; --border: rgba(0,0,0,0.08); --accent: #6d28d9; --accent2: #0284c7; --green: #059669; --orange: #d97706; --red: #dc2626;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;overflow-x:hidden;}
    .bg-grid{position:fixed;inset:0;z-index:0;background-image:linear-gradient(rgba(124,58,237,0.03)1px,transparent 1px),linear-gradient(90deg,rgba(124,58,237,0.03)1px,transparent 1px);background-size:60px 60px;pointer-events:none;}
    .bg-glow{position:fixed;z-index:0;pointer-events:none;border-radius:50%;filter:blur(120px);opacity:0.15;}
    .glow1{width:600px;height:600px;background:var(--accent);top:-200px;left:-100px;animation:driftA 12s infinite alternate;}
    .glow2{width:400px;height:400px;background:var(--accent2);bottom:10%;right:-100px;animation:driftB 10s infinite alternate;}
    @keyframes driftA{to{transform:translate(80px,80px)}}
    @keyframes driftB{to{transform:translate(-60px,-60px)}}
    .sidebar{background:var(--surface);min-height:100vh;border-right:1px solid var(--border);position:fixed;width:260px;z-index:10;overflow-y:auto;}
    .sidebar .nav-link{color:var(--muted);padding:0.6rem 1rem;margin:0.2rem 0.8rem;border-radius:10px;display:flex;transition:all 0.2s;font-size:0.9rem;text-decoration:none;}
    .sidebar .nav-link i{margin-right:0.8rem;color:var(--accent);width:1.8rem;}
    .sidebar .nav-link:hover{background:rgba(0,212,255,0.1);color:var(--accent2);}
    .sidebar .nav-link.active{background:var(--accent2);color:var(--bg);font-weight:600;}
    .main-content{margin-left:260px;padding:1.5rem;position:relative;z-index:1;}
    .top-navbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0.8rem 1.5rem;border-radius:16px;margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;}
    .card{background:var(--card);border:1px solid var(--border);border-radius:16px;transition:transform 0.2s;}
    .card:hover{transform:translateY(-2px);}
    .btn-primary{background:var(--accent2);border:none;color:var(--bg);font-weight:600;}
    .btn-primary:hover{background:#33deff;}
    .btn-outline-accent{border:1px solid var(--accent);color:var(--accent);background:transparent;}
    .btn-outline-accent:hover{background:rgba(124,58,237,0.1);}
    .stat-card{border-left:3px solid var(--accent);}
  </style>
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-glow glow1"></div>
<div class="bg-glow glow2"></div>

<!-- Sidebar -->
<div class="sidebar p-3">
  <a href="index.php" style="text-decoration:none;">
    <h4 class="text-center mb-4" style="color:var(--accent);"><i class="fas fa-parking"></i> ParkSmart<br><small style="font-size:0.7rem;color:var(--muted);">Owner Portal</small></h4>
  </a>
  <hr style="border-color:var(--border);">
  <ul class="nav nav-pills flex-column">
    <li><a class="nav-link <?= $page=='dashboard'?'active':'' ?>" href="index.php?controller=owner&action=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a class="nav-link <?= $page=='manage'?'active':'' ?>" href="index.php?controller=owner&action=manageSpots"><i class="fas fa-cogs"></i> Manage Spots</a></li>
    <li><a class="nav-link <?= $page=='analytics'?'active':'' ?>" href="index.php?controller=owner&action=analytics"><i class="fas fa-chart-bar"></i> Analytics</a></li>
  </ul>
  <hr style="border-color:var(--border);">
  <a href="index.php?controller=auth&action=logout" class="btn btn-danger w-100"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="top-navbar d-flex justify-content-between align-items-center">
    <h5 class="mb-0" style="color:var(--text);"><?= $pageTitle ?></h5>
    <div class="d-flex align-items-center gap-3">
      <button onclick="toggleTheme()" class="btn btn-sm btn-outline-accent" style="font-size:1.2rem;" title="Switch theme"><span id="themeIcon">🌙</span></button>
      <span class="badge bg-primary"><i class="fas fa-building"></i> <?= htmlspecialchars($ownerName) ?></span>
    </div>
  </div>

  <div class="p-4">
    <!-- Stats Cards -->
    <div class="row">
      <div class="col-md-4 mb-3">
        <div class="card stat-card p-3 text-center">
          <h5>⭐ Trust Score</h5>
          <h2><?= $trustScore ?></h2>
          <a href="index.php?controller=owner&action=analytics" class="btn btn-sm btn-primary">Details</a>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card stat-card p-3 text-center">
          <h5>💰 Pending Payout</h5>
          <h3>$<?= number_format($pendingPayout, 2) ?></h3>
          <a href="index.php?controller=owner&action=analytics" class="btn btn-sm btn-primary">Schedule</a>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card stat-card p-3 text-center">
          <h5>🏷️ Active Promos</h5>
          <h3><?= $activePromos ?></h3>
          <a href="index.php?controller=owner&action=analytics" class="btn btn-sm btn-primary">Manage</a>
        </div>
      </div>
    </div>

    <!-- My Spots Summary -->
    <div class="row mt-3">
      <div class="col-md-6 mb-3">
        <div class="card p-4">
          <h5><i class="fas fa-parking"></i> My Parking Spots</h5>
          <hr>
          <p><strong>Total Spots:</strong> <?= $totalSpots ?></p>
          <p><strong>Available:</strong> <?= $availableSpots ?></p>
          <p><strong>Occupied/Reserved:</strong> <?= $totalSpots - $availableSpots ?></p>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="card p-4">
          <h5><i class="fas fa-chart-simple"></i> Recent Revenue (30 days)</h5>
          <hr>
          <p><strong>Revenue:</strong> $<?= number_format($revenue['total'], 2) ?></p>
          <p><strong>Transactions:</strong> <?= $revenue['count'] ?></p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card p-4 mt-3">
      <h5>Quick Actions</h5>
      <a href="index.php?controller=owner&action=manageSpots" class="btn btn-outline-accent m-1">Manage Spots</a>
      <a href="index.php?controller=owner&action=analytics" class="btn btn-outline-accent m-1">Analytics</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function setTheme(t){document.body.classList.toggle('light-mode',t==='light');document.getElementById('themeIcon').textContent=t==='light'?'☀️':'🌙';localStorage.setItem('theme',t);}
  function toggleTheme(){const c=localStorage.getItem('theme')||'dark';setTheme(c==='dark'?'light':'dark');}
  (function(){setTheme(localStorage.getItem('theme')||'dark');})();
</script>
</body>
</html>