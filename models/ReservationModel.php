<?php
// models/ReservationModel.php – متوافق مع DBController (mysqli)
require_once __DIR__ . '/../core/DBController.php';

class ReservationModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // Create reservation
    public function createReservation($user_id, $spot_id, $start_time, $end_time, $amount)
    {
        $user_id    = (int)$user_id;
        $spot_id    = (int)$spot_id;
        $start_time = $this->db->escape($start_time);
        $end_time   = $this->db->escape($end_time);
        $amount     = (float)$amount;

        $query = "INSERT INTO reservations (user_id, spot_id, start_time, end_time, amount, status, created_at) 
                  VALUES ($user_id, $spot_id, '$start_time', '$end_time', $amount, 'active', NOW())";
        return $this->db->insert($query);
    }

    // Get reservation
    public function getReservation($id)
    {
        $id = (int)$id;
        $result = $this->db->select("
            SELECT r.*, u.email, u.name, ps.spot_number, ps.location 
            FROM reservations r 
            JOIN users u ON r.user_id = u.id 
            JOIN parking_spots ps ON r.spot_id = ps.id 
            WHERE r.id = $id
        ");
        return $result[0] ?? null;
    }

    // Get user reservations
    public function getUserReservations($user_id, $status = null)
    {
        $user_id = (int)$user_id;
        $query = "
            SELECT r.*, ps.spot_number, ps.location, ps.hourly_rate
            FROM reservations r
            JOIN parking_spots ps ON r.spot_id = ps.id
            WHERE r.user_id = $user_id
        ";
        if ($status) {
            $status = $this->db->escape($status);
            $query .= " AND r.status = '$status'";
        }
        $query .= " ORDER BY r.start_time DESC";

        return $this->db->select($query);
    }

    // Cancel reservation
    public function cancelReservation($id)
    {
        $id = (int)$id;
        return $this->db->execute("UPDATE reservations SET status = 'cancelled', updated_at = NOW() WHERE id = $id");
    }

    // Check grace period (15 minutes after booking ends)
    public function isInGracePeriod($user_id, $spot_id)
    {
        $user_id = (int)$user_id;
        $spot_id = (int)$spot_id;
        $result = $this->db->select("
            SELECT * FROM reservations 
            WHERE user_id = $user_id AND spot_id = $spot_id AND status = 'active'
            AND TIMESTAMPDIFF(MINUTE, end_time, NOW()) <= 15
            AND TIMESTAMPDIFF(MINUTE, end_time, NOW()) > 0
        ");
        return !empty($result);
    }

    // Get overstay time (in minutes)
    public function getOverstayTime($reservation_id)
    {
        $reservation_id = (int)$reservation_id;
        $result = $this->db->select("
            SELECT TIMESTAMPDIFF(MINUTE, end_time, NOW()) as overstay_minutes 
            FROM reservations WHERE id = $reservation_id
        ");
        return max(0, $result[0]['overstay_minutes'] ?? 0);
    }

    // Complete reservation
    public function completeReservation($id)
    {
        $id = (int)$id;
        return $this->db->execute("UPDATE reservations SET status = 'completed', updated_at = NOW() WHERE id = $id");
    }

    // Get spot availability grid
    public function getAvailabilityGrid($date)
    {
        $date = $this->db->escape($date);
        return $this->db->select("
            SELECT 
                ps.id, ps.spot_number, ps.location,
                CASE 
                    WHEN r.id IS NOT NULL THEN 'reserved'
                    WHEN ps.status = 'maintenance' THEN 'maintenance'
                    ELSE 'available'
                END as status
            FROM parking_spots ps
            LEFT JOIN reservations r ON ps.id = r.spot_id 
                AND DATE(r.start_time) = '$date'
                AND r.status IN ('active', 'confirmed')
            ORDER BY ps.spot_number
        ");
    }
}