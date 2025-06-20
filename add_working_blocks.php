<?php
require_once 'config/config.php';

echo "<h1>إضافة البلوكات التي تعمل</h1>";

$workingBlocks = [
    [
        'title' => 'أوقات الصلاة - يعمل',
        'content' => '<iframe id="prayerWidget" title="أوقات الصلاة" style="width: 100%; height: 358px; border: 1px solid #ddd; border-radius: 8px;" scrolling="no" sandbox="allow-scripts allow-same-origin allow-forms" src="https://www.islamicfinder.org/prayer-widget/2485801/shafi/15/0/18/17"></iframe>',
        'block_type' => 'iframe',
        'position' => 'right',
        'status' => 'active',
        'show_title' => 1,
        'display_order' => 1
    ],
    [
        'title' => 'إعلان متحرك - يعمل',
        'content' => '<marquee direction="right" scrollamount="3"><span style="color: red; font-size: 16px; font-weight: bold;">🔔 إنطلاق بث قنوات دروس الدعم لجميع المستويات والأطوار على موقع الديوان الوطني للتعليم والتكوين عن بعد: http://www.onefd.edu.dz/soutien-scolaire</span></marquee>',
        'block_type' => 'marquee',
        'position' => 'top',
        'status' => 'active',
        'show_title' => 0,
        'display_order' => 1
    ],
    [
        'title' => 'روابط مفيدة',
        'content' => '<div class="list-group">
            <a href="https://www.islamweb.net" target="_blank" class="list-group-item list-group-item-action">
                <i class="fas fa-external-link-alt me-2"></i>إسلام ويب
            </a>
            <a href="https://quran.com" target="_blank" class="list-group-item list-group-item-action">
                <i class="fas fa-book-open me-2"></i>القرآن الكريم
            </a>
            <a href="https://sunnah.com" target="_blank" class="list-group-item list-group-item-action">
                <i class="fas fa-quote-right me-2"></i>السنة النبوية
            </a>
        </div>',
        'block_type' => 'html',
        'position' => 'left',
        'status' => 'active',
        'show_title' => 1,
        'display_order' => 1
    ]
];

foreach ($workingBlocks as $block) {
    try {
        $block['allow_html'] = 1;
        $block['is_safe'] = 1;
        $block['created_at'] = date('Y-m-d H:i:s');
        $block['updated_at'] = date('Y-m-d H:i:s');
        
        $db->insert('blocks', $block);
        echo "✅ تم إضافة بلوك: " . $block['title'] . "<br>";
        
    } catch (Exception $e) {
        echo "❌ خطأ في إضافة بلوك " . $block['title'] . ": " . $e->getMessage() . "<br>";
    }
}

echo "<h2>✅ تم إضافة البلوكات بنجاح!</h2>";
echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>عرض الموقع</a></p>";
?>
