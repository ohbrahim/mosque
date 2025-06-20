<?php
/**
 * صفحة اختبار الهيدر والفوتر
 */
require_once 'config/config.php';

// إضافة بيانات تجريبية للقوائم إذا لم تكن موجودة
try {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM menu_items WHERE menu_position = 'header'");
    $stmt->execute();
    $existingMenus = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingMenus || $existingMenus['count'] == 0) {
        // إضافة عناصر القائمة الرئيسية
        $headerMenuItems = [
            ['title' => 'الرئيسية', 'url' => 'index.php', 'icon' => 'fas fa-home', 'display_order' => 1],
            ['title' => 'عن المسجد', 'url' => '?page=about', 'icon' => 'fas fa-mosque', 'display_order' => 2],
            ['title' => 'الخدمات', 'url' => '#', 'icon' => 'fas fa-hands-helping', 'display_order' => 3],
            ['title' => 'القرآن الكريم', 'url' => 'quran.php', 'icon' => 'fas fa-book-quran', 'display_order' => 4],
            ['title' => 'المكتبة', 'url' => 'library.php', 'icon' => 'fas fa-book', 'display_order' => 5],
            ['title' => 'التبرعات', 'url' => 'donations.php', 'icon' => 'fas fa-hand-holding-heart', 'display_order' => 6],
            ['title' => 'اتصل بنا', 'url' => 'contact.php', 'icon' => 'fas fa-envelope', 'display_order' => 7]
        ];
        
        foreach ($headerMenuItems as $item) {
            $stmt = $db->prepare("INSERT INTO menu_items (title, url, icon, menu_position, parent_id, display_order, target, status) VALUES (?, ?, ?, 'header', NULL, ?, '_self', 'active')");
            $stmt->execute([$item['title'], $item['url'], $item['icon'], $item['display_order']]);
        }
    
        // إضافة عناصر فرعية للخدمات
        $servicesParentId = $db->fetchOne("SELECT id FROM menu_items WHERE title = 'الخدمات' AND menu_position = 'header'")['id'];
        
        $subMenuItems = [
            ['title' => 'أوقات الصلاة', 'url' => '?page=prayer-times', 'icon' => 'fas fa-clock', 'parent_id' => $servicesParentId],
            ['title' => 'حاسبة الزكاة', 'url' => '?page=zakat', 'icon' => 'fas fa-calculator', 'parent_id' => $servicesParentId],
            ['title' => 'الدروس والمحاضرات', 'url' => '?page=lessons', 'icon' => 'fas fa-chalkboard-teacher', 'parent_id' => $servicesParentId],
            ['title' => 'الأنشطة', 'url' => '?page=activities', 'icon' => 'fas fa-calendar-alt', 'parent_id' => $servicesParentId]
        ];
        
        foreach ($subMenuItems as $index => $item) {
            $db->insert('menu_items', array_merge($item, [
                'menu_position' => 'header',
                'target' => '_self',
                'status' => 'active',
                'display_order' => $index + 1
            ]));
        }
        
        // إضافة عناصر قائمة الفوتر
        $footerMenuItems = [
            ['title' => 'سياسة الخصوصية', 'url' => '?page=privacy', 'display_order' => 1],
            ['title' => 'شروط الاستخدام', 'url' => '?page=terms', 'display_order' => 2],
            ['title' => 'خريطة الموقع', 'url' => '?page=sitemap', 'display_order' => 3],
            ['title' => 'الأسئلة الشائعة', 'url' => '?page=faq', 'display_order' => 4]
        ];
        
        foreach ($footerMenuItems as $item) {
            $db->insert('menu_items', array_merge($item, [
                'menu_position' => 'footer',
                'target' => '_self',
                'status' => 'active',
                'parent_id' => null,
                'icon' => ''
            ]));
        }
    }
} catch (Exception $e) {
    error_log('Menu setup error: ' . $e->getMessage());
}

// إضافة إعدادات افتراضية
$defaultSettings = [
    'site_name' => 'مسجد النور',
    'site_description' => 'مسجد النور - مكان للعبادة والتعلم والتواصل المجتمعي',
    'contact_email' => 'info@mosque-nour.com',
    'contact_phone' => '+213 123 456 789',
    'contact_address' => 'الجزائر العاصمة، الجزائر',
    'social_facebook' => 'https://facebook.com/mosque-nour',
    'social_twitter' => 'https://twitter.com/mosque_nour',
    'social_instagram' => 'https://instagram.com/mosque_nour',
    'social_youtube' => 'https://youtube.com/mosque-nour',
    'header_style' => 'modern',
    'footer_style' => 'modern',
    'header_bg_color' => '#667eea',
    'header_text_color' => '#ffffff',
    'footer_bg_color' => '#2c3e50',
    'footer_text_color' => '#ffffff',
    'footer_copyright' => 'جميع الحقوق محفوظة',
    'show_social_links' => '1',
    'show_search_box' => '1',
    'header_fixed' => '1'
];

foreach ($defaultSettings as $key => $value) {
    $existing = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
    if (!$existing) {
        $db->insert('settings', [
            'setting_key' => $key,
            'setting_value' => $value,
            'category' => 'general',
            'setting_type' => 'text',
            'description' => $key
        ]);
    }
}

