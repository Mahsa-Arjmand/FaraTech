<?php
/**
 * FaraTech — Job Application Submit API
 * POST /api/job_application.php
 * body (form-data): position_id, full_name, email, phone, cover_letter,
 *                   resume_file, portfolio_file, linkedin_url, lang
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$lang = $_POST['lang'] ?? 'fa';
if (!in_array($lang, ['fa', 'en'], true)) $lang = 'fa';

// Rate limiting for API
function checkRateLimit($ip, $endpoint) {
    $limitFile = __DIR__ . '/../logs/rate_limit.log';
    $maxRequests = 10;
    $timeWindow = 60; // 1 minute

    if (!file_exists($limitFile)) {
        return true;
    }

    $requests = json_decode(file_get_contents($limitFile), true) ?: [];
    $now = time();

    // Clean old entries
    $requests = array_filter($requests, function($entry) use ($now, $timeWindow) {
        return ($now - $entry['time']) < $timeWindow;
    });

    $ipRequests = array_filter($requests, function($entry) use ($ip, $endpoint) {
        return $entry['ip'] === $ip && $entry['endpoint'] === $endpoint;
    });

    if (count($ipRequests) >= $maxRequests) {
        return false;
    }

    // Record this request
    $requests[] = ['ip' => $ip, 'endpoint' => $endpoint, 'time' => $now];
    file_put_contents($limitFile, json_encode($requests));

    return true;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!checkRateLimit($ip, 'job_application')) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => $lang === 'fa' ? 'تعداد درخواست‌های شما بیش از حد مجاز است. لطفاً بعداً تلاش کنید.' : 'Too many requests. Please try again later.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function respond($lang, $success, $message, $extra = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function uploadFile($file, $prefix = 'resume') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/x-rar-compressed'];
    $fileType = $file['type'];

    if (!in_array($fileType, $allowedTypes)) {
        return null;
    }

    // File size limit (10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/job_applications/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return '/uploads/job_applications/' . $filename;
    }

    return null;
}

$positionId = isset($_POST['position_id']) ? (int)$_POST['position_id'] : 0;
$fullName   = trim($_POST['full_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$coverLetter = trim($_POST['cover_letter'] ?? '');
$linkedinUrl = trim($_POST['linkedin_url'] ?? '');
$isInternship = isset($_POST['is_internship']) ? 1 : 0;

// Sanitize inputs
$fullName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$coverLetter = htmlspecialchars($coverLetter, ENT_QUOTES, 'UTF-8');
$linkedinUrl = filter_var($linkedinUrl, FILTER_SANITIZE_URL);

// Handle file uploads
$resumePath = uploadFile($_FILES['resume_file'] ?? null, 'resume');
$portfolioPath = uploadFile($_FILES['portfolio_file'] ?? null, 'portfolio');

if ($fullName === '' || $email === '') {
    respond($lang, false, $lang === 'fa' ? 'نام و ایمیل الزامی هستند.' : 'Name and email are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond($lang, false, $lang === 'fa' ? 'ایمیل معتبر نیست.' : 'Invalid email address.');
}

try {
    $db = getDBConnection();

    // بررسی وجود موقعیت
    if ($positionId > 0) {
        $chk = $db->prepare("SELECT id FROM team_positions WHERE id = ? LIMIT 1");
        $chk->execute([$positionId]);
        if (!$chk->fetchColumn()) {
            respond($lang, false, $lang === 'fa' ? 'موقعیت شغلی یافت نشد.' : 'Position not found.');
        }
    }

    // جلوگیری از ارسال تکراری در ۵ دقیقه اخیر (اختیاری)
    $dup = $db->prepare("SELECT COUNT(*) FROM job_applications
                         WHERE email = ? AND position_id = ?
                           AND created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    $dup->execute([$email, $positionId]);
    if ((int)$dup->fetchColumn() > 0) {
        respond($lang, false, $lang === 'fa'
            ? 'شما اخیراً درخواست داده‌اید. لطفاً چند دقیقه بعد دوباره تلاش کنید.'
            : 'You recently applied. Please try again in a few minutes.');
    }

    $stmt = $db->prepare("
        INSERT INTO job_applications
            (position_id, full_name, email, phone, cover_letter,
             resume_file, portfolio_file, linkedin_url, is_internship, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([
        $positionId ?: null,
        $fullName,
        $email,
        $phone ?: null,
        $coverLetter ?: null,
        $resumePath,
        $portfolioPath,
        $linkedinUrl ?: null,
        $isInternship,
    ]);

    $newId = (int)$db->lastInsertId();

    respond($lang, true, $lang === 'fa'
        ? 'درخواست شما با موفقیت ثبت شد.'
        : 'Your application was submitted successfully.', ['id' => $newId]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $lang === 'fa' ? 'خطا در ثبت درخواست.' : 'Failed to save application.',
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}