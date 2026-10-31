<?php
/**
 * FaraTech — Public single-project API.
 * GET /api/public_project.php?id=N&lang=fa|en
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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'], true) ? $_GET['lang'] : 'fa';

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid project ID'], JSON_UNESCAPED_UNICODE);
    exit;
}

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
    $detailsColumn = in_array('details', $columns, true) ? 'details' : "''";
    $videoColumn = in_array('video_url', $columns, true) ? 'video_url' : "''";

    $stmt = $db->prepare("\n        SELECT\n            id,\n            " . $localized('title', 'title_fa') . " AS title,\n            " . $localized('description', 'description_fa') . " AS description,\n            " . $localized('client', 'client_fa') . " AS client,\n            " . $localized('category', 'category_fa') . " AS category,\n            $detailsColumn AS details,\n            technologies,\n            image_url,\n            $videoColumn AS video_url,\n            project_url,\n            featured,\n            status,\n            created_at,\n            updated_at\n        FROM projects\n        WHERE id = ? AND status = 'published'\n        LIMIT 1\n    ");
    $stmt->execute([$id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Project not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $technologies = array_map('trim', explode(',', (string)($project['technologies'] ?? '')));
    $project['technologies'] = array_values(array_filter($technologies, static function ($technology) {
        return $technology !== '';
    }));
    $project['image_url'] = publicMediaUrl($project['image_url'] ?? '');
    $project['video_url'] = publicMediaUrl($project['video_url'] ?? '');

    echo json_encode([
        'success' => true,
        'lang' => $lang,
        'data' => $project,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('Public project detail failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load project'], JSON_UNESCAPED_UNICODE);
}
