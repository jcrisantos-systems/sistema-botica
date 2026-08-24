# Reporte de Auditoría de Seguridad — Sistema de Botica

Alcance: revisión manual de todo el código fuente (`app/controllers`, `app/models`, `app/views`, `app/core`, `app/config`, `public`). Metodología: revisión estática línea por línea buscando inyección SQL, XSS, CSRF, control de acceso roto (IDOR), manejo de sesión/contraseñas y validación de entradas, siguiendo el checklist OWASP Top 10.

Estado: los hallazgos **CRÍTICOS** y **ALTOS** listados en este documento fueron corregidos directamente en el código como parte de la Fase 3 (ver sección "Remediación aplicada" al final de cada hallazgo y el resumen final).

---

## Resumen ejecutivo

| # | Hallazgo | Severidad | Estado |
|---|----------|-----------|--------|
| 1 | Ausencia total de protección CSRF en formularios y acciones de estado (incluyendo acciones destructivas vía enlaces GET) | **Crítico** | Corregido |
| 2 | Control de acceso roto / Falta de verificación de rol en la mayoría de controladores administrativos | **Crítico** | Corregido |
| 3 | XSS almacenado a través del nombre de producto en vistas que construyen HTML por JavaScript (`innerHTML`) | **Alto** | Corregido |
| 4 | XSS reflejado/almacenado por mensajes flash (`$_SESSION['error']`, `['mensaje']`, etc.) impresos sin escapar en más de 10 vistas | **Alto** | Corregido |
| 5 | XSS almacenado en el panel de Auditoría (campos `usuario`, `ip_address`, `modulo`, `accion` sin escapar) | **Medio** | Corregido |
| 6 | Fijación de sesión (no se regenera el ID de sesión tras autenticar) | **Alto** | Corregido |
| 7 | Sin límite de intentos de inicio de sesión (fuerza bruta) | **Medio** | Corregido (mitigación en capa de aplicación) |
| 8 | Cookie de sesión sin flags de seguridad (`HttpOnly`, `SameSite`, `Secure`) | **Medio** | Corregido |
| 9 | Validación de entradas insuficiente en Kardex/Caja (cantidades, tipos de movimiento) | **Medio** | Corregido |
| 10 | Divulgación de información sensible: errores de conexión a BD impresos al usuario final | **Bajo** | Corregido |
| 11 | Subida de logo sin validar que el archivo sea realmente una imagen | **Bajo/Medio** | Corregido |
| 12 | Credenciales de acceso precargadas en el formulario de login (`admin`/`admin`) | **Bajo** (higiene) | Corregido |
| 13 | Inyección SQL / *raw queries* | **No se encontró** | — |

No se encontraron vulnerabilidades de inyección SQL: **el 100% de las consultas en todos los modelos usa PDO con sentencias preparadas (`bindParam`/`bindValue`)**, sin concatenación de datos de usuario dentro del SQL.

---

## 1. CSRF (Cross-Site Request Forgery) — Crítico

**Descripción:** el framework no implementa ningún mecanismo de token CSRF. Ningún formulario (`<form method="POST">`) incluye un token de verificación, y el servidor no valida ninguno. Peor aún, varias acciones **destructivas o sensibles se ejecutan mediante enlaces `GET`** simples (sin siquiera requerir POST), por ejemplo:

- `producto/toggle/{id}` — activa/desactiva un producto (`app/views/productos/index.php:68,76`).
- `usuario/toggle/{id}` — activa/desactiva una cuenta de usuario (`app/views/usuarios/index.php:68`).
- `cliente/delete/{id}`, `proveedor/delete/{id}`, `categoria/delete/{id}`, `laboratorio/delete/{id}` — baja lógica de registros.
- `venta/anular/{id}` — **anula una venta y revierte stock/puntos** (`app/views/ventas/index.php:79`).

**Impacto:** un atacante puede alojar en cualquier sitio web una etiqueta `<img src="https://victima/venta/anular/123">` o un enlace, y si un administrador autenticado la visita (sin siquiera hacer clic, en el caso de `<img>`), la acción se ejecuta con sus privilegios. Los formularios POST (crear producto, guardar venta, cerrar caja, cambiar configuración, etc.) son igualmente forjables desde un sitio malicioso porque no hay token que los distinga de una petición legítima.

