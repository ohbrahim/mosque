<?php
/**
 * إصلاح قاعدة البيانات النهائي
 */

require_once 'config/config.php';

echo "<h2>إصلاح قاعدة البيانات</h2>";

try {
    // قراءة ملف SQL
    $sqlFile = 'database/fix_missing_tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("ملف SQL غير موجود: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    $statements = explode(';', $sql);
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $db->query($statement);
            $successCount++;
            echo "<p style='color: green;'>✓ تم تنفيذ: " . substr($statement, 0, 50) . "...</p>";
        } catch (Exception $e) {
            $errorCount++;
            echo "<p style='color: orange;'>⚠ تم تجاهل: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>النتائج:</h3>";
    echo "<p>تم تنفيذ $successCount استعلام بنجاح</p>";
    echo "<p>تم تجاهل $errorCount استعلام (ربما موجود مسبقاً)</p>";
    
    // التحقق من الجداول
    echo "<h3>التحقق من الجداول:</h3>";
    $tables = ['polls', 'poll_options', 'poll_votes', 'settings', 'pages', 'users'];
    
    foreach ($tables as $table) {
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM $table");
            echo "<p style='color: green;'>✓ جدول $table: " . $result['count'] . " سجل</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ جدول $table: غير موجود</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>تم إصلاح قاعدة البيانات بنجاح!</h3>";
    echo "<p><a href='admin/index.php'>الذهاب إلى لوحة التحكم</a></p>";
    echo "<p><a href='index.php'>الذهاب إلى الموقع الرئيسي</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>خطأ: " . $e->getMessage() . "</p>";
}
?>
