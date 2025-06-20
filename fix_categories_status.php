<?php
require_once 'config/config.php';

echo "<h2>إصلاح جدول التصنيفات</h2>";

try {
    // التحقق من وجود جدول categories
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'categories'");
    
    if (!$tableExists) {
        echo "<p>إنشاء جدول التصنيفات...</p>";
        
        $createTable = "
        CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            description TEXT,
            parent_id INT DEFAULT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->execute($createTable);
        echo "<p style='color: green;'>✅ تم إنشاء جدول التصنيفات بنجاح</p>";
    } else {
        echo "<p>جدول التصنيفات موجود، التحقق من العمود status...</p>";
        
        // التحقق من وجود عمود status
        $columns = $db->fetchAll("SHOW COLUMNS FROM categories LIKE 'status'");
        
        if (empty($columns)) {
            echo "<p>إضافة عمود status...</p>";
            $db->execute("ALTER TABLE categories ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER description");
            echo "<p style='color: green;'>✅ تم إضافة عمود status بنجاح</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ عمود status موجود بالفعل</p>";
        }
    }
    
    // إضافة تصنيفات افتراضية
    $existingCategories = $db->count('categories');
    
    if ($existingCategories == 0) {
        echo "<p>إضافة تصنيفات افتراضية...</p>";
        
        $defaultCategories = [
            ['name' => 'أخبار المسجد', 'slug' => 'mosque-news', 'description' => 'أخبار وفعاليات المسجد'],
            ['name' => 'الدروس والمحاضرات', 'slug' => 'lessons-lectures', 'description' => 'الدروس الدينية والمحاضرات'],
            ['name' => 'الأنشطة', 'slug' => 'activities', 'description' => 'أنشطة وفعاليات المسجد'],
            ['name' => 'الإعلانات', 'slug' => 'announcements', 'description' => 'الإعلانات والتنبيهات المهمة']
        ];
        
        foreach ($defaultCategories as $category) {
            $db->insert('categories', $category);
        }
        
        echo "<p style='color: green;'>✅ تم إضافة التصنيفات الافتراضية بنجاح</p>";
    }
    
    echo "<h3 style='color: green;'>🎉 تم إصلاح جدول التصنيفات بنجاح!</h3>";
    echo "<p><a href='admin/pages.php'>الانتقال إلى إدارة الصفحات</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ خطأ: " . $e->getMessage() . "</p>";
}
?>
