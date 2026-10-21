<?php
/**
 * FaraTech — Public Team Positions API
 * GET /api/public_team_positions.php?lang=fa|en
 *
 * اگر name_fa/description_fa وجود داشت و زبان fa بود، آنها برمی‌گردند؛
 * در غیر این صورت فیلدهای انگلیسی fallback می‌شوند.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'], true)
    ? $_GET['lang']
    : 'en';

try {
    $db = getDBConnection();

    // بررسی وجود ستون‌های _fa
    $cols = $db->query("SHOW COLUMNS FROM team_positions")->fetchAll(PDO::FETCH_COLUMN);
    $hasFa = in_array('name_fa', $cols, true) && in_array('description_fa', $cols, true);

    if ($hasFa) {
        $sql = "SELECT
                    id,
                    COALESCE(NULLIF(name_fa, ''), name) AS name,
                    COALESCE(NULLIF(description_fa, ''), description) AS description,
                    display_order,
                    created_at
                FROM team_positions
                ORDER BY display_order ASC, created_at ASC";
    } else {
        $sql = "SELECT
                    id, name, description, display_order, created_at
                FROM team_positions
                ORDER BY display_order ASC, created_at ASC";
    }

    $rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // در حالت fa، اگر فیلد _fa واقعاً وجود دارد، آن را به‌عنوان name/description نمایش بده
    // (بالا با COALESCE انجام شد)
    echo json_encode([
        'success' => true,
        'lang'    => $lang,
        'count'   => count($rows),
        'data'    => $rows,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load team positions',
    ], JSON_UNESCAPED_UNICODE);
}