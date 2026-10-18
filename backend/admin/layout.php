<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>FaraTech Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            flex-shrink: 0;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #34495e;
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 20px;
            font-weight: 600;
        }

        .nav-item {
            display: block;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: background 0.3s;
        }

        .nav-item:hover,
        .nav-item.active {
            background: #34495e;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            color: #2c3e50;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-name {
            color: #7f8c8d;
        }

        .logout-btn {
            padding: 8px 16px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 8px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 600;
            color: #2c3e50;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #229954;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .pagination {
            display: flex;
            gap: 8px;
            margin-top: 20px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #2c3e50;
        }

        .pagination a:hover {
            background: #f8f9fa;
        }

        .pagination .active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-published {
            background: #d4edda;
            color: #155724;
        }

        .badge-draft {
            background: #fff3cd;
            color: #856404;
        }

        .badge-archived {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-approved {
            background: #d4edda;
            color: #155724;
        }

        .badge-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-reviewed {
            background: #cce5ff;
            color: #004085;
        }

        .badge-interview {
            background: #e2e3e5;
            color: #383d41;
        }

        .badge-hired {
            background: #d4edda;
            color: #155724;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>FaraTech Admin</h2>
        </div>
        <a href="dashboard.php" class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="projects.php" class="nav-item <?php echo $currentPage == 'projects.php' ? 'active' : ''; ?>">Projects</a>
        <a href="content.php" class="nav-item <?php echo $currentPage == 'content.php' ? 'active' : ''; ?>">Content</a>
        <a href="comments.php" class="nav-item <?php echo $currentPage == 'comments.php' ? 'active' : ''; ?>">Comments</a>
        <a href="contact_submissions.php" class="nav-item <?php echo $currentPage == 'contact_submissions.php' ? 'active' : ''; ?>">Contact Submissions</a>
        <a href="team_positions.php" class="nav-item <?php echo $currentPage == 'team_positions.php' ? 'active' : ''; ?>">Team Positions</a>
        <a href="job_applications.php" class="nav-item <?php echo $currentPage == 'job_applications.php' ? 'active' : ''; ?>">Job Applications</a>
        <?php if (isAdmin()): ?>
            <a href="page_views.php" class="nav-item <?php echo $currentPage == 'page_views.php' ? 'active' : ''; ?>">Page Views</a>
            <a href="database.php" class="nav-item <?php echo $currentPage == 'database.php' ? 'active' : ''; ?>">Database</a>
            <a href="users.php" class="nav-item <?php echo $currentPage == 'users.php' ? 'active' : ''; ?>">Users</a>
        <?php endif; ?>
    </div>

    <div class="main-content">
        <div class="header">
            <h1><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../includes/auth.php?logout=1" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>
