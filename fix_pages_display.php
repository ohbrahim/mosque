<?php
// هذا الملف لإصلاح مشكلة عدم ظهور الصفحات
require_once 'config/config.php';

// التحقق من وجود جدول الصفحات
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'pages'");
    
    if (!$tableExists) {
        echo "<h3>جدول الصفحات غير موجود. جاري إنشاء الجدول...</h3>";
        
        // إنشاء جدول الصفحات
        $db->query("
            CREATE TABLE IF NOT EXISTS `pages` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `title` varchar(255) NOT NULL,
              `slug` varchar(255) NOT NULL,
              `content` text DEFAULT NULL,
              `excerpt` text DEFAULT NULL,
              `featured_image` varchar(255) DEFAULT NULL,
              `meta_title` varchar(255) DEFAULT NULL,
              `meta_description` text DEFAULT NULL,
              `meta_keywords` varchar(255) DEFAULT NULL,
              `status` enum('published','draft','scheduled') NOT NULL DEFAULT 'draft',
              `visibility` enum('public','private','password','members_only') NOT NULL DEFAULT 'public',
              `password` varchar(255) DEFAULT NULL,
              `allow_comments` tinyint(1) NOT NULL DEFAULT 1,
              `allow_ratings` tinyint(1) NOT NULL DEFAULT 1,
              `is_featured` tinyint(1) NOT NULL DEFAULT 0,
              `is_sticky` tinyint(1) NOT NULL DEFAULT 0,
              `template` varchar(50) DEFAULT 'default',
              `category_id` int(11) DEFAULT NULL,
              `author_id` int(11) DEFAULT NULL,
              `editor_id` int(11) DEFAULT NULL,
              `views_count` int(11) NOT NULL DEFAULT 0,
              `published_at` datetime DEFAULT NULL,
              `scheduled_at` datetime DEFAULT NULL,
              `created_at` datetime NOT NULL DEFAULT current_timestamp(),
              `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `slug` (`slug`),
              KEY `category_id` (`category_id`),
              KEY `author_id` (`author_id`),
              KEY `status` (`status`),
              KEY `is_featured` (`is_featured`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        echo "<h3>تم إنشاء جدول الصفحات بنجاح!</h3>";
    }
    
    // التحقق من وجود صفحات
    $pagesCount = $db->fetchOne("SELECT COUNT(*) as count FROM pages")['count'];
    
    if ($pagesCount == 0) {
        echo "<h3>لا توجد صفحات. جاري إضافة صفحات افتراضية...</h3>";
        
        // إضافة صفحة الرئيسية
        $db->insert('pages', [
            'title' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'content' => '<h2>مرحباً بكم في موقع مسجد النور</h2><p>هذا هو موقع مسجد النور الرسمي. يمكنكم من خلاله متابعة أخبار المسجد والأنشطة والفعاليات.</p><p>كما يمكنكم الاطلاع على أوقات الصلاة والدروس والمحاضرات.</p>',
            'excerpt' => 'الصفحة الرئيسية لموقع مسجد النور',
            'status' => 'published',
            'visibility' => 'public',
            'allow_comments' => 1,
            'allow_ratings' => 1,
            'is_featured' => 1,
            'author_id' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ]);
        
        // إضافة صفحة عن المسجد
        $db->insert('pages', [
            'title' => 'عن المسجد',
            'slug' => 'about',
            'content' => '<h2>نبذة عن مسجد النور</h2><p>تأسس مسجد النور في عام 1430هـ، ويعد من أكبر المساجد في المنطقة.</p><p>يتسع المسجد لأكثر من 1000 مصلي، ويضم مكتبة إسلامية ومركزاً لتحفيظ القرآن الكريم.</p>',
            'excerpt' => 'نبذة تعريفية عن مسجد النور وتاريخه',
            'status' => 'published',
            'visibility' => 'public',
            'allow_comments' => 1,
            'allow_ratings' => 1,
            'is_featured' => 1,
            'author_id' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ]);
        
        // إضافة صفحة الخدمات
        $db->insert('pages', [
            'title' => 'الخدمات',
            'slug' => 'services',
            'content' => '<h2>خدمات مسجد النور</h2><ul><li>حلقات تحفيظ القرآن الكريم</li><li>دروس في العلوم الشرعية</li><li>محاضرات وندوات</li><li>مكتبة إسلامية</li><li>خدمات اجتماعية</li></ul>',
            'excerpt' => 'الخدمات التي يقدمها مسجد النور للمجتمع',
            'status' => 'published',
            'visibility' => 'public',
            'allow_comments' => 1,
            'allow_ratings' => 1,
            'is_featured' => 1,
            'author_id' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ]);
        
        // إضافة صفحة الفعاليات
        $db->insert('pages', [
            'title' => 'الفعاليات',
            'slug' => 'events',
            'content' => '<h2>فعاليات مسجد النور</h2><p>يقيم المسجد العديد من الفعاليات والأنشطة على مدار العام، ومنها:</p><ul><li>المسابقة السنوية لحفظ القرآن الكريم</li><li>الدورات العلمية المكثفة</li><li>الأمسيات الدعوية</li><li>برامج رمضانية</li><li>فعاليات العيدين</li></ul>',
            'excerpt' => 'الفعاليات والأنشطة التي يقيمها مسجد النور',
            'status' => 'published',
            'visibility' => 'public',
            'allow_comments' => 1,
            'allow_ratings' => 1,
            'is_featured' => 1,
            'author_id' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ]);
        
        // إضافة صفحة اتصل بنا
        $db->insert('pages', [
            'title' => 'اتصل بنا',
            'slug' => 'contact',
            'content' => '<h2>اتصل بنا</h2><p>يمكنكم التواصل معنا من خلال:</p><ul><li>العنوان: شارع الرئيسي، المدينة</li><li>الهاتف: 0123456789</li><li>البريد الإلكتروني: info@mosque.com</li></ul><h3>نموذج التواصل</h3><p>يمكنكم أيضاً إرسال رسالة مباشرة من خلال النموذج التالي:</p>',
            'excerpt' => 'معلومات الاتصال بمسجد النور',
            'status' => 'published',
            'visibility' => 'public',
            'allow_comments' => 0,
            'allow_ratings' => 0,
            'is_featured' => 0,
            'author_id' => 1,
            'published_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "<h3>تم إضافة الصفحات الافتراضية بنجاح!</h3>";
    } else {
        echo "<h3>يوجد {$pagesCount} صفحة في قاعدة البيانات.</h3>";
    }
    
    echo "<p>يمكنك الآن <a href='index.php'>العودة إلى الصفحة الرئيسية</a>.</p>";
    
} catch (Exception $e) {
    echo "<h3>حدث خطأ: " . $e->getMessage() . "</h3>";
}
?>
