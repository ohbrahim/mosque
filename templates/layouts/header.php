<?php
$stmt = $pdo->query("SELECT * FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($settings['site_name']) ? $settings['site_name'] : 'My Personal Website'; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
</head>
<body>
    <header>
        <div class="top-banners">
            <?php if (!empty($settings['ad_banner_enabled']) && $settings['ad_banner_enabled']): ?>
            <div class="banner-ad">
                <?php echo isset($settings['ad_banner_content']) ? $settings['ad_banner_content'] : ''; ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($settings['event_banner_enabled']) && $settings['event_banner_enabled']): ?>
            <div class="banner-event">
                <?php echo isset($settings['event_banner_content']) ? $settings['event_banner_content'] : ''; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="header-main">
            <h1><a href="<?php echo SITE_URL; ?>"><?php echo isset($settings['site_name']) ? $settings['site_name'] : 'My Personal Website'; ?></a></h1>
            <form action="<?php echo SITE_URL; ?>/search" method="get">
                <input type="search" name="q" placeholder="ابحث في الموقع..." required>
                <button type="submit">بحث</button>
            </form>
        </div>
        <nav>
            <ul>
                <li><a href="<?php echo SITE_URL; ?>">الرئيسية</a></li>
                <li><a href="<?php echo SITE_URL; ?>/cv">السيرة الذاتية</a></li>
                <li><a href="<?php echo SITE_URL; ?>/services">خدماتنا</a></li>
                <li><a href="<?php echo SITE_URL; ?>/portfolio">أعمالي المنجزة</a></li>
                <li><a href="<?php echo SITE_URL; ?>/contact">اتصل بنا</a></li>
            </ul>
        </nav>
    </header>
    <main>
