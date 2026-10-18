USE bdbibliotech;

CREATE TABLE IF NOT EXISTS configuraciones (
    clave      VARCHAR(100) NOT NULL,
    valor      VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuraciones (clave, valor) VALUES
    ('dias_prestamo', '14'),
    ('max_prestamos_activos', '3'),
    ('max_reservas_activas', '3'),
    ('horas_vigencia_reserva', '48'),
    ('mora_por_dia', '0.20'),
    ('dias_gracia', '0'),
    ('bloquear_multas_pendientes', '1');
