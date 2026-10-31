<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDBConnection();
    $column = $db->query("SHOW COLUMNS FROM projects LIKE 'details'");
    if ($column->rowCount() === 0) {
        $db->exec('ALTER TABLE projects ADD COLUMN details TEXT NULL AFTER description');
        echo "Added projects.details.\n";
    } else {
        echo "projects.details already exists.\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo 'Migration failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
