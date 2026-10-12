<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Categoria
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @return list<array<string, mixed>> */
    public function todas(): array
    {
        return $this->db->query('SELECT id, nombre FROM categorias ORDER BY nombre')->fetchAll();
    }

    public function contar(string $busqueda): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM categorias WHERE nombre LIKE :q1 OR descripcion LIKE :q2');
        $like = '%' . $busqueda . '%';
        $stmt->execute(['q1' => $like, 'q2' => $like]);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public function listar(string $busqueda, int $limite, int $offset): array
    {
        $sql = 'SELECT c.id, c.nombre, c.descripcion, COUNT(l.id) AS total_libros
                FROM categorias c
                LEFT JOIN libros l ON l.id_categoria = c.id
                WHERE c.nombre LIKE :q1 OR c.descripcion LIKE :q2
                GROUP BY c.id, c.nombre, c.descripcion
                ORDER BY c.nombre
                LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset;
        $stmt = $this->db->prepare($sql);
        $like = '%' . $busqueda . '%';
        $stmt->execute(['q1' => $like, 'q2' => $like]);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nombre, descripcion FROM categorias WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    public function nombreExiste(string $nombre, int $exceptoId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM categorias WHERE nombre = :n AND id <> :id');
        $stmt->execute(['n' => $nombre, 'id' => $exceptoId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function crear(string $nombre, ?string $descripcion): int
    {
        $stmt = $this->db->prepare('INSERT INTO categorias (nombre, descripcion) VALUES (:n, :d)');
        $stmt->execute(['n' => $nombre, 'd' => $descripcion]);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, string $nombre, ?string $descripcion): void
    {
        $stmt = $this->db->prepare('UPDATE categorias SET nombre = :n, descripcion = :d WHERE id = :id');
        $stmt->execute(['n' => $nombre, 'd' => $descripcion, 'id' => $id]);
    }

    public function contarLibros(int $id): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM libros WHERE id_categoria = :id');
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn();
    }

    public function eliminar(int $id): void
    {
        $this->db->prepare('DELETE FROM categorias WHERE id = :id')->execute(['id' => $id]);
    }
}
