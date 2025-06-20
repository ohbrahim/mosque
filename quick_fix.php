<?php
/**
 * إصلاح سريع للأخطاء
 */

// التحقق من وجود ملف قاعدة البيانات
if (!file_exists('config/database.php')) {
    echo "ملف قاعدة البيانات غير موجود!<br>";
    exit;
}

// تضمين ملف قاعدة البيانات
require_once 'config/database.php';

try {
    $db = new Database();
    echo "✅ تم الاتصال بقاعدة البيانات بنجاح<br>";
    
    // التحقق من وجود جدول المستخدمين
    $result = $db->query("SHOW TABLES LIKE 'users'");
    if ($result->rowCount() == 0) {
        echo "❌ جدول المستخدمين غير موجود<br>";
        echo "يرجى تشغيل ملف إنشاء قاعدة البيانات أولاً<br>";
        exit;
    }
    
    // التحقق من وجود مستخدم مدير
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($adminCount == 0) {
        // إنشاء مستخدم مدير افتراضي
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute(['admin', 'admin@mosque.com', $hashedPassword, 'مدير النظام', 'admin', 'active']);
        echo "✅ تم إنشاء مستخدم المدير الافتراضي<br>";
        echo "اسم المستخدم: admin<br>";
        echo "كلمة المرور: admin123<br>";
    } else {
        echo "✅ مستخدم المدير موجود<br>";
    }
    
    echo "<br><a href='login.php' class='btn btn-primary'>تسجيل الدخول</a>";
    echo " <a href='system_check.php' class='btn btn-info'>فحص النظام</a>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}
?>
