<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        requireLogin();
        $db = getDBConnection();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(name LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM team_positions WHERE $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Get pagination
        $pagination = getPagination($page, $perPage, $total);

        // Get positions
        $stmt = $db->prepare("SELECT * FROM team_positions WHERE $whereClause ORDER BY display_order ASC, created_at ASC LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = $pagination['offset'];
        $stmt->execute($params);
        $positions = $stmt->fetchAll();

        jsonResponse(true, 'Team positions retrieved successfully', [
            'positions' => $positions,
            'pagination' => $pagination
        ]);

    case 'get':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM team_positions WHERE id = ?");
        $stmt->execute([$id]);
        $position = $stmt->fetch();

        if ($position) {
            jsonResponse(true, 'Team position retrieved successfully', $position);
        } else {
            jsonResponse(false, 'Team position not found');
        }

    case 'create':
        requireLogin();
        $db = getDBConnection();
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $db->prepare("INSERT INTO team_positions (name, description, display_order) VALUES (?, ?, ?)");
        $result = $stmt->execute([
            cleanInput($data['name']),
            cleanInput($data['description'] ?? ''),
            (int)($data['display_order'] ?? 0)
        ]);

        if ($result) {
            jsonResponse(true, 'Team position created successfully', ['id' => $db->lastInsertId()]);
        } else {
            jsonResponse(false, 'Failed to create team position');
        }

    case 'update':
        requireLogin();
        $db = getDBConnection();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];

        $stmt = $db->prepare("UPDATE team_positions SET name = ?, description = ?, display_order = ? WHERE id = ?");
        $result = $stmt->execute([
            cleanInput($data['name']),
            cleanInput($data['description'] ?? ''),
            (int)($data['display_order'] ?? 0),
            $id
        ]);

        if ($result) {
            jsonResponse(true, 'Team position updated successfully');
        } else {
            jsonResponse(false, 'Failed to update team position');
        }

    case 'delete':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM team_positions WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            jsonResponse(true, 'Team position deleted successfully');
        } else {
            jsonResponse(false, 'Failed to delete team position');
        }

    case 'public_list':
        // Public API to get all positions
        $db = getDBConnection();
        $stmt = $db->query("SELECT * FROM team_positions ORDER BY display_order ASC, created_at ASC");
        $positions = $stmt->fetchAll();

        jsonResponse(true, 'Team positions retrieved successfully', $positions);

    default:
        jsonResponse(false, 'Invalid action');
}
