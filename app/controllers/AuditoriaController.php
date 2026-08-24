<?php
class AuditoriaController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        // Solo administradores pueden ver auditoría (Asumiendo rol_id = 1 para admin)
        if ($_SESSION['rol_id'] != 1) {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }

        $modelo = $this->model('Auditoria');
        
        $data = [
            'title' => 'Panel de Auditoría y Seguridad',
            'accesos' => $modelo->getAccesos(150),
            'acciones' => $modelo->getAcciones(200)
        ];
        
        $this->view('auditoria/index', $data);
    }
}
