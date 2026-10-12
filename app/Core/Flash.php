<?php
declare(strict_types=1);

namespace App\Core;

final class Flash
{
    /** @param array<string, string> $messages */
    public static function set(array $messages): void
    {
        $_SESSION['_flash'] = $messages;
    }

    /** @return array<string, string> */
    public static function consume(): array
    {
        $messages = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        return is_array($messages) ? $messages : [];
    }
}
