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

    /** @return array<int, array<string, mixed>> */
    public function obtenerPorUsuario(int $id_usuario): array
    {
        $expire = $this->db->prepare(
            'UPDATE reservas
             SET estado = :nuevo_estado
             WHERE id_usuario = :id_usuario
               AND estado = :estado_actual
               AND fecha_expiracion <= CURRENT_TIMESTAMP'
        );
        $expire->execute([
            'nuevo_estado' => 'expirada',
            'id_usuario' => $id_usuario,
            'estado_actual' => 'pendiente',
        ]);

        $statement = $this->db->prepare(
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
        $this->db->beginTransaction();

        try {
            $book = $this->db->prepare(
                'SELECT stock_disponible FROM libros WHERE id = :id_libro FOR UPDATE'
            );
            $book->execute(['id_libro' => $id_libro]);
            $stock = $book->fetchColumn();

            if ($stock === false || (int) $stock < 1) {
                $this->db->rollBack();
                return false;
            }

            $reservation = $this->db->prepare(
                'INSERT INTO reservas
                    (id_usuario, id_libro, fecha_reserva, fecha_expiracion, estado)
                 VALUES
                    (:id_usuario, :id_libro, NOW(), DATE_ADD(NOW(), INTERVAL 48 HOUR), :estado)'
            );
            $reservation->execute([
                'id_usuario' => $id_usuario,
                'id_libro' => $id_libro,
                'estado' => 'pendiente',
            ]);
            $this->db->commit();

            return true;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }

    public function cancelar(int $id_usuario, int $id_reserva): bool
    {
        $statement = $this->db->prepare(
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

    /** @return array{0: string, 1: array<string, mixed>} */
    private function filtro(string $estado, string $busqueda): array
    {
        $like = '%' . $busqueda . '%';
        $where = '(l.titulo LIKE :titulo OR u.nombre LIKE :nombre OR u.email LIKE :email)';
        $params = ['titulo' => $like, 'nombre' => $like, 'email' => $like];
        if (in_array($estado, self::ESTADOS, true)) {
            $where .= ' AND r.estado = :estado';
            $params['estado'] = $estado;
        }

        return [$where, $params];
    }

    public function contar(string $estado, string $busqueda): int
    {
        [$where, $params] = $this->filtro($estado, $busqueda);
        $statement = $this->db->prepare(
            "SELECT COUNT(*)
             FROM reservas r
             INNER JOIN libros l ON l.id = r.id_libro
             INNER JOIN usuarios u ON u.id = r.id_usuario
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
            "SELECT r.id, r.fecha_reserva, r.fecha_expiracion, r.estado,
                    l.titulo AS libro, l.stock_disponible,
                    u.nombre AS usuario, u.email
             FROM reservas r
             INNER JOIN libros l ON l.id = r.id_libro
             INNER JOIN usuarios u ON u.id = r.id_usuario
             WHERE $where
             ORDER BY (r.estado = 'pendiente') DESC, r.fecha_expiracion ASC, r.id DESC
             LIMIT :limite OFFSET :desplazamiento"
        );
        $statement->bindValue(':titulo', '%' . $busqueda . '%');
        $statement->bindValue(':nombre', '%' . $busqueda . '%');
        $statement->bindValue(':email', '%' . $busqueda . '%');
        if (in_array($estado, self::ESTADOS, true)) {
            $statement->bindValue(':estado', $estado);
        }
        $statement->bindValue(':limite', max(1, $limite), PDO::PARAM_INT);
        $statement->bindValue(':desplazamiento', max(0, $offset), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
