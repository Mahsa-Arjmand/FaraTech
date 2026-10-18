<?php
require_once 'config/database.php';

try {
    // Connect to MySQL without database to create it
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '" . DB_NAME . "' created or already exists.\n";

    // Select the database
    $pdo->exec("USE " . DB_NAME);

    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'editor') DEFAULT 'editor',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Table 'users' created or already exists.\n";

    // Create projects table
    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        client VARCHAR(100),
        category VARCHAR(100),
        technologies TEXT,
        image_url VARCHAR(500),
        video_url VARCHAR(500),
        project_url VARCHAR(500),
        featured BOOLEAN DEFAULT FALSE,
        status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Table 'projects' created or already exists.\n";

    // Create content table
    $pdo->exec("CREATE TABLE IF NOT EXISTS content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page VARCHAR(50) NOT NULL,
        section VARCHAR(50) NOT NULL,
        content_key VARCHAR(100) NOT NULL,
        content_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_content (page, section, content_key)
    )");
    echo "Table 'content' created or already exists.\n";

    // Create page_views table
    $pdo->exec("CREATE TABLE IF NOT EXISTS page_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page VARCHAR(50) NOT NULL,
        view_date DATE NOT NULL,
        views_count INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_page_date (page, view_date)
    )");
    echo "Table 'page_views' created or already exists.\n";

    // Create comments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page VARCHAR(50) NOT NULL,
        author_name VARCHAR(100) NOT NULL,
        author_email VARCHAR(100),
        comment_text TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Table 'comments' created or already exists.\n";

    // Create team_positions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS team_positions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Table 'team_positions' created or already exists.\n";

    // Create job_applications table
    $pdo->exec("CREATE TABLE IF NOT EXISTS job_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        position_id INT,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        cover_letter TEXT,
        resume_url VARCHAR(500),
        portfolio_url VARCHAR(500),
        linkedin_url VARCHAR(500),
        status ENUM('pending', 'reviewed', 'interview', 'hired', 'rejected') DEFAULT 'pending',
        notes TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (position_id) REFERENCES team_positions(id) ON DELETE SET NULL
    )");
    echo "Table 'job_applications' created or already exists.\n";

    // Create default admin user (password: admin123)
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@faratech.com', $password, 'admin']);
    echo "Default admin user created (username: admin, password: admin123).\n";

    echo "\nDatabase setup completed successfully!\n";

} catch (PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
