<?php
// controllers/QRController.php – نسخة PHP مباشرة (بدون أي JSON)
require_once 'core/DBController.php';
require_once 'includes/auth.php';

class QRController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // عرض صفحة الماسح الضوئي (عامة، لا تحتاج تسجيل)
    public function scanner()
    {
        require 'views/qr/qr-scanner.php';
    }

    // تنفيذ check‑in (من نموذج POST)
    public function checkIn()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $spotId = (int)($_POST['spot_id'] ?? 0);
            $qrCode = trim($_POST['qr_code'] ?? '');

            if ($spotId && $qrCode) {
                $userId = $_SESSION['user_id'];
                $qrEsc  = $this->db->escape($qrCode);
                $this->db->execute("INSERT INTO qr_logs (user_id, spot_id, qr_code, check_in_time, status) VALUES ($userId, $spotId, '$qrEsc', NOW(), 'checked_in')");
                $_SESSION['qr_msg'] = "Checked in successfully.";
            } else {
                $_SESSION['qr_err'] = "Missing spot ID or QR code.";
            }
        }
        header('Location: index.php?controller=qr&action=scanner');
        exit;
    }

    // تنفيذ check‑out (من نموذج POST)
    public function checkOut()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $spotId = (int)($_POST['spot_id'] ?? 0);
            if ($spotId) {
                $userId = $_SESSION['user_id'];
                $this->db->execute("UPDATE qr_logs SET check_out_time = NOW(), status = 'checked_out' WHERE user_id = $userId AND spot_id = $spotId AND status = 'checked_in'");
                $_SESSION['qr_msg'] = "Checked out successfully.";
            } else {
                $_SESSION['qr_err'] = "Spot ID required.";
            }
        }
        header('Location: index.php?controller=qr&action=scanner');
        exit;
    }

    // عرض سجل QR للمستخدم (اختياري)
    public function history()
    {
        requireLogin();
        $userId = $_SESSION['user_id'];
        $logs = $this->db->select("SELECT * FROM qr_logs WHERE user_id = $userId ORDER BY check_in_time DESC");
        $driverName = $_SESSION['name'];
        require 'views/driver/qr-history.php'; // أنشئها إن أردت
    }
}