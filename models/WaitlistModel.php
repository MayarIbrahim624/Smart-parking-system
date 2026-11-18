<?php
// models/WaitlistModel.php – متوافق مع DBController (mysqli)
require_once __DIR__ . '/../core/DBController.php';

class WaitlistModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // Join waitlist
    public function joinWaitlist($user_id, $spot_id, $date)
    {
        $user_id = (int)$user_id;
        $spot_id = (int)$spot_id;
        $date    = $this->db->escape($date);

        // Get next position
        $posResult = $this->db->select("SELECT COUNT(*) AS cnt FROM waitlist WHERE spot_id = $spot_id AND date = '$date'");
        $position = ($posResult[0]['cnt'] ?? 0) + 1;

        $query = "INSERT INTO waitlist (user_id, spot_id, date, position, status, created_at) 
                  VALUES ($user_id, $spot_id, '$date', $position, 'waiting', NOW())";
        return $this->db->insert($query);
    }

    // Get waitlist position
    public function getPosition($user_id, $spot_id, $date)
    {
        $user_id = (int)$user_id;
        $spot_id = (int)$spot_id;
        $date    = $this->db->escape($date);

        $result = $this->db->select("
            SELECT * FROM waitlist 
            WHERE user_id = $user_id AND spot_id = $spot_id AND date = '$date' AND status = 'waiting'
        ");
        return $result[0] ?? null;
    }

    // Get waitlist for spot
    public function getSpotWaitlist($spot_id, $date)
    {
        $spot_id = (int)$spot_id;
        $date    = $this->db->escape($date);

        return $this->db->select("
            SELECT w.*, u.email, u.name, u.phone 
            FROM waitlist w 
            JOIN users u ON w.user_id = u.id 
            WHERE w.spot_id = $spot_id AND w.date = '$date' AND w.status = 'waiting'
            ORDER BY w.position
        ");
    }

    // Assign next in waitlist (without assigned_at)
    public function assignNext($spot_id, $date)
    {
        $spot_id = (int)$spot_id;
        $date    = $this->db->escape($date);
        $next    = $this->getSpotWaitlist($spot_id, $date);

        if (!empty($next)) {
            $user_id = (int)$next[0]['user_id'];
            return $this->db->execute("
                UPDATE waitlist SET status = 'assigned' 
                WHERE user_id = $user_id AND spot_id = $spot_id AND date = '$date'
            ");
        }
        return false;
    }

    // Leave waitlist
    public function leaveWaitlist($user_id, $spot_id, $date)
    {
        $user_id = (int)$user_id;
        $spot_id = (int)$spot_id;
        $date    = $this->db->escape($date);

        return $this->db->execute("
            UPDATE waitlist SET status = 'cancelled' 
            WHERE user_id = $user_id AND spot_id = $spot_id AND date = '$date'
        ");
    }
}