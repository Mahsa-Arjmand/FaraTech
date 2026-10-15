<?php
$pageTitle = 'Team Positions';
require_once 'layout.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$db = getDBConnection();

if ($action === 'create' || $action === 'edit') {
    $position = null;
    if ($action === 'edit' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM team_positions WHERE id = ?");
        $stmt->execute([$id]);
        $position = $stmt->fetch();
        if (!$position) {
            $_SESSION['flash_error'] = 'Position not found';
            redirect('team_positions.php');
        }
    }
    ?>
    <div class="card">
        <h2><?php echo $action === 'create' ? 'Create New Position' : 'Edit Position'; ?></h2>
        <form method="POST" action="team_positions_handler.php">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id" value="<?php echo $position['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="name">Position Name *</label>
                <input type="text" id="name" name="name" required
                       value="<?php echo $position ? htmlspecialchars($position['name']) : ''; ?>"
                       placeholder="e.g., Senior Frontend Developer">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          placeholder="Brief description of the position"><?php echo $position ? htmlspecialchars($position['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="display_order">Display Order</label>
                <input type="number" id="display_order" name="display_order"
                       value="<?php echo $position ? (int)$position['display_order'] : 0; ?>"
                       placeholder="Lower numbers appear first">
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $action === 'create' ? 'Create Position' : 'Update Position'; ?>
            </button>
            <a href="team_positions.php" class="btn" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

if ($action === 'delete' && $id > 0) {
    $stmt = $db->prepare("DELETE FROM team_positions WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_success'] = 'Position deleted successfully';
    redirect('team_positions.php');
}

// List positions
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

$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get positions
$stmt = $db->prepare("SELECT * FROM team_positions WHERE $whereClause ORDER BY display_order ASC, created_at ASC LIMIT ? OFFSET ?");
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$positions = $stmt->fetchAll();
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Team Positions</h2>
        <a href="team_positions.php?action=create" class="btn btn-primary">Add New Position</a>
    </div>

    <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="text" name="search" placeholder="Search positions..."
               value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if (empty($positions)): ?>
        <p>No positions found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($positions as $position): ?>
                    <tr>
                        <td><?php echo (int)$position['display_order']; ?></td>
                        <td><?php echo htmlspecialchars($position['name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($position['description'] ?? '', 0, 50)) . (strlen($position['description'] ?? '') > 50 ? '...' : ''); ?></td>
                        <td><?php echo date('M j, Y', strtotime($position['created_at'])); ?></td>
                        <td>
                            <div class="actions">
                                <a href="team_positions.php?action=edit&id=<?php echo $position['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="team_positions.php?action=delete&id=<?php echo $position['id']; ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this position?');">Delete</a>
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
