<?php
// controllers/DriverController.php
require_once 'core/DBController.php';
require_once 'includes/auth.php';

class DriverController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // ========== Dashboard ==========
    public function dashboard()
    {
        requireLogin();
        $userId = $_SESSION['user_id'];

        $stats = [
            'active_bookings'  => $this->db->select("SELECT COUNT(*) AS cnt FROM reservations WHERE user_id = $userId AND status = 'active'")[0]['cnt'] ?? 0,
            'loyalty_points'   => $this->db->select("SELECT points FROM loyalty WHERE user_id = $userId")[0]['points'] ?? 0,
            'vehicles_count'   => $this->db->select("SELECT COUNT(*) AS cnt FROM vehicles WHERE user_id = $userId")[0]['cnt'] ?? 0,
            'favorites_count'  => $this->db->select("SELECT COUNT(*) AS cnt FROM favorites WHERE user_id = $userId")[0]['cnt'] ?? 0,
            'waitlist_position'=> $this->db->select("SELECT MIN(position) AS pos FROM waitlist WHERE user_id = $userId AND status = 'waiting'")[0]['pos'] ?? 0,
        ];

        $driverName = $_SESSION['name'];
        require 'views/driver/dashboard.php';
    }

    // ========== Search Spots ==========
    public function search()
    {
        requireLogin();
        $spots = [];
        $searched = false;
        $location  = trim($_GET['location'] ?? '');
        $startTime = $_GET['start_time'] ?? '';
        $endTime   = $_GET['end_time'] ?? '';

        if ($startTime && $endTime) {
            $searched = true;
            $start = $this->db->escape($startTime);
            $end   = $this->db->escape($endTime);
            $query = "SELECT * FROM parking_spots WHERE status = 'available'";
            if ($location) {
                $loc = $this->db->escape($location);
                $query .= " AND location LIKE '%$loc%'";
            }
            $query .= " AND id NOT IN (SELECT spot_id FROM reservations WHERE status IN ('active','confirmed') AND ((start_time < '$end' AND end_time > '$start')))";
            $spots = $this->db->select($query);
        }

        $driverName = $_SESSION['name'];
        require 'views/driver/search.php';
    }

    // ========== Book Spot ==========
    public function book()
    {
        requireLogin();
        $spotId = $_GET['spot_id'] ?? null;
        $spot = null;
        if ($spotId) {
            $spot = $this->db->select("SELECT * FROM parking_spots WHERE id = " . (int)$spotId)[0] ?? null;
        }
        $driverName = $_SESSION['name'];
        require 'views/driver/book.php';
    }

    // ========== Create Reservation ==========
public function createReservation()
{
    requireLogin();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $spotId    = (int)($_POST['spot_id'] ?? 0);
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime   = trim($_POST['end_time'] ?? '');

        if ($spotId && $startTime && $endTime) {
            $spot = $this->db->select("SELECT hourly_rate FROM parking_spots WHERE id = $spotId")[0] ?? null;
            if ($spot) {
                $start  = strtotime($startTime);
                $end    = strtotime($endTime);
                $hours  = max(1, ceil(($end - $start) / 3600));
                $amount = $hours * $spot['hourly_rate'];
                $userId = $_SESSION['user_id'];
                $startEsc = $this->db->escape($startTime);
                $endEsc   = $this->db->escape($endTime);

                // إنشاء الحجز
                $reservationId = $this->db->insert(
                    "INSERT INTO reservations (user_id, spot_id, start_time, end_time, amount, status, created_at)
                     VALUES ($userId, $spotId, '$startEsc', '$endEsc', $amount, 'active', NOW())"
                );

                if ($reservationId) {
                    // إنشاء دفعة تلقائية لهذا الحجز
                    $txnId = 'TXN_' . time() . '_' . $userId;
                    $this->db->execute(
                        "INSERT INTO payments (user_id, reservation_id, amount, method, status, transaction_id, created_at)
                         VALUES ($userId, $reservationId, $amount, 'card', 'completed', '$txnId', NOW())"
                    );

                    $_SESSION['msg'] = "Booking confirmed and paid! Amount: $" . number_format($amount, 2);
                    header('Location: index.php?controller=driver&action=payment');
                    exit;
                }
            }
        }
        $_SESSION['err'] = "Please fill all fields correctly.";
    }
    header('Location: index.php?controller=driver&action=book&spot_id=' . ($_POST['spot_id'] ?? ''));
    exit;
}

    // ========== Payment History & Methods ==========
