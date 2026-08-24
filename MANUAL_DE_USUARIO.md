# Manual de Usuario — Sistema de Botica

Versión del documento: generada a partir del análisis completo del código fuente (controladores, modelos y vistas) del sistema. Sistema desarrollado en PHP nativo con arquitectura MVC ligera (sin framework), MySQL como motor de base de datos (`botica_db`) y Bootstrap 5 + Chart.js en el frontend.

## 1. Introducción y arquitectura

El sistema es un ERP/POS especializado para boticas y farmacias. Cubre el ciclo completo: compra a proveedores → recepción e ingreso a inventario (lotes con vencimiento, motor FEFO) → venta en el punto de venta → control de caja → reportes gerenciales → auditoría de seguridad.

Estructura de carpetas relevante:

```
public/index.php          Punto de entrada único (front controller)
app/core/App.php          Router simple basado en la URL (?url=controlador/metodo/parametros)
app/core/Controller.php   Clase base: carga de modelos, renderizado de vistas, registro de auditoría
app/config/database.php   Conexión PDO a MySQL (botica_db)
app/controllers/*         Un controlador por módulo
app/models/*              Acceso a datos (PDO, sentencias preparadas)
app/views/*               Vistas PHP, agrupadas por módulo; app/views/layouts/main.php es el layout general (sidebar + topbar)
```

Las URLs siguen el patrón `basehttp://.../controlador/metodo/parametro`, por ejemplo `venta/pos`, `producto/edit/15`, `caja/cierre`.

### Roles de usuario

La tabla `roles` define 4 perfiles: **Administrador (1)**, **Farmacéutico (2)**, **Cajero (3)**, **Almacenero (4)**. En la práctica, el menú lateral solo diferencia entre **Administrador** (ve todos los módulos) y el resto de roles (ven únicamente Caja, POS, Historial de Ventas y Clientes). El usuario con `id = 1` es el administrador principal y no puede desactivarse.

## 2. Acceso al sistema (Login)

- URL: `auth/login`.
- Se ingresa usuario y contraseña. Las contraseñas se validan contra el hash `bcrypt` almacenado en `usuarios.password`.
- Al autenticar correctamente se guarda en sesión: `user_id`, `username`, `nombre`, `rol_id`, se actualiza `ultimo_login` y se registra el acceso en la tabla de auditoría (`audit_accesos`).
- `auth/logout` cierra la sesión y también queda registrado en auditoría.
- Tras iniciar sesión, `auth/index` redirige automáticamente: los usuarios con `rol_id = 2` van directo al **POS** (`venta/pos`); el resto va al **Dashboard** (`dashboard/index`).

## 3. Dashboard Gerencial (`dashboard/index`)

Disponible para todos los roles salvo el Vendedor/Farmacéutico (`rol_id = 2`), que es redirigido al POS. Muestra:

- Tarjetas resumen: ingresos del día, cantidad de productos activos en catálogo, lotes en riesgo de vencimiento (próximos 90 días), tickets/ventas del día y clientes registrados.
- Gráfico de barras con los ingresos de los últimos 7 días.
- Gráfico de dona con la distribución de ingresos por método de pago (Efectivo, Yape/Plin, Tarjeta, etc.).

## 4. Gestión de Caja (apertura, movimientos y cierre/arqueo)

Módulo obligatorio antes de poder vender. Cada caja está asociada a un usuario y solo se puede tener **una caja abierta a la vez** por usuario.

### 4.1 Apertura de caja — `caja/apertura`
1. El sistema verifica si el usuario ya tiene una caja abierta; si es así, redirige directo al cierre.
2. Se ingresa el **monto inicial** (sencillo en la gaveta).
3. Al confirmar, se crea el registro en la tabla `cajas` (`estado = 1`, abierta) y se redirige al POS para empezar a vender.

