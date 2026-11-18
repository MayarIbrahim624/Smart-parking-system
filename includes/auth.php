<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?controller=auth&action=login&error=' . urlencode('Please login first'));
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header('Location: index.php?controller=auth&action=login&error=' . urlencode('Access denied'));
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function currentUserName() {
    return $_SESSION['name'] ?? 'User';
}

function currentUserRole() {
    return $_SESSION['role'] ?? null;
}