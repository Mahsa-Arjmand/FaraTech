<?php
/**
 * FaraTech public portfolio API.
 * GET /api/public_projects.php?lang=fa|en&limit=100
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'], true) ? $_GET['lang'] : 'fa';
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;

try {
    $db = getDBConnection();
    $columnRows = $db->query('SHOW COLUMNS FROM projects')->fetchAll(PDO::FETCH_ASSOC);
    $columns = array_column($columnRows, 'Field');

    $localized = static function ($base, $localizedColumn) use ($columns, $lang) {
        if ($lang === 'fa' && in_array($localizedColumn, $columns, true)) {
            return "COALESCE(NULLIF($localizedColumn, ''), $base)";
        }
        return $base;
    };

    $videoColumn = in_array('video_url', $columns, true) ? 'video_url' : "''";
    $stmt = $db->prepare("\n        SELECT\n            id,\n            " . $localized('title', 'title_fa') . " AS title,\n            " . $localized('description', 'description_fa') . " AS description,\n            " . $localized('client', 'client_fa') . " AS client,\n            " . $localized('category', 'category_fa') . " AS category,\n            technologies,\n            image_url,\n            $videoColumn AS video_url,\n            project_url,\n            featured,\n            status,\n            created_at\n        FROM projects\n        WHERE status = 'published'\n        ORDER BY featured DESC, created_at DESC\n        LIMIT :limit\n    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($projects as &$project) {
        $technologies = array_map('trim', explode(',', (string)($project['technologies'] ?? '')));
        $project['technologies'] = array_values(array_filter($technologies, static function ($technology) {
            return $technology !== '';
        }));
        $project['image_url'] = publicMediaUrl($project['image_url'] ?? '');
        $project['video_url'] = publicMediaUrl($project['video_url'] ?? '');
    }
    unset($project);

    echo json_encode([
        'success' => true,
        'lang' => $lang,
        'count' => count($projects),
        'data' => $projects,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('Public project list failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load projects',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
