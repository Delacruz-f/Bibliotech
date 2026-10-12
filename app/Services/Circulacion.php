<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use DomainException;
use PDO;
use Throwable;

/**
 * Reglas de circulación: préstamos, devoluciones, reservas y multas (RF13–RF22).
 * Es independiente del rol: el bibliotecario puede reutilizarla tal cual.
 * Todos los errores de negocio se lanzan como DomainException con mensaje listo para mostrar.
 */
final class Circulacion
{
    public const DIAS_PRESTAMO = 7;
    public const MULTA_POR_DIA = 1.50; // S/ 9.00 por 6 días de atraso (RF19)

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Marca préstamos vencidos y reservas expiradas. Se llama al abrir las pantallas de circulación. */
    public function actualizarEstados(): void
    {
        $this->db->exec(
            "UPDATE prestamos SET estado = 'vencido'
             WHERE estado = 'activo' AND fecha_devolucion_esperada < CURDATE()"
        );
        $this->db->exec(
            "UPDATE reservas SET estado = 'expirada'
             WHERE estado = 'pendiente' AND fecha_expiracion < NOW()"
        );
    }

    public static function calcularMulta(string $fechaEsperada, string $fechaReal): float
    {
        $dias = (int) (new \DateTimeImmutable($fechaEsperada))->diff(new \DateTimeImmutable($fechaReal))->format('%r%a');

        return $dias > 0 ? round($dias * self::MULTA_POR_DIA, 2) : 0.0;
    }

