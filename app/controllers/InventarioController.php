<?php
class InventarioController extends Controller {

    public function __construct() {
        $this->requireRole([1, 4]);
    }

    public function lotes() {
        $modelo = $this->model('Inventario');
        $lotes = $modelo->getLotesActivos();
        
        $this->view('inventario/lotes', ['title' => 'Fechas de Vencimiento (FEFO)', 'lotes' => $lotes]);
    }

    public function kardex() {
        $modelo = $this->model('Inventario');
        $prodModel = $this->model('Producto');
        
        $id_producto = isset($_GET['producto']) && !empty($_GET['producto']) ? (int)$_GET['producto'] : null;
        
        $movimientos = $modelo->getKardex($id_producto);
        $productos = $prodModel->getAll();
        
        $this->view('inventario/kardex', [
            'title' => 'Kardex General', 
            'movimientos' => $movimientos,
            'productos' => $productos,
            'filtro_producto' => $id_producto
        ]);
    }

    public function entrada_manual() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();

            $id_producto = (int)($_POST['id_producto'] ?? 0);
            $cantidad = (int)($_POST['cantidad'] ?? 0);
            $lote = trim($_POST['lote'] ?? '') ?: 'SIN-LOTE';
            $vencimiento = $_POST['fecha_vencimiento'] ?: date('Y-m-d', strtotime('+365 days'));
            $motivo = "Ajuste/Ingreso Manual: " . trim($_POST['motivo'] ?? '');

            if ($id_producto <= 0 || $cantidad <= 0) {
                $_SESSION['error'] = "Debe seleccionar un producto válido e ingresar una cantidad mayor a cero.";
                header('Location: ' . BASE_URL . 'inventario/kardex');
                exit;
            }

            require_once '../app/config/Database.php';
            $db = new Database();
            $conn = $db->getConnection();

            // Verificar que el producto exista antes de afectar el Kardex
            $chk = $conn->prepare("SELECT id FROM productos WHERE id = :id");
            $chk->bindParam(':id', $id_producto);
            $chk->execute();
            if (!$chk->fetch()) {
                $_SESSION['error'] = "El producto seleccionado no existe.";
                header('Location: ' . BASE_URL . 'inventario/kardex');
                exit;
            }

            $modelo = new Inventario($conn);

            try {
                $conn->beginTransaction();

                $modelo->registrarEntrada($id_producto, $_SESSION['user_id'], $cantidad, $motivo, $lote, $vencimiento);

                $conn->commit();
                $this->logAccion('Inventario', 'AJUSTE_STOCK', "Ajuste manual de stock: $motivo, Cant: $cantidad, Prod ID: $id_producto");
                $_SESSION['success'] = "El ingreso de stock fue insertado en el Kardex y en Lotes satisfactoriamente.";
            } catch (Exception $e) {
                $conn->rollBack();
                $_SESSION['error'] = "Hubo un problema al registrar la entrada de inventario.";
            }
        }

        header('Location: ' . BASE_URL . 'inventario/kardex');
        exit;
    }
}
