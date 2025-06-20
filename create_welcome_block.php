<?php
require_once 'config/config.php';

echo "<h1>إنشاء بلوك مرحباً بكم</h1>";

try {
    // إنشاء بلوك مرحباً بكم
    $welcomeContent = '
    <div class="welcome-section text-center py-4">
        <h2 class="mb-4" style="color: #2c5530; font-weight: bold;">مرحباً بكم في موقع المسجد</h2>
        <p class="lead mb-4" style="color: #6c757d;">نسعد بزيارتكم ونتمنى أن تجدوا ما تبحثون عنه</p>
        
        <div class="row mt-4">
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm border-0" style="transition: all 0.3s ease;">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-mosque fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title" style="color: #2c5530;">أوقات الصلاة</h5>
                        <p class="card-text text-muted">تابع أوقات الصلاة اليومية</p>
                        <div class="mt-3">
                            <a href="#prayer-times" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-clock me-1"></i>عرض الأوقات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm border-0" style="transition: all 0.3s ease;">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-book-open fa-3x text-success"></i>
                        </div>
                        <h5 class="card-title" style="color: #2c5530;">الدروس والمحاضرات</h5>
                        <p class="card-text text-muted">استمع للدروس والمحاضرات الدينية</p>
                        <div class="mt-3">
                            <a href="?page=lessons" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-play me-1"></i>استمع الآن
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm border-0" style="transition: all 0.3s ease;">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-calendar-alt fa-3x text-info"></i>
                        </div>
                        <h5 class="card-title" style="color: #2c5530;">الفعاليات</h5>
                        <p class="card-text text-muted">تابع فعاليات وأنشطة المسجد</p>
                        <div class="mt-3">
                            <a href="?page=events" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-calendar me-1"></i>عرض الفعاليات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .welcome-section .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    .welcome-section .icon-container {
        transition: all 0.3s ease;
    }
    .welcome-section .card:hover .icon-container {
        transform: scale(1.1);
    }
    .welcome-section .btn {
        transition: all 0.3s ease;
    }
    .welcome-section .btn:hover {
        transform: translateY(-2px);
    }
    </style>';
    
    $welcomeBlock = [
        'title' => 'مرحباً بكم',
        'content' => $welcomeContent,
        'block_type' => 'welcome',
        'position' => 'center', // يمكن تغييرها إلى top, center, bottom حسب الحاجة
        'status' => 'active',
        'show_title' => 0, // لا نريد إظهار العنوان لأنه موجود في المحتوى
        'css_class' => 'welcome-block',
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'created_by' => 1
    ];
    
    $db->insert('blocks', $welcomeBlock);
    echo "<p style='color: green;'>✅ تم إنشاء بلوك مرحباً بكم بنجاح!</p>";
    
    // إضافة بلوك الروابط الخارجية المحسن
    require_once 'includes/enhanced_blocks_v2.php';
    
    $externalLinksBlock = [
        'title' => 'روابط خارجية مفيدة',
        'content' => renderEnhancedExternalLinksBlock(),
        'block_type' => 'external_links',
        'position' => 'left',
        'status' => 'active',
        'show_title' => 1,
        'css_class' => 'external-links-block',
        'allow_html' => 1,
        'is_safe' => 1,
        'display_order' => 5,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'created_by' => 1
    ];
    
    $db->insert('blocks', $externalLinksBlock);
    echo "<p style='color: green;'>✅ تم إنشاء بلوك الروابط الخارجية المحسن بنجاح!</p>";
    
    echo "<h2>✅ تم إنشاء جميع البلوكات بنجاح!</h2>";
    echo "<p><strong>ملاحظة:</strong> يمكنك تغيير موقع بلوك 'مرحباً بكم' من لوحة التحكم عبر تعديل حقل 'الموقع' إلى:</p>";
    echo "<ul>";
    echo "<li><strong>center</strong> - وسط الصفحة (الافتراضي)</li>";
    echo "<li><strong>top</strong> - أعلى الصفحة</li>";
    echo "<li><strong>bottom</strong> - أسفل الصفحة</li>";
    echo "</ul>";
    
    echo "<p><a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-right: 10px;'>عرض الموقع</a>";
    echo "<a href='admin/blocks.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>إدارة البلوكات</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ حدث خطأ: " . $e->getMessage() . "</p>";
}
?>
