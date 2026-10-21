<?php
/**
 * Add i18n columns to projects table
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $db = getDBConnection();

    // Check if columns already exist
    $check = $db->query("SHOW COLUMNS FROM projects LIKE 'title_fa'");
    if ($check->rowCount() > 0) {
        echo "i18n columns already exist in projects table.\n";
        exit;
    }

    // Add i18n columns
    $sql = "
        ALTER TABLE projects
        ADD COLUMN title_fa VARCHAR(255) DEFAULT NULL AFTER title,
        ADD COLUMN description_fa TEXT DEFAULT NULL AFTER description,
        ADD COLUMN client_fa VARCHAR(255) DEFAULT NULL AFTER client,
        ADD COLUMN category_fa VARCHAR(255) DEFAULT NULL AFTER category
    ";

    $db->exec($sql);
    echo "i18n columns added to projects table successfully.\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
