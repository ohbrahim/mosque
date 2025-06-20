<?php
require_once 'config/config.php';

echo "<h1>إصلاح مشكلة iframe في البلوكات</h1>";

// 1. تحديث البلوكات التي تحتوي على iframe لتكون نشطة
echo "<h2>1. تفعيل بلوكات iframe</h2>";

try {
    // تفعيل البلوك رقم 35 (اختبار البلوك)
    $db->query("UPDATE blocks SET status = 'active' WHERE id = 35");
    echo "✅ تم تفعيل البلوك رقم 35<br>";
    
    // تفعيل البلوك رقم 29 (أوقات الصلاة)
    $db->query("UPDATE blocks SET status = 'active' WHERE id = 29");
    echo "✅ تم تفعيل البلوك رقم 29<br>";
    
    // تحديث نوع البلوك ليكون iframe
    $db->query("UPDATE blocks SET block_type = 'iframe' WHERE id IN (35, 29)");
    echo "✅ تم تحديث نوع البلوكات إلى iframe<br>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "<br>";
}

// 2. اختبار عرض البلوكات
echo "<h2>2. اختبار عرض البلوكات</h2>";

try {
    $iframeBlocks = $db->fetchAll("SELECT * FROM blocks WHERE content LIKE '%iframe%' AND status = 'active'");
    
    foreach ($iframeBlocks as $block) {
        echo "<div style='border: 2px solid #007bff; margin: 20px 0; padding: 15px;'>";
        echo "<h3>البلوك: " . htmlspecialchars($block['title']) . " (ID: " . $block['id'] . ")</h3>";
        
        echo "<h4>المحتوى الخام:</h4>";
        echo "<textarea style='width: 100%; height: 100px;' readonly>" . htmlspecialchars($block['content']) . "</textarea>";
        
        echo "<h4>المحتوى المعروض:</h4>";
        echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #ddd; min-height: 400px;'>";
        echo $block['content']; // عرض مباشر
        echo "</div>";
        
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ في جلب البلوكات: " . $e->getMessage();
}

// 3. إضافة بلوك iframe جديد للاختبار
echo "<h2>3. إضافة بلوك iframe جديد</h2>";

try {
    $testIframe = [
        'title' => 'اختبار iframe جديد - ' . date('H:i:s'),
        'content' => '<iframe id="testIframe" title="Prayer Times" style="width: 100%; height: 358px; border: 1px solid #ddd; border-radius: 8px;" scrolling="no" src="https://www.islamicfinder.org/prayer-widget/2485801/shafi/15/0/18/17"></iframe>',
        'block_type' => 'iframe',
        'position' => 'right',
        'status' => 'active',
        'show_title' => 1,
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $db->insert('blocks', $testIframe);
    $newId = $db->lastInsertId();
    echo "✅ تم إضافة بلوك iframe جديد بنجاح! ID: $newId<br>";
    
    // جلب البلوك الجديد وعرضه
    $newBlock = $db->fetchOne("SELECT * FROM blocks WHERE id = ?", [$newId]);
    echo "<div style='background: #d4edda; padding: 15px; margin: 10px 0; border: 1px solid #c3e6cb;'>";
    echo "<h4>البلوك الجديد:</h4>";
    echo "<p><strong>العنوان:</strong> " . htmlspecialchars($newBlock['title']) . "</p>";
    echo "<div style='background: white; padding: 15px; border: 1px solid #ddd; min-height: 400px;'>";
    echo $newBlock['content'];
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ خطأ في إضافة البلوك الجديد: " . $e->getMessage();
}

echo "<h2>✅ تم إصلاح مشكلة iframe!</h2>";
echo "<p>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; margin: 5px; border-radius: 4px;'>عرض الموقع</a>";
echo "<a href='diagnose_blocks.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; margin: 5px; border-radius: 4px;'>تشخيص مرة أخرى</a>";
echo "</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3, h4 { color: #333; }
</style>
