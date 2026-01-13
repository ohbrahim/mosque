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
    <title>إدارة البلوكات</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
</head>
<body>
    <h1>إدارة البلوكات</h1>
    <a href="index.php">العودة إلى لوحة التحكم</a>

    <?php if ($edit_block): ?>
    <h2>تعديل بلوك</h2>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $edit_block['id']; ?>">
        <input type="text" name="title" placeholder="عنوان البلوك" value="<?php echo htmlspecialchars($edit_block['title']); ?>" required>
        <textarea name="content" placeholder="محتوى البلوك" required><?php echo htmlspecialchars($edit_block['content']); ?></textarea>
        <label>
            <input type="checkbox" name="is_active" <?php echo $edit_block['is_active'] ? 'checked' : ''; ?>>
            فعال
        </label>
        <button type="submit" name="update">تحديث</button>
    </form>
    <?php else: ?>
    <h2>إنشاء بلوك جديد</h2>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="text" name="title" placeholder="عنوان البلوك" required>
        <textarea name="content" placeholder="محتوى البلوك" required></textarea>
        <button type="submit" name="create">إنشاء</button>
    </form>
    <?php endif; ?>

    <h2>البلوكات الحالية</h2>
    <table>
        <thead>
            <tr>
                <th>العنوان</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($blocks as $block): ?>
            <tr>
                <td><?php echo htmlspecialchars($block['title']); ?></td>
                <td><?php echo $block['is_active'] ? 'فعال' : 'غير فعال'; ?></td>
                <td>
                    <a href="blocks.php?edit=<?php echo $block['id']; ?>">تعديل</a>
                    <a href="blocks.php?delete=<?php echo $block['id']; ?>&csrf_token=<?php echo generate_csrf_token(); ?>" onclick="return confirm('هل أنت متأكد؟')">حذف</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
