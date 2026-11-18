<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php?controller=auth&action=login&error=Please+login+as+admin');
    exit;
}
$page = 'appeals';
$pageTitle = 'Appeals Management';
$adminName = $_SESSION['name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Appeals - ParkSmart</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="icon" href="https://img.icons8.com/color/48/000000/parking.png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* نفس تنسيقات admin مع متغيرات :root */
    :root {--bg:#0a0e1a;--surface:#111827;--card:#161d2e;--accent:#00d4ff;--accent2:#7c3aed;--green:#10b981;--orange:#f59e0b;--red:#ef4444;--text:#f1f5f9;--muted:#64748b;--border:rgba(255,255,255,0.07);}
    body.light-mode {--bg:#f8fafc;--surface:#ffffff;--card:#ffffff;--text:#0f172a;--muted:#475569;--border:rgba(0,0,0,0.08);--accent:#0284c7;--accent2:#6d28d9;--green:#059669;--orange:#d97706;--red:#dc2626;}
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
    .top-bar{background:var(--surface);border-bottom:1px solid var(--border);padding:0.8rem 1.5rem;border-radius:16px;margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;}
    .card{background:var(--card);border:1px solid var(--border);border-radius:16px;transition:transform 0.2s;}
    .card:hover{transform:translateY(-2px);}
    .btn-primary{background:var(--accent);border:none;color:var(--bg);font-weight:600;}
    .btn-primary:hover{background:#33deff;}
    .btn-outline-accent{border:1px solid var(--accent);color:var(--accent);background:transparent;}
    .btn-outline-accent:hover{background:rgba(0,212,255,0.1);}
    .table{color:var(--text);}
    .table thead th{border-bottom-color:var(--border);}
    .alert{padding:0.7rem;border-radius:10px;margin-bottom:1rem;}
    .alert-danger{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;}
    .alert-success{background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981;}
  </style>
</head>
<body>
<div class="bg-grid"></div><div class="bg-glow glow1"></div><div class="bg-glow glow2"></div>

<!-- Sidebar -->
<div class="sidebar p-3">
  <a href="index.php" style="text-decoration:none;">
    <h4 class="text-center mb-4" style="color:var(--accent);"><i class="fas fa-parking"></i> ParkSmart<br><small style="font-size:0.7rem;color:var(--muted);">Municipal Admin Portal</small></h4>
  </a>
  <hr style="border-color:var(--border);">
  <div class="nav flex-column">
    <a class="nav-link <?= $page=='dashboard'?'active':'' ?>" href="index.php?controller=admin&action=dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a class="nav-link <?= $page=='blacklist'?'active':'' ?>" href="index.php?controller=admin&action=blacklist"><i class="fas fa-ban"></i> Blacklist</a>
    <a class="nav-link <?= $page=='waitlist'?'active':'' ?>" href="index.php?controller=admin&action=waitlist"><i class="fas fa-list-alt"></i> Waitlist</a>
    <a class="nav-link <?= $page=='appeals'?'active':'' ?>" href="index.php?controller=appeal&action=listAppeals"><i class="fas fa-file-alt"></i> Appeals</a>
    <a class="nav-link <?= $page=='fines'?'active':'' ?>" href="index.php?controller=admin&action=fines"><i class="fas fa-money-bill-wave"></i> Fines</a>
    <a class="nav-link <?= $page=='reports'?'active':'' ?>" href="index.php?controller=admin&action=reports"><i class="fas fa-chart-line"></i> Reports</a>
    <a class="nav-link <?= $page=='users'?'active':'' ?>" href="index.php?controller=admin&action=users"><i class="fas fa-users"></i> Users</a>
  </div>
  <hr><a href="index.php?controller=auth&action=logout" class="btn btn-danger w-100"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="main-content">
  <div class="top-bar">
    <h5 class="mb-0"><?= $pageTitle ?></h5>
    <span class="badge bg-info"><i class="fas fa-user-shield"></i> <?= htmlspecialchars($adminName) ?></span>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
  <?php elseif ($err): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <div class="card p-4">
    <table class="table">
      <thead><tr><th>ID</th><th>Driver</th><th>Fine</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (!empty($appeals)): ?>
          <?php foreach ($appeals as $a): ?>
            <tr>
              <td><?= $a['id'] ?></td>
              <td><?= htmlspecialchars($a['driver_name']) ?><br><small><?= htmlspecialchars($a['driver_email']) ?></small></td>
              <td>$<?= number_format($a['fine_amount'],2) ?> – <?= htmlspecialchars($a['fine_reason']) ?></td>
              <td><?= htmlspecialchars($a['description']) ?></td>
              <td><span class="badge bg-<?= $a['status']==='approved'?'success':($a['status']==='rejected'?'danger':'warning') ?>"><?= $a['status'] ?></span></td>
              <td>
                <?php if ($a['status'] === 'pending'): ?>
                  <form method="post" action="index.php?controller=appeal&action=approve" style="display:inline;">
                    <input type="hidden" name="appeal_id" value="<?= $a['id'] ?>">
                    <button class="btn btn-sm btn-success">Approve</button>
                  </form>
                  <form method="post" action="index.php?controller=appeal&action=reject" style="display:inline;">
                    <input type="hidden" name="appeal_id" value="<?= $a['id'] ?>">
                    <input type="text" name="rejection_reason" placeholder="Reason" class="form-control form-control-sm d-inline-block" style="width:120px;">
                    <button class="btn btn-sm btn-danger">Reject</button>
                  </form>
                <?php else: ?>
                  <span class="text-muted">Completed</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6">No appeals found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
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