<?php
/**
 * تشغيل جميع الإصلاحات النهائية
 */
require_once 'config/config.php';

// تشغيل ملف إصلاح المشاكل النهائية
include_once 'fix_final_issues.php';

// إضافة بلوكات افتراضية
$defaultBlocks = [
    // بلوك أوقات الصلاة
    [
        'title' => 'أوقات الصلاة',
        'content' => '<iframe id="iframe" title="prayerWidget" class="widget-m-top" style="width: 100%; height: 358px; border: 1px solid #ddd;" scrolling="no" src="https://www.islamicfinder.org/prayer-widget/2485801/shafi/15/0/18/17"> </iframe>',
        'block_type' => 'iframe',
        'position' => 'right',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'prayer-times-block',
        'custom_css' => '',
        'display_order' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ],
    
    // بلوك إعلان متحرك
    [
        'title' => 'إعلانات هامة',
        'content' => '<marquee direction="right"> <p> <font size="4" color="red"> إنطلاق بث قنوات دروس الدعم لجميع المستويات والأطوار على موقع الديوان الوطني للتعليم والتكوين عن بعد على الرابط التالي : http://www.onefd.edu.dz/soutien-scolaire   "  </font></p></div><strong> </marquee>',
        'block_type' => 'marquee',
        'position' => 'top',
        'status' => 'active',
        'show_title' => 0,
        'css_class' => 'announcement-block',
        'custom_css' => '',
        'display_order' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ],
    
    // بلوك آية قرآنية
    [
        'title' => 'آية اليوم',
        'content' => '<div class="quran-verse text-center p-3 bg-light">
            <i class="fas fa-book-open text-success mb-2" style="font-size: 2rem;"></i>
            <p class="mb-2 fs-5">وَمَا خَلَقْتُ الْجِنَّ وَالْإِنسَ إِلَّا لِيَعْبُدُونِ</p>
            <small class="text-muted">سورة الذاريات - آية 56</small>
        </div>',
        'block_type' => 'quran_verse',
        'position' => 'right',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'quran-block',
        'custom_css' => '.quran-verse { border-radius: 10px; }',
        'display_order' => 2,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ],
    
    // بلوك روابط سريعة
    [
        'title' => 'روابط سريعة',
        'content' => '<div class="list-group">
            <a href="#" class="list-group-item list-group-item-action">جدول الدروس</a>
            <a href="#" class="list-group-item list-group-item-action">أوقات الصلاة</a>
            <a href="#" class="list-group-item list-group-item-action">المكتبة الإسلامية</a>
            <a href="#" class="list-group-item list-group-item-action">التبرعات</a>
            <a href="#" class="list-group-item list-group-item-action">اتصل بنا</a>
        </div>',
        'block_type' => 'quick_links',
        'position' => 'left',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'quick-links-block',
        'custom_css' => '',
        'display_order' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]
];

// إضافة البلوكات الافتراضية
foreach ($defaultBlocks as $block) {
    try {
        // التحقق من وجود بلوك مشابه
        $exists = $db->fetchOne("
            SELECT COUNT(*) as count 
            FROM blocks 
            WHERE title = ? AND position = ?
        ", [$block['title'], $block['position']]);
        
        if ($exists['count'] == 0) {
            $db->insert('blocks', $block);
            echo "تم إضافة بلوك: " . $block['title'] . "<br>";
        }
    } catch (Exception $e) {
        echo "خطأ في إضافة بلوك: " . $block['title'] . " - " . $e->getMessage() . "<br>";
    }
}

// إصلاح جدول التقييمات
try {
    // التحقق من وجود جدول التقييمات
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'ratings'");
    
    if (!$tableExists) {
        // إنشاء جدول التقييمات
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
        echo "تم إنشاء جدول التقييمات<br>";
    }
    
    // إضافة أعمدة التقييم في جدول الصفحات
    $result = $db->query("SHOW COLUMNS FROM `pages` LIKE 'average_rating'");
    if (!$result->rowCount()) {
        $db->query("ALTER TABLE `pages` ADD `average_rating` DECIMAL(3,2) DEFAULT NULL");
        echo "تم إضافة عمود متوسط التقييم في جدول الصفحات<br>";
    }
    
    $result = $db->query("SHOW COLUMNS FROM `pages` LIKE 'ratings_count'");
    if (!$result->rowCount()) {
        $db->query("ALTER TABLE `pages` ADD `ratings_count` INT DEFAULT 0");
        echo "تم إضافة عمود عدد التقييمات في جدول الصفحات<br>";
    }
} catch (Exception $e) {
    echo "خطأ في إصلاح جدول التقييمات: " . $e->getMessage() . "<br>";
}

// إصلاح جدول التعليقات
try {
    // التحقق من وجود عمود الحالة في جدول التعليقات
    $result = $db->query("SHOW COLUMNS FROM `comments` LIKE 'status'");
    if (!$result->rowCount()) {
        $db->query("ALTER TABLE `comments` ADD `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        echo "تم إضافة عمود الحالة في جدول التعليقات<br>";
    }
    
    // تحديث التعليقات القديمة لتكون معتمدة
    $db->query("UPDATE `comments` SET `status` = 'approved' WHERE `status` = ''");
    echo "تم تحديث حالة التعليقات القديمة<br>";
} catch (Exception $e) {
    echo "خطأ في إصلاح جدول التعليقات: " . $e->getMessage() . "<br>";
}

// إصلاح عرض الصور في الصفحة الرئيسية
try {
    // تحديث دالة عرض الصفحات الحديثة
    $db->query("
        INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`, `display_name`, `description`) 
        VALUES ('show_featured_image', '1', 'boolean', 'content', 'عرض الصورة المميزة', 'عرض الصورة المميزة في قائمة الصفحات')
        ON DUPLICATE KEY UPDATE `setting_value` = '1'
    ");
    echo "تم تحديث إعداد عرض الصور المميزة<br>";
} catch (Exception $e) {
    echo "خطأ في تحديث إعداد عرض الصور: " . $e->getMessage() . "<br>";
}

echo "<h3>تم إصلاح جميع المشاكل بنجاح!</h3>";
echo "<p>يمكنك الآن العودة إلى <a href='index.php'>الصفحة الرئيسية</a> أو <a href='admin/index.php'>لوحة التحكم</a>.</p>";
?>
