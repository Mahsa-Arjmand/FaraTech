<?php
$pageTitle = 'Page Views Analytics';
require_once 'layout.php';

requireAdmin();

$db = getDBConnection();

// Get statistics
$days = isset($_GET['days']) ? (int)$_GET['days'] : 30;

// Get total views by page
$stmt = $db->query("SELECT page, SUM(views_count) as total_views FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY) GROUP BY page ORDER BY total_views DESC");
$byPage = $stmt->fetchAll();

// Get daily views
$stmt = $db->query("SELECT view_date, SUM(views_count) as daily_views FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY) GROUP BY view_date ORDER BY view_date DESC");
$daily = $stmt->fetchAll();

// Get total views
$stmt = $db->query("SELECT SUM(views_count) as total FROM page_views WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL $days DAY)");
$totalViews = $stmt->fetchColumn() ?: 0;

// Get today's views
$stmt = $db->query("SELECT SUM(views_count) as total FROM page_views WHERE view_date = CURDATE()");
$todayViews = $stmt->fetchColumn() ?: 0;

// Filter parameters
$pageFilter = isset($_GET['page_filter']) ? cleanInput($_GET['page_filter']) : '';
$startDate = isset($_GET['start_date']) ? cleanInput($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? cleanInput($_GET['end_date']) : '';
$listPage = isset($_GET['list_page']) ? (int)$_GET['list_page'] : 1;

$where = ['1=1'];
$params = [];

if ($pageFilter) {
    $where[] = "page = ?";
    $params[] = $pageFilter;
}

if ($startDate) {
    $where[] = "view_date >= ?";
    $params[] = $startDate;
}

if ($endDate) {
    $where[] = "view_date <= ?";
    $params[] = $endDate;
}

$whereClause = implode(' AND ', $where);

// Get detailed views list
$countStmt = $db->prepare("SELECT COUNT(*) FROM page_views WHERE $whereClause");
$countStmt->execute($params);
$totalList = $countStmt->fetchColumn();

$perPage = 20;
$offset = ($listPage - 1) * $perPage;

$stmt = $db->prepare("SELECT * FROM page_views WHERE $whereClause ORDER BY view_date DESC LIMIT ? OFFSET ?");
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$viewsList = $stmt->fetchAll();
?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Views (Last <?php echo $days; ?> Days)</h3>
        <div class="value"><?php echo number_format($totalViews); ?></div>
    </div>
    <div class="stat-card">
        <h3>Today's Views</h3>
        <div class="value"><?php echo number_format($todayViews); ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Pages</h3>
        <div class="value"><?php echo count($byPage); ?></div>
    </div>
    <div class="stat-card">
        <h3>Avg Daily Views</h3>
        <div class="value"><?php echo $days > 0 ? number_format(round($totalViews / $days, 1)) : 0; ?></div>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Views by Page (Last <?php echo $days; ?> Days)</h2>
        <select onchange="window.location.href='?days=' + this.value" style="padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
            <option value="7" <?php echo $days === 7 ? 'selected' : ''; ?>>Last 7 Days</option>
            <option value="30" <?php echo $days === 30 ? 'selected' : ''; ?>>Last 30 Days</option>
            <option value="90" <?php echo $days === 90 ? 'selected' : ''; ?>>Last 90 Days</option>
            <option value="365" <?php echo $days === 365 ? 'selected' : ''; ?>>Last Year</option>
        </select>
    </div>

    <?php if (empty($byPage)): ?>
        <p>No view data available.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Total Views</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($byPage as $item): ?>
                    <tr>
                        <td><?php echo ucfirst(htmlspecialchars($item['page'])); ?></td>
                        <td><?php echo number_format($item['total_views']); ?></td>
                        <td><?php echo $totalViews > 0 ? round(($item['total_views'] / $totalViews) * 100, 1) : 0; ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Daily Views (Last <?php echo $days; ?> Days)</h2>
    <?php if (empty($daily)): ?>
        <p>No daily view data available.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Views</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily as $item): ?>
                    <tr>
                        <td><?php echo date('M j, Y', strtotime($item['view_date'])); ?></td>
                        <td><?php echo number_format($item['daily_views']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Detailed View Log</h2>

    <form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
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
        <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <?php if (empty($viewsList)): ?>
        <p>No view records found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Date</th>
                    <th>Views</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($viewsList as $view): ?>
                    <tr>
                        <td><?php echo ucfirst(htmlspecialchars($view['page'])); ?></td>
                        <td><?php echo date('M j, Y', strtotime($view['view_date'])); ?></td>
                        <td><?php echo number_format($view['views_count']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalList > $perPage): ?>
            <div class="pagination">
                <?php
                $totalPages = ceil($totalList / $perPage);
                for ($i = 1; $i <= $totalPages; $i++):
                    $paramsStr = http_build_query(array_merge($_GET, ['list_page' => $i]));
                ?>
                    <a href="?<?php echo $paramsStr; ?>" class="<?php echo $i === $listPage ? 'active' : ''; ?>">
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
