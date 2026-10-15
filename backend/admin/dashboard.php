<?php
$pageTitle = 'Dashboard';
require_once 'layout.php';

$db = getDBConnection();

// Get stats
$projectCount = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$contentCount = $db->query("SELECT COUNT(*) FROM content")->fetchColumn();
$publishedProjects = $db->query("SELECT COUNT(*) FROM projects WHERE status = 'published'")->fetchColumn();
$commentCount = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$pendingComments = $db->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn();
$positionCount = $db->query("SELECT COUNT(*) FROM team_positions")->fetchColumn();
$totalViews = $db->query("SELECT SUM(views_count) FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn() ?: 0;
$jobApplicationCount = $db->query("SELECT COUNT(*) FROM job_applications")->fetchColumn();
$pendingApplications = $db->query("SELECT COUNT(*) FROM job_applications WHERE status = 'pending'")->fetchColumn();

// Get recent projects
$recentProjects = $db->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Projects</h3>
        <div class="value"><?php echo $projectCount; ?></div>
    </div>
    <div class="stat-card">
        <h3>Published Projects</h3>
        <div class="value"><?php echo $publishedProjects; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Users</h3>
        <div class="value"><?php echo $userCount; ?></div>
    </div>
    <div class="stat-card">
        <h3>Content Items</h3>
        <div class="value"><?php echo $contentCount; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Comments</h3>
        <div class="value"><?php echo $commentCount; ?></div>
    </div>
    <div class="stat-card">
        <h3>Pending Comments</h3>
        <div class="value"><?php echo $pendingComments; ?></div>
    </div>
    <div class="stat-card">
        <h3>Team Positions</h3>
        <div class="value"><?php echo $positionCount; ?></div>
    </div>
    <div class="stat-card">
        <h3>Job Applications</h3>
        <div class="value"><?php echo $jobApplicationCount; ?></div>
    </div>
    <div class="stat-card">
        <h3>Pending Applications</h3>
        <div class="value"><?php echo $pendingApplications; ?></div>
    </div>
    <div class="stat-card">
        <h3>Page Views (30d)</h3>
        <div class="value"><?php echo number_format($totalViews); ?></div>
    </div>
</div>

<div class="card">
    <h2>Recent Projects</h2>
    <?php if (empty($recentProjects)): ?>
        <p>No projects yet. <a href="projects.php">Create your first project</a>.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Client</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentProjects as $project): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($project['title']); ?></td>
                        <td><?php echo htmlspecialchars($project['client'] ?? '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $project['status']; ?>">
                                <?php echo ucfirst($project['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($project['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Quick Actions</h2>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="projects.php?action=create" class="btn btn-primary">Add New Project</a>
        <a href="content.php" class="btn btn-primary">Manage Content</a>
        <a href="comments.php" class="btn btn-primary">Review Comments</a>
        <a href="team_positions.php?action=create" class="btn btn-primary">Add Team Position</a>
        <?php if (isAdmin()): ?>
            <a href="users.php?action=create" class="btn btn-primary">Add New User</a>
            <a href="page_views.php" class="btn btn-primary">View Analytics</a>
        <?php endif; ?>
    </div>
</div>

</div>
</body>
</html>
