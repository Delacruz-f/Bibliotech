<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Url;

final class DashboardController
{
    private const ROLE_NAMES = [
        'administrador' => 'Administración',
        'bibliotecario' => 'Bibliotecario',
        'estudiante' => 'Catálogo',
    ];

    public function show(string $requiredRole): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!is_array($user) || !isset($user['rol'])) {
            $this->redirect('/login');
        }

        if (($user['rol'] ?? '') !== $requiredRole) {
            $destinations = [
                'administrador' => '/admin',
                'bibliotecario' => '/bibliotecario',
                'estudiante' => '/home',
            ];
            $this->redirect($destinations[(string) $user['rol']] ?? '/login');
        }

        $pageTitle = self::ROLE_NAMES[$requiredRole] ?? 'BiblioTech';
        $csrfToken = Csrf::token();
        require dirname(__DIR__) . '/Views/dashboard.php';
    }

    private function redirect(string $path): void
    {
        header('Location: ' . Url::to($path));
        exit;
    }
}
