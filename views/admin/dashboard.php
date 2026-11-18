<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?controller=auth&action=login&error=Please+login+as+admin');
    exit;
}
$page = 'dashboard';
$pageTitle = 'Admin Dashboard';
$adminName = $_SESSION['name'] ?? 'Admin';
// البيانات تأتي من AdminController عبر include
$stats = $stats ?? ['total_users' => 0, 'active_bookings' => 0, 'total_revenue' => 0];
$blacklistCount = $blacklistCount ?? 0;
$recentActivities = $recentActivities ?? [];
$revenueOverview = $revenueOverview ?? ['daily' => 0, 'weekly' => 0, 'monthly' => 0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - ParkSmart</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="icon" href="https://img.icons8.com/color/48/000000/parking.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    body.light-mode {
      --bg: #f8fafc;
      --surface: #ffffff;
      --card: #ffffff;
      --text: #0f172a;
      --muted: #475569;
      --border: rgba(0,0,0,0.08);
      --accent: #0284c7;
      --accent2: #6d28d9;
      --green: #059669;
      --orange: #d97706;
      --red: #dc2626;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      overflow-x: hidden;
    }

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

    .sidebar {
      background: var(--surface);
      min-height: 100vh;
      border-right: 1px solid var(--border);
      position: fixed;
      width: 260px;
      z-index: 10;
      overflow-y: auto;
    }
    .sidebar .nav-link {
      color: var(--muted);
      padding: 0.6rem 1rem;
      margin: 0.2rem 0.8rem;
      border-radius: 10px;
      display: flex;
      transition: all 0.2s;
      font-size: 0.9rem;
      text-decoration: none;
    }
    .sidebar .nav-link i {
      margin-right: 0.8rem;
      color: var(--accent);
      width: 1.8rem;
    }
    .sidebar .nav-link:hover {
      background: rgba(0,212,255,0.1);
      color: var(--accent);
    }
    .sidebar .nav-link.active {
      background: var(--accent);
      color: var(--bg);
      font-weight: 600;
    }

    .main-content {
      margin-left: 260px;
      padding: 1.5rem;
      position: relative;
      z-index: 1;
    }
    .top-bar {
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      padding: 0.8rem 1.5rem;
      border-radius: 16px;
      margin-bottom: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 16px;
      transition: transform 0.2s;
    }
    .card:hover { transform: translateY(-2px); }
    .stat-card { border-left: 3px solid var(--accent); }
    .btn-primary {
      background: var(--accent);
      border: none;
      color: var(--bg);
      font-weight: 600;
    }
    .btn-primary:hover { background: #33deff; }
    .btn-outline-accent {
      border: 1px solid var(--accent);
      color: var(--accent);
      background: transparent;
    }
    .btn-outline-accent:hover { background: rgba(0,212,255,0.1); }
    .table { color: var(--text); }
    .table thead th { border-bottom-color: var(--border); }
    .list-group-item { background: transparent !important; border-color: var(--border) !important; }
    .progress { background: var(--surface); }
    .text-muted { color: var(--muted) !important; }
  </style>
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-glow glow1"></div>
<div class="bg-glow glow2"></div>

<!-- Sidebar -->
<div class="sidebar p-3">
  <a href="index.php" style="text-decoration: none;">
    <h4 class="text-center mb-4" style="color: var(--accent);">
      <i class="fas fa-parking"></i> ParkSmart<br>
      <small style="font-size: 0.7rem; color: var(--muted);">Municipal Admin Portal</small>
    </h4>
  </a>
  <hr style="border-color: var(--border);">
  <div class="nav flex-column">
    <a class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>" href="index.php?controller=admin&action=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a class="nav-link <?= $page == 'blacklist' ? 'active' : '' ?>" href="index.php?controller=admin&action=blacklist"><i class="fas fa-ban"></i> Blacklist Management</a>
    <a class="nav-link <?= $page == 'waitlist' ? 'active' : '' ?>" href="index.php?controller=admin&action=waitlist"><i class="fas fa-list-alt"></i> Waitlist Automation</a>
    <a class="nav-link <?= $page == 'reports' ? 'active' : '' ?>" href="index.php?controller=admin&action=reports"><i class="fas fa-chart-line"></i> Revenue & Heatmap</a>
    <a class="nav-link <?= $page == 'fines' ? 'active' : '' ?>" href="index.php?controller=admin&action=fines"><i class="fas fa-money-bill-wave"></i> Automated Fine Generation</a>
    <a class="nav-link <?= $page == 'users' ? 'active' : '' ?>" href="index.php?controller=admin&action=users"><i class="fas fa-users"></i> Users</a>
  </div>
  <hr style="border-color: var(--border);">
  <div class="mt-auto">
    <a href="index.php?controller=auth&action=logout" class="btn btn-danger w-100"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="top-bar">
    <h5 class="mb-0" style="color: var(--text);"><?= $pageTitle ?></h5>
    <div class="d-flex align-items-center gap-3">
      <button onclick="toggleTheme()" class="btn btn-sm btn-outline-accent" style="font-size:1.2rem;" title="Switch theme">
        <span id="themeIcon">🌙</span>
      </button>
      <span class="badge bg-info" style="color: #0a0e1a;"><i class="fas fa-user-shield"></i> <?= htmlspecialchars($adminName) ?></span>
      <span class="text-muted"><i class="fas fa-calendar"></i> <?= date('F j, Y') ?></span>
    </div>
  </div>

  <div class="mt-4">
    <!-- Stats Cards -->
    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card stat-card p-3">
          <div class="d-flex justify-content-between">
            <div><small class="text-muted">Total Users</small><h2 class="mb-0"><?= number_format($stats['total_users']) ?></h2></div>
            <i class="fas fa-users fa-2x" style="color: var(--accent2);"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card stat-card p-3">
          <div class="d-flex justify-content-between">
            <div><small class="text-muted">Active Bookings</small><h2 class="mb-0"><?= number_format($stats['active_bookings']) ?></h2></div>
            <i class="fas fa-calendar-check fa-2x" style="color: var(--accent);"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card stat-card p-3">
          <div class="d-flex justify-content-between">
            <div><small class="text-muted">Blacklisted</small><h2 class="mb-0"><?= number_format($blacklistCount) ?></h2></div>
            <i class="fas fa-ban fa-2x" style="color: var(--red);"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card stat-card p-3">
          <div class="d-flex justify-content-between">
            <div><small class="text-muted">Total Revenue</small><h2 class="mb-0">$<?= number_format($stats['total_revenue'], 2) ?></h2></div>
            <i class="fas fa-dollar-sign fa-2x" style="color: var(--green);"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Recent Activity -->
      <div class="col-md-6 mb-4">
        <div class="card p-4">
          <h5><i class="fas fa-clock"></i> Recent Activity</h5>
          <hr>
          <ul class="list-group list-group-flush">
            <?php if (!empty($recentActivities)): ?>
              <?php foreach ($recentActivities as $activity): ?>
                <li class="list-group-item d-flex justify-content-between">
                  <span>🚗 Reservation #<?= $activity['id'] ?> – Spot <?= htmlspecialchars($activity['spot_number']) ?></span>
                  <span class="text-muted"><?= date('M d, Y', strtotime($activity['start_time'])) ?></span>
                </li>
              <?php endforeach; ?>
            <?php else: ?>
              <li class="list-group-item">No recent activity.</li>
            <?php endif; ?>
          </ul>
        </div>
      </div>

      <!-- Revenue Overview -->
      <div class="col-md-6 mb-4">
        <div class="card p-4">
          <h5><i class="fas fa-chart-simple"></i> Revenue Overview</h5>
          <hr>
          <?php
          $maxRevenue = 100000; // for progress bar scaling
          $periods = ['daily', 'weekly', 'monthly'];
          foreach ($periods as $period):
              $amount = $revenueOverview[$period] ?? 0;
              $pct = min(($amount / $maxRevenue) * 100, 100);
          ?>
          <div class="mb-3">
            <label><?= ucfirst($period) ?></label>
            <div class="progress">
              <div class="progress-bar bg-<?= $period === 'daily' ? 'success' : ($period === 'weekly' ? 'info' : 'warning') ?>" style="width: <?= $pct ?>%"></div>
            </div>
            <small class="text-muted">$<?= number_format($amount, 2) ?></small>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ========== Theme Switcher ==========
  function setTheme(theme) {
    document.body.classList.toggle('light-mode', theme === 'light');
    document.getElementById('themeIcon').textContent = theme === 'light' ? '☀️' : '🌙';
    localStorage.setItem('theme', theme);
  }
  function toggleTheme() {
    const current = localStorage.getItem('theme') || 'dark';
    setTheme(current === 'dark' ? 'light' : 'dark');
  }
  (function() {
    const saved = localStorage.getItem('theme') || 'dark';
    setTheme(saved);
  })();
</script>
</body>
</html>