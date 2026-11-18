<?php
// models/QRModel.php – متوافق مع DBController (mysqli)
require_once __DIR__ . '/../core/DBController.php';

class QRModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // Check-in
    public function checkIn($user_id, $spot_id, $qr_code)
    {
        $user_id = (int)$user_id;
        $spot_id = (int)$spot_id;
        $qr_code = $this->db->escape($qr_code);

        $query = "INSERT INTO qr_logs (user_id, spot_id, qr_code, check_in_time, status) 
                  VALUES ($user_id, $spot_id, '$qr_code', NOW(), 'checked_in')";
        return $this->db->insert($query);
    }

    // Check-out
    public function checkOut($user_id, $spot_id)
    {
        $user_id = (int)$user_id;
        $spot_id = (int)$spot_id;

        $query = "UPDATE qr_logs 
                  SET check_out_time = NOW(), status = 'checked_out' 
                  WHERE user_id = $user_id AND spot_id = $spot_id AND status = 'checked_in'";
        return $this->db->execute($query);
    }

    // Get QR log
    public function getQRLog($id)
    {
        $id = (int)$id;
        $result = $this->db->select("SELECT * FROM qr_logs WHERE id = $id");
        return $result[0] ?? null;
    }

    // Get user check-in/check-out history
    public function getHistory($user_id)
    {
        $user_id = (int)$user_id;
        return $this->db->select("SELECT * FROM qr_logs WHERE user_id = $user_id ORDER BY check_in_time DESC");
    }

    // Validate QR code
    public function validateQRCode($qr_code)
    {
        $qr_code = $this->db->escape($qr_code);
        $result = $this->db->select("SELECT * FROM qr_logs WHERE qr_code = '$qr_code' LIMIT 1");
        return $result[0] ?? null;
    }
}