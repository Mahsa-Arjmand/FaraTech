<?php
require_once 'config/database.php';

try {
    $db = getDBConnection();

    // Delete existing sample projects
    $db->exec("DELETE FROM projects");

    // Real projects from portfolio.html
    $realProjects = [
        [
            'title' => 'Aram Fashion Store',
            'description' => 'Full redesign focused on a smooth shopping experience and higher conversion rate.',
            'client' => 'Aram Fashion',
            'category' => 'E-Commerce',
            'technologies' => 'PHP, Laravel, MySQL, Vue.js, Stripe',
            'image_url' => '',
            'project_url' => '',
            'featured' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'Safarin Travel Agency',
            'description' => 'Tour showcase and online booking with a custom content management system.',
            'client' => 'Safarin Travel',
            'category' => 'Corporate',
            'technologies' => 'React, Node.js, MongoDB, Express',
            'image_url' => '',
            'project_url' => '',
            'featured' => 0,
            'status' => 'published'
        ],
        [
            'title' => 'Zarrin Finance Platform',
            'description' => 'Financial dashboard with a clean interface and real-time reports.',
            'client' => 'Zarrin Finance',
            'category' => 'Web App',
            'technologies' => 'Python, Django, PostgreSQL, Chart.js',
            'image_url' => '',
            'project_url' => '',
            'featured' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'Roya Beauty Clinic',
            'description' => 'Website with online appointment booking and a full showcase gallery.',
            'client' => 'Roya Beauty Clinic',
            'category' => 'Website',
            'technologies' => 'WordPress, PHP, MySQL, jQuery',
            'image_url' => '',
            'project_url' => '',
            'featured' => 0,
            'status' => 'published'
        ],
        [
            'title' => 'Danesh Online Academy',
            'description' => 'Learning platform with instructor dashboards and HD video streaming.',
            'client' => 'Danesh Academy',
            'category' => 'Platform',
            'technologies' => 'PHP, Laravel, AWS S3, Video.js',
            'image_url' => '',
            'project_url' => '',
            'featured' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'Chashni Restaurant',
            'description' => 'Online food ordering with delivery system and order management panel.',
            'client' => 'Chashni Restaurant',
            'category' => 'E-Commerce',
            'technologies' => 'React Native, Node.js, MongoDB, Socket.io',
            'image_url' => '',
            'project_url' => '',
            'featured' => 0,
            'status' => 'published'
        ],
        [
            'title' => 'SupportMind AI',
            'description' => 'AI-powered customer support chatbot trained on a company\'s knowledge base.',
            'client' => 'SupportMind',
            'category' => 'AI Product',
            'technologies' => 'Python, TensorFlow, NLP, Flask',
            'image_url' => '',
            'project_url' => '',
            'featured' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'TeamFlow Dashboard',
            'description' => 'Project management SaaS with team collaboration and reporting tools.',
            'client' => 'TeamFlow',
            'category' => 'SaaS',
            'technologies' => 'Vue.js, Node.js, PostgreSQL, Socket.io',
            'image_url' => '',
            'project_url' => '',
            'featured' => 0,
            'status' => 'published'
        ],
    ];

    // Insert real projects
    $stmt = $db->prepare("INSERT INTO projects (title, description, client, category, technologies, image_url, project_url, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($realProjects as $project) {
        $stmt->execute([
            $project['title'],
            $project['description'],
            $project['client'],
            $project['category'],
            $project['technologies'],
            $project['image_url'],
            $project['project_url'],
            $project['featured'],
            $project['status']
        ]);
        echo "Added project: {$project['title']}\n";
    }

    echo "\nReal projects synced successfully!\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
