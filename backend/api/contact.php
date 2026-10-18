<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

try {
    $db = getDBConnection();

    // Get form data
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $topic = cleanInput($_POST['topic'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');

    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name, email, and message are required']);
        exit;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

    // Insert into database
    $stmt = $db->prepare("INSERT INTO contact_submissions (name, email, phone, topic, message, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$name, $email, $phone, $topic, $message, $ip_address]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save message']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
