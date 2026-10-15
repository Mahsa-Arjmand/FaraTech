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
        $pageFilter = isset($_GET['page_filter']) ? cleanInput($_GET['page_filter']) : '';

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(content_key LIKE ? OR content_value LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($pageFilter) {
            $where[] = "page = ?";
            $params[] = $pageFilter;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM content WHERE $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Get pagination
        $pagination = getPagination($page, $perPage, $total);

        // Get content
        $stmt = $db->prepare("SELECT * FROM content WHERE $whereClause ORDER BY page, section, content_key LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = $pagination['offset'];
        $stmt->execute($params);
        $content = $stmt->fetchAll();

        jsonResponse(true, 'Content retrieved successfully', [
            'content' => $content,
            'pagination' => $pagination
        ]);

    case 'get':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM content WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if ($item) {
            jsonResponse(true, 'Content retrieved successfully', $item);
        } else {
            jsonResponse(false, 'Content not found');
        }

    case 'create':
        requireLogin();
        $db = getDBConnection();
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $db->prepare("INSERT INTO content (page, section, content_key, content_value) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE content_value = ?");
        $result = $stmt->execute([
            cleanInput($data['page']),
            cleanInput($data['section']),
            cleanInput($data['content_key']),
            cleanInput($data['content_value'] ?? ''),
            cleanInput($data['content_value'] ?? '')
        ]);

        if ($result) {
            jsonResponse(true, 'Content saved successfully');
        } else {
            jsonResponse(false, 'Failed to save content');
        }

    case 'update':
        requireLogin();
        $db = getDBConnection();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];

        $stmt = $db->prepare("UPDATE content SET page = ?, section = ?, content_key = ?, content_value = ? WHERE id = ?");
        $result = $stmt->execute([
            cleanInput($data['page']),
            cleanInput($data['section']),
            cleanInput($data['content_key']),
            cleanInput($data['content_value'] ?? ''),
            $id
        ]);

        if ($result) {
            jsonResponse(true, 'Content updated successfully');
        } else {
            jsonResponse(false, 'Failed to update content');
        }

    case 'delete':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM content WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            jsonResponse(true, 'Content deleted successfully');
        } else {
            jsonResponse(false, 'Failed to delete content');
        }

    case 'get_page_content':
        requireLogin();
        $page = isset($_GET['page']) ? cleanInput($_GET['page']) : '';
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM content WHERE page = ? ORDER BY section, content_key");
        $stmt->execute([$page]);
        $content = $stmt->fetchAll();

        // Group by section
        $grouped = [];
        foreach ($content as $item) {
            $grouped[$item['section']][$item['content_key']] = $item['content_value'];
        }

        jsonResponse(true, 'Page content retrieved successfully', $grouped);

    default:
        jsonResponse(false, 'Invalid action');
}
