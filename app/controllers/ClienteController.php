<?php
class ClienteController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Cliente');
        $clientes = $modelo->getAll();
        
        $this->view('clientes/index', ['title' => 'Directorio de Clientes', 'clientes' => $clientes]);
    }

    public function save() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $modelo = $this->model('Cliente');
            $data = [
                'tipo_documento' => $_POST['tipo_documento'],
                'num_documento' => $_POST['num_documento'],
                'nombres' => $_POST['nombres'],
                'telefono' => $_POST['telefono'],
                'direccion' => $_POST['direccion'],
                'id' => $_POST['id'] ?? null
            ];
            
            try {
                if (empty($data['id'])) {
                    $modelo->create($data);
                    $this->flash('success', "Cliente creado exitosamente.");
                } else {
                    $modelo->update($data);
                    $this->flash('success', "Cliente actualizado exitosamente.");
                }
            } catch (PDOException $e) {
                $this->flash('error', $this->handleDbException($e, ['num_documento' => 'número de documento']));
            }
        }
        header('Location: ' . BASE_URL . 'cliente/index');
        exit;
    }

    public function delete($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'cliente/index'); exit; }
        $this->verifyCsrf();

        $modelo = $this->model('Cliente');
        try {
            $modelo->delete($id);
            $this->flash('success', "Cliente eliminado correctamente.");
        } catch (PDOException $e) {
            $this->flash('error', $this->handleDbException($e));
        }

        header('Location: ' . BASE_URL . 'cliente/index');
        exit;
    }
}
