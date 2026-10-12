<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Libro
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::connection();
    }

    /**
     * @return array{libros: array<int, array<string, mixed>>, total: int, pagina: int, totalPaginas: int}
     */
    public function obtenerTodos(int $pagina = 1, int $porPagina = 9): array
    {
        $pagina = max(1, $pagina);
        $porPagina = max(1, min(48, $porPagina));

        $countStatement = $this->connection->prepare('SELECT COUNT(*) FROM libros');
        $countStatement->execute();
        $total = (int) $countStatement->fetchColumn();

        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $totalPaginas);
        $offset = ($pagina - 1) * $porPagina;

        $statement = $this->connection->prepare(
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
}
