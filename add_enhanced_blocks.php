<?php
require_once 'config/config.php';
require_once 'includes/enhanced_blocks.php';

echo "<h1>إضافة البلوكات المحسنة</h1>";

try {
    // 1. إضافة بلوك آية قرآنية محسن
    $quranBlock = [
        'title' => 'آية اليوم - محسن',
        'content' => renderEnhancedQuranVerseBlock(),
        'block_type' => 'quran_verse',
        'position' => 'right',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'quran-verse-enhanced',
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 2,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'created_by' => 1
    ];
    
    $db->insert('blocks', $quranBlock);
    echo "<p style='color: green;'>✅ تم إضافة بلوك آية اليوم المحسن بنجاح!</p>";
    
    // 2. إضافة بلوك حديث محسن
    $hadithBlock = [
        'title' => 'حديث اليوم - محسن',
        'content' => renderEnhancedHadithBlock(),
        'block_type' => 'hadith',
        'position' => 'right',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'hadith-enhanced',
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 3,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'created_by' => 1
    ];
    
    $db->insert('blocks', $hadithBlock);
    echo "<p style='color: green;'>✅ تم إضافة بلوك حديث اليوم المحسن بنجاح!</p>";
    
    // 3. إضافة بلوك إحصائيات الزوار المحسن
    $statsBlock = [
        'title' => 'إحصائيات الزوار - محسن',
        'content' => renderEnhancedVisitorStatsBlock($db),
        'block_type' => 'visitor_stats',
        'position' => 'left',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'visitor-stats-enhanced',
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 2,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'created_by' => 1
    ];
    
    $db->insert('blocks', $statsBlock);
    echo "<p style='color: green;'>✅ تم إضافة بلوك إحصائيات الزوار المحسن بنجاح!</p>";
    
    // 4. إضافة بلوك روابط التواصل الاجتماعي المحسن
    $socialBlock = [
        'title' => 'تواصل معنا',
        'content' => renderEnhancedSocialLinksBlock($db),
        'block_type' => 'social_links',
        'position' => 'left',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'social-links-enhanced',
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 3,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'created_by' => 1
    ];
    
    $db->insert('blocks', $socialBlock);
    echo "<p style='color: green;'>✅ تم إضافة بلوك روابط التواصل الاجتماعي المحسن بنجاح!</p>";
    
    // 5. إضافة بلوك الصفحات الحديثة المحسن
    $recentPagesBlock = [
        'title' => 'الصفحات الحديثة',
        'content' => renderEnhancedRecentPagesBlock($db),
        'block_type' => 'recent_pages',
        'position' => 'left',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'recent-pages-enhanced',
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 4,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'created_by' => 1
    ];
    
    $db->insert('blocks', $recentPagesBlock);
    echo "<p style='color: green;'>✅ تم إضافة بلوك الصفحات الحديثة المحسن بنجاح!</p>";
    
    echo "<h2>✅ تم إضافة جميع البلوكات المحسنة بنجاح!</h2>";
    echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>عرض الموقع</a>";
    echo "<a href='admin/blocks.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>إدارة البلوكات</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ حدث خطأ: " . $e->getMessage() . "</p>";
}
?>
