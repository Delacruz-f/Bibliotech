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

    /**
     * @return array{libros: array<int, array<string, mixed>>, total: int, pagina: int, totalPaginas: int}
     */
    public function obtenerTodos(int $pagina = 1, int $porPagina = 9): array
    {
        $pagina = max(1, $pagina);
        $porPagina = max(1, min(48, $porPagina));

        $count = $this->db->prepare('SELECT COUNT(*) FROM libros');
        $count->execute();
        $total = (int) $count->fetchColumn();
        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;

        $statement = $this->db->prepare(
            'SELECT l.id, l.titulo, l.autor, l.isbn, l.id_categoria, l.editorial,
                    l.anio, l.portada_url, l.sinopsis, l.stock_total, l.stock_disponible,
                    c.nombre AS categoria_nombre
             FROM libros l
             LEFT JOIN categorias c ON c.id = l.id_categoria
             ORDER BY l.id DESC
             LIMIT :limite OFFSET :desplazamiento'
        );
        $statement->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $statement->bindValue(':desplazamiento', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'libros' => $statement->fetchAll(),
            'total' => $total,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
        ];
    }

    /** @return array{0: string, 1: array<string, mixed>} */
    private function filtro(string $busqueda, int $categoriaId): array
    {
        $where = '(l.titulo LIKE :q1 OR l.autor LIKE :q2 OR l.isbn LIKE :q3)';
        $like = '%' . $busqueda . '%';
        $params = ['q1' => $like, 'q2' => $like, 'q3' => $like];
        if ($categoriaId > 0) {
            $where .= ' AND l.id_categoria = :categoria_id';
            $params['categoria_id'] = $categoriaId;
        }

        return [$where, $params];
    }

    public function contar(string $busqueda, int $categoriaId = 0): int
    {
        [$where, $params] = $this->filtro($busqueda, $categoriaId);
        $statement = $this->db->prepare("SELECT COUNT(*) FROM libros l WHERE $where");
        $statement->execute($params);

        return (int) $statement->fetchColumn();
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
                LIMIT " . max(1, $limite) . ' OFFSET ' . max(0, $offset);
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function buscar(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM libros WHERE id = :id');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    public function disponibles(): array
    {
        $statement = $this->db->prepare(
            'SELECT id, titulo, autor, stock_disponible
             FROM libros
             WHERE stock_disponible > :stock_minimo
             ORDER BY titulo'
        );
        $statement->execute(['stock_minimo' => 0]);

        return $statement->fetchAll();
    }

    public function isbnExiste(string $isbn, int $exceptoId = 0): bool
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) FROM libros WHERE isbn = :isbn AND id <> :id'
        );
        $statement->execute(['isbn' => $isbn, 'id' => $exceptoId]);

        return (int) $statement->fetchColumn() > 0;
    }

    /** @param array<string, mixed> $data */
    public function crear(array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO libros
                (titulo, autor, isbn, id_categoria, editorial, anio, portada_url, sinopsis, stock_total, stock_disponible)
             VALUES
                (:titulo, :autor, :isbn, :categoria, :editorial, :anio, :portada_url, :sinopsis, :stock_total, :stock_disponible)'
        );
        $statement->execute($this->params($data) + [
            'stock_total' => $data['stock_total'],
            'stock_disponible' => $data['stock_total'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Ajusta el stock disponible por la diferencia de stock total, sin alterar
     * los ejemplares que actualmente están prestados.
     *
     * @param array<string, mixed> $data
     */
    public function actualizar(int $id, array $data): void
    {
        $statement = $this->db->prepare(
            'UPDATE libros
             SET titulo = :titulo,
                 autor = :autor,
                 isbn = :isbn,
                 id_categoria = :categoria,
                 editorial = :editorial,
                 anio = :anio,
                 portada_url = :portada_url,
                 sinopsis = :sinopsis,
                 stock_disponible = stock_disponible + (:stock_nuevo - stock_total),
                 stock_total = :stock_total
             WHERE id = :id'
        );
        $statement->execute($this->params($data) + [
            'stock_nuevo' => $data['stock_total'],
            'stock_total' => $data['stock_total'],
            'id' => $id,
        ]);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    private function params(array $data): array
    {
        return [
            'titulo' => $data['titulo'],
            'autor' => $data['autor'],
            'isbn' => $data['isbn'],
            'categoria' => $data['id_categoria'],
            'editorial' => $data['editorial'],
            'anio' => $data['anio'],
            'portada_url' => $data['portada_url'],
            'sinopsis' => $data['sinopsis'],
        ];
    }

    public function tieneMovimientos(int $id): bool
    {
        $statement = $this->db->prepare(
            'SELECT
                (SELECT COUNT(*) FROM prestamos WHERE id_libro = :id_prestamo)
                + (SELECT COUNT(*) FROM reservas WHERE id_libro = :id_reserva)'
        );
        $statement->execute(['id_prestamo' => $id, 'id_reserva' => $id]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function eliminar(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM libros WHERE id = :id');
        $statement->execute(['id' => $id]);
    }
}
