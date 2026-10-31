<?php
/** Dynamic XML sitemap including published portfolio case studies. */
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
require_once __DIR__ . '/../config/database.php';

$baseUrl = rtrim(getenv('SITE_URL') ?: 'https://faratechlab.ir', '/');
$today = gmdate('Y-m-d');
$urls = [];

try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $stmt = $db->query("SELECT id, updated_at FROM projects WHERE status = 'published' ORDER BY updated_at DESC");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $project) {
        $updatedAt = !empty($project['updated_at']) ? strtotime($project['updated_at']) : false;
        $urls[] = [
            'loc' => $baseUrl . '/project-detail.html?id=' . rawurlencode((string)$project['id']),
            'lastmod' => $updatedAt ? gmdate('Y-m-d', $updatedAt) : $today,
            'changefreq' => 'monthly',
            'priority' => '0.7',
        ];
    }
} catch (Throwable $e) {
    error_log('Dynamic sitemap could not read portfolio projects: ' . $e->getMessage());
}

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $item) {
    $loc = htmlspecialchars($item['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
    $xml .= "  <url>\n";
    $xml .= '    <loc>' . $loc . "</loc>\n";
    $xml .= '    <lastmod>' . $item['lastmod'] . "</lastmod>\n";
    $xml .= '    <changefreq>' . $item['changefreq'] . "</changefreq>\n";
    $xml .= '    <priority>' . $item['priority'] . "</priority>\n";
    $xml .= "  </url>\n";
}
$xml .= "</urlset>\n";
echo $xml;
