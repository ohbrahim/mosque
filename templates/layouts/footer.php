</main>
    <footer>
        <div>
            <span>المتواجدون الآن: <?php echo file_exists(__DIR__ . '/../../includes/online_users.txt') ? file_get_contents(__DIR__ . '/../../includes/online_users.txt') : '0'; ?></span> |
            <span>عدد الزوار الكلي: <?php echo file_exists(__DIR__ . '/../../includes/total_visitors.txt') ? file_get_contents(__DIR__ . '/../../includes/total_visitors.txt') : '0'; ?></span>
        </div>
        <p>&copy; <?php echo date('Y'); ?> <?php echo isset($settings['site_name']) ? $settings['site_name'] : 'My Personal Website'; ?>. جميع الحقوق محفوظة.</p>
    </footer>
    <script src="<?php echo SITE_URL; ?>/js/main.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));

            if ("IntersectionObserver" in window) {
                let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            let lazyImage = entry.target;
                            lazyImage.src = lazyImage.dataset.src;
                            lazyImage.classList.remove("lazy");
                            lazyImageObserver.unobserve(lazyImage);
                        }
                    });
                });

                lazyImages.forEach(function(lazyImage) {
                    lazyImageObserver.observe(lazyImage);
                });
            } else {
                // Fallback for browsers that don't support IntersectionObserver
            }
        });
    </script>
</body>
</html>
