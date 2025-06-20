<?php
require_once 'config/config.php';

echo "<h1>اختبار قاعدة بيانات الرسائل</h1>";

try {
    // 1. فحص بنية جدول messages
    echo "<h2>1. فحص بنية جدول messages</h2>";
    
    $tableInfo = $db->fetchAll("DESCRIBE messages");
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f8f9fa;'><th>اسم الحقل</th><th>النوع</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    foreach ($tableInfo as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . ($column['Default'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. اختبار إدراج رسالة تجريبية
    echo "<h2>2. اختبار إدراج رسالة تجريبية</h2>";
    
    $testMessage = [
        'sender_name' => 'اختبار المطور',
        'sender_email' => 'test@example.com',
        'sender_phone' => '0123456789',
        'subject' => 'رسالة اختبار',
        'message' => 'هذه رسالة اختبار للتأكد من عمل النظام بشكل صحيح.',
        'message_type' => 'contact',
        'priority' => 'normal',
        'status' => 'unread',
        'is_internal' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $result = $db->insert('messages', $testMessage);
    
    if ($result) {
        $insertId = $db->lastInsertId();
        echo "<p style='color: green;'>✅ تم إدراج الرسالة التجريبية بنجاح! ID: $insertId</p>";
        
        // جلب الرسالة المدرجة للتأكد
        $insertedMessage = $db->fetchOne("SELECT * FROM messages WHERE id = ?", [$insertId]);
        
        echo "<h3>بيانات الرسالة المدرجة:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        foreach ($insertedMessage as $key => $value) {
            echo "<tr>";
            echo "<td style='padding: 8px; background: #f8f9fa; font-weight: bold;'>$key</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars($value) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // حذف الرسالة التجريبية
        $db->query("DELETE FROM messages WHERE id = ?", [$insertId]);
        echo "<p style='color: blue;'>🗑️ تم حذف الرسالة التجريبية</p>";
        
    } else {
        echo "<p style='color: red;'>❌ فشل في إدراج الرسالة التجريبية</p>";
    }
    
    // 3. عرض آخر 5 رسائل
    echo "<h2>3. آخر 5 رسائل في قاعدة البيانات</h2>";
    
    $recentMessages = $db->fetchAll("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
    
    if (empty($recentMessages)) {
        echo "<p>لا توجد رسائل في قاعدة البيانات</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'>";
        echo "<th>ID</th><th>الاسم</th><th>البريد</th><th>الموضوع</th><th>الحالة</th><th>التاريخ</th>";
        echo "</tr>";
        
        foreach ($recentMessages as $msg) {
            echo "<tr>";
            echo "<td>" . $msg['id'] . "</td>";
            echo "<td>" . htmlspecialchars($msg['sender_name']) . "</td>";
            echo "<td>" . htmlspecialchars($msg['sender_email']) . "</td>";
            echo "<td>" . htmlspecialchars($msg['subject']) . "</td>";
            echo "<td>" . $msg['status'] . "</td>";
            echo "<td>" . $msg['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>✅ اختبار قاعدة البيانات مكتمل!</h2>";
    echo "<p><a href='contact_fixed.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>اختبار صفحة التواصل</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ حدث خطأ: " . $e->getMessage() . "</p>";
    echo "<p>تفاصيل الخطأ: " . $e->getTraceAsString() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { border-collapse: collapse; }
th, td { padding: 8px; text-align: right; border: 1px solid #ddd; }
th { background: #f8f9fa; font-weight: bold; }
</style>
