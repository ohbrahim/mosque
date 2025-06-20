<?php
/**
 * إصلاح المشاكل النهائية
 */
require_once 'config/config.php';

// إنشاء جدول التقييمات إذا لم يكن موجوداً
$db->query("
    CREATE TABLE IF NOT EXISTS `ratings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `page_id` int(11) NOT NULL,
      `user_id` int(11) DEFAULT NULL,
      `user_ip` varchar(45) DEFAULT NULL,
      `rating` tinyint(1) NOT NULL,
      `created_at` datetime NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `page_id` (`page_id`),
      KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// إضافة عمود متوسط التقييم في جدول الصفحات إذا لم يكن موجوداً
$result = $db->query("SHOW COLUMNS FROM `pages` LIKE 'average_rating'");
if (!$result->rowCount()) {
    $db->query("ALTER TABLE `pages` ADD `average_rating` DECIMAL(3,2) DEFAULT NULL");
}

// إضافة عمود عدد التقييمات في جدول الصفحات إذا لم يكن موجوداً
$result = $db->query("SHOW COLUMNS FROM `pages` LIKE 'ratings_count'");
if (!$result->rowCount()) {
    $db->query("ALTER TABLE `pages` ADD `ratings_count` INT DEFAULT 0");
}

// تحديث إعداد الموافقة التلقائية على التعليقات
$db->query("
    INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `display_name`, `description`) 
    VALUES ('auto_approve_comments', '0', 'boolean', 'التعليقات', 'الموافقة التلقائية', 'الموافقة على التعليقات تلقائياً')
    ON DUPLICATE KEY UPDATE `setting_value` = '0'
");

// تحديث إعداد عرض البلوكات المخصصة
$db->query("
    INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `display_name`, `description`) 
    VALUES ('allow_custom_html', '1', 'boolean', 'البلوكات', 'السماح بـ HTML مخصص', 'السماح باستخدام HTML مخصص في البلوكات')
    ON DUPLICATE KEY UPDATE `setting_value` = '1'
");

// إضافة أنواع بلوكات جديدة
$blockTypes = [
    'custom' => 'محتوى مخصص',
    'html' => 'HTML مخصص',
    'text' => 'نص عادي',
    'announcement' => 'إعلان',
    'prayer_times' => 'أوقات الصلاة',
    'weather' => 'الطقس',
    'iframe' => 'إطار خارجي (iframe)',
    'marquee' => 'نص متحرك',
    'recent_pages' => 'صفحات حديثة',
    'visitor_stats' => 'إحصائيات الزوار',
    'quran_verse' => 'آية قرآنية',
    'hadith' => 'حديث نبوي',
    'social_links' => 'روابط التواصل',
    'quick_links' => 'روابط سريعة'
];

// إضافة أنواع البلوكات إلى قاعدة البيانات
foreach ($blockTypes as $key => $name) {
    $db->query("
        INSERT INTO `block_types` (`type_key`, `type_name`) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE `type_name` = ?
    ", [$key, $name, $name]);
}

// تحديث عمود block_type في جدول blocks ليكون ENUM
$db->query("
    ALTER TABLE `blocks` 
    MODIFY COLUMN `block_type` VARCHAR(50) NOT NULL DEFAULT 'custom'
");

echo "تم إصلاح المشاكل النهائية بنجاح!";
?>
