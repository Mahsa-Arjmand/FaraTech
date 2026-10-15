<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'tables':
        requireAdmin();
        $db = getDBConnection();
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        jsonResponse(true, 'Tables retrieved successfully', ['tables' => $tables]);

    case 'table_data':
        requireAdmin();
        $table = isset($_GET['table']) ? cleanInput($_GET['table']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;

        if (empty($table)) {
            jsonResponse(false, 'Table name is required');
        }

        $db = getDBConnection();

        // Get total count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM `$table`");
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        // Get pagination
        $pagination = getPagination($page, $perPage, $total);

        // Get table structure
        $structureStmt = $db->query("DESCRIBE `$table`");
        $structure = $structureStmt->fetchAll();

        // Get table data
        $dataStmt = $db->prepare("SELECT * FROM `$table` LIMIT ? OFFSET ?");
        $dataStmt->execute([$perPage, $pagination['offset']]);
        $data = $dataStmt->fetchAll();

        jsonResponse(true, 'Table data retrieved successfully', [
            'structure' => $structure,
            'data' => $data,
            'pagination' => $pagination
        ]);

    case 'table_info':
        requireAdmin();
        $table = isset($_GET['table']) ? cleanInput($_GET['table']) : '';

        if (empty($table)) {
            jsonResponse(false, 'Table name is required');
        }

        $db = getDBConnection();

        // Get table structure
        $structureStmt = $db->query("DESCRIBE `$table`");
        $structure = $structureStmt->fetchAll();

        // Get row count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM `$table`");
        $countStmt->execute();
        $rowCount = $countStmt->fetchColumn();

        jsonResponse(true, 'Table info retrieved successfully', [
            'structure' => $structure,
            'rowCount' => $rowCount
        ]);

    default:
        jsonResponse(false, 'Invalid action');
}
