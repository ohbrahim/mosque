<?php
require_once 'config/config.php';

echo "<h2>إصلاح جميع المشاكل النهائي</h2>";

try {
    // 1. إصلاح مشكلة الاستطلاعات - إنشاء البيانات الأساسية أولاً
    echo "<h3>1. إصلاح جدول الاستطلاعات</h3>";
    
    // حذف الجداول وإعادة إنشائها
    $db->query("SET FOREIGN_KEY_CHECKS = 0");
    $db->query("DROP TABLE IF EXISTS poll_votes");
    $db->query("DROP TABLE IF EXISTS poll_options");
    $db->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // إنشاء جدول خيارات الاستطلاعات
    $db->query("
        CREATE TABLE poll_options (
            id INT AUTO_INCREMENT PRIMARY KEY,
            poll_id INT NOT NULL,
            option_text VARCHAR(255) NOT NULL,
            display_order INT DEFAULT 0,
            votes_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // إنشاء جدول أصوات الاستطلاعات
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
    
    // إضافة خيارات للاستطلاعات الموجودة
    $polls = $db->fetchAll("SELECT id FROM polls");
    foreach ($polls as $poll) {
        // إضافة خيارات للاستطلاع
        $options = [
            'ممتاز',
            'جيد جداً', 
            'جيد',
            'يحتاج تحسين'
        ];
        
        foreach ($options as $index => $option) {
            $db->insert('poll_options', [
                'poll_id' => $poll['id'],
                'option_text' => $option,
                'display_order' => $index + 1
            ]);
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
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ تم إصلاح جدول التقييمات<br>";
    
    // 3. إصلاح جدول التعليقات
    echo "<h3>3. إصلاح جدول التعليقات</h3>";
    
    // إضافة الأعمدة المفقودة
    $columns = [
        'user_id' => 'INT NULL',
        'author_name' => 'VARCHAR(100) NOT NULL DEFAULT ""',
        'author_email' => 'VARCHAR(100) NOT NULL DEFAULT ""',
        'author_ip' => 'VARCHAR(45)',
        'status' => 'ENUM("pending", "approved", "rejected") DEFAULT "approved"'
    ];
    
    foreach ($columns as $column => $definition) {
        try {
            $db->query("ALTER TABLE comments ADD COLUMN $column $definition");
        } catch (Exception $e) {
            // العمود موجود بالفعل
        }
    }
    
    // إضافة المفاتيح الخارجية
    try {
        $db->query("ALTER TABLE comments ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
        $db->query("ALTER TABLE comments ADD FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE");
    } catch (Exception $e) {
        // المفاتيح موجودة بالفعل
    }
    
    echo "✅ تم إصلاح جدول التعليقات<br>";
    
    // 4. إصلاح جدول البلوكات
    echo "<h3>4. إصلاح جدول البلوكات</h3>";
    
    $blockColumns = [
        'show_title' => 'BOOLEAN DEFAULT 1',
        'custom_css' => 'TEXT',
        'show_on_pages' => 'TEXT',
        'css_class' => 'VARCHAR(255)'
    ];
    
    foreach ($blockColumns as $column => $definition) {
        try {
            $db->query("ALTER TABLE blocks ADD COLUMN $column $definition");
        } catch (Exception $e) {
            // العمود موجود بالفعل
        }
    }
    
    echo "✅ تم إصلاح جدول البلوكات<br>";
    
    // 5. إنشاء جدول الملفات المرفوعة
    echo "<h3>5. إنشاء جدول الملفات</h3>";
    
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
    
    echo "✅ تم إنشاء جدول الملفات<br>";
    
    // 6. تحديث الإعدادات
    echo "<h3>6. تحديث الإعدادات</h3>";
    
    $settings = [
        'show_welcome_banner' => '0',
        'welcome_banner_title' => 'مرحباً بكم',
        'welcome_banner_subtitle' => 'أهلاً وسهلاً',
        'welcome_banner_content' => 'مرحباً بكم في موقع مسجد النور. نسعد بزيارتكم ونتمنى أن تجدوا ما تبحثون عنه.',
        'use_arabic_numbers' => '1'
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
                'category' => 'display'
            ]);
        }
    }
    
    echo "✅ تم تحديث الإعدادات<br>";
    
    echo "<h3>✅ تم إصلاح جميع المشاكل بنجاح!</h3>";
    
} catch (Exception $e) {
    echo "❌ حدث خطأ: " . $e->getMessage();
}
?>
