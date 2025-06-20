<?php
require_once 'config/config.php';

echo "<h2>إعداد نظام المصادقة المتقدم</h2>";

try {
    // إضافة أعمدة جديدة لجدول users
    echo "<p>تحديث جدول المستخدمين...</p>";
    
    // التحقق من الأعمدة الموجودة
    $columns = $db->fetchAll("SHOW COLUMNS FROM users");
    $existingColumns = array_column($columns, 'Field');
    
    $newColumns = [
        'email' => "ALTER TABLE users ADD COLUMN email VARCHAR(255) UNIQUE AFTER username",
        'email_verified' => "ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER email",
        'verified_at' => "ALTER TABLE users ADD COLUMN verified_at TIMESTAMP NULL AFTER email_verified",
        'verification_token' => "ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) NULL AFTER verified_at",
        'reset_token' => "ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL AFTER verification_token",
        'reset_token_expires' => "ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL AFTER reset_token",
        'registration_date' => "ALTER TABLE users ADD COLUMN registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER reset_token_expires",
        'approved_by' => "ALTER TABLE users ADD COLUMN approved_by INT NULL AFTER registration_date",
        'approved_at' => "ALTER TABLE users ADD COLUMN approved_at TIMESTAMP NULL AFTER approved_by"
    ];
    
    foreach ($newColumns as $column => $sql) {
        if (!in_array($column, $existingColumns)) {
            $db->execute($sql);
            echo "<p style='color: green;'>✅ تم إضافة عمود: $column</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ العمود موجود بالفعل: $column</p>";
        }
    }
    
    // تحديث حالة المستخدمين الحاليين
    echo "<p>تحديث بيانات المستخدمين الحاليين...</p>";
    
    $existingUsers = $db->fetchAll("SELECT id, username FROM users WHERE email IS NULL");
    foreach ($existingUsers as $user) {
        $email = $user['username'] . '@mosque.local';
        $db->update('users', [
            'email' => $email,
            'email_verified' => 1,
            'registration_date' => date('Y-m-d H:i:s')
        ], 'id = ?', [$user['id']]);
    }
    
    // إنشاء جدول طلبات التسجيل
    echo "<p>إنشاء جدول طلبات التسجيل...</p>";
    
    $registrationRequestsTable = "
    CREATE TABLE IF NOT EXISTS registration_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        verification_token VARCHAR(255) NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        processed_at TIMESTAMP NULL,
        processed_by INT NULL,
        notes TEXT,
        FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->execute($registrationRequestsTable);
    echo "<p style='color: green;'>✅ تم إنشاء جدول طلبات التسجيل</p>";

    // بعد إنشاء جدول registration_requests، أضف الأعمدة المفقودة:

    // إضافة الأعمدة المفقودة لجدول registration_requests
    $missingRequestColumns = [
        'reviewed_at' => "ALTER TABLE registration_requests ADD COLUMN reviewed_at TIMESTAMP NULL AFTER processed_at",
        'reviewed_by' => "ALTER TABLE registration_requests ADD COLUMN reviewed_by INT NULL AFTER reviewed_at"
    ];

    foreach ($missingRequestColumns as $column => $sql) {
        try {
            $db->execute($sql);
            echo "<p style='color: green;'>✅ تم إضافة عمود: $column</p>";
        } catch (Exception $e) {
            echo "<p style='color: blue;'>ℹ️ العمود موجود بالفعل: $column</p>";
        }
    }
    
    // إنشاء جدول سجل العمليات
    echo "<p>إنشاء جدول سجل العمليات...</p>";
    
    $activityLogTable = "
    CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(100) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->execute($activityLogTable);
    echo "<p style='color: green;'>✅ تم إنشاء جدول سجل العمليات</p>";
    
    echo "<h3 style='color: green;'>🎉 تم إعداد نظام المصادقة المتقدم بنجاح!</h3>";
    echo "<p><a href='auth/login.php'>تجربة صفحة الدخول الجديدة</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ خطأ: " . $e->getMessage() . "</p>";
}
?>
