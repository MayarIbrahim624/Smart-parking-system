<?php
// controllers/WaitlistController.php – نسخة PHP مباشرة (بدون أي JSON)
require_once 'core/DBController.php';
require_once 'includes/auth.php';

class WaitlistController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // ========== انضمام السائق إلى قائمة الانتظار ==========
    public function join()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $spotId = (int)($_POST['spot_id'] ?? 0);
            $date   = $_POST['date'] ?? '';   // yyyy-mm-dd

            if ($spotId && $date) {
                $userId = $_SESSION['user_id'];
                // حساب المركز الجديد
                $pos = $this->db->select("SELECT COUNT(*) AS cnt FROM waitlist WHERE spot_id = $spotId AND date = '$date'")[0]['cnt'] + 1;
                $this->db->execute("INSERT INTO waitlist (user_id, spot_id, date, position, status, created_at) VALUES ($userId, $spotId, '$date', $pos, 'waiting', NOW())");
                $_SESSION['msg'] = "You have been added to the waitlist (position $pos).";
            } else {
                $_SESSION['err'] = "Spot and date are required.";
            }
        }
        // العودة إلى صفحة البحث أو لوحة السائق
        header('Location: index.php?controller=driver&action=search');
        exit;
    }

    // ========== مغادرة قائمة الانتظار ==========
    public function leave()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $spotId = (int)($_POST['spot_id'] ?? 0);
            $date   = $_POST['date'] ?? '';

            if ($spotId && $date) {
                $userId = $_SESSION['user_id'];
                $this->db->execute("UPDATE waitlist SET status = 'cancelled' WHERE user_id = $userId AND spot_id = $spotId AND date = '$date'");
                $_SESSION['msg'] = "You have been removed from the waitlist.";
            }
        }
        header('Location: index.php?controller=driver&action=search');
        exit;
    }

    // ========== عرض قائمة الانتظار للأدمن ==========
    public function adminList()
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
        $msg = $_SESSION['msg'] ?? '';
        $err = $_SESSION['err'] ?? '';
        unset($_SESSION['msg'], $_SESSION['err']);

        require 'views/admin/waitlist.php';
    }

    // ========== تعيين موقف لشخص في القائمة (أدمن) ==========
    public function assign()
    {
        requireRole('admin');
        if (isset($_POST['waitlist_id'])) {
            $id = (int)$_POST['waitlist_id'];
            $this->db->execute("UPDATE waitlist SET status = 'assigned' WHERE id = $id");
            $_SESSION['msg'] = "Spot assigned to user.";
        }
        header('Location: index.php?controller=waitlist&action=adminList');
        exit;
    }
}