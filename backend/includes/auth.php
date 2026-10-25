<?php
// Secure session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 when using HTTPS
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Handle logout request
if (isset($_GET['logout'])) {
    logout();
}

// Rate limiting for login attempts
function checkLoginRateLimit($ip) {
    $attemptsFile = __DIR__ . '/../logs/login_attempts.log';
    $maxAttempts = 5;
    $lockoutTime = 900; // 15 minutes

    if (!file_exists($attemptsFile)) {
        return true;
    }

    $attempts = json_decode(file_get_contents($attemptsFile), true) ?: [];
    $now = time();

    // Clean old entries
    $attempts = array_filter($attempts, function($entry) use ($now, $lockoutTime) {
        return ($now - $entry['time']) < $lockoutTime;
    });

    $ipAttempts = array_filter($attempts, function($entry) use ($ip) {
        return $entry['ip'] === $ip;
    });

    if (count($ipAttempts) >= $maxAttempts) {
        return false;
    }

    return true;
}

function recordLoginAttempt($ip, $success) {
    $attemptsFile = __DIR__ . '/../logs/login_attempts.log';
    $attempts = json_decode(file_get_contents($attemptsFile), true) ?: [];
    $attempts[] = ['ip' => $ip, 'time' => time(), 'success' => $success];
    file_put_contents($attemptsFile, json_encode($attempts));
}

// Login function
function login($username, $password) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // Check rate limiting
    if (!checkLoginRateLimit($ip)) {
        error_log("Login blocked for IP: " . $ip . " due to rate limit");
        return false;
    }

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['ip'] = $ip;

        recordLoginAttempt($ip, true);
        return true;
    }

    recordLoginAttempt($ip, false);
    return false;
}

// Logout function
function logout() {
    $_SESSION = [];
    session_destroy();
    redirect('admin/login.php');
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('admin/login.php');
    }

    // Check session IP to prevent session hijacking
    if (isset($_SESSION['ip']) && $_SESSION['ip'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
        logout();
    }

    // Session timeout (30 minutes)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 1800) {
        logout();
    }
}

// Require admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        jsonResponse(false, 'Access denied. Admin only.');
    }
}
