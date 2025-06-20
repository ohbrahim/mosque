<?php
/**
 * صفحة اختبار مفصلة لتشخيص مشاكل التسجيل
 */
require_once 'config/config.php';

$debug_info = [];
$test_results = [];

// فحص الاتصال بقاعدة البيانات
try {
    $connection_test = $db->fetchOne("SELECT 1 as test");
    $debug_info['database_connection'] = $connection_test ? 'متصل' : 'غير متصل';
} catch (Exception $e) {
    $debug_info['database_connection'] = 'خطأ: ' . $e->getMessage();
}

// فحص هيكل الجدول
try {
    $table_structure = $db->fetchAll("DESCRIBE users");
    $debug_info['table_exists'] = 'موجود';
    $debug_info['table_columns'] = count($table_structure);
    $debug_info['columns_list'] = array_column($table_structure, 'Field');
} catch (Exception $e) {
    $debug_info['table_exists'] = 'غير موجود أو خطأ: ' . $e->getMessage();
}

// فحص صلاحيات قاعدة البيانات
try {
    $grants = $db->fetchAll("SHOW GRANTS");
    $debug_info['database_grants'] = $grants;
} catch (Exception $e) {
    $debug_info['database_grants'] = 'لا يمكن عرض الصلاحيات: ' . $e->getMessage();
}

// اختبار إدراج مفصل
if (isset($_POST['test_detailed_insert'])) {
    $test_username = 'test_user_' . time();
    $test_email = 'test_' . time() . '@example.com';
    
    // البيانات الأساسية فقط
    $userData = [
        'username' => $test_username,
        'email' => $test_email,
        'full_name' => 'مستخدم تجريبي',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'member',
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $test_results['prepared_data'] = $userData;
    
    try {
        // اختبار الإدراج خطوة بخطوة
        $columns = implode(',', array_keys($userData));
        $placeholders = ':' . implode(', :', array_keys($userData));
        $sql = "INSERT INTO users ({$columns}) VALUES ({$placeholders})";
        
        $test_results['generated_sql'] = $sql;
        
        // محاولة تحضير الاستعلام
        $stmt = $db->query("SELECT 1"); // اختبار الاتصال أولاً
        if ($stmt) {
            $test_results['connection_ok'] = 'الاتصال يعمل';
            
            // محاولة الإدراج الفعلي
            $insertResult = $db->insert('users', $userData);
            
            if ($insertResult) {
                $last_id = $db->lastInsertId();
                $test_results['insert_success'] = 'نجح الإدراج - ID: ' . $last_id;
                
                // التحقق من البيانات المدرجة
                $inserted_user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$last_id]);
                $test_results['inserted_data'] = $inserted_user;
                
                // حذف البيانات التجريبية
                $db->delete('users', 'id = ?', [$last_id]);
                $test_results['cleanup'] = 'تم حذف البيانات التجريبية';
            } else {
                $test_results['insert_failed'] = 'فشل الإدراج - لا توجد تفاصيل إضافية';
                
                // محاولة إدراج يدوي
                try {
                    $manual_sql = "INSERT INTO users (username, email, full_name, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $manual_stmt = $db->query($manual_sql, [
                        $test_username,
                        $test_email,
                        'مستخدم تجريبي',
                        password_hash('123456', PASSWORD_DEFAULT),
                        'member',
                        'pending',
                        date('Y-m-d H:i:s')
                    ]);
                    
                    if ($manual_stmt) {
                        $test_results['manual_insert'] = 'نجح الإدراج اليدوي';
                        $last_id = $db->lastInsertId();
                        $db->delete('users', 'id = ?', [$last_id]);
                    } else {
                        $test_results['manual_insert'] = 'فشل الإدراج اليدوي أيضاً';
                    }
                } catch (Exception $e) {
                    $test_results['manual_insert_error'] = $e->getMessage();
                }
            }
        } else {
            $test_results['connection_failed'] = 'فشل في الاتصال';
        }
        
    } catch (Exception $e) {
        $test_results['exception'] = $e->getMessage();
        $test_results['exception_code'] = $e->getCode();
        $test_results['exception_file'] = $e->getFile();
        $test_results['exception_line'] = $e->getLine();
    }
}

