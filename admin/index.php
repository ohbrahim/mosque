<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['remember_me'])) {
        list($user_id, $token) = explode(':', $_COOKIE['remember_me'], 2);
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user && hash_equals($user['remember_token'], $token)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
        }
    } else {
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
</head>
<body>
    <h1>أهلاً بك في لوحة التحكم</h1>
    <p>هنا يمكنك إدارة الموقع.</p>
    <ul>
        <li><a href="pages.php">إدارة الصفحات</a></li>
        <li><a href="blocks.php">إدارة البلوكات</a></li>
        <li><a href="polls.php">إدارة الاستفتاءات</a></li>
    </ul>
    <a href="logout.php">تسجيل الخروج</a>
</body>
</html>
