<?php
/**
 * معالجة إرسال التعليقات
 */

require_once 'config/config_final.php';

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// التحقق من رمز الأمان
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'رمز الأمان غير صحيح';
    header('Location: ' . ($_POST['return_url'] ?? 'index.php'));
    exit;
}

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    $_SESSION['error'] = 'يجب تسجيل الدخول لإضافة تعليق';
    header('Location: login.php');
    exit;
}

// جلب البيانات
$pageId = (int)($_POST['page_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
$returnUrl = $_POST['return_url'] ?? 'index.php';

// التحقق من صحة البيانات
if (empty($content)) {
    $_SESSION['error'] = 'محتوى التعليق مطلوب';
    header('Location: ' . $returnUrl);
    exit;
}

if ($pageId <= 0) {
    $_SESSION['error'] = 'معرف الصفحة غير صحيح';
    header('Location: ' . $returnUrl);
    exit;
}

// التحقق من وجود الصفحة
try {
    $page = $db->fetchOne("SELECT id, allow_comments FROM pages WHERE id = ? AND status = 'published'", [$pageId]);
    
    if (!$page) {
        $_SESSION['error'] = 'الصفحة غير موجودة';
        header('Location: ' . $returnUrl);
        exit;
    }
    
    if (!$page['allow_comments']) {
        $_SESSION['error'] = 'التعليقات غير مسموحة على هذه الصفحة';
        header('Location: ' . $returnUrl);
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'حدث خطأ أثناء التحقق من الصفحة';
    header('Location: ' . $returnUrl);
    exit;
}

// إضافة التعليق
try {
    // تحديد حالة التعليق (موافقة تلقائية أم لا)
    $autoApprove = getSetting($db, 'auto_approve_comments', '0') === '1';
    $status = $autoApprove ? 'approved' : 'pending';
    
    // إدراج التعليق
    $commentId = $db->insert('comments', [
        'page_id' => $pageId,
        'user_id' => $_SESSION['user_id'],
        'content' => sanitize($content),
        'author_ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        'status' => $status,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    if ($commentId) {
        if ($status === 'approved') {
            $_SESSION['success'] = 'تم إضافة تعليقك بنجاح';
        } else {
            $_SESSION['success'] = 'تم إرسال تعليقك وسيتم مراجعته قريباً';
        }
    } else {
        $_SESSION['error'] = 'فشل في إضافة التعليق';
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = 'حدث خطأ أثناء إضافة التعليق: ' . $e->getMessage();
}

// إعادة التوجيه
header('Location: ' . $returnUrl);
exit;
?>
