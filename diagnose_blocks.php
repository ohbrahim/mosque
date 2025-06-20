<?php
require_once 'config/config.php';

echo "<h1>تشخيص شامل لنظام البلوكات</h1>";

// 1. فحص جدول البلوكات
echo "<h2>1. فحص جدول البلوكات</h2>";
try {
    $tableInfo = $db->fetchAll("DESCRIBE blocks");
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>اسم العمود</th><th>النوع</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($tableInfo as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "❌ خطأ في فحص الجدول: " . $e->getMessage();
}

// 2. فحص البلوكات الموجودة
echo "<h2>2. البلوكات الموجودة</h2>";
try {
    $blocks = $db->fetchAll("SELECT * FROM blocks ORDER BY id DESC LIMIT 10");
    foreach ($blocks as $block) {
        echo "<div style='border: 2px solid #007bff; margin: 10px 0; padding: 15px;'>";
        echo "<h3>البلوك ID: " . $block['id'] . " - " . htmlspecialchars($block['title']) . "</h3>";
        echo "<p><strong>النوع:</strong> " . $block['block_type'] . " | <strong>الموقع:</strong> " . $block['position'] . " | <strong>الحالة:</strong> " . $block['status'] . "</p>";
        
        echo "<h4>المحتوى الخام (أول 500 حرف):</h4>";
        echo "<textarea style='width: 100%; height: 100px;' readonly>" . htmlspecialchars(substr($block['content'], 0, 500)) . "</textarea>";
        
        echo "<h4>المحتوى كما سيظهر:</h4>";
        echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #ddd;'>";
        echo $block['content']; // عرض مباشر بدون تنظيف
        echo "</div>";
        
        echo "</div>";
    }
} catch (Exception $e) {
    echo "❌ خطأ في جلب البلوكات: " . $e->getMessage();
}

// 3. اختبار إضافة بلوك جديد
echo "<h2>3. اختبار إضافة بلوك</h2>";

if ($_POST['test_block'] ?? false) {
    try {
        $testContent = $_POST['block_content'];
        $testTitle = $_POST['block_title'];
        
        $newBlock = [
            'title' => $testTitle,
            'content' => $testContent,
            'block_type' => 'html',
            'position' => 'right',
            'status' => 'active',
            'show_title' => 1,
            'allow_html' => 1,
            'is_safe' => 1,
            'display_order' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $db->insert('blocks', $newBlock);
        echo "✅ تم إضافة البلوك بنجاح! ID: " . $db->lastInsertId();
        
        // جلب البلوك المضاف للتأكد
        $addedBlock = $db->fetchOne("SELECT * FROM blocks WHERE id = ?", [$db->lastInsertId()]);
        echo "<h4>البلوك المضاف:</h4>";
        echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb;'>";
        echo "<p><strong>العنوان:</strong> " . htmlspecialchars($addedBlock['title']) . "</p>";
        echo "<p><strong>المحتوى المحفوظ:</strong></p>";
        echo "<textarea style='width: 100%; height: 100px;' readonly>" . htmlspecialchars($addedBlock['content']) . "</textarea>";
        echo "<p><strong>المحتوى المعروض:</strong></p>";
        echo "<div style='background: white; padding: 10px; border: 1px solid #ddd;'>";
        echo $addedBlock['content'];
        echo "</div>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "❌ خطأ في إضافة البلوك: " . $e->getMessage();
    }
}

echo "<form method='POST' style='background: #f8f9fa; padding: 20px; border: 1px solid #ddd; margin: 20px 0;'>";
echo "<h4>اختبار إضافة بلوك جديد:</h4>";
echo "<p><label>عنوان البلوك:</label><br>";
echo "<input type='text' name='block_title' value='اختبار البلوك' style='width: 100%; padding: 8px;'></p>";
echo "<p><label>محتوى البلوك:</label><br>";
echo "<textarea name='block_content' style='width: 100%; height: 150px; padding: 8px;'>";
echo '<iframe id="iframe" title="prayerWidget" style="width: 100%; height: 358px; border: 1px solid #ddd;" scrolling="no" src="https://www.islamicfinder.org/prayer-widget/2485801/shafi/15/0/18/17"></iframe>';
echo "</textarea></p>";
echo "<button type='submit' name='test_block' value='1' style='background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;'>إضافة البلوك</button>";
echo "</form>";

// 4. فحص الإعدادات
echo "<h2>4. إعدادات البلوكات</h2>";
try {
    $settings = $db->fetchAll("SELECT * FROM settings WHERE setting_group = 'blocks'");
    if (empty($settings)) {
        echo "⚠️ لا توجد إعدادات للبلوكات";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>المفتاح</th><th>القيمة</th><th>الوصف</th></tr>";
        foreach ($settings as $setting) {
            echo "<tr>";
            echo "<td>" . $setting['setting_key'] . "</td>";
            echo "<td>" . $setting['setting_value'] . "</td>";
            echo "<td>" . $setting['description'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "❌ خطأ في جلب الإعدادات: " . $e->getMessage();
}

echo "<p><a href='simple_block_manager.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>مدير البلوكات البسيط</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3, h4 { color: #333; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: right; }
th { background: #f8f9fa; }
</style>
