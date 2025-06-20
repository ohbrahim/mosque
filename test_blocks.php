<?php
require_once 'config/config.php';
require_once 'includes/block_functions.php';

echo "<h2>اختبار البلوكات</h2>";

try {
    // جلب جميع البلوكات النشطة
    $blocks = $db->fetchAll("SELECT * FROM blocks WHERE status = 'active' ORDER BY position, display_order");
    
    echo "<h3>البلوكات النشطة (" . count($blocks) . "):</h3>";
    
    foreach ($blocks as $block) {
        echo "<div style='border: 2px solid #007bff; margin: 20px 0; padding: 15px; border-radius: 8px;'>";
        echo "<h4>البلوك: " . htmlspecialchars($block['title']) . "</h4>";
        echo "<p><strong>الموقع:</strong> " . $block['position'] . " | <strong>النوع:</strong> " . $block['block_type'] . "</p>";
        
        echo "<div style='background: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong>المحتوى الخام:</strong><br>";
        echo "<code>" . htmlspecialchars(substr($block['content'], 0, 200)) . "...</code>";
        echo "</div>";
        
        echo "<div style='background: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px;'>";
        echo "<strong>المحتوى المعروض:</strong><br>";
        echo renderBlock($block);
        echo "</div>";
        
        echo "</div>";
    }
    
    // اختبار إضافة بلوك جديد
    echo "<h3>اختبار إضافة بلوك iframe:</h3>";
    
    $testBlock = [
        'id' => 999,
        'title' => 'اختبار iframe',
        'content' => '<iframe id="iframe" title="prayerWidget" class="widget-m-top" style="width: 100%; height: 358px; border: 1px solid #ddd;" scrolling="no" src="https://www.islamicfinder.org/prayer-widget/2485801/shafi/15/0/18/17"></iframe>',
        'block_type' => 'iframe',
        'position' => 'right',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'test-widget',
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 1
    ];
    
    echo "<div style='background: white; padding: 15px; border: 2px solid #28a745; border-radius: 8px;'>";
    echo renderBlock($testBlock);
    echo "</div>";
    
    echo "<h3>اختبار إضافة بلوك marquee:</h3>";
    
    $testMarquee = [
        'id' => 998,
        'title' => 'اختبار marquee',
        'content' => '<marquee direction="right"><p><font size="4" color="red">إنطلاق بث قنوات دروس الدعم لجميع المستويات والأطوار على موقع الديوان الوطني للتعليم والتكوين عن بعد على الرابط التالي : http://www.onefd.edu.dz/soutien-scolaire</font></p></marquee>',
        'block_type' => 'marquee',
        'position' => 'top',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'test-marquee',
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 1
    ];
    
    echo "<div style='background: white; padding: 15px; border: 2px solid #dc3545; border-radius: 8px;'>";
    echo renderBlock($testMarquee);
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage();
}

echo "<p><a href='index.php' class='btn btn-primary'>عرض الموقع</a> <a href='admin/blocks.php' class='btn btn-secondary'>إدارة البلوكات</a></p>";
?>

<style>
.btn {
    display: inline-block;
    padding: 8px 16px;
    margin: 4px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
}
.btn-secondary {
    background: #6c757d;
}
</style>
