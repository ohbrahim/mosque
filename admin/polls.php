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
    <title>إدارة الاستفتاءات</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
</head>
<body>
    <h1>إدارة الاستفتاءات</h1>
    <a href="index.php">العودة إلى لوحة التحكم</a>

    <h2>إنشاء استفتاء جديد</h2>
    <form method="post">
        <?php echo csrf_input(); ?>
        <input type="text" name="question" placeholder="سؤال الاستفتاء" required>
        <div id="options-container">
            <input type="text" name="options[]" placeholder="خيار 1" required>
            <input type="text" name="options[]" placeholder="خيار 2" required>
        </div>
        <button type="button" id="add-option">إضافة خيار</button>
        <button type="submit" name="create">إنشاء</button>
    </form>

    <h2>الاستفتاءات الحالية</h2>
    <table>
        <thead>
            <tr>
                <th>السؤال</th>
                <th>الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($polls as $poll): ?>
            <tr>
                <td><?php echo htmlspecialchars($poll['question']); ?></td>
                <td>
                    <a href="polls.php?delete=<?php echo $poll['id']; ?>&csrf_token=<?php echo generate_csrf_token(); ?>" onclick="return confirm('هل أنت متأكد؟')">حذف</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        document.getElementById('add-option').addEventListener('click', function() {
            var container = document.getElementById('options-container');
            var input = document.createElement('input');
            input.type = 'text';
            input.name = 'options[]';
            input.placeholder = 'خيار ' + (container.children.length + 1);
            container.appendChild(input);
        });
    </script>
</body>
</html>
