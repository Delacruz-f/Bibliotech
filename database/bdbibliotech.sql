-- BiblioTech · esquema de la base de datos bdbibliotech (MySQL / MariaDB)
-- Coincide con el diagrama de tablas del proyecto. Es seguro ejecutarlo sobre una BD existente
-- (usa IF NOT EXISTS): no borra ni modifica datos.

CREATE DATABASE IF NOT EXISTS bdbibliotech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bdbibliotech;

CREATE TABLE IF NOT EXISTS categorias (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categorias_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(120) NOT NULL,
    email         VARCHAR(180) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol           ENUM('estudiante','bibliotecario','administrador') NOT NULL DEFAULT 'estudiante',
    estado        ENUM('activo','bloqueado') NOT NULL DEFAULT 'activo',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS libros (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo           VARCHAR(255) NOT NULL,
    autor            VARCHAR(180) NOT NULL,
    isbn             VARCHAR(20) NOT NULL,
    id_categoria     BIGINT UNSIGNED NOT NULL,
    editorial        VARCHAR(150) NULL,
    anio             SMALLINT UNSIGNED NULL,
    portada_url      VARCHAR(500) NULL,
    sinopsis         TEXT NULL,
    stock_total      INT UNSIGNED NOT NULL DEFAULT 1,
    stock_disponible INT UNSIGNED NOT NULL DEFAULT 1,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_libros_isbn (isbn),
    KEY idx_libros_categoria (id_categoria),
    CONSTRAINT fk_libros_categoria FOREIGN KEY (id_categoria) REFERENCES categorias (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reservas (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario       BIGINT UNSIGNED NOT NULL,
    id_libro         BIGINT UNSIGNED NOT NULL,
    fecha_reserva    DATETIME NOT NULL,
    fecha_expiracion DATETIME NOT NULL,
    estado           ENUM('pendiente','confirmada','expirada','cancelada') NOT NULL DEFAULT 'pendiente',
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_reservas_usuario (id_usuario),
    KEY idx_reservas_libro (id_libro),
    CONSTRAINT fk_reservas_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id),
    CONSTRAINT fk_reservas_libro FOREIGN KEY (id_libro) REFERENCES libros (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prestamos (
    id                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario               BIGINT UNSIGNED NOT NULL,
    id_libro                 BIGINT UNSIGNED NOT NULL,
    id_reserva               BIGINT UNSIGNED NULL,
    fecha_prestamo           DATE NOT NULL,
    fecha_devolucion_esperada DATE NOT NULL,
    fecha_devolucion_real    DATE NULL,
    estado                   ENUM('activo','devuelto','vencido','cancelado') NOT NULL DEFAULT 'activo',
    created_at               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_prestamos_usuario (id_usuario),
    KEY idx_prestamos_libro (id_libro),
    KEY idx_prestamos_reserva (id_reserva),
    CONSTRAINT fk_prestamos_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id),
    CONSTRAINT fk_prestamos_libro FOREIGN KEY (id_libro) REFERENCES libros (id),
    CONSTRAINT fk_prestamos_reserva FOREIGN KEY (id_reserva) REFERENCES reservas (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS multas (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_prestamo      BIGINT UNSIGNED NOT NULL,
    monto            DECIMAL(10,2) NOT NULL,
    estado           ENUM('pendiente','pagada','condonada') NOT NULL DEFAULT 'pendiente',
    fecha_generacion DATETIME NOT NULL,
    fecha_pago       DATETIME NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_multas_prestamo (id_prestamo),
    CONSTRAINT fk_multas_prestamo FOREIGN KEY (id_prestamo) REFERENCES prestamos (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
