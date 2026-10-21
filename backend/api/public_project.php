<?php
/**
 * FaraTech — Public Single Project API
 * GET /api/public_project.php?id=N&lang=fa|en
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'], true)
    ? $_GET['lang']
    : 'en';

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid project ID']);
    exit;
}

try {
    $db = getDBConnection();

    $stmt = $db->prepare("
        SELECT
            id,
            COALESCE(NULLIF(title_fa, ''), title)             AS title,
            COALESCE(NULLIF(description_fa, ''), description) AS description,
            COALESCE(NULLIF(client_fa, ''), client)           AS client,
            COALESCE(NULLIF(category_fa, ''), category)       AS category,
            technologies,
            image_url,
            video_url,
            project_url,
            featured,
            status,
            created_at
        FROM projects
        WHERE id = ? AND status = 'published'
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    // technologies به آرایه
    $techs = array_map('trim', explode(',', (string)$project['technologies']));
    $project['technologies'] = array_values(array_filter($techs, fn($t) => $t !== ''));

    echo json_encode([
        'success' => true,
        'lang'    => $lang,
        'data'    => $project,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load project', 'error' => $e->getMessage()]);
}