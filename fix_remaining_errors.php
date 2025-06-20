<?php
/**
 * إصلاح الأخطاء المتبقية
 */

require_once 'config/config.php';

echo "<h2>إصلاح الأخطاء المتبقية</h2>";

try {
    // إنشاء جدول التعليقات إذا لم يكن موجوداً
    $db->query("
        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_id INT,
            user_id INT NULL,
            content TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_page_id (page_id),
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول التعليقات جاهز<br>";

    // إنشاء جدول البلوكات إذا لم يكن موجوداً
    $db->query("
        CREATE TABLE IF NOT EXISTS blocks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            block_type ENUM('custom', 'prayer_times', 'weather', 'recent_pages', 'visitor_stats', 'quran_verse', 'hadith', 'social_links', 'quick_links') DEFAULT 'custom',
            position ENUM('top', 'bottom', 'left', 'right', 'center') DEFAULT 'right',
            display_order INT DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            show_title BOOLEAN DEFAULT TRUE,
            css_class VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_position (position),
            INDEX idx_status (status),
            INDEX idx_order (display_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول البلوكات جاهز<br>";

    // إضافة بلوكات افتراضية
    $defaultBlocks = [
        [
            'title' => 'أوقات الصلاة',
            'content' => '',
            'block_type' => 'prayer_times',
            'position' => 'right',
            'display_order' => 1
        ],
        [
            'title' => 'الطقس',
            'content' => '',
            'block_type' => 'weather',
            'position' => 'right',
            'display_order' => 2
        ],
        [
            'title' => 'آية اليوم',
            'content' => '',
            'block_type' => 'quran_verse',
            'position' => 'right',
            'display_order' => 3
        ]
    ];

    foreach ($defaultBlocks as $block) {
        $existing = $db->fetchOne("SELECT id FROM blocks WHERE title = ?", [$block['title']]);
        if (!$existing) {
            $db->insert('blocks', $block);
            echo "✅ تم إضافة بلوك: " . $block['title'] . "<br>";
        }
    }

    // إنشاء جدول الإعدادات إذا لم يكن موجوداً
    $db->query("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            setting_type ENUM('text', 'textarea', 'number', 'boolean', 'select') DEFAULT 'text',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_key (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول الإعدادات جاهز<br>";

    // إضافة إعدادات افتراضية
    $defaultSettings = [
        ['setting_key' => 'site_name', 'setting_value' => 'مسجد النور', 'description' => 'اسم الموقع'],
        ['setting_key' => 'site_description', 'setting_value' => 'موقع مسجد النور الرسمي', 'description' => 'وصف الموقع'],
        ['setting_key' => 'primary_color', 'setting_value' => '#2c5530', 'description' => 'اللون الأساسي'],
        ['setting_key' => 'secondary_color', 'setting_value' => '#1e3a1e', 'description' => 'اللون الثانوي'],
        ['setting_key' => 'mosque_address', 'setting_value' => 'شارع الملك فهد، الرياض', 'description' => 'عنوان المسجد'],
        ['setting_key' => 'mosque_phone', 'setting_value' => '0112345678', 'description' => 'هاتف المسجد'],
        ['setting_key' => 'mosque_email', 'setting_value' => 'info@mosque.com', 'description' => 'بريد المسجد'],
        ['setting_key' => 'prayer_times_city', 'setting_value' => 'الرياض', 'description' => 'مدينة أوقات الصلاة'],
        ['setting_key' => 'weather_city', 'setting_value' => 'الرياض', 'description' => 'مدينة الطقس']
    ];

    foreach ($defaultSettings as $setting) {
        $existing = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$setting['setting_key']]);
        if (!$existing) {
            $db->insert('settings', $setting);
            echo "✅ تم إضافة إعداد: " . $setting['setting_key'] . "<br>";
        }
    }

    // إنشاء جدول الزوار إذا لم يكن موجوداً
    $db->query("
        CREATE TABLE IF NOT EXISTS visitors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            page_url VARCHAR(500),
            visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            user_id INT NULL,
            INDEX idx_ip (ip_address),
            INDEX idx_visit_time (visit_time),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول الزوار جاهز<br>";

    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>✅ تم إصلاح جميع الأخطاء بنجاح!</h3>";
    echo "<p>يمكنك الآن:</p>";
    echo "<ul>";
    echo "<li><a href='index.php'>زيارة الصفحة الرئيسية</a></li>";
    echo "<li><a href='admin/index.php'>دخول لوحة التحكم</a></li>";
    echo "<li><a href='login.php'>تسجيل الدخول</a></li>";
    echo "</ul>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #721c24;'>❌ حدث خطأ:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
