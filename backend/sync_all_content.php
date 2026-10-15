<?php
require_once 'config/database.php';

try {
    $db = getDBConnection();

    // Delete all existing content
    $db->exec("DELETE FROM content");

    // Comprehensive content from all pages
    $allContent = [
        // Index page
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'badge', 'content_value' => 'Available for new projects — 2025'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'We build websites that actually work'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'From early-stage startups to established brands, FaraTech helps businesses build a strong, professional presence online.'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'cta_primary', 'content_value' => 'Get a Free Consultation'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'cta_secondary', 'content_value' => 'View Our Work'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'stat_projects', 'content_value' => '240+'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'stat_satisfaction', 'content_value' => '98%'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'stat_experience', 'content_value' => '9yrs'],
        ['page' => 'index', 'section' => 'hero', 'content_key' => 'project_name', 'content_value' => 'Aram Fashion Store'],
        ['page' => 'index', 'section' => 'trusted', 'content_key' => 'label', 'content_value' => 'TRUSTED BY PROFESSIONAL TEAMS'],
        ['page' => 'index', 'section' => 'services', 'content_key' => 'eyebrow', 'content_value' => 'SERVICES'],
        ['page' => 'index', 'section' => 'services', 'content_key' => 'title', 'content_value' => 'Digital solutions tailored to your needs'],
        ['page' => 'index', 'section' => 'services', 'content_key' => 'subtitle', 'content_value' => 'We focus on three core areas — deep, precise and professional.'],
        ['page' => 'index', 'section' => 'services', 'content_key' => 'service1_title', 'content_value' => 'Web Design & Development'],
        ['page' => 'index', 'section' => 'services', 'content_key' => 'service1_desc', 'content_value' => 'Fast, secure and modern websites built around your business goals.'],
        ['page' => 'index', 'section' => 'services', 'content_key' => 'service2_title', 'content_value' => 'UI / UX Design'],
        ['page' => 'index', 'section' => 'services', 'content_key' => 'service2_desc', 'content_value' => 'Interfaces and experiences that capture users from the very first second.'],
        ['page' => 'index', 'section' => 'services', 'content_key' => 'service3_title', 'content_value' => 'SEO & Digital Marketing'],
        ['page' => 'index', 'section' => 'services', 'content_key' => 'service3_desc', 'content_value' => 'Get found, grow traffic, and convert visitors into customers.'],
        ['page' => 'index', 'section' => 'portfolio', 'content_key' => 'eyebrow', 'content_value' => 'SELECTED WORK'],
        ['page' => 'index', 'section' => 'portfolio', 'content_key' => 'title', 'content_value' => 'Recent projects'],
        ['page' => 'index', 'section' => 'portfolio', 'content_key' => 'subtitle', 'content_value' => 'A selection of recent websites, platforms and AI-powered products we\'ve built with our clients.'],
        ['page' => 'index', 'section' => 'portfolio', 'content_key' => 'cta', 'content_value' => 'View All Projects'],
        ['page' => 'index', 'section' => 'testimonials', 'content_key' => 'eyebrow', 'content_value' => 'TESTIMONIALS'],
        ['page' => 'index', 'section' => 'testimonials', 'content_key' => 'title', 'content_value' => 'What our clients say'],
        ['page' => 'index', 'section' => 'testimonials', 'content_key' => 'subtitle', 'content_value' => 'Honest feedback from people we\'ve worked with.'],
        ['page' => 'index', 'section' => 'cta', 'content_key' => 'title', 'content_value' => 'Have a project in mind?'],
        ['page' => 'index', 'section' => 'cta', 'content_key' => 'subtitle', 'content_value' => 'Let\'s turn your idea into a product your users will love.'],
        ['page' => 'index', 'section' => 'cta', 'content_key' => 'button', 'content_value' => 'Start a project'],

        // About page
        ['page' => 'about', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'ABOUT FARATECH'],
        ['page' => 'about', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'We design, build and scale digital products'],
        ['page' => 'about', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'Founded in 2016, FaraTech is a full-service digital agency helping businesses turn ideas into high-performing websites, platforms and AI-powered tools.'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'eyebrow', 'content_value' => 'OUR STORY'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'title', 'content_value' => 'Built by makers, for makers'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'description', 'content_value' => 'FaraTech started as a small studio of three designers and developers who believed that great digital products come from clear thinking, careful craft, and honest collaboration.'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'stat1', 'content_value' => '2016'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'stat1_label', 'content_value' => 'Founded'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'stat2', 'content_value' => '240+'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'stat2_label', 'content_value' => 'Projects'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'stat3', 'content_value' => '30+'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'stat3_label', 'content_value' => 'Team members'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'stat4', 'content_value' => '12'],
        ['page' => 'about', 'section' => 'story', 'content_key' => 'stat4_label', 'content_value' => 'Countries served'],
        ['page' => 'about', 'section' => 'values', 'content_key' => 'eyebrow', 'content_value' => 'WHAT WE BELIEVE'],
        ['page' => 'about', 'section' => 'values', 'content_key' => 'title', 'content_value' => 'Values that guide every project'],
        ['page' => 'about', 'section' => 'values', 'content_key' => 'value1_title', 'content_value' => 'Clarity First'],
        ['page' => 'about', 'section' => 'values', 'content_key' => 'value1_desc', 'content_value' => 'We start with understanding — clear goals, clear communication, clear outcomes.'],
        ['page' => 'about', 'section' => 'values', 'content_key' => 'value2_title', 'content_value' => 'Quality Over Speed'],
        ['page' => 'about', 'section' => 'values', 'content_key' => 'value2_desc', 'content_value' => 'We ship when it\'s right, not when it\'s due. Rushed work doesn\'t last.'],
        ['page' => 'about', 'section' => 'values', 'content_key' => 'value3_title', 'content_value' => 'Long-Term Partnerships'],
        ['page' => 'about', 'section' => 'values', 'content_key' => 'value3_desc', 'content_value' => 'We don\'t build and disappear. We grow with our clients.'],

        // Services page
        ['page' => 'services', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'SERVICES'],
        ['page' => 'services', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Services we offer'],
        ['page' => 'services', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'From strategy to execution, we provide end-to-end digital solutions.'],
        ['page' => 'services', 'section' => 'web_design', 'content_key' => 'title', 'content_value' => 'Web Design & Development'],
        ['page' => 'services', 'section' => 'web_design', 'content_key' => 'description', 'content_value' => 'Custom responsive design, SEO-friendly structure, high performance loading'],
        ['page' => 'services', 'section' => 'ui_ux', 'content_key' => 'title', 'content_value' => 'UI / UX Design'],
        ['page' => 'services', 'section' => 'ui_ux', 'content_key' => 'description', 'content_value' => 'User research & testing, design systems & components, prototyping'],
        ['page' => 'services', 'section' => 'seo', 'content_key' => 'title', 'content_value' => 'SEO & Digital Marketing'],
        ['page' => 'services', 'section' => 'seo', 'content_key' => 'description', 'content_value' => 'SEO optimization, content strategy, social media marketing'],
        ['page' => 'services', 'section' => 'ai_solutions', 'content_key' => 'title', 'content_value' => 'AI Solutions'],
        ['page' => 'services', 'section' => 'ai_solutions', 'content_key' => 'description', 'content_value' => 'Machine learning models, NLP applications, data analytics'],

        // Portfolio page
        ['page' => 'portfolio', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'SELECTED WORK'],
        ['page' => 'portfolio', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Projects we\'ve carefully crafted'],
        ['page' => 'portfolio', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'A selection of recent websites, platforms and AI-powered products we\'ve built with our clients.'],

        // Team page
        ['page' => 'team', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'OUR TEAM'],
        ['page' => 'team', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'The people behind FaraTech'],
        ['page' => 'team', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'A multidisciplinary team of designers, engineers, strategists and AI specialists — working together to ship great products.'],
        ['page' => 'team', 'section' => 'leadership', 'content_key' => 'eyebrow', 'content_value' => 'LEADERSHIP'],
        ['page' => 'team', 'section' => 'leadership', 'content_key' => 'title', 'content_value' => 'Leading the work'],
        ['page' => 'team', 'section' => 'team', 'content_key' => 'eyebrow', 'content_value' => 'THE TEAM'],
        ['page' => 'team', 'section' => 'team', 'content_key' => 'title', 'content_value' => 'Experts across disciplines'],
        ['page' => 'team', 'section' => 'careers', 'content_key' => 'eyebrow', 'content_value' => 'CAREERS'],
        ['page' => 'team', 'section' => 'careers', 'content_key' => 'title', 'content_value' => 'Want to join the team?'],
        ['page' => 'team', 'section' => 'careers', 'content_key' => 'subtitle', 'content_value' => 'We\'re always looking for talented people who love building things that matter.'],

        // Contact page
        ['page' => 'contact', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'CONTACT'],
        ['page' => 'contact', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Get in touch'],
        ['page' => 'contact', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'Ready to start your next project? Let\'s talk.'],
        ['page' => 'contact', 'section' => 'info', 'content_key' => 'email', 'content_value' => 'info@faratech.ir'],
        ['page' => 'contact', 'section' => 'info', 'content_key' => 'phone', 'content_value' => '+98 21 1234 5678'],
        ['page' => 'contact', 'section' => 'form', 'content_key' => 'title', 'content_value' => 'Send us a message'],
        ['page' => 'contact', 'section' => 'form', 'content_key' => 'subtitle', 'content_value' => 'Fill out the form below and we\'ll get back to you within 24 hours.'],

        // AI Lab page
        ['page' => 'ai-lab', 'section' => 'hero', 'content_key' => 'eyebrow', 'content_value' => 'AI LAB'],
        ['page' => 'ai-lab', 'section' => 'hero', 'content_key' => 'title', 'content_value' => 'Applied AI Research'],
        ['page' => 'ai-lab', 'section' => 'hero', 'content_key' => 'subtitle', 'content_value' => 'Our AI Lab develops practical machine learning solutions for real-world business problems.'],
        ['page' => 'ai-lab', 'section' => 'focus', 'content_key' => 'eyebrow', 'content_value' => 'OUR FOCUS'],
        ['page' => 'ai-lab', 'section' => 'focus', 'content_key' => 'title', 'content_value' => 'Research areas'],
        ['page' => 'ai-lab', 'section' => 'focus', 'content_key' => 'area1', 'content_value' => 'Natural Language Processing'],
        ['page' => 'ai-lab', 'section' => 'focus', 'content_key' => 'area2', 'content_value' => 'Computer Vision'],
        ['page' => 'ai-lab', 'section' => 'focus', 'content_key' => 'area3', 'content_value' => 'Predictive Analytics'],
    ];

    // Insert all content
    $stmt = $db->prepare("INSERT INTO content (page, section, content_key, content_value) VALUES (?, ?, ?, ?)");

    foreach ($allContent as $item) {
        $stmt->execute([$item['page'], $item['section'], $item['content_key'], $item['content_value']]);
        echo "Added: {$item['page']} - {$item['section']} - {$item['content_key']}\n";
    }

    echo "\nAll content synced successfully!\n";
    echo "Total content items: " . count($allContent) . "\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
