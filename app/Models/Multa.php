<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Multa
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::connection();
    }

    /** @return array<int, array<string, mixed>> */
    public function obtenerPorUsuario(int $id_usuario): array
    {
        $statement = $this->connection->prepare(
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
        $statement = $this->connection->prepare(
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
}
