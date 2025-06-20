<?php
require_once 'config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!verifyCSRFToken($input['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'رمز الأمان غير صحيح']);
        exit;
    }
    
    $pageId = (int)($input['page_id'] ?? 0);
    $rating = (int)($input['rating'] ?? 0);
    $userId = $_SESSION['user_id'] ?? null;
    $userIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    if ($pageId <= 0) {
        echo json_encode(['success' => false, 'message' => 'معرف الصفحة غير صحيح']);
        exit;
    }
    
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'التقييم يجب أن يكون بين 1 و 5']);
        exit;
    }
    
    try {
        // التحقق من وجود الصفحة
        $page = $db->fetchOne("SELECT id FROM pages WHERE id = ? AND status = 'published'", [$pageId]);
        if (!$page) {
            echo json_encode(['success' => false, 'message' => 'الصفحة غير موجودة']);
            exit;
        }
        
        // التحقق من التقييم السابق
        $existingRating = null;
        if ($userId) {
            $existingRating = $db->fetchOne("SELECT id, rating FROM ratings WHERE page_id = ? AND user_id = ?", [$pageId, $userId]);
        } else {
            $existingRating = $db->fetchOne("SELECT id, rating FROM ratings WHERE page_id = ? AND user_ip = ? AND user_id IS NULL", [$pageId, $userIp]);
        }
        
        if ($existingRating) {
            // تحديث التقييم الموجود
            $db->update('ratings', 
                ['rating' => $rating, 'created_at' => date('Y-m-d H:i:s')], 
                'id = ?', 
                [$existingRating['id']]
            );
            
            echo json_encode([
                'success' => true, 
                'message' => 'تم تحديث التقييم بنجاح',
                'updated' => true,
                'old_rating' => $existingRating['rating'],
                'new_rating' => $rating
            ]);
        } else {
            // إضافة تقييم جديد
            $db->insert('ratings', [
                'page_id' => $pageId,
                'user_id' => $userId,
                'user_ip' => $userIp,
                'rating' => $rating,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'تم حفظ التقييم بنجاح',
                'updated' => false,
                'rating' => $rating
            ]);
        }
        
        // تسجيل النشاط
        if (function_exists('logActivity')) {
            logActivity($db, 'rate_page', "تقييم الصفحة {$pageId} بـ {$rating} نجوم", $userId);
        }
        
    } catch (Exception $e) {
        error_log("Rating error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'حدث خطأ في النظام. يرجى المحاولة لاحقاً.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير مسموحة']);
}
?>