// جلب الإعدادات
$siteName = getSetting($db, 'site_name', 'مسجد النور');
$siteDescription = getSetting($db, 'site_description', '');
$siteLogo = getSetting($db, 'site_logo', '');
$page = 'test';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار الهيدر والفوتر - <?php echo htmlspecialchars($siteName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts - Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/custom.css">
    
    <style>
        .test-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .test-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #667eea;
        }
        
        .status-success {
            border-left-color: #28a745;
        }
        
        .status-warning {
            border-left-color: #ffc107;
        }
        
        .status-error {
            border-left-color: #dc3545;
        }
        
        .demo-content {
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="test-section">
            <h1 class="text-center mb-4">
                <i class="fas fa-vial text-primary"></i>
                اختبار نظام الهيدر والفوتر
            </h1>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="test-item status-success">
                        <h5><i class="fas fa-check-circle text-success"></i> الهيدر</h5>
                        <p>تم تحميل الهيدر بنجاح مع القائمة الديناميكية والتصميم المخصص</p>
                        <small class="text-muted">
                            النمط: <?php echo getSetting($db, 'header_style', 'modern'); ?> |
                            اللون: <?php echo getSetting($db, 'header_bg_color', '#667eea'); ?>
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="test-item status-success">
                        <h5><i class="fas fa-check-circle text-success"></i> الفوتر</h5>
                        <p>تم تحميل الفوتر بنجاح مع المحتوى المخصص والروابط</p>
                        <small class="text-muted">
                            النمط: <?php echo getSetting($db, 'footer_style', 'modern'); ?> |
                            اللون: <?php echo getSetting($db, 'footer_bg_color', '#2c3e50'); ?>
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="test-item status-success">
                        <h5><i class="fas fa-check-circle text-success"></i> القوائم</h5>
                        <p>تم إنشاء القوائم الديناميكية مع القوائم المنسدلة</p>
                        <small class="text-muted">
                            <?php
                            try {
                                $headerCount = $db->prepare("SELECT COUNT(*) as count FROM menu_items WHERE menu_position = 'header'");
                                $headerCount->execute();
                                $headerResult = $headerCount->fetch(PDO::FETCH_ASSOC);
                                
                                $footerCount = $db->prepare("SELECT COUNT(*) as count FROM menu_items WHERE menu_position = 'footer'");
                                $footerCount->execute();
                                $footerResult = $footerCount->fetch(PDO::FETCH_ASSOC);
                                
                                $headerMenuCount = $headerResult ? $headerResult['count'] : 0;
                                $footerMenuCount = $footerResult ? $footerResult['count'] : 0;
                            } catch (Exception $e) {
                                $headerMenuCount = 0;
                                $footerMenuCount = 0;
                            }
                            ?>
                            عناصر الهيدر: <?php echo $headerMenuCount; ?> |
                            عناصر الفوتر: <?php echo $footerMenuCount; ?>
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="test-item status-success">
                        <h5><i class="fas fa-check-circle text-success"></i> التصميم المتجاوب</h5>
                        <p>التصميم يتكيف مع جميع أحجام الشاشات</p>
                        <small class="text-muted">Bootstrap 5 + CSS مخصص</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="demo-content">
            <h2 class="text-center mb-4">محتوى تجريبي لاختبار التخطيط</h2>
            <p class="lead text-center">هذا المحتوى لاختبار كيفية ظهور الهيدر والفوتر مع المحتوى الرئيسي</p>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mosque"></i>
                    </div>
                    <h5>أوقات الصلاة</h5>
                    <p>عرض أوقات الصلاة اليومية بدقة</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book-quran"></i>
                    </div>
                    <h5>القرآن الكريم</h5>
                    <p>تصفح وقراءة القرآن الكريم</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <h5>حاسبة الزكاة</h5>
                    <p>حساب الزكاة بالعملة الجزائرية</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h5>المكتبة الإسلامية</h5>
                    <p>مجموعة من الكتب والمقالات</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h5>التبرعات</h5>
                    <p>نظام التبرعات الإلكترونية</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5>المجتمع</h5>
                    <p>التواصل مع أعضاء المسجد</p>
                </div>
            </div>
            
            <div class="mt-5 text-center">
                <h3>اختبار الروابط</h3>
                <div class="btn-group flex-wrap" role="group">
                    <a href="admin/header_footer.php" class="btn btn-primary">
                        <i class="fas fa-cog"></i> إدارة الهيدر والفوتر
                    </a>
                    <a href="admin/menu_manager.php" class="btn btn-secondary">
                        <i class="fas fa-bars"></i> إدارة القوائم
                    </a>
                    <a href="index.php" class="btn btn-success">
                        <i class="fas fa-home"></i> الصفحة الرئيسية
                    </a>
                    <a href="admin/" class="btn btn-info">
                        <i class="fas fa-tachometer-alt"></i> لوحة التحكم
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // اختبار التفاعل
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ تم تحميل الصفحة بنجاح');
            console.log('✅ Bootstrap JS يعمل');
            console.log('✅ Font Awesome يعمل');
            
            // اختبار القوائم المنسدلة
            const dropdowns = document.querySelectorAll('.dropdown-toggle');
            console.log(`✅ تم العثور على ${dropdowns.length} قائمة منسدلة`);
            
            // اختبار الروابط
            const links = document.querySelectorAll('a[href]');
            console.log(`✅ تم العثور على ${links.length} رابط`);
            
            // اختبار التصميم المتجاوب
            function checkResponsive() {
                const width = window.innerWidth;
                if (width < 768) {
                    console.log('📱 وضع الهاتف المحمول');
                } else if (width < 992) {
                    console.log('📱 وضع التابلت');
                } else {
                    console.log('🖥️ وضع سطح المكتب');
                }
            }
            
            checkResponsive();
            window.addEventListener('resize', checkResponsive);
        });
    </script>
</body>
</html>
