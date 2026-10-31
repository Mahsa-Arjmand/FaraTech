<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Public tracking endpoint: one database increment for each page load.
if ($action === 'track') {
    $page = isset($_GET['page']) ? cleanInput($_GET['page']) : cleanInput($_POST['page'] ?? '');
    $allowedPages = ['index', 'about', 'services', 'portfolio', 'team', 'contact', 'ai-lab', 'project-detail'];
    if ($page === '' || !in_array($page, $allowedPages, true)) {
        http_response_code(400);
        jsonResponse(false, 'A valid page name is required');
    }

    try {
        $db = getDBConnection();
        // Atomic upsert avoids losing increments when multiple visitors arrive together.
        $stmt = $db->prepare(
            'INSERT INTO page_views (page, view_date, views_count) VALUES (?, CURDATE(), 1)\n' .
            'ON DUPLICATE KEY UPDATE views_count = views_count + 1'
        );
        $stmt->execute([$page]);
        jsonResponse(true, 'Page view tracked');
    } catch (Throwable $e) {
        error_log('Page view tracking failed: ' . $e->getMessage());
        http_response_code(500);
        jsonResponse(false, 'Page view could not be tracked');
    }
}

// All analytics read endpoints require an authenticated admin/editor.
require_once __DIR__ . '/../includes/auth.php';

switch ($action) {
    case 'list':
        requireLogin();
        $db = getDBConnection();
        $page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
        $perPage = max(1, min(100, isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20));
        $pageFilter = isset($_GET['page_filter']) ? cleanInput($_GET['page_filter']) : '';
        $startDate = isset($_GET['start_date']) ? cleanInput($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? cleanInput($_GET['end_date']) : '';

        $where = ['1=1'];
        $params = [];
        if ($pageFilter) {
            $where[] = 'page = ?';
            $params[] = $pageFilter;
        }
        if ($startDate) {
            $where[] = 'view_date >= ?';
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = 'view_date <= ?';
            $params[] = $endDate;
        }

        $whereClause = implode(' AND ', $where);
        $countStmt = $db->prepare("SELECT COUNT(*) FROM page_views WHERE $whereClause");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $pagination = getPagination($page, $perPage, $total);

        $stmt = $db->prepare("SELECT * FROM page_views WHERE $whereClause ORDER BY view_date DESC, page ASC LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = $pagination['offset'];
        $stmt->execute($params);
        $views = $stmt->fetchAll();

        jsonResponse(true, 'Page views retrieved successfully', [
            'views' => $views,
            'pagination' => $pagination,
        ]);

    case 'stats':
        requireLogin();
        $db = getDBConnection();
        $days = max(1, min(3650, isset($_GET['days']) ? (int)$_GET['days'] : 30));

        $stmt = $db->query("SELECT page, SUM(views_count) AS total_views FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY) GROUP BY page ORDER BY total_views DESC");
        $byPage = $stmt->fetchAll();

        $stmt = $db->query("SELECT view_date, SUM(views_count) AS daily_views FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY) GROUP BY view_date ORDER BY view_date DESC");
        $daily = $stmt->fetchAll();

        $stmt = $db->query("SELECT COALESCE(SUM(views_count), 0) FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)");
        $total = (int)$stmt->fetchColumn();

        jsonResponse(true, 'Page view statistics retrieved successfully', [
            'by_page' => $byPage,
            'daily' => $daily,
            'total' => $total,
        ]);

    default:
        http_response_code(400);
        jsonResponse(false, 'Invalid action');
}
