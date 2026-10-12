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

    /** @return array<int, array<string, mixed>> */
    public function obtenerPorUsuario(int $id_usuario): array
    {
        $statement = $this->db->prepare(
            'SELECT m.id, m.id_prestamo, m.monto, m.estado,
                    m.fecha_generacion, m.fecha_pago,
                    p.id_libro, l.titulo AS libro_titulo
             FROM multas m
             INNER JOIN prestamos p ON p.id = m.id_prestamo
             INNER JOIN libros l ON l.id = p.id_libro
             WHERE p.id_usuario = :id_usuario
             ORDER BY m.fecha_generacion DESC, m.id DESC'
        );
        $statement->execute(['id_usuario' => $id_usuario]);

        return $statement->fetchAll();
    }

    public function pagar(int $id_multa, int $id_usuario): bool
    {
        $statement = $this->db->prepare(
            'UPDATE multas
             SET estado = :estado_pagada, fecha_pago = NOW()
             WHERE id = :id_multa
               AND estado = :estado_pendiente
               AND EXISTS (
                   SELECT 1
                   FROM prestamos p
                   WHERE p.id = multas.id_prestamo
                     AND p.id_usuario = :id_usuario
               )'
        );
        $statement->execute([
            'estado_pagada' => 'pagada',
            'id_multa' => $id_multa,
            'estado_pendiente' => 'pendiente',
            'id_usuario' => $id_usuario,
        ]);

        return $statement->rowCount() === 1;
    }

    /** @return array{0: string, 1: array<string, mixed>} */
    private function filtro(string $estado, string $busqueda): array
    {
        $like = '%' . $busqueda . '%';
        $where = '(l.titulo LIKE :titulo OR u.nombre LIKE :nombre OR u.email LIKE :email)';
        $params = ['titulo' => $like, 'nombre' => $like, 'email' => $like];
        if (in_array($estado, self::ESTADOS, true)) {
            $where .= ' AND m.estado = :estado';
            $params['estado'] = $estado;
        }

        return [$where, $params];
    }

    public function contar(string $estado, string $busqueda): int
    {
        [$where, $params] = $this->filtro($estado, $busqueda);
        $statement = $this->db->prepare(
            "SELECT COUNT(*)
             FROM multas m
             INNER JOIN prestamos p ON p.id = m.id_prestamo
             INNER JOIN libros l ON l.id = p.id_libro
             INNER JOIN usuarios u ON u.id = p.id_usuario
             WHERE $where"
        );
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    /** @return list<array<string, mixed>> */
    public function listar(string $estado, string $busqueda, int $limite, int $offset): array
    {
        [$where, $params] = $this->filtro($estado, $busqueda);
        $statement = $this->db->prepare(
            "SELECT m.id, m.monto, m.estado, m.fecha_generacion, m.fecha_pago,
                    l.titulo AS libro, u.nombre AS usuario, u.email
             FROM multas m
             INNER JOIN prestamos p ON p.id = m.id_prestamo
             INNER JOIN libros l ON l.id = p.id_libro
             INNER JOIN usuarios u ON u.id = p.id_usuario
             WHERE $where
             ORDER BY (m.estado = 'pendiente') DESC, m.fecha_generacion DESC, m.id DESC
             LIMIT :limite OFFSET :desplazamiento"
        );
        foreach ($params as $name => $value) {
            $statement->bindValue(':' . $name, $value);
        }
        $statement->bindValue(':limite', max(1, $limite), PDO::PARAM_INT);
        $statement->bindValue(':desplazamiento', max(0, $offset), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /** @return array{total: int, pendientes: int, pagadas: int, monto_pendiente: float} */
    public function resumen(): array
    {
        $statement = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(estado = :estado_pendiente), 0) AS pendientes,
                    COALESCE(SUM(estado = :estado_pagada), 0) AS pagadas,
                    COALESCE(
                        SUM(CASE WHEN estado = :estado_monto THEN monto ELSE 0 END),
                        0
                    ) AS monto_pendiente
             FROM multas"
        );
        $statement->execute([
            'estado_pendiente' => 'pendiente',
            'estado_pagada' => 'pagada',
            'estado_monto' => 'pendiente',
        ]);
        $row = $statement->fetch();

        return [
            'total' => (int) $row['total'],
            'pendientes' => (int) $row['pendientes'],
            'pagadas' => (int) $row['pagadas'],
            'monto_pendiente' => (float) $row['monto_pendiente'],
        ];
    }
}
