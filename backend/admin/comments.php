<?php
$pageTitle = 'Comments Management';
require_once 'layout.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$db = getDBConnection();

if ($action === 'approve' && $id > 0) {
    $stmt = $db->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_success'] = 'Comment approved successfully';
    redirect('comments.php');
}

if ($action === 'reject' && $id > 0) {
    $stmt = $db->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_success'] = 'Comment rejected successfully';
    redirect('comments.php');
}

if ($action === 'delete' && $id > 0) {
    $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_success'] = 'Comment deleted successfully';
    redirect('comments.php');
}

// List comments
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$status = isset($_GET['status']) ? cleanInput($_GET['status']) : '';
$pageFilter = isset($_GET['page_filter']) ? cleanInput($_GET['page_filter']) : '';

$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(author_name LIKE ? OR comment_text LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}

if ($pageFilter) {
    $where[] = "page = ?";
    $params[] = $pageFilter;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get comments
$stmt = $db->prepare("SELECT * FROM comments WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$comments = $stmt->fetchAll();
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Comments Management</h2>
    </div>

    <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
        <input type="text" name="search" placeholder="Search comments..."
               value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        <select name="status" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            <option value="">All Status</option>
            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
            <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
        </select>
        <select name="page_filter" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            <option value="">All Pages</option>
            <option value="index" <?php echo $pageFilter === 'index' ? 'selected' : ''; ?>>Home</option>
            <option value="about" <?php echo $pageFilter === 'about' ? 'selected' : ''; ?>>About</option>
            <option value="services" <?php echo $pageFilter === 'services' ? 'selected' : ''; ?>>Services</option>
            <option value="portfolio" <?php echo $pageFilter === 'portfolio' ? 'selected' : ''; ?>>Portfolio</option>
            <option value="team" <?php echo $pageFilter === 'team' ? 'selected' : ''; ?>>Team</option>
            <option value="contact" <?php echo $pageFilter === 'contact' ? 'selected' : ''; ?>>Contact</option>
            <option value="ai-lab" <?php echo $pageFilter === 'ai-lab' ? 'selected' : ''; ?>>AI Lab</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <?php if (empty($comments)): ?>
        <p>No comments found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Author</th>
                    <th>Comment</th>
                    <th>Page</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong>
                            <?php if ($comment['author_email']): ?>
                                <br><small><?php echo htmlspecialchars($comment['author_email']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars(substr($comment['comment_text'], 0, 100)) . (strlen($comment['comment_text']) > 100 ? '...' : ''); ?></td>
                        <td><?php echo ucfirst(htmlspecialchars($comment['page'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $comment['status']; ?>">
                                <?php echo ucfirst($comment['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y H:i', strtotime($comment['created_at'])); ?></td>
                        <td>
                            <div class="actions">
                                <?php if ($comment['status'] !== 'approved'): ?>
                                    <a href="comments.php?action=approve&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                <?php endif; ?>
                                <?php if ($comment['status'] !== 'rejected'): ?>
                                    <a href="comments.php?action=reject&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-danger">Reject</a>
                                <?php endif; ?>
                                <a href="comments.php?action=delete&id=<?php echo $comment['id']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this comment?');">Delete</a>
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
