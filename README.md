# BiblioTech

Sistema de biblioteca en **PHP puro** (sin framework) con MySQL/MariaDB. Se ejecuta sobre XAMPP (Apache + `mod_rewrite`).

## Puesta en marcha

1. Copia el proyecto en `htdocs/Bibliotech` y activa Apache y MySQL.
2. Crea la base de datos: importa `database/bdbibliotech.sql` (esquema) desde phpMyAdmin.
3. *(Opcional, solo desarrollo)* importa `database/datos_demo.sql`. Crea 5 usuarios con contraseña `Admin12345`:
   `admin@`, `biblio@`, `ana@`, `luis@`, `carla@` + `bibliotech.test`.
4. Ajusta la conexión con las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
   (valores por defecto en `app/Core/Database.php`).
5. Si la base ya existía antes del módulo de configuración, importa `database/migracion_configuraciones.sql`.

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
| Configuración | `/admin/configuracion` | Reglas de circulación |
| Libros | `/admin/libros` | RF06, RF07 |
| Categorías | `/admin/categorias` | RF08 |
| Usuarios | `/admin/usuarios` | RF03–RF05 |
| Préstamos | `/admin/prestamos` | RF14–RF16, RF18 |
| Reservas | `/admin/reservas` | RF17 |
| Multas | `/admin/multas` | RF19–RF22 |

Todas las rutas exigen sesión con rol `administrador` (`BaseController`) y los formularios POST validan el token CSRF.

La configuración se guarda en MySQL en la tabla `configuraciones` (clave-valor). El formulario limita los préstamos a 1–60 días, los máximos a 1–99, la vigencia de reservas a 1–720 horas, la mora a S/ 0.01–1,000.00 y los días de gracia a 0–60. Los cambios rigen en nuevos préstamos/reservas y devoluciones procesadas después del cambio.

## Módulo Bibliotecario (`/bibliotecario`)

Al iniciar sesión con el rol bibliotecario, `/bibliotecario` muestra la página de inicio y conserva sus módulos de catálogo, gestión de préstamos y devoluciones, confirmación de reservas para generar préstamos y registro de pagos de multas. Las rutas del panel están en `app/Routes/bibliotecario.php`, cargadas desde `index.php`; cada controlador exige sesión con rol `bibliotecario` y valida CSRF en las acciones POST.

## Catálogo público

La página de bienvenida con enlaces para iniciar sesión y registrarse se muestra al abrir la aplicación. El catálogo está disponible sin iniciar sesión en `/catalogo`, y cada libro tiene una ficha pública en `/catalogo/show?id=<id>`. Los visitantes pueden consultar disponibilidad y detalles; solo los usuarios autenticados con rol de estudiante pueden crear reservas.

## Reglas de circulación (`Circulacion`)

- Valores iniciales configurables: préstamo de **14 días**, mora de **S/ 0.20 por día**, sin días de gracia, hasta **3 préstamos** y **3 reservas** activas por estudiante, y reservas vigentes por **48 horas**.
- El formulario de reserva impide exceder el máximo activo y aplica el plazo configurado; el cálculo de mora resta los días de gracia y usa la tarifa vigente al procesar la devolución.
- Si está habilitada la opción de bloqueo, un usuario con multas pendientes no puede crear reservas ni recibir préstamos (incluidas las reservas confirmadas por el bibliotecario).
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
