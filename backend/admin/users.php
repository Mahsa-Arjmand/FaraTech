<?php
$pageTitle = 'User Management';
require_once 'layout.php';

requireAdmin();

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$db = getDBConnection();

if ($action === 'create' || $action === 'edit') {
    $user = null;
    if ($action === 'edit' && $id > 0) {
        $stmt = $db->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found';
            redirect('users.php');
        }
    }
    ?>
    <div class="card">
        <h2><?php echo $action === 'create' ? 'Create New User' : 'Edit User'; ?></h2>
        <form method="POST" action="users_handler.php">
            <?php if ($action === 'edit'): ?>
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" required
                       value="<?php echo $user ? htmlspecialchars($user['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo $user ? htmlspecialchars($user['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password <?php echo $action === 'edit' ? '(leave blank to keep current)' : '*'; ?></label>
                <input type="password" id="password" name="password"
                       <?php echo $action === 'create' ? 'required' : ''; ?>>
            </div>

            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <option value="editor" <?php echo (!$user || $user['role'] === 'editor') ? 'selected' : ''; ?>>Editor</option>
                    <option value="admin" <?php echo ($user && $user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <?php echo $action === 'create' ? 'Create User' : 'Update User'; ?>
            </button>
            <a href="users.php" class="btn" style="margin-left: 10px;">Cancel</a>
        </form>
    </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

if ($action === 'delete' && $id > 0) {
    // Prevent deleting yourself
    if ($id === $_SESSION['user_id']) {
        $_SESSION['flash_error'] = 'Cannot delete your own account';
        redirect('users.php');
    }

    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_success'] = 'User deleted successfully';
    redirect('users.php');
}

// List users
$search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';

$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(username LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = implode(' AND ', $where);

// Get total count
$countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get users (without password)
$stmt = $db->prepare("SELECT id, username, email, role, created_at FROM users WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Users</h2>
        <a href="users.php?action=create" class="btn btn-primary">Add New User</a>
    </div>

    <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px;">
        <input type="text" name="search" placeholder="Search users..."
               value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if (empty($users)): ?>
        <p>No users found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge" style="background: <?php echo $user['role'] === 'admin' ? '#3498db' : '#95a5a6'; ?>; color: white;">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="actions">
                                <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <a href="users.php?action=delete&id=<?php echo $user['id']; ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                                <?php endif; ?>
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
