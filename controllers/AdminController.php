<?php
// controllers/AdminController.php
require_once 'core/DBController.php';
require_once 'includes/auth.php';

class AdminController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // ========== Dashboard ==========
    public function dashboard()
    {
        requireRole('admin');

        $totalUsers = $this->db->select("SELECT COUNT(*) AS cnt FROM users WHERE status = 'active'")[0]['cnt'] ?? 0;
        $activeBookings = $this->db->select("SELECT COUNT(*) AS cnt FROM reservations WHERE status = 'active'")[0]['cnt'] ?? 0;
        $totalRevenue = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status = 'completed'")[0]['total'] ?? 0;
        $blacklistCount = $this->db->select("SELECT COUNT(*) AS cnt FROM users WHERE status = 'blocked'")[0]['cnt'] ?? 0;

        // أحدث الحجوزات (نشاط حديث)
        $recentActivities = $this->db->select("
            SELECT r.id, ps.spot_number, r.start_time
            FROM reservations r
            JOIN parking_spots ps ON r.spot_id = ps.id
            ORDER BY r.created_at DESC LIMIT 5
        ");

        // الإيرادات اليومية / الأسبوعية / الشهرية
        $revenueOverview = [
            'daily'   => $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND DATE(created_at)=CURDATE()")[0]['total'] ?? 0,
            'weekly'  => $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND YEARWEEK(created_at)=YEARWEEK(NOW())")[0]['total'] ?? 0,
            'monthly' => $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")[0]['total'] ?? 0,
        ];

        $adminName = $_SESSION['name'];
        require 'views/admin/dashboard.php';
    }

    // ========== Blacklist ==========
    public function blacklist()
    {
        requireRole('admin');
        $blacklisted = $this->db->select("SELECT id, name, email, block_reason, updated_at FROM users WHERE status = 'blocked' ORDER BY updated_at DESC");
        $adminName = $_SESSION['name'];
        require 'views/admin/blacklist.php';
    }

    public function addToBlacklist()
    {
        requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $reason = trim($_POST['reason'] ?? '');
            if ($email && $reason) {
                $user = $this->db->select("SELECT id FROM users WHERE email = '".$this->db->escape($email)."'");
                if (!empty($user)) {
                    $reasonEsc = $this->db->escape($reason);
                    $this->db->execute("UPDATE users SET status = 'blocked', block_reason = '$reasonEsc' WHERE id = {$user[0]['id']}");
                    $_SESSION['msg'] = "User blacklisted successfully.";
                } else {
                    $_SESSION['err'] = "Email not found.";
                }
            } else {
                $_SESSION['err'] = "All fields are required.";
            }
        }
        header('Location: index.php?controller=admin&action=blacklist');
        exit;
    }

    public function removeFromBlacklist()
    {
        requireRole('admin');
        if (isset($_POST['user_id'])) {
            $id = (int)$_POST['user_id'];
            $this->db->execute("UPDATE users SET status = 'active', block_reason = NULL WHERE id = $id");
            $_SESSION['msg'] = "User removed from blacklist.";
        }
        header('Location: index.php?controller=admin&action=blacklist');
        exit;
    }

    // ========== Users ==========
    public function users()
    {
        requireRole('admin');
        $users = $this->db->select("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC");
        $adminName = $_SESSION['name'];
        require 'views/admin/users.php';
    }

    // ========== Fines ==========
    public function fines()
    {
        requireRole('admin');
        $fines = $this->db->select("
            SELECT f.*, u.name AS driver_name, u.email
            FROM fines f JOIN users u ON f.user_id = u.id
            ORDER BY f.created_at DESC
        ");
        $adminName = $_SESSION['name'];
        require 'views/admin/fines.php';
    }

    public function addFine()
    {
        requireRole('admin');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $violation = trim($_POST['violation_type'] ?? 'Overstay');
            $mins = (int)($_POST['minutes'] ?? 30);
            $amount = $mins * 0.5;

            $user = $this->db->select("SELECT id FROM users WHERE email = '".$this->db->escape($email)."'");
            if (!empty($user)) {
                $reasonEsc = $this->db->escape("$violation ({$mins} min)");
                $this->db->execute("INSERT INTO fines (user_id, reservation_id, amount, reason, status, created_at) VALUES ({$user[0]['id']}, NULL, $amount, '$reasonEsc', 'issued', NOW())");
                $_SESSION['msg'] = "Fine issued to $name for \$$amount.";
            } else {
                $_SESSION['err'] = "Driver email not found.";
            }
        }
        header('Location: index.php?controller=admin&action=fines');
        exit;
    }

    // ========== Reports ==========
    public function reports()
    {
        requireRole('admin');
        $period = $_GET['period'] ?? 'monthly';

        $totalRevenue = 0;
        switch ($period) {
            case 'daily': $totalRevenue = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND DATE(created_at)=CURDATE()")[0]['total'] ?? 0; break;
            case 'weekly': $totalRevenue = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND YEARWEEK(created_at)=YEARWEEK(NOW())")[0]['total'] ?? 0; break;
            case 'monthly': $totalRevenue = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")[0]['total'] ?? 0; break;
            case 'yearly': $totalRevenue = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND YEAR(created_at)=YEAR(NOW())")[0]['total'] ?? 0; break;
        }

        $parkingFees = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed'")[0]['total'] ?? 0;
        $finesTotal  = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM fines WHERE status='paid'")[0]['total'] ?? 0;

        $adminName = $_SESSION['name'];
        require 'views/admin/reports.php';
    }

    public function generateReport()
    {
        requireRole('admin');
        $period = $_GET['period'] ?? 'monthly';
        $totalRevenue = 0;
        switch ($period) {
            case 'daily': $totalRevenue = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND DATE(created_at)=CURDATE()")[0]['total'] ?? 0; break;
            case 'weekly': $totalRevenue = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND YEARWEEK(created_at)=YEARWEEK(NOW())")[0]['total'] ?? 0; break;
            case 'monthly': $totalRevenue = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")[0]['total'] ?? 0; break;
            case 'yearly': $totalRevenue = $this->db->select("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE status='completed' AND YEAR(created_at)=YEAR(NOW())")[0]['total'] ?? 0; break;
        }

        echo "<!DOCTYPE html><html><head><title>Report</title><style>body{font-family:sans-serif;padding:20px;}h1{color:#333;}</style></head><body>";
        echo "<h1>Revenue Report ($period)</h1>";
        echo "<h2>Total: $" . number_format($totalRevenue, 2) . "</h2>";
        echo "<p>Generated on " . date('Y-m-d H:i:s') . "</p>";
        echo "<script>window.print();</script></body></html>";
        exit;
    }

    // ========== Waitlist ==========
    public function waitlist()
    {
        requireRole('admin');
        $entries = $this->db->select("
            SELECT w.id, u.name AS username, u.email, ps.spot_number, ps.location, w.date, w.position, w.status
            FROM waitlist w
            JOIN users u ON w.user_id = u.id
            JOIN parking_spots ps ON w.spot_id = ps.id
            ORDER BY w.date DESC, w.position ASC
        ");
        $adminName = $_SESSION['name'];
        require 'views/admin/waitlist.php';
    }

    public function assignWaitlist()
    {
        requireRole('admin');
        if (isset($_POST['waitlist_id'])) {
            $id = (int)$_POST['waitlist_id'];
            $this->db->execute("UPDATE waitlist SET status = 'assigned' WHERE id = $id");
            $_SESSION['msg'] = "Spot assigned to user.";
        }
        header('Location: index.php?controller=admin&action=waitlist');
        exit;
    }
}