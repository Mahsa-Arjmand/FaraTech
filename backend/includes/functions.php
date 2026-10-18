<?php
// General helper functions

// Clean input data
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// JSON response helper
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Check if user is admin
function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

// Redirect helper
function redirect($url) {
    header("Location: $url");
    exit;
}

// Pagination helper
function getPagination($page, $perPage, $total) {
    $totalPages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    return [
        'total' => $total,
        'perPage' => $perPage,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'offset' => $offset,
        'hasNext' => $page < $totalPages,
        'hasPrev' => $page > 1
    ];
}
