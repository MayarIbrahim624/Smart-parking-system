<?php
// controllers/LoyaltyController.php (إصدار احتياطي)
require_once 'core/DBController.php';
require_once 'includes/auth.php';

class LoyaltyController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // عرض نقاط الولاء لجميع المستخدمين (للأدمن)
    public function adminView()
    {
        requireRole('admin');

        $users = $this->db->select("
            SELECT u.id, u.name, u.email, l.points, l.tier
            FROM users u
            LEFT JOIN loyalty l ON u.id = l.user_id
            WHERE u.role = 'driver'
            ORDER BY l.points DESC
        ");

        $adminName = $_SESSION['name'];
        require 'views/admin/loyalty.php';
    }

    // عرض صفحة الولاء لسائق معين (يدوياً بتمرير user_id)
    public function driverView()
    {
        requireLogin();
        $userId = $_SESSION['user_id'];

        $loyalty = $this->db->select("SELECT points, tier, discount_balance FROM loyalty WHERE user_id = $userId");
        $loyalty = $loyalty[0] ?? ['points' => 0, 'tier' => 'bronze', 'discount_balance' => 0.00];

        $transactions = $this->db->select("SELECT points, description, created_at FROM loyalty_transactions WHERE user_id = $userId ORDER BY created_at DESC LIMIT 20");

        $driverName = $_SESSION['name'];
        require 'views/driver/loyalty.php';   // يمكن إعادة استخدام صفحة السائق
    }
}