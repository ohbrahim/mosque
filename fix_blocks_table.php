<?php
require_once 'config/config.php';

echo "<h1>إصلاح جدول البلوكات</h1>";

try {
    // 1. التحقق من وجود عمود created_by
    $tableInfo = $db->fetchAll("DESCRIBE blocks");
    $hasCreatedBy = false;
    
    foreach ($tableInfo as $column) {
        if ($column['Field'] === 'created_by') {
            $hasCreatedBy = true;
            break;
        }
    }
    
    if (!$hasCreatedBy) {
        // إضافة عمود created_by إذا لم يكن موجوداً
        $db->query("ALTER TABLE blocks ADD COLUMN created_by INT NULL");
        echo "<p style='color: green;'>✅ تم إضافة عمود created_by بنجاح!</p>";
    } else {
        echo "<p>عمود created_by موجود بالفعل.</p>";
    }
    
    // 2. تحديث البلوكات الموجودة لتعيين قيمة افتراضية لـ created_by
    $db->query("UPDATE blocks SET created_by = 1 WHERE created_by IS NULL");
    echo "<p style='color: green;'>✅ تم تحديث البلوكات الموجودة بنجاح!</p>";
    
    echo "<h2>✅ تم إصلاح جدول البلوكات بنجاح!</h2>";
    echo "<p><a href='admin/blocks.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>العودة إلى إدارة البلوكات</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ حدث خطأ: " . $e->getMessage() . "</p>";
}
?>
