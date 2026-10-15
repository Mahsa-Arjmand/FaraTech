<?php
$pageTitle = 'Content Management';
require_once 'layout.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$db = getDBConnection();

if ($action === 'create' || $action === 'edit') {
    $content = null;
    if ($action === 'edit' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM content WHERE id = ?");
        $stmt->execute([$id]);
        $content = $stmt->fetch();
        if (!$content) {
            $_SESSION['flash_error'] = 'Content not found';
            redirect('content.php');
        }
    }
    ?>
    <div class="card">
        <h2><?php echo $action === 'create' ? 'Create New Content' : 'Edit Content'; ?></h2>
        <form method="POST" action="content_handler.php">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="page">Page *</label>
                <select id="page" name="page" required>
                    <option value="">Select Page</option>
                    <option value="index" <?php echo ($content && $content['page'] === 'index') ? 'selected' : ''; ?>>Home</option>
                    <option value="about" <?php echo ($content && $content['page'] === 'about') ? 'selected' : ''; ?>>About</option>
                    <option value="services" <?php echo ($content && $content['page'] === 'services') ? 'selected' : ''; ?>>Services</option>
                    <option value="portfolio" <?php echo ($content && $content['page'] === 'portfolio') ? 'selected' : ''; ?>>Portfolio</option>
                    <option value="team" <?php echo ($content && $content['page'] === 'team') ? 'selected' : ''; ?>>Team</option>
                    <option value="contact" <?php echo ($content && $content['page'] === 'contact') ? 'selected' : ''; ?>>Contact</option>
                    <option value="ai-lab" <?php echo ($content && $content['page'] === 'ai-lab') ? 'selected' : ''; ?>>AI Lab</option>
                </select>
            </div>

            <div class="form-group">
                <label for="section">Section *</label>
                <input type="text" id="section" name="section" required
                       value="<?php echo $content ? htmlspecialchars($content['section']) : ''; ?>"
                       placeholder="e.g., hero, about-us, services">
            </div>

            <div class="form-group">
                <label for="content_key">Key *</label>
                <input type="text" id="content_key" name="content_key" required
                       value="<?php echo $content ? htmlspecialchars($content['content_key']) : ''; ?>"
                       placeholder="e.g., title, description, subtitle">
            </div>

            <div class="form-group">
                <label for="content_value">Value</label>
                <textarea id="content_value" name="content_value"
                          placeholder="Content value (HTML allowed)"><?php echo $content ? htmlspecialchars($content['content_value']) : ''; ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $action === 'create' ? 'Create Content' : 'Update Content'; ?>
            </button>
            <a href="content.php" class="btn" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

if ($action === 'delete' && $id > 0) {
    $stmt = $db->prepare("DELETE FROM content WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_success'] = 'Content deleted successfully';
    redirect('content.php');
}

// List content
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
$pageFilter = isset($_GET['page_filter']) ? cleanInput($_GET['page_filter']) : '';

$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(content_key LIKE ? OR content_value LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($pageFilter) {
    $where[] = "page = ?";
    $params[] = $pageFilter;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM content WHERE $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get content
$stmt = $db->prepare("SELECT * FROM content WHERE $whereClause ORDER BY page, section, content_key LIMIT ? OFFSET ?");
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$contentItems = $stmt->fetchAll();
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Content Management</h2>
        <a href="content.php?action=create" class="btn btn-primary">Add New Content</a>
    </div>

    <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="text" name="search" placeholder="Search content..."
               value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
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
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if (empty($contentItems)): ?>
        <p>No content found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Section</th>
                    <th>Key</th>
                    <th>Value Preview</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contentItems as $item): ?>
                    <tr>
                        <td><?php echo ucfirst(htmlspecialchars($item['page'])); ?></td>
                        <td><?php echo htmlspecialchars($item['section']); ?></td>
                        <td><?php echo htmlspecialchars($item['content_key']); ?></td>
                        <td><?php echo htmlspecialchars(substr($item['content_value'], 0, 50)) . (strlen($item['content_value']) > 50 ? '...' : ''); ?></td>
                        <td><?php echo date('M j, Y', strtotime($item['updated_at'])); ?></td>
                        <td>
                            <div class="actions">
                                <a href="content.php?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="content.php?action=delete&id=<?php echo $item['id']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this content?');">Delete</a>
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
