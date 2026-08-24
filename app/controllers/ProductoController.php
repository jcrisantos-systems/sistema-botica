<?php
class ProductoController extends Controller {

    public function __construct() {
        // Solo administradores gestionan el catálogo maestro
        $this->requireAdmin();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Producto');
        $productos = $modelo->getAllAdmin();
        
        $this->view('productos/index', ['title' => 'Productos', 'productos' => $productos]);
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        // Cargar combos para el formulario
        $catModel = $this->model('Categoria');
        $labModel = $this->model('Laboratorio');
        
        $data = [
            'title' => 'Nuevo Producto',
            'categorias' => $catModel->getAll(),
            'laboratorios' => $labModel->getAll(),
            'producto' => null // null significa que es creación
        ];
        
        $this->view('productos/form', $data);
    }
    
    public function edit($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Producto');
        $catModel = $this->model('Categoria');
        $labModel = $this->model('Laboratorio');
        
        $data = [
            'title' => 'Editar Producto',
            'categorias' => $catModel->getAll(),
            'laboratorios' => $labModel->getAll(),
            'producto' => $modelo->getById($id)
        ];
        
        $this->view('productos/form', $data);
    }

    public function save() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $modelo = $this->model('Producto');
            
            $data = [
                'codigo_barras' => $_POST['codigo_barras'] ?: null,
                'nombre_generico' => $_POST['nombre_generico'],
                'nombre_comercial' => $_POST['nombre_comercial'],
                'concentracion' => $_POST['concentracion'],
                'forma_farmaceutica' => $_POST['forma_farmaceutica'],
                'id_laboratorio' => empty($_POST['id_laboratorio']) ? null : $_POST['id_laboratorio'],
                'id_categoria' => empty($_POST['id_categoria']) ? null : $_POST['id_categoria'],
                'precio_compra' => $_POST['precio_compra'],
                'precio_venta' => $_POST['precio_venta'],
                'margen_ganancia' => $_POST['margen_ganancia'],
                'unidad_medida' => $_POST['unidad_medida'],
                'requiere_receta' => isset($_POST['requiere_receta']) ? 1 : 0,
                'stock_minimo' => $_POST['stock_minimo'] ?: 10,
                'fraccionable' => isset($_POST['fraccionable']) ? 1 : 0,
                'unidades_por_caja' => isset($_POST['fraccionable']) && !empty($_POST['unidades_por_caja']) ? $_POST['unidades_por_caja'] : 1,
                'unidad_fraccion' => isset($_POST['fraccionable']) && !empty($_POST['unidad_fraccion']) ? $_POST['unidad_fraccion'] : null,
                'precio_fraccion' => isset($_POST['fraccionable']) && !empty($_POST['precio_fraccion']) ? $_POST['precio_fraccion'] : 0.00,
                'id' => $_POST['id'] ?? null
            ];
            
            try {
                if (empty($data['id'])) {
                    $modelo->create($data);
                    $this->logAccion('Productos', 'CREAR', "Nuevo producto creado: " . $data['nombre_comercial']);
                    $this->flash('success', "Producto creado exitosamente.");
                } else {
                    $modelo->update($data);
                    $this->logAccion('Productos', 'EDITAR', "Producto ID #" . $data['id'] . " editado. Precios: S/ " . $data['precio_venta'] . " (Caja) / S/ " . $data['precio_fraccion'] . " (Frac)");
                    $this->flash('success', "Producto actualizado exitosamente.");
                }
            } catch (PDOException $e) {
                $this->flash('error', $this->handleDbException($e, ['codigo_barras' => 'código de barras']));
            }
        }
        header('Location: ' . BASE_URL . 'producto/index');
        exit;
    }

    public function toggle($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'producto/index'); exit; }
        $this->verifyCsrf();

        $modelo = $this->model('Producto');
        $modelo->toggleEstado($id);
        $this->logAccion('Productos', 'ESTADO', "Se cambió el estado (Activo/Inactivo) del producto ID #" . $id);
        
        header('Location: ' . BASE_URL . 'producto/index');
    }
}
