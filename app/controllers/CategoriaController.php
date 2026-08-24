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
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'id' => $_POST['id'] ?? null
            ];

            try {
                if (empty($data['id'])) {
                    $modelo->create($data);
                    $this->flash('success', "Categoría creada exitosamente.");
                } else {
                    $modelo->update($data);
                    $this->flash('success', "Categoría actualizada exitosamente.");
                }
            } catch (PDOException $e) {
                $this->flash('error', $this->handleDbException($e, ['nombre' => 'nombre de categoría']));
            }
        }
        header('Location: ' . BASE_URL . 'categoria/index');
        exit;
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'categoria/index'); exit; }
        $this->verifyCsrf();

        $modelo = $this->model('Categoria');
        try {
            $modelo->delete($id);
            $this->flash('success', "Categoría eliminada correctamente.");
        } catch (PDOException $e) {
            $this->flash('error', $this->handleDbException($e));
        }

        header('Location: ' . BASE_URL . 'categoria/index');
        exit;
    }
}
