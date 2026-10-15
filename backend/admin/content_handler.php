<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('content.php');
}

$db = getDBConnection();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$page = cleanInput($_POST['page']);
$section = cleanInput($_POST['section']);
$content_key = cleanInput($_POST['content_key']);
$content_value = cleanInput($_POST['content_value'] ?? '');

if ($id > 0) {
    // Update
    $stmt = $db->prepare("UPDATE content SET page = ?, section = ?, content_key = ?, content_value = ? WHERE id = ?");
    $result = $stmt->execute([$page, $section, $content_key, $content_value, $id]);
    if ($result) {
        $_SESSION['flash_success'] = 'Content updated successfully';
    } else {
        $_SESSION['flash_error'] = 'Failed to update content';
    }
} else {
    // Create (or update if exists)
    $stmt = $db->prepare("INSERT INTO content (page, section, content_key, content_value) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE content_value = ?");
    $result = $stmt->execute([$page, $section, $content_key, $content_value, $content_value]);
    if ($result) {
        $_SESSION['flash_success'] = 'Content saved successfully';
    } else {
        $_SESSION['flash_error'] = 'Failed to save content';
    }
}

redirect('content.php');
