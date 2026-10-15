<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

try {
    $db = getDBConnection();
    $page = isset($_GET['page']) ? cleanInput($_GET['page']) : '';

    if (empty($page)) {
        echo json_encode([
            'success' => false,
            'message' => 'Page parameter is required'
        ]);
        exit;
    }

    // Get content for the page
    $stmt = $db->prepare("SELECT * FROM content WHERE page = ? ORDER BY section, content_key");
    $stmt->execute([$page]);
    $content = $stmt->fetchAll();

    // Group by section
    $grouped = [];
    foreach ($content as $item) {
        $grouped[$item['section']][$item['content_key']] = $item['content_value'];
    }

    echo json_encode([
        'success' => true,
        'data' => $grouped
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching content'
    ]);
}
