<?php
declare(strict_types=1);

namespace App\Core;




/** Mensajes de una sola vista para el panel (clave propia, no choca con AuthController). */
final class Flash
{
    private const KEY = '_flash_panel';

    public static function set(string $type, string $message): void
    {
        $_SESSION[self::KEY][] = ['type' => $type, 'message' => $message];
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    /** @return list<array{type: string, message: string}> */
    public static function pull(): array
    {
        $messages = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);

        return is_array($messages) ? array_values($messages) : [];
    }
}