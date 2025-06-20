<?php
require_once 'config/config.php';
require_once 'includes/enhanced_blocks_v2.php';

echo "<h1>تحديث البلوكات المحسنة</h1>";

try {
    // 1. تحديث بلوك آية اليوم
    $quranBlocks = $db->fetchAll("SELECT * FROM blocks WHERE block_type = 'quran_verse' AND status = 'active'");
    foreach ($quranBlocks as $block) {
        $newContent = renderEnhancedQuranVerseBlock();
        $db->query("UPDATE blocks SET content = ?, updated_at = ? WHERE id = ?", [
            $newContent,
            date('Y-m-d H:i:s'),
            $block['id']
        ]);
    }
    echo "<p style='color: green;'>✅ تم تحديث " . count($quranBlocks) . " بلوك آية قرآنية!</p>";
    
    // 2. تحديث بلوك حديث اليوم
    $hadithBlocks = $db->fetchAll("SELECT * FROM blocks WHERE block_type = 'hadith' AND status = 'active'");
    foreach ($hadithBlocks as $block) {
        $newContent = renderEnhancedHadithBlock();
        $db->query("UPDATE blocks SET content = ?, updated_at = ? WHERE id = ?", [
            $newContent,
            date('Y-m-d H:i:s'),
            $block['id']
        ]);
    }
    echo "<p style='color: green;'>✅ تم تحديث " . count($hadithBlocks) . " بلوك حديث!</p>";
    
    // 3. تحديث بلوك إحصائيات الزوار
    $statsBlocks = $db->fetchAll("SELECT * FROM blocks WHERE block_type = 'visitor_stats' AND status = 'active'");
    foreach ($statsBlocks as $block) {
        $newContent = renderEnhancedVisitorStatsBlock($db);
        $db->query("UPDATE blocks SET content = ?, updated_at = ? WHERE id = ?", [
            $newContent,
            date('Y-m-d H:i:s'),
            $block['id']
        ]);
    }
    echo "<p style='color: green;'>✅ تم تحديث " . count($statsBlocks) . " بلوك إحصائيات الزوار!</p>";
    
    echo "<h2>✅ تم تحديث جميع البلوكات المحسنة بنجاح!</h2>";
    echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>عرض الموقع</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ حدث خطأ: " . $e->getMessage() . "</p>";
}
?>