**Remediación aplicada:**
- Se genera un token CSRF único por sesión (`$_SESSION['csrf_token']`, `random_bytes(32)`) en `public/index.php`.
- Se añadió `Controller::verifyCsrf()` que valida el token con `hash_equals()` en **todas** las acciones POST que modifican datos (ventas, compras, devoluciones, recepciones, caja, inventario, productos, categorías, laboratorios, clientes, proveedores, usuarios, configuración, inventario físico, login).
- Se agregó el campo oculto `csrf_token` a todos los formularios `<form method="POST">` del sistema.
- Las acciones antes disparadas por enlaces `GET` (`toggle`, `delete`, `anular`) se convirtieron en formularios `POST` con su token CSRF, conservando la confirmación (`confirm()`) previa al envío.

## 2. Control de acceso roto / Falta de verificación de rol (Broken Access Control) — Crítico

**Descripción:** el sidebar (`app/views/layouts/main.php`) oculta los módulos administrativos (Productos, Categorías, Laboratorios, Compras, Proveedores, Inventario, Inventario Físico, Alertas Sanitarias, Historial de Cajas) a los usuarios que no son `rol_id = 1`, dando la falsa sensación de que están protegidos. Sin embargo, a nivel de **controlador** casi ninguno valida el rol, solo la existencia de sesión (`isset($_SESSION['user_id'])`):

- `ProductoController` (`index/create/edit/save/toggle`) — cualquier usuario autenticado (Cajero, Almacenero, Farmacéutico) puede crear, editar o desactivar productos y alterar precios.
- `CategoriaController`, `LaboratorioController`, `ProveedorController` — CRUD completo sin verificación de rol.
- `CompraController` — cualquier usuario puede registrar compras, procesar recepciones y generar devoluciones (movimientos financieros y de stock).
- `InventarioController` — cualquier usuario puede insertar ajustes manuales de stock (`entrada_manual`) y ver el Kardex completo.
- `InventarioFisicoController` — cualquier usuario puede iniciar/finalizar auditorías de inventario y forzar ajustes de stock.
- `NotificacionController` — expone alertas operativas sin restricción.
- `CajaController::index` (historial de todos los arqueos de todos los cajeros) — sin restricción de rol, pese a que el menú lo oculta a no-administradores.
- `VentaController::anular` — cualquier usuario autenticado puede anular **cualquier** venta (no solo las propias), revirtiendo stock y puntos de fidelización — es además un vector de fraude interno (anular una venta ya cobrada en efectivo).
- `CajaController::ticket_arqueo($id)` — **IDOR**: cualquier usuario autenticado puede ver el ticket de arqueo de la caja de **cualquier otro cajero** simplemente cambiando el `id` en la URL, exponiendo montos de caja y diferencias de otros turnos.

**Impacto:** un usuario con el perfil más bajo (Cajero) puede escalar funcionalmente a nivel Administrador: alterar el catálogo y precios, manipular inventario y compras, y anular ventas de otros cajeros para encubrir faltantes de caja.

**Remediación aplicada:**
- Se añadió verificación de rol (`rol_id == 1`, admin) en el constructor de: `ProductoController`, `CategoriaController`, `LaboratorioController`, `CompraController`, `ProveedorController`, `InventarioController`, `InventarioFisicoController`, `NotificacionController`, alineando el backend con lo que la interfaz ya insinuaba.
- `CajaController::index` (historial) ahora exige `rol_id == 1`.
- `CajaController::ticket_arqueo` ahora exige que el solicitante sea administrador **o** el usuario dueño de esa caja.
- `VentaController::anular` ahora exige `rol_id == 1` (solo un administrador/supervisor puede anular ventas), reduciendo el riesgo de fraude interno.

## 3. XSS almacenado vía `innerHTML` en JavaScript (nombre de producto) — Alto

**Descripción:** varias vistas insertan datos que provienen de la base de datos (nombre de producto, unidad de medida) dentro de `innerHTML`/plantillas de cadena en JavaScript **sin escapar HTML**, aun cuando el mismo dato sí se escapa correctamente del lado del servidor en otras partes de la misma página. Como el nombre de producto (`nombre_comercial`) es editable por **cualquier usuario autenticado** (ver hallazgo 2), esto es explotable de extremo a extremo:

- `app/views/compras/create.php:131` — el listado de productos (`<option>`) del selector de compras se construye con `${p.nombre_comercial}` dentro de un template string asignado a `innerHTML`.
- `app/views/ventas/pos.php:198-201` — el atributo `data-busqueda` se imprime **sin `htmlspecialchars`**, permitiendo cerrar el atributo con `"` e inyectar HTML/atributos arbitrarios en la tarjeta de producto del catálogo del POS.
- `app/views/ventas/pos.php:424-457` (`renderCarrito()`) — el nombre del producto (`item.nombre`) y el combo de unidad se insertan en `innerHTML` de cada fila del carrito sin escapar.

