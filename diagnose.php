<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>تشخيص شامل للنظام</h2>";

// فحص الجلسة
session_start();
echo "<h3>1. فحص الجلسة:</h3>";
echo "معرف الجلسة: " . session_id() . "<br>";
echo "بيانات الجلسة: <pre>" . print_r($_SESSION, true) . "</pre>";

// فحص قاعدة البيانات
echo "<h3>2. فحص قاعدة البيانات:</h3>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mosque_management;charset=utf8mb4", "root", "");
    echo "✓ الاتصال بقاعدة البيانات نجح<br>";
    
    // فحص الجداول
    $tables = ['users', 'pages', 'blocks', 'settings', 'messages'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✓ جدول $table موجود<br>";
        } else {
            echo "✗ جدول $table مفقود<br>";
        }
    }
    
    // فحص المستخدم المدير
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    if ($admin) {
        echo "✓ المستخدم المدير موجود<br>";
        echo "الدور: " . $admin['role'] . "<br>";
        echo "الحالة: " . $admin['status'] . "<br>";
    } else {
        echo "✗ المستخدم المدير غير موجود<br>";
    }
    
} catch (Exception $e) {
    echo "✗ خطأ في قاعدة البيانات: " . $e->getMessage() . "<br>";
}

// فحص الملفات
echo "<h3>3. فحص الملفات:</h3>";
$files = [
    'config/config.php',
    'config/database.php', 
    'includes/functions.php',
    'includes/auth.php',
    'admin/index.php',
    'admin/pages.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ $file موجود<br>";
    } else {
        echo "✗ $file مفقود<br>";
    }
}

// فحص الأخطاء
echo "<h3>4. فحص آخر الأخطاء:</h3>";
$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $errors = file_get_contents($errorLog);
    $lastErrors = array_slice(explode("\n", $errors), -10);
    echo "<pre>" . implode("\n", $lastErrors) . "</pre>";
} else {
    echo "لا توجد أخطاء مسجلة<br>";
}

// اختبار تحميل config
echo "<h3>5. اختبار تحميل الإعدادات:</h3>";
try {
    require_once 'config/config.php';
    echo "✓ تم تحميل config.php بنجاح<br>";
    
    if (isset($db)) {
        echo "✓ كائن قاعدة البيانات موجود<br>";
    } else {
        echo "✗ كائن قاعدة البيانات غير موجود<br>";
    }
    
    if (isset($auth)) {
        echo "✓ كائن المصادقة موجود<br>";
    } else {
        echo "✗ كائن المصادقة غير موجود<br>";
    }
    
} catch (Exception $e) {
    echo "✗ خطأ في تحميل الإعدادات: " . $e->getMessage() . "<br>";
}

echo "<h3>6. اختبار الدوال:</h3>";
if (function_exists('sanitize')) {
    echo "✓ دالة sanitize موجودة<br>";
} else {
    echo "✗ دالة sanitize مفقودة<br>";
}

if (function_exists('requireLogin')) {
    echo "✓ دالة requireLogin موجودة<br>";
} else {
    echo "✗ دالة requireLogin مفقودة<br>";
}
?>
