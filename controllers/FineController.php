<?php
// controllers/FineController.php (محوّل إلى PHP مباشر – إصدار احتياطي)
require_once 'core/DBController.php';
require_once 'models/UserModel.php';
require_once 'includes/auth.php';

class FineController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // عرض غرامات السائق (يمكن استدعاؤها من DriverController)
    public function myFines()
    {
        requireLogin();
        $userId = $_SESSION['user_id'];
        $fines = $this->db->select("
            SELECT f.id, f.amount, f.reason, f.status, f.created_at,
                   ps.spot_number, ps.location
            FROM fines f
            LEFT JOIN reservations r ON f.reservation_id = r.id
            LEFT JOIN parking_spots ps ON r.spot_id = ps.id
            WHERE f.user_id = $userId
            ORDER BY f.created_at DESC
        ");
        $driverName = $_SESSION['name'];
        $msg = $_SESSION['msg'] ?? '';
        $err = $_SESSION['err'] ?? '';
        unset($_SESSION['msg'], $_SESSION['err']);

        require 'views/driver/fines.php';   // يجب إنشاء هذه الصفحة لاحقًا أو الاكتفاء بالاستئناف
    }

    // دفع غرامة (من السائق)
    public function pay()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fineId = (int)($_POST['fine_id'] ?? 0);
            $amount = (float)($_POST['amount'] ?? 0);

            // التأكد من أن الغرامة تخص المستخدم
            $fine = $this->db->select("SELECT * FROM fines WHERE id = $fineId AND user_id = {$_SESSION['user_id']} AND status = 'issued'");
            if (!empty($fine) && $amount > 0) {
                $this->db->execute("UPDATE fines SET status = 'paid', amount_paid = $amount, paid_at = NOW() WHERE id = $fineId");
                $_SESSION['msg'] = "Fine #$fineId paid successfully.";
            } else {
                $_SESSION['err'] = "Invalid fine or already paid.";
            }
        }
        header('Location: index.php?controller=fine&action=myFines');
        exit;
    }

    // عرض جميع الغرامات للأدمن (طريقة بديلة عن AdminController)
    public function allFines()
    {
        requireRole('admin');
        $fines = $this->db->select("
            SELECT f.*, u.name AS driver_name, u.email
            FROM fines f
            JOIN users u ON f.user_id = u.id
            ORDER BY f.created_at DESC
        ");
        $adminName = $_SESSION['name'];
        require 'views/admin/fines.php';   // يمكن إعادة استخدام نفس صفحة الأدمن
    }
}