### 4.2 Movimientos extraordinarios — `caja/movimiento` (POST)
Desde la pantalla de cierre se pueden registrar **ingresos** o **egresos** manuales de efectivo (ej. pago de un servicio, retiro de seguridad), indicando tipo, monto y motivo. Quedan listados en el historial del turno actual (`caja_movimientos`).

### 4.3 Cierre de caja / Arqueo — `caja/cierre`
1. El sistema calcula el **efectivo esperado** = monto inicial + ventas en efectivo (no se cuentan las ventas por transferencia/tarjeta) + ingresos extra − egresos.
2. El cajero cuenta el efectivo físico de la gaveta e ingresa el **monto real**; puede añadir una observación.
3. Al confirmar, se calcula la **diferencia** (sobrante/faltante), se marca la caja como cerrada (`estado = 0`) y se redirige al **ticket de arqueo** para imprimir.

### 4.4 Historial de cajas — `caja/index` (solo Administrador)
Lista todos los arqueos por rango de fechas, con el detalle de cada cajero, montos y diferencias, y acceso al ticket impreso de cada cierre.

## 5. Punto de Venta — POS (`venta/pos`)

Pantalla central del día a día, pensada para trabajar rápido con teclado/lector de código de barras.

**Requisito:** el usuario debe tener una caja abierta; si no la tiene, se le redirige a `caja/apertura`.

### 5.1 Flujo de una venta paso a paso
1. **Buscar producto**: escribir en el buscador (nombre o código de barras) o escanear con lector; también se puede hacer clic directo sobre las tarjetas del catálogo a la derecha.
2. **Agregar al carrito**: cada producto agregado valida en el navegador que no se supere el stock disponible. Si el producto es "fraccionable" (ej. se vende por caja o por pastilla/blíster), se puede alternar la unidad de venta (Caja / Fracción) directamente en la fila del carrito.
3. **Seleccionar cliente**: por defecto "Público General" (id = 1, no acumula puntos). Si se elige un cliente registrado, se muestran sus puntos de fidelización acumulados y se habilita el botón "Usar Pts" para canjearlos como descuento (10 puntos = S/ 1, configurable en el código como `ratioCanje`).
4. **Aplicar descuento** (opcional): manual en soles, o automático vía canje de puntos.
5. **Elegir método de pago**: Efectivo, Yape/Plin o Tarjeta. Si es Efectivo, se ingresa el monto recibido y el sistema calcula el vuelto.
6. **Productos controlados**: si algún producto del carrito requiere receta médica, el sistema exige ingresar el número de colegiatura médica (CMP) antes de permitir cobrar.
7. **Cobrar**: al confirmar, se envía la venta al servidor (`venta/save`), que:
   - Verifica nuevamente que la caja siga abierta.
   - Calcula puntos ganados (1 sol = 1 punto, salvo Público General) y descuenta los puntos usados.
   - Registra la cabecera de venta (`ventas`) y, por cada línea, descuenta stock usando el motor **FEFO** (First Expired, First Out): siempre se consume primero el lote con fecha de vencimiento más próxima, pudiendo dividir una misma línea entre varios lotes si es necesario.
   - Actualiza el stock general del producto y registra el movimiento de **salida** en el Kardex.
   - Actualiza los puntos del cliente.
   - Si no hay stock suficiente en ningún lote, toda la operación se revierte (transacción) y se informa el error.
8. Tras una venta exitosa se puede **imprimir el ticket** (`venta/ticket/{id}`), una vista de impresión térmica de 80mm que se abre en una ventana nueva y se imprime automáticamente.

### 5.2 Historial de Ventas (`venta/index`)
Lista todas las boletas/tickets emitidos, con cajero, cliente, comprobante, método de pago y total. Permite:
- Reimprimir el ticket de cualquier venta.
- **Anular una venta**: revierte el stock a los lotes de origen y al stock general, registra la reversión en el Kardex (`ENTRADA`), revierte los puntos de fidelización otorgados/usados y marca la venta como `Anulada` (no se elimina el registro, por trazabilidad).

