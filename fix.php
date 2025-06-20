<?php
// إيقاف عرض الأخطاء للمستخدم النهائي
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>إصلاح مشروع إدارة المسجد</h2>";

// التحقق من وجود قاعدة البيانات
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'mosque_management',
    'username' => 'root',
    'password' => ''
];

try {
    // محاولة الاتصال بقاعدة البيانات
    $dsn = "mysql:host={$dbConfig['host']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ تم الاتصال بالخادم بنجاح</p>";
    
    // التحقق من وجود قاعدة البيانات
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbConfig['dbname']}'");
    if ($stmt->rowCount() === 0) {
        // إنشاء قاعدة البيانات إذا لم تكن موجودة
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p>✅ تم إنشاء قاعدة البيانات</p>";
    } else {
        echo "<p>✅ قاعدة البيانات موجودة</p>";
    }
    
    // الاتصال بقاعدة البيانات المحددة
    $pdo = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4", 
                   $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // إنشاء الجداول الأساسية
    $tables = [
        "users" => "
            CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL,
                `email` varchar(100) NOT NULL,
                `password` varchar(255) NOT NULL,
                `full_name` varchar(100) NOT NULL,
                `role` enum('admin','moderator','user') NOT NULL DEFAULT 'user',
                `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active',
                `created_at` datetime NOT NULL,
                `last_login` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `username` (`username`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        "settings" => "
            CREATE TABLE IF NOT EXISTS `settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `setting_key` varchar(50) NOT NULL,
                `setting_value` text DEFAULT NULL,
                `category` varchar(50) DEFAULT 'general',
                `setting_type` varchar(20) DEFAULT 'text',
                `description` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        "pages" => "
            CREATE TABLE IF NOT EXISTS `pages` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `slug` varchar(255) NOT NULL,
                `content` text DEFAULT NULL,
                `excerpt` text DEFAULT NULL,
                `featured_image` varchar(255) DEFAULT NULL,
                `meta_description` varchar(255) DEFAULT NULL,
                `status` enum('published','draft','trash') NOT NULL DEFAULT 'published',
                `views_count` int(11) NOT NULL DEFAULT 0,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                `author_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        "blocks" => "
            CREATE TABLE IF NOT EXISTS `blocks` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `content` text DEFAULT NULL,
                `position` varchar(50) NOT NULL,
                `display_order` int(11) NOT NULL DEFAULT 0,
                `status` enum('active','inactive') NOT NULL DEFAULT 'active',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        "menu_items" => "
            CREATE TABLE IF NOT EXISTS `menu_items` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(100) NOT NULL,
                `url` varchar(255) NOT NULL,
                `icon` varchar(50) DEFAULT NULL,
                `menu_position` varchar(50) NOT NULL,
                `parent_id` int(11) DEFAULT NULL,
                `display_order` int(11) NOT NULL DEFAULT 0,
                `target` varchar(20) DEFAULT '_self',
                `status` enum('active','inactive') NOT NULL DEFAULT 'active',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        "header_footer_content" => "
            CREATE TABLE IF NOT EXISTS `header_footer_content` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(100) DEFAULT NULL,
                `content` text DEFAULT NULL,
                `content_type` enum('text','html','widget') NOT NULL DEFAULT 'text',
                `section` enum('header','footer') NOT NULL,
                `position` varchar(20) NOT NULL DEFAULT 'full',
                `display_order` int(11) NOT NULL DEFAULT 0,
                `status` enum('active','inactive') NOT NULL DEFAULT 'active',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    foreach ($tables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            echo "<p>✅ تم إنشاء/التحقق من جدول {$tableName}</p>";
        } catch (PDOException $e) {
            echo "<p>❌ خطأ في إنشاء جدول {$tableName}: " . $e->getMessage() . "</p>";
        }
    }
    
    // إنشاء مستخدم مدير إذا لم يكن موجوداً
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute(['admin', 'admin@mosque.com', $hashedPassword, 'مدير النظام', 'admin', 'active']);
        echo "<p>✅ تم إنشاء مستخدم المدير</p>";
        echo "<p>اسم المستخدم: admin</p>";
        echo "<p>كلمة المرور: admin123</p>";
    } else {
        echo "<p>✅ مستخدم المدير موجود بالفعل</p>";
    }
    
    // إضافة الإعدادات الافتراضية
    $defaultSettings = [
        'site_name' => 'مسجد النور',
        'site_description' => 'مسجد النور - مكان للعبادة والتعلم والتواصل المجتمعي',
        'header_style' => 'modern',
        'footer_style' => 'modern',
        'header_bg_color' => '#667eea',
        'header_text_color' => '#ffffff',
        'footer_bg_color' => '#2c3e50',
        'footer_text_color' => '#ffffff',
        'contact_email' => 'info@mosque.com',
        'contact_phone' => '+213123456789',
        'contact_address' => 'الجزائر العاصمة، الجزائر'
    ];
    
    foreach ($defaultSettings as $key => $value) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, category) VALUES (?, ?, 'general')");
            $stmt->execute([$key, $value]);
        }
    }
    echo "<p>✅ تم التحقق من الإعدادات الافتراضية</p>";
    
    // إضافة عناصر القائمة الافتراضية
    $stmt = $pdo->query("SELECT COUNT(*) FROM menu_items WHERE menu_position = 'header'");
    if ($stmt->fetchColumn() == 0) {
        $headerMenuItems = [
            ['الرئيسية', 'index.php', 'fas fa-home', 1],
            ['عن المسجد', '?page=about', 'fas fa-mosque', 2],
            ['القرآن الكريم', 'quran.php', 'fas fa-book-quran', 3],
            ['المكتبة', 'library.php', 'fas fa-book', 4],
            ['اتصل بنا', 'contact.php', 'fas fa-envelope', 5]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO menu_items (title, url, icon, menu_position, display_order, target, status) VALUES (?, ?, ?, 'header', ?, '_self', 'active')");
        foreach ($headerMenuItems as $item) {
            $stmt->execute([$item[0], $item[1], $item[2], $item[3]]);
        }
        echo "<p>✅ تم إضافة عناصر قائمة الهيدر</p>";
    } else {
        echo "<p>✅ عناصر قائمة الهيدر موجودة بالفعل</p>";
    }
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM menu_items WHERE menu_position = 'footer'");
    if ($stmt->fetchColumn() == 0) {
        $footerMenuItems = [
            ['الرئيسية', 'index.php', '', 1],
            ['عن المسجد', '?page=about', '', 2],
            ['سياسة الخصوصية', '?page=privacy', '', 3],
            ['اتصل بنا', 'contact.php', '', 4]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO menu_items (title, url, icon, menu_position, display_order, target, status) VALUES (?, ?, ?, 'footer', ?, '_self', 'active')");
        foreach ($footerMenuItems as $item) {
            $stmt->execute([$item[0], $item[1], $item[2], $item[3]]);
        }
        echo "<p>✅ تم إضافة عناصر قائمة الفوتر</p>";
    } else {
        echo "<p>✅ عناصر قائمة الفوتر موجودة بالفعل</p>";
    }
    
    echo "<h3>تم إصلاح قاعدة البيانات بنجاح!</h3>";
    echo "<p><a href='index.php'>الذهاب إلى الصفحة الرئيسية</a></p>";
    echo "<p><a href='login.php'>تسجيل الدخول</a></p>";
    
} catch (PDOException $e) {
    echo "<p>❌ خطأ: " . $e->getMessage() . "</p>";
}
?>
