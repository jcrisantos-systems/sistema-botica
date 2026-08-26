<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta #<?php echo htmlspecialchars($data['venta']['num_comprobante']); ?></title>
    <style>
        @page { size: 80mm auto; margin: 0; }

        body {
            font-family: 'Courier New', Courier, monospace; font-size: 12px;
            margin: 0; padding: 20px 0; background-color: #e5e7eb;
            display: flex; justify-content: center; box-sizing: border-box;
        }
        .ticket {
            position: relative; overflow: hidden;
            width: 72mm; max-width: 72mm; background-color: #fff;
            padding: 3mm; box-sizing: border-box;
            box-shadow: 0 2px 10px rgba(0,0,0,0.18); border-radius: 4px;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; font-size: 11px; table-layout: fixed; }
        th { border-bottom: 1px dashed #000; border-top: 1px dashed #000; padding: 4px 0; text-align: left; }
        td { padding: 3px 0; word-wrap: break-word; overflow-wrap: break-word; }
        .divider { border-bottom: 1px dashed #000; margin: 10px 0; }
        .total-row { font-size: 14px; font-weight: bold; }

        .anulada-banner {
            background-color: #dc2626; color: #fff; text-align: center;
            font-weight: bold; font-size: 11px; letter-spacing: .3px;
            padding: 6px 4px; margin-bottom: 8px; border-radius: 3px;
        }
        .anulada-estado { color: #dc2626; font-weight: bold; }
        .watermark {
            position: absolute; top: 45%; left: 50%; width: 140%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 34px; font-weight: 800; color: rgba(220, 38, 38, 0.14);
            text-align: center; pointer-events: none; white-space: nowrap;
        }

        /* Ajustes exclusivos de impresión */
        @media print {
            body { background-color: #fff; padding: 0; }
            .no-print { display: none !important; }
            .ticket { box-shadow: none; border-radius: 0; margin: 0; }
        }
    </style>
</head>
<body>
    <?php $esAnulada = ($data['venta']['estado'] ?? '') === 'Anulada'; ?>
    <div class="ticket">
        <?php if ($esAnulada): ?>
        <div class="watermark">ANULADA</div>
        <?php endif; ?>

        <div class="no-print center" style="margin-bottom: 15px;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #059669; color:#fff; border:none; font-weight:bold; border-radius: 8px;">IMPRIMIR TICKET</button>
            <button onclick="window.close()" style="padding: 10px; cursor: pointer; background: #e5e7eb; color:#333; border:none; border-radius: 8px;">X Cerrar</button>
        </div>

        <?php if ($esAnulada): ?>
        <div class="anulada-banner">COMPROBANTE ANULADO &mdash; NO V&Aacute;LIDO PARA COBRO</div>
        <?php endif; ?>

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
        <p style="margin: 3px 0;">Estado: <span class="<?php echo $esAnulada ? 'anulada-estado' : ''; ?>"><?php echo htmlspecialchars($data['venta']['estado'] ?? 'Emitida'); ?></span></p>

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
</body>
</html>
