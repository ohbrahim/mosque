<?php
require_once 'config/config.php';

echo "<h1>تشخيص شامل لنظام الرسائل</h1>";

// 1. فحص اتصال قاعدة البيانات
echo "<h2>1. فحص اتصال قاعدة البيانات</h2>";
try {
    $testQuery = $db->fetchOne("SELECT 1 as test");
    if ($testQuery) {
        echo "<p style='color: green;'>✅ اتصال قاعدة البيانات يعمل بشكل صحيح</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ خطأ في اتصال قاعدة البيانات: " . $e->getMessage() . "</p>";
    exit;
}

// 2. فحص وجود جدول messages
echo "<h2>2. فحص جدول messages</h2>";
try {
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'messages'");
    if ($tableExists) {
        echo "<p style='color: green;'>✅ جدول messages موجود</p>";
        
        // عرض بنية الجدول
        $tableStructure = $db->fetchAll("DESCRIBE messages");
        echo "<h3>بنية الجدول:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>الحقل</th><th>النوع</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($tableStructure as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . ($column['Default'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>❌ جدول messages غير موجود</p>";
        echo "<p><a href='fix_messages_table.php'>إنشاء الجدول</a></p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ خطأ في فحص الجدول: " . $e->getMessage() . "</p>";
}

// 3. فحص الرسائل الموجودة
echo "<h2>3. فحص الرسائل الموجودة</h2>";
try {
    $totalMessages = $db->fetchOne("SELECT COUNT(*) as count FROM messages")['count'];
    echo "<p>إجمالي الرسائل في قاعدة البيانات: <strong>$totalMessages</strong></p>";
    
    if ($totalMessages > 0) {
        $recentMessages = $db->fetchAll("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
        echo "<h3>آخر 5 رسائل:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>الاسم</th><th>البريد</th><th>الموضوع</th><th>الحالة</th><th>التاريخ</th></tr>";
        foreach ($recentMessages as $msg) {
            echo "<tr>";
            echo "<td>" . $msg['id'] . "</td>";
            echo "<td>" . htmlspecialchars($msg['sender_name'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($msg['sender_email'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($msg['subject'] ?? 'N/A') . "</td>";
            echo "<td>" . ($msg['status'] ?? 'N/A') . "</td>";
            echo "<td>" . ($msg['created_at'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ لا توجد رسائل في قاعدة البيانات</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ خطأ في جلب الرسائل: " . $e->getMessage() . "</p>";
}

// 4. اختبار إدراج رسالة تجريبية
echo "<h2>4. اختبار إدراج رسالة تجريبية</h2>";

if (isset($_POST['test_insert'])) {
    try {
        $testData = [
            'sender_name' => 'اختبار النظام',
            'sender_email' => 'test@example.com',
            'sender_phone' => '0123456789',
            'subject' => 'رسالة اختبار - ' . date('H:i:s'),
            'message' => 'هذه رسالة اختبار لفحص النظام في ' . date('Y-m-d H:i:s'),
            'message_type' => 'contact',
            'priority' => 'normal',
            'status' => 'unread',
            'is_internal' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        echo "<h3>البيانات التي سيتم إدراجها:</h3>";
        echo "<pre>" . print_r($testData, true) . "</pre>";
        
        $result = $db->insert('messages', $testData);
        
        if ($result) {
            $insertId = $db->lastInsertId();
            echo "<p style='color: green;'>✅ تم إدراج الرسالة التجريبية بنجاح! ID: $insertId</p>";
            
            // التحقق من الإدراج
            $insertedMessage = $db->fetchOne("SELECT * FROM messages WHERE id = ?", [$insertId]);
            if ($insertedMessage) {
                echo "<h3>الرسالة المدرجة:</h3>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                foreach ($insertedMessage as $key => $value) {
                    echo "<tr>";
                    echo "<td style='font-weight: bold; background: #f8f9fa; padding: 8px;'>$key</td>";
                    echo "<td style='padding: 8px;'>" . htmlspecialchars($value) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // حذف الرسالة التجريبية
                echo "<p><a href='?delete_test=$insertId' style='color: red;'>حذف الرسالة التجريبية</a></p>";
            }
        } else {
            echo "<p style='color: red;'>❌ فشل في إدراج الرسالة التجريبية</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ خطأ في إدراج الرسالة: " . $e->getMessage() . "</p>";
        echo "<p>تفاصيل الخطأ: " . $e->getTraceAsString() . "</p>";
    }
}

// حذف الرسالة التجريبية
if (isset($_GET['delete_test'])) {
    $testId = (int)$_GET['delete_test'];
    try {
        $db->query("DELETE FROM messages WHERE id = ? AND sender_name = 'اختبار النظام'", [$testId]);
        echo "<p style='color: blue;'>🗑️ تم حذف الرسالة التجريبية</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ خطأ في حذف الرسالة: " . $e->getMessage() . "</p>";
    }
}

// 5. فحص دوال قاعدة البيانات
echo "<h2>5. فحص دوال قاعدة البيانات</h2>";

// فحص دالة insert
if (method_exists($db, 'insert')) {
    echo "<p style='color: green;'>✅ دالة insert موجودة</p>";
} else {
    echo "<p style='color: red;'>❌ دالة insert غير موجودة</p>";
}

// فحص دالة fetchAll
if (method_exists($db, 'fetchAll')) {
    echo "<p style='color: green;'>✅ دالة fetchAll موجودة</p>";
} else {
    echo "<p style='color: red;'>❌ دالة fetchAll غير موجودة</p>";
}

// فحص دالة lastInsertId
if (method_exists($db, 'lastInsertId')) {
    echo "<p style='color: green;'>✅ دالة lastInsertId موجودة</p>";
} else {
    echo "<p style='color: red;'>❌ دالة lastInsertId غير موجودة</p>";
}

// 6. فحص دوال الأمان
echo "<h2>6. فحص دوال الأمان</h2>";

if (function_exists('sanitize')) {
    echo "<p style='color: green;'>✅ دالة sanitize موجودة</p>";
} else {
    echo "<p style='color: red;'>❌ دالة sanitize غير موجودة</p>";
}

if (function_exists('generateCSRFToken')) {
    echo "<p style='color: green;'>✅ دالة generateCSRFToken موجودة</p>";
} else {
    echo "<p style='color: orange;'>⚠️ دالة generateCSRFToken غير موجودة (اختيارية)</p>";
}

if (function_exists('verifyCSRFToken')) {
    echo "<p style='color: green;'>✅ دالة verifyCSRFToken موجودة</p>";
} else {
    echo "<p style='color: orange;'>⚠️ دالة verifyCSRFToken غير موجودة (اختيارية)</p>";
}

echo "<h2>7. اختبار النظام</h2>";
echo "<form method='POST'>";
echo "<button type='submit' name='test_insert' value='1' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>اختبار إدراج رسالة تجريبية</button>";
echo "</form>";

echo "<h2>8. روابط مفيدة</h2>";
echo "<p>";
echo "<a href='contact_fixed.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 5px;'>اختبار صفحة التواصل</a>";
echo "<a href='admin/messages.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 5px;'>لوحة التحكم - الرسائل</a>";
echo "<a href='fix_messages_table.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 5px;'>إصلاح جدول الرسائل</a>";
echo "</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3 { color: #333; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; text-align: right; border: 1px solid #ddd; }
th { background: #f8f9fa; font-weight: bold; }
pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
</style>
