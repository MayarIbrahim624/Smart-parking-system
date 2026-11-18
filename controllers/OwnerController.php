<?php
// controllers/OwnerController.php
require_once 'core/DBController.php';
require_once 'includes/auth.php';

class OwnerController
{
    private $db;

    public function __construct()
    {
        $this->db = new DBController();
    }

    // ========== Dashboard ==========
    public function dashboard()
    {
        requireRole('owner');
        $ownerId = $_SESSION['user_id'];
        $totalSpots = $this->db->select("SELECT COUNT(*) AS cnt FROM parking_spots WHERE owner_id = $ownerId")[0]['cnt'] ?? 0;
        $availableSpots = $this->db->select("SELECT COUNT(*) AS cnt FROM parking_spots WHERE owner_id = $ownerId AND status = 'available'")[0]['cnt'] ?? 0;
        $revenueData = $this->db->select("SELECT COALESCE(SUM(p.amount), 0) AS total, COUNT(p.id) AS count FROM payments p JOIN reservations r ON p.reservation_id = r.id JOIN parking_spots ps ON r.spot_id = ps.id WHERE ps.owner_id = $ownerId AND p.status = 'completed' AND p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $revenue = $revenueData[0] ?? ['total' => 0, 'count' => 0];
        $lastPayout = $this->db->select("SELECT net_amount FROM payouts WHERE owner_id = $ownerId ORDER BY created_at DESC LIMIT 1");
        $pendingPayout = $lastPayout[0]['net_amount'] ?? 0;
        $activePromos = $this->db->select("SELECT COUNT(*) AS cnt FROM promo_codes WHERE owner_id = $ownerId AND is_active = 1")[0]['cnt'] ?? 0;
        $ownerName = $_SESSION['name'];
        require 'views/owner/dashboard.php';
    }

    // ========== Manage Spots ==========
    public function manageSpots()
    {
        requireRole('owner');
        $ownerId = $_SESSION['user_id'];
        $spots = $this->db->select("SELECT * FROM parking_spots WHERE owner_id = $ownerId ORDER BY created_at DESC");
        $ownerName = $_SESSION['name'];
        $msg = $_SESSION['msg'] ?? '';
        $err = $_SESSION['err'] ?? '';
        unset($_SESSION['msg'], $_SESSION['err']);
        require 'views/owner/manage-spots.php';
    }

    public function addSpot()
    {
        requireRole('owner');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $location = trim($_POST['location'] ?? '');
            $spotNumber = trim($_POST['spot_number'] ?? '');
            $hourlyRate = (float)($_POST['hourly_rate'] ?? 5);
            if ($location && $spotNumber) {
                $ownerId = $_SESSION['user_id'];
                $locEsc = $this->db->escape($location);
                $numEsc = $this->db->escape($spotNumber);
                $this->db->execute("INSERT INTO parking_spots (owner_id, location, spot_number, hourly_rate, status, created_at) VALUES ($ownerId, '$locEsc', '$numEsc', $hourlyRate, 'available', NOW())");
                $_SESSION['msg'] = "Spot '$spotNumber' added.";
            } else {
                $_SESSION['err'] = "Location and spot number are required.";
            }
        }
        header('Location: index.php?controller=owner&action=manageSpots');
        exit;
    }

    public function deleteSpot()
    {
        requireRole('owner');
        if (isset($_POST['spot_id'])) {
            $spotId = (int)$_POST['spot_id'];
            $ownerId = $_SESSION['user_id'];
            $spot = $this->db->select("SELECT id FROM parking_spots WHERE id = $spotId AND owner_id = $ownerId");
            if (!empty($spot)) {
                $this->db->execute("UPDATE parking_spots SET status = 'maintenance' WHERE id = $spotId");
                $_SESSION['msg'] = "Spot removed.";
            }
        }
        header('Location: index.php?controller=owner&action=manageSpots');
        exit;
    }

    // ========== Analytics ==========
    public function analytics()
    {
        requireRole('owner');
        $ownerId = $_SESSION['user_id'];
        $payout = $this->db->select("SELECT period_start, period_end, net_amount, paid_at FROM payouts WHERE owner_id = $ownerId ORDER BY created_at DESC LIMIT 1");
        $payoutData = $payout[0] ?? null;
        $promos = $this->db->select("SELECT id, code, discount_percent, valid_until, is_active FROM promo_codes WHERE owner_id = $ownerId ORDER BY created_at DESC");
        $ownerName = $_SESSION['name'];
        $msg = $_SESSION['msg'] ?? '';
        $err = $_SESSION['err'] ?? '';
        unset($_SESSION['msg'], $_SESSION['err']);
        require 'views/owner/analytics.php';
    }

    public function addPromo()
    {
        requireRole('owner');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = strtoupper(trim($_POST['code'] ?? ''));
            $discount = (int)($_POST['discount_percent'] ?? 0);
            $validUntil = $_POST['valid_until'] ?? date('Y-m-d', strtotime('+1 year'));
            if ($code && $discount >= 1 && $discount <= 100) {
                $ownerId = $_SESSION['user_id'];
                $codeEsc = $this->db->escape($code);
                $exists = $this->db->select("SELECT id FROM promo_codes WHERE code = '$codeEsc'");
                if (empty($exists)) {
                    $this->db->execute("INSERT INTO promo_codes (owner_id, code, discount_percent, valid_until, is_active) VALUES ($ownerId, '$codeEsc', $discount, '$validUntil', 1)");
                    $_SESSION['msg'] = "Promo code '$code' created.";
                } else {
                    $_SESSION['err'] = "Promo code '$code' already exists.";
                }
            } else {
                $_SESSION['err'] = "Invalid code or discount.";
            }
        }
        header('Location: index.php?controller=owner&action=analytics');
        exit;
    }

    public function requestPayout()
    {
        requireRole('owner');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ownerId = $_SESSION['user_id'];
            $revenue = $this->db->select("SELECT COALESCE(SUM(p.amount), 0) AS total FROM payments p JOIN reservations r ON p.reservation_id = r.id JOIN parking_spots ps ON r.spot_id = ps.id WHERE ps.owner_id = $ownerId AND p.status = 'completed' AND p.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $gross = $revenue[0]['total'] ?? 0;
            if ($gross > 0) {
                $net = $gross * 0.86;
                $start = date('Y-m-d', strtotime('-30 days'));
                $end = date('Y-m-d');
                $this->db->execute("INSERT INTO payouts (owner_id, period_start, period_end, gross_revenue, net_amount, created_at) VALUES ($ownerId, '$start', '$end', $gross, $net, NOW())");
                $_SESSION['msg'] = "Payout request submitted for $" . number_format($net, 2) . ".";
            } else {
                $_SESSION['err'] = "No revenue to pay out in the last 30 days.";
            }
        }
        header('Location: index.php?controller=owner&action=analytics');
        exit;
    }
}