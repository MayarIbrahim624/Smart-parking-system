<?php
// controllers/AppealController.php - Appeal management (admin)
require_once 'core/DBController.php';
require_once 'includes/auth.php';

class AppealController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // عرض قائمة الاستئنافات (للأدمن)
    public function listAppeals()
    {
        requireRole('admin');

        $appeals = $this->db->select("
            SELECT a.id, a.fine_id, a.user_id, a.description, a.status,
                   u.name AS driver_name, u.email AS driver_email,
                   f.amount AS fine_amount, f.reason AS fine_reason
            FROM appeals a
            JOIN users u ON a.user_id = u.id
            JOIN fines f ON a.fine_id = f.id
            ORDER BY a.created_at DESC
        ");

        $adminName = $_SESSION['name'];
        $msg = $_SESSION['msg'] ?? '';
        $err = $_SESSION['err'] ?? '';
        unset($_SESSION['msg'], $_SESSION['err']);

        require 'views/admin/appeals.php';
    }

    // قبول استئناف
    public function approve()
    {
        requireRole('admin');
        if (isset($_POST['appeal_id'])) {
            $id = (int)$_POST['appeal_id'];
            // جلب الاستئناف لتحديث الغرامة المرتبطة
            $appeal = $this->db->select("SELECT fine_id FROM appeals WHERE id = $id");
            if (!empty($appeal)) {
                $fineId = $appeal[0]['fine_id'];
                $this->db->execute("UPDATE appeals SET status = 'approved' WHERE id = $id");
                $this->db->execute("UPDATE fines SET status = 'cancelled' WHERE id = $fineId");
                $_SESSION['msg'] = "Appeal #$id approved. Fine cancelled.";
            }
        }
        header('Location: index.php?controller=appeal&action=listAppeals');
        exit;
    }

    // رفض استئناف
    public function reject()
    {
        requireRole('admin');
        if (isset($_POST['appeal_id'])) {
            $id = (int)$_POST['appeal_id'];
            $reason = $_POST['rejection_reason'] ?? '';
            $reasonEsc = $this->db->escape($reason);
            $this->db->execute("UPDATE appeals SET status = 'rejected', rejection_reason = '$reasonEsc' WHERE id = $id");
            $_SESSION['msg'] = "Appeal #$id rejected.";
        }
        header('Location: index.php?controller=appeal&action=listAppeals');
        exit;
    }
}