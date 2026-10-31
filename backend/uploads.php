<?php
$relativePath = str_replace(chr(92), '/', (string)($_GET['file'] ?? ''));
$filePath = __DIR__ . '/' . ltrim($relativePath, '/');
$realPath = realpath($filePath);
$uploadsDir = realpath(__DIR__ . '/uploads');
$uploadsPrefix = $uploadsDir ? $uploadsDir . DIRECTORY_SEPARATOR : '';

if (!$realPath || !$uploadsDir || strpos($realPath, $uploadsPrefix) !== 0 || !is_file($realPath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'File not found';
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = $finfo ? finfo_file($finfo, $realPath) : 'application/octet-stream';
if ($finfo) finfo_close($finfo);

$fileSize = filesize($realPath);
$start = 0;
$end = $fileSize - 1;
$status = 200;

header('Content-Type: ' . ($mimeType ?: 'application/octet-stream'));
header('Content-Disposition: inline; filename="' . rawurlencode(basename($realPath)) . '"');
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=31536000, immutable');
header('X-Content-Type-Options: nosniff');

$range = $_SERVER['HTTP_RANGE'] ?? '';
if ($range !== '') {
    if (!preg_match('/^bytes=(\d*)-(\d*)$/', trim($range), $matches)) {
        http_response_code(416);
        header('Content-Range: bytes */' . $fileSize);
        exit;
    }

    if ($matches[1] === '' && $matches[2] !== '') {
        $suffixLength = (int)$matches[2];
        $start = max(0, $fileSize - $suffixLength);
    } else {
        $start = (int)$matches[1];
        if ($matches[2] !== '') $end = (int)$matches[2];
    }

    if ($start > $end || $start >= $fileSize) {
        http_response_code(416);
        header('Content-Range: bytes */' . $fileSize);
        exit;
    }

    $end = min($end, $fileSize - 1);
    $status = 206;
    http_response_code($status);
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
}

$length = $end - $start + 1;
header('Content-Length: ' . $length);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') exit;

$handle = fopen($realPath, 'rb');
if (!$handle) {
    http_response_code(500);
    exit;
}
fseek($handle, $start);
$remaining = $length;
while ($remaining > 0 && !feof($handle)) {
    $chunk = fread($handle, min(1024 * 1024, $remaining));
    if ($chunk === false || $chunk === '') break;
    echo $chunk;
    $remaining -= strlen($chunk);
    if (connection_aborted()) break;
}
fclose($handle);
