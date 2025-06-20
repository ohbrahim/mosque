<?php
require_once 'config/config.php';

echo "<h2>إصلاح شامل لنظام البلوكات</h2>";

try {
    // 1. إصلاح جدول البلوكات
    echo "<h3>1. إصلاح جدول البلوكات</h3>";
    
    // إضافة الأعمدة المفقودة
    $columns = [
        'show_title' => 'BOOLEAN DEFAULT 1',
        'custom_css' => 'TEXT',
        'css_class' => 'VARCHAR(255)',
        'allow_html' => 'BOOLEAN DEFAULT 1',
        'is_safe' => 'BOOLEAN DEFAULT 1'
    ];
    
    foreach ($columns as $column => $definition) {
        try {
            $db->query("ALTER TABLE blocks ADD COLUMN $column $definition");
            echo "✅ تم إضافة العمود: $column<br>";
        } catch (Exception $e) {
            echo "⚠️ العمود $column موجود بالفعل<br>";
        }
    }
    
    // 2. تحديث جميع البلوكات لتكون آمنة وقابلة للتنفيذ
    echo "<h3>2. تحديث البلوكات الموجودة</h3>";
    
    $db->query("UPDATE blocks SET is_safe = 1, allow_html = 1 WHERE 1=1");
    echo "✅ تم تحديث جميع البلوكات لتكون آمنة وقابلة للتنفيذ<br>";
    
    // 3. إضافة البلوكات الافتراضية الكاملة
    echo "<h3>3. إضافة البلوكات الافتراضية</h3>";
    
    $defaultBlocks = [
        [
            'title' => 'أوقات الصلاة',
            'content' => '<iframe id="prayerWidget" title="أوقات الصلاة" style="width: 100%; height: 358px; border: 1px solid #ddd; border-radius: 8px;" scrolling="no" src="https://www.islamicfinder.org/prayer-widget/2485801/shafi/15/0/18/17"></iframe>',
            'block_type' => 'iframe',
            'position' => 'right',
            'status' => 'active',
            'show_title' => 1,
            'css_class' => 'prayer-times-widget',
            'allow_html' => 1,
            'is_safe' => 1,
            'display_order' => 1
        ],
        [
            'title' => 'إعلانات هامة',
            'content' => '<marquee direction="right" scrollamount="3" behavior="scroll"><span style="color: #d63384; font-weight: bold; font-size: 16px;">🔔 إنطلاق بث قنوات دروس الدعم لجميع المستويات والأطوار على موقع الديوان الوطني للتعليم والتكوين عن بعد: http://www.onefd.edu.dz/soutien-scolaire</span></marquee>',
            'block_type' => 'marquee',
            'position' => 'top',
            'status' => 'active',
            'show_title' => 0,
            'css_class' => 'announcement-marquee',
            'allow_html' => 1,
            'is_safe' => 1,
            'display_order' => 1
        ],
        [
            'title' => 'آية اليوم',
            'content' => '<div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-book-open text-success mb-3" style="font-size: 2.5rem;"></i>
                    <p class="card-text fs-5 fw-bold text-dark mb-3">وَمَا خَلَقْتُ الْجِنَّ وَالْإِنسَ إِلَّا لِيَعْبُدُونِ</p>
                    <footer class="blockquote-footer">
                        سورة <cite title="الذاريات">الذاريات</cite> - آية ٥٦
                    </footer>
                </div>
            </div>',
            'block_type' => 'html',
            'position' => 'right',
            'status' => 'active',
            'show_title' => 1,
            'css_class' => 'quran-widget',
            'allow_html' => 1,
            'is_safe' => 1,
            'display_order' => 2
        ],
        [
            'title' => 'حديث اليوم',
            'content' => '<div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-quote-right text-warning mb-3" style="font-size: 2rem;"></i>
                    <p class="fs-6 fw-bold text-dark mb-3">قال رسول الله صلى الله عليه وسلم: "إنما الأعمال بالنيات وإنما لكل امرئ ما نوى"</p>
                    <small class="text-muted">رواه البخاري ومسلم</small>
                </div>
            </div>',
            'block_type' => 'html',
            'position' => 'right',
            'status' => 'active',
            'show_title' => 1,
            'css_class' => 'hadith-widget',
            'allow_html' => 1,
            'is_safe' => 1,
            'display_order' => 3
        ],
        [
            'title' => 'إحصائيات الزوار',
            'content' => '<div class="card border-info">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <i class="fas fa-users fa-2x text-info mb-2"></i>
                            <h6>زوار اليوم</h6>
                            <h4 class="text-primary" id="todayVisitors">0</h4>
                        </div>
                        <div class="col-6">
                            <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                            <h6>إجمالي الزوار</h6>
                            <h4 class="text-success" id="totalVisitors">0</h4>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-12">
                            <i class="fas fa-clock fa-lg text-warning me-2"></i>
                            <small class="text-muted">آخر تحديث: <span id="lastUpdate">' . date('H:i') . '</span></small>
                        </div>
                    </div>
                </div>
            </div>',
            'block_type' => 'html',
            'position' => 'right',
            'status' => 'active',
            'show_title' => 1,
            'css_class' => 'stats-widget',
            'allow_html' => 1,
            'is_safe' => 1,
            'display_order' => 4
        ],
        [
            'title' => 'روابط خارجية',
            'content' => '<div class="list-group">
                <a href="https://www.islamweb.net" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-external-link-alt me-2 text-primary"></i>إسلام ويب</span>
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="https://www.islamqa.info" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-external-link-alt me-2 text-success"></i>الإسلام سؤال وجواب</span>
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="https://quran.com" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-external-link-alt me-2 text-info"></i>القرآن الكريم</span>
                    <i class="fas fa-chevron-left"></i>
                </a>
                <a href="https://sunnah.com" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-external-link-alt me-2 text-warning"></i>السنة النبوية</span>
                    <i class="fas fa-chevron-left"></i>
                </a>
            </div>',
            'block_type' => 'html',
            'position' => 'left',
            'status' => 'active',
            'show_title' => 1,
            'css_class' => 'external-links-widget',
            'allow_html' => 1,
            'is_safe' => 1,
            'display_order' => 1
        ]
    ];
    
    foreach ($defaultBlocks as $block) {
        try {
            // حذف البلوك إذا كان موجوداً لإعادة إنشائه
            $db->query("DELETE FROM blocks WHERE title = ? AND position = ?", [$block['title'], $block['position']]);
            
            $block['created_at'] = date('Y-m-d H:i:s');
            $block['updated_at'] = date('Y-m-d H:i:s');
            $db->insert('blocks', $block);
            echo "✅ تم إضافة بلوك: " . $block['title'] . "<br>";
        } catch (Exception $e) {
            echo "❌ خطأ في إضافة بلوك: " . $block['title'] . " - " . $e->getMessage() . "<br>";
        }
    }
    
    // 4. إضافة إعدادات البلوكات
    echo "<h3>4. إضافة إعدادات البلوكات</h3>";
    
    $blockSettings = [
        ['allow_iframe', '1', 'boolean', 'blocks', 'السماح بـ iframe', 'السماح باستخدام iframe في البلوكات'],
        ['allow_marquee', '1', 'boolean', 'blocks', 'السماح بـ marquee', 'السماح باستخدام marquee في البلوكات'],
        ['allow_custom_html', '1', 'boolean', 'blocks', 'السماح بـ HTML مخصص', 'السماح باستخدام HTML مخصص في البلوكات'],
        ['sanitize_blocks', '0', 'boolean', 'blocks', 'تنظيف البلوكات', 'تنظيف محتوى البلوكات من العناصر الخطيرة']
    ];
    
    foreach ($blockSettings as $setting) {
        try {
            // حذف الإعداد إذا كان موجوداً لإعادة إنشائه
            $db->query("DELETE FROM settings WHERE setting_key = ?", [$setting[0]]);
            
            $db->insert('settings', [
                'setting_key' => $setting[0],
                'setting_value' => $setting[1],
                'setting_type' => $setting[2],
                'setting_group' => $setting[3],
                'display_name' => $setting[4],
                'description' => $setting[5]
            ]);
            echo "✅ تم إضافة إعداد: " . $setting[4] . "<br>";
        } catch (Exception $e) {
            echo "❌ خطأ في إضافة إعداد: " . $setting[4] . "<br>";
        }
    }
    
    echo "<h3>✅ تم إصلاح نظام البلوكات بنجاح!</h3>";
    echo "<p><a href='test_blocks.php' class='btn btn-warning'>اختبار البلوكات</a> <a href='index.php' class='btn btn-primary'>عرض الموقع</a> <a href='admin/blocks.php' class='btn btn-secondary'>إدارة البلوكات</a></p>";
    
} catch (Exception $e) {
    echo "❌ حدث خطأ: " . $e->getMessage();
}
?>
