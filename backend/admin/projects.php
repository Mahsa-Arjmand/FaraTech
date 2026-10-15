<?php
$pageTitle = 'Projects';
require_once 'layout.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$db = getDBConnection();

if ($action === 'create' || $action === 'edit') {
    $project = null;
    if ($action === 'edit' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        if (!$project) {
            $_SESSION['flash_error'] = 'Project not found';
            redirect('projects.php');
        }
    }
    ?>
    <div class="card">
        <h2><?php echo $action === 'create' ? 'Create New Project' : 'Edit Project'; ?></h2>
        <form method="POST" action="projects_handler.php" enctype="multipart/form-data">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required
                       value="<?php echo $project ? htmlspecialchars($project['title']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo $project ? htmlspecialchars($project['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="client">Client</label>
                <input type="text" id="client" name="client"
                       value="<?php echo $project ? htmlspecialchars($project['client']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category"
                       value="<?php echo $project ? htmlspecialchars($project['category']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="technologies">Technologies (comma-separated)</label>
                <input type="text" id="technologies" name="technologies"
                       value="<?php echo $project ? htmlspecialchars($project['technologies']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="image_url">Image URL</label>
                <input type="text" id="image_url" name="image_url"
                       value="<?php echo $project ? htmlspecialchars($project['image_url']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="project_url">Project URL</label>
                <input type="text" id="project_url" name="project_url"
                       value="<?php echo $project ? htmlspecialchars($project['project_url']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" <?php echo $project && $project['featured'] ? 'checked' : ''; ?>>
                    Featured Project
                </label>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="draft" <?php echo (!$project || $project['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo ($project && $project['status'] === 'published') ? 'selected' : ''; ?>>Published</option>
                    <option value="archived" <?php echo ($project && $project['status'] === 'archived') ? 'selected' : ''; ?>>Archived</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $action === 'create' ? 'Create Project' : 'Update Project'; ?>
            </button>
            <a href="projects.php" class="btn" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

if ($action === 'delete' && $id > 0) {
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_success'] = 'Project deleted successfully';
    redirect('projects.php');
}

// List projects
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

$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get projects
$stmt = $db->prepare("SELECT * FROM projects WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$projects = $stmt->fetchAll();
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Projects</h2>
        <a href="projects.php?action=create" class="btn btn-primary">Add New Project</a>
    </div>

    <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="text" name="search" placeholder="Search projects..."
               value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        <select name="status" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
            <option value="">All Status</option>
            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
            <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>Archived</option>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if (empty($projects)): ?>
        <p>No projects found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Client</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($project['title']); ?></td>
                        <td><?php echo htmlspecialchars($project['client'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($project['category'] ?? '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $project['status']; ?>">
                                <?php echo ucfirst($project['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($project['created_at'])); ?></td>
                        <td>
                            <div class="actions">
                                <a href="projects.php?action=edit&id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="projects.php?action=delete&id=<?php echo $project['id']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this project?');">Delete</a>
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
