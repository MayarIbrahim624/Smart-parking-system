<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?controller=auth&action=login&error=Please+login+first');
    exit;
}
$page = 'payment';
$pageTitle = 'Payments';
$driverName = $_SESSION['name'] ?? 'Driver';
// بيانات من DriverController
$payments = $payments ?? [];
$reservations = $reservations ?? [];
$msg = $_SESSION['msg'] ?? '';
$err = $_SESSION['err'] ?? '';
unset($_SESSION['msg'], $_SESSION['err']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payments - ParkSmart</title>
  <link rel="icon" href="https://img.icons8.com/color/48/000000/parking.png">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {--bg: #0a0e1a; --surface: #111827; --card: #161d2e; --accent: #00d4ff; --accent2: #7c3aed; --green: #10b981; --orange: #f59e0b; --red: #ef4444; --text: #f1f5f9; --muted: #64748b; --border: rgba(255,255,255,0.07);}
    body.light-mode {--bg: #f8fafc; --surface: #ffffff; --card: #ffffff; --text: #0f172a; --muted: #475569; --border: rgba(0,0,0,0.08); --accent: #0284c7; --accent2: #6d28d9; --green: #059669; --orange: #d97706; --red: #dc2626;}
    *{margin:0;padding:0;box-sizing:border-box;}
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;overflow-x:hidden;}
    .bg-grid{position:fixed;inset:0;z-index:0;background-image:linear-gradient(rgba(0,212,255,0.03)1px,transparent 1px),linear-gradient(90deg,rgba(0,212,255,0.03)1px,transparent 1px);background-size:60px 60px;pointer-events:none;}
    .bg-glow{position:fixed;z-index:0;pointer-events:none;border-radius:50%;filter:blur(120px);opacity:0.15;}
    .glow1{width:600px;height:600px;background:var(--accent);top:-200px;left:-100px;animation:driftA 12s infinite alternate;}
    .glow2{width:400px;height:400px;background:var(--accent2);bottom:10%;right:-100px;animation:driftB 10s infinite alternate;}
    @keyframes driftA{to{transform:translate(80px,80px)}}
    @keyframes driftB{to{transform:translate(-60px,-60px)}}
    .sidebar{background:var(--surface);min-height:100vh;border-right:1px solid var(--border);position:fixed;width:260px;z-index:10;overflow-y:auto;}
    .sidebar .nav-link{color:var(--muted);padding:0.6rem 1rem;margin:0.2rem 0.8rem;border-radius:10px;display:flex;transition:all 0.2s;font-size:0.9rem;text-decoration:none;}
    .sidebar .nav-link i{margin-right:0.8rem;color:var(--accent);width:1.8rem;}
    .sidebar .nav-link:hover{background:rgba(0,212,255,0.1);color:var(--accent);}
    .sidebar .nav-link.active{background:var(--accent);color:var(--bg);font-weight:600;}
    .main-content{margin-left:260px;padding:1.5rem;position:relative;z-index:1;}
    .top-navbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0.8rem 1.5rem;border-radius:16px;margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;}
    .card{background:var(--card);border:1px solid var(--border);border-radius:16px;transition:transform 0.2s;margin-bottom:1.5rem;}
    .card:hover{transform:translateY(-2px);}
    .btn-primary{background:var(--accent);border:none;color:var(--bg);font-weight:600;cursor:pointer;}
    .btn-primary:hover{background:#33deff;}
    .btn-outline-accent{border:1px solid var(--accent);color:var(--accent);background:transparent;}
    .btn-outline-accent:hover{background:rgba(0,212,255,0.1);}
    .btn-success{background:var(--green);border:none;color:white;}
    .form-control,.form-select{background:var(--surface);border:1px solid var(--border);color:var(--text);}
    .form-control:focus{background:var(--surface);border-color:var(--accent);color:var(--text);box-shadow:none;}
    .table{color:var(--text);}
    .table thead th{border-bottom-color:var(--border);}
    .alert{padding:0.7rem;border-radius:10px;margin-bottom:1rem;}
    .alert-danger{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;}
    .alert-success{background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;}
    hr{border-color:var(--border);}
    .text-muted{color:var(--muted)!important;}
  </style>
</head>
<body>

<div class="bg-grid"></div>
<div class="bg-glow glow1"></div>
<div class="bg-glow glow2"></div>

<div class="sidebar p-3">
  <a href="index.php" style="text-decoration:none;">
    <h4 class="text-center mb-4" style="color:var(--accent);"><i class="fas fa-parking"></i> ParkSmart<br><small style="font-size:0.7rem;color:var(--muted);">Driver Portal</small></h4>
  </a>
  <hr style="border-color:var(--border);">
  <div class="nav flex-column">
    <a class="nav-link <?= $page=='dashboard'?'active':'' ?>" href="index.php?controller=driver&action=dashboard"><i class="fas fa-home"></i> Dashboard</a>
    <a class="nav-link <?= $page=='search'?'active':'' ?>" href="index.php?controller=driver&action=search"><i class="fas fa-search"></i> Find Parking</a>
    <a class="nav-link <?= $page=='book'?'active':'' ?>" href="index.php?controller=driver&action=book"><i class="fas fa-calendar-check"></i> Book a Spot</a>
    <a class="nav-link <?= $page=='payment'?'active':'' ?>" href="index.php?controller=driver&action=payment"><i class="fas fa-credit-card"></i> Payments</a>
    <a class="nav-link <?= $page=='history'?'active':'' ?>" href="index.php?controller=driver&action=history"><i class="fas fa-history"></i> History</a>
    <a class="nav-link <?= $page=='extension'?'active':'' ?>" href="index.php?controller=driver&action=extension"><i class="fas fa-clock"></i> Extension</a>
    <a class="nav-link <?= $page=='appeal'?'active':'' ?>" href="index.php?controller=driver&action=appeal"><i class="fas fa-file-alt"></i> Appeal</a>
    <a class="nav-link <?= $page=='loyalty'?'active':'' ?>" href="index.php?controller=driver&action=loyalty"><i class="fas fa-gem"></i> Loyalty</a>
  </div>
  <hr style="border-color:var(--border);">
  <a href="index.php?controller=auth&action=logout" class="btn btn-danger w-100"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
  <div class="top-navbar d-flex justify-content-between align-items-center">
    <h5 class="mb-0" style="color:var(--text);"><?= $pageTitle ?></h5>
    <div class="d-flex align-items-center gap-3">
      <button onclick="toggleTheme()" class="btn btn-sm btn-outline-accent" style="font-size:1.2rem;" title="Switch theme"><span id="themeIcon">🌙</span></button>
      <span class="badge bg-success"><i class="fas fa-user"></i> <?= htmlspecialchars($driverName) ?></span>
    </div>
  </div>

  <div class="p-4">
    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <!-- سجل المدفوعات -->
    <div class="card p-4">
      <h5><i class="fas fa-credit-card"></i> Payment History</h5>
      <hr>
      <?php if (!empty($payments)): ?>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Date</th><th>Spot</th><th>Amount</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($payments as $p): ?>
                <tr>
                  <td><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                  <td><?= htmlspecialchars($p['spot_number'] ?? 'N/A') ?></td>
                  <td>$<?= number_format($p['amount'], 2) ?></td>
                  <td><span class="badge bg-<?= $p['status'] === 'completed' ? 'success' : 'warning' ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted">No payments yet.</p>
      <?php endif; ?>
    </div>

    <!-- حجوزات غير مدفوعة -->
    <?php if (!empty($reservations)): ?>
    <div class="card p-4">
      <h5><i class="fas fa-receipt"></i> Unpaid Reservations</h5>
      <hr>
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Spot</th><th>Start</th><th>End</th><th>Amount</th><th>Pay</th></tr></thead>
          <tbody>
            <?php foreach ($reservations as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['spot_number']) ?></td>
                <td><?= date('M d, H:i', strtotime($r['start_time'])) ?></td>
                <td><?= date('M d, H:i', strtotime($r['end_time'])) ?></td>
                <td>$<?= number_format($r['amount'], 2) ?></td>
                <td>
                  <form method="post" action="index.php?controller=driver&action=processPayment" style="display:inline;">
                    <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="amount" value="<?= $r['amount'] ?>">
                    <button type="submit" class="btn btn-success btn-sm">Pay Now</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- إضافة وسيلة دفع -->
    <div class="card p-4">
      <h5><i class="fas fa-wallet"></i> Add Payment Method</h5>
      <hr>
      <form method="post" action="index.php?controller=driver&action=addPaymentMethod">
        <div class="row">
          <div class="col-md-3 mb-2">
            <input type="text" name="card_holder" class="form-control" placeholder="Card Holder" required>
          </div>
          <div class="col-md-4 mb-2">
            <input type="text" name="card_number" class="form-control" placeholder="Card Number (16 digits)" maxlength="19" required>
          </div>
          <div class="col-md-2 mb-2">
            <input type="number" name="exp_month" class="form-control" placeholder="MM" min="1" max="12" required>
          </div>
          <div class="col-md-2 mb-2">
            <input type="number" name="exp_year" class="form-control" placeholder="YYYY" min="2026" max="2036" required>
          </div>
          <div class="col-md-1 mb-2">
            <button type="submit" class="btn btn-primary w-100">Add</button>
          </div>
        </div>
      </form>
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