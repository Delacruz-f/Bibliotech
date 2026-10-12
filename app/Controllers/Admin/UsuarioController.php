<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Flash;
use App\Core\Paginator;
use App\Models\UsuarioAdmin;
use PDOException;

final class UsuarioController extends BaseController
{
    public const ROLES = ['estudiante' => 'Estudiante', 'bibliotecario' => 'Bibliotecario', 'administrador' => 'Administrador'];
    public const ESTADOS = ['activo' => 'Activo', 'bloqueado' => 'Bloqueado'];

    private UsuarioAdmin $usuarios;

    public function __construct()
    {
        parent::__construct();
        $this->usuarios = new UsuarioAdmin();
    }

    public function index(): void
    {
        $q = $this->busqueda();
        $pag = new Paginator($this->usuarios->contar($q), self::POR_PAGINA, $this->pagina());

        $this->render('admin/usuarios/index', 'usuarios', 'Usuarios', [
            'filas' => $this->usuarios->listar($q, $pag->perPage, $pag->offset),
            'pag' => $pag,
            'q' => $q,
        ]);
    }

    public function crear(): void
    {
        $this->formulario(null, ['nombre' => '', 'email' => '', 'rol' => 'estudiante', 'estado' => 'activo']);
    }

    public function editar(): void
    {
        $usuario = $this->usuarios->buscar($this->idDe($_GET));
        if ($usuario === null) {
            Flash::error('El usuario no existe.');
            $this->redirigir('/admin/usuarios');
        }
        $this->formulario($usuario, $usuario);
    }

    public function guardar(): void
    {
        $this->validarCsrf('/admin/usuarios');
        $id = $this->idDe($_POST);
        $actual = $id > 0 ? $this->usuarios->buscar($id) : null;
        if ($id > 0 && $actual === null) {
            Flash::error('El usuario no existe.');
            $this->redirigir('/admin/usuarios');
        }

        $v = [
            'nombre' => $this->texto('nombre'),
            'email' => mb_strtolower($this->texto('email')),
            'rol' => $this->texto('rol'),
            'estado' => $this->texto('estado'),
        ];
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirmacion = is_string($_POST['password_confirmation'] ?? null) ? $_POST['password_confirmation'] : '';

        $error = $this->validar($v, $password, $confirmacion, $id);
        // Un administrador no puede quitarse a sí mismo el acceso al panel.
        if ($error === null && $id === (int) $this->usuario['id'] && ($v['rol'] !== 'administrador' || $v['estado'] !== 'activo')) {
            $error = 'No puedes cambiar tu propio rol ni bloquear tu propia cuenta.';
        }
        if ($error !== null) {
            http_response_code(422);
            $this->formulario($actual, $v, $error);

            return;
        }

        $hash = $password === '' ? null : password_hash($password, PASSWORD_DEFAULT);
        if ($id > 0) {
            $this->usuarios->actualizar($id, $v['nombre'], $v['email'], $v['rol'], $v['estado'], $hash);
            Flash::success('Usuario actualizado.');
        } else {
            $this->usuarios->crear($v['nombre'], $v['email'], (string) $hash, $v['rol'], $v['estado']);
            Flash::success('Usuario creado.');
        }
        $this->redirigir('/admin/usuarios');
    }

    public function eliminar(): void
    {
        $this->validarCsrf('/admin/usuarios');
        $id = $this->idDe($_POST);

        if ($this->usuarios->buscar($id) === null) {
            Flash::error('El usuario no existe.');
        } elseif ($id === (int) $this->usuario['id']) {
            Flash::error('No puedes eliminar tu propia cuenta.');
        } elseif ($this->usuarios->tieneMovimientos($id)) {
            Flash::error('No se puede eliminar: tiene préstamos o reservas registrados. Puedes bloquear la cuenta en su lugar.');
        } else {
            try {
                $this->usuarios->eliminar($id);
                Flash::success('Usuario eliminado.');
            } catch (PDOException) {
                Flash::error('No se pudo eliminar el usuario.');
            }
        }
        $this->redirigir('/admin/usuarios');
    }

    /** @param array<string, string> $v */
    private function validar(array $v, string $password, string $confirmacion, int $id): ?string
    {
        if ($v['nombre'] === '' || mb_strlen($v['nombre']) > 120) {
            return 'El nombre es obligatorio (máximo 120 caracteres).';
        }
        if (mb_strlen($v['email']) > 180 || filter_var($v['email'], FILTER_VALIDATE_EMAIL) === false) {
            return 'Ingresa un correo electrónico válido.';
        }
        if ($this->usuarios->emailExiste($v['email'], $id)) {
            return 'Ya existe un usuario con ese correo.';
        }
        if (!isset(self::ROLES[$v['rol']])) {
            return 'Selecciona un rol válido.';
        }
        if (!isset(self::ESTADOS[$v['estado']])) {
            return 'Selecciona un estado válido.';
        }
        if ($id === 0 && $password === '') {
            return 'La contraseña es obligatoria para crear un usuario.';
        }
        if ($password !== '' && (strlen($password) < 8 || strlen($password) > 72)) {
            return 'La contraseña debe tener entre 8 y 72 caracteres.';
        }
        if ($password !== $confirmacion) {
            return 'La confirmación de contraseña no coincide.';
        }

        return null;
    }

    /** @param array<string, mixed>|null $usuario @param array<string, mixed> $valores */
    private function formulario(?array $usuario, array $valores, ?string $error = null): void
    {
        $this->render('admin/usuarios/form', 'usuarios', $usuario ? 'Editar usuario' : 'Nuevo usuario', [
            'editado' => $usuario,
            'v' => $valores,
            'error' => $error,
            'roles' => self::ROLES,
            'estados' => self::ESTADOS,
            'esYo' => $usuario !== null && (int) $usuario['id'] === (int) $this->usuario['id'],
        ]);
    }
}
