<?php
/**
 * فحص حالة النظام
 */
require_once 'config/config.php';

function checkTable($db, $tableName) {
    try {
        $result = $db->fetchOne("SHOW TABLES LIKE '{$tableName}'");
        return $result !== false;
    } catch (Exception $e) {
        return false;
    }
}

function checkSetting($db, $key) {
    try {
        $result = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
        return $result !== false;
    } catch (Exception $e) {
        return false;
    }
}

function checkMenuItems($db) {
    try {
        $count = $db->fetchOne("SELECT COUNT(*) as count FROM menu_items")['count'];
        return $count > 0;
    } catch (Exception $e) {
        return false;
    }
}

$checks = [
    'database' => [
        'title' => 'قاعدة البيانات',
        'items' => [
            'connection' => ['name' => 'الاتصال بقاعدة البيانات', 'status' => true],
            'settings_table' => ['name' => 'جدول الإعدادات', 'status' => checkTable($db, 'settings')],
            'menu_items_table' => ['name' => 'جدول عناصر القائمة', 'status' => checkTable($db, 'menu_items')],
            'header_footer_content_table' => ['name' => 'جدول محتوى الهيدر والفوتر', 'status' => checkTable($db, 'header_footer_content')],
            'pages_table' => ['name' => 'جدول الصفحات', 'status' => checkTable($db, 'pages')],
            'blocks_table' => ['name' => 'جدول البلوكات', 'status' => checkTable($db, 'blocks')]
        ]
    ],
    'settings' => [
        'title' => 'الإعدادات',
        'items' => [
            'site_name' => ['name' => 'اسم الموقع', 'status' => checkSetting($db, 'site_name')],
            'header_style' => ['name' => 'نمط الهيدر', 'status' => checkSetting($db, 'header_style')],
            'footer_style' => ['name' => 'نمط الفوتر', 'status' => checkSetting($db, 'footer_style')],
            'header_bg_color' => ['name' => 'لون الهيدر', 'status' => checkSetting($db, 'header_bg_color')],
            'footer_bg_color' => ['name' => 'لون الفوتر', 'status' => checkSetting($db, 'footer_bg_color')]
        ]
    ],
    'content' => [
        'title' => 'المحتوى',
        'items' => [
            'menu_items' => ['name' => 'عناصر القائمة', 'status' => checkMenuItems($db)],
            'header_file' => ['name' => 'ملف الهيدر', 'status' => file_exists('includes/header.php')],
            'footer_file' => ['name' => 'ملف الفوتر', 'status' => file_exists('includes/footer.php')],
            'css_file' => ['name' => 'ملف CSS المخصص', 'status' => file_exists('assets/css/custom.css')]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فحص حالة النظام</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin-top: 30px;
        }
        
        .check-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .check-item:last-child {
            border-bottom: none;
        }
        
        .status-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
        }
        
        .status-success {
            background: #28a745;
        }
        
        .status-error {
            background: #dc3545;
        }
        
        .header-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .action-buttons {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-card">
            <h1 class="mb-3">
                <i class="fas fa-stethoscope text-primary"></i>
                فحص حالة النظام
            </h1>
            <p class="lead">التحقق من حالة جميع مكونات نظام إدارة المسجد</p>
        </div>
        
        <?php foreach ($checks as $categoryKey => $category): ?>
            <div class="check-card">
                <h4 class="mb-4">
                    <i class="fas fa-<?php echo $categoryKey === 'database' ? 'database' : ($categoryKey === 'settings' ? 'cog' : 'file-alt'); ?> text-primary"></i>
                    <?php echo $category['title']; ?>
                </h4>
                
                <?php 
                $totalItems = count($category['items']);
                $successItems = 0;
                foreach ($category['items'] as $item) {
                    if ($item['status']) $successItems++;
                }
                ?>
                
                <div class="mb-3">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: <?php echo ($successItems / $totalItems) * 100; ?>%"></div>
                    </div>
                    <small class="text-muted"><?php echo $successItems; ?> من <?php echo $totalItems; ?> عنصر يعمل بشكل صحيح</small>
                </div>
                
                <?php foreach ($category['items'] as $itemKey => $item): ?>
                    <div class="check-item">
                        <span><?php echo $item['name']; ?></span>
                        <div class="status-icon <?php echo $item['status'] ? 'status-success' : 'status-error'; ?>">
                            <i class="fas fa-<?php echo $item['status'] ? 'check' : 'times'; ?>"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="action-buttons">
            <h5 class="mb-3">إجراءات الإصلاح</h5>
            <div class="btn-group flex-wrap" role="group">
                <a href="install/fix_header_footer_db.php" class="btn btn-primary">
                    <i class="fas fa-wrench"></i> إصلاح قاعدة البيانات
                </a>
                <a href="test_header_footer.php" class="btn btn-success">
                    <i class="fas fa-vial"></i> اختبار النظام
                </a>
                <a href="admin/header_footer.php" class="btn btn-info">
                    <i class="fas fa-cog"></i> إدارة الهيدر والفوتر
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> الصفحة الرئيسية
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
