<?php
/**
 * ملف اختبار النظام
 */
echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>اختبار النظام</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
    </style>
</head>
<body>";

echo "<h1>🔍 اختبار نظام إدارة المسجد</h1>";

// اختبار PHP
echo "<h2>1. اختبار PHP</h2>";
echo "<p class='info'>إصدار PHP: " . phpversion() . "</p>";

if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<p class='success'>✅ إصدار PHP مناسب</p>";
} else {
    echo "<p class='error'>❌ إصدار PHP قديم جداً</p>";
}

// اختبار الإضافات المطلوبة
echo "<h2>2. اختبار الإضافات المطلوبة</h2>";

$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ {$ext} متوفر</p>";
    } else {
        echo "<p class='error'>❌ {$ext} غير متوفر</p>";
    }
}

// اختبار الاتصال بقاعدة البيانات
echo "<h2>3. اختبار قاعدة البيانات</h2>";

try {
    require_once 'config/config.php';
    echo "<p class='success'>✅ تم تحميل ملف الإعدادات</p>";
    
    $testQuery = $db->fetchOne("SELECT COUNT(*) as count FROM users");
    echo "<p class='success'>✅ الاتصال بقاعدة البيانات نجح</p>";
    echo "<p class='info'>عدد المستخدمين: " . $testQuery['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ خطأ في قاعدة البيانات: " . $e->getMessage() . "</p>";
}

// اختبار الملفات المطلوبة
echo "<h2>4. اختبار الملفات المطلوبة</h2>";

$files = [
    'config/config.php',
    'config/database.php',
    'includes/functions.php',
    'includes/auth.php',
    'index.php',
    'login.php',
    'admin/index.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ {$file} موجود</p>";
    } else {
        echo "<p class='error'>❌ {$file} غير موجود</p>";
    }
}

// اختبار صلاحيات المجلدات
echo "<h2>5. اختبار صلاحيات المجلدات</h2>";

$uploadDir = 'uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (is_writable($uploadDir)) {
    echo "<p class='success'>✅ مجلد الرفع قابل للكتابة</p>";
} else {
    echo "<p class='error'>❌ مجلد الرفع غير قابل للكتابة</p>";
}

echo "<h2>6. روابط الاختبار</h2>";
echo "<p><a href='index.php' target='_blank'>🏠 اختبار الصفحة الرئيسية</a></p>";
echo "<p><a href='login.php' target='_blank'>🔐 اختبار صفحة تسجيل الدخول</a></p>";
echo "<p><a href='admin/index.php' target='_blank'>⚙️ اختبار لوحة التحكم</a></p>";

echo "<h2>7. بيانات تسجيل الدخول</h2>";
echo "<p class='info'><strong>اسم المستخدم:</strong> admin</p>";
echo "<p class='info'><strong>كلمة المرور:</strong> password</p>";

echo "</body></html>";
?>
