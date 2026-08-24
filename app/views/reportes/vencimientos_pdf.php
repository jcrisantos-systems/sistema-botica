<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Lotes por Vencer PDF</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; color: #333; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { max-height: 60px; max-width: 150px; object-fit: contain; }
        .info-empresa { text-align: right; }
        .info-empresa h2 { margin: 0 0 5px 0; font-size: 18px; color: #1FA95B; }
        .info-empresa p { margin: 0; font-size: 11px; color: #666; }
        h1.titulo-reporte { text-align: center; font-size: 16px; margin: 20px 0; padding: 5px; background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        td { font-size: 11px; }
        .text-center { text-align: center; }
        .badge-rojo { background: #dc3545; color:#fff; padding:3px 8px; border-radius:3px; font-weight:bold; }
        .badge-naranja { background: #ffc107; color:#000; padding:3px 8px; border-radius:3px; font-weight:bold; }
        
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

    <h1 class="titulo-reporte">REPORTE DE ALERTA SANITARIA: LOTES PRÓXIMOS A VENCER <br><small style="font-size:11px; font-weight:normal;">Alerta Preventiva a 90 días</small></h1>

    <table>
        <thead>
            <tr>
                <th>MEDICAMENTO / PRODUCTO</th>
                <th>CÓDIGO DE LOTE</th>
                <th class="text-center">FECHA VENCIMIENTO</th>
                <th class="text-center">ESTADO / DÍAS RESTANTES</th>
                <th class="text-center">STOCK AFECTADO</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(empty($data['lotes'])): ?>
                <tr><td colspan="5" style="text-align:center; padding: 20px;">✓ Excelente. No hay lotes próximos a vencer en los siguientes 90 días.</td></tr>
            <?php else: ?>
                <?php 
                $hoy = new DateTime();
                foreach($data['lotes'] as $l): 
                    $fv = new DateTime($l['fecha_vencimiento']);
                    $diff = $hoy->diff($fv)->days;
                    $isVencido = $fv < $hoy;
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($l['producto']); ?></strong></td>
                    <td><?php echo htmlspecialchars($l['lote']); ?></td>
                    <td class="text-center"><?php echo date('d/m/Y', strtotime($l['fecha_vencimiento'])); ?></td>
                    <td class="text-center">
                        <?php if($isVencido): ?>
                            <span class="badge-rojo">¡VENCIDO!</span>
                        <?php elseif($diff <= 30): ?>
                            <span class="badge-rojo">CRÍTICO: <?php echo $diff; ?> DÍAS</span>
                        <?php else: ?>
                            <span class="badge-naranja">En <?php echo $diff; ?> días</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><strong><?php echo htmlspecialchars($l['stock']); ?> unid.</strong></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="font-size: 11px; font-style: italic; color: #555;">
        * Por normativa de salud (DIGEMID u homólogos locales), los productos vencidos deben ser separados inmediatamente a cuarentena. Aquellos con menos de 30 días de vigencia sugieren ser mermados o retirados de escaparate comercial.
    </p>

    <div style="margin-top: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px;">
        Documento generado por el Sistema de Botica el <?php echo date('d/m/Y H:i:s'); ?>. Todos los derechos reservados.
    </div>
</body>
</html>