## 6. Clientes (`cliente/index`)

Directorio simple de clientes (DNI/RUC/Pasaporte). Permite crear, editar y dar de baja (baja lógica, `estado = 0`). El cliente `id = 1` ("Público General") está protegido: no se puede eliminar ni acumula puntos de fidelización.

## 7. Catálogo Maestro (solo Administrador)

### 7.1 Productos (`producto/index`, `producto/create`, `producto/edit/{id}`)
Ficha completa del medicamento/insumo: nombres genérico y comercial, concentración, forma farmacéutica, laboratorio, categoría, si requiere receta médica, precios (compra, venta, margen calculado automáticamente en el formulario), stock mínimo de alerta, y configuración de **venta fraccionada** (ej. vender por pastilla además de por caja, indicando unidades por caja y precio de la fracción). Los productos no se eliminan físicamente: se **activan/desactivan** (`producto/toggle/{id}`), y solo los activos aparecen en el POS.

### 7.2 Categorías (`categoria/index`) y Laboratorios (`laboratorio/index`)
CRUD simple (nombre + descripción) usado para clasificar y filtrar productos. Baja lógica igual que productos.

## 8. Compras y Proveedores (solo Administrador)

### 8.1 Proveedores (`proveedor/index`)
CRUD de proveedores (RUC, razón social, representante, teléfono, dirección).

### 8.2 Registrar una compra — `compra/create` → `compra/save`
1. Se elige el proveedor y el tipo/serie/número de comprobante.
2. Se elige el **estado**: `Completada` (carga stock de inmediato) o `Pendiente` (queda como orden de compra en borrador, sin afectar stock).
3. Se agregan líneas de producto indicando cantidad, lote, fecha de vencimiento y precio de compra unitario (con opción de actualizar el precio de compra del producto en el catálogo).
4. Si la compra es `Completada`, por cada línea se genera automáticamente un **lote** en `inventario_lotes` y un movimiento de **entrada** en el Kardex, y se incrementa el stock del producto.

### 8.3 Recepción de mercadería — `compra/recepcion/{id}` → `compra/procesar_recepcion`
Para las compras registradas como `Pendiente`: al llegar físicamente la mercadería, se ingresa el lote y fecha de vencimiento real de cada línea; al confirmar, recién ahí se generan los lotes, se actualiza el stock y se registra la entrada en el Kardex, y la compra pasa a `Completada`.

### 8.4 Devolución a proveedor — `compra/devolver/{id}` → `compra/save_devolucion`
Genera una nota de crédito de devolución sobre una compra ya recibida: se eligen las líneas/lotes a devolver y la cantidad (topada al stock disponible de ese lote), se descuenta el stock del lote y del producto, y se registra una **salida** en el Kardex.

## 9. Inventario (solo Administrador)

### 9.1 Fechas de Vencimiento / FEFO (`inventario/lotes`)
Lista todos los lotes con stock disponible, ordenados por fecha de vencimiento más próxima, con semáforo visual: **Vencido**, **En riesgo** (≤ 90 días) y **Sano**.

### 9.2 Kardex General (`inventario/kardex`)
Historial completo (hasta 500 movimientos) de entradas y salidas de stock por producto: motivo, tipo (ENTRADA/SALIDA/AJUSTE), cantidad y saldo resultante. Se puede filtrar por producto.

**Ingreso manual de stock al Kardex** (`inventario/entrada_manual`, modal "Añadir Lote/Stock Manual"):
1. Seleccionar el producto a afectar.
2. Indicar cantidad (en unidades mínimas), código de lote (opcional) y fecha de vencimiento (opcional; si no se indica y hay lote, se asume un año).
3. Indicar el motivo del ajuste (ej. "Inventario inicial", "Sobrante detectado").
4. Al confirmar, se crea el lote, se inserta el movimiento `ENTRADA` en el Kardex y se actualiza el stock del producto — todo en una sola transacción.

