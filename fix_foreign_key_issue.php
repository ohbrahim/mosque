<?php
/**
 * إصلاح مشكلة Foreign Key في الاستطلاعات
 */

require_once 'config/config.php';

echo "<h2>إصلاح مشكلة الاستطلاعات</h2>";

try {
    // حذف الأصوات القديمة
    $db->query("DELETE FROM poll_votes");
    echo "✅ تم حذف الأصوات القديمة<br>";
    
    // جلب معرف الاستطلاع والخيارات
    $poll = $db->fetchOne("SELECT id FROM polls LIMIT 1");
    if (!$poll) {
        echo "❌ لا يوجد استطلاع<br>";
        exit;
    }
    
    $pollId = $poll['id'];
    $options = $db->fetchAll("SELECT id FROM poll_options WHERE poll_id = ?", [$pollId]);
    
    if (empty($options)) {
        echo "❌ لا توجد خيارات للاستطلاع<br>";
        exit;
    }
    
    echo "✅ تم العثور على " . count($options) . " خيارات<br>";
    
    // إضافة أصوات جديدة بشكل صحيح
    $votes = [
        ['poll_id' => $pollId, 'option_id' => $options[0]['id'], 'voter_ip' => '192.168.1.1'],
        ['poll_id' => $pollId, 'option_id' => $options[0]['id'], 'voter_ip' => '192.168.1.2'],
        ['poll_id' => $pollId, 'option_id' => $options[1]['id'], 'voter_ip' => '192.168.1.3'],
        ['poll_id' => $pollId, 'option_id' => $options[0]['id'], 'voter_ip' => '192.168.1.4'],
        ['poll_id' => $pollId, 'option_id' => $options[2]['id'], 'voter_ip' => '192.168.1.5'],
        ['poll_id' => $pollId, 'option_id' => $options[1]['id'], 'voter_ip' => '192.168.1.6'],
        ['poll_id' => $pollId, 'option_id' => $options[0]['id'], 'voter_ip' => '192.168.1.7']
    ];
    
    foreach ($votes as $vote) {
        $db->insert('poll_votes', $vote);
    }
    
    echo "✅ تم إضافة " . count($votes) . " صوت بنجاح<br>";
    
    // التحقق من النتائج
    $results = $db->fetchAll("
        SELECT po.option_text, COUNT(pv.id) as votes_count
        FROM poll_options po 
        LEFT JOIN poll_votes pv ON po.id = pv.option_id 
        WHERE po.poll_id = ? 
        GROUP BY po.id 
        ORDER BY po.display_order
    ", [$pollId]);
    
    echo "<h3>نتائج الاستطلاع:</h3>";
    foreach ($results as $result) {
        echo "- " . $result['option_text'] . ": " . $result['votes_count'] . " صوت<br>";
    }
    
    echo "<br><h3 style='color: green;'>✅ تم إصلاح مشكلة الاستطلاعات بنجاح!</h3>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ حدث خطأ: " . $e->getMessage() . "</h3>";
}
?>
