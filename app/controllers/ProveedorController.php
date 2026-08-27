<?php
class ProveedorController extends Controller {

    public function __construct() {
        // Permisos específicos por método (ver cada uno más abajo).
    }

    public function index() {
        $this->requireRole([1, 4]);

        $modelo = $this->model('Proveedor');
        $proveedores = $modelo->getAll();

        $this->view('proveedores/index', ['title' => 'Proveedores', 'proveedores' => $proveedores]);
    }

    public function save() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $modelo = $this->model('Proveedor');
            $data = [
                'ruc' => $_POST['ruc'],
                'razon_social' => $_POST['razon_social'],
                'representante' => $_POST['representante'],
                'telefono' => $_POST['telefono'],
                'direccion' => $_POST['direccion'],
                'id' => $_POST['id'] ?? null
            ];
            
            try {
                if (empty($data['id'])) {
                    $modelo->create($data);
                    $this->flash('success', "Proveedor creado exitosamente.");
                } else {
                    $modelo->update($data);
                    $this->flash('success', "Proveedor actualizado exitosamente.");
                }
            } catch (PDOException $e) {
                $this->flash('error', $this->handleDbException($e, ['ruc' => 'RUC']));
            }
        }
        header('Location: ' . BASE_URL . 'proveedor/index');
        exit;
    }

    public function delete($id) {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . 'proveedor/index'); exit; }
        $this->verifyCsrf();

        $modelo = $this->model('Proveedor');
        try {
            $modelo->delete($id);
            $this->flash('success', "Proveedor eliminado correctamente.");
        } catch (PDOException $e) {
            $this->flash('error', $this->handleDbException($e));
        }

        header('Location: ' . BASE_URL . 'proveedor/index');
        exit;
    }
}
