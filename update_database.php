<?php
/**
 * تحديث هيكل قاعدة البيانات
 */

// تضمين ملف الإعدادات
require_once 'config/config.php';

// قراءة ملف SQL
$sqlFile = __DIR__ . '/database/update_tables_structure.sql';
$sql = file_get_contents($sqlFile);

// تقسيم الأوامر SQL
$queries = explode(';', $sql);

// تنفيذ الاستعلامات
$success = true;
$errors = [];

try {
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        try {
            $db->query($query);
        } catch (Exception $e) {
            // تجاهل أخطاء "Column already exists"
            if (strpos($e->getMessage(), 'Duplicate column name') === false &&
                strpos($e->getMessage(), 'already exists') === false) {
                $errors[] = $e->getMessage();
                $success = false;
            }
        }
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
    $success = false;
}

// عرض النتيجة
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحديث قاعدة البيانات</title>
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
    </style>
</head>
<body>
    <div class="container text-center">
        <?php if ($success): ?>
            <div class="success-icon">✓</div>
            <h2 class="mb-4">تم تحديث قاعدة البيانات بنجاح!</h2>
            <p class="mb-4">تم تحديث هيكل الجداول وإضافة الأعمدة المفقودة.</p>
            
            <div class="d-flex justify-content-center gap-3 mt-4">
                <a href="fix_function_conflicts.php" class="btn btn-primary">إصلاح تضارب الدوال</a>
                <a href="admin/index.php" class="btn btn-success">الذهاب للوحة التحكم</a>
            </div>
        <?php else: ?>
            <div class="error-icon">✗</div>
            <h2 class="mb-4">حدث خطأ أثناء تحديث قاعدة البيانات</h2>
            
            <div class="alert alert-danger text-start">
                <h5>تفاصيل الأخطاء:</h5>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="d-flex justify-content-center gap-3 mt-4">
                <a href="index.php" class="btn btn-secondary">العودة للرئيسية</a>
                <a href="fix_function_conflicts.php" class="btn btn-primary">إصلاح تضارب الدوال</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
