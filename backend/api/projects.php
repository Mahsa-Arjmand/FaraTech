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
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
        $status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(title LIKE ? OR description LIKE ? OR client LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status) {
            $where[] = "status = ?";
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM projects WHERE $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Get pagination
        $pagination = getPagination($page, $perPage, $total);

        // Get projects
        $stmt = $db->prepare("SELECT * FROM projects WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = $pagination['offset'];
        $stmt->execute($params);
        $projects = $stmt->fetchAll();

        jsonResponse(true, 'Projects retrieved successfully', [
            'projects' => $projects,
            'pagination' => $pagination
        ]);

    case 'get':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();

        if ($project) {
            jsonResponse(true, 'Project retrieved successfully', $project);
        } else {
            jsonResponse(false, 'Project not found');
        }

    case 'create':
        requireLogin();
        $db = getDBConnection();
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $db->prepare("INSERT INTO projects (title, description, client, category, technologies, image_url, project_url, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([
            cleanInput($data['title']),
            cleanInput($data['description'] ?? ''),
            cleanInput($data['client'] ?? ''),
            cleanInput($data['category'] ?? ''),
            cleanInput($data['technologies'] ?? ''),
            cleanInput($data['image_url'] ?? ''),
            cleanInput($data['project_url'] ?? ''),
            $data['featured'] ?? false,
            $data['status'] ?? 'draft'
        ]);

        if ($result) {
            jsonResponse(true, 'Project created successfully', ['id' => $db->lastInsertId()]);
        } else {
            jsonResponse(false, 'Failed to create project');
        }

    case 'update':
        requireLogin();
        $db = getDBConnection();
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)$data['id'];

        $stmt = $db->prepare("UPDATE projects SET title = ?, description = ?, client = ?, category = ?, technologies = ?, image_url = ?, project_url = ?, featured = ?, status = ? WHERE id = ?");
        $result = $stmt->execute([
            cleanInput($data['title']),
            cleanInput($data['description'] ?? ''),
            cleanInput($data['client'] ?? ''),
            cleanInput($data['category'] ?? ''),
            cleanInput($data['technologies'] ?? ''),
            cleanInput($data['image_url'] ?? ''),
            cleanInput($data['project_url'] ?? ''),
            $data['featured'] ?? false,
            $data['status'] ?? 'draft',
            $id
        ]);

        if ($result) {
            jsonResponse(true, 'Project updated successfully');
        } else {
            jsonResponse(false, 'Failed to update project');
        }

    case 'delete':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            jsonResponse(true, 'Project deleted successfully');
        } else {
            jsonResponse(false, 'Failed to delete project');
        }

    default:
        jsonResponse(false, 'Invalid action');
}
