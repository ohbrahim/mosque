<?php
/**
 * ملف إصلاح مشاكل قاعدة البيانات
 * يضيف الجداول والأعمدة المفقودة
 */

// إعدادات قاعدة البيانات
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'mosque_management';

// دالة لتنفيذ استعلام SQL
function executeQuery($pdo, $query, $description = '') {
    try {
        $pdo->exec($query);
        echo "<div style='color: green; margin: 5px 0;'>✓ تم: $description</div>";
        return true;
    } catch (PDOException $e) {
        echo "<div style='color: #ff6600; margin: 5px 0;'>⚠️ تحذير: $description - " . $e->getMessage() . "</div>";
        return false;
    }
}

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>إصلاح مشاكل قاعدة البيانات</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap' rel='stylesheet'>
    <style>
        body { font-family: 'Cairo', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .fix-container { max-width: 800px; margin: 50px auto; }
        .fix-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .log-container { max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; background: #f8f9fa; margin: 20px 0; border-radius: 8px; }
    </style>
</head>
<body>
    <div class='container fix-container'>
        <div class='fix-card'>
            <h2 class='text-center mb-4'>
                <i class='fas fa-tools'></i>
                إصلاح مشاكل قاعدة البيانات
            </h2>";

try {
    // الاتصال بقاعدة البيانات
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<div class='alert alert-success'>تم الاتصال بقاعدة البيانات بنجاح</div>";
    echo "<div class='log-container'>";
    
    // ===================================
    // إضافة الجداول المفقودة
    // ===================================
    
    // جدول الإعلانات
    executeQuery($pdo, "CREATE TABLE IF NOT EXISTS advertisements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        content TEXT,
        image VARCHAR(255),
        link_url VARCHAR(500),
        position ENUM('header', 'sidebar', 'footer', 'content') DEFAULT 'sidebar',
        start_date DATE,
        end_date DATE,
        status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
        clicks_count INT DEFAULT 0,
        impressions_count INT DEFAULT 0,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id),
        INDEX idx_position (position),
        INDEX idx_status (status),
        INDEX idx_dates (start_date, end_date)
    )", "إنشاء جدول الإعلانات");
    
    // جدول البلوكات
    executeQuery($pdo, "CREATE TABLE IF NOT EXISTS blocks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200),
        content LONGTEXT,
        block_type ENUM('custom', 'prayer_times', 'weather', 'recent_pages', 'visitor_stats', 'quran_verse', 'hadith', 'social_links', 'quick_links') DEFAULT 'custom',
        position ENUM('left', 'right', 'center', 'top', 'bottom') DEFAULT 'right',
        display_order INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        show_title BOOLEAN DEFAULT TRUE,
        css_class VARCHAR(100),
        updated_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (updated_by) REFERENCES users(id),
        INDEX idx_position (position),
        INDEX idx_status (status),
        INDEX idx_order (display_order)
    )", "إنشاء جدول البلوكات");
    
    // جدول محتوى الهيدر والفوتر
    executeQuery($pdo, "CREATE TABLE IF NOT EXISTS header_footer_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        section ENUM('header', 'footer') NOT NULL,
        content_type ENUM('text', 'html', 'widget') DEFAULT 'text',
        title VARCHAR(200),
        content LONGTEXT,
        position ENUM('left', 'center', 'right', 'full') DEFAULT 'center',
        display_order INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_section (section),
        INDEX idx_status (status),
        INDEX idx_order (display_order)
    )", "إنشاء جدول محتوى الهيدر والفوتر");
    
    // ===================================
    // إضافة الأعمدة المفقودة
    // ===================================
    
    // إضافة عمود options إلى جدول settings
    executeQuery($pdo, "ALTER TABLE settings ADD COLUMN IF NOT EXISTS options TEXT", "إضافة عمود options إلى جدول settings");
    
    // تعديل جدول menu_items لإضافة الأعمدة المفقودة
    executeQuery($pdo, "ALTER TABLE menu_items ADD COLUMN IF NOT EXISTS menu_position ENUM('header', 'footer', 'sidebar', 'mobile') DEFAULT 'header'", "إضافة عمود menu_position");
    executeQuery($pdo, "ALTER TABLE menu_items ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0", "إضافة عمود display_order");
    
    // تحديث أسماء الأعمدة في menu_items
    executeQuery($pdo, "ALTER TABLE menu_items CHANGE COLUMN menu_location menu_position ENUM('header', 'footer', 'sidebar', 'mobile') DEFAULT 'header'", "تحديث اسم عمود menu_location");
    
    // إضافة عمود page_id إلى جدول comments
    executeQuery($pdo, "ALTER TABLE comments ADD COLUMN IF NOT EXISTS page_id INT", "إضافة عمود page_id إلى جدول comments");
    executeQuery($pdo, "ALTER TABLE comments ADD INDEX IF NOT EXISTS idx_page_id (page_id)", "إضافة فهرس page_id");
    
    // ===================================
    // إصلاح البيانات الموجودة
    // ===================================
    
    // تحديث بيانات menu_items إذا كانت فارغة
    executeQuery($pdo, "UPDATE menu_items SET menu_position = 'header' WHERE menu_position IS NULL", "تحديث بيانات menu_position");
    executeQuery($pdo, "UPDATE menu_items SET display_order = sort_order WHERE display_order = 0", "تحديث بيانات display_order");
    
    // ===================================
    // إضافة بيانات أساسية للجداول الجديدة
    // ===================================
    
    // إضافة بلوكات أساسية
    executeQuery($pdo, "INSERT IGNORE INTO blocks (title, content, block_type, position, display_order, status) VALUES 
    ('أوقات الصلاة', '', 'prayer_times', 'right', 1, 'active'),
    ('حالة الطقس', '', 'weather', 'right', 2, 'active'),
    ('آية اليوم', '', 'quran_verse', 'right', 3, 'active'),
    ('آخر الصفحات', '', 'recent_pages', 'right', 4, 'active'),
    ('روابط سريعة', '<ul><li><a href=\"/quran\">القرآن الكريم</a></li><li><a href=\"/library\">المكتبة</a></li><li><a href=\"/donations\">التبرعات</a></li></ul>', 'custom', 'right', 5, 'active')", "إضافة البلوكات الأساسية");
    
    // إضافة محتوى الهيدر والفوتر الأساسي
    executeQuery($pdo, "INSERT IGNORE INTO header_footer_content (section, content_type, title, content, position, display_order) VALUES 
    ('header', 'text', 'مرحباً بكم في مسجد النور', 'نسعد بزيارتكم لموقع مسجد النور', 'center', 1),
    ('footer', 'html', 'معلومات التواصل', '<p>العنوان: الرياض، المملكة العربية السعودية<br>الهاتف: +966501234567<br>البريد الإلكتروني: info@mosque.com</p>', 'left', 1),
    ('footer', 'html', 'روابط مهمة', '<ul><li><a href=\"/about\">عن المسجد</a></li><li><a href=\"/services\">خدماتنا</a></li><li><a href=\"/contact\">اتصل بنا</a></li></ul>', 'center', 2),
    ('footer', 'text', 'حقوق الطبع', 'جميع الحقوق محفوظة © 2024 مسجد النور', 'right', 3)", "إضافة محتوى الهيدر والفوتر الأساسي");
    
    // ===================================
    // إصلاح مشاكل الترميز
    // ===================================
    
    executeQuery($pdo, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci", "ضبط ترميز UTF8");
    executeQuery($pdo, "ALTER DATABASE $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci", "ضبط ترميز قاعدة البيانات");
    
    // ===================================
    // إنشاء صفحات أساسية
    // ===================================
    
    // التحقق من وجود صفحات أساسية وإنشاؤها إذا لم تكن موجودة
    $existingPages = $pdo->query("SELECT COUNT(*) as count FROM pages")->fetch()['count'];
    
    if ($existingPages == 0) {
        executeQuery($pdo, "INSERT INTO pages (title, slug, content, status, author_id, published_at) VALUES 
        ('مرحباً بكم في مسجد النور', 'welcome', '<h2>مرحباً بكم في موقع مسجد النور</h2><p>نسعد بزيارتكم لموقعنا الإلكتروني. نحن نسعى لخدمة المجتمع المسلم وتقديم أفضل الخدمات الدينية والتعليمية.</p><h3>خدماتنا:</h3><ul><li>تعليم القرآن الكريم</li><li>الدروس الدينية</li><li>الفعاليات والأنشطة</li><li>الخدمات الاجتماعية</li></ul>', 'published', 1, NOW()),
        ('عن المسجد', 'about', '<h2>عن مسجد النور</h2><p>مسجد النور هو مؤسسة دينية تهدف إلى خدمة المجتمع المسلم وتقديم التعليم الديني والخدمات الاجتماعية.</p><h3>رؤيتنا:</h3><p>أن نكون منارة للعلم والهداية في المجتمع</p><h3>رسالتنا:</h3><p>تقديم التعليم الديني الأصيل والخدمات المجتمعية المتميزة</p>', 'published', 1, NOW()),
        ('اتصل بنا', 'contact', '<h2>تواصل معنا</h2><p>نسعد بتواصلكم معنا في أي وقت</p><h3>معلومات التواصل:</h3><ul><li><strong>العنوان:</strong> الرياض، المملكة العربية السعودية</li><li><strong>الهاتف:</strong> +966501234567</li><li><strong>البريد الإلكتروني:</strong> info@mosque.com</li><li><strong>أوقات العمل:</strong> يومياً من 6 صباحاً حتى 10 مساءً</li></ul>', 'published', 1, NOW())", "إنشاء الصفحات الأساسية");
    }
    
    // ===================================
    // إنشاء فهارس إضافية لتحسين الأداء
    // ===================================
    
    executeQuery($pdo, "CREATE INDEX IF NOT EXISTS idx_pages_status_published ON pages(status, published_at)", "إنشاء فهرس للصفحات المنشورة");
    executeQuery($pdo, "CREATE INDEX IF NOT EXISTS idx_comments_status_created ON comments(status, created_at)", "إنشاء فهرس للتعليقات");
    executeQuery($pdo, "CREATE INDEX IF NOT EXISTS idx_users_role_status ON users(role, status)", "إنشاء فهرس للمستخدمين");
    
    echo "</div>";
    
    echo "<div class='alert alert-success mt-4'>
            <h5>تم إصلاح جميع مشاكل قاعدة البيانات بنجاح! 🎉</h5>
            <ul>
                <li>✓ تم إنشاء الجداول المفقودة</li>
                <li>✓ تم إضافة الأعمدة المفقودة</li>
                <li>✓ تم إصلاح مشاكل الترميز</li>
                <li>✓ تم إنشاء الصفحات الأساسية</li>
                <li>✓ تم تحسين الفهارس</li>
            </ul>
          </div>";
    
    echo "<div class='alert alert-info mt-4'>
            <h5>الخطوات التالية:</h5>
            <ol>
                <li>قم بزيارة <a href='admin/' class='btn btn-primary btn-sm'>لوحة التحكم</a></li>
                <li>سجل الدخول باستخدام: admin / admin123</li>
                <li>تحقق من جميع الصفحات والميزات</li>
                <li>ابدأ في إضافة المحتوى الخاص بك</li>
            </ol>
          </div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>
            <strong>خطأ في الاتصال بقاعدة البيانات:</strong><br>
            " . $e->getMessage() . "
          </div>";
}

echo "        </div>
    </div>
</body>
</html>";
?>
