<?php
/**
 * الإعدادات العامة للنظام - نسخة محدثة
 */

// إعدادات الجلسة
session_start();

// إعدادات التوقيت
date_default_timezone_set('Asia/Riyadh');

// إعدادات الترميز
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// إعدادات الأمان
define('SITE_KEY', 'your_secret_key_here_change_this');
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// إعدادات الموقع
define('SITE_URL', 'http://localhost/mosque_management/');
define('ADMIN_URL', SITE_URL . 'admin/');

// إعدادات البريد الإلكتروني
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_password');

// تضمين ملفات النظام
require_once 'database_fixed.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// إنشاء اتصال قاعدة البيانات
$db = new Database();

// تحديد اللغة
define('LANG', 'ar');

// إعدادات الأمان للرؤوس
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// دالة لتنظيف البيانات
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// دالة للتحقق من CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
