<?php

namespace App\Core\Cache;

class CacheManager
{
    private static ?CacheInterface $instance = null;

    public static function getInstance(): CacheInterface
    {
        if (self::$instance === null) {
            self::$instance = new FileCache();
        }
        return self::$instance;
    }

    public static function setInstance(?CacheInterface $cache): void
    {
        self::$instance = $cache;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getInstance()->get($key, $default);
    }

    public static function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        return self::getInstance()->set($key, $value, $ttl);
    }

    public static function delete(string $key): bool
    {
        return self::getInstance()->delete($key);
    }

    public static function clear(): bool
    {
        return self::getInstance()->clear();
    }
}