**Prueba de concepto:** un Cajero (que, antes de la corrección del hallazgo 2, podía editar productos) registra un producto con `nombre_comercial = <img src=x onerror=alert(document.cookie)>`. Al añadirlo al carrito en el POS, o al abrir el formulario de "Registrar Compra", el script se ejecuta en el navegador de quien vea esa pantalla (incluido un Administrador).

**Remediación aplicada:** se agregó una función `escapeHtml()` en JavaScript y se aplicó a todo dato dinámico insertado vía `innerHTML`/atributos en `ventas/pos.php` (incluida la corrección del atributo `data-busqueda`) y `compras/create.php`.

## 4. XSS por mensajes flash de sesión sin escapar — Alto

**Descripción:** el patrón `echo $_SESSION['mensaje']; unset($_SESSION['mensaje']);` (y equivalentes con `error`, `success`, `error_pos`, `mensaje_pos`) se repite en al menos 10 vistas **sin `htmlspecialchars`**:
`ventas/pos.php`, `ventas/index.php`, `inventario_fisico/index.php`, `usuarios/index.php`, `inventario/kardex.php`, `configuracion/index.php`, `cajas/apertura.php`, `cajas/cierre.php`, `compras/index.php`.

Aunque la mayoría de estos mensajes son cadenas fijas escritas por el propio controlador, varios se construyen concatenando datos que **sí provienen de entradas de usuario** (nombres de producto en mensajes de error de `Compra::procesarRecepcion`/`registrarDevolucion`, motivos, etc.), por lo que constituyen un vector real de XSS almacenado/reflejado, no solo teórico.

