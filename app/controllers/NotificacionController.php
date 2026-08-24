<?php
class NotificacionController extends Controller {

    public function __construct() {
        $this->requireAdmin();
    }

    public function index() {
        $invModel = $this->model('Inventario');
        $lotesVencer = $invModel->getLotesProximosVencer(90); // a 90 dias
        $stockBajo = $invModel->getProductosBajoStock(20);    // stock <= 20
        
        $data = [
            'title' => 'Centro de Alertas Sanitarias',
            'lotes' => $lotesVencer,
            'bajos' => $stockBajo
        ];
        
        $this->view('notificaciones/index', $data);
    }
}
