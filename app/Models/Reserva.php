<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Reserva
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::connection();
    }

    /** @return array<int, array<string, mixed>> */
    public function obtenerPorUsuario(int $id_usuario): array
    {
        $expire = $this->connection->prepare(
            'UPDATE reservas
             SET estado = :nuevo_estado
             WHERE id_usuario = :id_usuario
               AND LOWER(estado) = :estado_actual
               AND fecha_expiracion <= CURRENT_TIMESTAMP'
        );
        $expire->execute([
            'nuevo_estado' => 'expirada',
            'id_usuario' => $id_usuario,
            'estado_actual' => 'pendiente',
        ]);

        $statement = $this->connection->prepare(
            'SELECT r.id, r.id_usuario, r.id_libro, r.fecha_reserva,
                    r.fecha_expiracion, r.estado,
                    l.titulo AS libro_titulo, l.autor AS libro_autor
             FROM reservas r
             INNER JOIN libros l ON l.id = r.id_libro
             WHERE r.id_usuario = :id_usuario
             ORDER BY r.fecha_reserva DESC, r.id DESC'
        );
        $statement->execute(['id_usuario' => $id_usuario]);

        return $statement->fetchAll();
    }

    public function crear(int $id_usuario, int $id_libro): bool
    {
        $this->connection->beginTransaction();

        try {
            $book = $this->connection->prepare(
                'SELECT stock_disponible FROM libros WHERE id = :id_libro FOR UPDATE'
            );
            $book->execute(['id_libro' => $id_libro]);
            $stock = $book->fetchColumn();

            if ($stock === false || (int) $stock < 1) {
                $this->connection->rollBack();
                return false;
            }

            $expiry = (new \DateTimeImmutable('now'))
                ->modify('+48 hours')
                ->format('Y-m-d H:i:s');
            $reservation = $this->connection->prepare(
                'INSERT INTO reservas (id_usuario, id_libro, fecha_reserva, fecha_expiracion, estado)
                 VALUES (:id_usuario, :id_libro, :fecha_reserva, :fecha_expiracion, :estado)'
            );
            $reservation->execute([
                'id_usuario' => $id_usuario,
                'id_libro' => $id_libro,
                'fecha_reserva' => (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s'),
                'fecha_expiracion' => $expiry,
                'estado' => 'pendiente',
            ]);
            $this->connection->commit();

            return true;
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }

    public function cancelar(int $id_usuario, int $id_reserva): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE reservas
             SET estado = :nuevo_estado
             WHERE id = :id_reserva
               AND id_usuario = :id_usuario
               AND estado = :estado_actual'
        );
        $statement->execute([
            'nuevo_estado' => 'cancelada',
            'id_reserva' => $id_reserva,
            'id_usuario' => $id_usuario,
            'estado_actual' => 'pendiente',
        ]);

        return $statement->rowCount() > 0;
    }
}
