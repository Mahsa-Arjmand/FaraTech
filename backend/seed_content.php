<?php
require_once 'config/database.php';

try {
    $db = getDBConnection();

    // Sample content for home page
    $sampleContent = [
        // Home page
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'We design, build and scale digital products'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'Founded in 2016, FaraTech is a full-service digital agency helping businesses turn ideas into high-performing websites, platforms and AI-powered tools.'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'cta_text', 'content_value' => 'Start a Project'],

        // About page
        ['page' => 'about', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'We design, build and scale digital products'],
        ['page' => 'about', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'Founded in 2016, FaraTech is a full-service digital agency helping businesses turn ideas into high-performing websites, platforms and AI-powered tools.'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'title', 'content_value' => 'Built by makers, for makers'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'description', 'content_value' => 'FaraTech started as a small studio of three designers and developers who believed that great digital products come from clear thinking, careful craft, and honest collaboration.'],

        // Services page
        ['page' => 'services', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Services we offer'],
        ['page' => 'services', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'From strategy to execution, we provide end-to-end digital solutions.'],
        ['page' => 'services', 'section' => 'web_design', 'content_key' => 'title', 'content_value' => 'Web Design & Development'],
        ['page' => 'services', 'section' => 'web_design', 'content_key' => 'description', 'content_value' => 'Custom websites built with modern technologies and best practices.'],

        // Team page
        ['page' => 'team', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'The people behind FaraTech'],
        ['page' => 'team', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'A multidisciplinary team of designers, engineers, strategists and AI specialists — working together to ship great products.'],

        // Contact page
        ['page' => 'contact', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Get in touch'],
        ['page' => 'contact', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'Ready to start your next project? Let\'s talk.'],
        ['page' => 'contact', 'section' => 'info', 'content_key' => 'email', 'content_value' => 'hello@faratech.com'],
        ['page' => 'contact', 'section' => 'info', 'content_key' => 'phone', 'content_value' => '+98 21 1234 5678'],
    ];

    // Insert sample content
    $stmt = $db->prepare("INSERT INTO content (page, section, content_key, content_value) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)");

    foreach ($sampleContent as $item) {
        $stmt->execute([$item['page'], $item['section'], $item['content_key'], $item['content_value']]);
        echo "Added: {$item['page']} - {$item['section']} - {$item['content_key']}\n";
    }

    echo "\nSample content added successfully!\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
