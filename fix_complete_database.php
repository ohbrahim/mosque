<?php
/**
 * إصلاح شامل لقاعدة البيانات وإضافة البيانات الافتراضية
 */

require_once 'config/config.php';

echo "<h2>إصلاح قاعدة البيانات بشكل شامل</h2>";

try {
    // إنشاء جدول الصفحات مع جميع الأعمدة المطلوبة
    $db->query("
        CREATE TABLE IF NOT EXISTS pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            content TEXT,
            excerpt TEXT,
            meta_description TEXT,
            featured_image VARCHAR(255),
            status ENUM('published', 'draft', 'private') DEFAULT 'published',
            is_featured TINYINT(1) DEFAULT 0,
            allow_comments TINYINT(1) DEFAULT 1,
            allow_ratings TINYINT(1) DEFAULT 1,
            views_count INT DEFAULT 0,
            author_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول الصفحات تم إنشاؤه<br>";

    // إنشاء جدول البلوكات
    $db->query("
        CREATE TABLE IF NOT EXISTS blocks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255),
            content TEXT,
            block_type VARCHAR(50) NOT NULL,
            position VARCHAR(20) NOT NULL,
            display_order INT DEFAULT 1,
            status ENUM('active', 'inactive') DEFAULT 'active',
            show_title TINYINT(1) DEFAULT 1,
            css_class VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            updated_by INT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول البلوكات تم إنشاؤه<br>";

    // إنشاء جدول التعليقات
    $db->query("
        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_id INT NOT NULL,
            user_id INT,
            author_name VARCHAR(100),
            author_email VARCHAR(100),
            content TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول التعليقات تم إنشاؤه<br>";

    // إنشاء جدول الاستطلاعات
    $db->query("
        CREATE TABLE IF NOT EXISTS polls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            status ENUM('active', 'inactive') DEFAULT 'active',
            start_date DATE,
            end_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول الاستطلاعات تم إنشاؤه<br>";

    // إنشاء جدول خيارات الاستطلاعات
    $db->query("
        CREATE TABLE IF NOT EXISTS poll_options (
            id INT AUTO_INCREMENT PRIMARY KEY,
            poll_id INT NOT NULL,
            option_text VARCHAR(255) NOT NULL,
            display_order INT DEFAULT 1,
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول خيارات الاستطلاعات تم إنشاؤه<br>";

    // إنشاء جدول أصوات الاستطلاعات
    $db->query("
        CREATE TABLE IF NOT EXISTS poll_votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            poll_id INT NOT NULL,
            option_id INT NOT NULL,
            user_id INT,
            voter_ip VARCHAR(45),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
            FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول أصوات الاستطلاعات تم إنشاؤه<br>";

    // إنشاء جدول الزوار
    $db->query("
        CREATE TABLE IF NOT EXISTS visitors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45),
            user_agent TEXT,
            page_url VARCHAR(255),
            visit_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            user_id INT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول الزوار تم إنشاؤه<br>";

    // إنشاء جدول الإعلانات
    $db->query("
        CREATE TABLE IF NOT EXISTS advertisements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            image VARCHAR(255),
            link_url VARCHAR(255),
            position ENUM('header', 'sidebar', 'footer', 'content') DEFAULT 'sidebar',
            status ENUM('active', 'inactive') DEFAULT 'active',
            start_date DATE,
            end_date DATE,
            clicks_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ جدول الإعلانات تم إنشاؤه<br>";

    // حذف البيانات القديمة
    $db->query("DELETE FROM pages");
    $db->query("DELETE FROM blocks");
    $db->query("DELETE FROM comments");
    $db->query("DELETE FROM polls");
    $db->query("DELETE FROM poll_options");
    $db->query("DELETE FROM poll_votes");

    // إضافة صفحات تجريبية
    $pages = [
        [
            'title' => 'عن المسجد',
            'slug' => 'about',
            'content' => 'مرحباً بكم في مسجد النور. نحن مجتمع إسلامي يهدف إلى خدمة المسلمين وتقديم الخدمات الدينية والاجتماعية.',
            'excerpt' => 'تعرف على مسجد النور ورسالته',
            'is_featured' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'الخدمات',
            'slug' => 'services',
            'content' => 'نقدم العديد من الخدمات منها: الصلوات الخمس، صلاة الجمعة، الدروس الدينية، حفظ القرآن، والأنشطة الاجتماعية.',
            'excerpt' => 'اكتشف الخدمات التي نقدمها',
            'is_featured' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'الفعاليات',
            'slug' => 'events',
            'content' => 'نقوم بتنظيم فعاليات دينية واجتماعية متنوعة طوال العام. تابعونا لمعرفة آخر الفعاليات والأنشطة.',
            'excerpt' => 'تابع فعالياتنا وأنشطتنا',
            'is_featured' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'اتصل بنا',
            'slug' => 'contact',
            'content' => 'يمكنكم التواصل معنا عبر الهاتف أو البريد الإلكتروني أو زيارتنا في المسجد. نحن هنا لخدمتكم.',
            'excerpt' => 'طرق التواصل معنا',
            'is_featured' => 0,
            'status' => 'published'
        ],
        [
            'title' => 'القرآن الكريم',
            'slug' => 'quran',
            'content' => 'اقرأ القرآن الكريم واستمع إلى التلاوات المختلفة. نوفر لكم مصحف إلكتروني سهل الاستخدام.',
            'excerpt' => 'اقرأ واستمع للقرآن الكريم',
            'is_featured' => 1,
            'status' => 'published'
        ],
        [
            'title' => 'المكتبة الإسلامية',
            'slug' => 'library',
            'content' => 'مكتبة شاملة تحتوي على الكتب الإسلامية والمراجع الدينية المختلفة.',
            'excerpt' => 'استفد من مكتبتنا الإسلامية',
            'is_featured' => 1,
            'status' => 'published'
        ]
    ];

    foreach ($pages as $page) {
        $db->insert('pages', $page);
    }
    echo "✅ تم إضافة " . count($pages) . " صفحة تجريبية<br>";

    // إضافة بلوكات تجريبية
    $blocks = [
        [
            'title' => 'أوقات الصلاة',
            'content' => '',
            'block_type' => 'prayer_times',
            'position' => 'right',
            'display_order' => 1,
            'status' => 'active'
        ],
        [
            'title' => 'حالة الطقس',
            'content' => '',
            'block_type' => 'weather',
            'position' => 'right',
            'display_order' => 2,
            'status' => 'active'
        ],
        [
            'title' => 'آية اليوم',
            'content' => '',
            'block_type' => 'quran_verse',
            'position' => 'left',
            'display_order' => 1,
            'status' => 'active'
        ],
        [
            'title' => 'حديث اليوم',
            'content' => '',
            'block_type' => 'hadith',
            'position' => 'left',
            'display_order' => 2,
            'status' => 'active'
        ],
        [
            'title' => 'الصفحات الحديثة',
            'content' => '',
            'block_type' => 'recent_pages',
            'position' => 'right',
            'display_order' => 3,
            'status' => 'active'
        ],
        [
            'title' => 'إحصائيات الزوار',
            'content' => '',
            'block_type' => 'visitor_stats',
            'position' => 'left',
            'display_order' => 3,
            'status' => 'active'
        ],
        [
            'title' => 'مرحباً بكم',
            'content' => '<div class="alert alert-info"><h4>أهلاً وسهلاً</h4><p>مرحباً بكم في موقع مسجد النور. نسعد بزيارتكم ونتمنى أن تجدوا ما تبحثون عنه.</p></div>',
            'block_type' => 'custom',
            'position' => 'top',
            'display_order' => 1,
            'status' => 'active'
        ],
        [
            'title' => 'روابط التواصل الاجتماعي',
            'content' => '',
            'block_type' => 'social_links',
            'position' => 'right',
            'display_order' => 4,
            'status' => 'active'
        ]
    ];

    foreach ($blocks as $block) {
        $db->insert('blocks', $block);
    }
    echo "✅ تم إضافة " . count($blocks) . " بلوك تجريبي<br>";

    // إضافة استطلاع تجريبي
    $pollId = $db->insert('polls', [
        'title' => 'ما رأيكم في خدمات المسجد؟',
        'description' => 'نود معرفة رأيكم في الخدمات التي نقدمها',
        'status' => 'active'
    ]);

    $pollOptions = [
        'ممتازة',
        'جيدة جداً',
        'جيدة',
        'تحتاج تحسين'
    ];

    foreach ($pollOptions as $index => $option) {
        $db->insert('poll_options', [
            'poll_id' => $pollId,
            'option_text' => $option,
            'display_order' => $index + 1
        ]);
    }
    echo "✅ تم إضافة استطلاع تجريبي مع " . count($pollOptions) . " خيارات<br>";

    // إضافة تعليقات تجريبية
    $comments = [
        [
            'page_id' => 1, // صفحة "عن المسجد"
            'author_name' => 'أحمد محمد',
            'author_email' => 'ahmed@example.com',
            'content' => 'بارك الله فيكم على هذه الخدمات الرائعة',
            'status' => 'approved'
        ],
        [
            'page_id' => 1,
            'author_name' => 'فاطمة علي',
            'author_email' => 'fatima@example.com',
            'content' => 'جزاكم الله خيراً على جهودكم المباركة',
            'status' => 'approved'
        ],
        [
            'page_id' => 2, // صفحة "الخدمات"
            'author_name' => 'محمد عبدالله',
            'author_email' => 'mohammed@example.com',
            'content' => 'الخدمات ممتازة ونتمنى المزيد',
            'status' => 'approved'
        ]
    ];

    foreach ($comments as $comment) {
        $db->insert('comments', $comment);
    }
    echo "✅ تم إضافة " . count($comments) . " تعليق تجريبي<br>";

    // إضافة بعض الأصوات للاستطلاع
    $votes = [
        ['poll_id' => $pollId, 'option_id' => 1, 'voter_ip' => '192.168.1.1'],
        ['poll_id' => $pollId, 'option_id' => 1, 'voter_ip' => '192.168.1.2'],
        ['poll_id' => $pollId, 'option_id' => 2, 'voter_ip' => '192.168.1.3'],
        ['poll_id' => $pollId, 'option_id' => 1, 'voter_ip' => '192.168.1.4'],
        ['poll_id' => $pollId, 'option_id' => 3, 'voter_ip' => '192.168.1.5']
    ];

    foreach ($votes as $vote) {
        $db->insert('poll_votes', $vote);
    }
    echo "✅ تم إضافة " . count($votes) . " صوت للاستطلاع<br>";

    echo "<br><h3 style='color: green;'>✅ تم إصلاح قاعدة البيانات بنجاح!</h3>";
    echo "<p><a href='index.php'>انتقل إلى الصفحة الرئيسية</a></p>";
    echo "<p><a href='admin/index.php'>انتقل إلى لوحة التحكم</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ حدث خطأ: " . $e->getMessage() . "</h3>";
}
?>
