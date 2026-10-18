<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDBConnection();
    
    // Add video_url column if it doesn't exist
    $db->exec("ALTER TABLE projects ADD COLUMN video_url VARCHAR(500) AFTER image_url");
    echo "Column 'video_url' added to projects table successfully.\n";
    
} catch (PDOException $e) {
    // Column might already exist, check if that's the case
    $db = getDBConnection();
    $stmt = $db->query("SHOW COLUMNS FROM projects LIKE 'video_url'");
    if ($stmt->fetch()) {
        echo "Column 'video_url' already exists in projects table.\n";
    } else {
        die("Migration failed: " . $e->getMessage());
    }
}
