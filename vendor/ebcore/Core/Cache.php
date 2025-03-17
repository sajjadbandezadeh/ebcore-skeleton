<?php

namespace ebcore\Core;

class Cache
{
    private static $storage = [];
    private static $prefix = 'ebcore_cache:';
    private static $defaultTtl = 3600; // 1 hour
    private static $cacheDir;

    public static function initialize()
    {
        self::$cacheDir = dirname(__DIR__, 3) . '/storage/cache';
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
    }

    public static function get($key)
    {
        $key = self::$prefix . $key;
        
        // Check memory first
        if (isset(self::$storage[$key])) {
            $data = self::$storage[$key];
            if ($data['expires_at'] > time()) {
                return $data['value'];
            }
            unset(self::$storage[$key]);
        }

        // Check file
        $path = self::getFilePath($key);
        if (file_exists($path)) {
            $data = unserialize(file_get_contents($path));
            if ($data['expires_at'] > time()) {
                self::$storage[$key] = $data;
                return $data['value'];
            }
            unlink($path);
        }

        return null;
    }

    public static function put($key, $value, $ttl = null)
    {
        $key = self::$prefix . $key;
        $ttl = $ttl ?? self::$defaultTtl;

        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl
        ];

        // Save in memory
        self::$storage[$key] = $data;

        // Save in file
        $path = self::getFilePath($key);
        file_put_contents($path, serialize($data));

        return true;
    }

    public static function increment($key, $value = 1)
    {
        $current = self::get($key);
        if ($current === null) {
            return false;
        }

        $newValue = (int)$current + $value;
        self::put($key, $newValue);

        return $newValue;
    }

    public static function decrement($key, $value = 1)
    {
        return self::increment($key, -$value);
    }

    public static function has($key)
    {
        return self::get($key) !== null;
    }

    public static function forget($key)
    {
        $key = self::$prefix . $key;
        
        // Remove from memory
        unset(self::$storage[$key]);

        // Remove from file
        $path = self::getFilePath($key);
        if (file_exists($path)) {
            unlink($path);
        }

        return true;
    }

    private static function getFilePath($key)
    {
        return self::$cacheDir . '/' . md5($key) . '.cache';
    }

    public static function cleanup()
    {
        $now = time();

        // Cleanup memory
        foreach (self::$storage as $key => $data) {
            if ($data['expires_at'] <= $now) {
                unset(self::$storage[$key]);
            }
        }
        // Cleanup files
        foreach (glob(self::$cacheDir . '/*.cache') as $file) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires_at'] <= $now) {
                unlink($file);
            }
        }
    }
}