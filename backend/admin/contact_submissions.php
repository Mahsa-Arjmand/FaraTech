<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../includes/functions.php';

$db = getDBConnection();

// Handle status update
if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'] ?? 'new';

    $stmt = $db->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    $_SESSION['flash_success'] = 'Status updated successfully';
    redirect('contact_submissions.php');
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $db->prepare("DELETE FROM contact_submissions WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['flash_success'] = 'Submission deleted successfully';
    redirect('contact_submissions.php');
}

$pageTitle = 'Contact Submissions';
require_once 'layout.php';

// Get submissions
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? cleanInput($_GET['status']) : '';

$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(name LIKE ? OR email LIKE ? OR message LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM contact_submissions WHERE $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

// Get submissions with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$stmt = $db->prepare("SELECT * FROM contact_submissions WHERE $whereClause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
$stmt->execute($params);
$submissions = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2>Contact Submissions</h2>
        <div class="card-actions">
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </div>

    <div class="card-body">
        <!-- Filters -->
        <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
            <div class="form-group" style="margin-bottom: 0; flex: 1;">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                    <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                    <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="contact_submissions.php" class="btn">Clear</a>
        </form>

        <!-- Submissions Table -->
        <?php if (empty($submissions)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">No submissions found.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f5f5f5;">
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Date</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Name</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Email</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Topic</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $sub): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px;"><?php echo date('M j, Y', strtotime($sub['created_at'])); ?></td>
                            <td style="padding: 12px;">
                                <strong><?php echo htmlspecialchars($sub['name']); ?></strong>
                                <?php if ($sub['phone']): ?>
                                    <br><small><?php echo htmlspecialchars($sub['phone']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px;">
                                <a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>"><?php echo htmlspecialchars($sub['email']); ?></a>
                            </td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($sub['topic'] ?: '-'); ?></td>
                            <td style="padding: 12px;">
                                <select onchange="window.location.href='contact_submissions.php?action=update_status&id=<?php echo $sub['id']; ?>&status='+this.value">
                                    <option value="new" <?php echo $sub['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="read" <?php echo $sub['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                    <option value="replied" <?php echo $sub['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                    <option value="archived" <?php echo $sub['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                </select>
                            </td>
                            <td style="padding: 12px;">
                                <button onclick="showMessage(<?php echo $sub['id']; ?>)" class="btn btn-sm">View</button>
                                <a href="contact_submissions.php?action=delete&id=<?php echo $sub['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total > $per_page): ?>
                <div style="margin-top: 20px; text-align: center;">
                    <?php
                    $total_pages = ceil($total / $per_page);
                    for ($i = 1; $i <= $total_pages; $i++):
                        $params_str = http_build_query(array_merge($_GET, ['page' => $i]));
                    ?>
                        <a href="contact_submissions.php?<?php echo $params_str; ?>" class="btn <?php echo $i === $page ? 'btn-primary' : ''; ?>" style="margin: 0 5px;"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Message Modal -->
<div id="messageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: relative; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <button onclick="closeModal()" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        <h3 id="modalTitle" style="margin-bottom: 15px;">Message</h3>
        <div id="modalContent"></div>
    </div>
</div>

<script>
const submissions = <?php echo json_encode($submissions); ?>;

function showMessage(id) {
    const sub = submissions.find(s => s.id === id);
    if (!sub) return;

    document.getElementById('modalTitle').textContent = 'Message from ' + sub.name;
    document.getElementById('modalContent').innerHTML = `
        <p><strong>Email:</strong> ${sub.email}</p>
        ${sub.phone ? `<p><strong>Phone:</strong> ${sub.phone}</p>` : ''}
        ${sub.topic ? `<p><strong>Topic:</strong> ${sub.topic}</p>` : ''}
        <p><strong>Date:</strong> ${new Date(sub.created_at).toLocaleString()}</p>
        <hr style="margin: 15px 0;">
        <p style="white-space: pre-wrap;">${sub.message}</p>
    `;
    document.getElementById('messageModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('messageModal').style.display = 'none';
}

// Close modal on outside click
document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
