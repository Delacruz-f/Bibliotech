<?php
declare(strict_types=1);

namespace App\Core;

final class Url
{
    private static string $basePath = '';

    public static function setBasePath(string $basePath): void
    {
        self::$basePath = rtrim($basePath, '/');
    }

    public static function basePath(): string
    {
        return self::$basePath;
    }

    public static function to(string $path): string
    {
        return self::$basePath . '/' . ltrim($path, '/');
    }
}
