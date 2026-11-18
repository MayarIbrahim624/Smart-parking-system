<?php
// models/LoyaltyModel.php – متوافق مع DBController (mysqli)
require_once __DIR__ . '/../core/DBController.php';

class LoyaltyModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // Get user loyalty tier
    public function getUserTier($user_id)
    {
        $user_id = (int)$user_id;
        $result = $this->db->select("SELECT * FROM loyalty WHERE user_id = $user_id");
        return $result[0] ?? null;
    }

    // Add loyalty points
    public function addPoints($user_id, $points, $description = null)
    {
        $user_id = (int)$user_id;
        $points  = (int)$points;
        $tier    = $this->getUserTier($user_id);

        if (!$tier) {
            // إنشاء سجل ولاء جديد
            $this->db->execute("INSERT INTO loyalty (user_id, points, tier, created_at) VALUES ($user_id, $points, 'bronze', NOW())");
        } else {
            $new_points = $tier['points'] + $points;
            $new_tier   = $this->calculateTier($new_points);
            $this->db->execute("UPDATE loyalty SET points = $new_points, tier = '$new_tier', updated_at = NOW() WHERE user_id = $user_id");
        }

        // تسجيل العملية
        $desc = $description ? "'" . $this->db->escape($description) . "'" : "NULL";
        $this->db->execute("INSERT INTO loyalty_transactions (user_id, points, description, created_at) VALUES ($user_id, $points, $desc, NOW())");

        return true;
    }

    // Calculate tier based on points
    private function calculateTier($points)
    {
        if ($points >= 1000) return 'gold';
        if ($points >= 500) return 'silver';
        return 'bronze';
    }

    // Get discount by tier
    public function getTierDiscount($tier)
    {
        $discounts = ['bronze' => 0, 'silver' => 5, 'gold' => 15];
        return $discounts[$tier] ?? 0;
    }

    // Redeem points for discount balance
    public function redeemPoints($user_id, $points)
    {
        $user_id = (int)$user_id;
        $points  = (int)$points;
        $tier = $this->getUserTier($user_id);
        if (!$tier || $tier['points'] < $points) {
            return false;
        }

        // خصم النقاط وإضافة رصيد الخصم (بافتراض حقل discount_balance في الجدول)
        $discount = round($points / 100, 2);
        $this->db->execute("UPDATE loyalty SET points = points - $points, discount_balance = discount_balance + $discount, updated_at = NOW() WHERE user_id = $user_id");

        // تسجيل العملية
        $desc = "Redeemed $points points for $$discount discount";
        $this->db->execute("INSERT INTO loyalty_transactions (user_id, points, description, created_at) VALUES ($user_id, -$points, '$desc', NOW())");

        return true;
    }

    // Get loyalty history
    public function getHistory($user_id)
    {
        $user_id = (int)$user_id;
        return $this->db->select("SELECT * FROM loyalty_transactions WHERE user_id = $user_id ORDER BY created_at DESC");
    }
}