public function payment()
{
    requireLogin();
    $userId = $_SESSION['user_id'];

    // سجل المدفوعات
    $payments = $this->db->select("
        SELECT p.amount, p.status, p.created_at, ps.spot_number
        FROM payments p
        LEFT JOIN reservations r ON p.reservation_id = r.id
        LEFT JOIN parking_spots ps ON r.spot_id = ps.id
        WHERE p.user_id = $userId
        ORDER BY p.created_at DESC
    ");

    // حجوزات بدون دفعة مكتملة
    $reservations = $this->db->select("
        SELECT r.id, r.start_time, r.end_time, r.amount, ps.spot_number
        FROM reservations r
        JOIN parking_spots ps ON r.spot_id = ps.id
        WHERE r.user_id = $userId AND r.status IN ('active','confirmed')
        AND r.id NOT IN (SELECT reservation_id FROM payments WHERE status = 'completed')
    ");

    $driverName = $_SESSION['name'];
    $msg = $_SESSION['msg'] ?? '';
    $err = $_SESSION['err'] ?? '';
    unset($_SESSION['msg'], $_SESSION['err']);
    require 'views/driver/payment.php';
}

    public function addPaymentMethod()
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
        header('Location: index.php?controller=driver&action=payment');
        exit;
    }

    public function processPayment()
{
    requireLogin();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reservationId = (int)($_POST['reservation_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $userId = $_SESSION['user_id'];

        if ($reservationId && $amount > 0) {
            // التحقق من أن الحجز للمستخدم وغير مدفوع
            $res = $this->db->select("
                SELECT id FROM reservations
                WHERE id = $reservationId AND user_id = $userId
                AND id NOT IN (SELECT reservation_id FROM payments WHERE status = 'completed')
            ");
            if (!empty($res)) {
                $txnId = 'TXN_' . time() . '_' . $userId;
                $this->db->execute("
                    INSERT INTO payments (user_id, reservation_id, amount, method, status, transaction_id, created_at)
                    VALUES ($userId, $reservationId, $amount, 'card', 'completed', '$txnId', NOW())
                ");
                $_SESSION['msg'] = "Payment of \$" . number_format($amount, 2) . " processed successfully.";
            } else {
                $_SESSION['err'] = "Invalid or already paid reservation.";
            }
        }
    }
    header('Location: index.php?controller=driver&action=payment');
    exit;
}
    // ========== History & Favorites ==========
    public function history()
    {
        requireLogin();
        $userId = $_SESSION['user_id'];
        $reservations = $this->db->select("SELECT r.start_time, r.amount, r.status, ps.spot_number FROM reservations r JOIN parking_spots ps ON r.spot_id = ps.id WHERE r.user_id = $userId ORDER BY r.start_time DESC LIMIT 20");
        $favorites = $this->db->select("SELECT ps.spot_number, ps.location FROM favorites f JOIN parking_spots ps ON f.spot_id = ps.id WHERE f.user_id = $userId");
        $driverName = $_SESSION['name'];
        $msg = $_SESSION['msg'] ?? '';
        $err = $_SESSION['err'] ?? '';
        unset($_SESSION['msg'], $_SESSION['err']);
        require 'views/driver/history.php';
    }

    public function addFavorite()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $spotNumber = trim($_POST['spot_number'] ?? '');
            if ($spotNumber) {
                $userId = $_SESSION['user_id'];
                $spotNumberEsc = $this->db->escape($spotNumber);
                $spot = $this->db->select("SELECT id FROM parking_spots WHERE spot_number = '$spotNumberEsc'");
                if (!empty($spot)) {
                    $spotId = $spot[0]['id'];
                    $exists = $this->db->select("SELECT id FROM favorites WHERE user_id = $userId AND spot_id = $spotId");
                    if (empty($exists)) {
                        $this->db->execute("INSERT INTO favorites (user_id, spot_id) VALUES ($userId, $spotId)");
                        $_SESSION['msg'] = "Spot added to favorites.";
                    } else {
                        $_SESSION['err'] = "Spot already in favorites.";
                    }
                } else {
                    $_SESSION['err'] = "Spot not found.";
                }
            } else {
                $_SESSION['err'] = "Please enter a spot number.";
            }
        }
        header('Location: index.php?controller=driver&action=history');
        exit;
    }

    // ========== Extension ==========
    public function extension()
    {
        requireLogin();
        $userId = $_SESSION['user_id'];
        $reservation = $this->db->select("SELECT r.id, r.end_time, ps.hourly_rate FROM reservations r JOIN parking_spots ps ON r.spot_id = ps.id WHERE r.user_id = $userId AND r.status = 'active' ORDER BY r.end_time DESC LIMIT 1");
        $activeReservation = $reservation[0] ?? null;
        $driverName = $_SESSION['name'];
        require 'views/driver/extension.php';
    }

    public function submitExtension()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reservationId = (int)($_POST['reservation_id'] ?? 0);
            $hours = (int)($_POST['hours'] ?? 2);
            if ($reservationId > 0 && $hours > 0) {
                $userId = $_SESSION['user_id'];
                $res = $this->db->select("SELECT r.id, ps.hourly_rate FROM reservations r JOIN parking_spots ps ON r.spot_id = ps.id WHERE r.id = $reservationId AND r.user_id = $userId AND r.status = 'active'");
                if (!empty($res)) {
                    $additionalCost = $hours * $res[0]['hourly_rate'];
                    $this->db->execute("INSERT INTO extensions (reservation_id, additional_hours, additional_cost, status, created_at) VALUES ($reservationId, $hours, $additionalCost, 'pending', NOW())");
                    $_SESSION['msg'] = "Extension request submitted for $hours hour(s). Pending approval.";
                    header('Location: index.php?controller=driver&action=extension');
                    exit;
                }
            }
            $_SESSION['err'] = "Invalid extension request.";
        }
        header('Location: index.php?controller=driver&action=extension');
        exit;
    }

    // ========== Appeal ==========
    public function appeal()
    {
        requireLogin();
        $userId = $_SESSION['user_id'];
        $fines = $this->db->select("SELECT f.* FROM fines f WHERE f.user_id = $userId ORDER BY f.created_at DESC");
        $driverName = $_SESSION['name'];
        $msg = $_SESSION['msg'] ?? '';
        $err = $_SESSION['err'] ?? '';
        unset($_SESSION['msg'], $_SESSION['err']);
        require 'views/driver/appeal.php';
    }

    public function submitAppeal()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fineId = (int)($_POST['fine_id'] ?? 0);
            $description = trim($_POST['description'] ?? '');
            if ($fineId > 0 && $description) {
                $userId = $_SESSION['user_id'];
                $fine = $this->db->select("SELECT id FROM fines WHERE id = $fineId AND user_id = $userId");
                if (!empty($fine)) {
                    $descEsc = $this->db->escape($description);
                    $this->db->execute("INSERT INTO appeals (fine_id, user_id, description, status, created_at) VALUES ($fineId, $userId, '$descEsc', 'pending', NOW())");
                    $_SESSION['msg'] = 'Appeal submitted successfully!';
                } else {
                    $_SESSION['err'] = 'Invalid fine selected.';
                }
            } else {
                $_SESSION['err'] = 'Please fill all fields.';
            }
        }
        header('Location: index.php?controller=driver&action=appeal');
        exit;
    }

    // ========== Loyalty ==========
    public function loyalty()
    {
        requireLogin();
        $userId = $_SESSION['user_id'];
        $loyalty = $this->db->select("SELECT points, tier, discount_balance FROM loyalty WHERE user_id = $userId")[0] ?? ['points' => 0, 'tier' => 'bronze', 'discount_balance' => 0.00];
        $transactions = $this->db->select("SELECT points, description, created_at FROM loyalty_transactions WHERE user_id = $userId ORDER BY created_at DESC LIMIT 20");
        $driverName = $_SESSION['name'];
        $msg = $_SESSION['msg'] ?? '';
        $err = $_SESSION['err'] ?? '';
        unset($_SESSION['msg'], $_SESSION['err']);
        require 'views/driver/loyalty.php';
    }

    public function redeemPoints()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pointsToRedeem = (int)($_POST['points'] ?? 500);
            $userId = $_SESSION['user_id'];
            $loyalty = $this->db->select("SELECT points, discount_balance FROM loyalty WHERE user_id = $userId");
            if (!empty($loyalty)) {
                $currentPoints = (int)$loyalty[0]['points'];
                if ($currentPoints >= $pointsToRedeem) {
                    $discount = round($pointsToRedeem / 100, 2);
                    if ($discount > 0) {
                        $this->db->execute("UPDATE loyalty SET points = points - $pointsToRedeem, discount_balance = discount_balance + $discount WHERE user_id = $userId");
                        $desc = "Redeemed $pointsToRedeem points for $$discount discount";
                        $this->db->execute("INSERT INTO loyalty_transactions (user_id, points, description) VALUES ($userId, -$pointsToRedeem, '$desc')");
                        $_SESSION['msg'] = "Redeemed $pointsToRedeem points! $$discount added to your discount balance.";
                    } else {
                        $_SESSION['err'] = "Invalid points amount.";
                    }
                } else {
                    $_SESSION['err'] = "Insufficient points.";
                }
            } else {
                $_SESSION['err'] = "No loyalty account found.";
            }
        }
        header('Location: index.php?controller=driver&action=loyalty');
        exit;
    }
}