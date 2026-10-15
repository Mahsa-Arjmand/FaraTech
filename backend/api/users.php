<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        requireAdmin();
        $db = getDBConnection();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(username LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Get pagination
        $pagination = getPagination($page, $perPage, $total);

        // Get users (without password)
        $stmt = $db->prepare("SELECT id, username, email, role, created_at FROM users WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = $pagination['offset'];
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        jsonResponse(true, 'Users retrieved successfully', [
            'users' => $users,
            'pagination' => $pagination
        ]);

    case 'get':
        requireAdmin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            jsonResponse(true, 'User retrieved successfully', $user);
        } else {
            jsonResponse(false, 'User not found');
        }

    case 'create':
        requireAdmin();
        $db = getDBConnection();
        $data = json_decode(file_get_contents('php://input'), true);

        // Check if username or email already exists
        $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$data['username'], $data['email']]);
        if ($checkStmt->fetch()) {
            jsonResponse(false, 'Username or email already exists');
        }

        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([
            cleanInput($data['username']),
            cleanInput($data['email']),
            $password,
            $data['role'] ?? 'editor'
        ]);

        if ($result) {
            jsonResponse(true, 'User created successfully', ['id' => $db->lastInsertId()]);
        } else {
            jsonResponse(false, 'Failed to create user');
        }

    case 'update':
        requireAdmin();
        $db = getDBConnection();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];

        // Check if username or email already exists (excluding current user)
        $checkStmt = $db->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $checkStmt->execute([$data['username'], $data['email'], $id]);
        if ($checkStmt->fetch()) {
            jsonResponse(false, 'Username or email already exists');
        }

        if (!empty($data['password'])) {
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?");
            $result = $stmt->execute([
                cleanInput($data['username']),
                cleanInput($data['email']),
                $password,
                $data['role'],
                $id
            ]);
        } else {
            $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
            $result = $stmt->execute([
                cleanInput($data['username']),
                cleanInput($data['email']),
                $data['role'],
                $id
            ]);
        }

        if ($result) {
            jsonResponse(true, 'User updated successfully');
        } else {
            jsonResponse(false, 'Failed to update user');
        }

    case 'delete':
        requireAdmin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // Prevent deleting yourself
        if ($id === $_SESSION['user_id']) {
            jsonResponse(false, 'Cannot delete your own account');
        }

        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            jsonResponse(true, 'User deleted successfully');
        } else {
            jsonResponse(false, 'Failed to delete user');
        }

    default:
        jsonResponse(false, 'Invalid action');
}
