<?php
// controllers/ExtensionController.php – Admin manages extension requests
require_once 'core/DBController.php';
require_once 'includes/auth.php';

class ExtensionController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // List all pending extension requests (admin)
    public function listExtensions()
    {
        requireRole('admin');

        $extensions = $this->db->select("
            SELECT e.*,
                   u.name AS driver_name,
                   ps.spot_number,
                   r.user_id
            FROM extensions e
            JOIN reservations r ON e.reservation_id = r.id
            JOIN users u ON r.user_id = u.id
            JOIN parking_spots ps ON r.spot_id = ps.id
            ORDER BY e.created_at DESC
        ");

        $adminName = $_SESSION['name'];
        $msg = $_SESSION['msg'] ?? '';
        $err = $_SESSION['err'] ?? '';
        unset($_SESSION['msg'], $_SESSION['err']);

        require 'views/admin/extensions.php';
    }

    // Approve an extension request
    public function approve()
    {
        requireRole('admin');
        if (isset($_POST['extension_id'])) {
            $id = (int)$_POST['extension_id'];

            // Fetch the extension details
            $ext = $this->db->select("SELECT * FROM extensions WHERE id = $id");
            if (!empty($ext)) {
                $ext = $ext[0];
                $reservationId = $ext['reservation_id'];
                $additionalHours = $ext['additional_hours'];

                // Update reservation end_time
                $this->db->execute("
                    UPDATE reservations
                    SET end_time = DATE_ADD(end_time, INTERVAL $additionalHours HOUR),
                        updated_at = NOW()
                    WHERE id = $reservationId
                ");

                // Mark extension as approved
                $this->db->execute("UPDATE extensions SET status = 'approved', approved_at = NOW() WHERE id = $id");

                $_SESSION['msg'] = "Extension #$id approved. Reservation extended by $additionalHours hours.";
            }
        }
        header('Location: index.php?controller=extension&action=listExtensions');
        exit;
    }

    // Reject an extension request
    public function reject()
    {
        requireRole('admin');
        if (isset($_POST['extension_id'])) {
            $id = (int)$_POST['extension_id'];
            $this->db->execute("UPDATE extensions SET status = 'rejected', rejected_at = NOW() WHERE id = $id");
            $_SESSION['msg'] = "Extension #$id rejected.";
        }
        header('Location: index.php?controller=extension&action=listExtensions');
        exit;
    }
}