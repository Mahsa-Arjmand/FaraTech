<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('team_positions.php');
}

$db = getDBConnection();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$name = cleanInput($_POST['name']);
$description = cleanInput($_POST['description'] ?? '');
$display_order = (int)$_POST['display_order'];

if ($id > 0) {
    // Update
    $stmt = $db->prepare("UPDATE team_positions SET name = ?, description = ?, display_order = ? WHERE id = ?");
    $result = $stmt->execute([$name, $description, $display_order, $id]);
    if ($result) {
        $_SESSION['flash_success'] = 'Position updated successfully';
    } else {
        $_SESSION['flash_error'] = 'Failed to update position';
    }
} else {
    // Create
    $stmt = $db->prepare("INSERT INTO team_positions (name, description, display_order) VALUES (?, ?, ?)");
    $result = $stmt->execute([$name, $description, $display_order]);
    if ($result) {
        $_SESSION['flash_success'] = 'Position created successfully';
    } else {
        $_SESSION['flash_error'] = 'Failed to create position';
    }
}

redirect('team_positions.php');
