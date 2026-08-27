<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($tituloTicket, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        /* Ancho de ticket: pensado para una impresora térmica de 80mm (aún no se tiene
           una física). Para cambiar a 58mm más adelante, ajustar --ticket-width Y el
           "size" de @page (los navegadores no soportan variables CSS dentro de @page). */
        :root { --ticket-width: 80mm; }
        @page { size: 80mm auto; margin: 0; }
        body { margin: 0; padding: 10px; font-family: 'Courier New', Courier, monospace; font-size: 12px; width: var(--ticket-width); }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 10px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        @media print { body { width: var(--ticket-width); margin: 0; padding: 0; } }
    </style>
</head>
<body onload="window.print()">

<div class="center bold" style="font-size: 16px; margin-bottom:5px;">MI BOTICA</div>
<div class="center">TICKET DE ARQUEO CAJA</div>
<div class="center bold" style="font-size: 14px; margin-top:5px;">#CAJ-<?php echo str_pad($caja['id'], 6, '0', STR_PAD_LEFT); ?></div>
<div class="line"></div>

<div class="row"><span>Cajero:</span> <span class="bold"><?php echo htmlspecialchars($caja['nombres'] . ' ' . $caja['apellidos']); ?></span></div>
<div class="row"><span>Apertura:</span> <span><?php echo date('d/m/Y H:i', strtotime($caja['fecha_apertura'])); ?></span></div>
<div class="row"><span>Cierre:</span> <span><?php echo date('d/m/Y H:i', strtotime($caja['fecha_cierre'])); ?></span></div>

<div class="line"></div>
<div class="center bold" style="margin-bottom: 5px;">SISTEMA / ESPERADO</div>

<div class="row"><span>Saldo Inicial:</span> <span>S/ <?php echo number_format($caja['monto_inicial'], 2); ?></span></div>
<div class="row"><span>Ventas Efectivo:</span> <span>S/ <?php echo number_format($caja['ingresos_efectivo'], 2); ?></span></div>
<div class="row"><span>Ventas Trans/Tarj:</span> <span>S/ <?php echo number_format($caja['ingresos_transferencia'], 2); ?></span></div>
<div class="row bold"><span>EFECTIVO ESPERADO:</span> <span>S/ <?php echo number_format($caja['monto_final_esperado'], 2); ?></span></div>

<div class="line"></div>
<div class="center bold" style="margin-bottom: 5px;">DECLARADO / REAL</div>

<div class="row bold text-lg"><span>EFECTIVO CONTADO:</span> <span>S/ <?php echo number_format($caja['monto_final_real'], 2); ?></span></div>

<?php if($caja['diferencia'] != 0): ?>
    <div class="row bold" style="margin-top:5px;">
        <span><?php echo $caja['diferencia'] > 0 ? "SOBRANTE:" : "FALTANTE:"; ?></span> 
        <span>S/ <?php echo number_format($caja['diferencia'], 2); ?></span>
    </div>
<?php else: ?>
    <div class="row bold" style="margin-top:5px; text-align:center; width:100%">
        <span>CUADRE PERFECTO</span>
    </div>
<?php endif; ?>

<?php if(!empty($caja['observacion'])): ?>
    <div class="line"></div>
    <div><span class="bold">Obs:</span> <?php echo htmlspecialchars($caja['observacion']); ?></div>
<?php endif; ?>

<div class="line" style="margin-top: 50px;"></div>
<div class="center">Firma de Administrador</div>

<div class="line" style="margin-top: 50px;"></div>
<div class="center">Firma de Cajero</div>

<div class="center" style="margin-top: 20px; font-size:10px;">Impreso: <?php echo date('d/m/Y H:i:s'); ?></div>

</body>
</html>
