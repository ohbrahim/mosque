<?php
/**
 * إصلاح تضارب الدوال
 */

// تعطيل عرض الأخطاء مؤقتاً
ini_set('display_errors', 0);

// حذف الدوال المكررة من ملف functions.php
$functionsFile = __DIR__ . '/includes/functions.php';
$configFile = __DIR__ . '/config/config.php';

if (file_exists($functionsFile) && file_exists($configFile)) {
    // قراءة محتوى الملفات
    $functionsContent = file_get_contents($functionsFile);
    $configContent = file_get_contents($configFile);
    
    // تحديد الدوال المكررة
    $duplicateFunctions = [
        'requireLogin',
        'requirePermission',
        'generateCSRFToken',
        'verifyCSRFToken'
    ];
    
    // إنشاء نسخة احتياطية من الملفات
    file_put_contents($functionsFile . '.bak', $functionsContent);
    file_put_contents($configFile . '.bak', $configContent);
    
    // إزالة الدوال المكررة من ملف functions.php
    foreach ($duplicateFunctions as $function) {
        $pattern = '/function\s+' . $function . '\s*$$[^)]*$$\s*\{[^}]*\}/s';
        $functionsContent = preg_replace($pattern, '// Function ' . $function . ' moved to config.php', $functionsContent);
    }
    
    // كتابة المحتوى المعدل
    file_put_contents($functionsFile, $functionsContent);
    
    echo '<div style="padding: 20px; background-color: #d4edda; color: #155724; border-radius: 5px; margin: 20px; font-family: Arial;">';
    echo '<h3>تم إصلاح تضارب الدوال بنجاح!</h3>';
    echo '<p>تم إنشاء نسخة احتياطية من الملفات الأصلية:</p>';
    echo '<ul>';
    echo '<li>' . $functionsFile . '.bak</li>';
    echo '<li>' . $configFile . '.bak</li>';
    echo '</ul>';
    echo '<p>الآن يمكنك استخدام النظام بدون مشاكل.</p>';
    echo '<a href="admin/index.php" style="display: inline-block; background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 10px;">الذهاب للوحة التحكم</a>';
    echo '</div>';
} else {
    echo '<div style="padding: 20px; background-color: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px; font-family: Arial;">';
    echo '<h3>خطأ!</h3>';
    echo '<p>لم يتم العثور على الملفات المطلوبة:</p>';
    echo '<ul>';
    if (!file_exists($functionsFile)) echo '<li>ملف الدوال غير موجود: ' . $functionsFile . '</li>';
    if (!file_exists($configFile)) echo '<li>ملف الإعدادات غير موجود: ' . $configFile . '</li>';
    echo '</ul>';
    echo '</div>';
}
?>