// اختبار إدراج بسيط جداً
if (isset($_POST['test_simple_insert'])) {
    try {
        $simple_sql = "INSERT INTO users (username, email, full_name, password, role, status) VALUES (?, ?, ?, ?, ?, ?)";
        $simple_data = [
            'simple_test_' . time(),
            'simple_' . time() . '@test.com',
            'اختبار بسيط',
            password_hash('123456', PASSWORD_DEFAULT),
            'member',
            'pending'
        ];
        
        $result = $db->query($simple_sql, $simple_data);
        
        if ($result) {
            $test_results['simple_insert'] = 'نجح الإدراج البسيط';
            $last_id = $db->lastInsertId();
            $db->delete('users', 'id = ?', [$last_id]);
        } else {
            $test_results['simple_insert'] = 'فشل الإدراج البسيط';
        }
    } catch (Exception $e) {
        $test_results['simple_insert_error'] = $e->getMessage();
    }
}

// عرض المستخدمين الحاليين
try {
    $current_users = $db->fetchAll("SELECT id, username, email, status, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $debug_info['current_users'] = $current_users;
} catch (Exception $e) {
    $debug_info['current_users_error'] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار التسجيل المفصل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .debug-section { margin-bottom: 30px; }
        .code-block { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; }
        .result-success { color: #28a745; }
        .result-error { color: #dc3545; }
        .result-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>اختبار التسجيل المفصل</h1>
        
        <!-- معلومات النظام -->
        <div class="debug-section">
            <h3>معلومات النظام</h3>
            <div class="code-block">
                <strong>PHP Version:</strong> <?php echo phpversion(); ?><br>
                <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'غير محدد'; ?><br>
                <strong>Database Connection:</strong> <span class="<?php echo strpos($debug_info['database_connection'], 'متصل') !== false ? 'result-success' : 'result-error'; ?>"><?php echo $debug_info['database_connection']; ?></span><br>
                <strong>Users Table:</strong> <span class="<?php echo $debug_info['table_exists'] === 'موجود' ? 'result-success' : 'result-error'; ?>"><?php echo $debug_info['table_exists']; ?></span><br>
                <?php if (isset($debug_info['table_columns'])): ?>
                <strong>Table Columns:</strong> <?php echo $debug_info['table_columns']; ?> عمود<br>
                <strong>Columns List:</strong> <?php echo implode(', ', $debug_info['columns_list']); ?><br>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- اختبارات الإدراج -->
        <div class="debug-section">
            <h3>اختبارات الإدراج</h3>
            <div class="row">
                <div class="col-md-6">
                    <form method="POST" class="mb-3">
                        <button type="submit" name="test_detailed_insert" class="btn btn-primary w-100">اختبار إدراج مفصل</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <form method="POST" class="mb-3">
                        <button type="submit" name="test_simple_insert" class="btn btn-success w-100">اختبار إدراج بسيط</button>
                    </form>
                </div>
            </div>
            
            <?php if (!empty($test_results)): ?>
                <div class="code-block">
                    <h5>نتائج الاختبار:</h5>
                    <?php foreach ($test_results as $key => $value): ?>
                        <div class="mb-2">
                            <strong><?php echo $key; ?>:</strong>
                            <?php if (is_array($value)): ?>
                                <pre style="font-size: 11px; margin: 5px 0;"><?php print_r($value); ?></pre>
                            <?php else: ?>
                                <span class="<?php echo strpos($key, 'error') !== false || strpos($key, 'failed') !== false ? 'result-error' : (strpos($key, 'success') !== false ? 'result-success' : 'result-warning'); ?>">
                                    <?php echo $value; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <hr style="margin: 10px 0;">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- صلاحيات قاعدة البيانات -->
        <?php if (isset($debug_info['database_grants'])): ?>
        <div class="debug-section">
            <h3>صلاحيات قاعدة البيانات</h3>
            <div class="code-block">
                <?php if (is_array($debug_info['database_grants'])): ?>
                    <?php foreach ($debug_info['database_grants'] as $grant): ?>
                        <?php echo implode(' | ', $grant); ?><br>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php echo $debug_info['database_grants']; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- المستخدمون الحاليون -->
        <?php if (isset($debug_info['current_users'])): ?>
        <div class="debug-section">
            <h3>آخر المستخدمين (5)</h3>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>اسم المستخدم</th>
                            <th>البريد</th>
                            <th>الدور</th>
                            <th>الحالة</th>
                            <th>تاريخ التسجيل</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debug_info['current_users'] as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo $user['role']; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo $user['status']; ?>
                                </span>
                            </td>
                            <td><?php echo $user['created_at']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">خطأ في جلب المستخدمين: <?php echo $debug_info['current_users_error'] ?? 'خطأ غير محدد'; ?></div>
        <?php endif; ?>
        </div>
        
        <div class="mt-4">
            <a href="login.php" class="btn btn-secondary">العودة لصفحة تسجيل الدخول</a>
        </div>
    </div>
</body>
</html>