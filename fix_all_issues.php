<?php
require_once 'config/config.php';

echo "<h2>إصلاح جميع المشاكل</h2>";

try {
    // 1. إصلاح جدول poll_votes
    echo "<h3>1. إصلاح جدول الاستطلاعات</h3>";
    
    $db->query("DROP TABLE IF EXISTS poll_votes");
    $db->query("
        CREATE TABLE poll_votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            poll_id INT NOT NULL,
            option_id INT NOT NULL,
            voter_ip VARCHAR(45),
            voter_email VARCHAR(100),
            user_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
            FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // إضافة أصوات تجريبية
    $pollOptions = $db->fetchAll("SELECT id FROM poll_options LIMIT 4");
    if (!empty($pollOptions)) {
        foreach ($pollOptions as $index => $option) {
            $votes = rand(5, 15);
            for ($i = 0; $i < $votes; $i++) {
                $db->insert('poll_votes', [
                    'poll_id' => 1,
                    'option_id' => $option['id'],
                    'voter_ip' => '192.168.1.' . rand(1, 254),
                    'voter_email' => 'user' . $i . '@example.com',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'))
                ]);
            }
        }
    }
    echo "✅ تم إصلاح جدول الاستطلاعات<br>";
    
    // 2. إصلاح جدول التقييمات
    echo "<h3>2. إصلاح جدول التقييمات</h3>";
    
    $db->query("DROP TABLE IF EXISTS ratings");
    $db->query("
        CREATE TABLE ratings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_id INT NOT NULL,
            user_id INT NULL,
            user_ip VARCHAR(45),
            rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            UNIQUE KEY unique_user_rating (page_id, user_id),
            UNIQUE KEY unique_ip_rating (page_id, user_ip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ تم إصلاح جدول التقييمات<br>";
    
    // 3. إضافة أعمدة مفقودة في جدول البلوكات
    echo "<h3>3. إصلاح جدول البلوكات</h3>";
    
    try {
        $db->query("ALTER TABLE blocks ADD COLUMN show_title BOOLEAN DEFAULT 1");
    } catch (Exception $e) {
        // العمود موجود بالفعل
    }
    
    try {
        $db->query("ALTER TABLE blocks ADD COLUMN custom_css TEXT");
    } catch (Exception $e) {
        // العمود موجود بالفعل
    }
    
    try {
        $db->query("ALTER TABLE blocks ADD COLUMN show_on_pages TEXT");
    } catch (Exception $e) {
        // العمود موجود بالفعل
    }
    
    echo "✅ تم إصلاح جدول البلوكات<br>";
    
    // 4. إضافة أعمدة مفقودة في جدول الصفحات
    echo "<h3>4. إصلاح جدول الصفحات</h3>";
    
    $columnsToAdd = [
        'is_featured' => 'BOOLEAN DEFAULT 0',
        'meta_keywords' => 'TEXT',
        'visibility' => 'ENUM("public", "private", "password", "members_only") DEFAULT "public"',
        'scheduled_at' => 'DATETIME NULL',
        'published_at' => 'DATETIME NULL',
        'editor_id' => 'INT NULL'
    ];
    
    foreach ($columnsToAdd as $column => $definition) {
        try {
            $db->query("ALTER TABLE pages ADD COLUMN $column $definition");
        } catch (Exception $e) {
            // العمود موجود بالفعل
        }
    }
    
    echo "✅ تم إصلاح جدول الصفحات<br>";
    
    // 5. إضافة أعمدة مفقودة في جدول التعليقات
    echo "<h3>5. إصلاح جدول التعليقات</h3>";
    
    try {
        $db->query("ALTER TABLE comments ADD COLUMN user_id INT NULL");
        $db->query("ALTER TABLE comments ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
    } catch (Exception $e) {
        // العمود موجود بالفعل
    }
    
    echo "✅ تم إصلاح جدول التعليقات<br>";
    
    // 6. إنشاء جدول uploads
    echo "<h3>6. إنشاء جدول الملفات المرفوعة</h3>";
    
    $db->query("DROP TABLE IF EXISTS uploads");
    $db->query("
        CREATE TABLE uploads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size INT NOT NULL,
            file_type VARCHAR(100) NOT NULL,
            uploaded_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✅ تم إنشاء جدول الملفات المرفوعة<br>";
    
    // 7. تحديث الإعدادات
    echo "<h3>7. تحديث الإعدادات</h3>";
    
    $settings = [
        'show_welcome_banner' => '0',
        'welcome_banner_title' => 'مرحباً بكم',
        'welcome_banner_subtitle' => 'أهلاً وسهلاً',
        'welcome_banner_content' => 'مرحباً بكم في موقع مسجد النور. نسعد بزيارتكم ونتمنى أن تجدوا ما تبحثون عنه.',
        'use_arabic_numbers' => '1',
        'enable_comments' => '1',
        'enable_ratings' => '1'
    ];
    
    foreach ($settings as $key => $value) {
        $existing = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if ($existing) {
            $db->update('settings', ['setting_value' => $value], 'setting_key = ?', [$key]);
        } else {
            $db->insert('settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_type' => 'text',
                'category' => 'general'
            ]);
        }
    }
    
    echo "✅ تم تحديث الإعدادات<br>";
    
    echo "<h3>✅ تم إصلاح جميع المشاكل بنجاح!</h3>";
    
} catch (Exception $e) {
    echo "❌ حدث خطأ: " . $e->getMessage();
}
?>