### 9.3 Inventario Físico / Toma de Inventario (`inventariofisico/index`)
Proceso de auditoría de stock:
1. **Iniciar auditoría** (`inventariofisico/iniciar`): el sistema toma una "foto" de todos los lotes activos con stock > 0 y sus cantidades según sistema.
2. **Conteo** (`inventariofisico/conteo/{id}`): se recorre la lista y se ingresa la cantidad física real contada por cada lote; el navegador calcula la diferencia (sobrante/faltante) en tiempo real.
3. **Finalizar** (`inventariofisico/finalizar`): por cada lote con diferencia, se ajusta la cantidad del lote, se registra el ajuste en el Kardex (tipo `AJUSTE`) y se actualiza el stock del producto. La auditoría queda marcada como `Finalizada`.

## 10. Alertas Sanitarias / Notificaciones (`notificacion/index`, solo Administrador)

Centraliza dos tipos de alerta operativa:
- **Lotes críticos**: próximos a vencer (90 días) o ya vencidos.
- **Bajo stock**: productos con `stock_actual` por debajo del umbral de alerta (por defecto ≤ 20 unidades mínimas).

Un contador con el total de alertas aparece como badge en el ícono de campana del sidebar y del topbar.

## 11. Reportes Gerenciales (`reporte/index`, solo Administrador)

- **Extracto de ventas**: exportación a CSV (compatible Excel) o vista imprimible en PDF (impresión del navegador), filtrando por rango de fechas, con desglose de subtotal, IGV y total por venta, más totales generales.
- **Medicamentos por vencer**: exportación CSV o PDF de los lotes con vencimiento dentro de 90 días, incluyendo estado (vencido / días restantes) y stock afectado.

## 12. Gestión de Personal / Usuarios (`usuario/index`, solo Administrador)

CRUD de cuentas de acceso al sistema: nombres, apellidos, correo, usuario, rol y contraseña (bcrypt). Al editar, la contraseña puede dejarse en blanco para no modificarla. Los usuarios no se eliminan: se **activan/desactivan** (`usuario/toggle/{id}`); el usuario `id = 1` (administrador principal) no puede desactivarse.

## 13. Configuración General (`configuracion/index`, solo Administrador)

Datos de la empresa usados en tickets y reportes impresos: nombre/razón social, RUC, dirección, teléfono, símbolo de moneda, porcentaje de IGV y logotipo (subida de imagen).

## 14. Auditoría del Sistema (`auditoria/index`, solo Administrador)

Panel de trazabilidad con dos pestañas:
- **Registro de Actividad**: acciones críticas del sistema (crear/editar producto, anular venta, ajustes de stock, compras, devoluciones, recepciones, etc.), con usuario, módulo, acción y descripción detallada.
- **Control de Accesos**: historial de inicios y cierres de sesión, con fecha/hora, usuario, dirección IP y navegador/sistema operativo del cliente.

## 15. Resumen de flujo operativo diario recomendado

1. El cajero/vendedor inicia sesión y **abre su caja** (`caja/apertura`) indicando el sencillo inicial.
2. Durante el turno, atiende ventas desde el **POS**, registra movimientos extraordinarios de caja si corresponde.
3. Al finalizar el turno, va a **Cierre de Caja**, cuenta el efectivo físico y confirma el arqueo; imprime el comprobante de cierre.
4. El administrador, en paralelo, gestiona el **catálogo** (altas de productos/categorías/laboratorios), registra **compras** a proveedores (directas o como orden pendiente con recepción posterior), realiza **ajustes de Kardex** cuando corresponde, supervisa las **Alertas Sanitarias** (vencimientos y quiebres de stock) y revisa el **Dashboard** y los **Reportes** para la toma de decisiones.
5. Periódicamente, el administrador ejecuta una **Toma de Inventario Físico** para conciliar el stock del sistema contra el conteo real del almacén.
