<?php
// views/authentication/login.php
if (session_status() === PHP_SESSION_NONE) session_start();
$error = $_GET['error'] ?? '';
if (empty($error)) {
    $error = $_SESSION['login_error'] ?? '';
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - ParkSmart</title>
  <link rel="icon" href="https://img.icons8.com/color/48/000000/parking.png">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      background: #0a0e1a;
      color: #f1f5f9;
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-card {
      background: #161d2e;
      border: 1px solid rgba(255,255,255,0.07);
      border-radius: 28px;
      padding: 2.5rem;
      width: 100%;
      max-width: 450px;
      margin: 1rem;
    }
    .logo {
      font-family: 'Syne', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      color: #00d4ff;
      text-align: center;
      text-decoration: none;
      margin-bottom: 1.5rem;
    }
    .logo a { text-decoration: none; color: #00d4ff; }
    h3 { text-align: center; margin: 0.5rem 0 1.5rem; font-weight: 500; }
    .role-buttons {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 1.5rem;
      background: #111827;
      padding: 0.3rem;
      border-radius: 14px;
    }
    .role-btn {
      flex: 1;
      background: transparent;
      border: none;
      padding: 0.7rem;
      border-radius: 10px;
      color: #64748b;
      cursor: pointer;
      font-weight: 500;
    }
    .role-btn.active { background: #00d4ff; color: #0a0e1a; }
    input {
      width: 100%;
      padding: 0.8rem;
      background: #111827;
      border: 1px solid rgba(255,255,255,0.07);
      border-radius: 12px;
      color: white;
      margin: 0.5rem 0;
      font-family: 'DM Sans', sans-serif;
    }
    button[type="submit"] {
      width: 100%;
      background: #00d4ff;
      border: none;
      padding: 0.8rem;
      border-radius: 12px;
      font-weight: bold;
      margin-top: 1rem;
      cursor: pointer;
      font-family: 'DM Sans', sans-serif;
      color: #0a0e1a;
    }
    .links { display: flex; justify-content: space-between; margin-top: 1rem; font-size: 0.8rem; }
    .links a { color: #64748b; text-decoration: none; }
    hr { border-color: rgba(255,255,255,0.07); margin: 1.5rem 0; }
    .register-link { text-align: center; font-size: 0.85rem; }
    .register-link a { color: #00d4ff; text-decoration: none; }
    .alert { padding: 0.7rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.9rem; }
    .alert-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; display: block; }
  </style>
</head>
<body>
<div class="login-card">
  <div class="logo"><a href="index.php">🅿 ParkSmart</a></div>
  <h3>Sign in to your account</h3>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="role-buttons" id="roleSelector">
    <button type="button" class="role-btn active" data-role="driver">🚗 Driver</button>
    <button type="button" class="role-btn" data-role="admin">⚙️ Admin</button>
    <button type="button" class="role-btn" data-role="owner">🏢 Owner</button>
  </div>

  <form method="post" action="index.php?controller=auth&action=login">
    <input type="hidden" name="role" id="roleInput" value="driver">
    <input type="email" name="email" id="emailField" placeholder="Email address" required>
    <input type="password" name="password" id="passwordField" placeholder="Password" required>
    <div class="links">
      <a href="index.php?controller=auth&action=resetPassword">Forgot password?</a>
      <a href="#" id="demoBtn">Demo credentials</a>
    </div>
    <button type="submit">Sign In →</button>
  </form>
  <hr>
  <div class="register-link">Don't have an account? <a href="index.php?controller=auth&action=register">Create Account</a></div>
</div>

<script>
  // Toggle role buttons
  document.querySelectorAll('.role-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('roleInput').value = btn.dataset.role;
    });
  });

  // Demo credentials – تملأ الحقول فقط، ولا ترسل النموذج
  document.getElementById('demoBtn').addEventListener('click', (e) => {
    e.preventDefault();
    const selectedRole = document.getElementById('roleInput').value;
    const emails = {
      driver: 'driver@parksmart.com',
      admin: 'admin@parksmart.com',
      owner: 'owner@parksmart.com'
    };
    document.getElementById('emailField').value = emails[selectedRole] || emails.driver;
    document.getElementById('passwordField').value = 'password123';
  });

  // إخفاء رسالة الخطأ عند بدء كتابة أي حقل (لتوضيح أن الخطأ قديم)
  const emailInput = document.getElementById('emailField');
  const passwordInput = document.getElementById('passwordField');
  function hideError() {
    const errDiv = document.querySelector('.alert-danger');
    if (errDiv) errDiv.style.display = 'none';
  }
  emailInput.addEventListener('input', hideError);
  passwordInput.addEventListener('input', hideError);
</script>
</body>
</html>