<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Handle track action first (no auth required)
if ($action === 'track') {
    $page = isset($_GET['page']) ? cleanInput($_GET['page']) : '';
    if (empty($page)) {
        jsonResponse(false, 'Page parameter is required');
    }

    $db = getDBConnection();
    $today = date('Y-m-d');

    // Check if entry exists for today
    $stmt = $db->prepare("SELECT id FROM page_views WHERE page = ? AND view_date = ?");
    $stmt->execute([$page, $today]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Increment count
        $stmt = $db->prepare("UPDATE page_views SET views_count = views_count + 1 WHERE id = ?");
        $stmt->execute([$existing['id']]);
    } else {
        // Create new entry
        $stmt = $db->prepare("INSERT INTO page_views (page, view_date, views_count) VALUES (?, ?, 1)");
        $stmt->execute([$page, $today]);
    }

    jsonResponse(true, 'Page view tracked successfully');
}

// Require auth for other actions
require_once __DIR__ . '/../includes/auth.php';

switch ($action) {
    case 'list':
        requireLogin();
        $db = getDBConnection();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        $pageFilter = isset($_GET['page_filter']) ? cleanInput($_GET['page_filter']) : '';
        $startDate = isset($_GET['start_date']) ? cleanInput($_GET['start_date']) : '';
        $endDate = isset($_GET['end_date']) ? cleanInput($_GET['end_date']) : '';

        $where = ['1=1'];
        $params = [];

        if ($pageFilter) {
            $where[] = "page = ?";
            $params[] = $pageFilter;
        }

        if ($startDate) {
            $where[] = "view_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $where[] = "view_date <= ?";
            $params[] = $endDate;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM page_views WHERE $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Get pagination
        $pagination = getPagination($page, $perPage, $total);

        // Get page views
        $stmt = $db->prepare("SELECT * FROM page_views WHERE $whereClause ORDER BY view_date DESC LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = $pagination['offset'];
        $stmt->execute($params);
        $views = $stmt->fetchAll();

        jsonResponse(true, 'Page views retrieved successfully', [
            'views' => $views,
            'pagination' => $pagination
        ]);

    case 'stats':
        requireLogin();
        $db = getDBConnection();
        $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;

        // Get total views by page
        $stmt = $db->query("SELECT page, SUM(views_count) as total_views FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY) GROUP BY page ORDER BY total_views DESC");
        $byPage = $stmt->fetchAll();

        // Get daily views
        $stmt = $db->query("SELECT view_date, SUM(views_count) as daily_views FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY) GROUP BY view_date ORDER BY view_date DESC");
        $daily = $stmt->fetchAll();

        // Get total views
        $stmt = $db->query("SELECT SUM(views_count) as total FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)");
        $total = $stmt->fetchColumn();

        jsonResponse(true, 'Page view statistics retrieved successfully', [
            'by_page' => $byPage,
            'daily' => $daily,
            'total' => $total ?: 0
        ]);

    default:
        jsonResponse(false, 'Invalid action');
}
