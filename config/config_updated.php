<?php
/**
 * ملف التكوين المحدث والكامل
 */

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// إعدادات قاعدة البيانات
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'mosque_management');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// إعدادات الموقع
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'مسجد النور');
    define('SITE_URL', 'http://localhost/mosque2');
    define('UPLOAD_PATH', __DIR__ . '/../uploads/');
    define('ADMIN_EMAIL', 'admin@mosque.com');
}

// تضمين الملفات المطلوبة
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions_complete.php';

// إنشاء اتصال قاعدة البيانات
try {
    $db = new Database();
} catch (Exception $e) {
    die('خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage());
}

// إنشاء كائن المصادقة إذا كان الملف موجوداً
if (file_exists(__DIR__ . '/../includes/auth.php')) {
    require_once __DIR__ . '/../includes/auth.php';
    $auth = new Auth($db);
}
?>
