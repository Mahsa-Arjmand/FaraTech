<?php
/**
 * FaraTech — Public Projects API
 * GET /api/public_projects.php?lang=fa|en&limit=N
 *
 * اگر lang=fa و فیلد title_fa خالی بود، به title انگلیسی fallback می‌شود.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'], true)
    ? $_GET['lang']
    : 'en';

$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;

try {
    $db = getDBConnection();

    // Check if i18n columns exist
    $check = $db->query("SHOW COLUMNS FROM projects LIKE 'title_fa'");
    $hasI18n = $check->rowCount() > 0;

    if ($hasI18n) {
        $stmt = $db->prepare("
            SELECT
                id,
                COALESCE(NULLIF(title_fa, ''), title)       AS title,
                COALESCE(NULLIF(description_fa, ''), description) AS description,
                COALESCE(NULLIF(client_fa, ''), client)     AS client,
                COALESCE(NULLIF(category_fa, ''), category) AS category,
                technologies,
                image_url,
                video_url,
                project_url,
                featured,
                status,
                created_at
            FROM projects
            WHERE status = 'published'
            ORDER BY featured DESC, created_at DESC
            LIMIT :limit
        ");
    } else {
        $stmt = $db->prepare("
            SELECT
                id,
                title,
                description,
                client,
                category,
                technologies,
                image_url,
                video_url,
                project_url,
                featured,
                status,
                created_at
            FROM projects
            WHERE status = 'published'
            ORDER BY featured DESC, created_at DESC
            LIMIT :limit
        ");
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // technologies رو به آرایه تبدیل کن
    foreach ($projects as &$p) {
        $techs = array_map('trim', explode(',', (string)$p['technologies']));
        $p['technologies'] = array_values(array_filter($techs, fn($t) => $t !== ''));
    }
    unset($p);

    echo json_encode([
        'success' => true,
        'lang'    => $lang,
        'count'   => count($projects),
        'data'    => $projects,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load projects',
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}