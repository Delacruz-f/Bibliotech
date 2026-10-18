<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

final class Configuracion
{
    /** @var array<string, string> */
    public const VALORES_POR_DEFECTO = [
        'dias_prestamo' => '14',
        'max_prestamos_activos' => '3',
        'max_reservas_activas' => '3',
        'horas_vigencia_reserva' => '48',
        'mora_por_dia' => '0.20',
        'dias_gracia' => '0',
        'bloquear_multas_pendientes' => '1',
    ];

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** @return array<string, string> */
    public function obtenerTodas(): array
    {
        $configuracion = self::VALORES_POR_DEFECTO;
        $filas = $this->db->query('SELECT clave, valor FROM configuraciones')->fetchAll();
        foreach ($filas as $fila) {
            $clave = (string) $fila['clave'];
            if (array_key_exists($clave, self::VALORES_POR_DEFECTO)) {
                $configuracion[$clave] = (string) $fila['valor'];
            }
        }

        return $configuracion;
    }

    /** @param array<string, string> $datos */
    public function actualizarTodas(array $datos): void
    {
        $claves = array_keys(self::VALORES_POR_DEFECTO);
        if (array_diff($claves, array_keys($datos)) !== []) {
            throw new \InvalidArgumentException('Faltan valores de configuración requeridos.');
        }

        $statement = $this->db->prepare(
            'INSERT INTO configuraciones (clave, valor)
             VALUES (:clave, :valor)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)'
        );

        $this->db->beginTransaction();
        try {
            foreach ($claves as $clave) {
                $statement->execute(['clave' => $clave, 'valor' => $datos[$clave]]);
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }
}
