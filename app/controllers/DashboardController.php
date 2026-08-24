<?php
class DashboardController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        // Si es Vendedor (rol_id = 2), no tiene acceso al dashboard gerencial
        if ($_SESSION['rol_id'] == 2) {
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
