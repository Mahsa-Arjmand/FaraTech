<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDBConnection();
    
    // Create project_images table
    $db->exec("CREATE TABLE IF NOT EXISTS project_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT NOT NULL,
        image_url VARCHAR(500) NOT NULL,
        caption TEXT,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
    )");
    echo "Table 'project_images' created successfully.\n";
    
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
