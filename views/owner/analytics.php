<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: index.php?controller=auth&action=login&error=Please+login+as+owner');
    exit;
}
$page = 'analytics';
$pageTitle = 'Analytics & Revenue';
$ownerName = $_SESSION['name'] ?? 'Owner';
// بيانات من OwnerController
$payoutData = $payoutData ?? null;
$promos = $promos ?? [];
$msg = $_SESSION['msg'] ?? '';
$err = $_SESSION['err'] ?? '';
unset($_SESSION['msg'], $_SESSION['err']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Analytics - ParkSmart</title>
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
    .btn-primary{background:var(--accent2);border:none;color:var(--bg);font-weight:600;cursor:pointer;}
    .btn-primary:hover{background:#33deff;}
    .btn-outline-accent{border:1px solid var(--accent);color:var(--accent);background:transparent;}
    .btn-outline-accent:hover{background:rgba(124,58,237,0.1);}
    .btn-success{background:var(--green);border:none;color:white;}
    .form-control,.form-select{background:var(--surface);border:1px solid var(--border);color:var(--text);}
    .form-control:focus,.form-select:focus{background:var(--surface);border-color:var(--accent);color:var(--text);box-shadow:none;}
    .table{color:var(--text);}
    .table thead th{border-bottom-color:var(--border);}
    .alert{padding:0.7rem;border-radius:10px;margin-bottom:1rem;}
    .alert-danger{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;}
    .alert-success{background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;}
    .alert-info{background:rgba(0,212,255,0.1);border:1px solid rgba(0,212,255,0.3);color:var(--accent2);}
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
    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <!-- Market-Rate Calculator -->
    <div class="card p-4 mb-4">
      <h5><i class="fas fa-chart-line"></i> Dynamic Market-Rate Calculator</h5>
      <div class="row">
        <div class="col-md-6">
          <label>Zone demand index (0-100)</label>
          <input type="number" id="demandIdx" class="form-control" value="72" min="0" max="100">
        </div>
        <div class="col-md-6">
          <label>Your base rate ($/hr)</label>
          <input type="number" id="baseRate" class="form-control" value="8" step="0.5">
        </div>
      </div>
      <button class="btn btn-primary mt-3" onclick="calculateMarketRate()">Recalculate</button>
      <div id="marketResult" class="mt-3 alert alert-info">Suggested list price: $8.94/hr</div>
    </div>

    <!-- Commission Split -->
    <div class="card p-4 mb-4">
      <h5><i class="fas fa-percent"></i> Commission Split Logic</h5>
      <p>Example booking: $100</p>
      <table class="table">
        <tr><td>Platform fee (12%)</td><td>-$12</td></tr>
        <tr><td>Municipal levy (2%)</td><td>-$2</td></tr>
        <tr><td><strong>Owner net</strong></td><td><strong>$86.00</strong></td></tr>
      </table>
    </div>

    <!-- Payout Scheduling -->
    <div class="card p-4 mb-4">
      <h5><i class="fas fa-calendar-check"></i> Owner Payout Scheduling</h5>
      <table class="table">
        <tr><td>Period</td><td><?= $payoutData ? htmlspecialchars($payoutData['period_start'] . ' – ' . $payoutData['period_end']) : 'No payouts yet' ?></td></tr>
        <tr><td>Last Amount</td><td><strong>$<?= $payoutData ? number_format($payoutData['net_amount'], 2) : '0.00' ?></strong></td></tr>
        <tr><td>Minimum threshold</td><td>$50 · <span class="text-success">met</span></td></tr>
      </table>
      <form method="post" action="index.php?controller=owner&action=requestPayout">
        <button type="submit" class="btn btn-primary">Request Early Payout</button>
      </form>
    </div>

    <!-- Promotional Codes -->
    <div class="card p-4 mb-4">
      <h5><i class="fas fa-tag"></i> Promotional Code Validator</h5>
      <hr>
      <h6>Your active promos</h6>
      <ul class="list-group list-group-flush mb-3">
        <?php if (!empty($promos)): ?>
          <?php foreach ($promos as $p): ?>
            <li class="list-group-item bg-transparent text-white border-secondary">
              <?= htmlspecialchars($p['code']) ?> - <?= $p['discount_percent'] ?>% off
              (Expires: <?= date('M d, Y', strtotime($p['valid_until'])) ?>)
              <span class="badge bg-<?= $p['is_active'] ? 'success' : 'secondary' ?> float-end"><?= $p['is_active'] ? 'Active' : 'Inactive' ?></span>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li class="list-group-item bg-transparent text-white border-secondary">No promo codes yet.</li>
        <?php endif; ?>
      </ul>
      <form method="post" action="index.php?controller=owner&action=addPromo" class="mt-2">
        <div class="row">
          <div class="col-md-4">
            <input type="text" name="code" class="form-control" placeholder="CODE" required>
          </div>
          <div class="col-md-4">
            <input type="number" name="discount_percent" class="form-control" placeholder="Discount %" min="1" max="100" required>
          </div>
          <div class="col-md-4">
            <input type="date" name="valid_until" class="form-control" value="<?= date('Y-m-d', strtotime('+6 months')) ?>">
          </div>
        </div>
        <button type="submit" class="btn btn-success mt-2">Add Promo Code</button>
      </form>
    </div>

    <!-- Trust Score -->
    <div class="card p-4 mb-4">
      <h5><i class="fas fa-star"></i> Social Review & Trust Score</h5>
      <div class="text-center">
        <span style="font-size:3rem;font-weight:bold;color:var(--accent);">8.6</span><span style="font-size:1.2rem;">/10</span>
        <p>Based on <strong>214</strong> reviews, response time, and cancellation rate.</p>
      </div>
      <table class="table">
        <tr><td>5★ reviews</td><td>89%</td></tr>
        <tr><td>Avg response time</td><td>12 min</td></tr>
        <tr><td>Cancellation rate</td><td>2.4%</td></tr>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function setTheme(t){document.body.classList.toggle('light-mode',t==='light');document.getElementById('themeIcon').textContent=t==='light'?'☀️':'🌙';localStorage.setItem('theme',t);}
  function toggleTheme(){const c=localStorage.getItem('theme')||'dark';setTheme(c==='dark'?'light':'dark');}
  (function(){setTheme(localStorage.getItem('theme')||'dark');})();

  function calculateMarketRate() {
    let demand = document.getElementById('demandIdx').valueAsNumber / 100;
    let base = document.getElementById('baseRate').valueAsNumber;
    let suggested = (base * (0.85 + demand * 0.35)).toFixed(2);
    document.getElementById('marketResult').innerHTML = `<strong>Suggested list price: $${suggested}/hr</strong> (comparable-adjusted)`;
  }
</script>
</body>
</html>