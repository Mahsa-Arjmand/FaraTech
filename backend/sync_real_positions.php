<?php
require_once 'config/database.php';

try {
    $db = getDBConnection();

    // Delete existing sample positions
    $db->exec("DELETE FROM team_positions");

    // Real positions from team.html
    $realPositions = [
        ['name' => 'Founder & CEO', 'description' => 'Product strategist with 12+ years in digital. Leads vision, client partnerships and long-term growth.', 'display_order' => 1],
        ['name' => 'Head of Design', 'description' => 'Design lead specialized in design systems, user research and scalable product design.', 'display_order' => 2],
        ['name' => 'CTO', 'description' => 'Full-stack architect. Oversees engineering, infrastructure and technical quality.', 'display_order' => 3],
        ['name' => 'Head of AI Lab', 'description' => 'ML engineer and researcher. Leads applied AI projects and model development.', 'display_order' => 4],
        ['name' => 'Senior Frontend Engineer', 'description' => 'Expert in modern frontend technologies and frameworks.', 'display_order' => 5],
        ['name' => 'Product Designer', 'description' => 'Creates intuitive and beautiful user experiences.', 'display_order' => 6],
        ['name' => 'Backend Engineer', 'description' => 'Builds robust and scalable backend systems.', 'display_order' => 7],
        ['name' => 'SEO Specialist', 'description' => 'Optimizes websites for search engines and user experience.', 'display_order' => 8],
        ['name' => 'UI/UX Designer', 'description' => 'Designs user interfaces and experiences.', 'display_order' => 9],
        ['name' => 'Content Strategist', 'description' => 'Creates and manages content strategy.', 'display_order' => 10],
        ['name' => 'DevOps Engineer', 'description' => 'Manages infrastructure and deployment.', 'display_order' => 11],
        ['name' => 'ML Engineer', 'description' => 'Develops machine learning models and solutions.', 'display_order' => 12],
    ];

    // Insert real positions
    $stmt = $db->prepare("INSERT INTO team_positions (name, description, display_order) VALUES (?, ?, ?)");

    foreach ($realPositions as $pos) {
        $stmt->execute([$pos['name'], $pos['description'], $pos['display_order']]);
        echo "Added position: {$pos['name']}\n";
    }

    echo "\nReal team positions synced successfully!\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
