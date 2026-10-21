<?php
/**
 * FaraTech — Job Application Submit API
 * POST /api/job_application.php
 * body (form-data): position_id, full_name, email, phone, cover_letter,
 *                   resume_url, portfolio_url, linkedin_url, lang
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

require_once __DIR__ . '/../includes/functions.php';

$lang = $_POST['lang'] ?? 'fa';
if (!in_array($lang, ['fa', 'en'], true)) $lang = 'fa';

function respond($lang, $success, $message, $extra = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$positionId = isset($_POST['position_id']) ? (int)$_POST['position_id'] : 0;
$fullName   = trim($_POST['full_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$coverLetter = trim($_POST['cover_letter'] ?? '');
$resumeUrl  = trim($_POST['resume_url'] ?? '');
$portfolioUrl = trim($_POST['portfolio_url'] ?? '');
$linkedinUrl = trim($_POST['linkedin_url'] ?? '');

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
             resume_url, portfolio_url, linkedin_url, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([
        $positionId ?: null,
        $fullName,
        $email,
        $phone ?: null,
        $coverLetter ?: null,
        $resumeUrl ?: null,
        $portfolioUrl ?: null,
        $linkedinUrl ?: null,
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
    ], JSON_UNESCAPED_UNICODE);
}