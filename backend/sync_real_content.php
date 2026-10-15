<?php
require_once 'config/database.php';

try {
    $db = getDBConnection();

    // Delete existing sample content
    $db->exec("DELETE FROM content");

    // Real content from website
    $realContent = [
        // Index page
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'SELECTED WORK'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Projects we\'ve carefully crafted'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'A selection of recent websites, platforms and AI-powered products we\'ve built with our clients.'],

        // About page
        ['page' => 'about', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'ABOUT FARATECH'],
        ['page' => 'about', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'We design, build and scale digital products'],
        ['page' => 'about', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'Founded in 2016, FaraTech is a full-service digital agency helping businesses turn ideas into high-performing websites, platforms and AI-powered tools.'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'eyebrow', 'content_value' => 'OUR STORY'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'title', 'content_value' => 'Built by makers, for makers'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'description', 'content_value' => 'FaraTech started as a small studio of three designers and developers who believed that great digital products come from clear thinking, careful craft, and honest collaboration.'],

        // Services page
        ['page' => 'services', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'SERVICES'],
        ['page' => 'services', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Services we offer'],
        ['page' => 'services', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'From strategy to execution, we provide end-to-end digital solutions.'],

        // Portfolio page
        ['page' => 'portfolio', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'SELECTED WORK'],
        ['page' => 'portfolio', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Projects we\'ve carefully crafted'],
        ['page' => 'portfolio', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'A selection of recent websites, platforms and AI-powered products we\'ve built with our clients.'],

        // Team page
        ['page' => 'team', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'OUR TEAM'],
        ['page' => 'team', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'The people behind FaraTech'],
        ['page' => 'team', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'A multidisciplinary team of designers, engineers, strategists and AI specialists — working together to ship great products.'],
        ['page' => 'team', 'section' => 'careers', 'content_key' => 'eyebrow', 'content_value' => 'CAREERS'],
        ['page' => 'team', 'section' => 'careers', 'content_key' => 'title', 'content_value' => 'Want to join the team?'],
        ['page' => 'team', 'section' => 'careers', 'content_key' => 'subtitle', 'content_value' => 'We\'re always looking for talented people who love building things that matter.'],

        // Contact page
        ['page' => 'contact', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'CONTACT'],
        ['page' => 'contact', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Get in touch'],
        ['page' => 'contact', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'Ready to start your next project? Let\'s talk.'],
        ['page' => 'contact', 'section' => 'info', 'content_key' => 'email', 'content_value' => 'info@faratech.ir'],
        ['page' => 'contact', 'section' => 'info', 'content_key' => 'phone', 'content_value' => '+98 21 1234 5678'],

        // AI Lab page
        ['page' => 'ai-lab', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'AI LAB'],
        ['page' => 'ai-lab', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Applied AI Research'],
        ['page' => 'ai-lab', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'Our AI Lab develops practical machine learning solutions for real-world business problems.'],
    ];

    // Insert real content
    $stmt = $db->prepare("INSERT INTO content (page, section, content_key, content_value) VALUES (?, ?, ?, ?)");

    foreach ($realContent as $item) {
        $stmt->execute([$item['page'], $item['section'], $item['content_key'], $item['content_value']]);
        echo "Added content: {$item['page']} - {$item['section']} - {$item['content_key']}\n";
    }

    echo "\nReal content synced successfully!\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
