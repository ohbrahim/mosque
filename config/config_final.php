<?php
/**
 * ملف الإعدادات الرئيسي المحدث
 */

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'mosque_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// إعدادات الموقع
define('SITE_URL', 'http://localhost/mosque2/');
define('UPLOAD_PATH', 'uploads/');
define('ADMIN_EMAIL', 'admin@mosque.com');

// إعدادات الأمان
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // ساعة واحدة

// تضمين ملف قاعدة البيانات
require_once __DIR__ . '/database.php';

// تضمين الدوال المساعدة
require_once __DIR__ . '/../includes/functions_fixed.php';

// إنشاء اتصال قاعدة البيانات
try {
    $db = new Database();
    
    // تعيين قاعدة البيانات كمتغير عام
    $GLOBALS['db'] = $db;
    
} catch (Exception $e) {
    die('خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage());
}

// دالة للحصول على قاعدة البيانات العامة
function getDB() {
    global $db;
    return $db;
}

// التحقق من انتهاء صلاحية الجلسة
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// تعيين المنطقة الزمنية
date_default_timezone_set('Africa/Algiers');

// تعيين ترميز الأحرف
mb_internal_encoding('UTF-8');
?>
