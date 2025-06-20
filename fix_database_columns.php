<?php
require_once 'config/config.php';

echo "<h2>إصلاح أعمدة قاعدة البيانات</h2>";

try {
    // إضافة الأعمدة المفقودة لجدول users
    echo "<p>إضافة الأعمدة المفقودة لجدول users...</p>";
    
    $userColumns = [
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
    
    foreach ($userColumns as $column => $sql) {
        try {
            $db->execute($sql);
            echo "<p style='color: green;'>✅ تم إضافة عمود: $column</p>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<p style='color: blue;'>ℹ️ العمود موجود بالفعل: $column</p>";
            } else {
                echo "<p style='color: red;'>❌ خطأ في إضافة عمود $column: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // إنشاء جدول registration_requests إذا لم يكن موجوداً
    echo "<p>التحقق من جدول registration_requests...</p>";
    
    $createRegistrationTable = "
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
        reviewed_at TIMESTAMP NULL,
        reviewed_by INT NULL,
        notes TEXT,
        FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->execute($createRegistrationTable);
    echo "<p style='color: green;'>✅ تم التحقق من جدول registration_requests</p>";
    
    // إضافة الأعمدة المفقودة لجدول registration_requests
    $requestColumns = [
        'reviewed_at' => "ALTER TABLE registration_requests ADD COLUMN reviewed_at TIMESTAMP NULL AFTER processed_at",
        'reviewed_by' => "ALTER TABLE registration_requests ADD COLUMN reviewed_by INT NULL AFTER reviewed_at"
    ];
    
    foreach ($requestColumns as $column => $sql) {
        try {
            $db->execute($sql);
            echo "<p style='color: green;'>✅ تم إضافة عمود: $column</p>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<p style='color: blue;'>ℹ️ العمود موجود بالفعل: $column</p>";
            } else {
                echo "<p style='color: red;'>❌ خطأ في إضافة عمود $column: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // إنشاء جدول activity_log
    echo "<p>التحقق من جدول activity_log...</p>";
    
    $createActivityTable = "
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
    
    $db->execute($createActivityTable);
    echo "<p style='color: green;'>✅ تم التحقق من جدول activity_log</p>";
    
    // تحديث المستخدمين الحاليين
    echo "<p>تحديث بيانات المستخدمين الحاليين...</p>";
    
    $existingUsers = $db->fetchAll("SELECT id, username FROM users WHERE email IS NULL OR email = ''");
    foreach ($existingUsers as $user) {
        $email = $user['username'] . '@mosque.local';
        try {
            $db->update('users', [
                'email' => $email,
                'email_verified' => 1,
                'registration_date' => date('Y-m-d H:i:s')
            ], 'id = ?', [$user['id']]);
            echo "<p style='color: green;'>✅ تم تحديث المستخدم: " . htmlspecialchars($user['username']) . "</p>";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ تخطي المستخدم: " . htmlspecialchars($user['username']) . " - " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3 style='color: green;'>🎉 تم إصلاح قاعدة البيانات بنجاح!</h3>";
    echo "<p><a href='dev_tools.php' class='btn btn-primary'>الانتقال لأدوات التطوير</a></p>";
    echo "<p><a href='auth/login.php' class='btn btn-success'>تجربة صفحة الدخول</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ خطأ عام: " . $e->getMessage() . "</p>";
}
?>
