<?php
require_once 'config/config.php';

echo "<h1>إزالة البلوكات المكررة</h1>";

try {
    // 1. جلب جميع البلوكات مرتبة حسب العنوان والموقع
    $blocks = $db->fetchAll("SELECT * FROM blocks ORDER BY title, position");
    
    // 2. تحديد البلوكات المكررة
    $uniqueBlocks = [];
    $duplicateBlocks = [];
    
    foreach ($blocks as $block) {
        $key = $block['title'] . '_' . $block['position'] . '_' . $block['block_type'];
        
        if (!isset($uniqueBlocks[$key])) {
            $uniqueBlocks[$key] = $block;
        } else {
            $duplicateBlocks[] = $block;
        }
    }
    
    echo "<h2>البلوكات المكررة: " . count($duplicateBlocks) . "</h2>";
    
    if (empty($duplicateBlocks)) {
        echo "<p>لا توجد بلوكات مكررة.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>العنوان</th><th>الموقع</th><th>النوع</th><th>الحالة</th></tr>";
        
        foreach ($duplicateBlocks as $block) {
            echo "<tr>";
            echo "<td>" . $block['id'] . "</td>";
            echo "<td>" . htmlspecialchars($block['title']) . "</td>";
            echo "<td>" . $block['position'] . "</td>";
            echo "<td>" . $block['block_type'] . "</td>";
            echo "<td>" . $block['status'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // 3. حذف البلوكات المكررة
        if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
            foreach ($duplicateBlocks as $block) {
                $db->query("DELETE FROM blocks WHERE id = ?", [$block['id']]);
            }
            
            echo "<p style='color: green;'>✅ تم حذف " . count($duplicateBlocks) . " بلوك مكرر بنجاح!</p>";
            echo "<p><a href='admin/blocks.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>العودة إلى إدارة البلوكات</a></p>";
        } else {
            echo "<form method='POST'>";
            echo "<p style='color: red;'>تحذير: سيتم حذف البلوكات المكررة المذكورة أعلاه. هل أنت متأكد؟</p>";
            echo "<input type='hidden' name='confirm_delete' value='yes'>";
            echo "<button type='submit' style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>نعم، حذف البلوكات المكررة</button>";
            echo " <a href='admin/blocks.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>إلغاء</a>";
            echo "</form>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ حدث خطأ: " . $e->getMessage() . "</p>";
}
?>
