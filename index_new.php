<?php
require_once 'config/config_new.php';

// جلب إعدادات الموقع
$site_title = getSetting($pdo, 'site_title', 'موقع المسجد');
$site_description = getSetting($pdo, 'site_description', 'موقع إدارة المسجد');

// جلب الصفحة المطلوبة
$page_slug = isset($_GET['page']) ? sanitize($_GET['page']) : '';
$current_page = null;

if (!empty($page_slug)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND status = 'published'");
        $stmt->execute([$page_slug]);
        $current_page = $stmt->fetch();
    } catch (PDOException $e) {
        // تجاهل الخطأ
    }
}

// جلب الصفحات للقائمة
try {
    $stmt = $pdo->query("SELECT title, slug FROM pages WHERE status = 'published' ORDER BY created_at DESC LIMIT 10");
    $menu_pages = $stmt->fetchAll();
} catch (PDOException $e) {
    $menu_pages = [];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $current_page ? htmlspecialchars($current_page['title']) . ' - ' : ''; ?><?php echo htmlspecialchars($site_title); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            direction: rtl;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .nav {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        .nav li {
            margin: 0 15px;
        }
        .nav a {
            text-decoration: none;
            color: #333;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .nav a:hover {
            background-color: #e9ecef;
        }
        .main-content {
            background: white;
            margin: 20px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 400px;
        }
        .welcome {
            text-align: center;
            padding: 50px 0;
        }
        .welcome h2 {
            color: #667eea;
            margin-bottom: 20px;
        }
        .admin-link {
            background: #28a745;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
        }
        .admin-link:hover {
            background: #218838;
        }
        .page-content {
            line-height: 1.8;
        }
        .page-content h1, .page-content h2, .page-content h3 {
            color: #333;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .footer {
            background: #343a40;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1><?php echo htmlspecialchars($site_title); ?></h1>
            <p><?php echo htmlspecialchars($site_description); ?></p>
        </div>
    </div>

    <nav class="nav">
        <div class="container">
            <ul>
                <li><a href="index_new.php">الرئيسية</a></li>
                <?php foreach ($menu_pages as $menu_page): ?>
                    <li><a href="?page=<?php echo urlencode($menu_page['slug']); ?>"><?php echo htmlspecialchars($menu_page['title']); ?></a></li>
                <?php endforeach; ?>
                <li><a href="admin/pages_working.php">لوحة التحكم</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <?php if ($current_page): ?>
                <div class="page-content">
                    <h1><?php echo htmlspecialchars($current_page['title']); ?></h1>
                    <div><?php echo $current_page['content']; ?></div>
                </div>
            <?php else: ?>
                <div class="welcome">
                    <h2>مرحباً بكم في <?php echo htmlspecialchars($site_title); ?></h2>
                    <p>هذا هو الموقع الرسمي لإدارة المسجد</p>
                    <p>يمكنكم تصفح الصفحات من القائمة أعلاه</p>
                    
                    <?php if (checkLogin()): ?>
                        <a href="admin/pages_working.php" class="admin-link">دخول لوحة التحكم</a>
                    <?php else: ?>
                        <a href="login.php" class="admin-link">تسجيل الدخول</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_title); ?>. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
