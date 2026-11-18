<?php
// views/qr/qr-scanner.php
// أي بيانات تحتاجها الصفحة يمكن وضعها هنا، مثلاً اسم المشغل
$operatorName = $_SESSION['user']['name'] ?? 'Operator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>QR Scanner - ParkSmart IOT</title>
  <link rel="icon" href="https://img.icons8.com/color/48/000000/parking.png">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      background: #0a0e1a;
      color: #f1f5f9;
      font-family: 'Segoe UI', sans-serif;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .scanner-card {
      background: #161d2e;
      border: 1px solid rgba(255,255,255,0.07);
      border-radius: 28px;
      padding: 2rem;
      max-width: 500px;
      width: 100%;
      margin: 1rem;
    }
    .qr-box {
      background: white;
      width: 200px;
      height: 200px;
      margin: 1rem auto;
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    .qr-scan {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: #00d4ff;
      animation: scan 2s linear infinite;
    }
    @keyframes scan {
      0% { top: 0; }
      100% { top: 100%; }
    }
    .qr-placeholder {
      font-size: 4rem;
    }
    button {
      background: #00d4ff;
      border: none;
      padding: 0.8rem;
      border-radius: 12px;
      font-weight: bold;
      width: 100%;
      margin: 0.5rem 0;
      cursor: pointer;
      color: #0a0e1a;
    }
    .btn-danger { background: #ef4444; color: white; }
    .btn-warning { background: #f59e0b; color: white; }
    .status {
      text-align: center;
      padding: 1rem;
      border-radius: 12px;
      margin: 1rem 0;
    }
    .status.success { background: rgba(16,185,129,0.2); color: #10b981; }
    .status.error { background: rgba(239,68,68,0.2); color: #ef4444; }
    .status.info { background: rgba(0,212,255,0.2); color: #00d4ff; }
    table { width: 100%; margin-top: 1rem; color: #cbd5e1; }
    td { padding: 0.5rem; }
    hr { border-color: rgba(255,255,255,0.07); margin: 1rem 0; }
  </style>
</head>
<body>
<div class="scanner-card">
  <h2 style="text-align: center;">📷 QR Scanner</h2>
  <p style="text-align: center; color: #64748b;">IoT Gateway - Barrier Control</p>
  <?php if (!empty($operatorName)): ?>
    <p style="text-align: center; color: #64748b;">Operator: <strong><?= htmlspecialchars($operatorName) ?></strong></p>
  <?php endif; ?>

  <div class="qr-box">
    <div class="qr-scan"></div>
    <div class="qr-placeholder">📱</div>
  </div>

  <div id="statusDiv" class="status info">
    🔍 Ready to scan QR code...
  </div>

  <div style="display: flex; gap: 0.5rem;">
    <button id="scanBtn" onclick="simulateScan()">📷 Simulate Scan</button>
    <button id="manualBtn" onclick="manualEntry()">⌨️ Manual Entry</button>
  </div>

  <hr>

  <h3>🔐 Barrier Controls</h3>
  <div style="display: flex; gap: 0.8rem; margin: 1rem 0;">
    <button id="lockBtn" class="btn-warning" onclick="lockArea()">🔒 Lock Area</button>
    <button id="unlockBtn" onclick="unlockArea()">🔓 Unlock Area</button>
  </div>

  <hr>

  <h3>📋 Recent Activity</h3>
  <table>
    <thead><tr><th>Time</th><th>Event</th><th>Status</th></tr></thead>
    <tbody id="logTable">
      <tr><td>--:--</td><td>Waiting for scan</td><td>●</td></tr>
    </tbody>
  </table>

  <hr>
  <a href="/" style="color: #64748b; text-decoration: none;">← Back to Home</a>
</div>

<script>
  let isAreaLocked = false;
  let logs = [];

  function addLog(event, status, isError = false) {
    const time = new Date().toLocaleTimeString();
    logs.unshift({ time, event, status, isError });
    if (logs.length > 10) logs.pop();
    
    const logTable = document.getElementById('logTable');
    logTable.innerHTML = logs.map(log => `
      <tr style="color: ${log.isError ? '#ef4444' : '#10b981'}">
        <td>${log.time}</td><td>${log.event}</td><td>${log.status === 'success' ? '✅' : (log.isError ? '❌' : '🔄')}</td>
      </tr>
    `).join('');
  }

  function showStatus(message, type) {
    const statusDiv = document.getElementById('statusDiv');
    statusDiv.innerHTML = message;
    statusDiv.className = `status ${type}`;
  }

  function simulateScan() {
    if (isAreaLocked) {
      showStatus('❌ ERROR: Area is LOCKED! Cannot scan. Contact admin.', 'error');
      addLog('Scan attempted', 'blocked - area locked', true);
      return;
    }

    // محاكاة فحص QR (90% نجاح)
    const random = Math.random();
    if (random < 0.1) {
      showStatus('⚠️ ERROR: Invalid QR code. Please try again.', 'error');
      addLog('Invalid QR scan', 'error', true);
    } else {
      showStatus('✅ SUCCESS: QR verified! Check-in complete. Barrier opening...', 'success');
      addLog('QR Check-in successful', 'success');
    }
  }

  function manualEntry() {
    const code = prompt("Enter QR code manually:");
    if (code) {
      if (isAreaLocked) {
        showStatus('❌ Area locked! Cannot process entry.', 'error');
        addLog(`Manual entry ${code}`, 'blocked', true);
      } else {
        showStatus(`✅ Manual entry ${code} verified. Gate opening...`, 'success');
        addLog(`Manual entry ${code}`, 'success');
      }
    }
  }

  function lockArea() {
    isAreaLocked = true;
    showStatus('🔒 Area has been LOCKED. No entries allowed.', 'info');
    addLog('Area LOCKED by admin', 'locked');
    document.getElementById('lockBtn').style.opacity = '0.5';
    document.getElementById('unlockBtn').style.opacity = '1';
  }

  function unlockArea() {
    isAreaLocked = false;
    showStatus('🔓 Area UNLOCKED. Normal operations resumed.', 'success');
    addLog('Area UNLOCKED', 'unlocked');
    document.getElementById('lockBtn').style.opacity = '1';
    document.getElementById('unlockBtn').style.opacity = '0.5';
  }

  // بدء التشغيل مع حالة "مفتوح"
  unlockArea();
</script>
</body>
</html>