<?php
/**
 * إصلاح جميع الأخطاء الحرجة في النظام
 */

require_once 'config/config.php';

echo "<h2>إصلاح الأخطاء الحرجة</h2>";

try {
    // 1. إصلاح جدول الاستطلاعات
    echo "<h3>1. إصلاح جدول الاستطلاعات...</h3>";
    
    // التحقق من وجود الجداول وإنشاؤها
    $db->query("
        CREATE TABLE IF NOT EXISTS `polls` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(500) NOT NULL,
          `description` text DEFAULT NULL,
          `status` enum('active','inactive') DEFAULT 'inactive',
          `start_date` date DEFAULT NULL,
          `end_date` date DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS `poll_options` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `poll_id` int(11) NOT NULL,
          `option_text` varchar(500) NOT NULL,
          `display_order` int(11) NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`),
          KEY `poll_id` (`poll_id`),
          FOREIGN KEY (`poll_id`) REFERENCES `polls`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    $db->query("
        CREATE TABLE IF NOT EXISTS `poll_votes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `poll_id` int(11) NOT NULL,
          `option_id` int(11) NOT NULL,
          `user_id` int(11) DEFAULT NULL,
          `voter_ip` varchar(45) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `poll_id` (`poll_id`),
          KEY `option_id` (`option_id`),
          KEY `user_id` (`user_id`),
          FOREIGN KEY (`poll_id`) REFERENCES `polls`(`id`) ON DELETE CASCADE,
          FOREIGN KEY (`option_id`) REFERENCES `poll_options`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✓ تم إصلاح جداول الاستطلاعات<br>";
    
    // 2. إصلاح جدول التعليقات
    echo "<h3>2. إصلاح جدول التعليقات...</h3>";
    
    $db->query("
        CREATE TABLE IF NOT EXISTS `comments` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `page_id` int(11) NOT NULL,
          `user_id` int(11) DEFAULT NULL,
          `author_name` varchar(100) DEFAULT NULL,
          `author_email` varchar(100) DEFAULT NULL,
          `author_ip` varchar(45) DEFAULT NULL,
          `content` text NOT NULL,
          `status` enum('pending','approved','rejected','spam') DEFAULT 'pending',
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `page_id` (`page_id`),
          KEY `user_id` (`user_id`),
          KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✓ تم إصلاح جدول التعليقات<br>";
    
    // 3. إصلاح جدول البلوكات
    echo "<h3>3. إصلاح جدول البلوكات...</h3>";
    
    $db->query("
        CREATE TABLE IF NOT EXISTS `blocks` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(200) NOT NULL,
          `content` longtext DEFAULT NULL,
          `block_type` varchar(50) NOT NULL DEFAULT 'custom',
          `position` enum('left','right','top','bottom','center') NOT NULL DEFAULT 'right',
          `status` enum('active','inactive') NOT NULL DEFAULT 'active',
          `show_title` tinyint(1) NOT NULL DEFAULT 1,
          `css_class` varchar(100) DEFAULT NULL,
          `display_order` int(11) NOT NULL DEFAULT 0,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `position` (`position`),
          KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // إضافة بلوكات افتراضية
    $defaultBlocks = [
        [
            'title' => 'أوقات الصلاة',
            'content' => '',
            'block_type' => 'prayer_times',
            'position' => 'right',
            'status' => 'active',
            'display_order' => 1
        ],
        [
            'title' => 'الطقس',
            'content' => '',
            'block_type' => 'weather',
            'position' => 'right',
            'status' => 'active',
            'display_order' => 2
        ],
        [
            'title' => 'آية قرآنية',
            'content' => '',
            'block_type' => 'quran_verse',
            'position' => 'left',
            'status' => 'active',
            'display_order' => 1
        ]
    ];
    
    foreach ($defaultBlocks as $block) {
        $existing = $db->fetchOne("SELECT id FROM blocks WHERE title = ?", [$block['title']]);
        if (!$existing) {
            $db->insert('blocks', $block);
        }
    }
    
    echo "✓ تم إصلاح جدول البلوكات<br>";
    
    // 4. إصلاح جدول الإعدادات
    echo "<h3>4. إصلاح جدول الإعدادات...</h3>";
    
    $db->query("
        CREATE TABLE IF NOT EXISTS `settings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `setting_key` varchar(100) NOT NULL,
          `setting_value` text DEFAULT NULL,
          `setting_type` enum('text','textarea','number','email','url','color','select','boolean','file') NOT NULL DEFAULT 'text',
          `setting_group` varchar(50) DEFAULT 'عام',
          `display_name` varchar(200) NOT NULL,
          `description` text DEFAULT NULL,
          `setting_options` text DEFAULT NULL,
          `is_required` tinyint(1) NOT NULL DEFAULT 0,
          `display_order` int(11) NOT NULL DEFAULT 0,
          `created_at` datetime NOT NULL DEFAULT current_timestamp(),
          `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // إضافة إعدادات افتراضية
    $defaultSettings = [
        ['site_name', 'مسجد النور', 'text', 'عام', 'اسم الموقع', 'اسم الموقع الذي يظهر في العنوان', null, 1, 1],
        ['site_description', 'موقع مسجد النور الرسمي', 'textarea', 'عام', 'وصف الموقع', 'وصف مختصر للموقع', null, 0, 2],
        ['admin_email', 'admin@mosque.com', 'email', 'عام', 'بريد المدير', 'البريد الإلكتروني للمدير', null, 1, 3],
        ['enable_comments', '1', 'boolean', 'التعليقات', 'تفعيل التعليقات', 'السماح بالتعليقات على الصفحات', null, 0, 4],
        ['auto_approve_comments', '0', 'boolean', 'التعليقات', 'الموافقة التلقائية', 'الموافقة على التعليقات تلقائياً', null, 0, 5]
    ];
    
    foreach ($defaultSettings as $setting) {
        $existing = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$setting[0]]);
        if (!$existing) {
            $db->insert('settings', [
                'setting_key' => $setting[0],
                'setting_value' => $setting[1],
                'setting_type' => $setting[2],
                'setting_group' => $setting[3],
                'display_name' => $setting[4],
                'description' => $setting[5],
                'setting_options' => $setting[6],
                'is_required' => $setting[7],
                'display_order' => $setting[8]
            ]);
        }
    }
    
    echo "✓ تم إصلاح جدول الإعدادات<br>";
    
    // 5. إصلاح جدول الزوار
    echo "<h3>5. إصلاح جدول الزوار...</h3>";
    
    $db->query("
        CREATE TABLE IF NOT EXISTS `visitors` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ip_address` varchar(45) NOT NULL,
          `user_agent` text DEFAULT NULL,
          `page_url` varchar(500) DEFAULT NULL,
          `visit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `user_id` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `ip_address` (`ip_address`),
          KEY `visit_time` (`visit_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✓ تم إصلاح جدول الزوار<br>";
    
    echo "<h3>✅ تم إصلاح جميع الأخطاء الحرجة بنجاح!</h3>";
    
} catch (Exception $e) {
    echo "<div style='color: red;'>خطأ: " . $e->getMessage() . "</div>";
}
?>
