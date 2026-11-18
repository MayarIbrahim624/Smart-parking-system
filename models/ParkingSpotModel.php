<?php
// models/ParkingSpotModel.php – متوافق مع DBController (mysqli)
require_once __DIR__ . '/../core/DBController.php';

class ParkingSpotModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // Create parking spot
    public function createSpot($owner_id, $location, $spot_number, $hourly_rate = 5, $status = 'available')
    {
        $owner_id    = (int)$owner_id;
        $location    = $this->db->escape($location);
        $spot_number = $this->db->escape($spot_number);
        $hourly_rate = (float)$hourly_rate;
        $status      = $this->db->escape($status);

        $query = "INSERT INTO parking_spots (owner_id, location, spot_number, hourly_rate, status, created_at) 
                  VALUES ($owner_id, '$location', '$spot_number', $hourly_rate, '$status', NOW())";
        return $this->db->insert($query);
    }

    // Get all spots
    public function getAllSpots()
    {
        return $this->db->select("
            SELECT ps.*, COUNT(r.id) as reservations_count 
            FROM parking_spots ps 
            LEFT JOIN reservations r ON ps.id = r.spot_id AND r.status = 'active'
            GROUP BY ps.id
            ORDER BY ps.spot_number
        ");
    }

    // Get spot details
    public function getSpot($id)
    {
        $id = (int)$id;
        $result = $this->db->select("SELECT * FROM parking_spots WHERE id = $id");
        return $result[0] ?? null;
    }

    // Search available spots (smart search with date/time)
    public function searchSpots($date, $start_time, $duration, $filters = [])
    {
        $date       = $this->db->escape($date);
        $start_time = $this->db->escape($start_time);
        $end_time   = date('H:i', strtotime("+{$duration} hours", strtotime($start_time)));
        $end_time   = $this->db->escape($end_time);

        $query = "
            SELECT ps.* FROM parking_spots ps
            WHERE ps.status = 'available'
            AND ps.id NOT IN (
                SELECT DISTINCT spot_id FROM reservations 
                WHERE DATE(start_time) = '$date' 
                AND (
                    (TIME(start_time) < '$end_time' AND TIME(end_time) > '$start_time')
                    OR (TIME(start_time) < '$end_time' AND TIME(end_time) > '$start_time')
                    OR (TIME(start_time) >= '$start_time' AND TIME(end_time) <= '$end_time')
                )
                AND status IN ('active', 'confirmed')
            )
        ";

        // Apply filters
        if (!empty($filters['max_height'])) {
            $query .= " AND max_vehicle_height >= " . (int)$filters['max_height'];
        }
        if (!empty($filters['max_width'])) {
            $query .= " AND max_vehicle_width >= " . (int)$filters['max_width'];
        }
        if (!empty($filters['ev_charging'])) {
            $query .= " AND has_ev_charging = 1";
        }

        return $this->db->select($query);
    }

    // Update spot status
    public function updateSpotStatus($id, $status)
    {
        $id     = (int)$id;
        $status = $this->db->escape($status);
        return $this->db->execute("UPDATE parking_spots SET status = '$status', updated_at = NOW() WHERE id = $id");
    }

    // Update spot pricing
    public function updatePricing($id, $hourly_rate, $daily_rate = null)
    {
        $id          = (int)$id;
        $hourly_rate = (float)$hourly_rate;
        $daily_rate  = $daily_rate !== null ? (float)$daily_rate : 'NULL';
        return $this->db->execute("UPDATE parking_spots SET hourly_rate = $hourly_rate, daily_rate = $daily_rate, updated_at = NOW() WHERE id = $id");
    }

    // Get spots by owner
    public function getOwnerSpots($owner_id)
    {
        $owner_id = (int)$owner_id;
        return $this->db->select("
            SELECT ps.*, COUNT(r.id) as active_reservations 
            FROM parking_spots ps 
            LEFT JOIN reservations r ON ps.id = r.spot_id AND r.status = 'active'
            WHERE ps.owner_id = $owner_id 
            GROUP BY ps.id
        ");
    }

    // Get spot analytics
    public function getSpotAnalytics($id)
    {
        $id = (int)$id;
        $analytics = [];

        // Occupancy rate
        $bookings = $this->db->select("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM reservations WHERE spot_id = $id
        ");
        $analytics['bookings'] = $bookings[0] ?? null;

        // Revenue
        $revenue = $this->db->select("
            SELECT COALESCE(SUM(amount), 0) as revenue 
            FROM payments 
            WHERE reservation_id IN (SELECT id FROM reservations WHERE spot_id = $id)
            AND status = 'completed'
        ");
        $analytics['revenue'] = $revenue[0]['revenue'] ?? 0;

        return $analytics;
    }
}