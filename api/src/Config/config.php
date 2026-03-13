<?php

namespace App\Config;

/**
 * Loads .env from api directory and exposes get()
 */
final class Config
{
    /** @var array|null */
    private static $env = null;

    public static function load(string $baseDir): void
    {
        if (self::$env !== null) {
            return;
        }
        $path = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';
        self::$env = [];
        if (!is_file($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            self::$env[trim($name)] = trim($value, " \t\"'");
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        return self::$env[$key] ?? $default;
    }
}
