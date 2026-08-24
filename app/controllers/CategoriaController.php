<?php
class CategoriaController extends Controller {

    public function __construct() {
        $this->requireAdmin();
    }

    public function index() {
        $modelo = $this->model('Categoria');
        $categorias = $modelo->getAll();

        $this->view('categorias/index', ['title' => 'Categorías', 'categorias' => $categorias]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $modelo = $this->model('Categoria');
            $data = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'id' => $_POST['id'] ?? null
            ];
            
            if (empty($data['id'])) {
                $modelo->create($data);
            } else {
                $modelo->update($data);
            }
        }
        header('Location: ' . BASE_URL . 'categoria/index');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'categoria/index'); exit; }
        $this->verifyCsrf();

        $modelo = $this->model('Categoria');
        $modelo->delete($id);

        header('Location: ' . BASE_URL . 'categoria/index');
    }
}
