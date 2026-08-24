<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta #<?php echo htmlspecialchars($data['venta']['num_comprobante']); ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 0; background-color: #f0f0f0; }
        .ticket { width: 80mm; max-width: 80mm; background-color: white; margin: 0 auto; padding: 5mm; box-sizing: border-box; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; font-size: 11px; }
        th { border-bottom: 1px dashed #000; border-top: 1px dashed #000; padding: 4px 0; text-align: left; }
        td { padding: 3px 0; }
        .divider { border-bottom: 1px dashed #000; margin: 10px 0; }
        .total-row { font-size: 14px; font-weight: bold; }
        
        /* Ocultar elementos en la impresión */
        @media print {
            body { background-color: white; }
            .no-print { display: none !important; }
            .ticket { margin: 0; padding: 0; width: 100%; }
            @page { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="no-print center" style="margin-bottom: 15px;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #000; color:#fff; border:none; font-weight:bold;">IMPRIMIR TICKET</button>
            <button onclick="window.close()" style="padding: 10px; cursor: pointer; background: #ccc; border:none;">X Cerrar</button>
        </div>

        <div class="center">
            <h2 style="margin: 0; padding: 0;"><?php echo htmlspecialchars($data['config']['nombre_botica']['valor']); ?></h2>
            <p style="margin: 3px 0;">RUC: <?php echo htmlspecialchars($data['config']['ruc']['valor']); ?></p>
            <p style="margin: 3px 0;"><?php echo htmlspecialchars($data['config']['direccion']['valor']); ?></p>
            <?php if(!empty($data['config']['telefono']['valor'])): ?>
            <p style="margin: 3px 0;">Tel: <?php echo htmlspecialchars($data['config']['telefono']['valor']); ?></p>
            <?php endif; ?>
            <p style="margin: 3px 0;">--------------------------------</p>
        </div>
        
        <p style="margin: 3px 0;"><span class="bold">TICKET BOLETA ELECTRÓNICA</span></p>
        <p style="margin: 3px 0;">Nro: B001-<?php echo htmlspecialchars($data['venta']['num_comprobante']); ?></p>
        <p style="margin: 3px 0;">Fecha: <?php echo date('d/m/Y H:i:s', strtotime($data['venta']['fecha_venta'])); ?></p>
        <p style="margin: 3px 0;">Cajero: <?php echo htmlspecialchars($data['venta']['cajero']); ?></p>
        <p style="margin: 3px 0;">Cliente: <?php echo htmlspecialchars($data['venta']['cliente']); ?></p>

        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    <th>CANT</th>
                    <th>PRODUCTO</th>
                    <th class="right">SUBT</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($data['detalles'] as $det): ?>
                <tr>
                    <td valign="top"><?php echo $det['cantidad']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($det['nombre_comercial']); ?>
                        <?php if(!empty($det['tipo_unidad']) && $det['tipo_unidad'] !== 'Unidad'): ?>
                            <small class="bold" style="background:#000; color:#fff; padding:1px 3px; border-radius:3px;">(<?php echo htmlspecialchars($det['tipo_unidad']); ?>)</small>
                        <?php endif; ?>
                        <br>
                        <small>P.U: <?php echo number_format($det['precio_unitario'], 2); ?></small>
                    </td>
                    <td valign="top" class="right"><?php echo number_format($det['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="divider"></div>

        <table style="font-size: 12px; margin-top:0;">
            <tr>
                <td>OP. GRAVADA</td>
                <td class="right">S/ <?php echo number_format($data['venta']['subtotal'], 2); ?></td>
            </tr>
            <?php if(isset($data['venta']['descuento']) && $data['venta']['descuento'] > 0): ?>
            <tr>
                <td>DESCUENTO</td>
                <td class="right">-S/ <?php echo number_format($data['venta']['descuento'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>I.G.V. (<?php echo htmlspecialchars($data['config']['igv']['valor']); ?>%)</td>
                <td class="right">S/ <?php echo number_format($data['venta']['igv'], 2); ?></td>
            </tr>
            <tr class="total-row">
                <td>TOTAL PERCIBIDO</td>
                <td class="right">S/ <?php echo number_format($data['venta']['total'], 2); ?></td>
            </tr>
        </table>
        
        <div class="divider"></div>
        
        <table style="font-size: 11px;">
            <tr>
                <td>Forma de Pago:</td>
                <td class="right"><?php echo htmlspecialchars($data['venta']['metodo_pago']); ?></td>
            </tr>
            <tr>
                <td>Pago Recibido:</td>
                <td class="right">S/ <?php echo number_format($data['venta']['pago_recibido'], 2); ?></td>
            </tr>
            <tr>
                <td>Vuelto:</td>
                <td class="right">S/ <?php echo number_format($data['venta']['vuelto'], 2); ?></td>
            </tr>
        </table>

        <div class="center" style="margin-top: 15px;">
            <p>*** GRACIAS POR SU COMPRA ***</p>
            <p>Conserve este ticket para <br>cualquier reclamo.</p>
        </div>
        
        <?php if($data['venta']['id_cliente'] != 1 && (isset($data['venta']['puntos_ganados']) || isset($data['venta']['puntos_usados']))): ?>
        <div class="divider"></div>
        <div class="center" style="font-size:10px; margin-top:5px; border:1px solid #000; padding:5px; border-radius:5px;">
            <p style="margin:2px 0;"><strong>-- CLUB DE CLIENTES --</strong></p>
            <?php if($data['venta']['puntos_ganados'] > 0): ?>
                <p style="margin:2px 0;">Puntos Ganados Hoy: <span class="bold">+<?php echo $data['venta']['puntos_ganados']; ?></span></p>
            <?php endif; ?>
            <?php if($data['venta']['puntos_usados'] > 0): ?>
                <p style="margin:2px 0;">Puntos Usados Hoy: <span class="bold">-<?php echo $data['venta']['puntos_usados']; ?></span></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <br>
    </div>
    
    <script>
        // Auto imprimir al cargar 
        window.onload = function() { 
            window.print(); 
            // window.onafterprint no es compatible en todos los navegadores, pero si lo es, cerrará la ventana automáticamente
            window.onafterprint = function() { window.close(); }
        }
    </script>
</body>
</html>
