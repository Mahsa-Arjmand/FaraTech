<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $db = getDBConnection();
    
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id === 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Project ID is required'
        ]);
        exit;
    }
    
    // Get single project
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ? AND status = 'published'");
    $stmt->execute([$id]);
    $project = $stmt->fetch();
    
    if (!$project) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Project not found'
        ]);
        exit;
    }
    
    // Format technologies as array
    if ($project['technologies']) {
        $project['technologies'] = array_map('trim', explode(',', $project['technologies']));
    } else {
        $project['technologies'] = [];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $project
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching project'
    ]);
}