**Remediación aplicada:** todos los `echo $_SESSION[...]` de mensajes flash fueron envueltos en `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.

## 5. XSS almacenado en el panel de Auditoría — Medio

**Descripción:** en `app/views/auditoria/index.php`, los campos `$log['username']` (línea 98) y `$log['ip_address']` (línea 106) del listado de "Control de Accesos" se imprimen sin `htmlspecialchars`. El nombre de usuario (`usuarios.usuario`) es un dato que el Administrador ingresa al crear cuentas (`UsuarioController::save`) y no se sanitiza; si contuviera marcado HTML, se ejecutaría cada vez que cualquier administrador revise el log de accesos. Los campos `modulo` y `accion` (líneas 56 y 65) tampoco se escapan (actualmente son constantes definidas por los propios controladores, por lo que el riesgo real es bajo, pero se corrigieron por defensa en profundidad).

**Remediación aplicada:** se añadió `htmlspecialchars()` a `username`, `ip_address`, `modulo` y `accion` en la vista de auditoría.

## 6. Fijación de sesión (Session Fixation) — Alto

**Descripción:** `AuthController::login()` asigna las variables de sesión tras autenticar (`$_SESSION['user_id'] = ...`) pero **nunca regenera el identificador de sesión** (`session_regenerate_id`). Un atacante que logre fijar/conocer un `PHPSESSID` antes del login (por ejemplo, mediante un enlace con la cookie de sesión precargada en entornos que la aceptan por URL, o mediante un subdominio hermano) puede secuestrar la sesión ya autenticada del usuario víctima.

**Remediación aplicada:** se agregó `session_regenerate_id(true)` inmediatamente después de una autenticación exitosa, antes de escribir cualquier dato de sesión.

## 7. Sin límite de intentos de login (fuerza bruta) — Medio

**Descripción:** `User::login()` no implementa bloqueo ni retardo tras intentos fallidos, permitiendo ataques de fuerza bruta/diccionario contra las contraseñas de los usuarios (especialmente relevante para la cuenta `admin` con contraseña precargada en el formulario, ver hallazgo 12).

**Remediación aplicada:** se implementó un contador de intentos fallidos por sesión con bloqueo temporal (backoff) en `AuthController::login()`, sin requerir cambios de esquema de base de datos: tras 5 intentos fallidos se exige esperar un intervalo creciente antes de reintentar. (Nota: por tratarse de un control en la capa de aplicación basado en sesión, no sustituye una solución de nivel de infraestructura como `fail2ban`/WAF/rate-limiting por IP para un entorno de producción expuesto a Internet; se documenta como mitigación razonable dentro del alcance de este sistema.)

## 8. Cookie de sesión sin flags de seguridad — Medio

**Descripción:** `session_start()` se invoca en `public/index.php` con la configuración por defecto de PHP, sin `HttpOnly`, `SameSite` ni `Secure` explícitos, lo que facilita el robo de la cookie de sesión vía XSS (ver hallazgos 3-5) y ataques CSRF (ver hallazgo 1).

**Remediación aplicada:** se configuró `session_set_cookie_params()` antes de `session_start()` con `httponly = true`, `samesite = Lax` y `secure` condicionado a si la petición llega por HTTPS (`$_SERVER['HTTPS']`).

## 9. Validación de entradas insuficiente en Kardex y movimientos de Caja — Medio

**Descripción:**
- `InventarioController::entrada_manual()` castea `id_producto`/`cantidad` a enteros pero no valida que sean positivos ni que el producto exista antes de llamar a `Inventario::registrarEntrada()`, permitiendo registrar movimientos de Kardex con cantidad `0` o negativa, o contra un `id_producto` inexistente (dejando un registro huérfano en `kardex`).
- `CajaController::movimiento()` valida `monto > 0` pero no valida que `tipo` sea uno de los valores esperados (`INGRESO`/`EGRESO`); un valor arbitrario se insertaría tal cual en `caja_movimientos.tipo`, pudiendo romper los cálculos de `Caja::getResumenActual()` (que distingue explícitamente por esos dos valores) y descuadrar el arqueo de caja sin ningún aviso.

**Remediación aplicada:** se agregaron validaciones explícitas de rango/whitelist antes de tocar la base de datos en ambos controladores, devolviendo un mensaje de error controlado en vez de insertar datos inconsistentes.

## 10. Divulgación de información sensible en errores de conexión — Bajo

**Descripción:** `app/config/database.php` hace `echo "Error de conexión: " . $exception->getMessage();` dentro del `catch`, exponiendo detalles internos (motor de BD, a veces rutas o versión) directamente al usuario final si la conexión falla.

**Remediación aplicada:** se reemplazó por registro del error en el log del servidor (`error_log`) y un mensaje genérico no técnico hacia el usuario.

## 11. Subida de logo sin validar que sea una imagen real — Bajo/Medio

**Descripción:** `ConfiguracionController::save()` valida la extensión del archivo subido (whitelist `jpg/jpeg/png/gif`) pero no verifica que el contenido sea realmente una imagen (`getimagesize()`), permitiendo subir un archivo arbitrario con una de esas extensiones (por ejemplo, HTML/SVG camuflado) al directorio público `public/img/`.

**Remediación aplicada:** se añadió validación con `getimagesize()` antes de mover el archivo subido; si el archivo no es una imagen válida, se rechaza la subida con un mensaje de error.

## 12. Credenciales precargadas en el formulario de login — Bajo (higiene de seguridad)

**Descripción:** `app/views/auth/login.php` trae los campos de usuario y contraseña con `value="admin"` fijo, lo cual sugiere/facilita el uso de credenciales por defecto y puede quedar así en un despliegue real por descuido.

**Remediación aplicada:** se removieron los valores precargados del formulario de login.

## 13. Inyección SQL — No se encontró

Se revisaron los 14 modelos y controladores con acceso a base de datos. El 100% de las operaciones usa `PDO::prepare()` con `bindParam`/`bindValue`, incluyendo los parámetros dinámicos de `LIMIT` (`Auditoria::getAccesos/getAcciones`, correctamente forzados con `PDO::PARAM_INT`). No se detectó concatenación de entrada de usuario dentro de una cadena SQL en ningún punto del código.

---

## Alcance no cubierto / recomendaciones adicionales para un entorno de producción

Estas quedan fuera de lo que puede resolverse solo con cambios de código de aplicación, pero se documentan para el equipo responsable de infraestructura:

- Servir el sitio exclusivamente por HTTPS (actualmente `BASE_URL` se arma dinámicamente según el request, sin forzar redirección a HTTPS).
- Definir políticas de complejidad/expiración de contraseñas y autenticación de dos factores para el rol Administrador.
- Configurar cabeceras de seguridad HTTP (`Content-Security-Policy`, `X-Content-Type-Options`, `X-Frame-Options`) a nivel de servidor web.
- Mover el límite de intentos de login de sesión a un mecanismo persistente por IP/usuario (Redis, tabla dedicada o WAF) para que sobreviva a sesiones nuevas.
