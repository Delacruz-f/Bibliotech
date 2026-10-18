<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Url;
use App\Models\Usuario;
use PDOException;

final class AuthController
{
    public function index(): void
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('/login');
        }

        $this->redirectForRole((string) ($_SESSION['user']['rol'] ?? ''));
    }

    public function showLogin(): void
    {
        if (isset($_SESSION['user'])) {
            $this->redirectForRole((string) ($_SESSION['user']['rol'] ?? ''));
        }

        $flash = $this->consumeFlash();
        $this->render('login', [
            'csrfToken' => Csrf::token(),
            'error' => $flash['error'] ?? null,
            'email' => $flash['email'] ?? '',
            'success' => $flash['success'] ?? null,
        ]);
    }

    public function login(): void
    {
        if (!Csrf::isValid($_POST['_csrf_token'] ?? null)) {
            http_response_code(419);
            $this->render('login', [
                'csrfToken' => Csrf::token(),
                'error' => 'La sesión expiró. Actualiza la página e inténtalo de nuevo.',
                'email' => '',
            ]);
            return;
        }

        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? null;
        if (!is_string($email) || !is_string($password) || $password === '') {
            $this->flashAndRedirect('/login', 'Revisa el correo y la contraseña.');
        }

        $usuario = (new Usuario())->buscarPorEmail($email);
        if (
            $usuario === null
            || !password_verify($password, (string) $usuario['password_hash'])
            || !$this->isActive($usuario['estado'] ?? null)
        ) {
            $this->flashAndRedirect('/login', 'Correo o contraseña incorrectos, o cuenta inactiva.', $email);
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $usuario['id'],
            'nombre' => (string) $usuario['nombre'],
            'email' => (string) $usuario['email'],
            'rol' => (string) $usuario['rol'],
        ];
        unset($_SESSION['_csrf_token']);

        $this->redirectForRole((string) $usuario['rol']);
    }

    public function showRegister(): void
    {
        if (isset($_SESSION['user'])) {
            $this->redirectForRole((string) ($_SESSION['user']['rol'] ?? ''));
        }

        $flash = $this->consumeFlash();
        $this->render('register', [
            'csrfToken' => Csrf::token(),
            'error' => $flash['error'] ?? null,
            'nombre' => $flash['nombre'] ?? '',
            'email' => $flash['email'] ?? '',
        ]);
    }

    public function register(): void
    {
        if (!Csrf::isValid($_POST['_csrf_token'] ?? null)) {
            http_response_code(419);
            $this->render('register', [
                'csrfToken' => Csrf::token(),
                'error' => 'La sesión expiró. Actualiza la página e inténtalo de nuevo.',
                'nombre' => '',
                'email' => '',
            ]);
            return;
        }

        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? null;
        $passwordConfirmation = $_POST['password_confirmation'] ?? null;

        if (
            $nombre === ''
            || strlen($nombre) > 120
            || !is_string($email)
            || !is_string($password)
            || strlen($password) < 8
            || !is_string($passwordConfirmation)
            || !hash_equals($password, $passwordConfirmation)
        ) {
            $this->flashAndRedirect(
                '/register',
                'Completa los datos correctamente. La contraseña debe tener al menos 8 caracteres y coincidir.',
                $email === false ? '' : $email,
                $nombre
            );
        }

        $usuarios = new Usuario();
        if ($usuarios->buscarPorEmail($email) !== null) {
            $this->flashAndRedirect('/register', 'Ya existe una cuenta con ese correo.', $email, $nombre);
        }

        try {
            $usuarios->crear($nombre, $email, password_hash($password, PASSWORD_DEFAULT));
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000' && $usuarios->buscarPorEmail($email) !== null) {
                $this->flashAndRedirect('/register', 'Ya existe una cuenta con ese correo.', $email, $nombre);
            }

            throw $exception;
        }

        $_SESSION['_flash'] = ['success' => 'Tu cuenta fue creada. Ya puedes iniciar sesión.'];
        $this->redirect('/login');
    }

    public function logout(): void
    {
        if (!Csrf::isValid($_POST['_csrf_token'] ?? null)) {
            http_response_code(419);
            echo 'La solicitud expiró. Actualiza la página e inténtalo de nuevo.';
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();

        $this->redirect('/login');
    }

    /** @param array<string, mixed> $data */
    private function render(string $view, array $data): void
    {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/' . $view . '.php';
    }

    private function redirectForRole(string $role): void
    {
        if (!in_array($role, ['administrador', 'bibliotecario', 'estudiante'], true)) {
            unset($_SESSION['user']);
            $this->redirect('/login');
        }

        $destinations = [
            'administrador' => '/admin',
            'bibliotecario' => '/bibliotecario',
            'estudiante' => '/home',
        ];
        $this->redirect($destinations[$role]);
    }

    private function redirect(string $path): void
    {
        header('Location: ' . Url::to($path));
        exit;
    }

    private function flashAndRedirect(
        string $path,
        string $error,
        string $email = '',
        string $nombre = ''
    ): void {
        $_SESSION['_flash'] = [
            'error' => $error,
            'email' => $email,
            'nombre' => $nombre,
        ];
        $this->redirect($path);
    }

    /** @return array<string, string> */
    private function consumeFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        return is_array($flash) ? $flash : [];
    }

    private function isActive(mixed $state): bool
    {
        return in_array(strtolower(trim((string) $state)), ['activo', '1', 'true', 'active'], true);
    }
}
