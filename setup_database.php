<?php
/**
 * ملف إعداد قاعدة البيانات السريع
 */

// إعدادات قاعدة البيانات
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'mosque_management';

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>إعداد قاعدة البيانات</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap' rel='stylesheet'>
    <style>
        body { font-family: 'Cairo', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .setup-container { max-width: 800px; margin: 50px auto; }
        .setup-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .step { padding: 15px; margin: 10px 0; border-radius: 8px; }
        .step.success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .step.error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .step.info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .progress-bar { transition: width 0.3s ease; }
    </style>
</head>
<body>
    <div class='container setup-container'>
        <div class='setup-card'>
            <h2 class='text-center mb-4'>
                <i class='fas fa-database'></i>
                إعداد قاعدة البيانات - نظام إدارة المسجد
            </h2>";

try {
    // الاتصال بـ MySQL بدون تحديد قاعدة بيانات
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<div class='step success'>
            <i class='fas fa-check-circle'></i>
            <strong>تم الاتصال بخادم قاعدة البيانات بنجاح</strong>
          </div>";
    
    // قراءة ملف SQL
    $sqlFile = __DIR__ . '/database/complete_database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("ملف قاعدة البيانات غير موجود: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new Exception("فشل في قراءة ملف قاعدة البيانات");
    }
    
    echo "<div class='step info'>
            <i class='fas fa-file-alt'></i>
            <strong>تم قراءة ملف قاعدة البيانات بنجاح</strong>
            <br><small>حجم الملف: " . number_format(strlen($sql)) . " حرف</small>
          </div>";
    
    // تقسيم الاستعلامات
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
        }
    );
    
    $totalStatements = count($statements);
    $successCount = 0;
    $errorCount = 0;
    
    echo "<div class='step info'>
            <i class='fas fa-list'></i>
            <strong>عدد الاستعلامات المراد تنفيذها: $totalStatements</strong>
          </div>";
    
    echo "<div class='progress mb-3'>
            <div class='progress-bar bg-success' role='progressbar' style='width: 0%' id='progressBar'></div>
          </div>";
    
    echo "<div id='executionLog' style='max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f8f9fa; margin: 20px 0;'>";
    
    // تنفيذ الاستعلامات
    foreach ($statements as $index => $statement) {
        try {
            $pdo->exec($statement);
            $successCount++;
            
            // تحديد نوع الاستعلام
            $statementType = '';
            if (preg_match('/^\s*CREATE\s+DATABASE/i', $statement)) {
                $statementType = 'إنشاء قاعدة البيانات';
            } elseif (preg_match('/^\s*USE\s+/i', $statement)) {
                $statementType = 'تحديد قاعدة البيانات';
            } elseif (preg_match('/^\s*CREATE\s+TABLE\s+(\w+)/i', $statement, $matches)) {
                $statementType = "إنشاء جدول: {$matches[1]}";
            } elseif (preg_match('/^\s*INSERT\s+INTO\s+(\w+)/i', $statement, $matches)) {
                $statementType = "إدراج بيانات في: {$matches[1]}";
            } elseif (preg_match('/^\s*CREATE\s+INDEX/i', $statement)) {
                $statementType = 'إنشاء فهرس';
            } elseif (preg_match('/^\s*CREATE\s+VIEW\s+(\w+)/i', $statement, $matches)) {
                $statementType = "إنشاء مشاهدة: {$matches[1]}";
            } elseif (preg_match('/^\s*CREATE\s+PROCEDURE\s+(\w+)/i', $statement, $matches)) {
                $statementType = "إنشاء إجراء: {$matches[1]}";
            } elseif (preg_match('/^\s*CREATE\s+TRIGGER\s+(\w+)/i', $statement, $matches)) {
                $statementType = "إنشاء محفز: {$matches[1]}";
            } elseif (preg_match('/^\s*CREATE\s+FUNCTION\s+(\w+)/i', $statement, $matches)) {
                $statementType = "إنشاء دالة: {$matches[1]}";
            } else {
                $statementType = 'استعلام عام';
            }
            
            echo "<div style='color: green; margin: 2px 0;'>
                    <i class='fas fa-check'></i> 
                    ✓ $statementType
                  </div>";
            
        } catch (PDOException $e) {
            $errorCount++;
            $errorMsg = $e->getMessage();
            
            echo "<div style='color: red; margin: 2px 0;'>
                    <i class='fas fa-times'></i> 
                    ✗ خطأ في الاستعلام " . ($index + 1) . ": $errorMsg
                  </div>";
        }
        
        // تحديث شريط التقدم
        $progress = (($index + 1) / $totalStatements) * 100;
        echo "<script>
                document.getElementById('progressBar').style.width = '{$progress}%';
                document.getElementById('progressBar').textContent = '" . round($progress) . "%';
              </script>";
        
        // تحديث الصفحة تدريجياً
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
        
        // توقف قصير لإظهار التقدم
        usleep(50000); // 0.05 ثانية
    }
    
    echo "</div>";
    
    // النتائج النهائية
    if ($errorCount == 0) {
        echo "<div class='step success'>
                <i class='fas fa-check-circle'></i>
                <strong>تم إنشاء قاعدة البيانات بنجاح!</strong>
                <br>تم تنفيذ جميع الاستعلامات ($successCount) بنجاح
              </div>";
        
        echo "<div class='step info'>
                <i class='fas fa-info-circle'></i>
                <strong>بيانات تسجيل الدخول:</strong>
                <br>اسم المستخدم: <code>admin</code>
                <br>كلمة المرور: <code>admin123</code>
              </div>";
        
        echo "<div class='text-center mt-4'>
                <a href='login.php' class='btn btn-primary btn-lg'>
                    <i class='fas fa-sign-in-alt'></i>
                    الذهاب إلى صفحة تسجيل الدخول
                </a>
                <a href='index.php' class='btn btn-success btn-lg ms-2'>
                    <i class='fas fa-home'></i>
                    عرض الموقع
                </a>
              </div>";
        
    } else {
        echo "<div class='step error'>
                <i class='fas fa-exclamation-triangle'></i>
                <strong>تم إنشاء قاعدة البيانات مع بعض الأخطاء</strong>
                <br>نجح: $successCount | فشل: $errorCount
              </div>";
        
        echo "<div class='step info'>
                <i class='fas fa-info-circle'></i>
                <strong>يمكنك المتابعة رغم الأخطاء</strong>
                <br>معظم الأخطاء عادة ما تكون بسبب وجود البيانات مسبقاً
              </div>";
        
        echo "<div class='text-center mt-4'>
                <a href='login.php' class='btn btn-warning btn-lg'>
                    <i class='fas fa-sign-in-alt'></i>
                    المحاولة مع تسجيل الدخول
                </a>
              </div>";
    }
    
} catch (Exception $e) {
    echo "<div class='step error'>
            <i class='fas fa-exclamation-triangle'></i>
            <strong>خطأ في إعداد قاعدة البيانات:</strong>
            <br>" . htmlspecialchars($e->getMessage()) . "
          </div>";
    
    echo "<div class='step info'>
            <i class='fas fa-info-circle'></i>
            <strong>تأكد من:</strong>
            <ul>
                <li>تشغيل خادم MySQL</li>
                <li>صحة بيانات الاتصال</li>
                <li>وجود صلاحيات إنشاء قواعد البيانات</li>
            </ul>
          </div>";
}

echo "        </div>
    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js'></script>
</body>
</html>";
?>
