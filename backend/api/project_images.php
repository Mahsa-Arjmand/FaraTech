<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $db = getDBConnection();
    $action = $_GET['action'] ?? 'list';

    if ($action === 'list') {
        $project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

        if ($project_id === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Project ID is required']);
            exit;
        }

        $projectStmt = $db->prepare('SELECT status FROM projects WHERE id = ? LIMIT 1');
        $projectStmt->execute([$project_id]);
        $projectStatus = $projectStmt->fetchColumn();
        if ($projectStatus === false) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Project not found']);
            exit;
        }
        if ($projectStatus !== 'published') {
            require_once __DIR__ . '/../includes/auth.php';
            requireLogin();
        }

        $stmt = $db->prepare("SELECT * FROM project_images WHERE project_id = ? ORDER BY display_order ASC, created_at ASC");
        $stmt->execute([$project_id]);
        $images = $stmt->fetchAll();
        foreach ($images as &$image) {
            $image['image_url'] = publicMediaUrl($image['image_url'] ?? '');
        }
        unset($image);

        echo json_encode(['success' => true, 'data' => $images], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    } elseif ($action === 'delete') {
        require_once __DIR__ . '/../includes/auth.php';
        requireLogin();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Image ID is required']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM project_images WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);

    } elseif ($action === 'add') {
        require_once __DIR__ . '/../includes/auth.php';
        requireLogin();
        require_once __DIR__ . '/../includes/functions.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
        $caption = cleanInput($_POST['caption'] ?? '');

        if ($project_id === 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Project ID is required']);
            exit;
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Image file is required']);
            exit;
        }

        $imageFile = $_FILES['image'];
        $imageExt = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        $imageName = 'gallery_' . time() . '_' . uniqid() . '.' . $imageExt;
        $uploadPath = __DIR__ . '/../uploads/projects/' . $imageName;

        if (move_uploaded_file($imageFile['tmp_name'], $uploadPath)) {
            $image_url = '/uploads.php?file=' . rawurlencode('uploads/projects/' . $imageName);

            $stmt = $db->prepare("INSERT INTO project_images (project_id, image_url, caption, display_order) VALUES (?, ?, ?, 0)");
            $stmt->execute([$project_id, $image_url, $caption]);

            echo json_encode(['success' => true, 'message' => 'Image added successfully', 'data' => ['id' => $db->lastInsertId(), 'image_url' => $image_url]]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        }

    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
