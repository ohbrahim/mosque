<?php
require_once 'config/config.php';

echo "<h1>إصلاح مشكلة HTML entities في البلوكات</h1>";

// دالة لفك تشفير HTML entities
function decodeHtmlEntities($content) {
    // فك تشفير HTML entities
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // إصلاحات إضافية
    $content = str_replace('&nbsp;', ' ', $content);
    $content = str_replace('&amp;', '&', $content);
    
    return $content;
}

// دالة لتنظيف وإصلاح المحتوى
function cleanBlockContent($content) {
    // فك تشفير HTML entities
    $content = decodeHtmlEntities($content);
    
    // إزالة <br> الزائدة
    $content = str_replace('<br>', '', $content);
    
    // إصلاح العلامات المكسورة
    $content = preg_replace('/&lt;(\/?[a-zA-Z][^&]*?)&gt;/', '<$1>', $content);
    
    return $content;
}

try {
    // 1. جلب جميع البلوكات التي تحتوي على HTML entities
    echo "<h2>1. البلوكات التي تحتاج إصلاح</h2>";
    
    $problematicBlocks = $db->fetchAll("SELECT * FROM blocks WHERE content LIKE '%&lt;%' OR content LIKE '%&gt;%' OR content LIKE '%&nbsp;%'");
    
    echo "<p>تم العثور على " . count($problematicBlocks) . " بلوك يحتاج إصلاح</p>";
    
    // 2. إصلاح كل بلوك
    echo "<h2>2. إصلاح البلوكات</h2>";
    
    foreach ($problematicBlocks as $block) {
        echo "<div style='border: 1px solid #ddd; margin: 10px 0; padding: 15px;'>";
        echo "<h3>البلوك: " . htmlspecialchars($block['title']) . " (ID: " . $block['id'] . ")</h3>";
        
        echo "<h4>المحتوى الأصلي:</h4>";
        echo "<textarea style='width: 100%; height: 100px;' readonly>" . htmlspecialchars(substr($block['content'], 0, 300)) . "...</textarea>";
        
        // تنظيف المحتوى
        $cleanedContent = cleanBlockContent($block['content']);
        
        echo "<h4>المحتوى بعد التنظيف:</h4>";
        echo "<textarea style='width: 100%; height: 100px;' readonly>" . htmlspecialchars(substr($cleanedContent, 0, 300)) . "...</textarea>";
        
        echo "<h4>المعاينة:</h4>";
        echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow: auto;'>";
        echo $cleanedContent;
        echo "</div>";
        
        // تحديث البلوك في قاعدة البيانات
        try {
            $db->query("UPDATE blocks SET content = ?, updated_at = ? WHERE id = ?", [
                $cleanedContent,
                date('Y-m-d H:i:s'),
                $block['id']
            ]);
            echo "<p style='color: green;'>✅ تم إصلاح البلوك بنجاح!</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ خطأ في إصلاح البلوك: " . $e->getMessage() . "</p>";
        }
        
        echo "</div>";
    }
    
    // 3. إضافة بلوكات اختبار مصححة
    echo "<h2>3. إضافة بلوكات اختبار مصححة</h2>";
    
    $testBlocks = [
        [
            'title' => 'دعم الموقع - مصحح',
            'content' => '<p style="text-align: center;"><span style="font-size: small;"><strong><span style="color: #008000;">إدعمنا ليبقى الموقع مستمر</span></strong></span></p>
<p style="text-align:center;"><span style="font-size: small;"><strong><span style="color: #008000;"><font color="blue">داخليا<br>عن طريق البطاقة الذهبية</span></font></span></strong></p>
<p style="text-align: center;"><span style="font-size: small;"><strong><span style="color: #008000;"><img src="http://club.ohbrahim.com/upload/1663182052-1f22e.jpg" alt="" width="111" height="86" /></span></span></strong></p>
<p style="text-align: center;"><span style="font-size: medium; color: #ff0000;"><strong>123456</strong></span></p>
<p style="text-align:center;"> </p>
<p style="text-align:center;"><span style="font-size: small;"><strong><span style="color: #008000;"><font color="blue">أو<br>عن طريق الحساب البنكي ل Wise</span></font></span></strong></p>
<p style="text-align: center;"><span style="font-size: medium; color: #ff0000;"><strong>12345678</strong></span></p>
<p style="text-align:center;"> </p>
<p style="text-align:center;"><span style="font-size: small;"><strong><span style="color: #008000;"><font color="blue">أو عن طريق حساب Paypal </span></font></span></strong></p>
<p style="text-align: center;"><span style="font-size: medium; color: #ff0000;"><strong>ohbrahim@hotmail.com</strong></span></p>',
            'block_type' => 'html',
            'position' => 'right'
        ],
        [
            'title' => 'عداد النتائج - مصحح',
            'content' => '<style>
.countdown-container {
    text-align: center;
    font-size: 16px;
}
.countdown-title {
    color: blue;
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}
.countdown-timer {
    color: red;
    font-size: 20px;
    font-weight: bold;
    margin: 15px 0;
}
.countdown-message {
    color: green;
    font-size: 16px;
    margin-top: 15px;
}
</style>
<div class="countdown-container">
    <p class="countdown-title">ترقبوا ظهور نتائج شهادة التعليم المتوسط 2019</p>
    <div class="countdown-timer" id="demo"></div>
    <p class="countdown-message">نتمنى النجاح والتوفيق لجميع تلامذتنا</p>
</div>
<script>
var deadline = new Date("July 1, 2025 15:50:00").getTime();
var x = setInterval(function() {
    var now = new Date().getTime();
    var t = deadline - now;
    var days = Math.floor(t / (1000 * 60 * 60 * 24));
    var hours = Math.floor((t%(1000 * 60 * 60 * 24))/(1000 * 60 * 60));
    var minutes = Math.floor((t % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((t % (1000 * 60)) / 1000);
    document.getElementById("demo").innerHTML = days + " يوم " + hours + " ساعة " + minutes + " دقيقة " + seconds + " ثانية ";
    if (t < 0) {
        clearInterval(x);
        document.getElementById("demo").innerHTML = "انتهت المدة";
    }
}, 1000);
</script>',
            'block_type' => 'html',
            'position' => 'left'
        ],
        [
            'title' => 'إعلان دروس الدعم - مصحح',
            'content' => '<marquee direction="right" scrollamount="3" behavior="scroll">
    <span style="font-size: 16px; color: red; font-weight: bold;">
        🔔 إنطلاق بث قنوات دروس الدعم لجميع المستويات والأطوار على موقع الديوان الوطني للتعليم والتكوين عن بعد على الرابط التالي: http://www.onefd.edu.dz/soutien-scolaire
    </span>
</marquee>',
            'block_type' => 'marquee',
            'position' => 'top'
        ]
    ];
    
    foreach ($testBlocks as $block) {
        try {
            $block['status'] = 'active';
            $block['show_title'] = 1;
            $block['allow_html'] = 1;
            $block['is_safe'] = 1;
            $block['display_order'] = 1;
            $block['created_at'] = date('Y-m-d H:i:s');
            $block['updated_at'] = date('Y-m-d H:i:s');
            
            $db->insert('blocks', $block);
            echo "✅ تم إضافة بلوك مصحح: " . $block['title'] . "<br>";
            
        } catch (Exception $e) {
            echo "❌ خطأ في إضافة بلوك " . $block['title'] . ": " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h2>✅ تم إصلاح جميع البلوكات بنجاح!</h2>";
    
} catch (Exception $e) {
    echo "❌ حدث خطأ عام: " . $e->getMessage();
}

echo "<p>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; margin: 5px; border-radius: 4px;'>عرض الموقع</a>";
echo "<a href='diagnose_blocks.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; margin: 5px; border-radius: 4px;'>تشخيص مرة أخرى</a>";
echo "</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1, h2, h3, h4 { color: #333; }
</style>
