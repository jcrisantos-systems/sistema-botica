<?php
class DashboardController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        // Si es Cajero (rol_id = 3), no tiene acceso al dashboard gerencial
        // ("Acceso al Punto de Venta (POS) únicamente" según la tabla `roles` de la BD)
        if ($_SESSION['rol_id'] == 3) {
            header('Location: ' . BASE_URL . 'venta/pos');
            exit;
        }

        $modelo = $this->model('Dashboard');
        $metricas = $modelo->getMetricasHoy();
        $grafico = $modelo->getGraficoSemanal();
        $pagos = $modelo->getMediosPago();

        $data = [
            'title' => 'Dashboard Gerencial',
            'metricas' => $metricas,
            'grafico' => $grafico,
            'pagos' => $pagos
        ];

        $this->view('dashboard/index', $data);
    }
}
