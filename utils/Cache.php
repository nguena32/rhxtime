<?php
// utils/Cache.php
class Cache {
    private static $cacheDir = __DIR__ . '/../tmp/cache/';

    public static function init() {
        if (!is_dir(self::$cacheDir)) {
            @mkdir(self::$cacheDir, 0777, true);
        }
    }

    public static function get($key, $ttl = 300) {
        self::init();
        $file = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
            $data = file_get_contents($file);
            return json_decode($data, true);
        }
        return false;
    }

    public static function set($key, $data) {
        self::init();
        $file = self::$cacheDir . md5($key) . '.cache';
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    public static function delete($key) {
        self::init();
        $file = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
