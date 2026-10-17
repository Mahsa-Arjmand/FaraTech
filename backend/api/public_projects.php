<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $db = getDBConnection();

    // Get published projects
    $stmt = $db->prepare("SELECT * FROM projects WHERE status = 'published' ORDER BY featured DESC, created_at DESC");
    $stmt->execute();
    $projects = $stmt->fetchAll();

    // Format technologies as array
    foreach ($projects as &$project) {
        if ($project['technologies']) {
            $project['technologies'] = array_map('trim', explode(',', $project['technologies']));
        } else {
            $project['technologies'] = [];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $projects
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching projects'
    ]);
}
