<?php
declare(strict_types=1);

namespace App\Core;

/** Mensajes de una sola vista para panel y estudiante (clave propia, no choca con AuthController). */
final class Flash
{
    private const KEY = '_flash_panel';

    /**
     * Acepta los dos formatos usados por el proyecto:
     * - Flash::set('success', 'Mensaje')
     * - Flash::set(['success' => 'Mensaje'])
     *
     * @param array<string, string>|string $type
     */
    public static function set(array|string $type, ?string $message = null): void
    {
        if (is_array($type)) {
            $_SESSION[self::KEY] = $type;

            return;
        }

        $messages = $_SESSION[self::KEY] ?? [];
        if (!is_array($messages)) {
            $messages = [];
        }

        $messages[] = ['type' => $type, 'message' => (string) $message];
        $_SESSION[self::KEY] = $messages;
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    /**
     * @return array<string, string>|list<array{type: string, message: string}>
     */
    public static function pull(): array
    {
        $messages = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);

        if (!is_array($messages)) {
            return [];
        }

        if (array_is_list($messages)) {
            return array_values($messages);
        }

        return $messages;
    }

    /**
     * Alias para compatibilidad con rutas de estudiante.
     *
     * @return array<string, string>|list<array{type: string, message: string}>
     */
    public static function consume(): array
    {
        return self::pull();
    }
}