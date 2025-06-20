<?php
/**
 * إصلاح جداول الاستطلاعات
 */

require_once 'config/config.php';

echo "<h2>إصلاح جداول الاستطلاعات</h2>";

try {
    // التحقق من وجود جدول polls
    $pollsExists = false;
    try {
        $db->query("SELECT 1 FROM polls LIMIT 1");
        $pollsExists = true;
        echo "<p style='color: green;'>✓ جدول polls موجود</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ جدول polls غير موجود</p>";
    }
    
    // إنشاء جدول polls إذا لم يكن موجوداً
    if (!$pollsExists) {
        $db->query("
            CREATE TABLE polls (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NULL,
                status ENUM('active', 'inactive', 'closed') DEFAULT 'active',
                start_date DATE NULL,
                end_date DATE NULL,
                allow_multiple_votes TINYINT(1) DEFAULT 0,
                created_by INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p style='color: green;'>✓ تم إنشاء جدول polls</p>";
    }
    
    // التحقق من وجود الأعمدة المطلوبة في جدول polls
    try {
        $columns = $db->fetchAll("SHOW COLUMNS FROM polls");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = [
            'description' => "ALTER TABLE polls ADD COLUMN description TEXT NULL AFTER title",
            'start_date' => "ALTER TABLE polls ADD COLUMN start_date DATE NULL AFTER status",
            'end_date' => "ALTER TABLE polls ADD COLUMN end_date DATE NULL AFTER start_date",
            'allow_multiple_votes' => "ALTER TABLE polls ADD COLUMN allow_multiple_votes TINYINT(1) DEFAULT 0 AFTER end_date"
        ];
        
        foreach ($requiredColumns as $column => $sql) {
            if (!in_array($column, $columnNames)) {
                $db->query($sql);
                echo "<p style='color: green;'>✓ تم إضافة عمود $column إلى جدول polls</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ خطأ في التحقق من أعمدة جدول polls: " . $e->getMessage() . "</p>";
    }
    
    // إنشاء جدول poll_options
    try {
        $db->query("DROP TABLE IF EXISTS poll_options");
        $db->query("
            CREATE TABLE poll_options (
                id INT AUTO_INCREMENT PRIMARY KEY,
                poll_id INT NOT NULL,
                option_text VARCHAR(255) NOT NULL,
                display_order INT DEFAULT 0,
                votes_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p style='color: green;'>✓ تم إنشاء جدول poll_options</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ خطأ في إنشاء جدول poll_options: " . $e->getMessage() . "</p>";
    }
    
    // إنشاء جدول poll_votes
    try {
        $db->query("DROP TABLE IF EXISTS poll_votes");
        $db->query("
            CREATE TABLE poll_votes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                poll_id INT NOT NULL,
                option_id INT NOT NULL,
                user_id INT NULL,
                voter_ip VARCHAR(45) NOT NULL,
                voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
                FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p style='color: green;'>✓ تم إنشاء جدول poll_votes</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ خطأ في إنشاء جدول poll_votes: " . $e->getMessage() . "</p>";
    }
    
    // إضافة بيانات تجريبية للاستطلاعات
    try {
        // التحقق من وجود بيانات
        $pollCount = $db->fetchOne("SELECT COUNT(*) as count FROM polls")['count'];
        
        if ($pollCount == 0) {
            // إضافة استطلاعات تجريبية
            $pollId1 = $db->insert('polls', [
                'title' => 'ما رأيك في خدمات المسجد؟',
                'description' => 'نود معرفة رأيكم في الخدمات المقدمة في المسجد',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $pollId2 = $db->insert('polls', [
                'title' => 'أي الأنشطة تفضل أكثر؟',
                'description' => 'ساعدونا في اختيار الأنشطة المناسبة',
                'status' => 'active',
                'created_by' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // إضافة خيارات للاستطلاع الأول
            if ($pollId1) {
                $options1 = [
                    ['poll_id' => $pollId1, 'option_text' => 'ممتازة', 'display_order' => 1],
                    ['poll_id' => $pollId1, 'option_text' => 'جيدة', 'display_order' => 2],
                    ['poll_id' => $pollId1, 'option_text' => 'تحتاج تحسين', 'display_order' => 3],
                    ['poll_id' => $pollId1, 'option_text' => 'ضعيفة', 'display_order' => 4]
                ];
                
                foreach ($options1 as $option) {
                    $db->insert('poll_options', $option);
                }
            }
            
            // إضافة خيارات للاستطلاع الثاني
            if ($pollId2) {
                $options2 = [
                    ['poll_id' => $pollId2, 'option_text' => 'الدروس الدينية', 'display_order' => 1],
                    ['poll_id' => $pollId2, 'option_text' => 'الأنشطة الثقافية', 'display_order' => 2],
                    ['poll_id' => $pollId2, 'option_text' => 'الأنشطة الاجتماعية', 'display_order' => 3],
                    ['poll_id' => $pollId2, 'option_text' => 'الأنشطة الرياضية', 'display_order' => 4]
                ];
                
                foreach ($options2 as $option) {
                    $db->insert('poll_options', $option);
                }
            }
            
            echo "<p style='color: green;'>✓ تم إضافة بيانات تجريبية للاستطلاعات</p>";
        } else {
            echo "<p style='color: blue;'>ℹ توجد بيانات استطلاعات بالفعل</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ خطأ في إضافة بيانات تجريبية: " . $e->getMessage() . "</p>";
    }
    
    // التحقق من الجداول بعد الإصلاح
    echo "<h3>التحقق من الجداول بعد الإصلاح:</h3>";
    $tables = ['polls', 'poll_options', 'poll_votes'];
    
    foreach ($tables as $table) {
        try {
            $result = $db->fetchOne("SELECT COUNT(*) as count FROM $table");
            echo "<p style='color: green;'>✓ جدول $table: " . $result['count'] . " سجل</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ جدول $table: غير موجود</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>تم إصلاح جداول الاستطلاعات بنجاح!</h3>";
    echo "<p><a href='admin/polls.php' class='btn btn-primary'>الذهاب إلى إدارة الاستطلاعات</a></p>";
    echo "<p><a href='admin/index.php' class='btn btn-secondary'>الذهاب إلى لوحة التحكم</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>خطأ: " . $e->getMessage() . "</p>";
}
?>
