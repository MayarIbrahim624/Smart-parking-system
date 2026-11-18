<?php
// views/authentication/reset-password.php
if (session_status() === PHP_SESSION_NONE) session_start();
$error   = $_SESSION['reset_error'] ?? '';
$success = $_SESSION['reset_success'] ?? '';
unset($_SESSION['reset_error'], $_SESSION['reset_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - ParkSmart</title>
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
    .reset-card {
      background: #161d2e;
      border: 1px solid rgba(255,255,255,0.07);
      border-radius: 28px;
      padding: 2.5rem;
      width: 100%;
      max-width: 420px;
      margin: 1rem;
    }
    .logo {
      font-family: 'Syne', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      color: #00d4ff;
      text-align: center;
      margin-bottom: 1.5rem;
    }
    .logo a { text-decoration: none; color: inherit; }
    h3 { text-align: center; margin: 0.5rem 0 1.5rem; font-weight: 500; }
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
    button {
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
    .links { text-align: center; margin-top: 1.5rem; font-size: 0.9rem; }
    .links a { color: #00d4ff; text-decoration: none; }
    .alert { padding: 0.7rem; border-radius: 10px; margin-bottom: 1rem; font-size: 0.9rem; }
    .alert-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; display: block; }
    .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; display: block; }
  </style>
</head>
<body>
<div class="reset-card">
  <div class="logo"><a href="index.php">🅿 ParkSmart</a></div>
  <h3>Reset Your Password</h3>
  <p style="text-align:center; color: #64748b; margin-bottom:1.5rem;">
    Enter your email to receive a reset link.
  </p>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post" action="index.php?controller=auth&action=resetPassword">
    <input type="email" name="email" placeholder="Email address" required>
    <button type="submit">Send Reset Link</button>
  </form>
  <div class="links">
    <a href="index.php?controller=auth&action=login">← Back to Login</a>
  </div>
</div>
</body>
</html>