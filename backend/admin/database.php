<?php
$pageTitle = 'Database Viewer';
require_once 'layout.php';

requireAdmin();

$table = isset($_GET['table']) ? cleanInput($_GET['table']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$db = getDBConnection();

// Get all tables
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

if ($table) {
    // Get table structure
    $structure = $db->query("DESCRIBE `$table`")->fetchAll();

    // Get row count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM `$table`");
    $countStmt->execute();
    $rowCount = $countStmt->fetchColumn();

    // Get table data
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    $dataStmt = $db->prepare("SELECT * FROM `$table` LIMIT ? OFFSET ?");
    $dataStmt->execute([$perPage, $offset]);
    $data = $dataStmt->fetchAll();

    $totalPages = ceil($rowCount / $perPage);
}
?>

<div class="card">
    <h2>Database Tables</h2>

    <div style="margin-bottom: 20px;">
        <select id="tableSelect" onchange="window.location.href='?table=' + this.value" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-width: 200px;">
            <option value="">Select a table</option>
            <?php foreach ($tables as $t): ?>
                <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $table === $t ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($t); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($table): ?>
        <div style="margin-bottom: 20px;">
            <strong>Table:</strong> <?php echo htmlspecialchars($table); ?> |
            <strong>Rows:</strong> <?php echo $rowCount; ?> |
            <strong>Columns:</strong> <?php echo count($structure); ?>
        </div>

        <h3>Structure</h3>
        <table style="margin-bottom: 30px;">
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Type</th>
                    <th>Null</th>
                    <th>Key</th>
                    <th>Default</th>
                    <th>Extra</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($structure as $col): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($col['Field']); ?></td>
                        <td><?php echo htmlspecialchars($col['Type']); ?></td>
                        <td><?php echo htmlspecialchars($col['Null']); ?></td>
                        <td><?php echo htmlspecialchars($col['Key']); ?></td>
                        <td><?php echo htmlspecialchars($col['Default'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($col['Extra']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Data</h3>
        <?php if (empty($data)): ?>
            <p>No data in this table.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <?php foreach ($structure as $col): ?>
                            <th><?php echo htmlspecialchars($col['Field']); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($structure as $col): ?>
                                <td><?php echo htmlspecialchars($row[$col['Field']] ?? ''); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?table=<?php echo htmlspecialchars($table); ?>&page=<?php echo $i; ?>"
                           class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <p>Select a table to view its data and structure.</p>
    <?php endif; ?>
</div>

</div>
</body>
</html>
