## Sistema Web de Botica — PHP MVC + MySQL + Bootstrap

Aquí tienes el plan completo de funcionalidades del sistema antes de escribir una sola línea de código.

---

### 🏗️ Arquitectura General

**Patrón:** MVC puro en PHP (sin frameworks externos)
**Frontend:** Bootstrap 5 + Bootstrap Icons
**Base de datos:** MySQL
**Autenticación:** Sesiones PHP nativas

---

### 👥 Módulo 1 — Gestión de Usuarios y Roles

* Roles: **Administrador**, **Farmacéutico**, **Cajero**, **Almacenero**
* Login con sesiones y control de acceso por rol
* CRUD de usuarios (solo admin)
* Cambio de contraseña y perfil propio
* Bitácora de accesos (login/logout con IP y hora)

---

### 💊 Módulo 2 — Catálogo de Productos (Medicamentos)

* CRUD de medicamentos con: nombre genérico, nombre comercial, concentración, forma farmacéutica (tableta, jarabe, inyectable, etc.)
* Clasificación por **categoría** (analgésicos, antibióticos, vitaminas…) y **laboratorio**
* Código de barras / código interno
* Precio de compra, precio de venta, margen de ganancia automático
* Unidad de medida (unidad, caja, blíster, frasco)
* Indicación de si requiere **receta médica**
* Foto del producto (opcional)
* Estado activo/inactivo

---

### 🏭 Módulo 3 — Proveedores y Laboratorios

* CRUD de proveedores (RUC, razón social, representante, teléfono, dirección)
* CRUD de laboratorios/marcas
* Historial de compras por proveedor

---

### 📦 Módulo 4 — Gestión de Almacén / Inventario

* **Ingreso de stock** (entrada por compra o ajuste manual)
* **Control de lotes** con fecha de vencimiento por cada entrada
* Alertas automáticas de:
  * Stock mínimo (medicamentos por agotarse)
  * Productos próximos a vencer (configurable: 30, 60, 90 días)
  * Productos vencidos (bloqueo automático de venta)
* Ajuste de inventario (merma, pérdida, donación) con motivo y responsable
* **Kardex** por producto (movimientos de entrada y salida)
* Toma de inventario físico vs sistema

---

### 🛒 Módulo 5 — Compras

* Órdenes de compra a proveedores (estado: borrador → enviada → recibida)
* Registro de facturas/boletas de compra
* Actualización automática de stock al confirmar recepción
* Devolución a proveedor con nota de crédito
* Historial de compras con filtros por fecha, proveedor, producto

---

### 🧾 Módulo 6 — Ventas (Punto de Venta)

* Interfaz de **caja rápida** (búsqueda de producto por nombre o código de barras)
* Carrito de venta con cantidad, precio, subtotal
* Tipos de comprobante: **Boleta** / **Factura** / **Ticket interno**
* Aplicación de descuentos (por producto o total de venta)
* Métodos de pago: **efectivo** (con vuelto automático), **transferencia**, **tarjeta**
* Verificación automática de:
  * Stock disponible antes de vender
  * Si requiere receta (solicitar nro. de receta o CMP del médico)
  * Lote más próximo a vencer (FEFO — primero en vencer, primero en salir)
* Impresión de comprobante (ticket 80mm o A4)
* Anulación de venta con motivo y reposición de stock

---

### 👤 Módulo 7 — Clientes

* CRUD de clientes (DNI, nombre, teléfono, dirección)
* Búsqueda rápida por DNI o nombre en el POS
* Historial de compras por cliente
* Sistema de **puntos o fidelización** (opcional, configurable)

---

### 📊 Módulo 8 — Reportes y Estadísticas

* **Ventas:** por día, semana, mes, rango de fechas, por cajero, por producto
* **Inventario:** stock actual, kardex general, productos críticos
* **Vencimientos:** listado por rango de fecha de caducidad
* **Compras:** por proveedor, por período
* **Productos más vendidos** (ranking)
* **Rentabilidad:** margen por producto, utilidad del período
* Exportación a **PDF** y **Excel**

---

### ⚙️ Módulo 9 — Configuración del Sistema

* Datos de la botica (nombre, RUC, dirección, logo, teléfono) para comprobantes
* Configuración de stock mínimo global o por producto
* Configuración de días de alerta para vencimientos
* Gestión de categorías, unidades de medida, formas farmacéuticas
* Parámetros de IGV (18%) activable/desactivable

---

### 🔐 Módulo 10 — Seguridad y Auditoría

* Registro de todas las acciones críticas (quién hizo qué y cuándo)
* Log de ventas anuladas con responsable
* Log de ajustes de inventario
* Sesiones con tiempo de expiración configurable
* Protección CSRF en formularios

---

### 🗂️ Estructura MVC sugerida

```
/botica
├── app/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── core/         ← Router, Controller base, Model base
├── config/
│   └── database.php
├── public/
│   ├── index.php     ← Front controller
│   ├── css/
│   └── js/
└── assets/
```

---

### ✅ ¿Por dónde empezamos?

Dime qué módulo quieres implementar primero y si tienes alguna preferencia sobre la estructura de la base de datos, y arrancamos con el código.