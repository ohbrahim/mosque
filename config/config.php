<?php
/**
 * ملف التكوين الرئيسي المُصلح والمحدث
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

// إنشاء اتصال قاعدة البيانات
try {
    $db = new Database();
} catch (Exception $e) {
    die('خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage());
}

// الدوال الأساسية
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && (
        $_SESSION['role'] === 'admin' || 
        $_SESSION['role'] === 'super_admin' ||
        isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1
    );
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        die('ليس لديك صلاحية للوصول إلى هذه الصفحة');
    }
}

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function getSetting($db, $key, $default = '') {
    if (!$db) {
        return $default;
    }
    
    try {
        $result = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function formatArabicDate($date) {
    $timestamp = strtotime($date);
    return date('Y/m/d H:i', $timestamp);
}

function convertToArabicNumbers($number) {
    $useArabicNumbers = getSetting($GLOBALS['db'], 'use_arabic_numbers', '1');
    
    if ($useArabicNumbers === '1') {
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($english, $arabic, $number);
    }
    
    return $number;
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// تضمين ملف الصلاحيات (بدون تضارب)
if (file_exists(__DIR__ . '/permissions.php')) {
    require_once __DIR__ . '/permissions.php';
}

// تضمين ملف الدوال الشامل
if (file_exists(__DIR__ . '/../includes/all_functions.php')) {
    require_once __DIR__ . '/../includes/all_functions.php';
}

// إنشاء كائل المصادقة إذا كان الملف موجوداً
if (file_exists(__DIR__ . '/../includes/auth.php')) {
    require_once __DIR__ . '/../includes/auth.php';
    $auth = new Auth($db);
}
?>