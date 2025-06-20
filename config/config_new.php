<?php
/**
 * الإعدادات العامة للنظام - نسخة جديدة
 */

// بدء الجلسة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// إعدادات التوقيت والترميز
date_default_timezone_set('Asia/Riyadh');
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'mosque_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// إعدادات الأمان
define('SITE_KEY', 'your_secret_key_here_change_this');
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// إعدادات الموقع
define('SITE_URL', 'http://localhost/mosque2/');
define('ADMIN_URL', SITE_URL . 'admin/');

// تضمين الدوال الجديدة
require_once __DIR__ . '/../includes/functions_new.php';

// إنشاء اتصال قاعدة البيانات
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    die('خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage());
}

// دوال المصادقة والصلاحيات
function checkLogin() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireAdminLogin() {
    if (!checkLogin()) {
        header('Location: ../login.php');
        exit;
    }
}

function hasPermission($permission) {
    if (!checkLogin()) {
        return false;
    }
    
    // المدير له كل الصلاحيات
    if ($_SESSION['role'] === 'admin') {
        return true;
    }
    
    // يمكن إضافة منطق صلاحيات أكثر تعقيداً هنا
    return false;
}
?>
