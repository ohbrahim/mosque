<?php
require_once 'config/config.php';

// تفعيل عرض الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>صفحة التواصل مع التشخيص المفصل</h1>";

// معالجة نموذج الاتصال مع تشخيص مفصل
$success = false;
$error = null;
$debug_info = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $debug_info[] = "تم استلام طلب POST";
    
    // جمع البيانات
    $name = isset($_POST['name']) ? sanitize($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? sanitize($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitize($_POST['message']) : '';
    
    $debug_info[] = "البيانات المستلمة: الاسم=$name, البريد=$email, الهاتف=$phone, الموضوع=$subject";
    
    // التحقق من البيانات
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'يرجى ملء جميع الحقول المطلوبة.';
        $debug_info[] = "خطأ: حقول مطلوبة فارغة";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'يرجى إدخال بريد إلكتروني صحيح.';
        $debug_info[] = "خطأ: بريد إلكتروني غير صحيح";
    } else {
        $debug_info[] = "التحقق من البيانات نجح";
        
        try {
            // إعداد البيانات للإدراج
            $messageData = [
                'sender_name' => $name,
                'sender_email' => $email,
                'sender_phone' => !empty($phone) ? $phone : null,
                'subject' => !empty($subject) ? $subject : 'رسالة من الموقع',
                'message' => $message,
                'message_type' => 'contact',
                'priority' => 'normal',
                'status' => 'unread',
                'is_internal' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $debug_info[] = "البيانات المعدة للإدراج: " . json_encode($messageData, JSON_UNESCAPED_UNICODE);
            
            // محاولة الإدراج
            $debug_info[] = "محاولة إدراج البيانات في قاعدة البيانات...";
            
            $result = $db->insert('messages', $messageData);
            
            $debug_info[] = "نتيجة الإدراج: " . ($result ? 'نجح' : 'فشل');
            
            if ($result) {
                $insertId = $db->lastInsertId();
                $debug_info[] = "ID الرسالة المدرجة: $insertId";
                
                // التحقق من الإدراج
                $insertedMessage = $db->fetchOne("SELECT * FROM messages WHERE id = ?", [$insertId]);
                if ($insertedMessage) {
                    $debug_info[] = "تم التحقق من إدراج الرسالة بنجاح";
                    $success = true;
                    
                    // مسح البيانات من النموذج
                    $_POST = [];
                } else {
                    $debug_info[] = "خطأ: لم يتم العثور على الرسالة بعد الإدراج";
                    $error = 'حدث خطأ في التحقق من حفظ الرسالة.';
                }
            } else {
                $debug_info[] = "خطأ: فشل في إدراج البيانات";
                $error = 'حدث خطأ أثناء حفظ الرسالة.';
            }
            
        } catch (Exception $e) {
            $debug_info[] = "استثناء: " . $e->getMessage();
            $debug_info[] = "تفاصيل الخطأ: " . $e->getTraceAsString();
            error_log("Contact form error: " . $e->getMessage());
            $error = 'حدث خطأ أثناء إرسال الرسالة: ' . $e->getMessage();
        }
    }
}

// جلب معلومات الاتصال
$siteName = getSetting($db, 'site_name', 'مسجد النور');
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اتصل بنا - تشخيص - <?php echo htmlspecialchars($siteName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-info { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; }
        .debug-info h3 { color: #333; margin-bottom: 10px; }
        .debug-info ul { margin: 0; padding-left: 20px; }
        .debug-info li { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>صفحة التواصل مع التشخيص</h1>
        
        <!-- Debug Information -->
        <?php if (!empty($debug_info)): ?>
            <div class="debug-info">
                <h3>معلومات التشخيص:</h3>
                <ul>
                    <?php foreach ($debug_info as $info): ?>
                        <li><?php echo htmlspecialchars($info); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>تم بنجاح!</strong> تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>خطأ!</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Contact Form -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">أرسل لنا رسالة</h2>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                           required maxlength="100">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                           required maxlength="100">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">رقم الهاتف</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                           maxlength="20">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">الموضوع</label>
                                    <select class="form-select" id="subject" name="subject">
                                        <option value="">-- اختر الموضوع --</option>
                                        <option value="استفسار عام">استفسار عام</option>
                                        <option value="أوقات الصلاة">أوقات الصلاة</option>
                                        <option value="الدروس والمحاضرات">الدروس والمحاضرات</option>
                                        <option value="الفعاليات">الفعاليات</option>
                                        <option value="التبرعات">التبرعات</option>
                                        <option value="شكوى أو اقتراح">شكوى أو اقتراح</option>
                                        <option value="أخرى">أخرى</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="message" class="form-label">الرسالة <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="6" 
                                          placeholder="اكتب رسالتك هنا..." required 
                                          maxlength="5000"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            </div>
                            
                            <button type="submit" name="send_message" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                إرسال الرسالة
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h3>روابط التشخيص</h3>
                        <div class="d-grid gap-2">
                            <a href="debug_contact_system.php" class="btn btn-info">
                                <i class="fas fa-bug"></i> تشخيص شامل للنظام
                            </a>
                            <a href="test_contact_db.php" class="btn btn-warning">
                                <i class="fas fa-database"></i> اختبار قاعدة البيانات
                            </a>
                            <a href="admin/messages.php" class="btn btn-success">
                                <i class="fas fa-envelope"></i> لوحة التحكم - الرسائل
                            </a>
                            <a href="fix_messages_table.php" class="btn btn-secondary">
                                <i class="fas fa-wrench"></i> إصلاح جدول الرسائل
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
