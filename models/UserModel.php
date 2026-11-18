<?php
// models/UserModel.php
require_once __DIR__ . '/../core/DBController.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = new DBController();
    }

    // Register new user
    public function register($email, $password, $name, $phone, $role = 'driver') {
        $email = $this->db->escape($email);
        // فحص ما إذا كان البريد الإلكتروني موجوداً مسبقاً
        $check = $this->db->select("SELECT id FROM users WHERE email = '$email'");
        if (!empty($check)) {
            return false;
        }
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $name = $this->db->escape($name);
        $phone = $this->db->escape($phone);
        $role = $this->db->escape($role);
        $query = "INSERT INTO users (email, password, name, phone, role, status, created_at, updated_at)
                  VALUES ('$email', '$hashedPassword', '$name', '$phone', '$role', 'active', NOW(), NOW())";
        return $this->db->insert($query);
    }

    // User login
    public function login($email, $password) {
        $email = $this->db->escape($email);
        $result = $this->db->select("SELECT * FROM users WHERE email = '$email' AND status = 'active'");
        if (count($result) === 1) {
            $user = $result[0];
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];
                $this->logActivity($user['id'], 'login', 'User logged in');
                return $user;
            }
        }
        return false;
    }

    // Get user profile
    public function getUser($id) {
        $id = (int)$id;
        $result = $this->db->select("SELECT * FROM users WHERE id = $id");
        return $result[0] ?? null;
    }

    // Update user profile
    public function updateProfile($id, $name, $phone, $vehicle_plate = null) {
        $id = (int)$id;
        $name = $this->db->escape($name);
        $phone = $this->db->escape($phone);
        $plate = $this->db->escape($vehicle_plate);
        $query = "UPDATE users SET name='$name', phone='$phone', vehicle_plate='$plate', updated_at=NOW() WHERE id=$id";
        return $this->db->execute($query);
    }

    // Verify email
    public function verifyEmail($id) {
        $id = (int)$id;
        return $this->db->execute("UPDATE users SET email_verified=1, updated_at=NOW() WHERE id=$id");
    }

    // Change password
    public function changePassword($id, $oldPassword, $newPassword) {
        $user = $this->getUser($id);
        if (!$user || !password_verify($oldPassword, $user['password'])) {
            return false;
        }
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $id = (int)$id;
        return $this->db->execute("UPDATE users SET password='$hashedPassword', updated_at=NOW() WHERE id=$id");
    }

    // Reset password
    public function resetPassword($email, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $email = $this->db->escape($email);
        return $this->db->execute("UPDATE users SET password='$hashedPassword', updated_at=NOW() WHERE email='$email'");
    }

    // Block user
    public function blockUser($id, $reason = null) {
        $id = (int)$id;
        $reason = $this->db->escape($reason);
        return $this->db->execute("UPDATE users SET status='blocked', block_reason='$reason', updated_at=NOW() WHERE id=$id");
    }

    // Unblock user
    public function unblockUser($id) {
        $id = (int)$id;
        return $this->db->execute("UPDATE users SET status='active', block_reason=NULL, updated_at=NOW() WHERE id=$id");
    }

    // Log activity
    public function logActivity($user_id, $action, $description) {
        $user_id = (int)$user_id;
        $action = $this->db->escape($action);
        $description = $this->db->escape($description);
        $ip = $this->db->escape($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $query = "INSERT INTO activity_logs (user_id, action, description, ip_address, created_at)
                  VALUES ($user_id, '$action', '$description', '$ip', NOW())";
        return $this->db->execute($query);
    }

    // Get user by email
    public function getUserByEmail($email) {
        $email = $this->db->escape($email);
        $result = $this->db->select("SELECT * FROM users WHERE email = '$email'");
        return $result[0] ?? null;
    }

    // Get user statistics
    public function getUserStats($id) {
        $id = (int)$id;
        $stats = [];
        $totalBookings = $this->db->select("SELECT COUNT(*) as count FROM reservations WHERE user_id = $id");
        $stats['total_bookings'] = $totalBookings[0]['count'] ?? 0;
        $totalSpent = $this->db->select("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE user_id = $id AND status = 'completed'");
        $stats['total_spent'] = $totalSpent[0]['total'] ?? 0;
        $activeBookings = $this->db->select("SELECT COUNT(*) as count FROM reservations WHERE user_id = $id AND status = 'active'");
        $stats['active_bookings'] = $activeBookings[0]['count'] ?? 0;
        return $stats;
    }
}