# BiblioTech

Sistema de biblioteca en **PHP puro** (sin framework) con MySQL/MariaDB. Se ejecuta sobre XAMPP (Apache + `mod_rewrite`).

## Puesta en marcha

1. Copia el proyecto en `htdocs/Bibliotech` y activa Apache y MySQL.
2. Crea la base de datos: importa `database/bdbibliotech.sql` (esquema) desde phpMyAdmin.
3. *(Opcional, solo desarrollo)* importa `database/datos_demo.sql`. Crea 5 usuarios con contraseña `Admin12345`:
   `admin@`, `biblio@`, `ana@`, `luis@`, `carla@` + `bibliotech.test`.
4. Ajusta la conexión con las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
   (valores por defecto en `app/Core/Database.php`).

## Estructura

```
index.php                      Front controller, sesión, cabeceras de seguridad y rutas base
app/Core/                      Router, Database, Csrf, Url, Auth, View, Flash, Paginator
app/Routes/admin.php           Rutas del módulo Administrador
app/Controllers/Admin/         Un controlador por pantalla del panel
app/Models/                    Acceso a datos (PDO, consultas preparadas)
app/Services/Circulacion.php   Reglas de préstamos, devoluciones, reservas y multas
app/Views/admin/               Vistas del panel (layout + una carpeta por sección)
assets/css/admin.css           Estilos del panel   ·   assets/js/admin.js  Confirmaciones
database/                      Esquema y datos demo
```

## Módulo Administrador (`/admin`)

| Sección | Ruta | Requerimientos |
|---|---|---|
| Dashboard | `/admin` | RF23–RF27 |
| Libros | `/admin/libros` | RF06, RF07 |
| Categorías | `/admin/categorias` | RF08 |
| Usuarios | `/admin/usuarios` | RF03–RF05 |
| Préstamos | `/admin/prestamos` | RF14–RF16, RF18 |
| Reservas | `/admin/reservas` | RF17 |
| Multas | `/admin/multas` | RF19–RF22 |

Todas las rutas exigen sesión con rol `administrador` (`BaseController`) y los formularios POST validan el token CSRF.

## Reglas de circulación (`Circulacion`)

- Préstamo por defecto: **7 días**. Multa: **S/ 1.50 por día de atraso** (S/ 9.00 por 6 días), generada al devolver.
- El stock se descuenta al prestar y se repone al devolver. Reservar **no** descuenta stock; sí lo hace confirmar la entrega.
- Reservas pendientes pasan a `expirada` y préstamos activos a `vencido` automáticamente al abrir las pantallas de circulación.
- La clase no depende del rol: el módulo **Bibliotecario** puede reutilizarla (`registrarPrestamo`, `registrarDevolucion`, `confirmarReserva`, `pagarMulta`, ...).

## Trabajo en equipo con GitHub

Cada integrante trabaja en su rama y agrega **sus propios archivos**, para evitar conflictos:

- Rutas propias en `app/Routes/<rol>.php` y un `require` en `index.php`.
- Controladores en `app/Controllers/<Rol>/`, vistas en `app/Views/<rol>/`, estilos en `assets/css/<rol>.css`.
- Los archivos compartidos (`Core/`, `Models/Usuario.php`, `index.php`) se tocan lo mínimo.

```bash
git checkout -b modulo-administrador
git add .
git commit -m "Módulo administrador: dashboard, libros, categorías, usuarios, préstamos, reservas y multas"
git push -u origin modulo-administrador   # luego abre un Pull Request hacia main
```

> La política de seguridad (CSP) no permite estilos ni scripts en línea: usa archivos `.css`/`.js` y atributos SVG.
