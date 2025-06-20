<?php
// ملف بسيط لإصلاح تضارب الدوال
error_reporting(0);

// المسار إلى ملف functions.php
$functionsFile = __DIR__ . '/includes/functions.php';

if (file_exists($functionsFile)) {
    // قراءة محتوى الملف
    $content = file_get_contents($functionsFile);
    
    // تعليق الدوال المكررة
    $functionsToComment = [
        'requireLogin',
        'requirePermission'
    ];
    
    foreach ($functionsToComment as $function) {
        // البحث عن تعريف الدالة وتعليقها
        $pattern = '/function\s+' . $function . '\s*$$[^$$]*\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s';
        $replacement = '/* 
// تم تعليق هذه الدالة لتجنب التضارب مع config.php
function ' . $function . '() {
    // تم التعليق
}
*/';
        
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // حفظ الملف المعدل
    file_put_contents($functionsFile, $content);
    
    echo '<div style="direction: rtl; font-family: Arial; padding: 20px; margin: 20px auto; max-width: 600px; background-color: #f0f8ff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">';
    echo '<h2 style="color: #4CAF50; text-align: center;">تم إصلاح تضارب الدوال بنجاح!</h2>';
    echo '<p style="text-align: center;">تم تعليق الدوال المكررة في ملف functions.php</p>';
    echo '<div style="text-align: center; margin-top: 20px;">';
    echo '<a href="index.php" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">الصفحة الرئيسية</a>';
    echo '<a href="admin/index.php" style="background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">لوحة التحكم</a>';
    echo '</div>';
    echo '</div>';
} else {
    echo '<div style="direction: rtl; font-family: Arial; padding: 20px; margin: 20px auto; max-width: 600px; background-color: #fff3f3; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">';
    echo '<h2 style="color: #f44336; text-align: center;">خطأ!</h2>';
    echo '<p style="text-align: center;">لم يتم العثور على ملف functions.php</p>';
    echo '<p style="text-align: center;">المسار المتوقع: ' . htmlspecialchars($functionsFile) . '</p>';
    echo '</div>';
}
?>
