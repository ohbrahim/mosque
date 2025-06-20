<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار iframe مباشر</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-container { border: 2px solid #007bff; padding: 20px; margin: 20px 0; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>اختبار iframe مباشر</h1>
        
        <div class="test-container">
            <h3>اختبار 1: iframe مباشر</h3>
            <iframe id="iframe1" title="Prayer Widget Direct" style="width: 100%; height: 358px; border: 1px solid #ddd;" scrolling="no" src="https://www.islamicfinder.org/prayer-widget/2485801/shafi/15/0/18/17"></iframe>
        </div>
        
        <div class="test-container">
            <h3>اختبار 2: iframe مع sandbox</h3>
            <iframe id="iframe2" title="Prayer Widget Sandbox" style="width: 100%; height: 358px; border: 1px solid #ddd;" scrolling="no" sandbox="allow-scripts allow-same-origin allow-forms" src="https://www.islamicfinder.org/prayer-widget/2485801/shafi/15/0/18/17"></iframe>
        </div>
        
        <div class="test-container">
            <h3>اختبار 3: iframe من البلوك</h3>
            <?php
            try {
                $iframeBlock = $db->fetchOne("SELECT * FROM blocks WHERE id = 35");
                if ($iframeBlock) {
                    echo $iframeBlock['content'];
                } else {
                    echo "البلوك غير موجود";
                }
            } catch (Exception $e) {
                echo "خطأ: " . $e->getMessage();
            }
            ?>
        </div>
        
        <div class="test-container">
            <h3>اختبار 4: marquee</h3>
            <marquee direction="right">
                <p><font size="4" color="red">إنطلاق بث قنوات دروس الدعم لجميع المستويات والأطوار على موقع الديوان الوطني للتعليم والتكوين عن بعد على الرابط التالي : http://www.onefd.edu.dz/soutien-scolaire</font></p>
            </marquee>
        </div>
        
        <p>
            <a href="index.php" class="btn btn-primary">عرض الموقع</a>
            <a href="diagnose_blocks.php" class="btn btn-warning">تشخيص البلوكات</a>
        </p>
    </div>
    
    <script>
        // فحص تحميل iframe
        document.addEventListener('DOMContentLoaded', function() {
            const iframes = document.querySelectorAll('iframe');
            iframes.forEach((iframe, index) => {
                iframe.onload = function() {
                    console.log(`iframe ${index + 1} تم تحميله بنجاح`);
                };
                iframe.onerror = function() {
                    console.log(`iframe ${index + 1} فشل في التحميل`);
                };
            });
        });
    </script>
</body>
</html>
