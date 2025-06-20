<?php
require_once 'config/config.php';

echo "<h2>تشغيل إصلاحات النظام</h2>";

try {
    // تشغيل إصلاحات قاعدة البيانات
    $sqlFile = 'database/fix_missing_columns.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $db->query($statement);
                    echo "<p style='color: green;'>✓ تم تنفيذ: " . substr($statement, 0, 50) . "...</p>";
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠ تم تجاهل: " . $e->getMessage() . "</p>";
                }
            }
        }
    }
    
    // إضافة الإعدادات الافتراضية
    $defaultSettings = [
        'site_name' => 'موقع المسجد',
        'site_description' => 'موقع إدارة المسجد',
        'admin_email' => 'admin@mosque.com',
        'maintenance_mode' => '0',
        'auto_approve_comments' => '1',
        'enable_comments' => '1',
        'enable_ratings' => '1',
        'posts_per_page' => '10'
    ];
    
    foreach ($defaultSettings as $key => $value) {
        $existing = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        if (!$existing) {
            $db->insert('settings', [
                'setting_key' => $key,
                'setting_value' => $value,
                'setting_group' => 'عام',
                'display_name' => $key,
                'setting_type' => 'text'
            ]);
            echo "<p style='color: blue;'>✓ تم إضافة إعداد: {$key}</p>";
        }
    }
    
    echo "<h3 style='color: green;'>تم الانتهاء من جميع الإصلاحات بنجاح!</h3>";
    echo "<p><a href='admin/'>الذهاب إلى لوحة التحكم</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>خطأ: " . $e->getMessage() . "</p>";
}
?>
