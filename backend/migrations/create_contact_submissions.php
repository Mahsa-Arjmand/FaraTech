<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = getDBConnection();
    
    // Create contact_submissions table
    $db->exec("CREATE TABLE IF NOT EXISTS contact_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        topic VARCHAR(100),
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Table 'contact_submissions' created successfully.\n";
    
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
