<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('users.php');
}

$db = getDBConnection();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$username = cleanInput($_POST['username']);
$email = cleanInput($_POST['email']);
$password = $_POST['password'] ?? '';
$role = cleanInput($_POST['role']);

if ($id > 0) {
    // Update
    // Check if username or email already exists (excluding current user)
    $checkStmt = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $checkStmt->execute([$username, $email, $id]);
    if ($checkStmt->fetch()) {
        $_SESSION['flash_error'] = 'Username or email already exists';
        redirect('users.php');
    }

    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
        $result = $stmt->execute([$username, $email, $passwordHash, $role, $id]);
    } else {
        $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        $result = $stmt->execute([$username, $email, $role, $id]);
    }

    if ($result) {
        $_SESSION['flash_success'] = 'User updated successfully';
    } else {
        $_SESSION['flash_error'] = 'Failed to update user';
    }
} else {
    // Create
    // Check if username or email already exists
    $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $checkStmt->execute([$username, $email]);
    if ($checkStmt->fetch()) {
        $_SESSION['flash_error'] = 'Username or email already exists';
        redirect('users.php');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$username, $email, $passwordHash, $role]);

    if ($result) {
        $_SESSION['flash_success'] = 'User created successfully';
    } else {
        $_SESSION['flash_error'] = 'Failed to create user';
    }
}

redirect('users.php');
