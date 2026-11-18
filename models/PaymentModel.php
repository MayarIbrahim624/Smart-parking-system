<?php
// models/PaymentModel.php – متوافق مع DBController (mysqli)
require_once __DIR__ . '/../core/DBController.php';

class PaymentModel
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // Process payment
    public function processPayment($user_id, $reservation_id, $amount, $method = 'card')
    {
        $user_id        = (int)$user_id;
        $reservation_id = (int)$reservation_id;
        $amount         = (float)$amount;
        $method         = $this->db->escape($method);
        $transaction_id = 'TXN_' . time() . '_' . $user_id;

        $query = "INSERT INTO payments (user_id, reservation_id, amount, method, status, transaction_id, created_at) 
                  VALUES ($user_id, $reservation_id, $amount, '$method', 'completed', '$transaction_id', NOW())";
        return $this->db->insert($query);
    }

    // Get single payment
    public function getPayment($id)
    {
        $id = (int)$id;
        $result = $this->db->select("SELECT * FROM payments WHERE id = $id");
        return $result[0] ?? null;
    }

    // Get payment history for a user
    public function getPaymentHistory($user_id)
    {
        $user_id = (int)$user_id;
        return $this->db->select("
            SELECT p.*, r.spot_id, ps.spot_number 
            FROM payments p 
            JOIN reservations r ON p.reservation_id = r.id 
            JOIN parking_spots ps ON r.spot_id = ps.id 
            WHERE p.user_id = $user_id 
            ORDER BY p.created_at DESC
        ");
    }

    // Request refund
    public function requestRefund($payment_id, $reason = null)
    {
        $payment_id = (int)$payment_id;
        $reason     = $reason ? "'" . $this->db->escape($reason) . "'" : "NULL";
        return $this->db->execute("UPDATE payments SET status = 'refund_requested', refund_reason = $reason, refund_requested_at = NOW() WHERE id = $payment_id");
    }

    // Process refund (admin manually)
    public function processRefund($payment_id)
    {
        $payment_id = (int)$payment_id;
        return $this->db->execute("UPDATE payments SET status = 'refunded', refund_processed_at = NOW() WHERE id = $payment_id");
    }

    // Calculate revenue (daily/monthly/yearly)
    public function getRevenue($period = 'daily')
    {
        $date_format = match($period) {
            'weekly' => '%Y-W%v',
            'monthly' => '%Y-%m',
            'yearly' => '%Y',
            default => '%Y-%m-%d'
        };

        return $this->db->select("
            SELECT 
                DATE_FORMAT(created_at, '$date_format') as period,
                COUNT(*) as transactions,
                SUM(amount) as total,
                AVG(amount) as average
            FROM payments 
            WHERE status = 'completed'
            GROUP BY period
            ORDER BY period DESC
        ");
    }

    // Get payment by reservation
    public function getPaymentByReservation($reservation_id)
    {
        $reservation_id = (int)$reservation_id;
        $result = $this->db->select("SELECT * FROM payments WHERE reservation_id = $reservation_id ORDER BY created_at DESC LIMIT 1");
        return $result[0] ?? null;
    }
}