<?php
// models/AppealModel.php – متوافق مع DBController (mysqli)
require_once __DIR__ . '/../core/DBController.php';

class AppealModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // Submit appeal
    public function submitAppeal($fine_id, $user_id, $description, $evidence_file = null)
    {
        $fine_id    = (int)$fine_id;
        $user_id    = (int)$user_id;
        $desc       = $this->db->escape($description);
        $evidence   = $evidence_file ? "'" . $this->db->escape($evidence_file) . "'" : "NULL";

        $query = "INSERT INTO appeals (fine_id, user_id, description, evidence_file, status, created_at) 
                  VALUES ($fine_id, $user_id, '$desc', $evidence, 'pending', NOW())";
        return $this->db->insert($query);
    }

    // Get appeal
    public function getAppeal($id)
    {
        $id = (int)$id;
        $result = $this->db->select("
            SELECT a.*, f.amount, u.email, u.name 
            FROM appeals a 
            JOIN fines f ON a.fine_id = f.id 
            JOIN users u ON a.user_id = u.id 
            WHERE a.id = $id
        ");
        return $result[0] ?? null;
    }

    // Get user appeals
    public function getUserAppeals($user_id)
    {
        $user_id = (int)$user_id;
        return $this->db->select("
            SELECT a.*, f.amount, f.reason 
            FROM appeals a 
            JOIN fines f ON a.fine_id = f.id 
            WHERE a.user_id = $user_id 
            ORDER BY a.created_at DESC
        ");
    }

    // Approve appeal
    public function approveAppeal($id)
    {
        $id = (int)$id;
        return $this->db->execute("UPDATE appeals SET status = 'approved', updated_at = NOW() WHERE id = $id");
    }

    // Reject appeal
    public function rejectAppeal($id, $reason = null)
    {
        $id = (int)$id;
        $reason = $this->db->escape($reason);
        return $this->db->execute("UPDATE appeals SET status = 'rejected', rejection_reason = '$reason', updated_at = NOW() WHERE id = $id");
    }

    // Get appeal statistics
    public function getAppealStats()
    {
        return $this->db->select("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM appeals
        ");
    }
}