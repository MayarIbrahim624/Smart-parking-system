<?php
// views/authentication/register.php
if (session_status() === PHP_SESSION_NONE) session_start();
$error = $_SESSION['register_error'] ?? '';
$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_error'], $_SESSION['register_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ParkSmart</title>
    <link rel="icon" href="https://img.icons8.com/color/48/000000/parking.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --bg: #0a0e1a;
            --surface: #111827;
            --card: #161d2e;
            --accent: #00d4ff;
            --text: #f1f5f9;
            --muted: #64748b;
            --border: rgba(255,255,255,0.07);
        }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .auth-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1000px;
            width: 100%;
            gap: 2rem;
            align-items: center;
        }
        @media (max-width: 768px) {
            .auth-container { grid-template-columns: 1fr; }
            .auth-sidebar { display: none; }
        }
        .auth-form-wrapper {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 2.5rem;
        }
        .auth-sidebar {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 2.5rem;
        }
        .text-gradient { color: var(--accent); }
        .btn-gradient {
            background: var(--accent);
            border: none;
            color: var(--bg);
            font-weight: 600;
        }
        .btn-gradient:hover { background: #33deff; }
        .btn-outline-primary {
            border-color: var(--accent);
            color: var(--accent);
        }
        .btn-outline-primary:hover {
            background: rgba(0,212,255,0.1);
            border-color: var(--accent);
            color: var(--accent);
        }
        .form-control, .form-select {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
        }
        .form-control:focus, .form-select:focus {
            background: var(--surface);
            border-color: var(--accent);
            color: var(--text);
            box-shadow: none;
        }
        .input-group-text {
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--muted);
        }
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--muted);
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border);
        }
        .divider span { padding: 0 1rem; }
        .alert { padding: 0.7rem; border-radius: 10px; font-size: 0.9rem; margin-bottom: 1rem; }
        .alert-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; display: block; }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; display: block; }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="auth-form-wrapper">
        <div class="text-center mb-4">
            <h2 class="mb-2"><i class="fas fa-parking text-gradient"></i> ParkSmart</h2>
            <p class="text-muted">Create your account</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="role-selector mb-4">
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="role" id="driver" value="driver" checked>
                <label class="btn btn-outline-primary" for="driver">Driver</label>
                <input type="radio" class="btn-check" name="role" id="owner" value="owner">
                <label class="btn btn-outline-primary" for="owner">Space Owner</label>
            </div>
        </div>

        <form method="post" action="index.php?controller=auth&action=register">
            <div class="row g-2 mb-3">
                <div class="col-12">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" id="password" required>
                </div>
                <small class="text-muted">At least 8 characters</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" id="confirm" required>
                </div>
            </div>

            <button type="submit" class="btn btn-gradient w-100 mb-3 py-2">Create Account</button>
        </form>

        <p class="text-center text-muted mt-3">
            Already have an account? <a href="index.php?controller=auth&action=login" class="text-gradient text-decoration-none">Sign in</a>
        </p>
    </div>

    <div class="auth-sidebar">
        <h3 class="mb-4">Get Started Today</h3>
        <div class="benefits mb-4">
            <div class="benefit-item mb-3 d-flex align-items-start gap-2">
                <i class="fas fa-check-circle text-success mt-1"></i>
                <div>
                    <strong>Free Forever</strong>
                    <small class="d-block text-muted">No credit card required</small>
                </div>
            </div>
            <div class="benefit-item mb-3 d-flex align-items-start gap-2">
                <i class="fas fa-check-circle text-success mt-1"></i>
                <div>
                    <strong>Instant Access</strong>
                    <small class="d-block text-muted">Start parking in minutes</small>
                </div>
            </div>
            <div class="benefit-item mb-3 d-flex align-items-start gap-2">
                <i class="fas fa-check-circle text-success mt-1"></i>
                <div>
                    <strong>Secure & Safe</strong>
                    <small class="d-block text-muted">Bank-level encryption</small>
                </div>
            </div>
            <div class="benefit-item d-flex align-items-start gap-2">
                <i class="fas fa-check-circle text-success mt-1"></i>
                <div>
                    <strong>24/7 Support</strong>
                    <small class="d-block text-muted">We're always here to help</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // التحقق من تطابق كلمة المرور قبل الإرسال (اختياري)
    document.querySelector('form').addEventListener('submit', function(e) {
        const pass = document.getElementById('password').value;
        const confirm = document.getElementById('confirm').value;
        if (pass !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
        }
    });
</script>
</body>
</html>