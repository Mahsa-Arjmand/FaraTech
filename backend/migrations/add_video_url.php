<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDBConnection();
    $column = $db->query("SHOW COLUMNS FROM projects LIKE 'video_url'");
    if ($column->rowCount() === 0) {
        $db->exec('ALTER TABLE projects ADD COLUMN video_url VARCHAR(500) NULL AFTER image_url');
        echo "Added projects.video_url.\n";
    } else {
        echo "projects.video_url already exists.\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Migration failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
