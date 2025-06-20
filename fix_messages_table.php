<?php
require_once 'config/config.php';

echo "<h1>إصلاح جدول الرسائل</h1>";

try {
    // التحقق من وجود الجدول
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'messages'");
    
    if (!$tableExists) {
        echo "<h2>إنشاء جدول messages</h2>";
        
        $createTableSQL = "
        CREATE TABLE `messages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sender_id` int(11) DEFAULT NULL,
            `recipient_id` int(11) DEFAULT NULL,
            `sender_name` varchar(100) NOT NULL,
            `sender_email` varchar(100) NOT NULL,
            `sender_phone` varchar(20) DEFAULT NULL,
            `recipient_name` varchar(100) DEFAULT NULL,
            `recipient_email` varchar(100) DEFAULT NULL,
            `subject` varchar(200) NOT NULL,
            `message` text NOT NULL,
            `message_type` enum('contact','support','complaint','suggestion') DEFAULT 'contact',
            `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
            `status` enum('unread','read','replied','closed','spam') DEFAULT 'unread',
            `is_internal` tinyint(1) DEFAULT 0,
            `attachments` longtext DEFAULT NULL,
            `replied_by` int(11) DEFAULT NULL,
            `reply_message` text DEFAULT NULL,
            `replied_at` timestamp NULL DEFAULT NULL,
            `assigned_to` int(11) DEFAULT NULL,
            `assigned_at` timestamp NULL DEFAULT NULL,
            `tags` longtext DEFAULT NULL,
            `metadata` longtext DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `sender_id` (`sender_id`),
            KEY `recipient_id` (`recipient_id`),
            KEY `status` (`status`),
            KEY `message_type` (`message_type`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->query($createTableSQL);
        echo "<p style='color: green;'>✅ تم إنشاء جدول messages بنجاح!</p>";
    } else {
        echo "<p>جدول messages موجود بالفعل</p>";
    }
    
    // التحقق من الحقول المطلوبة وإضافتها إذا لم تكن موجودة
    $requiredColumns = [
        'sender_name' => 'VARCHAR(100) NOT NULL',
        'sender_email' => 'VARCHAR(100) NOT NULL',
        'sender_phone' => 'VARCHAR(20) DEFAULT NULL',
        'message_type' => "ENUM('contact','support','complaint','suggestion') DEFAULT 'contact'",
        'priority' => "ENUM('low','normal','high','urgent') DEFAULT 'normal'",
        'status' => "ENUM('unread','read','replied','closed','spam') DEFAULT 'unread'",
        'is_internal' => 'TINYINT(1) DEFAULT 0'
    ];
    
    $existingColumns = [];
    $tableInfo = $db->fetchAll("DESCRIBE messages");
    foreach ($tableInfo as $column) {
        $existingColumns[] = $column['Field'];
    }
    
    echo "<h2>التحقق من الحقول المطلوبة</h2>";
    
    foreach ($requiredColumns as $columnName => $columnDefinition) {
        if (!in_array($columnName, $existingColumns)) {
            try {
                $db->query("ALTER TABLE messages ADD COLUMN $columnName $columnDefinition");
                echo "<p style='color: green;'>✅ تم إضافة الحقل: $columnName</p>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ خطأ في إضافة الحقل $columnName: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p>✓ الحقل $columnName موجود</p>";
        }
    }
    
    echo "<h2>✅ تم إصلاح جدول الرسائل بنجاح!</h2>";
    echo "<p><a href='test_contact_db.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>اختبار قاعدة البيانات</a>";
    echo "<a href='contact_fixed.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>اختبار صفحة التواصل</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ حدث خطأ: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2 { color: #333; }
</style>
