<?php
require_once 'config/simple_config.php';

// الحصول على الصفحة المطلوبة
$page = $_GET['page'] ?? 'home';
$pageData = null;

if ($page !== 'home') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published'");
        $stmt->execute([$page]);
        $pageData = $stmt->fetch();
    } catch (Exception $e) {
        // تجاهل الأخطاء
    }
}

// جلب آخر الصفحات للصفحة الرئيسية
$latestPages = [];
if ($page === 'home') {
    try {
        $stmt = $pdo->query("SELECT * FROM pages WHERE status = 'published' ORDER BY created_at DESC LIMIT 5");
        $latestPages = $stmt->fetchAll();
    } catch (Exception $e) {
        // تجاهل الأخطاء
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مسجد النور</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; }
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 60px 0; }
        .prayer-times { background: #f8f9fa; padding: 20px; border-radius: 10px; }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="simple_index.php">
                <i class="fas fa-mosque"></i> مسجد النور
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="simple_index.php">الرئيسية</a>
                <a class="nav-link" href="admin/simple_admin.php">لوحة التحكم</a>
            </div>
        </div>
    </nav>

    <?php if ($page === 'home'): ?>
        <!-- Hero Section -->
        <section class="hero text-center">
            <div class="container">
                <h1 class="display-4">مرحباً بكم في مسجد النور</h1>
                <p class="lead">موقع مسجد النور - مرحباً بكم في بيت الله</p>
            </div>
        </section>
    <?php endif; ?>

    <div class="container my-5">
        <div class="row">
            <!-- المحتوى الرئيسي -->
            <div class="col-md-8">
                <?php if ($page === 'home'): ?>
                    <h2>أهلاً بكم في مسجد النور</h2>
                    <p>مرحباً بكم في موقع مسجدنا الإلكتروني. نسعى لتقديم خدمات متنوعة للمجتمع المسلم.</p>
                    
                    <?php if (!empty($latestPages)): ?>
                        <h3 class="mt-4">آخر الصفحات</h3>
                        <div class="row">
                            <?php foreach ($latestPages as $latestPage): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($latestPage['title']); ?></h5>
                                            <p class="card-text"><?php echo truncateText($latestPage['content'], 100); ?></p>
                                            <a href="?page=<?php echo $latestPage['slug']; ?>" class="btn btn-primary btn-sm">اقرأ المزيد</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php elseif ($pageData): ?>
                    <h1><?php echo htmlspecialchars($pageData['title']); ?></h1>
                    <div class="content">
                        <?php echo nl2br(htmlspecialchars($pageData['content'])); ?>
                    </div>
                    <small class="text-muted">تاريخ النشر: <?php echo formatArabicDate($pageData['created_at']); ?></small>
                    
                <?php else: ?>
                    <h2>الصفحة غير موجودة</h2>
                    <p>عذراً، الصفحة التي تبحث عنها غير موجودة.</p>
                    <a href="simple_index.php" class="btn btn-primary">العودة للرئيسية</a>
                <?php endif; ?>
            </div>
            
            <!-- الشريط الجانبي -->
            <div class="col-md-4">
                <div class="prayer-times">
                    <h5><i class="fas fa-clock"></i> أوقات الصلاة</h5>
                    <div class="d-flex justify-content-between"><span>الفجر:</span> <strong>04:30</strong></div>
                    <div class="d-flex justify-content-between"><span>الظهر:</span> <strong>12:15</strong></div>
                    <div class="d-flex justify-content-between"><span>العصر:</span> <strong>15:45</strong></div>
                    <div class="d-flex justify-content-between"><span>المغرب:</span> <strong>18:30</strong></div>
                    <div class="d-flex justify-content-between"><span>العشاء:</span> <strong>20:00</strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <div class="container">
            <p>&copy; 2024 مسجد النور - جميع الحقوق محفوظة</p>
        </div>
    </footer>
</body>
</html>
