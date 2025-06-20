<?php
/**
 * أدوات التطوير للبيئة المحلية
 */
require_once 'config/config.php';

// التحقق من البيئة المحلية
function isLocalEnvironment() {
    $localHosts = ['localhost', '127.0.0.1', '::1'];
    $currentHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    return in_array($currentHost, $localHosts) || 
           strpos($currentHost, '.local') !== false ||
           strpos($currentHost, 'xampp') !== false ||
           strpos($currentHost, 'wamp') !== false;
}

if (!isLocalEnvironment()) {
    die('هذه الصفحة متاحة فقط في بيئة التطوير المحلية');
}

// معالجة الطلبات
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'activate_user':
                $userId = (int)$_POST['user_id'];
                $db->update('users', [
                    'status' => 'active',
                    'email_verified' => 1
                ], 'id = ?', [$userId]);
                $message = "تم تفعيل المستخدم بنجاح!";
                break;
                
            case 'approve_request':
                $requestId = (int)$_POST['request_id'];
                $db->update('registration_requests', [
                    'status' => 'approved',
                    'processed_at' => date('Y-m-d H:i:s'),
                    'processed_by' => 1
                ], 'id = ?', [$requestId]);
                
                // إنشاء المستخدم
                $request = $db->fetchOne("SELECT * FROM registration_requests WHERE id = ?", [$requestId]);
                if ($request) {
                    $db->insert('users', [
                        'username' => $request['username'],
                        'email' => $request['email'],
                        'password' => $request['password'],
                        'full_name' => $request['full_name'],
                        'phone' => $request['phone'],
                        'role' => 'editor',
                        'status' => 'active',
                        'email_verified' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
                $message = "تم قبول الطلب وإنشاء المستخدم بنجاح!";
                break;
        }
    }
}

// جلب البيانات
$pendingUsers = $db->fetchAll("SELECT * FROM users WHERE status = 'pending' OR email_verified = 0");
$pendingRequests = $db->fetchAll("SELECT * FROM registration_requests WHERE status = 'pending'");
$emailCount = 0;
if (file_exists('emails/index.json')) {
    $emails = json_decode(file_get_contents('emails/index.json'), true) ?: [];
    $emailCount = count($emails);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أدوات التطوير - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: #f8f9fa;
        }
        .dev-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .tool-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        .warning-banner {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dev-header">
        <div class="container">
            <h1>🛠️ أدوات التطوير المحلية</h1>
            <p class="mb-0">أدوات مساعدة لبيئة التطوير - XAMPP</p>
        </div>
    </div>

    <div class="container">
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="warning-banner">
            <strong>⚠️ تنبيه:</strong> هذه الأدوات متاحة فقط في بيئة التطوير المحلية ولن تظهر في الخادم المباشر.
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo $emailCount; ?></h3>
                    <p>رسائل بريد محفوظة</p>
                    <a href="emails/view_emails.php" class="btn btn-light btn-sm">عرض الرسائل</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo count($pendingUsers); ?></h3>
                    <p>مستخدمين معلقين</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo count($pendingRequests); ?></h3>
                    <p>طلبات تسجيل معلقة</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="tool-card">
                    <h4>🔓 تفعيل المستخدمين يدوياً</h4>
                    <p>تفعيل المستخدمين بدون الحاجة لرسائل البريد الإلكتروني</p>
                    
                    <?php if (!empty($pendingUsers)): ?>
                        <?php foreach ($pendingUsers as $user): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                </div>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="activate_user">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">تفعيل</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">لا توجد مستخدمين معلقين</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="tool-card">
                    <h4>✅ قبول طلبات التسجيل</h4>
                    <p>قبول طلبات التسجيل وإنشاء المستخدمين مباشرة</p>
                    
                    <?php if (!empty($pendingRequests)): ?>
                        <?php foreach ($pendingRequests as $request): ?>
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong><?php echo htmlspecialchars($request['full_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($request['email']); ?></small>
                                </div>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="approve_request">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" class="btn btn-primary btn-sm">قبول</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">لا توجد طلبات معلقة</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tool-card">
            <h4>🔗 روابط مفيدة</h4>
            <div class="row">
                <div class="col-md-3">
                    <a href="auth/login.php" class="btn btn-outline-primary w-100 mb-2">صفحة الدخول</a>
                </div>
                <div class="col-md-3">
                    <a href="emails/view_emails.php" class="btn btn-outline-success w-100 mb-2">عرض الرسائل</a>
                </div>
                <div class="col-md-3">
                    <a href="admin/registration_requests.php" class="btn btn-outline-info w-100 mb-2">إدارة الطلبات</a>
                </div>
                <div class="col-md-3">
                    <a href="admin/index.php" class="btn btn-outline-secondary w-100 mb-2">لوحة التحكم</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
