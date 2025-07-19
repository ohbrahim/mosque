<?php
require_once __DIR__ . '/../includes/cache.php';
require_once __DIR__ . '/../includes/db.php'; // Ensure $pdo is available

$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';
$url = filter_var($url, FILTER_SANITIZE_URL);

$page_content = $cache->get($url);

if (!$page_content) {
    ob_start();
    require_once __DIR__ . '/../includes/functions.php';

    // Update visitor count
    $total_visitors_file = __DIR__ . '/../includes/total_visitors.txt';
    if (file_exists($total_visitors_file)) {
        $total_visitors = (int)file_get_contents($total_visitors_file);
        $total_visitors++;
        file_put_contents($total_visitors_file, $total_visitors);
    }

    // For simplicity, we'll just show a random number for online users for now
    $online_users_file = __DIR__ . '/../includes/online_users.txt';
    if (file_exists($online_users_file)) {
        file_put_contents($online_users_file, rand(1, 10));
    }

    // Simple router
    $urlParts = explode('/', $url);
    $slug = !empty($urlParts[0]) ? $urlParts[0] : 'home';

    if ($slug === 'search') {
        require_once __DIR__ . '/../templates/layouts/header.php';
        require_once __DIR__ . '/../templates/pages/search.php';
        require_once __DIR__ . '/../templates/layouts/footer.php';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = TRUE");
        $stmt->execute([$slug]);
        $page = $stmt->fetch();

        if ($page) {
            require_once __DIR__ . '/../templates/layouts/header.php';
            echo "<h2>" . htmlspecialchars($page['title']) . "</h2>";
            echo "<div>" . $page['content'] . "</div>";
            require_once __DIR__ . '/../templates/layouts/footer.php';
        } else {
            header("HTTP/1.0 404 Not Found");
            require_once __DIR__ . '/../templates/pages/404.php';
        }
    }

    $page_content = ob_get_clean();
    $cache->set($url, $page_content);
}

echo $page_content;
?>