    /** RF14 + RF18: registra el préstamo y descuenta el stock. */
    public function registrarPrestamo(int $idUsuario, int $idLibro, int $dias = self::DIAS_PRESTAMO, ?int $idReserva = null): int
    {
        return $this->enTransaccion(function () use ($idUsuario, $idLibro, $dias, $idReserva): int {
            $usuario = $this->fila('SELECT id, estado FROM usuarios WHERE id = ? FOR UPDATE', [$idUsuario]);
            if ($usuario === null) {
                throw new DomainException('El usuario seleccionado no existe.');
            }
            if ($usuario['estado'] !== 'activo') {
                throw new DomainException('El usuario está bloqueado y no puede recibir préstamos.');
            }

            $libro = $this->fila('SELECT id, titulo, stock_disponible FROM libros WHERE id = ? FOR UPDATE', [$idLibro]);
            if ($libro === null) {
                throw new DomainException('El libro seleccionado no existe.');
            }
            if ((int) $libro['stock_disponible'] < 1) {
                throw new DomainException('No hay ejemplares disponibles de «' . $libro['titulo'] . '».');
            }

            $repetido = $this->fila(
                "SELECT id FROM prestamos WHERE id_usuario = ? AND id_libro = ? AND estado IN ('activo','vencido') LIMIT 1",
                [$idUsuario, $idLibro]
            );
            if ($repetido !== null) {
                throw new DomainException('El usuario ya tiene un préstamo sin devolver de este libro.');
            }

            $dias = max(1, min(60, $dias));
            $stmt = $this->db->prepare(
                'INSERT INTO prestamos (id_usuario, id_libro, id_reserva, fecha_prestamo, fecha_devolucion_esperada, estado)
                 VALUES (?, ?, ?, CURDATE(), CURDATE() + INTERVAL ' . $dias . " DAY, 'activo')"
            );
            $stmt->execute([$idUsuario, $idLibro, $idReserva]);
            $idPrestamo = (int) $this->db->lastInsertId();

            $this->db->prepare('UPDATE libros SET stock_disponible = stock_disponible - 1 WHERE id = ?')->execute([$idLibro]);

            return $idPrestamo;
        });
    }

    /**
     * RF16 + RF18 + RF19: registra la devolución, repone stock y genera la multa si hubo atraso.
     *
     * @return float monto de la multa generada (0.0 si fue puntual)
     */
    public function registrarDevolucion(int $idPrestamo): float
    {
        return $this->enTransaccion(function () use ($idPrestamo): float {
            $prestamo = $this->fila(
                'SELECT id, id_libro, estado, fecha_devolucion_esperada, CURDATE() AS hoy FROM prestamos WHERE id = ? FOR UPDATE',
                [$idPrestamo]
            );
            if ($prestamo === null) {
                throw new DomainException('El préstamo no existe.');
            }
            if (!in_array($prestamo['estado'], ['activo', 'vencido'], true)) {
                throw new DomainException('Este préstamo ya fue cerrado.');
            }

            $this->db->prepare("UPDATE prestamos SET estado = 'devuelto', fecha_devolucion_real = CURDATE() WHERE id = ?")
                ->execute([$idPrestamo]);
            $this->db->prepare(
                'UPDATE libros SET stock_disponible = LEAST(stock_total, stock_disponible + 1) WHERE id = ?'
            )->execute([(int) $prestamo['id_libro']]);

            $monto = self::calcularMulta((string) $prestamo['fecha_devolucion_esperada'], (string) $prestamo['hoy']);
            if ($monto > 0) {
                $this->db->prepare(
                    "INSERT INTO multas (id_prestamo, monto, estado, fecha_generacion) VALUES (?, ?, 'pendiente', NOW())"
                )->execute([$idPrestamo, $monto]);
            }

            return $monto;
        });
    }

    /** RF17: el bibliotecario confirma la entrega de una reserva y se genera el préstamo. */
    public function confirmarReserva(int $idReserva): int
    {
        return $this->enTransaccion(function () use ($idReserva): int {
            $reserva = $this->fila(
                'SELECT id, id_usuario, id_libro, estado, fecha_expiracion < NOW() AS vencida FROM reservas WHERE id = ? FOR UPDATE',
                [$idReserva]
            );
            if ($reserva === null) {
                throw new DomainException('La reserva no existe.');
            }
            if ($reserva['estado'] !== 'pendiente') {
                throw new DomainException('Solo se pueden confirmar reservas pendientes.');
            }
            if ((int) $reserva['vencida'] === 1) {
                throw new DomainException('La reserva ya expiró.');
            }

            // Reutiliza la lógica (y los bloqueos) del préstamo directo: es una transacción anidada lógica.
            $idPrestamo = $this->prestamoDesdeReserva((int) $reserva['id_usuario'], (int) $reserva['id_libro'], $idReserva);
            $this->db->prepare("UPDATE reservas SET estado = 'confirmada' WHERE id = ?")->execute([$idReserva]);

            return $idPrestamo;
        });
    }

    public function cancelarReserva(int $idReserva): void
    {
        $stmt = $this->db->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = ? AND estado = 'pendiente'");
        $stmt->execute([$idReserva]);
        if ($stmt->rowCount() === 0) {
            throw new DomainException('Solo se pueden cancelar reservas pendientes.');
        }
    }

    /** RF21: registra el pago de una multa. */
    public function pagarMulta(int $idMulta): void
    {
        $this->cerrarMulta($idMulta, 'pagada', 'fecha_pago = NOW()');
    }

    public function condonarMulta(int $idMulta): void
    {
        $this->cerrarMulta($idMulta, 'condonada', 'fecha_pago = NULL');
    }

    private function cerrarMulta(int $idMulta, string $estado, string $extra): void
    {
        $stmt = $this->db->prepare("UPDATE multas SET estado = ?, $extra WHERE id = ? AND estado = 'pendiente'");
        $stmt->execute([$estado, $idMulta]);
        if ($stmt->rowCount() === 0) {
            throw new DomainException('La multa no existe o ya no está pendiente.');
        }
    }

    private function prestamoDesdeReserva(int $idUsuario, int $idLibro, int $idReserva): int
    {
        // Misma validación que registrarPrestamo, pero sin abrir otra transacción.
        $libro = $this->fila('SELECT titulo, stock_disponible FROM libros WHERE id = ? FOR UPDATE', [$idLibro]);
        if ($libro === null || (int) $libro['stock_disponible'] < 1) {
            throw new DomainException('No hay ejemplares disponibles para entregar esta reserva.');
        }
        $usuario = $this->fila('SELECT estado FROM usuarios WHERE id = ?', [$idUsuario]);
        if ($usuario === null || $usuario['estado'] !== 'activo') {
            throw new DomainException('El usuario de la reserva está bloqueado o ya no existe.');
        }

        $this->db->prepare(
            "INSERT INTO prestamos (id_usuario, id_libro, id_reserva, fecha_prestamo, fecha_devolucion_esperada, estado)
             VALUES (?, ?, ?, CURDATE(), CURDATE() + INTERVAL " . self::DIAS_PRESTAMO . " DAY, 'activo')"
        )->execute([$idUsuario, $idLibro, $idReserva]);
        $idPrestamo = (int) $this->db->lastInsertId();
        $this->db->prepare('UPDATE libros SET stock_disponible = stock_disponible - 1 WHERE id = ?')->execute([$idLibro]);

        return $idPrestamo;
    }

    /**
     * @template T
     * @param callable(): T $accion
     * @return T
     */
    private function enTransaccion(callable $accion): mixed
    {
        $this->db->beginTransaction();
        try {
            $resultado = $accion();
            $this->db->commit();

            return $resultado;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** @param list<mixed> $params @return array<string, mixed>|null */
    private function fila(string $sql, array $params): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }
}
