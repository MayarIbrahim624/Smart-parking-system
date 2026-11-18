<?php
// models/FineModel.php – متوافق مع DBController (mysqli)
require_once __DIR__ . '/../core/DBController.php';

class FineModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // إنشاء غرامة
    public function generateFine($user_id, $reservation_id, $amount, $reason)
    {
        $user_id        = (int)$user_id;
        $reservation_id = $reservation_id ? (int)$reservation_id : 'NULL';
        $amount         = (float)$amount;
        $reason         = $this->db->escape($reason);

        $query = "INSERT INTO fines (user_id, reservation_id, amount, reason, status, created_at) 
                  VALUES ($user_id, $reservation_id, $amount, '$reason', 'issued', NOW())";
        return $this->db->insert($query);
    }

    // جلب غرامة واحدة
    public function getFine($id)
    {
        $id = (int)$id;
        $result = $this->db->select("SELECT * FROM fines WHERE id = $id");
        return $result[0] ?? null;
    }

    // غرامات مستخدم معين (مع تفاصيل الموقف)
    public function getUserFines($user_id)
    {
        $user_id = (int)$user_id;
        return $this->db->select("
            SELECT f.*, r.spot_id, ps.spot_number 
            FROM fines f 
            JOIN reservations r ON f.reservation_id = r.id 
            JOIN parking_spots ps ON r.spot_id = ps.id 
            WHERE f.user_id = $user_id 
            ORDER BY f.created_at DESC
        ");
    }

    // دفع غرامة
    public function payFine($fine_id, $amount_paid)
    {
        $fine_id     = (int)$fine_id;
        $amount_paid = (float)$amount_paid;
        return $this->db->execute("UPDATE fines SET status = 'paid', amount_paid = $amount_paid, paid_at = NOW() WHERE id = $fine_id");
    }

    // كشف التجاوزات (overstay)
    public function detectOverstay()
    {
        $query = "
            SELECT r.id, r.user_id, r.spot_id, r.end_time,
                   CEIL(TIMESTAMPDIFF(MINUTE, r.end_time, NOW()) / 60) * 5 AS fine_amount
            FROM reservations r
            WHERE r.status = 'active'
            AND NOW() > DATE_ADD(r.end_time, INTERVAL 15 MINUTE)
            AND r.id NOT IN (SELECT reservation_id FROM fines WHERE status = 'issued')
        ";
        return $this->db->select($query);
    }

    // إحصائيات الغرامات لمستخدم
    public function getFineStats($user_id)
    {
        $user_id = (int)$user_id;
        $result = $this->db->select("
            SELECT 
                COUNT(*) AS total_fines,
                SUM(CASE WHEN status = 'issued' THEN amount ELSE 0 END) AS outstanding,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS paid,
                AVG(amount) AS average_fine
            FROM fines 
            WHERE user_id = $user_id
        ");
        return $result[0] ?? null;
    }
}