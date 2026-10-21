<?php
/**
 * FaraTech — Public Content API
 * GET /api/public_content.php?page=index&lang=fa|en
 * GET /api/public_content.php?page=index&section=hero&lang=fa
 *
 * خروجی: { success, lang, data: { section: { key: value, ... }, ... } }
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

$page = isset($_GET['page']) ? trim($_GET['page']) : '';
$section = isset($_GET['section']) ? trim($_GET['section']) : '';
$lang = isset($_GET['lang']) && in_array($_GET['lang'], ['fa', 'en'], true)
    ? $_GET['lang']
    : 'en';

if ($page === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'page parameter is required']);
    exit;
}

try {
    $db = getDBConnection();

    $sql = "SELECT section, content_key, content_value
            FROM content
            WHERE page = :page AND language = :lang";
    $params = [':page' => $page, ':lang' => $lang];

    if ($section !== '') {
        $sql .= " AND section = :section";
        $params[':section'] = $section;
    }

    $sql .= " ORDER BY section, content_key";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fallback: اگر هیچ رکوردی با زبان درخواستی نبود، انگلیسی برگردون
    if (empty($rows) && $lang !== 'en') {
        $sqlEn = str_replace(':lang', "'en'", $sql);
        // برای امنیت، دوباره با prepare
        $stmtEn = $db->prepare(
            "SELECT section, content_key, content_value
             FROM content
             WHERE page = :page AND language = 'en'"
             . ($section !== '' ? " AND section = :section" : "")
             . " ORDER BY section, content_key"
        );
        $paramsEn = [':page' => $page];
        if ($section !== '') $paramsEn[':section'] = $section;
        $stmtEn->execute($paramsEn);
        $rows = $stmtEn->fetchAll(PDO::FETCH_ASSOC);
    }

    // گروه‌بندی بر اساس section
    $grouped = [];
    foreach ($rows as $row) {
        $sec = $row['section'];
        if (!isset($grouped[$sec])) $grouped[$sec] = [];
        $grouped[$sec][$row['content_key']] = $row['content_value'];
    }

    echo json_encode([
        'success' => true,
        'lang'    => $lang,
        'page'    => $page,
        'data'    => $grouped,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load content']);
}