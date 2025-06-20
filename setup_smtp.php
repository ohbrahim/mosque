<?php
/**
 * إعداد SMTP للبريد الإلكتروني
 * هذا الملف لإعداد خدمة البريد الإلكتروني باستخدام Gmail أو خدمات أخرى
 */

// إعدادات SMTP لـ Gmail
function setupGmailSMTP() {
    // إعدادات php.ini للـ SMTP
    ini_set('SMTP', 'smtp.gmail.com');
    ini_set('smtp_port', '587');
    ini_set('sendmail_from', 'your-email@gmail.com');
    
    echo "تم إعداد Gmail SMTP بنجاح!<br>";
    echo "تأكد من تفعيل 'كلمات المرور للتطبيقات' في حساب Gmail الخاص بك.<br>";
    echo "أو استخدم مكتبة PHPMailer للحصول على تحكم أفضل.<br>";
}

// اختبار إرسال بريد إلكتروني
function testEmail($to, $subject, $message) {
    $headers = 'From: your-email@gmail.com' . "\r\n" .
               'Reply-To: your-email@gmail.com' . "\r\n" .
               'X-Mailer: PHP/' . phpversion();
    
    if (mail($to, $subject, $message, $headers)) {
        echo "تم إرسال البريد الإلكتروني بنجاح!";
    } else {
        echo "فشل في إرسال البريد الإلكتروني.";
    }
}

// تشغيل الإعداد
setupGmailSMTP();

// اختبار (غير هذه القيم)
// testEmail('test@example.com', 'اختبار', 'هذه رسالة اختبار');
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعداد البريد الإلكتروني</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3e0; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>إعداد البريد الإلكتروني</h1>
    
    <div class="info">
        <h3>الحل الأول: تعطيل البريد في بيئة التطوير (الأسهل)</h3>
        <p>استخدم الملف <code>contact_fixed_email.php</code> الذي يتحقق من البيئة المحلية ولا يرسل بريد إلكتروني في XAMPP.</p>
    </div>
    
    <div class="warning">
        <h3>الحل الثاني: إعداد Gmail SMTP</h3>
        <p>لاستخدام Gmail لإرسال البريد الإلكتروني:</p>
        <ol>
            <li>فعّل "التحقق بخطوتين" في حساب Gmail</li>
            <li>أنشئ "كلمة مرور للتطبيق" من إعدادات الأمان</li>
            <li>عدّل ملف <code>php.ini</code> في XAMPP:</li>
        </ol>
        
        <div class="code">
[mail function]<br>
SMTP = smtp.gmail.com<br>
smtp_port = 587<br>
sendmail_from = your-email@gmail.com<br>
sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"<br>
        </div>
        
        <p>ثم عدّل ملف <code>sendmail.ini</code> في مجلد <code>C:\xampp\sendmail\</code>:</p>
        
        <div class="code">
smtp_server=smtp.gmail.com<br>
smtp_port=587<br>
smtp_ssl=tls<br>
auth_username=your-email@gmail.com<br>
auth_password=your-app-password<br>
        </div>
    </div>
    
    <div class="info">
        <h3>الحل الثالث: استخدام PHPMailer (الأفضل)</h3>
        <p>لحل أكثر احترافية، يمكن استخدام مكتبة PHPMailer.</p>
    </div>
</body>
</html>
