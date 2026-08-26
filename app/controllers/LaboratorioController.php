<?php
class LaboratorioController extends Controller {

    public function __construct() {
        $this->requireRole([1, 4]);
    }

    public function index() {
        $modelo = $this->model('Laboratorio');
        $laboratorios = $modelo->getAll();

        $this->view('laboratorios/index', ['title' => 'Laboratorios', 'laboratorios' => $laboratorios]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $modelo = $this->model('Laboratorio');
            $data = [
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'id' => $_POST['id'] ?? null
            ];

            try {
                if (empty($data['id'])) {
                    $modelo->create($data);
                    $this->flash('success', "Laboratorio creado exitosamente.");
                } else {
                    $modelo->update($data);
                    $this->flash('success', "Laboratorio actualizado exitosamente.");
                }
            } catch (PDOException $e) {
                $this->flash('error', $this->handleDbException($e, ['nombre' => 'nombre de laboratorio']));
            }
        }
        header('Location: ' . BASE_URL . 'laboratorio/index');
        exit;
    }

    public function delete($id) {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'laboratorio/index'); exit; }
        $this->verifyCsrf();

        $modelo = $this->model('Laboratorio');
        try {
            $modelo->delete($id);
            $this->flash('success', "Laboratorio eliminado correctamente.");
        } catch (PDOException $e) {
            $this->flash('error', $this->handleDbException($e));
        }

        header('Location: ' . BASE_URL . 'laboratorio/index');
        exit;
    }
}
