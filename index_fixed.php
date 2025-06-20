<?php
require_once 'config/config.php';

// دوال عرض البلوكات مدمجة في نفس الملف
function renderBlock($block) {
    global $db;
    
    // التحقق من الإعدادات
    $allowIframe = getSetting($db, 'allow_iframe', '1');
    $allowMarquee = getSetting($db, 'allow_marquee', '1');
    $allowCustomHtml = getSetting($db, 'allow_custom_html', '1');
    
    $cssClass = 'block-widget mb-4 ' . ($block['css_class'] ?: '');
    
    $html = '<div class="' . $cssClass . '" id="block-' . $block['id'] . '">';
    
    // إضافة CSS مخصص
    if (!empty($block['custom_css'])) {
        $html .= '<style>' . $block['custom_css'] . '</style>';
    }
    
    // عرض العنوان إذا كان مطلوباً
    if ($block['show_title'] && !empty($block['title'])) {
        $html .= '<div class="block-header mb-3">';
        $html .= '<h5 class="block-title text-primary border-bottom pb-2">';
        $html .= '<i class="fas fa-cube me-2"></i>';
        $html .= htmlspecialchars($block['title']);
        $html .= '</h5>';
        $html .= '</div>';
    }
    
    $html .= '<div class="block-content">';
    
    // عرض المحتوى حسب نوع البلوك
    switch ($block['block_type']) {
        case 'iframe':
            // السماح بـ iframe بشكل آمن
            $html .= '<div class="iframe-container">' . $block['content'] . '</div>';
            break;
            
        case 'marquee':
            // السماح بـ marquee
            $html .= '<div class="marquee-container">' . $block['content'] . '</div>';
            break;
            
        case 'custom':
        case 'html':
            $html .= $block['content'];
            break;
            
        case 'text':
            $html .= '<div class="text-content">';
            $html .= nl2br(htmlspecialchars($block['content']));
            $html .= '</div>';
            break;
            
        case 'announcement':
            $html .= '<div class="alert alert-info">';
            $html .= '<i class="fas fa-bullhorn me-2"></i>';
            $html .= $block['content'];
            $html .= '</div>';
            break;
            
        case 'prayer_times':
            $html .= renderPrayerTimesWidget();
            break;
            
        case 'weather':
            $html .= renderWeatherWidget();
            break;
            
        case 'quran_verse':
            $html .= renderQuranVerseWidget();
            break;
            
        case 'recent_pages':
            $html .= renderRecentPagesWidget($db);
            break;
            
        default:
            $html .= '<div class="alert alert-secondary">';
            $html .= '<i class="fas fa-info-circle me-2"></i>';
            $html .= 'نوع البلوك: ' . htmlspecialchars($block['block_type']);
            $html .= '</div>';
            break;
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

function renderPrayerTimesWidget() {
    $prayerTimes = [
        'الفجر' => '04:30',
        'الظهر' => '12:15', 
        'العصر' => '15:45',
        'المغرب' => '18:30',
        'العشاء' => '20:00'
    ];
    
    $html = '<div class="prayer-times-widget">';
    $html .= '<div class="card border-primary">';
    $html .= '<div class="card-body">';
    
    foreach ($prayerTimes as $prayer => $time) {
        $html .= '<div class="d-flex justify-content-between align-items-center py-2 border-bottom">';
        $html .= '<span class="prayer-name fw-bold">' . $prayer . '</span>';
        $html .= '<span class="prayer-time text-primary fw-bold">' . $time . '</span>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

function renderWeatherWidget() {
    $temp = rand(20, 35);
    $condition = 'مشمس';
    
    $html = '<div class="weather-widget text-center">';
    $html .= '<div class="card border-info">';
    $html .= '<div class="card-body">';
    $html .= '<i class="fas fa-sun fa-3x text-warning mb-3"></i>';
    $html .= '<h3 class="text-primary">' . $temp . '°C</h3>';
    $html .= '<p class="text-muted">' . $condition . '</p>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

function renderQuranVerseWidget() {
    $verses = [
        ['text' => 'وَمَا خَلَقْتُ الْجِنَّ وَالْإِنسَ إِلَّا لِيَعْبُدُونِ', 'surah' => 'الذاريات', 'ayah' => '56'],
        ['text' => 'إِنَّ اللَّهَ لَا يُضِيعُ أَجْرَ الْمُحْسِنِينَ', 'surah' => 'التوبة', 'ayah' => '120']
    ];
    
    $verse = $verses[array_rand($verses)];
    
    $html = '<div class="quran-verse-widget text-center">';
    $html .= '<div class="card border-success">';
    $html .= '<div class="card-body">';
    $html .= '<i class="fas fa-book-open text-success mb-3" style="font-size: 2rem;"></i>';
    $html .= '<p class="fs-6 fw-bold text-dark mb-3">' . $verse['text'] . '</p>';
    $html .= '<small class="text-muted">سورة ' . $verse['surah'] . ' - آية ' . $verse['ayah'] . '</small>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

function renderRecentPagesWidget($db) {
    try {
        $pages = $db->fetchAll("SELECT title, slug, created_at FROM pages WHERE status = 'published' ORDER BY created_at DESC LIMIT 5");
        
        $html = '<div class="recent-pages-widget">';
        $html .= '<div class="list-group">';
        
        if (empty($pages)) {
            $html .= '<div class="list-group-item text-center text-muted">لا توجد صفحات حديثة</div>';
        } else {
            foreach ($pages as $page) {
                $html .= '<a href="?page=' . htmlspecialchars($page['slug']) . '" class="list-group-item list-group-item-action">';
                $html .= '<div class="d-flex w-100 justify-content-between">';
                $html .= '<h6 class="mb-1">' . htmlspecialchars($page['title']) . '</h6>';
                $html .= '<small>' . date('d/m/Y', strtotime($page['created_at'])) . '</small>';
                $html .= '</div>';
                $html .= '</a>';
            }
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    } catch (Exception $e) {
        return '<div class="alert alert-warning">تعذر جلب الصفحات الحديثة</div>';
    }
}

// تسجيل زيارة الصفحة
if (function_exists('logVisitor')) {
    logVisitor($db, $_SERVER['REQUEST_URI']);
}

// جلب الصفحة المطلوبة
$pageSlug = isset($_GET['page']) ? sanitize($_GET['page']) : '';
$currentPage = null;

if ($pageSlug) {
    try {
        $currentPage = $db->fetchOne("SELECT * FROM pages WHERE slug = ? AND status = 'published'", [$pageSlug]);
        if ($currentPage) {
            $db->query("UPDATE pages SET views_count = views_count + 1 WHERE id = ?", [$currentPage['id']]);
        }
    } catch (Exception $e) {
        error_log("Error fetching page: " . $e->getMessage());
    }
}

// جلب البلوكات النشطة
try {
    $rightBlocks = $db->fetchAll("SELECT * FROM blocks WHERE status = 'active' AND position = 'right' ORDER BY display_order");
    $leftBlocks = $db->fetchAll("SELECT * FROM blocks WHERE status = 'active' AND position = 'left' ORDER BY display_order");
    $topBlocks = $db->fetchAll("SELECT * FROM blocks WHERE status = 'active' AND position = 'top' ORDER BY display_order");
    $bottomBlocks = $db->fetchAll("SELECT * FROM blocks WHERE status = 'active' AND position = 'bottom' ORDER BY display_order");
    $centerBlocks = $db->fetchAll("SELECT * FROM blocks WHERE status = 'active' AND position = 'center' ORDER BY display_order");
} catch (Exception $e) {
    $rightBlocks = $leftBlocks = $topBlocks = $bottomBlocks = $centerBlocks = [];
    error_log("Error fetching blocks: " . $e->getMessage());
}

// إعدادات الموقع
$siteName = getSetting($db, 'site_name', 'مسجد النور');
$siteDescription = getSetting($db, 'site_description', 'موقع مسجد النور الرسمي');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $currentPage ? htmlspecialchars($currentPage['title']) . ' - ' : ''; ?><?php echo htmlspecialchars($siteName); ?></title>
    <meta name="description" content="<?php echo $currentPage ? htmlspecialchars($currentPage['meta_description'] ?: $currentPage['excerpt']) : htmlspecialchars($siteDescription); ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts - Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            line-height: 1.6;
            background-color: #f8f9fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, #2c5530 0%, #1e3a1e 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #2c5530 0%, #1e3a1e 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .block-widget {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .block-widget:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .block-title {
            color: #2c5530;
            font-weight: 600;
        }
        
        .iframe-container iframe {
            border-radius: 8px;
            width: 100%;
            max-width: 100%;
        }
        
        .marquee-container {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 8px;
            padding: 10px;
            border-left: 4px solid #dc3545;
        }
        
        .prayer-times-widget .prayer-time {
            font-family: 'Courier New', monospace;
        }
        
        .footer {
            background: #2c5530;
            color: white;
            padding: 50px 0 30px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-mosque me-2"></i>
                <?php echo htmlspecialchars($siteName); ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=about">عن المسجد</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=services">الخدمات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=events">الفعاليات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">اتصل بنا</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                مرحباً، <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (function_exists('hasPermission') && hasPermission('admin_access')): ?>
                                    <li><a class="dropdown-item" href="admin/index.php"><i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>الملف الشخصي</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i> تسجيل الدخول</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Top Blocks -->
    <?php if (!empty($topBlocks)): ?>
        <div class="container-fluid mt-3">
            <div class="row">
                <div class="col-12">
                    <?php foreach ($topBlocks as $block): ?>
                        <?php echo renderBlock($block); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <?php if ($currentPage): ?>
        <!-- Single Page View -->
        <div class="container mt-4">
            <div class="row">
                <!-- Left Sidebar -->
                <div class="col-lg-3">
                    <?php foreach ($leftBlocks as $block): ?>
                        <?php echo renderBlock($block); ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Main Content -->
                <div class="col-lg-6">
                    <article class="block-widget">
                        <?php if ($currentPage['featured_image']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($currentPage['featured_image']); ?>" 
                                 class="img-fluid rounded mb-3" alt="<?php echo htmlspecialchars($currentPage['title']); ?>">
                        <?php endif; ?>
                        
                        <h1 class="mb-3"><?php echo htmlspecialchars($currentPage['title']); ?></h1>
                        
                        <div class="text-muted mb-3">
                            <i class="fas fa-calendar me-2"></i>
                            <?php echo function_exists('formatArabicDate') ? formatArabicDate($currentPage['created_at']) : date('Y-m-d', strtotime($currentPage['created_at'])); ?>
                            <i class="fas fa-eye me-2 ms-3"></i>
                            <?php echo $currentPage['views_count']; ?> مشاهدة
                        </div>
                        
                        <div class="content">
                            <?php echo nl2br($currentPage['content']); ?>
                        </div>
                    </article>
                    
                    <!-- Center Blocks -->
                    <?php foreach ($centerBlocks as $block): ?>
                        <?php echo renderBlock($block); ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Right Sidebar -->
                <div class="col-lg-3">
                    <?php foreach ($rightBlocks as $block): ?>
                        <?php echo renderBlock($block); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Homepage -->
        <div class="hero-section">
            <div class="container">
                <h1>مرحباً بكم في <?php echo htmlspecialchars($siteName); ?></h1>
                <p class="lead"><?php echo htmlspecialchars($siteDescription); ?></p>
                <div class="mt-4">
                    <a href="?page=about" class="btn btn-light btn-lg me-2">تعرف علينا</a>
                    <a href="contact.php" class="btn btn-outline-light btn-lg">تواصل معنا</a>
                </div>
            </div>
        </div>
        
        <div class="container mt-5">
            <div class="row">
                <!-- Left Sidebar -->
                <div class="col-lg-3">
                    <?php foreach ($leftBlocks as $block): ?>
                        <?php echo renderBlock($block); ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Main Content -->
                <div class="col-lg-6">
                    <?php foreach ($centerBlocks as $block): ?>
                        <?php echo renderBlock($block); ?>
                    <?php endforeach; ?>
                    
                    <div class="block-widget text-center">
                        <h2 class="mb-4">مرحباً بكم في موقع المسجد</h2>
                        <p class="lead">نسعد بزيارتكم ونتمنى أن تجدوا ما تبحثون عنه</p>
                        <div class="row mt-4">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-mosque fa-3x text-primary mb-3"></i>
                                        <h5>أوقات الصلاة</h5>
                                        <p>تابع أوقات الصلاة اليومية</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-book-open fa-3x text-success mb-3"></i>
                                        <h5>الدروس والمحاضرات</h5>
                                        <p>استمع للدروس والمحاضرات الدينية</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-calendar-alt fa-3x text-info mb-3"></i>
                                        <h5>الفعاليات</h5>
                                        <p>تابع فعاليات وأنشطة المسجد</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Sidebar -->
                <div class="col-lg-3">
                    <?php foreach ($rightBlocks as $block): ?>
                        <?php echo renderBlock($block); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bottom Blocks -->
    <?php if (!empty($bottomBlocks)): ?>
        <div class="container mt-5">
            <div class="row">
                <div class="col-12">
                    <?php foreach ($bottomBlocks as $block): ?>
                        <?php echo renderBlock($block); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><?php echo htmlspecialchars($siteName); ?></h5>
                    <p><?php echo htmlspecialchars($siteDescription); ?></p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>روابط سريعة</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-light">الرئيسية</a></li>
                        <li><a href="?page=about" class="text-light">عن المسجد</a></li>
                        <li><a href="?page=services" class="text-light">الخدمات</a></li>
                        <li><a href="contact.php" class="text-light">اتصل بنا</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>تواصل معنا</h5>
                    <p><i class="fas fa-map-marker-alt me-2"></i> عنوان المسجد</p>
                    <p><i class="fas fa-phone me-2"></i> 0123456789</p>
                    <p><i class="fas fa-envelope me-2"></i> info@mosque.com</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?>. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // تحسين عرض البلوكات
        document.addEventListener('DOMContentLoaded', function() {
            // تحسين عرض iframe
            const iframes = document.querySelectorAll('iframe');
            iframes.forEach(iframe => {
                iframe.style.maxWidth = '100%';
                iframe.style.height = 'auto';
            });
            
            // تحسين عرض marquee
            const marquees = document.querySelectorAll('marquee');
            marquees.forEach(marquee => {
                marquee.addEventListener('mouseenter', function() {
                    this.stop();
                });
                marquee.addEventListener('mouseleave', function() {
                    this.start();
                });
            });
        });
    </script>
</body>
</html>
