<div class="page-content">
    <div class="mb-4">
        <h1 class="page-title">Control de Vencimientos (FEFO)</h1>
        <div class="page-subtitle">Supervisión de lotes activos y próximos a vencer.</div>
    </div>

    <!-- Filtro simple -->
    <div class="card-metric mb-3 p-3">
        <div class="row align-items-center">
            <div class="col-md-5">
                <div class="search-box w-100">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" onkeyup="tableSearch()" placeholder="Buscar producto o lote...">
                </div>
            </div>
            <div class="col-md-7 text-end">
                <button class="btn btn-sm btn-outline-danger" onclick="filtrarAlerta('Rojo')"><i class="bi bi-circle-fill text-danger"></i> Vencidos (0 días)</button>
                <button class="btn btn-sm btn-outline-warning" onclick="filtrarAlerta('Amarillo')"><i class="bi bi-circle-fill text-warning"></i> Riesgo (< 90 días)</button>
                <button class="btn btn-sm btn-outline-success" onclick="filtrarAlerta('Verde')"><i class="bi bi-circle-fill text-success"></i> Sano (> 90 días)</button>
                <button class="btn btn-sm btn-outline-secondary" onclick="filtrarAlerta('')">Todos</button>
            </div>
        </div>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="lotesTable" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th width="150">Lote</th>
                        <th width="150" class="text-center">Vencimiento</th>
                        <th width="120" class="text-center">Estado</th>
                        <th width="100" class="text-end">Stock Real</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['lotes'])): ?>
                    <tr><td colspan="5" class="text-center text-muted">No hay lotes con stock en el almacén.</td></tr>
                    <?php else: ?>
                    <?php 
                        $hoy = new DateTime();
                        foreach($data['lotes'] as $lote): 
                            $venc = new DateTime($lote['fecha_vencimiento']);
                            $diferencia = $hoy->diff($venc);
                            $dias = $diferencia->days;
                            $invertido = $diferencia->invert; // 1 si ya pasó la fecha
                            
                            $estadoCss = 'status-delivered';
                            $estadoText = 'Sano';
                            $estadoFiltro = 'Verde';
                            $colorFila = '';

                            if($invertido == 1 || $dias <= 0) {
                                $estadoCss = 'bg-danger text-white border-0';
                                $estadoText = 'VENCIDO';
                                $estadoFiltro = 'Rojo';
                                $colorFila = 'background-color: rgba(234, 84, 85, 0.05);';
                            } else if($dias <= 90) {
                                $estadoCss = 'bg-warning text-dark border-0';
                                $estadoText = $dias . ' días';
                                $estadoFiltro = 'Amarillo';
                                $colorFila = 'background-color: rgba(255, 193, 7, 0.05);';
                            }
                    ?>
                    <tr style="<?php echo $colorFila; ?>" data-estado="<?php echo $estadoFiltro; ?>">
                        <td>
                            <div style="font-weight:700; color:var(--text-primary);"><?php echo htmlspecialchars($lote['nombre_comercial']); ?></div>
                            <div style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($lote['forma_farmaceutica'] . ' ' . $lote['concentracion']); ?></div>
                        </td>
                        <td style="color:var(--text-secondary); font-family:monospace; font-weight: 600;">
                            <?php echo htmlspecialchars($lote['codigo_lote']); ?>
                        </td>
                        <td class="text-center">
                            <?php echo date('d/m/Y', strtotime($lote['fecha_vencimiento'])); ?>
                        </td>
                        <td class="text-center">
                            <span class="badge-status <?php echo $estadoCss; ?>"><?php echo $estadoText; ?></span>
                        </td>
                        <td class="text-end" style="font-size: 16px; font-weight:700; color: var(--accent-primary);">
                            <?php echo $lote['cantidad_disponible']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function tableSearch() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("searchInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("lotesTable");
  tr = table.getElementsByTagName("tr");
  for (i = 1; i < tr.length; i++) {
    var tdProd = tr[i].getElementsByTagName("td")[0];
    var tdLote = tr[i].getElementsByTagName("td")[1];
    if (tdProd || tdLote) {
      txtValue = (tdProd.textContent || tdProd.innerText) + " " + (tdLote.textContent || tdLote.innerText);
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }       
  }
}

function filtrarAlerta(estado) {
  var table, tr, i;
  table = document.getElementById("lotesTable");
  tr = table.getElementsByTagName("tr");
  for (i = 1; i < tr.length; i++) {
      if(estado === '') {
          tr[i].style.display = "";
          continue;
      }
      var f = tr[i].getAttribute('data-estado');
      if (f === estado) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
  }
}
</script>
