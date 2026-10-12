<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Prestamo
{
    public const ESTADOS = ['activo', 'vencido', 'devuelto', 'cancelado'];

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @return array{0: string, 1: array<string, mixed>} */
    private function filtro(string $estado, string $busqueda): array
    {
        $like = '%' . $busqueda . '%';
        $where = '(l.titulo LIKE :q1 OR u.nombre LIKE :q2 OR u.email LIKE :q3)';
        $params = ['q1' => $like, 'q2' => $like, 'q3' => $like];
        if (in_array($estado, self::ESTADOS, true)) {
            $where .= ' AND p.estado = :estado';
            $params['estado'] = $estado;
        }

        return [$where, $params];
    }

    public function contar(string $estado, string $busqueda): int
    {
        [$where, $params] = $this->filtro($estado, $busqueda);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM prestamos p JOIN libros l ON l.id = p.id_libro JOIN usuarios u ON u.id = p.id_usuario WHERE $where"
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public function listar(string $estado, string $busqueda, int $limite, int $offset): array
    {
        [$where, $params] = $this->filtro($estado, $busqueda);
        $sql = "SELECT p.id, p.fecha_prestamo, p.fecha_devolucion_esperada, p.fecha_devolucion_real, p.estado,
                       l.titulo AS libro, u.nombre AS usuario, u.email,
                       (SELECT COUNT(*) FROM multas m WHERE m.id_prestamo = p.id) AS tiene_multa
                FROM prestamos p
                JOIN libros l ON l.id = p.id_libro
                JOIN usuarios u ON u.id = p.id_usuario
                WHERE $where
                ORDER BY (p.estado IN ('activo','vencido')) DESC, p.fecha_prestamo DESC, p.id DESC
                LIMIT " . (int) $limite . ' OFFSET ' . (int) $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
