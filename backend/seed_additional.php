<?php
require_once 'config/database.php';

try {
    $db = getDBConnection();

    // Sample team positions
    $positions = [
        ['name' => 'Founder & CEO', 'description' => 'Leads vision, client partnerships and long-term growth', 'display_order' => 1],
        ['name' => 'Head of Design', 'description' => 'Design lead specialized in design systems, user research and scalable product design', 'display_order' => 2],
        ['name' => 'CTO', 'description' => 'Full-stack architect. Oversees engineering, infrastructure and technical quality', 'display_order' => 3],
        ['name' => 'Head of AI Lab', 'description' => 'ML engineer and researcher. Leads applied AI projects and model development', 'display_order' => 4],
        ['name' => 'Senior Frontend Engineer', 'description' => 'Expert in modern frontend technologies and frameworks', 'display_order' => 5],
        ['name' => 'Product Designer', 'description' => 'Creates intuitive and beautiful user experiences', 'display_order' => 6],
        ['name' => 'Backend Engineer', 'description' => 'Builds robust and scalable backend systems', 'display_order' => 7],
        ['name' => 'SEO Specialist', 'description' => 'Optimizes websites for search engines and user experience', 'display_order' => 8],
        ['name' => 'UI/UX Designer', 'description' => 'Designs user interfaces and experiences', 'display_order' => 9],
        ['name' => 'Content Strategist', 'description' => 'Creates and manages content strategy', 'display_order' => 10],
        ['name' => 'DevOps Engineer', 'description' => 'Manages infrastructure and deployment', 'display_order' => 11],
        ['name' => 'ML Engineer', 'description' => 'Develops machine learning models and solutions', 'display_order' => 12],
    ];

    // Insert team positions
    $stmt = $db->prepare("INSERT INTO team_positions (name, description, display_order) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), display_order = VALUES(display_order)");

    foreach ($positions as $pos) {
        $stmt->execute([$pos['name'], $pos['description'], $pos['display_order']]);
        echo "Added position: {$pos['name']}\n";
    }

    // Sample comments
    $comments = [
        ['page' => 'index', 'author_name' => 'Akram Akbari', 'author_email' => '', 'comment_text' => 'Great website! Very professional and clean design.', 'status' => 'approved'],
        ['page' => 'index', 'author_name' => 'Mahdeh', 'author_email' => '', 'comment_text' => 'Love the portfolio section. Would love to work with you guys!', 'status' => 'approved'],
        ['page' => 'index', 'author_name' => 'Hedieh', 'author_email' => '', 'comment_text' => 'Impressive team and vision. Keep up the good work!', 'status' => 'approved'],
        ['page' => 'services', 'author_name' => 'Sarah Williams', 'author_email' => 'sarah@example.com', 'comment_text' => 'Do you offer mobile app development as well?', 'status' => 'pending'],
        ['page' => 'portfolio', 'author_name' => 'David Brown', 'author_email' => 'david@example.com', 'comment_text' => 'Amazing projects! The AI Lab work is particularly interesting.', 'status' => 'approved'],
        ['page' => 'contact', 'author_name' => 'Emily Davis', 'author_email' => 'emily@example.com', 'comment_text' => 'Spam comment - delete this', 'status' => 'rejected'],
        ['page' => 'team', 'author_name' => 'Robert Wilson', 'author_email' => 'robert@example.com', 'comment_text' => 'Looking for job opportunities. Great company culture!', 'status' => 'pending'],
    ];

    // Insert comments
    $stmt = $db->prepare("INSERT INTO comments (page, author_name, author_email, comment_text, status, ip_address) VALUES (?, ?, ?, ?, ?, '127.0.0.1')");

    foreach ($comments as $comment) {
        $stmt->execute([$comment['page'], $comment['author_name'], $comment['author_email'], $comment['comment_text'], $comment['status']]);
        echo "Added comment from {$comment['author_name']} ({$comment['status']})\n";
    }

    // Page-view analytics are intentionally not seeded; the public tracker records real traffic.

    // Sample projects
    $projects = [
        [
            'title' => 'E-Commerce Platform',
            'description' => 'A full-featured e-commerce platform with payment integration, inventory management, and analytics dashboard.',
            'client' => 'TechRetail Inc.',
            'category' => 'Web Development',
            'technologies' => 'PHP, Laravel, MySQL, Vue.js, Stripe',
            'image_url' => '',
            'project_url' => '',
            'featured' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'AI-Powered Analytics Tool',
            'description' => 'Machine learning platform that provides predictive analytics and business intelligence for enterprise clients.',
            'client' => 'DataCorp',
            'category' => 'AI/ML',
            'technologies' => 'Python, TensorFlow, React, PostgreSQL',
            'image_url' => '',
            'project_url' => '',
            'featured' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'Mobile Banking App',
            'description' => 'Secure mobile banking application with biometric authentication, real-time transactions, and budget tracking.',
            'client' => 'FinBank',
            'category' => 'Mobile Development',
            'technologies' => 'React Native, Node.js, MongoDB',
            'image_url' => '',
            'project_url' => '',
            'featured' => 0,
            'status' => 'published'
        ],
        [
            'title' => 'Healthcare Management System',
            'description' => 'Comprehensive healthcare platform for hospitals with patient management, appointment scheduling, and telemedicine features.',
            'client' => 'MedCare Group',
            'category' => 'Web Development',
            'technologies' => 'Django, PostgreSQL, React, AWS',
            'image_url' => '',
            'project_url' => '',
            'featured' => 0,
            'status' => 'published'
        ],
    ];

    // Insert projects
    $stmt = $db->prepare("INSERT INTO projects (title, description, client, category, technologies, image_url, project_url, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($projects as $project) {
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

    echo "\nAll sample data added successfully!\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
