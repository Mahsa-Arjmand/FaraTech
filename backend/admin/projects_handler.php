<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('projects.php');
}

$db = getDBConnection();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$title = cleanInput($_POST['title']);
$description = cleanInput($_POST['description'] ?? '');
$client = cleanInput($_POST['client'] ?? '');
$category = cleanInput($_POST['category'] ?? '');
$technologies = cleanInput($_POST['technologies'] ?? '');
$image_url = cleanInput($_POST['image_url'] ?? '');
$project_url = cleanInput($_POST['project_url'] ?? '');
$featured = isset($_POST['featured']) ? 1 : 0;
$status = cleanInput($_POST['status'] ?? 'draft');

if ($id > 0) {
    // Update
    $stmt = $db->prepare("UPDATE projects SET title = ?, description = ?, client = ?, category = ?, technologies = ?, image_url = ?, project_url = ?, featured = ?, status = ? WHERE id = ?");
    $result = $stmt->execute([$title, $description, $client, $category, $technologies, $image_url, $project_url, $featured, $status, $id]);
    if ($result) {
        $_SESSION['flash_success'] = 'Project updated successfully';
    } else {
        $_SESSION['flash_error'] = 'Failed to update project';
    }
} else {
    // Create
    $stmt = $db->prepare("INSERT INTO projects (title, description, client, category, technologies, image_url, project_url, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$title, $description, $client, $category, $technologies, $image_url, $project_url, $featured, $status]);
    if ($result) {
        $_SESSION['flash_success'] = 'Project created successfully';
    } else {
        $_SESSION['flash_error'] = 'Failed to create project';
    }
}

redirect('projects.php');
