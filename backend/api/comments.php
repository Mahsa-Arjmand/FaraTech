<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        requireLogin();
        $db = getDBConnection();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
        $pageFilter = isset($_GET['page_filter']) ? cleanInput($_GET['page_filter']) : '';

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(author_name LIKE ? OR comment_text LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status) {
            $where[] = "status = ?";
            $params[] = $status;
        }

        if ($pageFilter) {
            $where[] = "page = ?";
            $params[] = $pageFilter;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Get pagination
        $pagination = getPagination($page, $perPage, $total);

        // Get comments
        $stmt = $db->prepare("SELECT * FROM comments WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = $pagination['offset'];
        $stmt->execute($params);
        $comments = $stmt->fetchAll();

        jsonResponse(true, 'Comments retrieved successfully', [
            'comments' => $comments,
            'pagination' => $pagination
        ]);

    case 'get':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        $comment = $stmt->fetch();

        if ($comment) {
            jsonResponse(true, 'Comment retrieved successfully', $comment);
        } else {
            jsonResponse(false, 'Comment not found');
        }

    case 'approve':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            jsonResponse(true, 'Comment approved successfully');
        } else {
            jsonResponse(false, 'Failed to approve comment');
        }

    case 'reject':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            jsonResponse(true, 'Comment rejected successfully');
        } else {
            jsonResponse(false, 'Failed to reject comment');
        }

    case 'delete':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            jsonResponse(true, 'Comment deleted successfully');
        } else {
            jsonResponse(false, 'Failed to delete comment');
        }

    case 'public_list':
        // Public API to get approved comments for a page
        $page = isset($_GET['page']) ? cleanInput($_GET['page']) : '';
        if (empty($page)) {
            jsonResponse(false, 'Page parameter is required');
        }

        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM comments WHERE page = ? AND status = 'approved' ORDER BY created_at DESC");
        $stmt->execute([$page]);
        $comments = $stmt->fetchAll();

        jsonResponse(true, 'Comments retrieved successfully', $comments);

    case 'public_create':
        // Public API to submit a comment
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['page']) || empty($data['author_name']) || empty($data['comment_text'])) {
            jsonResponse(false, 'Required fields: page, author_name, comment_text');
        }

        $db = getDBConnection();
        $stmt = $db->prepare("INSERT INTO comments (page, author_name, author_email, comment_text, ip_address, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $result = $stmt->execute([
            cleanInput($data['page']),
            cleanInput($data['author_name']),
            cleanInput($data['author_email'] ?? ''),
            cleanInput($data['comment_text']),
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);

        if ($result) {
            jsonResponse(true, 'Comment submitted successfully and awaiting approval');
        } else {
            jsonResponse(false, 'Failed to submit comment');
        }

    default:
        jsonResponse(false, 'Invalid action');
}
