<?php
class ClienteController extends Controller {

    public function __construct() {
        // Administrador (1), Farmacéutico (2) y Cajero (3) gestionan clientes;
        // Almacenero (4) queda fuera. delete() además exige Administrador (ver más abajo).
        $this->requireRole([1, 2, 3]);
    }

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
                'tipo_documento' => trim($_POST['tipo_documento']),
                'num_documento' => trim($_POST['num_documento']),
                'nombres' => trim($_POST['nombres']),
                'telefono' => trim($_POST['telefono']),
                'direccion' => trim($_POST['direccion']),
                'id' => $_POST['id'] ?? null
            ];

            // Validación backend de duplicados por (tipo_documento, num_documento). No existe
            // todavía un UNIQUE de BD para esto (pendiente de Fase F, tras depurar los
            // duplicados históricos ya detectados) — este chequeo previene que se sigan
            // creando duplicados nuevos, sin tocar ni bloquear los registros existentes.
            $excludeId = empty($data['id']) ? null : $data['id'];
            if ($modelo->existeCliente($data['tipo_documento'], $data['num_documento'], $excludeId)) {
                $this->flash('error', "Ya existe un cliente registrado con el documento {$data['tipo_documento']} {$data['num_documento']}. Verifica el Directorio de Clientes antes de crear uno nuevo.");
                header('Location: ' . BASE_URL . 'cliente/index');
                exit;
            }

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
        $this->requireAdmin();
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
