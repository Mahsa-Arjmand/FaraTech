<?php
$pageTitle = 'Job Applications';
require_once 'layout.php';

requireLogin();

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$db = getDBConnection();

if ($action === 'view' && $id > 0) {
    $stmt = $db->prepare("SELECT ja.*, tp.name as position_name FROM job_applications ja LEFT JOIN team_positions tp ON ja.position_id = tp.id WHERE ja.id = ?");
    $stmt->execute([$id]);
    $application = $stmt->fetch();

    if (!$application) {
        $_SESSION['flash_error'] = 'Application not found';
        redirect('job_applications.php');
    }
    ?>
    <div class="card">
        <h2>Job Application Details</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <strong>Applicant Name:</strong><br>
                <?php echo htmlspecialchars($application['full_name']); ?>
            </div>
            <div>
                <strong>Email:</strong><br>
                <?php echo htmlspecialchars($application['email']); ?>
            </div>
            <div>
                <strong>Phone:</strong><br>
                <?php echo htmlspecialchars($application['phone'] ?? 'Not provided'); ?>
            </div>
            <div>
                <strong>Position:</strong><br>
                <?php echo htmlspecialchars($application['position_name'] ?? 'Not specified'); ?>
            </div>
            <div>
                <strong>Type:</strong><br>
                <?php if ($application['is_internship']): ?>
                    <span style="font-size:14px; color:#e5b94a; font-weight:600;">کارآموزی</span>
                <?php else: ?>
                    <span style="font-size:14px; color:#10b981; font-weight:600;">شغلی</span>
                <?php endif; ?>
            </div>
            <div>
                <strong>Status:</strong><br>
                <span class="badge badge-<?php echo $application['status']; ?>">
                    <?php echo ucfirst($application['status']); ?>
                </span>
            </div>
            <div>
                <strong>Applied On:</strong><br>
                <?php echo date('M j, Y H:i', strtotime($application['created_at'])); ?>
            </div>
        </div>

        <?php if ($application['cover_letter']): ?>
            <div style="margin-bottom: 20px;">
                <strong>Cover Letter:</strong>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; margin-top: 10px;">
                    <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 20px;">
            <strong>Links:</strong>
            <ul style="margin-top: 10px; padding-left: 20px;">
                <?php if ($application['resume_file']): ?>
                    <li><a href="<?php echo htmlspecialchars($application['resume_file']); ?>" target="_blank" download>Download Resume/CV</a></li>
                <?php elseif ($application['resume_url']): ?>
                    <li><a href="<?php echo htmlspecialchars($application['resume_url']); ?>" target="_blank">Resume/CV URL</a></li>
                <?php endif; ?>
                <?php if ($application['portfolio_file']): ?>
                    <li><a href="<?php echo htmlspecialchars($application['portfolio_file']); ?>" target="_blank" download>Download Portfolio</a></li>
                <?php elseif ($application['portfolio_url']): ?>
                    <li><a href="<?php echo htmlspecialchars($application['portfolio_url']); ?>" target="_blank">Portfolio URL</a></li>
                <?php endif; ?>
                <?php if ($application['linkedin_url']): ?>
                    <li><a href="<?php echo htmlspecialchars($application['linkedin_url']); ?>" target="_blank">LinkedIn</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div style="margin-bottom: 20px;">
            <strong>Update Status:</strong>
            <form method="GET" style="margin-top: 10px; display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                <select name="status" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                    <option value="pending" <?php echo $application['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="reviewed" <?php echo $application['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                    <option value="interview" <?php echo $application['status'] === 'interview' ? 'selected' : ''; ?>>Interview</option>
                    <option value="hired" <?php echo $application['status'] === 'hired' ? 'selected' : ''; ?>>Hired</option>
                    <option value="rejected" <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                <input type="text" name="notes" placeholder="Add notes..." value="<?php echo htmlspecialchars($application['notes'] ?? ''); ?>" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>

        <a href="job_applications.php" class="btn">Back to List</a>
        <a href="job_applications.php?action=delete&id=<?php echo $application['id']; ?>"
           class="btn btn-danger"
           style="margin-left: 10px;"
           onclick="return confirm('Are you sure you want to delete this application?');">Delete</a>
    </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

if ($action === 'update_status' && $id > 0) {
    $status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
    $notes = isset($_GET['notes']) ? cleanInput($_GET['notes']) : '';

    if (!in_array($status, ['pending', 'reviewed', 'interview', 'hired', 'rejected'])) {
        $_SESSION['flash_error'] = 'Invalid status';
        redirect('job_applications.php');
    }

    $stmt = $db->prepare("UPDATE job_applications SET status = ?, notes = ? WHERE id = ?");
    $stmt->execute([$status, $notes, $id]);
    $_SESSION['flash_success'] = 'Application status updated successfully';
    redirect('job_applications.php');
}

if ($action === 'delete' && $id > 0) {
    $stmt = $db->prepare("DELETE FROM job_applications WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_success'] = 'Application deleted successfully';
    redirect('job_applications.php');
}

// List applications
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

$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get applications with position name
$stmt = $db->prepare("SELECT ja.*, tp.name as position_name FROM job_applications ja LEFT JOIN team_positions tp ON ja.position_id = tp.id WHERE $whereClause ORDER BY ja.created_at DESC LIMIT ? OFFSET ?");
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Get all positions for filter
$positions = $db->query("SELECT * FROM team_positions ORDER BY display_order ASC")->fetchAll();
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Job Applications</h2>
    </div>

    <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
        <input type="text" name="search" placeholder="Search by name or email..."
               value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        <select name="status" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            <option value="">All Status</option>
            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="reviewed" <?php echo $status === 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
            <option value="interview" <?php echo $status === 'interview' ? 'selected' : ''; ?>>Interview</option>
            <option value="hired" <?php echo $status === 'hired' ? 'selected' : ''; ?>>Hired</option>
            <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select>
        <select name="position_id" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            <option value="">All Positions</option>
            <?php foreach ($positions as $pos): ?>
                <option value="<?php echo $pos['id']; ?>" <?php echo $positionId === $pos['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($pos['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <?php if (empty($applications)): ?>
        <p>No job applications found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Email</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Applied</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applications as $app): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($app['email']); ?></td>
                        <td><?php echo htmlspecialchars($app['position_name'] ?? 'Not specified'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $app['status']; ?>">
                                <?php echo ucfirst($app['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($app['created_at'])); ?></td>
                        <td>
                            <div class="actions">
                                <a href="job_applications.php?action=view&id=<?php echo $app['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                <a href="job_applications.php?action=delete&id=<?php echo $app['id']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this application?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total > $perPage): ?>
            <div class="pagination">
                <?php
                $totalPages = ceil($total / $perPage);
                for ($i = 1; $i <= $totalPages; $i++):
                    $paramsStr = http_build_query(array_merge($_GET, ['page' => $i]));
                ?>
                    <a href="?<?php echo $paramsStr; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

</div>
</body>
</html>
