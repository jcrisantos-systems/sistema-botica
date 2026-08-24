# Sistema de Botica

[![License: Proprietary](https://img.shields.io/badge/license-proprietary-red.svg)](LICENSE)

ERP/POS para boticas y farmacias: catálogo de medicamentos, proveedores, compras con lotes FEFO, punto de venta, caja, inventario físico, reportes y auditoría.

**Stack:** PHP MVC nativo (sin framework) + MySQL/MariaDB + Bootstrap 5 + Chart.js.

## Requisitos

- PHP 8.0 o superior, con la extensión `pdo_mysql` habilitada (viene por defecto en Laragon/XAMPP).
- MySQL o MariaDB.
- Apache con `mod_rewrite` habilitado (el proyecto usa URLs amigables vía `.htaccess`).
- Un entorno local tipo [Laragon](https://laragon.org/) o XAMPP es lo más simple para levantar esto en Windows.

## Instalación

### 1. Clonar el repositorio

Colócalo en la carpeta que sirve tu servidor web (p. ej. `C:\laragon\www\sistema-botica` en Laragon):

```
git clone https://github.com/jcrisantos-systems/sistema-botica.git
```

### 2. Crear la base de datos

Los scripts en `database/` deben ejecutarse **en este orden** (cada uno depende del anterior). Usa `--default-character-set=utf8mb4` al importar para evitar problemas de codificación con tildes/eñes:

```
mysql -u root --default-character-set=utf8mb4 < database/botica_db.sql
mysql -u root --default-character-set=utf8mb4 botica_db < database/fase2.sql
mysql -u root --default-character-set=utf8mb4 botica_db < database/fase3.sql
mysql -u root --default-character-set=utf8mb4 botica_db < database/fase4.sql
mysql -u root --default-character-set=utf8mb4 botica_db < database/fase5_caja_usuarios.sql
mysql -u root --default-character-set=utf8mb4 botica_db < database/fase6_pendientes.sql
mysql -u root --default-character-set=utf8mb4 botica_db < database/fase7_fidelizacion.sql
mysql -u root --default-character-set=utf8mb4 botica_db < database/fase8_devoluciones.sql
mysql -u root --default-character-set=utf8mb4 botica_db < database/fase10_auditoria.sql
mysql -u root --default-character-set=utf8mb4 botica_db < database/fase11_inventario_fisico.sql
```

`database/botica_db.sql` ya crea la base `botica_db`, los 4 roles del sistema, los parámetros de configuración por defecto (nombre de la botica, IGV, moneda, etc.) y un usuario administrador inicial — no falta ningún dato para poder iniciar sesión después de este paso.

Opcionalmente, para tener datos de ejemplo (productos, clientes, proveedores, ventas de prueba) con los que explorar el sistema:

```
mysql -u root --default-character-set=utf8mb4 botica_db < database/seed_data.sql
```

También puedes hacer todo esto desde phpMyAdmin (HeidiSQL en Laragon) si prefieres una interfaz gráfica: crea la base `botica_db` con collation `utf8mb4_unicode_ci` e importa los mismos archivos en el mismo orden.

### 3. Configurar la conexión a la base de datos

Edita `app/config/database.php` con tus credenciales de MySQL:

```php
private $host = "localhost";
private $db_name = "botica_db";
private $username = "root";
private $password = "";
```

### 4. Levantar el sitio

- **Con Laragon:** al colocar el proyecto en `www/`, Laragon lo sirve automáticamente en `http://sistema-botica.test/` (con "Auto Virtual Hosts" activado) o en `http://localhost/sistema-botica/`.
- **Con XAMPP/Apache manual:** asegúrate de que `mod_rewrite` esté habilitado y que `AllowOverride All` esté configurado para la carpeta del proyecto, luego accede vía `http://localhost/sistema-botica/`.

El punto de entrada real es `public/index.php`; el `.htaccess` de la raíz redirige todo hacia ahí, así que no necesitas apuntar el DocumentRoot directamente a `public/`.

### 5. Iniciar sesión

Credenciales por defecto (creadas por `botica_db.sql`):

| Usuario | Contraseña |
|---|---|
| `admin` | `admin` |

**Importante:** cambia esta contraseña desde *Mi Perfil* apenas ingreses, especialmente si vas a exponer el sistema fuera de tu red local.

## Roles del sistema

| Rol | Acceso |
|---|---|
| Administrador | Todos los módulos |
| Farmacéutico | Redirigido directo al Punto de Venta (POS) |
| Cajero | Caja, POS, Historial de Ventas, Clientes |
| Almacenero | Caja, POS, Historial de Ventas, Clientes |

## Más documentación

- [`MANUAL_DE_USUARIO.md`](MANUAL_DE_USUARIO.md) — guía de uso de cada módulo.
- [`REPORTE_SEGURIDAD.md`](REPORTE_SEGURIDAD.md) — auditoría de seguridad y hallazgos corregidos.

## Licencia

Software propietario. Todos los derechos reservados — ver [`LICENSE`](LICENSE).
