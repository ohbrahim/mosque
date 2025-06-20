<?php
require_once 'config/config.php';

// معالجة الإجراءات
if ($_POST['action'] ?? false) {
    switch ($_POST['action']) {
        case 'add':
            try {
                $newBlock = [
                    'title' => $_POST['title'],
                    'content' => $_POST['content'], // بدون أي تنظيف
                    'block_type' => $_POST['block_type'],
                    'position' => $_POST['position'],
                    'status' => 'active',
                    'show_title' => isset($_POST['show_title']) ? 1 : 0,
                    'allow_html' => 1,
                    'is_safe' => 1,
                    'display_order' => (int)($_POST['display_order'] ?? 1),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $db->insert('blocks', $newBlock);
                $message = "✅ تم إضافة البلوك بنجاح!";
            } catch (Exception $e) {
                $message = "❌ خطأ: " . $e->getMessage();
            }
            break;
            
        case 'delete':
            try {
                $db->query("DELETE FROM blocks WHERE id = ?", [$_POST['block_id']]);
                $message = "✅ تم حذف البلوك بنجاح!";
            } catch (Exception $e) {
                $message = "❌ خطأ في الحذف: " . $e->getMessage();
            }
            break;
            
        case 'toggle':
            try {
                $currentStatus = $db->fetchOne("SELECT status FROM blocks WHERE id = ?", [$_POST['block_id']]);
                $newStatus = $currentStatus['status'] === 'active' ? 'inactive' : 'active';
                $db->query("UPDATE blocks SET status = ? WHERE id = ?", [$newStatus, $_POST['block_id']]);
                $message = "✅ تم تغيير حالة البلوك!";
            } catch (Exception $e) {
                $message = "❌ خطأ: " . $e->getMessage();
            }
            break;
    }
}

// جلب البلوكات
$blocks = $db->fetchAll("SELECT * FROM blocks ORDER BY position, display_order");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدير البلوكات البسيط</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .message { padding: 15px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; 
        }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .block-item { border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 4px; }
        .block-preview { background: #f8f9fa; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        .tabs { display: flex; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: #e9ecef; border: 1px solid #ddd; cursor: pointer; }
        .tab.active { background: #007bff; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <h1>مدير البلوكات البسيط</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="tabs">
            <div class="tab active" onclick="showTab('add')">إضافة بلوك جديد</div>
            <div class="tab" onclick="showTab('manage')">إدارة البلوكات</div>
            <div class="tab" onclick="showTab('templates')">قوالب جاهزة</div>
        </div>
        
        <!-- إضافة بلوك جديد -->
        <div id="add" class="tab-content active">
            <h2>إضافة بلوك جديد</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>عنوان البلوك:</label>
                    <input type="text" name="title" required>
                </div>
                
                <div class="form-group">
                    <label>نوع البلوك:</label>
                    <select name="block_type">
                        <option value="html">HTML مخصص</option>
                        <option value="iframe">إطار مدمج (iframe)</option>
                        <option value="marquee">نص متحرك (marquee)</option>
                        <option value="text">نص عادي</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>موقع البلوك:</label>
                    <select name="position">
                        <option value="right">الشريط الجانبي الأيمن</option>
                        <option value="left">الشريط الجانبي الأيسر</option>
                        <option value="top">أعلى الصفحة</option>
                        <option value="bottom">أسفل الصفحة</option>
                        <option value="center">وسط الصفحة</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>ترتيب العرض:</label>
                    <input type="number" name="display_order" value="1" min="1">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="show_title" checked> عرض العنوان
                    </label>
                </div>
                
                <div class="form-group">
                    <label>محتوى البلوك:</label>
                    <textarea name="content" rows="10" placeholder="أدخل محتوى البلوك هنا..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-success">إضافة البلوك</button>
            </form>
        </div>
        
        <!-- إدارة البلوكات -->
        <div id="manage" class="tab-content">
            <h2>البلوكات الموجودة (<?php echo count($blocks); ?>)</h2>
            
            <?php foreach ($blocks as $block): ?>
                <div class="block-item">
                    <h3><?php echo htmlspecialchars($block['title']); ?> 
                        <span style="color: <?php echo $block['status'] === 'active' ? 'green' : 'red'; ?>;">
                            (<?php echo $block['status'] === 'active' ? 'نشط' : 'غير نشط'; ?>)
                        </span>
                    </h3>
                    
                    <p><strong>النوع:</strong> <?php echo $block['block_type']; ?> | 
                       <strong>الموقع:</strong> <?php echo $block['position']; ?> | 
                       <strong>الترتيب:</strong> <?php echo $block['display_order']; ?></p>
                    
                    <div class="block-preview">
                        <strong>معاينة:</strong><br>
                        <?php echo $block['content']; ?>
                    </div>
                    
                    <div style="margin-top: 10px;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="block_id" value="<?php echo $block['id']; ?>">
                            <button type="submit" class="btn btn-warning">
                                <?php echo $block['status'] === 'active' ? 'إلغاء التفعيل' : 'تفعيل'; ?>
                            </button>
                        </form>
                        
                        <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="block_id" value="<?php echo $block['id']; ?>">
                            <button type="submit" class="btn btn-danger">حذف</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- قوالب جاهزة -->
        <div id="templates" class="tab-content">
            <h2>قوالب جاهزة</h2>
            
            <div class="block-item">
                <h3>أوقات الصلاة</h3>
                <textarea readonly rows="3" style="width: 100%;">&lt;iframe id="iframe" title="prayerWidget" style="width: 100%; height: 358px; border: 1px solid #ddd;" scrolling="no" src="https://www.islamicfinder.org/prayer-widget/2485801/shafi/15/0/18/17"&gt;&lt;/iframe&gt;</textarea>
                <button onclick="copyToClipboard(this.previousElementSibling)" class="btn btn-primary">نسخ</button>
            </div>
            
            <div class="block-item">
                <h3>إعلان متحرك</h3>
                <textarea readonly rows="3" style="width: 100%;">&lt;marquee direction="right"&gt;&lt;p&gt;&lt;font size="4" color="red"&gt;إنطلاق بث قنوات دروس الدعم لجميع المستويات والأطوار على موقع الديوان الوطني للتعليم والتكوين عن بعد على الرابط التالي : http://www.onefd.edu.dz/soutien-scolaire&lt;/font&gt;&lt;/p&gt;&lt;/marquee&gt;</textarea>
                <button onclick="copyToClipboard(this.previousElementSibling)" class="btn btn-primary">نسخ</button>
            </div>
            
            <div class="block-item">
                <h3>آية قرآنية</h3>
                <textarea readonly rows="5" style="width: 100%;">&lt;div class="card border-success"&gt;
    &lt;div class="card-body text-center"&gt;
        &lt;i class="fas fa-book-open text-success mb-3" style="font-size: 2rem;"&gt;&lt;/i&gt;
        &lt;p class="fs-6 fw-bold text-dark mb-3"&gt;وَمَا خَلَقْتُ الْجِنَّ وَالْإِنسَ إِلَّا لِيَعْبُدُونِ&lt;/p&gt;
        &lt;small class="text-muted"&gt;سورة الذاريات - آية ٥٦&lt;/small&gt;
    &lt;/div&gt;
&lt;/div&gt;</textarea>
                <button onclick="copyToClipboard(this.previousElementSibling)" class="btn btn-primary">نسخ</button>
            </div>
        </div>
        
        <p style="margin-top: 30px;">
            <a href="index.php" class="btn btn-primary">عرض الموقع</a>
            <a href="diagnose_blocks.php" class="btn btn-warning">تشخيص البلوكات</a>
        </p>
    </div>
    
    <script>
        function showTab(tabName) {
            // إخفاء جميع التبويبات
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => tab.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));
            
            // إظهار التبويب المحدد
            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }
        
        function copyToClipboard(textarea) {
            textarea.select();
            document.execCommand('copy');
            alert('تم نسخ الكود!');
        }
    </script>
</body>
</html>
