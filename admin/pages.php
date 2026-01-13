<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    // ... (rest of the code)
}

// ... (rest of the code)
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الصفحات</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
</head>
<body>
    <h1>إدارة الصفحات</h1>
    <a href="index.php">العودة إلى لوحة التحكم</a>
    
    <?php if ($edit_page): ?>
    <h2>تعديل صفحة</h2>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $edit_page['id']; ?>">
        <input type="text" name="title" placeholder="عنوان الصفحة" value="<?php echo htmlspecialchars($edit_page['title']); ?>" required>
        <textarea name="content" placeholder="محتوى الصفحة" required><?php echo htmlspecialchars($edit_page['content']); ?></textarea>
        <button type="submit" name="update">تحديث</button>
    </form>
    <?php else: ?>
    <h2>إنشاء صفحة جديدة</h2>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="text" name="title" placeholder="عنوان الصفحة" required>
        <textarea name="content" placeholder="محتوى الصفحة" required></textarea>
        <button type="submit" name="create">إنشاء</button>
    </form>
    <?php endif; ?>

    <h2>الصفحات الحالية</h2>
    <table>
        <thead>
            <tr>
                <th>العنوان</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pages as $page): ?>
            <tr>
                <td><?php echo htmlspecialchars($page['title']); ?></td>
                <td>
                    <a href="pages.php?edit=<?php echo $page['id']; ?>">تعديل</a>
                    <a href="pages.php?delete=<?php echo $page['id']; ?>&csrf_token=<?php echo generate_csrf_token(); ?>" onclick="return confirm('هل أنت متأكد؟')">حذف</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
