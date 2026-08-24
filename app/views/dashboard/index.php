<div class="page-content">
    <div class="page-header">
        <h1 class="page-title">Hola, <?php echo htmlspecialchars(isset($_SESSION['nombre']) ? explode(' ', $_SESSION['nombre'])[0] : 'Admin', ENT_QUOTES, 'UTF-8'); ?>!</h1>
        <div class="page-subtitle">Este es el resumen general de la botica al día de hoy.</div>
    </div>

    <!-- TARJETAS SUPERIORES -->
    <div class="row g-4 mb-4">
        <!-- Ingresos Totales -->
        <div class="col-md-3">
            <div class="card-metric" style="background: linear-gradient(135deg, var(--accent-primary) 0%, var(--success) 100%); color: white; border: none;">
                <div class="metric-header">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9;">Ingresos Hoy</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 8px; letter-spacing: -1px;">
                    S/ <?php echo number_format($data['metricas']['ingresos_hoy'], 2); ?>
                </div>
                <div style="font-size: 13px; font-weight: 500; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px;">
                    <i class="bi bi-arrow-up-right-circle-fill"></i> Ingresos del día actual
                </div>
            </div>
        </div>
        
        <!-- Medicamentos Activos / Riesgo -->
        <div class="col-md-3">
            <div class="card-metric" style="background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%); color: white; border: none;">
                <div class="metric-header">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9;">Stock Botica</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="bi bi-capsule-pill"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 8px; letter-spacing: -1px;">
                    <?php echo number_format($data['metricas']['productos_total']); ?>
                </div>
                <?php if($data['metricas']['lotes_riesgo'] > 0): ?>
                <div style="font-size: 13px; font-weight: 500; background: rgba(230, 57, 70, 0.9); display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $data['metricas']['lotes_riesgo']; ?> lotes por vencer
                </div>
                <?php else: ?>
                <div style="font-size: 13px; font-weight: 500; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px;">
                    <i class="bi bi-check-circle-fill"></i> Inventario saludable
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ventas Hoy -->
        <div class="col-md-3">
             <div class="card-metric" style="background: linear-gradient(135deg, #F4A261 0%, #E67E22 100%); color: white; border: none;">
                <div class="metric-header">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9;">Tickets / Ventas</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="bi bi-receipt"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 8px; letter-spacing: -1px;">
                    <?php echo number_format($data['metricas']['ventas_hoy']); ?>
                </div>
                <div style="font-size: 13px; font-weight: 500; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px;">
                    <i class="bi bi-clock-history"></i> Transacciones de hoy
                </div>
            </div>
        </div>

        <!-- Clientes Activos -->
        <div class="col-md-3">
            <div class="card-metric" style="background: linear-gradient(135deg, #8E44AD 0%, #9B59B6 100%); color: white; border: none;">
                <div class="metric-header">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9;">Clientes Registrados</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 8px; letter-spacing: -1px;">
                    <?php echo number_format($data['metricas']['clientes_total']); ?>
                </div>
                <div style="font-size: 13px; font-weight: 500; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px;">
                    <i class="bi bi-person-plus-fill"></i> Base de datos total
                </div>
            </div>
        </div>
    </div>

    <!-- ZONA GRÁFICOS -->
    <div class="row g-4">
        <!-- Gráfico Crecimiento Ventas -->
        <div class="col-md-8">
            <div class="card-metric h-100">
                <h5 style="color: var(--text-primary); font-size: 16px; margin-bottom: 25px; font-weight: 700;">Ingresos de los últimos 7 días</h5>
                <div style="height: 320px;">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico Medios de Pago -->
        <div class="col-md-4">
            <div class="card-metric h-100 d-flex flex-column">
                <h5 style="color: var(--text-primary); font-size: 16px; margin-bottom: 25px; font-weight: 700;">Distribución de Ingresos</h5>
                
                <div style="flex-grow: 1; display:flex; align-items:center; justify-content:center; position:relative; min-height: 220px;">
                    <canvas id="pieChart"></canvas>
                </div>
                
                <div class="mt-4 pt-3 border-top">
                    <!-- Detalle rápido debajo del pie chart -->
                    <?php 
                        $total_pagos = 0;
                        foreach($data['pagos'] as $p) $total_pagos += (float)$p['value'];
                        if($total_pagos == 0) $total_pagos = 1; // Evitar división por cero
                    ?>
                    <?php foreach($data['pagos'] as $i => $p): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary font-weight-500" style="font-size: 13px;">
                                <i class="bi bi-circle-fill me-1" style="font-size: 8px; color: <?php echo ['#00A896', '#02C39A', '#3498DB', '#F4A261'][$i % 4]; ?>"></i>
                                <?php echo htmlspecialchars($p['label']); ?>
                            </span>
                            <span class="font-weight-600" style="font-size: 13px;">S/ <?php echo number_format($p['value'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lógica JavaScript de ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Variables Generadas desde PHP
    const grafLabels = <?php echo json_encode($data['grafico']['labels']); ?>;
    const grafData = <?php echo json_encode($data['grafico']['data']); ?>;

    <?php
        $pagosLabels = [];
        $pagosData = [];
        foreach($data['pagos'] as $p) {
            $pagosLabels[] = $p['label'];
            $pagosData[] = (float)$p['value'];
        }
    ?>
    const pieLabels = <?php echo json_encode($pagosLabels); ?>;
    const pieData = <?php echo json_encode($pagosData); ?>;

    // Config Inicial UI ChartJS (Claro/Médico)
    Chart.defaults.color = '#7F8C8D';
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 12;

    // 1. Gráfico de Barras - Ingresos 7 días
    const ctxBar = document.getElementById('barChart').getContext('2d');
    
    // Crear Gradiente para las barras
    let gradientBar = ctxBar.createLinearGradient(0, 0, 0, 400);
    gradientBar.addColorStop(0, '#00A896');
    gradientBar.addColorStop(1, '#02C39A');

    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: grafLabels,
            datasets: [{
                label: 'Ingresos S/',
                data: grafData,
                backgroundColor: gradientBar,
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#2C3E50',
                    padding: 12,
                    titleFont: { size: 14, family: 'Inter' },
                    bodyFont: { size: 14, weight: 'bold' },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'S/ ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#E2E8F0', drawBorder: false },
                    border: { display: false },
                    ticks: {
                        callback: function(value) { return 'S/ ' + value; },
                        padding: 10
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    border: { display: false }
                }
            }
        }
    });

    // 2. Gráfico Donut - Medios de Pago
    const ctxPie = document.getElementById('pieChart').getContext('2d');
    
    let realData = pieData;
    let realLabels = pieLabels;
    if(realData.length === 0) {
        realData = [100];
        realLabels = ['Sin Movimientos'];
    }

    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: realLabels,
            datasets: [{
                data: realData,
                backgroundColor: ['#00A896', '#02C39A', '#3498DB', '#F4A261', '#E63946'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    display: false // Oculto el legend automático para usar el HTML de abajo
                },
                tooltip: {
                    backgroundColor: '#2C3E50',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return ' S/ ' + context.parsed.toFixed(2);
                        }
                    }
                }
            }
        }
    });
</script>
