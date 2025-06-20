<?php
/**
 * الإعدادات النظيفة للنظام - بدون تضارب
 */

// بدء الجلسة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// إعدادات التوقيت والترميز
date_default_timezone_set('Asia/Riyadh');
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'mosque_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// إعدادات الأمان
define('SITE_KEY', 'mosque_secret_key_2025');
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// إعدادات الموقع
define('SITE_URL', 'http://localhost/mosque2/');
define('ADMIN_URL', SITE_URL . 'admin/');

// اتصال قاعدة البيانات
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('خطأ في الاتصال بقاعدة البيانات: ' . $e->getMessage());
}

// دوال الأمان والمصادقة
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}

function hasPermission($permission) {
    if (!isLoggedIn()) return false;
    
    $role = $_SESSION['role'] ?? '';
    
    // المدير له جميع الصلاحيات
    if ($role === 'admin') return true;
    
    // صلاحيات المحرر
    if ($role === 'editor') {
        $editorPermissions = ['manage_pages', 'manage_blocks', 'view_stats'];
        return in_array($permission, $editorPermissions);
    }
    
    return false;
}

function requirePermission($permission) {
    requireLogin();
    if (!hasPermission($permission)) {
        die('<div style="padding:20px;text-align:center;font-family:Arial;">
                <h3>ليس لديك صلاحية للوصول لهذه الصفحة</h3>
                <p>الصلاحية المطلوبة: ' . $permission . '</p>
                <a href="index.php">العودة للوحة التحكم</a>
             </div>');
    }
}

// دوال قاعدة البيانات
function getSetting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function saveSetting($key, $value, $userId = null) {
    global $pdo;
    try {
        $userId = $userId ?? $_SESSION['user_id'] ?? 1;
        
        // التحقق من وجود الإعداد
        $stmt = $pdo->prepare("SELECT id FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        
        if ($stmt->fetch()) {
            // تحديث
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ?, updated_by = ?, updated_at = NOW() WHERE setting_key = ?");
            return $stmt->execute([$value, $userId, $key]);
        } else {
            // إدراج جديد
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, updated_by, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            return $stmt->execute([$key, $value, $userId]);
        }
    } catch (Exception $e) {
        error_log("خطأ في حفظ الإعداد: " . $e->getMessage());
        return false;
    }
}

// دوال مساعدة
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function formatArabicDate($date) {
    $timestamp = strtotime($date);
    $months = [
        "يناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو",
        "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"
    ];
    
    $day = date('j', $timestamp);
    $month = $months[date('n', $timestamp) - 1];
    $year = date('Y', $timestamp);
    
    return $day . ' ' . $month . ' ' . $year;
}
?>
