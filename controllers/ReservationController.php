<?php
// controllers/ReservationController.php – نسخة PHP مباشرة (بدون أي JSON)
require_once 'core/DBController.php';
require_once 'includes/auth.php';

class ReservationController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // إنشاء حجز (من نموذج POST)
    public function create()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $spotId    = (int)($_POST['spot_id'] ?? 0);
            $startTime = trim($_POST['start_time'] ?? '');
            $endTime   = trim($_POST['end_time'] ?? '');
            $amount    = (float)($_POST['amount'] ?? 0);

            if ($spotId && $startTime && $endTime && $amount > 0) {
                $userId    = $_SESSION['user_id'];
                $startEsc  = $this->db->escape($startTime);
                $endEsc    = $this->db->escape($endTime);
                $this->db->execute("INSERT INTO reservations (user_id, spot_id, start_time, end_time, amount, status, created_at) VALUES ($userId, $spotId, '$startEsc', '$endEsc', $amount, 'active', NOW())");
                $_SESSION['msg'] = "Reservation created.";
            } else {
                $_SESSION['err'] = "All fields are required.";
            }
        }
        header('Location: index.php?controller=driver&action=history'); // أو صفحة الحجز
        exit;
    }

    // عرض سجل حجوزات السائق
    public function getHistory()
    {
        requireLogin();
        $userId = $_SESSION['user_id'];
        $reservations = $this->db->select("
            SELECT r.*, ps.spot_number, ps.location
            FROM reservations r
            JOIN parking_spots ps ON r.spot_id = ps.id
            WHERE r.user_id = $userId
            ORDER BY r.start_time DESC
        ");
        $driverName = $_SESSION['name'];
        require 'views/driver/history.php';
    }

    // إلغاء حجز
    public function cancel()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reservationId = (int)($_POST['reservation_id'] ?? 0);
            $userId = $_SESSION['user_id'];
            // التأكد من أن الحجز يخص المستخدم
            $res = $this->db->select("SELECT id FROM reservations WHERE id = $reservationId AND user_id = $userId");
            if (!empty($res)) {
                $this->db->execute("UPDATE reservations SET status = 'cancelled', updated_at = NOW() WHERE id = $reservationId");
                $_SESSION['msg'] = "Reservation cancelled.";
            } else {
                $_SESSION['err'] = "Reservation not found.";
            }
        }
        header('Location: index.php?controller=reservation&action=getHistory');
        exit;
    }

    // أحدث الحجوزات (للأدمن)
    public function getRecentActivity()
    {
        requireRole('admin');
        $activities = $this->db->select("
            SELECT r.id, u.name AS user_name, ps.spot_number, r.start_time, r.end_time, r.status
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            JOIN parking_spots ps ON r.spot_id = ps.id
            ORDER BY r.created_at DESC
            LIMIT 10
        ");
        // يمكن عرضها مباشرة في صفحة dashboard، لكننا هنا نرجعها كجزء من صفحة الأدمن
        $adminName = $_SESSION['name'];
        // نعيد استخدام نفس صفحة dashboard أو نمرر البيانات
        // لكن في AdminController نمرر هذه البيانات عبر require
        // لذلك هذه الدالة قد لا تستخدم كثيراً
    }
}