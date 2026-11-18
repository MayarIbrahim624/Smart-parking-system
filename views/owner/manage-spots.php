<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    header('Location: index.php?controller=auth&action=login&error=Please+login+as+owner');
    exit;
}
$page = 'manage';
$pageTitle = 'Manage Your Spots';
$ownerName = $_SESSION['name'] ?? 'Owner';
// البيانات من OwnerController
$spots = $spots ?? [];
$msg = $_SESSION['msg'] ?? '';
$err = $_SESSION['err'] ?? '';
unset($_SESSION['msg'], $_SESSION['err']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Spots - ParkSmart</title>
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
    .btn-outline-accent{border:1px solid var(--accent);color:var(--accent);background:transparent;cursor:pointer;}
    .btn-outline-accent:hover{background:rgba(124,58,237,0.1);}
    .btn-success{background:var(--green);border:none;color:white;}
    .btn-danger{background:var(--red);border:none;}
    .form-control,.form-select{background:var(--surface);border:1px solid var(--border);color:var(--text);}
    .form-control:focus,.form-select:focus{background:var(--surface);border-color:var(--accent);color:var(--text);box-shadow:none;}
    .table{color:var(--text);}
    .table thead th{border-bottom-color:var(--border);}
    .blind-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:6px;max-width:250px;margin:1rem 0;}
    .blind-cell{aspect-ratio:1;border-radius:6px;background:#334155;}
    .blind-cell.is-clear{background:var(--green);}
    .blind-cell.is-risk{background:var(--orange);}
    .chat-thread{border:1px solid var(--border);border-radius:12px;height:200px;overflow-y:auto;padding:0.75rem;background:#0f1219;margin-bottom:0.5rem;}
    .chat-bubble{max-width:85%;padding:0.45rem 0.65rem;border-radius:12px;margin-bottom:0.5rem;font-size:0.9rem;}
    .chat-bubble.them{background:var(--surface);margin-right:auto;color:var(--text);}
    .chat-bubble.me{background:rgba(124,58,237,0.3);margin-left:auto;color:var(--accent2);}
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
    <li class="nav-item"><a class="nav-link <?= $page=='dashboard'?'active':'' ?>" href="index.php?controller=owner&action=dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
    <li class="nav-item"><a class="nav-link <?= $page=='manage'?'active':'' ?>" href="index.php?controller=owner&action=manageSpots"><i class="fas fa-cogs"></i> Manage Spots</a></li>
    <li class="nav-item"><a class="nav-link <?= $page=='analytics'?'active':'' ?>" href="index.php?controller=owner&action=analytics"><i class="fas fa-chart-bar"></i> Analytics</a></li>
  </ul>
  <hr style="border-color:var(--border);">
  <a href="index.php?controller=auth&action=logout" class="btn btn-danger w-100"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

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

    <!-- My Parking Spots List -->
    <div class="card p-4 mb-4">
      <h5><i class="fas fa-parking"></i> My Parking Spots</h5>
      <hr>
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Spot #</th><th>Location</th><th>Rate/hr</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if (!empty($spots)): ?>
              <?php foreach ($spots as $s): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($s['spot_number']) ?></strong></td>
                  <td><?= htmlspecialchars($s['location']) ?></td>
                  <td>$<?= number_format($s['hourly_rate'], 2) ?></td>
                  <td><span class="badge bg-<?= $s['status'] === 'available' ? 'success' : 'warning' ?>"><?= htmlspecialchars($s['status']) ?></span></td>
                  <td>
                    <button class="btn btn-sm btn-outline-accent" onclick="editSpot(<?= $s['id'] ?>)">Edit</button>
                    <form method="post" action="index.php?controller=owner&action=deleteSpot" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                      <input type="hidden" name="spot_id" value="<?= $s['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="5">No spots yet. Add one below.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <button class="btn btn-primary mt-2" onclick="document.getElementById('addSpotForm').style.display='block'">
        <i class="fas fa-plus"></i> Add New Spot
      </button>

      <div id="addSpotForm" class="mt-3" style="display:none;">
        <h6>Register a New Parking Spot</h6>
        <form method="post" action="index.php?controller=owner&action=addSpot">
          <div class="row">
            <div class="col-md-6 mb-2">
              <input type="text" name="location" class="form-control" placeholder="Location (e.g. Downtown Mall)" required>
            </div>
            <div class="col-md-3 mb-2">
              <input type="text" name="spot_number" class="form-control" placeholder="Spot Number (e.g. A-15)" required>
            </div>
            <div class="col-md-3 mb-2">
              <input type="number" name="hourly_rate" class="form-control" placeholder="Hourly Rate ($)" step="0.5" value="5" required>
            </div>
          </div>
          <button type="submit" class="btn btn-success btn-sm">Save Spot</button>
          <button type="button" class="btn btn-outline-accent btn-sm" onclick="document.getElementById('addSpotForm').style.display='none'">Cancel</button>
        </form>
      </div>
    </div>

    <!-- Blind-Spot Prevention -->
    <div class="card p-4 mb-4">
      <h5><i class="fas fa-eye-slash"></i> Blind-Spot Prevention</h5>
      <p>Sensor / sightline grid - Green: clear, Orange: blind spot</p>
      <div class="blind-grid" id="blindGrid"></div>
      <button class="btn btn-primary mt-2" onclick="alert('Sensor calibration simulated successfully.')">Recalibrate Sensors</button>
    </div>

    <!-- Filter Engine -->
    <div class="card p-4 mb-4">
      <h5><i class="fas fa-sliders-h"></i> Filter Engine (Height/Width/EV)</h5>
      <div class="row">
        <div class="col-md-4"><label>Max vehicle height (m)</label><input type="number" id="maxH" class="form-control" value="2.1" step="0.1"></div>
        <div class="col-md-4"><label>Min vehicle width (m)</label><input type="number" id="minW" class="form-control" value="2.4" step="0.1"></div>
        <div class="col-md-4"><label><input type="checkbox" id="evReq"> EV charging required</label></div>
      </div>
      <button class="btn btn-primary mt-3" onclick="applyFilters()">Apply Filters</button>
      <div id="filterOut" class="mt-3 alert alert-info">Drivers outside height 2.1m / width 2.4m will not see this listing.</div>
    </div>

    <!-- Owner Verification -->
    <div class="card p-4 mb-4">
      <h5><i class="fas fa-id-card"></i> Owner Verification Workflow</h5>
      <p>Current status: <strong id="verifStatus">Pending verification</strong></p>
      <ul>
        <li>✓ Identity document uploaded</li>
        <li>✓ Proof of authority</li>
        <li id="bankConfirm">○ Bank account confirmation (pending)</li>
      </ul>
      <button class="btn btn-primary" onclick="document.getElementById('verifStatus').innerHTML='Verified ✓'; document.getElementById('bankConfirm').innerHTML='✓ Bank account confirmed';">Upload remaining documents</button>
    </div>

    <!-- Encrypted P2P Messaging -->
    <div class="card p-4 mb-4">
      <h5><i class="fas fa-lock"></i> Encrypted P2P Messaging</h5>
      <div class="chat-thread" id="chatThread">
        <div class="chat-bubble them">Please use the east entrance after 6pm.<br><small class="text-muted">10:32</small></div>
        <div class="chat-bubble me">Got it, thanks.<br><small class="text-muted">10:35</small></div>
      </div>
      <div class="input-group mt-2">
        <input type="text" id="msgInput" class="form-control" placeholder="Type your message...">
        <button class="btn btn-primary" onclick="sendMessage()">Send <i class="fas fa-lock"></i></button>
      </div>
      <p class="text-muted mt-2">🔒 End-to-end encrypted</p>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function setTheme(t){document.body.classList.toggle('light-mode',t==='light');document.getElementById('themeIcon').textContent=t==='light'?'☀️':'🌙';localStorage.setItem('theme',t);}
  function toggleTheme(){const c=localStorage.getItem('theme')||'dark';setTheme(c==='dark'?'light':'dark');}
  (function(){setTheme(localStorage.getItem('theme')||'dark');})();

  // Blind-spot grid
  let blindCells = '';
  for(let i=0;i<25;i++) {
    let cls = (i%7===0 || i===12 || i===18) ? 'is-risk' : 'is-clear';
    blindCells += `<div class="blind-cell ${cls}"></div>`;
  }
  document.getElementById('blindGrid').innerHTML = blindCells;

  function applyFilters() {
    let h = document.getElementById('maxH').value;
    let w = document.getElementById('minW').value;
    let ev = document.getElementById('evReq').checked;
    document.getElementById('filterOut').innerHTML = `Drivers outside height ${h}m / width ${w}m ${ev ? 'and without EV' : ''} will not see this listing.`;
  }

  // Messaging (demo)
  let messages = [
    { sender: "driver", text: "Please use the east entrance after 6pm.", time: "10:32" },
    { sender: "owner", text: "Got it, thanks.", time: "10:35" }
  ];
  function renderChat() {
    const chatThread = document.getElementById('chatThread');
    chatThread.innerHTML = messages.map(m => 
      `<div class="chat-bubble ${m.sender === 'owner' ? 'me' : 'them'}">${m.text}<br><small class="text-muted">${m.time}</small></div>`
    ).join('');
  }
  function sendMessage() {
    let input = document.getElementById('msgInput');
    let text = input.value.trim();
    if(!text) return;
    messages.push({ sender: 'owner', text: text, time: new Date().toLocaleTimeString() });
    renderChat();
    input.value = '';
  }
  renderChat();

  function editSpot(id) {
    alert('Edit spot #' + id + ' functionality to be implemented.');
  }
</script>
</body>
</html>