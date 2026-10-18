<?php
require_once __DIR__ . '/config/database.php';

$file = $_GET['file'] ?? '';
$filePath = __DIR__ . '/' . $file;

// Security check: ensure the file is in the uploads directory
$realPath = realpath($filePath);
$uploadsDir = realpath(__DIR__ . '/uploads');

if ($realPath === false || strpos($realPath, $uploadsDir) !== 0) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

if (!file_exists($filePath)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

// Get file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Set headers
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: public, max-age=31536000');

// Output file
readfile($filePath);
