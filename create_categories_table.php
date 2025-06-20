<?php
require_once 'config/config.php';

try {
    echo "<h2>إنشاء جدول التصنيفات...</h2>";
    
    // حذف الجدول إذا كان موجوداً (للتأكد من البداية النظيفة)
    $db->query("DROP TABLE IF EXISTS categories");
    echo "✅ تم حذف الجدول القديم إن وجد<br>";
    
    // إنشاء جدول التصنيفات
    $createTableSQL = "
        CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            color VARCHAR(7) DEFAULT '#6c757d',
            icon VARCHAR(50),
            parent_id INT DEFAULT NULL,
            display_order INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            meta_title VARCHAR(255),
            meta_description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $db->query($createTableSQL);
    echo "✅ تم إنشاء جدول التصنيفات بنجاح!<br>";
    
    // التحقق من إنشاء الجدول
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'categories'");
    if (!$tableExists) {
        throw new Exception("فشل في إنشاء جدول التصنيفات");
    }
    
    // إضافة التصنيفات الافتراضية
    $default_categories = [
        [
            'name' => 'أخبار المسجد',
            'slug' => 'mosque-news',
            'description' => 'آخر أخبار وأنشطة المسجد',
            'color' => '#28a745',
            'icon' => 'fas fa-newspaper',
            'display_order' => 1,
            'is_active' => 1
        ],
        [
            'name' => 'الدروس والمحاضرات',
            'slug' => 'lessons',
            'description' => 'الدروس الدينية والمحاضرات الإسلامية',
            'color' => '#007bff',
            'icon' => 'fas fa-book-open',
            'display_order' => 2,
            'is_active' => 1
        ],
        [
            'name' => 'الفعاليات',
            'slug' => 'events',
            'description' => 'فعاليات وأنشطة المسجد',
            'color' => '#ffc107',
            'icon' => 'fas fa-calendar-alt',
            'display_order' => 3,
            'is_active' => 1
        ],
        [
            'name' => 'الإعلانات',
            'slug' => 'announcements',
            'description' => 'إعلانات مهمة من إدارة المسجد',
            'color' => '#dc3545',
            'icon' => 'fas fa-bullhorn',
            'display_order' => 4,
            'is_active' => 1
        ],
        [
            'name' => 'مقالات دينية',
            'slug' => 'religious-articles',
            'description' => 'مقالات ومواضيع دينية مفيدة',
            'color' => '#6f42c1',
            'icon' => 'fas fa-mosque',
            'display_order' => 5,
            'is_active' => 1
        ],
        [
            'name' => 'خدمات المسجد',
            'slug' => 'services',
            'description' => 'الخدمات التي يقدمها المسجد للمجتمع',
            'color' => '#20c997',
            'icon' => 'fas fa-hands-helping',
            'display_order' => 6,
            'is_active' => 1
        ]
    ];
    
    echo "<h3>إضافة التصنيفات الافتراضية...</h3>";
    
    foreach ($default_categories as $index => $category) {
        try {
            $result = $db->insert('categories', $category);
            if ($result) {
                echo "✅ تم إضافة تصنيف: <strong>" . htmlspecialchars($category['name']) . "</strong><br>";
            } else {
                echo "❌ فشل في إضافة تصنيف: " . htmlspecialchars($category['name']) . "<br>";
            }
        } catch (Exception $e) {
            echo "❌ خطأ في إضافة تصنيف " . htmlspecialchars($category['name']) . ": " . $e->getMessage() . "<br>";
        }
    }
    
    // عرض التصنيفات الموجودة
    $categories = $db->fetchAll("SELECT * FROM categories ORDER BY display_order, name");
    echo "<h3>التصنيفات الموجودة (" . count($categories) . "):</h3>";
    
    if (count($categories) > 0) {
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
        echo "<tr style='background: #e9ecef;'>";
        echo "<th style='padding: 10px;'>ID</th>";
        echo "<th style='padding: 10px;'>الاسم</th>";
        echo "<th style='padding: 10px;'>الرابط</th>";
        echo "<th style='padding: 10px;'>اللون</th>";
        echo "<th style='padding: 10px;'>الأيقونة</th>";
        echo "<th style='padding: 10px;'>الحالة</th>";
        echo "</tr>";
        
        foreach ($categories as $cat) {
            echo "<tr>";
            echo "<td style='padding: 8px; text-align: center;'>" . $cat['id'] . "</td>";
            echo "<td style='padding: 8px;'><strong>" . htmlspecialchars($cat['name']) . "</strong></td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($cat['slug']) . "</td>";
            echo "<td style='padding: 8px; text-align: center;'>";
            echo "<span style='background: " . htmlspecialchars($cat['color']) . "; color: white; padding: 5px 10px; border-radius: 5px;'>" . htmlspecialchars($cat['color']) . "</span>";
            echo "</td>";
            echo "<td style='padding: 8px; text-align: center;'><i class='" . htmlspecialchars($cat['icon']) . "'></i></td>";
            echo "<td style='padding: 8px; text-align: center;'>" . ($cat['is_active'] ? '✅ نشط' : '❌ غير نشط') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ لم يتم العثور على أي تصنيفات!</p>";
    }
    
    echo "<hr>";
    echo "<h3>✅ تم الانتهاء بنجاح!</h3>";
    echo "<p><a href='archive.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 اذهب إلى صفحة الأرشيف</a></p>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>❌ خطأ:</strong> " . $e->getMessage();
    echo "</div>";
    
    // معلومات إضافية للتشخيص
    echo "<h4>معلومات التشخيص:</h4>";
    echo "<ul>";
    echo "<li><strong>نوع قاعدة البيانات:</strong> " . $db->getAttribute(PDO::ATTR_DRIVER_NAME) . "</li>";
    echo "<li><strong>إصدار قاعدة البيانات:</strong> " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "</li>";
    echo "</ul>";
}
?>
