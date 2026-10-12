<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Reserva
{
    public const ESTADOS = ['pendiente', 'confirmada', 'expirada', 'cancelada'];

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
            $where .= ' AND r.estado = :estado';
            $params['estado'] = $estado;
        }

        return [$where, $params];
    }

    public function contar(string $estado, string $busqueda): int
    {
        [$where, $params] = $this->filtro($estado, $busqueda);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM reservas r JOIN libros l ON l.id = r.id_libro JOIN usuarios u ON u.id = r.id_usuario WHERE $where"
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public function listar(string $estado, string $busqueda, int $limite, int $offset): array
    {
        [$where, $params] = $this->filtro($estado, $busqueda);
        $sql = "SELECT r.id, r.fecha_reserva, r.fecha_expiracion, r.estado,
                       l.titulo AS libro, l.stock_disponible, u.nombre AS usuario, u.email
                FROM reservas r
                JOIN libros l ON l.id = r.id_libro
                JOIN usuarios u ON u.id = r.id_usuario
                WHERE $where
                ORDER BY (r.estado = 'pendiente') DESC, r.fecha_expiracion ASC, r.id DESC
                LIMIT " . (int) $limite . ' OFFSET ' . (int) $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
