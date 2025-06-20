<?php
require_once 'config/config.php';

echo "<h2>إصلاح نظام البلوكات</h2>";

try {
    // 1. إصلاح جدول البلوكات
    echo "<h3>1. إصلاح جدول البلوكات</h3>";
    
    // إضافة الأعمدة المفقودة
    $columns = [
        'show_title' => 'BOOLEAN DEFAULT 1',
        'custom_css' => 'TEXT',
        'css_class' => 'VARCHAR(255)',
        'allow_html' => 'BOOLEAN DEFAULT 1',
        'is_safe' => 'BOOLEAN DEFAULT 1' // تغيير القيمة الافتراضية إلى 1 لتشغيل البلوكات
    ];
    
    foreach ($columns as $column => $definition) {
        try {
            $db->query("ALTER TABLE blocks ADD COLUMN $column $definition");
            echo "✅ تم إضافة العمود: $column<br>";
        } catch (Exception $e) {
            echo "⚠️ العمود $column موجود بالفعل<br>";
            
            // تحديث العمود is_safe ليكون 1 بشكل افتراضي
            if ($column == 'is_safe') {
                $db->query("ALTER TABLE blocks ALTER COLUMN is_safe SET DEFAULT 1");
                echo "✅ تم تحديث القيمة الافتراضية للعمود is_safe<br>";
            }
        }
    }
    
    // 2. تحديث البلوكات الموجودة لتكون آمنة
    echo "<h3>2. تحديث البلوكات الموجودة</h3>";
    
    // تحديث جميع البلوكات لتكون آمنة
    $db->query("UPDATE blocks SET is_safe = 1, allow_html = 1");
    echo "✅ تم تحديث جميع البلوكات لتكون آمنة<br>";
    
    // 3. إضافة بلوكات افتراضية محدثة
    echo "<h3>3. إضافة بلوكات افتراضية</h3>";
    
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
            'block_type' => 'quran_verse',
            'position' => 'right',
            'status' => 'active',
            'show_title' => 1,
            'css_class' => 'quran-widget',
            'allow_html' => 1,
            'is_safe' => 1,
            'display_order' => 2
        ]
    ];
    
    foreach ($defaultBlocks as $block) {
        try {
            // التحقق من عدم وجود بلوك مشابه
            $exists = $db->fetchOne("SELECT COUNT(*) as count FROM blocks WHERE title = ? AND position = ?", [$block['title'], $block['position']]);
            
            if ($exists['count'] == 0) {
                $block['created_at'] = date('Y-m-d H:i:s');
                $block['updated_at'] = date('Y-m-d H:i:s');
                $db->insert('blocks', $block);
                echo "✅ تم إضافة بلوك: " . $block['title'] . "<br>";
            } else {
                echo "⚠️ البلوك موجود بالفعل: " . $block['title'] . "<br>";
            }
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
            $exists = $db->fetchOne("SELECT COUNT(*) as count FROM settings WHERE setting_key = ?", [$setting[0]]);
            if ($exists['count'] == 0) {
                $db->insert('settings', [
                    'setting_key' => $setting[0],
                    'setting_value' => $setting[1],
                    'setting_type' => $setting[2],
                    'setting_group' => $setting[3],
                    'display_name' => $setting[4],
                    'description' => $setting[5]
                ]);
                echo "✅ تم إضافة إعداد: " . $setting[4] . "<br>";
            } else {
                // تحديث الإعدادات الموجودة
                $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$setting[1], $setting[0]]);
                echo "✅ تم تحديث إعداد: " . $setting[4] . "<br>";
            }
        } catch (Exception $e) {
            echo "❌ خطأ في إضافة إعداد: " . $setting[4] . "<br>";
        }
    }
    
    echo "<h3>✅ تم إصلاح نظام البلوكات بنجاح!</h3>";
    echo "<p><a href='index.php' class='btn btn-primary'>عرض الموقع</a> <a href='admin/blocks.php' class='btn btn-secondary'>إدارة البلوكات</a></p>";
    
} catch (Exception $e) {
    echo "❌ حدث خطأ: " . $e->getMessage();
}
?>
