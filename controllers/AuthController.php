<?php
// controllers/AuthController.php
require_once 'models/UserModel.php';
require_once 'core/DBController.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ========== LOGIN ==========
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                header('Location: index.php?controller=auth&action=login&error=' . urlencode('Please fill all fields'));
                exit;
            }

            $user = $this->userModel->login($email, $password);
            if ($user) {
                switch ($user['role']) {
                    case 'admin':
                        header('Location: index.php?controller=admin&action=dashboard');
                        exit;
                    case 'owner':
                        header('Location: index.php?controller=owner&action=dashboard');
                        exit;
                    default:
                        header('Location: index.php?controller=driver&action=dashboard');
                        exit;
                }
            } else {
                header('Location: index.php?controller=auth&action=login&error=' . urlencode('Invalid email or password'));
                exit;
            }
        }
        // GET request
        require 'views/authentication/login.php';
    }

    // ========== REGISTER ==========
    public function register()
    {
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone    = trim($_POST['phone'] ?? '');
            $role     = $_POST['role'] ?? 'driver';

            if (empty($name) || empty($email) || empty($password)) {
                $error = "Please fill all required fields.";
            } elseif ($this->userModel->getUserByEmail($email)) {
                $error = "Email already registered.";
            } else {
                $result = $this->userModel->register($email, $password, $name, $phone, $role);
                if ($result) {
                    // Auto‑login after registration
                    $user = $this->userModel->login($email, $password);
                    if ($user) {
                        header('Location: index.php?controller=driver&action=dashboard');
                        exit;
                    }
                } else {
                    $error = "Registration failed.";
                }
            }
        }
        $_SESSION['register_error'] = $error;
        require 'views/authentication/register.php';
    }

    // ========== LOGOUT ==========
    public function logout()
    {
        session_start();
        session_destroy();
        header('Location: index.php');
        exit;
    }

    // ========== CHANGE PASSWORD ==========
    public function changePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        $error = '';
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldPassword = $_POST['oldPassword'] ?? '';
            $newPassword = $_POST['newPassword'] ?? '';
            if (empty($oldPassword) || empty($newPassword)) {
                $error = "Please fill all fields.";
            } else {
                $result = $this->userModel->changePassword($_SESSION['user_id'], $oldPassword, $newPassword);
                if ($result) {
                    $success = "Password changed successfully.";
                } else {
                    $error = "Old password is incorrect.";
                }
            }
        }
        require 'views/authentication/change-password.php';   // أنشئيها إن أردتِ
    }

    // ========== RESET PASSWORD ==========
    public function resetPassword()
    {
        $error = '';
        $msg   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $error = "Please enter your email address.";
            } else {
                $user = $this->userModel->getUserByEmail($email);
                if ($user) {
                    // إنشاء token وتخزينه
                    $token   = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    $db = new DBController();
                    $emailEsc = $db->escape($email);
                    $tokenEsc = $db->escape($token);
                    $db->execute("INSERT INTO password_resets (email, token, expires_at) VALUES ('$emailEsc', '$tokenEsc', '$expires')");
                    $msg = "A password reset link has been sent to your email.";
                } else {
                    $error = "Email not found.";
                }
            }
        }

        $_SESSION['reset_error']   = $error;
        $_SESSION['reset_success'] = $msg;
        require 'views/authentication/reset-password.php';
    }
}