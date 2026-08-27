<?php
class DashboardController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        if ($_SESSION['rol_id'] == 1) {
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
        } elseif ($_SESSION['rol_id'] == 2) {
            $ventaModelo = $this->model('Venta');
            $invModelo = $this->model('Inventario');

            $data = [
                'title' => 'Mi Dashboard',
                'ventasHoy' => $ventaModelo->getMetricasHoyPorUsuario($_SESSION['user_id']),
                'lotesVencer' => $invModelo->getLotesProximosVencer(90)
            ];

            $this->view('dashboard/farmaceutico', $data);
        } elseif ($_SESSION['rol_id'] == 3) {
            $ventaModelo = $this->model('Venta');
            $cajaModelo = $this->model('Caja');

            $data = [
                'title' => 'Mi Dashboard',
                'ventasHoy' => $ventaModelo->getMetricasHoyPorUsuario($_SESSION['user_id']),
                'cajaAbierta' => $cajaModelo->getCajaAbiertaPorUsuario($_SESSION['user_id'])
            ];

            $this->view('dashboard/cajero', $data);
        } elseif ($_SESSION['rol_id'] == 4) {
            $invModelo = $this->model('Inventario');

            $data = [
                'title' => 'Mi Dashboard',
                'lotesVencer' => $invModelo->getLotesProximosVencer(90),
                'stockBajo' => $invModelo->getProductosBajoStockMinimo()
            ];

            $this->view('dashboard/almacenero', $data);
        } else {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }
}
