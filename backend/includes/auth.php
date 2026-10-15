<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Handle logout request
if (isset($_GET['logout'])) {
    logout();
}

// Login function
function login($username, $password) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

// Logout function
function logout() {
    session_destroy();
    redirect('admin/login.php');
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('admin/login.php');
    }
}

// Require admin
function requireAdmin() {
    if (!isAdmin()) {
        jsonResponse(false, 'Access denied. Admin only.');
    }
}
