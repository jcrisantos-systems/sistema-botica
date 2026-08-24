<?php
// Modelo del "Restablecer a Estado de Fábrica" del módulo de Configuración.
// Ejecuta borrados reales e irreversibles; toda la validación de identidad/confirmación
// (contraseña, palabra "RESTABLECER", rol Administrador, CSRF) vive en el controlador,
// que solo debe llamar a ejecutar() cuando ya validó todo eso.
class SistemaReset {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // $opcion: 'total' (Limpieza Total para Producción) o 'transacciones'
    // (Limpiar Transacciones y Mantener Catálogos). Devuelve true/false.
    //
    // Nota: se usa DELETE (no TRUNCATE) en todas las tablas porque TRUNCATE hace un
    // COMMIT implícito en MySQL/InnoDB y no puede revertirse con rollBack(); con DELETE
    // toda la operación queda realmente atómica dentro de una única transacción.
    public function ejecutar($opcion) {
        try {
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            $this->conn->beginTransaction();

            $this->limpiarHistorialOperativo();

            if ($opcion === 'total') {
                $this->limpiarCatalogos();
            }

            $this->conn->commit();
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->conn->exec("SET FOREIGN_KEY_CHECKS = 1");
            error_log('Error en SistemaReset::ejecutar: ' . $e->getMessage());
            return false;
        }
    }

    // Común a ambas opciones: vacía el historial operativo (ventas, compras, caja,
    // kardex, devoluciones, lotes y auditorías de inventario físico). Los logs de
    // auditoría del sistema (audit_accesos / audit_acciones) se preservan a propósito:
    // un reset no debe borrar su propia bitácora de seguridad.
    private function limpiarHistorialOperativo() {
        $tablas = [
            'venta_detalles', 'ventas',
            'compras_devolucion_detalles', 'compras_devoluciones',
            'compra_detalles', 'compras',
            'caja_movimientos', 'cajas',
            'kardex',
            'inventario_auditoria_detalles', 'inventario_auditorias',
            'inventario_lotes',
        ];
        foreach ($tablas as $tabla) {
            $this->conn->exec("DELETE FROM `$tabla`");
        }

        // Sin lotes/kardex que lo respalden, el stock físico y los puntos de
        // fidelización acumulados por ventas ya eliminadas quedarían inconsistentes.
        $this->conn->exec("UPDATE productos SET stock_actual = 0");
        $this->conn->exec("UPDATE clientes SET puntos_acumulados = 0");
    }

    // Solo para la Opción A (Limpieza Total): además del historial operativo,
    // elimina el catálogo maestro completo (productos, categorías, laboratorios,
    // proveedores), los clientes y los usuarios secundarios.
    private function limpiarCatalogos() {
        $tablas = ['productos', 'categorias', 'laboratorios', 'proveedores'];
        foreach ($tablas as $tabla) {
            $this->conn->exec("DELETE FROM `$tabla`");
        }

        // Se preserva el id=1 en ambas tablas porque el resto del sistema depende de
        // que siempre existan: el Administrador principal (no se puede desactivar,
        // ver UsuarioController::toggle) y el cliente "Público en General" (usado
        // por defecto en el POS y protegido explícitamente en Cliente::delete()).
        $this->conn->exec("DELETE FROM clientes WHERE id != 1");
        $this->conn->exec("DELETE FROM usuarios WHERE id != 1");

        $existeClientePublico = $this->conn->query("SELECT id FROM clientes WHERE id = 1")->fetch();
        if (!$existeClientePublico) {
            $this->conn->exec("INSERT INTO clientes (id, tipo_documento, num_documento, nombres, telefono, direccion, puntos_acumulados, estado)
                                VALUES (1, 'Sin Documento', '00000000', 'Cliente Público en General', '', NULL, 0, 1)");
        } else {
            $this->conn->exec("UPDATE clientes SET puntos_acumulados = 0, estado = 1 WHERE id = 1");
        }
    }
}
