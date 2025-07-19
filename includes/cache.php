<?php
class Cache {
    private $cache_dir;
    private $cache_time;

    public function __construct($cache_dir = 'cache', $cache_time = 3600) {
        $this->cache_dir = __DIR__ . '/../' . $cache_dir;
        $this->cache_time = $cache_time;
        if (!file_exists($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }

    public function get($key) {
        $file = $this->cache_dir . '/' . md5($key);
        if (file_exists($file) && (time() - $this->cache_time) < filemtime($file)) {
            return unserialize(file_get_contents($file));
        }
        return false;
    }

    public function set($key, $data) {
        $file = $this->cache_dir . '/' . md5($key);
        file_put_contents($file, serialize($data));
    }
}

$cache = new Cache();
?>
