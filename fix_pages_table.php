<?php
// ملف بسيط لإصلاح جدول pages
error_reporting(0);

try {
    // الاتصال بقاعدة البيانات مباشرة
    $db = new PDO('mysql:host=localhost;dbname=mosque_management;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // التحقق من وجود العمود created_by
    $stmt = $db->query("SHOW COLUMNS FROM pages LIKE 'created_by'");
    if ($stmt->rowCount() == 0) {
        // إضافة العمود إذا لم يكن موجوداً
        $db->exec("ALTER TABLE pages ADD COLUMN created_by INT NULL");
        $db->exec("UPDATE pages SET created_by = 1");
    }
    
    // التحقق من وجود العمود updated_by
    $stmt = $db->query("SHOW COLUMNS FROM pages LIKE 'updated_by'");
    if ($stmt->rowCount() == 0) {
        // إضافة العمود إذا لم يكن موجوداً
        $db->exec("ALTER TABLE pages ADD COLUMN updated_by INT NULL");
    }
    
    // التحقق من وجود العمود views_count
    $stmt = $db->query("SHOW COLUMNS FROM pages LIKE 'views_count'");
    if ($stmt->rowCount() == 0) {
        // إضافة العمود إذا لم يكن موجوداً
        $db->exec("ALTER TABLE pages ADD COLUMN views_count INT DEFAULT 0");
    }
    
    echo '<div style="direction: rtl; font-family: Arial; padding: 20px; margin: 20px auto; max-width: 600px; background-color: #f0f8ff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">';
    echo '<h2 style="color: #4CAF50; text-align: center;">تم إصلاح جدول pages بنجاح!</h2>';
    echo '<p style="text-align: center;">تم إضافة الأعمدة المفقودة إلى جدول pages</p>';
    echo '<div style="text-align: center; margin-top: 20px;">';
    echo '<a href="fix_duplicate_functions.php" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">إصلاح تضارب الدوال</a>';
    echo '</div>';
    echo '</div>';
    
} catch (PDOException $e) {
    echo '<div style="direction: rtl; font-family: Arial; padding: 20px; margin: 20px auto; max-width: 600px; background-color: #fff3f3; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">';
    echo '<h2 style="color: #f44336; text-align: center;">خطأ في قاعدة البيانات!</h2>';
    echo '<p style="text-align: center;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}
?>
