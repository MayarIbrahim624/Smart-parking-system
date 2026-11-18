<?php
// models/ExtensionModel.php – متوافق مع DBController (mysqli)
require_once __DIR__ . '/../core/DBController.php';

class ExtensionModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // طلب تمديد
    public function requestExtension($reservation_id, $additional_hours, $additional_cost)
    {
        $reservation_id = (int)$reservation_id;
        $additional_hours = (int)$additional_hours;
        $additional_cost = (float)$additional_cost;

        $query = "INSERT INTO extensions (reservation_id, additional_hours, additional_cost, status, created_at) 
                  VALUES ($reservation_id, $additional_hours, $additional_cost, 'pending', NOW())";
        return $this->db->insert($query);
    }

    // جلب تمديد واحد
    public function getExtension($id)
    {
        $id = (int)$id;
        $result = $this->db->select("
            SELECT e.*, r.user_id, r.spot_id, r.end_time 
            FROM extensions e 
            JOIN reservations r ON e.reservation_id = r.id 
            WHERE e.id = $id
        ");
        return $result[0] ?? null;
    }

    // الموافقة على تمديد
    public function approveExtension($id, $new_end_time)
    {
        $id = (int)$id;
        $this->db->execute("UPDATE extensions SET status = 'approved', approved_at = NOW() WHERE id = $id");

        $extension = $this->getExtension($id);
        if ($extension) {
            $new_end_time = $this->db->escape($new_end_time);
            $reservation_id = (int)$extension['reservation_id'];
            return $this->db->execute("UPDATE reservations SET end_time = '$new_end_time' WHERE id = $reservation_id");
        }
        return false;
    }

    // رفض تمديد
    public function rejectExtension($id)
    {
        $id = (int)$id;
        return $this->db->execute("UPDATE extensions SET status = 'rejected', rejected_at = NOW() WHERE id = $id");
    }

    // تمديدات المستخدم
    public function getUserExtensions($user_id)
    {
        $user_id = (int)$user_id;
        return $this->db->select("
            SELECT e.*, r.spot_id, ps.spot_number 
            FROM extensions e 
            JOIN reservations r ON e.reservation_id = r.id 
            JOIN parking_spots ps ON r.spot_id = ps.id 
            WHERE r.user_id = $user_id 
            ORDER BY e.created_at DESC
        ");
    }
}