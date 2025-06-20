<?php
/**
 * صفحة تشخيص مشاكل التسجيل
 */
require_once 'config/config.php';

// التحقق من صلاحيات الإدارة
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('هذه الصفحة للإدارة فقط');
}

$debug_info = [];

// فحص جدول المستخدمين
try {
    $table_structure = $db->fetchAll("DESCRIBE users");
    $debug_info['table_structure'] = $table_structure;
} catch (Exception $e) {
    $debug_info['table_error'] = $e->getMessage();
}

// فحص المستخدمين الأخيرين
try {
    $recent_users = $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $debug_info['recent_users'] = $recent_users;
} catch (Exception $e) {
    $debug_info['users_error'] = $e->getMessage();
}

// فحص إعدادات PHP
$debug_info['php_info'] = [
    'mail_function' => function_exists('mail') ? 'متاح' : 'غير متاح',
    'php_version' => phpversion(),
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'غير محدد',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'غير محدد'
];

// اختبار إدراج بيانات تجريبية
if (isset($_POST['test_insert'])) {
    try {
        $test_data = [
            'username' => 'test_user_' . time(),
            'email' => 'test_' . time() . '@example.com',
            'full_name' => 'مستخدم تجريبي',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'role' => 'member',
            'status' => 'pending',
            'email_verified' => 0,
            'verification_token' => bin2hex(random_bytes(16)),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $db->insert('users', $test_data);
        $debug_info['test_insert'] = $result ? 'نجح الإدراج' : 'فشل الإدراج';
        
        if ($result) {
            $last_id = $db->lastInsertId();
            $debug_info['last_insert_id'] = $last_id;
            
            // حذف البيانات التجريبية
            $db->delete('users', 'id = ?', [$last_id]);
        }
    } catch (Exception $e) {
        $debug_info['test_insert_error'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تشخيص مشاكل التسجيل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .debug-section { margin-bottom: 30px; }
        .code-block { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>تشخيص مشاكل التسجيل</h1>
        
        <!-- معلومات PHP -->
        <div class="debug-section">
            <h3>معلومات الخادم</h3>
            <div class="code-block">
                <?php foreach ($debug_info['php_info'] as $key => $value): ?>
                    <strong><?php echo $key; ?>:</strong> <?php echo $value; ?><br>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- هيكل الجدول -->
        <div class="debug-section">
            <h3>هيكل جدول المستخدمين</h3>
            <?php if (isset($debug_info['table_structure'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>اسم العمود</th>
                                <th>النوع</th>
                                <th>Null</th>
                                <th>Key</th>
                                <th>Default</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($debug_info['table_structure'] as $column): ?>
                                <tr>
                                    <td><?php echo $column['Field']; ?></td>
                                    <td><?php echo $column['Type']; ?></td>
                                    <td><?php echo $column['Null']; ?></td>
                                    <td><?php echo $column['Key']; ?></td>
                                    <td><?php echo $column['Default'] ?? 'NULL'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">خطأ في جدول المستخدمين: <?php echo $debug_info['table_error'] ?? 'خطأ غير محدد'; ?></div>
            <?php endif; ?>
        </div>
        
        <!-- المستخدمون الأخيرون -->
        <div class="debug-section">
            <h3>آخر المستخدمين المسجلين</h3>
            <?php if (isset($debug_info['recent_users'])): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>اسم المستخدم</th>
                                <th>البريد</th>
                                <th>الحالة</th>
                                <th>البريد مفعل</th>
                                <th>تاريخ التسجيل</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($debug_info['recent_users'] as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                            <?php echo $user['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['email_verified'] ? 'success' : 'danger'; ?>">
                                            <?php echo $user['email_verified'] ? 'نعم' : 'لا'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $user['created_at']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">خطأ في جلب المستخدمين: <?php echo $debug_info['users_error'] ?? 'خطأ غير محدد'; ?></div>
            <?php endif; ?>
        </div>
        
        <!-- اختبار الإدراج -->
        <div class="debug-section">
            <h3>اختبار إدراج البيانات</h3>
            <form method="POST">
                <button type="submit" name="test_insert" class="btn btn-primary">اختبار إدراج مستخدم تجريبي</button>
            </form>
            
            <?php if (isset($debug_info['test_insert'])): ?>
                <div class="alert alert-<?php echo strpos($debug_info['test_insert'], 'نجح') !== false ? 'success' : 'danger'; ?> mt-3">
                    <?php echo $debug_info['test_insert']; ?>
                    <?php if (isset($debug_info['last_insert_id'])): ?>
                        <br>آخر ID مدرج: <?php echo $debug_info['last_insert_id']; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($debug_info['test_insert_error'])): ?>
                <div class="alert alert-danger mt-3">
                    خطأ في الإدراج: <?php echo $debug_info['test_insert_error']; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mt-4">
            <a href="admin/index.php" class="btn btn-secondary">العودة للوحة التحكم</a>
        </div>
    </div>
</body>
</html>