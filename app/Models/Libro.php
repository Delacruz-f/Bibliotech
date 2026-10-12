<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Libro
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @return array{0: string, 1: array<string, mixed>} */
    private function filtro(string $busqueda, int $categoriaId): array
    {
        $where = '(l.titulo LIKE :q1 OR l.autor LIKE :q2 OR l.isbn LIKE :q3)';
        $like = '%' . $busqueda . '%';
        $params = ['q1' => $like, 'q2' => $like, 'q3' => $like];
        if ($categoriaId > 0) {
            $where .= ' AND l.id_categoria = :cat';
            $params['cat'] = $categoriaId;
        }

        return [$where, $params];
    }

    public function contar(string $busqueda, int $categoriaId = 0): int
    {
        [$where, $params] = $this->filtro($busqueda, $categoriaId);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM libros l WHERE $where");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public function listar(string $busqueda, int $categoriaId, int $limite, int $offset): array
    {
        [$where, $params] = $this->filtro($busqueda, $categoriaId);
        $sql = "SELECT l.id, l.titulo, l.autor, l.isbn, l.stock_total, l.stock_disponible,
                       l.portada_url, c.nombre AS categoria
                FROM libros l
                LEFT JOIN categorias c ON c.id = l.id_categoria
                WHERE $where
                ORDER BY l.titulo
                LIMIT " . (int) $limite . ' OFFSET ' . (int) $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM libros WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    /**
     * Libros con al menos un ejemplar disponible (para registrar préstamos).
     *
     * @return list<array<string, mixed>>
     */
    public function disponibles(): array
    {
        return $this->db->query(
            'SELECT id, titulo, autor, stock_disponible FROM libros WHERE stock_disponible > 0 ORDER BY titulo'
        )->fetchAll();
    }

    public function isbnExiste(string $isbn, int $exceptoId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM libros WHERE isbn = :i AND id <> :id');
        $stmt->execute(['i' => $isbn, 'id' => $exceptoId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /** @param array<string, mixed> $d */
    public function crear(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO libros (titulo, autor, isbn, id_categoria, editorial, anio, portada_url, sinopsis, stock_total, stock_disponible)
             VALUES (:titulo, :autor, :isbn, :cat, :editorial, :anio, :portada, :sinopsis, :stock1, :stock2)'
        );
        $stmt->execute($this->params($d) + ['stock1' => $d['stock_total'], 'stock2' => $d['stock_total']]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Si cambia stock_total, stock_disponible se ajusta en la misma diferencia
     * (los ejemplares prestados no se tocan).
     *
     * @param array<string, mixed> $d
     */
    public function actualizar(int $id, array $d): void
    {
        $stmt = $this->db->prepare(
            'UPDATE libros SET titulo = :titulo, autor = :autor, isbn = :isbn, id_categoria = :cat,
                    editorial = :editorial, anio = :anio, portada_url = :portada, sinopsis = :sinopsis,
                    stock_disponible = stock_disponible + (:stock1 - stock_total),
                    stock_total = :stock2
             WHERE id = :id'
        );
        $stmt->execute($this->params($d) + ['stock1' => $d['stock_total'], 'stock2' => $d['stock_total'], 'id' => $id]);
    }

    /** @param array<string, mixed> $d @return array<string, mixed> */
    private function params(array $d): array
    {
        return [
            'titulo' => $d['titulo'],
            'autor' => $d['autor'],
            'isbn' => $d['isbn'],
            'cat' => $d['id_categoria'],
            'editorial' => $d['editorial'],
            'anio' => $d['anio'],
            'portada' => $d['portada_url'],
            'sinopsis' => $d['sinopsis'],
        ];
    }

    public function tieneMovimientos(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT (SELECT COUNT(*) FROM prestamos WHERE id_libro = :a) + (SELECT COUNT(*) FROM reservas WHERE id_libro = :b)'
        );
        $stmt->execute(['a' => $id, 'b' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function eliminar(int $id): void
    {
        $this->db->prepare('DELETE FROM libros WHERE id = :id')->execute(['id' => $id]);
    }
}
