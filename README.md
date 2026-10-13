# LogiTrack

Sistema de gestión de una cadena logística terrestre: envíos, viajes, vehículos, choferes y sucursales, con trazabilidad completa del paquete desde que entra a una sucursal hasta que se entrega.

Proyecto de Base de Datos II (Licenciatura en Sistemas de Información, Universidad Champagnat). Escrito en PHP sin frameworks, con foco en el diseño relacional y en dejar reglas de negocio dentro del motor de base de datos.

> **Todos los datos de este repositorio son ficticios.** `database/seed.sql` carga nombres, documentos y patentes inventados. No hay datos reales de personas.

## Qué incluye

- **19 tablas** relacionales, con claves foráneas, y cuatro roles: administrador, empleado de sucursal, chofer y cliente, cada uno con su dashboard.
- **Lógica en el motor** (`database/schema.sql`): dos triggers, un procedimiento almacenado y una vista (`VW_Viajes_Activos`).
- **Seguimiento público**: cualquiera con el número de tracking consulta el estado de un envío sin iniciar sesión.
- **PHP 8 con PDO**, arquitectura MVC (`models/`, `controllers/`, `views/`) y consultas preparadas. JavaScript solo en el mapa de un viaje (Leaflet).

## Requisitos

- PHP 8 o superior con las extensiones `pdo_mysql` y `mbstring` (probado con PHP 8.4)
- MariaDB o MySQL

## Cómo correrlo en local

```bash
# 1. Crear la base y cargar estructura + datos ficticios
mariadb -u root -p -e "CREATE DATABASE LOGITRACK CHARACTER SET utf8mb4"
mariadb -u root -p LOGITRACK < database/schema.sql
mariadb -u root -p LOGITRACK < database/seed.sql

# 2. Configurar la conexión (config/db.php no se versiona)
cp config/db.example.php config/db.php
#    editar DB_USER y DB_PASS

# 3. Servirlo bajo /LOGITRACK/ (las rutas de la app son absolutas)
mkdir -p ~/www && ln -s "$PWD" ~/www/LOGITRACK
php -S localhost:8080 -t ~/www
```

Abrir <http://localhost:8080/LOGITRACK/login.php>.

## Usuarios de prueba

Contraseña de todos: `LogiTrack-demo-2026`

| Usuario | Rol |
|---|---|
| `admin.demo` | Administrador |
| `empleado.demo` | Empleado de sucursal |
| `chofer.demo` | Chofer |
| `cliente.demo` | Cliente |

Tracking público de ejemplo: `LT-DEMO0001`. Estas credenciales son solo para pruebas locales.

## Estructura

```
config/       conexión a la base y control de sesión
models/       acceso a datos (PDO)
controllers/  lógica de cada módulo
views/        pantallas
database/     schema.sql (estructura) y seed.sql (datos ficticios)
```
