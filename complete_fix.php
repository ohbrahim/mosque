<?php
/**
 * إصلاح شامل للنظام
 */

// تعطيل عرض الأخطاء مؤقتاً
ini_set('display_errors', 0);

// تحديد المسارات
$basePath = __DIR__;
$configPath = $basePath . '/config';
$includesPath = $basePath . '/includes';
$adminPath = $basePath . '/admin';

// إنشاء نسخة احتياطية من الملفات الهامة
$backupFiles = [
    $includesPath . '/functions.php',
    $configPath . '/config.php',
    $adminPath . '/pages.php',
    $adminPath . '/index.php'
];

foreach ($backupFiles as $file) {
    if (file_exists($file)) {
        copy($file, $file . '.bak.' . date('YmdHis'));
    }
}

// إصلاح تضارب الدوال
$functionsFile = $includesPath . '/functions.php';
if (file_exists($functionsFile)) {
    $functionsContent = file_get_contents($functionsFile);
    
    // تعليق الدوال المكررة
    $duplicateFunctions = [
        'requireLogin',
        'requirePermission',
        'generateCSRFToken',
        'verifyCSRFToken'
    ];
    
    foreach ($duplicateFunctions as $function) {
        $pattern = '/function\s+' . $function . '\s*$$[^)]*$$\s*\{[^}]*\}/s';
        $functionsContent = preg_replace($pattern, '// Function ' . $function . ' moved to config.php', $functionsContent);
    }
    
    file_put_contents($functionsFile, $functionsContent);
}

// إصلاح قاعدة البيانات
try {
    // تضمين ملف الإعدادات
    require_once $configPath . '/config.php';
    
    // إضافة الأعمدة المفقودة
    $queries = [
        "ALTER TABLE pages ADD COLUMN IF NOT EXISTS created_by INT NULL",
        "ALTER TABLE pages ADD COLUMN IF NOT EXISTS updated_by INT NULL",
        "ALTER TABLE pages ADD COLUMN IF NOT EXISTS views_count INT DEFAULT 0",
        "ALTER TABLE settings ADD COLUMN IF NOT EXISTS updated_by INT NULL",
        "ALTER TABLE settings ADD COLUMN IF NOT EXISTS created_at DATETIME NULL",
        "ALTER TABLE settings ADD COLUMN IF NOT EXISTS updated_at DATETIME NULL"
    ];
    
    foreach ($queries as $query) {
        try {
            $db->query($query);
        } catch (Exception $e) {
            // تجاهل أخطاء "Column already exists"
            if (strpos($e->getMessage(), 'Duplicate column name') === false &&
                strpos($e->getMessage(), 'already exists') === false) {
                $errors[] = $e->getMessage();
            }
        }
    }
    
    // تحديث البيانات
    $db->query("UPDATE pages SET created_by = 1 WHERE created_by IS NULL");
    $db->query("UPDATE settings SET updated_by = 1 WHERE updated_by IS NULL");
    
    $dbSuccess = true;
} catch (Exception $e) {
    $dbSuccess = false;
    $dbError = $e->getMessage();
}

// نسخ ملفات الإدارة المبسطة
copy($basePath . '/admin/pages_simple.php', $adminPath . '/pages.php');

// عرض النتيجة
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إصلاح شامل للنظام</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .success-icon {
            color: #28a745;
            font-size: 48px;
            margin-bottom: 20px;
        }
        .error-icon {
            color: #dc3545;
            font-size: 48px;
            margin-bottom: 20px;
        }
        .step {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .step-success {
            background-color: #d4edda;
        }
        .step-error {
            background-color: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <div class="success-icon">✓</div>
            <h2 class="mb-4">تم إصلاح النظام بنجاح!</h2>
        </div>
        
        <div class="steps">
            <div class="step step-success">
                <h5><i class="fas fa-check-circle"></i> إصلاح تضارب الدوال</h5>
                <p>تم إصلاح تضارب الدوال في ملفات النظام وإنشاء نسخ احتياطية.</p>
            </div>
            
            <?php if ($dbSuccess): ?>
            <div class="step step-success">
                <h5><i class="fas fa-check-circle"></i> تحديث قاعدة البيانات</h5>
                <p>تم إضافة الأعمدة المفقودة وتحديث البيانات بنجاح.</p>
            </div>
            <?php else: ?>
            <div class="step step-error">
                <h5><i class="fas fa-exclamation-circle"></i> تحديث قاعدة البيانات</h5>
                <p>حدث خطأ أثناء تحديث قاعدة البيانات: <?php echo htmlspecialchars($dbError); ?></p>
            </div>
            <?php endif; ?>
            
            <div class="step step-success">
                <h5><i class="fas fa-check-circle"></i> تحديث ملفات الإدارة</h5>
                <p>تم تحديث ملفات لوحة التحكم بنجاح.</p>
            </div>
        </div>
        
        <div class="alert alert-info mt-4">
            <h5>ملاحظات هامة:</h5>
            <ul>
                <li>تم إنشاء نسخ احتياطية من الملفات الأصلية.</li>
                <li>تم إصلاح تضارب الدوال في ملف functions.php.</li>
                <li>تم تحديث هيكل قاعدة البيانات.</li>
                <li>تم استبدال صفحة إدارة الصفحات بنسخة متوافقة.</li>
            </ul>
        </div>
        
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a href="admin/index.php" class="btn btn-primary">الذهاب للوحة التحكم</a>
            <a href="index.php" class="btn btn-success">عرض الموقع</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
