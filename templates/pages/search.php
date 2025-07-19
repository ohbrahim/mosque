<?php
$query = isset($_GET['q']) ? $_GET['q'] : '';
$results = [];

if ($query) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE title LIKE ? OR content LIKE ?");
    $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
    $results = $stmt->fetchAll();
}
?>
<h2>نتائج البحث عن "<?php echo htmlspecialchars($query); ?>"</h2>

<?php if (empty($results)): ?>
    <p>لم يتم العثور على نتائج.</p>
<?php else: ?>
    <ul>
        <?php foreach ($results as $result): ?>
            <li>
                <h3><a href="<?php echo SITE_URL . '/' . $result['slug']; ?>"><?php echo htmlspecialchars($result['title']); ?></a></h3>
                <p><?php echo htmlspecialchars(substr($result['content'], 0, 150)); ?>...</p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
