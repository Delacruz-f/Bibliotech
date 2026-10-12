<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/** Consultas de gestión de usuarios (módulo Administrador). El login/registro sigue en Usuario. */
final class UsuarioAdmin
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    public function contar(string $busqueda): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuarios WHERE nombre LIKE :q1 OR email LIKE :q2');
        $like = '%' . $busqueda . '%';
        $stmt->execute(['q1' => $like, 'q2' => $like]);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public function listar(string $busqueda, int $limite, int $offset): array
    {
        $sql = 'SELECT id, nombre, email, rol, estado, created_at
                FROM usuarios
                WHERE nombre LIKE :q1 OR email LIKE :q2
                ORDER BY nombre
                LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset;
        $stmt = $this->db->prepare($sql);
        $like = '%' . $busqueda . '%';
        $stmt->execute(['q1' => $like, 'q2' => $like]);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nombre, email, rol, estado FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    /** @return list<array<string, mixed>> */
    public function activosPorRol(string $rol): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, nombre, email FROM usuarios WHERE rol = :r AND estado = 'activo' ORDER BY nombre"
        );
        $stmt->execute(['r' => $rol]);

        return $stmt->fetchAll();
    }

    public function emailExiste(string $email, int $exceptoId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuarios WHERE email = :e AND id <> :id');
        $stmt->execute(['e' => $email, 'id' => $exceptoId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function crear(string $nombre, string $email, string $passwordHash, string $rol, string $estado): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash, rol, estado) VALUES (:n, :e, :p, :r, :s)'
        );
        $stmt->execute(['n' => $nombre, 'e' => $email, 'p' => $passwordHash, 'r' => $rol, 's' => $estado]);

        return (int) $this->db->lastInsertId();
    }

    /** $passwordHash = null conserva la contraseña actual. */
    public function actualizar(int $id, string $nombre, string $email, string $rol, string $estado, ?string $passwordHash): void
    {
        $sql = 'UPDATE usuarios SET nombre = :n, email = :e, rol = :r, estado = :s';
        $params = ['n' => $nombre, 'e' => $email, 'r' => $rol, 's' => $estado, 'id' => $id];
        if ($passwordHash !== null) {
            $sql .= ', password_hash = :p';
            $params['p'] = $passwordHash;
        }
        $this->db->prepare($sql . ' WHERE id = :id')->execute($params);
    }

    public function tieneMovimientos(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT (SELECT COUNT(*) FROM prestamos WHERE id_usuario = :a) + (SELECT COUNT(*) FROM reservas WHERE id_usuario = :b)'
        );
        $stmt->execute(['a' => $id, 'b' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function eliminar(int $id): void
    {
        $this->db->prepare('DELETE FROM usuarios WHERE id = :id')->execute(['id' => $id]);
    }
}
