<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('projects.php');
}

$db = getDBConnection();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$title = cleanInput($_POST['title']);
$description = cleanInput($_POST['description'] ?? '');
$details = cleanInput($_POST['details'] ?? '');
$client = cleanInput($_POST['client'] ?? '');
$category = cleanInput($_POST['category'] ?? '');
$technologies = cleanInput($_POST['technologies'] ?? '');
$image_url = cleanInput($_POST['image_url'] ?? '');
$video_url = cleanInput($_POST['video_url'] ?? '');
$project_url = cleanInput($_POST['project_url'] ?? '');
$featured = isset($_POST['featured']) ? 1 : 0;
$status = cleanInput($_POST['status'] ?? 'draft');

// Handle image upload
if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] === UPLOAD_ERR_OK) {
    $imageFile = $_FILES['image_upload'];
    $imageExt = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
    $imageName = 'project_' . time() . '_' . uniqid() . '.' . $imageExt;
    $uploadPath = __DIR__ . '/../uploads/projects/' . $imageName;

    if (move_uploaded_file($imageFile['tmp_name'], $uploadPath)) {
        $image_url = '/uploads.php?file=' . rawurlencode('uploads/projects/' . $imageName);
    }
}

// Handle video upload
if (isset($_FILES['video_upload']) && $_FILES['video_upload']['error'] === UPLOAD_ERR_OK) {
    $videoFile = $_FILES['video_upload'];
    $videoExt = pathinfo($videoFile['name'], PATHINFO_EXTENSION);
    $videoName = 'video_' . time() . '_' . uniqid() . '.' . $videoExt;
    $uploadPath = __DIR__ . '/../uploads/projects/' . $videoName;

    if (move_uploaded_file($videoFile['tmp_name'], $uploadPath)) {
        $video_url = '/uploads.php?file=' . rawurlencode('uploads/projects/' . $videoName);
    }
}

// Handle gallery images
$gallery_images = $_FILES['gallery_images'] ?? [];
$gallery_captions = $_POST['gallery_captions'] ?? [];

if ($id > 0) {
    // Update
    $stmt = $db->prepare("UPDATE projects SET title = ?, description = ?, details = ?, client = ?, category = ?, technologies = ?, image_url = ?, video_url = ?, project_url = ?, featured = ?, status = ? WHERE id = ?");
    $result = $stmt->execute([$title, $description, $details, $client, $category, $technologies, $image_url, $video_url, $project_url, $featured, $status, $id]);
    if ($result) {
        $_SESSION['flash_success'] = 'Project updated successfully';
    } else {
        $_SESSION['flash_error'] = 'Failed to update project';
    }
} else {
    // Create
    $stmt = $db->prepare("INSERT INTO projects (title, description, details, client, category, technologies, image_url, video_url, project_url, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$title, $description, $details, $client, $category, $technologies, $image_url, $video_url, $project_url, $featured, $status]);
    if ($result) {
        $id = $db->lastInsertId();
        $_SESSION['flash_success'] = 'Project created successfully';
    } else {
        $_SESSION['flash_error'] = 'Failed to create project';
    }
}

// Handle gallery images after project is created/updated
if ($id > 0 && !empty($gallery_images['name'])) {
    foreach ($gallery_images['name'] as $index => $imageName) {
        if ($gallery_images['error'][$index] === UPLOAD_ERR_OK) {
            $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);
            $newImageName = 'gallery_' . time() . '_' . uniqid() . '.' . $imageExt;
            $uploadPath = __DIR__ . '/../uploads/projects/' . $newImageName;

            if (move_uploaded_file($gallery_images['tmp_name'][$index], $uploadPath)) {
                $imageUrl = '/uploads.php?file=' . rawurlencode('uploads/projects/' . $newImageName);
                $caption = cleanInput($gallery_captions[$index] ?? '');

                $stmt = $db->prepare("INSERT INTO project_images (project_id, image_url, caption, display_order) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $imageUrl, $caption, $index]);
            }
        }
    }
}

redirect('projects.php');
