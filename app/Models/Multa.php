<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Multa
{
    public const ESTADOS = ['pendiente', 'pagada', 'condonada'];

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
            $where .= ' AND m.estado = :estado';
            $params['estado'] = $estado;
        }

        return [$where, $params];
    }

    public function contar(string $estado, string $busqueda): int
    {
        [$where, $params] = $this->filtro($estado, $busqueda);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM multas m JOIN prestamos p ON p.id = m.id_prestamo
             JOIN libros l ON l.id = p.id_libro JOIN usuarios u ON u.id = p.id_usuario WHERE $where"
        );
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public function listar(string $estado, string $busqueda, int $limite, int $offset): array
    {
        [$where, $params] = $this->filtro($estado, $busqueda);
        $sql = "SELECT m.id, m.monto, m.estado, m.fecha_generacion, m.fecha_pago,
                       l.titulo AS libro, u.nombre AS usuario, u.email
                FROM multas m
                JOIN prestamos p ON p.id = m.id_prestamo
                JOIN libros l ON l.id = p.id_libro
                JOIN usuarios u ON u.id = p.id_usuario
                WHERE $where
                ORDER BY (m.estado = 'pendiente') DESC, m.fecha_generacion DESC, m.id DESC
                LIMIT " . (int) $limite . ' OFFSET ' . (int) $offset;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /** @return array{total: int, pendientes: int, pagadas: int, monto_pendiente: float} */
    public function resumen(): array
    {
        $fila = $this->db->query(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(estado = 'pendiente'), 0) AS pendientes,
                    COALESCE(SUM(estado = 'pagada'), 0) AS pagadas,
                    COALESCE(SUM(CASE WHEN estado = 'pendiente' THEN monto ELSE 0 END), 0) AS monto_pendiente
             FROM multas"
        )->fetch();

        return [
            'total' => (int) $fila['total'],
            'pendientes' => (int) $fila['pendientes'],
            'pagadas' => (int) $fila['pagadas'],
            'monto_pendiente' => (float) $fila['monto_pendiente'],
        ];
    }
}
