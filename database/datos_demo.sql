-- BiblioTech · datos de DEMOSTRACIÓN (solo para desarrollo, sobre una BD vacía).
-- Todos los usuarios usan la contraseña: Admin12345
-- Las fechas son relativas a la fecha en que se ejecuta el script.
SET NAMES utf8mb4;
USE bdbibliotech;

INSERT INTO categorias (id, nombre, descripcion) VALUES
 (1, 'Bases de Datos', 'Modelado, SQL y administración de datos'),
 (2, 'Historia', 'Historia universal y del Perú'),
 (3, 'Literatura', 'Novela, cuento y poesía'),
 (4, 'Programación', 'Lenguajes y desarrollo de software'),
 (5, 'Ciencias', 'Matemática, física y biología');

INSERT INTO usuarios (id, nombre, email, password_hash, rol, estado) VALUES
 (1, 'Administrador BiblioTech', 'admin@bibliotech.test',  '$2y$10$B9i49XWAIqWLS5ACEBew1uavxWf6P3DlvU8rtYvtfagb2qiWoqHlS', 'administrador', 'activo'),
 (2, 'Bibliotecaria Demo',       'biblio@bibliotech.test', '$2y$10$B9i49XWAIqWLS5ACEBew1uavxWf6P3DlvU8rtYvtfagb2qiWoqHlS', 'bibliotecario', 'activo'),
 (3, 'Ana Torres',               'ana@bibliotech.test',    '$2y$10$B9i49XWAIqWLS5ACEBew1uavxWf6P3DlvU8rtYvtfagb2qiWoqHlS', 'estudiante',    'activo'),
 (4, 'Luis Quispe',              'luis@bibliotech.test',   '$2y$10$B9i49XWAIqWLS5ACEBew1uavxWf6P3DlvU8rtYvtfagb2qiWoqHlS', 'estudiante',    'activo'),
 (5, 'Carla Mendoza',            'carla@bibliotech.test',  '$2y$10$B9i49XWAIqWLS5ACEBew1uavxWf6P3DlvU8rtYvtfagb2qiWoqHlS', 'estudiante',    'activo');

INSERT INTO libros (id, titulo, autor, isbn, id_categoria, editorial, anio, sinopsis, stock_total, stock_disponible) VALUES
 (1,  'Fundamentos de Bases de Datos', 'Silberschatz, Korth', '9780078022159', 1, 'McGraw-Hill', 2019, 'Texto clásico de modelado relacional y SQL.', 8, 8),
 (2,  'SQL para Mortales',             'Alan Beaulieu',       '9781492057611', 1, 'O''Reilly',    2020, 'Aprende SQL con ejemplos prácticos.',       6, 5),
 (3,  'Historia del Perú',             'Jorge Basadre',       '9786124123450', 2, 'Universo',     2005, 'Panorama de la historia peruana.',          4, 3),
 (4,  'Los Ríos Profundos',            'José María Arguedas', '9788437604947', 3, 'Cátedra',      1958, 'Novela sobre la infancia andina.',          5, 5),
 (5,  'Cien años de soledad',          'Gabriel García Márquez','9780307474728',3, 'Vintage',     1967, 'La saga de los Buendía en Macondo.',        4, 4),
 (6,  'Clean Code',                    'Robert C. Martin',    '9780132350884', 4, 'Prentice Hall',2008, 'Buenas prácticas para código limpio.',      4, 4),
 (7,  'PHP y MySQL',                   'Luke Welling',        '9780672329166', 4, 'Pearson',      2016, 'Desarrollo web con PHP y MySQL.',           4, 4),
 (8,  'Breve historia del tiempo',     'Stephen Hawking',     '9780553380163', 5, 'Bantam',       1988, 'Cosmología para el lector general.',        3, 3),
 (9,  'El Principito',                 'Antoine de Saint-Exupéry','9780156012195',3,'Harcourt',   1943, 'Cuento filosófico clásico.',                4, 4),
 (10, 'Introducción a los Algoritmos', 'Cormen et al.',       '9780262046305', 4, 'MIT Press',    2022, 'Referencia de algoritmos y estructuras.',   3, 3),
 (11, 'La Ciudad y los Perros',        'Mario Vargas Llosa',  '9788432216220', 3, 'Alfaguara',    1963, 'Novela ambientada en el Leoncio Prado.',    1, 0);

-- Préstamo devuelto con 6 días de atraso (multa S/ 9.00 ya pagada)
INSERT INTO prestamos (id, id_usuario, id_libro, fecha_prestamo, fecha_devolucion_esperada, fecha_devolucion_real, estado) VALUES
 (1, 3, 1, CURDATE() - INTERVAL 20 DAY, CURDATE() - INTERVAL 13 DAY, CURDATE() - INTERVAL 7 DAY, 'devuelto');
INSERT INTO multas (id, id_prestamo, monto, estado, fecha_generacion, fecha_pago) VALUES
 (1, 1, 9.00, 'pagada', NOW() - INTERVAL 7 DAY, NOW() - INTERVAL 5 DAY);

-- Préstamos sin devolver (uno vence en unos días, otro ya está vencido, otro agota el stock)
INSERT INTO prestamos (id, id_usuario, id_libro, fecha_prestamo, fecha_devolucion_esperada, estado) VALUES
 (2, 4, 2,  CURDATE() - INTERVAL 2 DAY,  CURDATE() + INTERVAL 5 DAY, 'activo'),
 (3, 5, 3,  CURDATE() - INTERVAL 15 DAY, CURDATE() - INTERVAL 8 DAY, 'activo'),
 (4, 4, 11, CURDATE() - INTERVAL 1 DAY,  CURDATE() + INTERVAL 6 DAY, 'activo');

-- Reservas pendientes (una expira en menos de 24 h)
INSERT INTO reservas (id, id_usuario, id_libro, fecha_reserva, fecha_expiracion, estado) VALUES
 (1, 3, 4, NOW() - INTERVAL 38 HOUR, NOW() + INTERVAL 10 HOUR, 'pendiente'),
 (2, 5, 6, NOW() - INTERVAL 2 HOUR,  NOW() + INTERVAL 46 HOUR, 'pendiente');
