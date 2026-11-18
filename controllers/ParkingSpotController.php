<?php
// controllers/PaymentController.php – نسخة PHP مباشرة (احتياطية)
require_once 'core/DBController.php';
require_once 'includes/auth.php';

class PaymentController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // عرض سجل المدفوعات للسائق الحالي
    public function history()
    {
        requireLogin();
        $userId = $_SESSION['user_id'];

        $payments = $this->db->select("
            SELECT p.amount, p.status, p.created_at, ps.spot_number
            FROM payments p
            LEFT JOIN reservations r ON p.reservation_id = r.id
            LEFT JOIN parking_spots ps ON r.spot_id = ps.id
            WHERE p.user_id = $userId
            ORDER BY p.created_at DESC
        ");

        $paymentMethods = $this->db->select("
            SELECT card_number, card_holder, exp_month, exp_year
            FROM payment_methods
            WHERE user_id = $userId
        ");

        $driverName = $_SESSION['name'];
        $msg = $_SESSION['msg'] ?? '';
        $err = $_SESSION['err'] ?? '';
        unset($_SESSION['msg'], $_SESSION['err']);

        // نستخدم نفس صفحة driver/payment.php
        require 'views/driver/payment.php';
    }

    // إضافة وسيلة دفع جديدة
    public function addMethod()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cardHolder = trim($_POST['card_holder'] ?? '');
            $cardNumber = str_replace(' ', '', $_POST['card_number'] ?? '');
            $expMonth   = (int)($_POST['exp_month'] ?? 0);
            $expYear    = (int)($_POST['exp_year'] ?? 0);

            if ($cardHolder && strlen($cardNumber) >= 13 && $expMonth >= 1 && $expMonth <= 12 && $expYear >= 2026) {
                $userId = $_SESSION['user_id'];
                $this->db->execute("INSERT INTO payment_methods (user_id, card_number, card_holder, exp_month, exp_year) VALUES ($userId, '" . $this->db->escape($cardNumber) . "', '" . $this->db->escape($cardHolder) . "', $expMonth, $expYear)");
                $_SESSION['msg'] = "Payment method added.";
            } else {
                $_SESSION['err'] = "Invalid card details.";
            }
        }
        header('Location: index.php?controller=payment&action=history');
        exit;
    }

    // طلب استرداد
    public function refund()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentId = (int)($_POST['payment_id'] ?? 0);
            $reason    = trim($_POST['reason'] ?? '');

            if ($paymentId && $reason) {
                $userId = $_SESSION['user_id'];
                // التحقق من أن الدفعة تخص المستخدم
                $payment = $this->db->select("SELECT id FROM payments WHERE id = $paymentId AND user_id = $userId AND status = 'completed'");
                if (!empty($payment)) {
                    $reasonEsc = $this->db->escape($reason);
                    $this->db->execute("UPDATE payments SET status = 'refund_requested', refund_reason = '$reasonEsc', refund_requested_at = NOW() WHERE id = $paymentId");
                    $_SESSION['msg'] = "Refund request submitted for payment #$paymentId.";
                } else {
                    $_SESSION['err'] = "Invalid payment or already refunded.";
                }
            } else {
                $_SESSION['err'] = "Payment ID and reason are required.";
            }
        }
        header('Location: index.php?controller=payment&action=history');
        exit;
    }

    // معالجة دفع (تُستخدم بعد إنشاء حجز)
    public function process()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reservationId = (int)($_POST['reservation_id'] ?? 0);
            $amount        = (float)($_POST['amount'] ?? 0);
            $method        = $_POST['method'] ?? 'card';

            if ($reservationId && $amount > 0) {
                $userId = $_SESSION['user_id'];
                $txnId  = 'TXN_' . time() . '_' . $userId;
                $this->db->execute("INSERT INTO payments (user_id, reservation_id, amount, method, status, transaction_id, created_at) VALUES ($userId, $reservationId, $amount, '$method', 'completed', '$txnId', NOW())");
                $_SESSION['msg'] = "Payment of \$" . number_format($amount,2) . " processed.";
            } else {
                $_SESSION['err'] = "Invalid payment details.";
            }
        }
        header('Location: index.php?controller=payment&action=history');
        exit;
    }
}