<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas PDF</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; color: #333; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { max-height: 60px; max-width: 150px; object-fit: contain; }
        .info-empresa { text-align: right; }
        .info-empresa h2 { margin: 0 0 5px 0; font-size: 18px; color: #1FA95B; }
        .info-empresa p { margin: 0; font-size: 11px; color: #666; }
        h1.titulo-reporte { text-align: center; font-size: 16px; margin: 20px 0; padding: 5px; background: #f4f4f4; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        td { font-size: 11px; }
        .text-right { text-align: right; }
        .totales-box { width: 300px; float: right; border: 1px solid #000; padding: 10px; background: #fff; }
        .totales-box p { margin: 5px 0; display: flex; justify-content: space-between; }
        .totales-box .gran-total { font-size: 14px; font-weight: bold; border-top: 1px solid #ccc; padding-top: 5px; margin-top: 5px; }
        
        .no-print { text-align: center; margin-bottom: 20px; }
        .btn-print { background: #000; color: #fff; padding: 10px 20px; font-weight: bold; border: none; cursor: pointer; border-radius: 5px; }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / GUARDAR COMO PDF</button>
        <button onclick="window.close()" style="padding: 10px; margin-left: 10px;">Cerrar</button>
    </div>

    <div class="cabecera">
        <div>
            <?php 
                $logoUrl = !empty($data['config']['logo']['valor']) ? $data['config']['logo']['valor'] : BASE_URL . 'img/default_logo.png';
            ?>
            <img src="<?php echo htmlspecialchars($logoUrl); ?>" class="logo" alt="Logo">
        </div>
        <div class="info-empresa">
            <h2><?php echo htmlspecialchars($data['config']['nombre_botica']['valor']); ?></h2>
            <p>RUC: <?php echo htmlspecialchars($data['config']['ruc']['valor']); ?></p>
            <p><?php echo htmlspecialchars($data['config']['direccion']['valor']); ?></p>
            <p>Tel: <?php echo htmlspecialchars($data['config']['telefono']['valor']); ?></p>
        </div>
    </div>

    <h1 class="titulo-reporte">REPORTE FINANCIERO DE VENTAS <br><small style="font-size:11px; font-weight:normal;">Desde: <?php echo date('d/m/Y', strtotime($data['fecha_inicio'])); ?> - Hasta: <?php echo date('d/m/Y', strtotime($data['fecha_fin'])); ?></small></h1>

    <table>
        <thead>
            <tr>
                <th>FECHA</th>
                <th>TICKET / COMPR.</th>
                <th>CAJERO</th>
                <th>MÉTODO PAGO</th>
                <th class="text-right">SUBTOTAL</th>
                <th class="text-right">IGV</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sum_sub = 0; $sum_igv = 0; $sum_tot = 0;
            if(empty($data['ventas'])): ?>
                <tr><td colspan="7" style="text-align:center;">No se registraron ventas en este periodo.</td></tr>
            <?php else: ?>
                <?php foreach($data['ventas'] as $v): 
                    $sum_sub += $v['subtotal'];
                    $sum_igv += $v['igv'];
                    $sum_tot += $v['total'];
                ?>
                <tr>
                    <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_venta'])); ?></td>
                    <td><?php echo htmlspecialchars($v['tipo_comprobante'] . ' ' . $v['num_comprobante']); ?></td>
                    <td><?php echo htmlspecialchars($v['cajero']); ?></td>
                    <td><?php echo htmlspecialchars($v['metodo_pago']); ?></td>
                    <td class="text-right"><?php echo number_format($v['subtotal'], 2); ?></td>
                    <td class="text-right"><?php echo number_format($v['igv'], 2); ?></td>
                    <td class="text-right"><strong><?php echo number_format($v['total'], 2); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="overflow: hidden;">
        <div class="totales-box">
            <p><span>Subtotal General:</span> <span><?php echo htmlspecialchars($data['config']['moneda']['valor']); ?> <?php echo number_format($sum_sub, 2); ?></span></p>
            <p><span>Total Impuestos I.G.V:</span> <span><?php echo htmlspecialchars($data['config']['moneda']['valor']); ?> <?php echo number_format($sum_igv, 2); ?></span></p>
            <p class="gran-total"><span>INGRESOS BRUTOS:</span> <span><?php echo htmlspecialchars($data['config']['moneda']['valor']); ?> <?php echo number_format($sum_tot, 2); ?></span></p>
        </div>
    </div>
    
    <div style="margin-top: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px;">
        Documento generado por el Sistema de Botica el <?php echo date('d/m/Y H:i:s'); ?>. Todos los derechos reservados.
    </div>
</body>
</html>
