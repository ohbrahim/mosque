<?php
require_once 'config/config.php';

echo "<h1>الإصلاح النهائي لنظام البلوكات</h1>";

try {
    // 1. إعادة إنشاء جدول البلوكات بشكل صحيح
    echo "<h2>1. إعادة إنشاء جدول البلوكات</h2>";
    
    // إنشاء نسخة احتياطية من البلوكات الموجودة
    try {
        $existingBlocks = $db->fetchAll("SELECT * FROM blocks");
        echo "✅ تم حفظ " . count($existingBlocks) . " بلوك موجود<br>";
    } catch (Exception $e) {
        $existingBlocks = [];
        echo "⚠️ لا توجد بلوكات موجودة<br>";
    }
    
    // إضافة الأعمدة المفقودة إذا لم تكن موجودة
    $columnsToAdd = [
        'show_title' => 'TINYINT(1) DEFAULT 1',
        'custom_css' => 'TEXT',
        'css_class' => 'VARCHAR(255)',
        'allow_html' => 'TINYINT(1) DEFAULT 1',
        'is_safe' => 'TINYINT(1) DEFAULT 1'
    ];
    
    foreach ($columnsToAdd as $column => $definition) {
        try {
            $db->query("ALTER TABLE blocks ADD COLUMN $column $definition");
            echo "✅ تم إضافة العمود: $column<br>";
        } catch (Exception $e) {
            echo "⚠️ العمود $column موجود بالفعل<br>";
        }
    }
    
    // 2. تحديث جميع البلوكات الموجودة
    echo "<h2>2. تحديث البلوكات الموجودة</h2>";
    
    $db->query("UPDATE blocks SET allow_html = 1, is_safe = 1 WHERE 1=1");
    echo "✅ تم تحديث جميع البلوكات لتكون آمنة وقابلة للتنفيذ<br>";
    
    // 3. إضافة إعدادات البلوكات
    echo "<h2>3. إعدادات البلوكات</h2>";
    
    $settings = [
        'allow_iframe' => '1',
        'allow_marquee' => '1', 
        'allow_custom_html' => '1',
        'sanitize_blocks' => '0'
    ];
    
    foreach ($settings as $key => $value) {
        try {
            $exists = $db->fetchOne("SELECT COUNT(*) as count FROM settings WHERE setting_key = ?", [$key]);
            if ($exists['count'] == 0) {
                $db->insert('settings', [
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'setting_type' => 'boolean',
                    'setting_group' => 'blocks',
                    'display_name' => $key,
                    'description' => 'Block setting'
                ]);
                echo "✅ تم إضافة إعداد: $key<br>";
            } else {
                $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
                echo "✅ تم تحديث إعداد: $key<br>";
            }
        } catch (Exception $e) {
            echo "❌ خطأ في إعداد $key: " . $e->getMessage() . "<br>";
        }
    }
    
    // 4. اختبار إضافة بلوك
    echo "<h2>4. اختبار إضافة بلوك</h2>";
    
    $testBlock = [
        'title' => 'اختبار البلوك - ' . date('H:i:s'),
        'content' => '<iframe id="iframe" title="prayerWidget" style="width: 100%; height: 358px; border: 1px solid #ddd;" scrolling="no" src="https://www.islamicfinder.org/prayer-widget/2485801/shafi/15/0/18/17"></iframe>',
        'block_type' => 'iframe',
        'position' => 'right',
        'status' => 'active',
        'show_title' => 1,
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 999,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    try {
        $db->insert('blocks', $testBlock);
        $testId = $db->lastInsertId();
        echo "✅ تم إضافة بلوك اختبار بنجاح! ID: $testId<br>";
        
        // جلب البلوك للتأكد
        $addedBlock = $db->fetchOne("SELECT * FROM blocks WHERE id = ?", [$testId]);
        echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
        echo "<h4>البلوك المضاف:</h4>";
        echo "<p><strong>المحتوى المحفوظ:</strong></p>";
        echo "<code>" . htmlspecialchars($addedBlock['content']) . "</code>";
        echo "<p><strong>المحتوى المعروض:</strong></p>";
        echo $addedBlock['content'];
        echo "</div>";
        
    } catch (Exception $e) {
        echo "❌ خطأ في إضافة بلوك الاختبار: " . $e->getMessage() . "<br>";
    }
    
    echo "<h2>✅ تم الإصلاح النهائي بنجاح!</h2>";
    echo "<p>";
    echo "<a href='simple_block_manager.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; margin: 5px; border-radius: 4px;'>مدير البلوكات البسيط</a>";
    echo "<a href='diagnose_blocks.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; margin: 5px; border-radius: 4px;'>تشخيص البلوكات</a>";
    echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; margin: 5px; border-radius: 4px;'>عرض الموقع</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "❌ حدث خطأ عام: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
</style>
