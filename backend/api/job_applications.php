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
        $status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
        $positionId = isset($_GET['position_id']) ? (int)$_GET['position_id'] : 0;

        $where = ['1=1'];
        $params = [];

        if ($search) {
            $where[] = "(full_name LIKE ? OR email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($status) {
            $where[] = "status = ?";
            $params[] = $status;
        }

        if ($positionId > 0) {
            $where[] = "position_id = ?";
            $params[] = $positionId;
        }

        $whereClause = implode(' AND ', $where);

        // Get total count
        $countStmt = $db->prepare("SELECT COUNT(*) FROM job_applications WHERE $whereClause");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Get pagination
        $pagination = getPagination($page, $perPage, $total);

        // Get applications with position name
        $stmt = $db->prepare("SELECT ja.*, tp.name as position_name FROM job_applications ja LEFT JOIN team_positions tp ON ja.position_id = tp.id WHERE $whereClause ORDER BY ja.created_at DESC LIMIT ? OFFSET ?");
        $params[] = $perPage;
        $params[] = $pagination['offset'];
        $stmt->execute($params);
        $applications = $stmt->fetchAll();

        jsonResponse(true, 'Job applications retrieved successfully', [
            'applications' => $applications,
            'pagination' => $pagination
        ]);

    case 'get':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT ja.*, tp.name as position_name FROM job_applications ja LEFT JOIN team_positions tp ON ja.position_id = tp.id WHERE ja.id = ?");
        $stmt->execute([$id]);
        $application = $stmt->fetch();

        if ($application) {
            jsonResponse(true, 'Job application retrieved successfully', $application);
        } else {
            jsonResponse(false, 'Job application not found');
        }

    case 'update_status':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
        $notes = isset($_GET['notes']) ? cleanInput($_GET['notes']) : '';

        if (!in_array($status, ['pending', 'reviewed', 'interview', 'hired', 'rejected'])) {
            jsonResponse(false, 'Invalid status');
        }

        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE job_applications SET status = ?, notes = ? WHERE id = ?");
        $result = $stmt->execute([$status, $notes, $id]);

        if ($result) {
            jsonResponse(true, 'Job application status updated successfully');
        } else {
            jsonResponse(false, 'Failed to update job application status');
        }

    case 'delete':
        requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $db = getDBConnection();
        $stmt = $db->prepare("DELETE FROM job_applications WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            jsonResponse(true, 'Job application deleted successfully');
        } else {
            jsonResponse(false, 'Failed to delete job application');
        }

    case 'public_create':
        // Public API to submit job application
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['full_name']) || empty($data['email'])) {
            jsonResponse(false, 'Required fields: full_name, email');
        }

        $db = getDBConnection();
        $stmt = $db->prepare("INSERT INTO job_applications (position_id, full_name, email, phone, cover_letter, resume_url, portfolio_url, linkedin_url, ip_address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $result = $stmt->execute([
            (int)($data['position_id'] ?? 0),
            cleanInput($data['full_name']),
            cleanInput($data['email']),
            cleanInput($data['phone'] ?? ''),
            cleanInput($data['cover_letter'] ?? ''),
            cleanInput($data['resume_url'] ?? ''),
            cleanInput($data['portfolio_url'] ?? ''),
            cleanInput($data['linkedin_url'] ?? ''),
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);

        if ($result) {
            jsonResponse(true, 'Job application submitted successfully');
        } else {
            jsonResponse(false, 'Failed to submit job application');
        }

    default:
        jsonResponse(false, 'Invalid action');
}
