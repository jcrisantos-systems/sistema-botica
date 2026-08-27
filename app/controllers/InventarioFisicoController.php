<?php
class InventarioFisicoController extends Controller {

    public function __construct() {
        $this->requireRole([1, 4]);
    }

    public function index() {
        $modelo = $this->model('Inventario');
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->query("SELECT a.*, u.usuario FROM inventario_auditorias a 
                              INNER JOIN usuarios u ON a.id_usuario = u.id 
                              ORDER BY a.fecha_inicio DESC");
        $auditorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->view('inventario_fisico/index', [
            'title' => 'Toma de Inventario Físico',
            'auditorias' => $auditorias
        ]);
    }

    public function iniciar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $modelo = $this->model('Inventario');
            $obs = $_POST['observaciones'] ?? '';
            $id = $modelo->iniciarAuditoria($_SESSION['user_id'], $obs);
            
            if ($id) {
                $this->logAccion('Inventario', 'CREAR', "Inicio de auditoría de inventario físico ID #$id");
                header('Location: ' . BASE_URL . 'inventariofisico/conteo/' . $id);
                exit;
            }
            $this->flash('error', "No se pudo iniciar la auditoría de inventario. Por favor, intenta nuevamente.");
        }
        header('Location: ' . BASE_URL . 'inventariofisico/index');
        exit;
    }

    public function conteo($id) {
        $modelo = $this->model('Inventario');
        $detalles = $modelo->getDetallesAuditoria($id);
        
        $db = new Database();
        $audit = $db->getConnection()->prepare("SELECT * FROM inventario_auditorias WHERE id = ?");
        $audit->execute([$id]);
        $auditoria = $audit->fetch(PDO::FETCH_ASSOC);

        if (!$auditoria || $auditoria['estado'] !== 'Abierta') {
            header('Location: ' . BASE_URL . 'inventariofisico/index');
            exit;
        }

        $this->view('inventario_fisico/conteo', [
            'title' => 'Realizando Conteo Físico',
            'auditoria' => $auditoria,
            'detalles' => $detalles
        ]);
    }

    public function finalizar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_auditoria'])) {
            $this->verifyCsrf();
            $modelo = $this->model('Inventario');
            $id_audit = (int)$_POST['id_auditoria'];
            $conteos = $_POST['conteo'] ?? []; // Array [id_lote => valor]

            $res = $modelo->finalizarAuditoria($id_audit, $conteos, $_SESSION['user_id']);
            
            if ($res === true) {
                $this->logAccion('Inventario', 'FINALIZAR', "Cierre y ajuste automático de stock por Auditoría ID #$id_audit");
                $this->flash('success', "Auditoría finalizada y stock actualizado con éxito.");
            } else {
                $this->flash('error', "No se pudo finalizar la auditoría: " . $res);
            }
        }
        header('Location: ' . BASE_URL . 'inventariofisico/index');
        exit;
    }
}
