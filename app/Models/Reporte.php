<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/** Consultas de solo lectura para el Dashboard del administrador (RF23–RF27). */
final class Reporte
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @return array<string, int|float> */
    public function tarjetas(): array
    {
        $fila = $this->db->query(
            "SELECT
                (SELECT COUNT(*) FROM libros) AS libros,
                (SELECT COALESCE(SUM(stock_total), 0) FROM libros) AS ejemplares,
                (SELECT COALESCE(SUM(stock_disponible), 0) FROM libros) AS disponibles,
                (SELECT COUNT(*) FROM prestamos WHERE estado IN ('activo','vencido')) AS prestamos_activos,
                (SELECT COUNT(*) FROM prestamos WHERE estado = 'vencido') AS prestamos_vencidos,
                (SELECT COUNT(*) FROM reservas WHERE estado = 'pendiente') AS reservas_activas,
                (SELECT COUNT(*) FROM multas WHERE estado = 'pendiente') AS multas_pendientes,
                (SELECT COALESCE(SUM(monto), 0) FROM multas WHERE estado = 'pendiente') AS monto_pendiente,
                (SELECT COUNT(*) FROM usuarios WHERE rol = 'estudiante') AS estudiantes,
                (SELECT COUNT(*) FROM usuarios WHERE rol = 'bibliotecario') AS bibliotecarios,
                (SELECT COUNT(*) FROM prestamos
                  WHERE fecha_prestamo >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                    AND fecha_prestamo < DATE_FORMAT(CURDATE(), '%Y-%m-01') + INTERVAL 1 MONTH) AS prestamos_mes,
                (SELECT COALESCE(SUM(monto), 0) FROM multas
                  WHERE estado = 'pagada'
                    AND fecha_pago >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                    AND fecha_pago < DATE_FORMAT(CURDATE(), '%Y-%m-01') + INTERVAL 1 MONTH) AS cobrado_mes"
        )->fetch();

        return [
            'libros' => (int) $fila['libros'],
            'ejemplares' => (int) $fila['ejemplares'],
            'disponibles' => (int) $fila['disponibles'],
            'prestamos_activos' => (int) $fila['prestamos_activos'],
            'prestamos_vencidos' => (int) $fila['prestamos_vencidos'],
            'reservas_activas' => (int) $fila['reservas_activas'],
            'multas_pendientes' => (int) $fila['multas_pendientes'],
            'monto_pendiente' => (float) $fila['monto_pendiente'],
            'estudiantes' => (int) $fila['estudiantes'],
            'bibliotecarios' => (int) $fila['bibliotecarios'],
            'prestamos_mes' => (int) $fila['prestamos_mes'],
            'cobrado_mes' => (float) $fila['cobrado_mes'],
        ];
    }

    /**
     * Préstamos por mes de los últimos 6 meses (incluye meses en cero).
     *
     * @return list<array{clave: string, total: int}>
     */
    public function prestamosUltimosMeses(int $meses = 6): array
    {
        $stmt = $this->db->prepare(
            "SELECT DATE_FORMAT(fecha_prestamo, '%Y-%m') AS clave, COUNT(*) AS total
             FROM prestamos
             WHERE fecha_prestamo >= DATE_FORMAT(CURDATE() - INTERVAL :m MONTH, '%Y-%m-01')
             GROUP BY clave"
        );
        $stmt->execute(['m' => $meses - 1]);
        $conteo = [];
        foreach ($stmt->fetchAll() as $fila) {
            $conteo[$fila['clave']] = (int) $fila['total'];
        }

        $serie = [];
        $primerDia = new \DateTimeImmutable('first day of this month');
        for ($i = $meses - 1; $i >= 0; $i--) {
            $clave = $primerDia->modify("-$i month")->format('Y-m');
            $serie[] = ['clave' => $clave, 'total' => $conteo[$clave] ?? 0];
        }

        return $serie;
    }

    /** @return list<array<string, mixed>> */
    public function topLibros(int $limite = 5): array
    {
        return $this->db->query(
            'SELECT l.id, l.titulo, l.autor, COUNT(p.id) AS total
             FROM prestamos p JOIN libros l ON l.id = p.id_libro
             WHERE p.estado <> \'cancelado\'
             GROUP BY l.id, l.titulo, l.autor
             ORDER BY total DESC, l.titulo
             LIMIT ' . (int) $limite
        )->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function topEstudiantes(int $limite = 5): array
    {
        return $this->db->query(
            "SELECT u.id, u.nombre, u.email, COUNT(p.id) AS total
             FROM prestamos p JOIN usuarios u ON u.id = p.id_usuario
             WHERE p.estado <> 'cancelado' AND u.rol = 'estudiante'
             GROUP BY u.id, u.nombre, u.email
             ORDER BY total DESC, u.nombre
             LIMIT " . (int) $limite
        )->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function librosSinStock(): array
    {
        return $this->db->query(
            'SELECT id, titulo, autor, stock_total FROM libros WHERE stock_disponible = 0 ORDER BY titulo LIMIT 10'
        )->fetchAll();
    }

    /** @return list<array<string, mixed>> */
    public function reservasPorExpirar(): array
    {
        return $this->db->query(
            "SELECT r.id, r.fecha_expiracion, l.titulo AS libro, u.nombre AS usuario
             FROM reservas r
             JOIN libros l ON l.id = r.id_libro
             JOIN usuarios u ON u.id = r.id_usuario
             WHERE r.estado = 'pendiente'
               AND r.fecha_expiracion >= NOW()
               AND r.fecha_expiracion <= NOW() + INTERVAL 24 HOUR
             ORDER BY r.fecha_expiracion
             LIMIT 10"
        )->fetchAll();
    }
}
