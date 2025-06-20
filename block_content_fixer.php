<?php
require_once 'config/config.php';

echo "<h1>أداة إصلاح محتوى البلوكات</h1>";

// دالة لفك تشفير HTML entities
function fixHtmlEntities($content) {
    // فك تشفير HTML entities
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    // إصلاحات إضافية
    $replacements = [
        '&nbsp;' => ' ',
        '&amp;' => '&',
        '&lt;' => '<',
        '&gt;' => '>',
        '&quot;' => '"',
        '&#39;' => "'",
    ];
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    // إزالة <br> الزائدة
    $content = preg_replace('/<br\s*\/?>\s*</', '<', $content);
    $content = preg_replace('/>\s*<br\s*\/?>/', '>', $content);
    
    return $content;
}

if ($_POST['action'] ?? false) {
    if ($_POST['action'] === 'fix_all') {
        try {
            $blocks = $db->fetchAll("SELECT * FROM blocks WHERE content LIKE '%&lt;%' OR content LIKE '%&gt;%' OR content LIKE '%&nbsp;%'");
            $fixed = 0;
            
            foreach ($blocks as $block) {
                $fixedContent = fixHtmlEntities($block['content']);
                $db->query("UPDATE blocks SET content = ?, updated_at = ? WHERE id = ?", [
                    $fixedContent,
                    date('Y-m-d H:i:s'),
                    $block['id']
                ]);
                $fixed++;
            }
            
            $message = "✅ تم إصلاح $fixed بلوك بنجاح!";
        } catch (Exception $e) {
            $message = "❌ خطأ: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'fix_single') {
        try {
            $blockId = $_POST['block_id'];
            $block = $db->fetchOne("SELECT * FROM blocks WHERE id = ?", [$blockId]);
            
            if ($block) {
                $fixedContent = fixHtmlEntities($block['content']);
                $db->query("UPDATE blocks SET content = ?, updated_at = ? WHERE id = ?", [
                    $fixedContent,
                    date('Y-m-d H:i:s'),
                    $blockId
                ]);
                $message = "✅ تم إصلاح البلوك رقم $blockId بنجاح!";
            } else {
                $message = "❌ البلوك غير موجود!";
            }
        } catch (Exception $e) {
            $message = "❌ خطأ: " . $e->getMessage();
        }
    }
}

// جلب البلوكات التي تحتاج إصلاح
$problematicBlocks = $db->fetchAll("SELECT * FROM blocks WHERE content LIKE '%&lt;%' OR content LIKE '%&gt;%' OR content LIKE '%&nbsp;%'");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أداة إصلاح محتوى البلوكات</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .message { padding: 15px; margin: 10px 0; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .block-item { border: 1px solid #ddd; margin: 10px 0; padding: 15px; border-radius: 4px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 2px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .content-box { background: #f8f9fa; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin: 10px 0; max-height: 200px; overflow: auto; }
        textarea { width: 100%; height: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>أداة إصلاح محتوى البلوكات</h1>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div style="background: #e7f3ff; padding: 15px; border-radius: 4px; margin: 20px 0;">
            <h3>البلوكات التي تحتاج إصلاح: <?php echo count($problematicBlocks); ?></h3>
            
            <?php if (count($problematicBlocks) > 0): ?>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="fix_all">
                    <button type="submit" class="btn btn-success" onclick="return confirm('هل أنت متأكد من إصلاح جميع البلوكات؟')">
                        إصلاح جميع البلوكات (<?php echo count($problematicBlocks); ?>)
                    </button>
                </form>
            <?php else: ?>
                <p style="color: green;">✅ جميع البلوكات سليمة!</p>
            <?php endif; ?>
        </div>
        
        <?php foreach ($problematicBlocks as $block): ?>
            <div class="block-item">
                <h3>البلوك: <?php echo htmlspecialchars($block['title']); ?> (ID: <?php echo $block['id']; ?>)</h3>
                
                <h4>المحتوى الحالي (مُرمز):</h4>
                <textarea readonly><?php echo htmlspecialchars(substr($block['content'], 0, 500)); ?></textarea>
                
                <h4>المحتوى بعد الإصلاح:</h4>
                <textarea readonly><?php echo htmlspecialchars(substr(fixHtmlEntities($block['content']), 0, 500)); ?></textarea>
                
                <h4>معاينة بعد الإصلاح:</h4>
                <div class="content-box">
                    <?php echo fixHtmlEntities($block['content']); ?>
                </div>
                
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="fix_single">
                    <input type="hidden" name="block_id" value="<?php echo $block['id']; ?>">
                    <button type="submit" class="btn btn-primary">إصلاح هذا البلوك</button>
                </form>
            </div>
        <?php endforeach; ?>
        
        <p style="margin-top: 30px;">
            <a href="index.php" class="btn btn-primary">عرض الموقع</a>
            <a href="diagnose_blocks.php" class="btn btn-success">تشخيص البلوكات</a>
        </p>
    </div>
</body>
</html>
