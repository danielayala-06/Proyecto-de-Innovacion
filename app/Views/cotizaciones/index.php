<?= $header ?>
<p class="page-title"><i class="bi bi-list-ul"></i> Cotizaciones</p>

    <!-- STATS -->
    <div class="cot-stats-bar">
      <div class="stat-card">
        <div class="stat-label">Total</div>
        <div class="stat-value" id="statTotal">0</div>
      </div>
      <div class="stat-card" style="border-left-color:var(--amber-text);">
        <div class="stat-label">Pendientes</div>
        <div class="stat-value" id="statPend" style="color:var(--amber-text);">0</div>
      </div>
      <div class="stat-card" style="border-left-color:var(--green-text);">
        <div class="stat-label">Aprobadas</div>
        <div class="stat-value" id="statAprobadas" style="color:var(--green-text);">0</div>
      </div>
      <div class="stat-card" style="border-left-color:var(--red-text);">
        <div class="stat-label">Rechazadas</div>
        <div class="stat-value" id="statRechazadas" style="color:var(--red-text);">0</div>
      </div>
      <div class="stat-card" style="border-left-color:var(--accent);">
        <div class="stat-label">Monto total</div>
        <div class="stat-value" id="statMonto" style="color:var(--accent);font-size:1.2rem;">S/ 0</div>
      </div>
    </div>
    <!-- FIN STATS -->

    <!-- TOOLBAR -->
    <div class="toolbar mb-3">
      <div class="d-flex align-items-center gap-2 flex-wrap" style="flex:1;">
        <div class="search-wrap" style="max-width:300px;">
          <input type="text" id="searchInput" placeholder="Buscar cliente, tipo o código...">
          <button><i class="bi bi-search"></i></button>
        </div>
        <select class="filter-select" id="filterEstado">
          <option value="">Todos los estados</option>
          <option value="pendiente">Pendiente</option>
          <option value="aprobada">Aprobada</option>
          <option value="rechazada">Rechazada</option>
          <option value="completada">Completada</option>
        </select>
        <select class="filter-select" id="filterTipo">
          <option value="">Todos los tipos</option>
          <option value="Matrimonio">Matrimonio</option>
          <option value="Quinceañero">Quinceañero</option>
          <option value="Retrato">Retrato</option>
          <option value="Corporativo">Corporativo</option>
          <option value="Otro">Otro</option>
        </select>
      </div>
      <a href="cotizacion.html" class="btn-nuevo-paquete" style="text-decoration:none;">
        <i class="bi bi-plus-circle"></i> Nueva cotización
      </a>
    </div>

    <!-- TABLA -->
    <div class="cot-table-card">
      <table>
        <thead>
          <tr>
            <th onclick="sortBy('codigo')">Código <i class="bi bi-arrow-down-up sort-icon" id="sort-codigo"></i></th>
            <th onclick="sortBy('cliente')">Cliente <i class="bi bi-arrow-down-up sort-icon" id="sort-cliente"></i></th>
            <!-- <th onclick="sortBy('tipoEvento')">Tipo evento <i class="bi bi-arrow-down-up sort-icon" id="sort-tipoEvento"></i></th> -->
            <th onclick="sortBy('fecha')">Fecha evento <i class="bi bi-arrow-down-up sort-icon" id="sort-fecha"></i></th>
            <th onclick="sortBy('total')">Total <i class="bi bi-arrow-down-up sort-icon" id="sort-total"></i></th>
            <th onclick="sortBy('estado')">Estado <i class="bi bi-arrow-down-up sort-icon" id="sort-estado"></i></th>
            <th onclick="sortBy('creado')">Creado <i class="bi bi-arrow-down-up sort-icon" id="sort-creado"></i></th>
            <th style="width:90px;text-align:center;">Acciones</th>
          </tr>
        </thead>
        <tbody id="tablaBody">
          <!-- FILAS GENERADAS POR PHP :D -->
           <?php foreach ($cotizaciones as $cotizacion): ?>
            <tr>
              <td class="col-md-2">
                <p class="cot-detail-val">
                  COT-0<?= $cotizacion['id_cotizacion'] ?>
                </p>
              </td>
              <td class="col-md-4">
                <p class="cot-detail-val">
                  <?= $cotizacion['cliente'] ?>
                </p>
              </td>
              <td class="col-md-2">
                <p class="cot-detail-val">
                  <?= $cotizacion['fecha_evento'] ?>
                </p>
              </td>
              <td class="col-md-2">
                <p class="cot-detail-val">
                  <?= $cotizacion['total'] ?>
                </p> 
              </td>
              <td class="col-md-2">
                <p class="cot-detail-val">
                  <?= $cotizacion['estado'] ?>
                </p>
              </td>
              <td class="col-md-2">
                <p class="cot-detail-val">
                  <?= $cotizacion['fecha_creado'] ?>
                </p>
              </td>
              <!-- ACCIONES -->
              <td style="text-align:center;">
                <a href="#" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalDetalle" onclick="verDetalle(<?= $cotizacion['id_cotizacion'] ?>)">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="#" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalConfirm" onclick="prepararEliminar(<?= $cotizacion['id_cotizacion'] ?>)">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="cot-pagination">
        <span id="paginaInfo" style="font-size:0.78rem;color:var(--text-muted);"></span>
        <div class="pag-btns" id="paginaBtns"></div>
      </div>
    </div>

    <!-- MODAL VER DETALLE -->
    <div class="modal fade" id="modalDetalle" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h6 class="modal-title" id="detalleTitle">Detalle de cotización</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="detalleBody"></div>
          <div class="modal-footer d-flex justify-content-between">
            <div class="d-flex gap-2" id="detalleAcciones"></div>
            <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL CONFIRMAR ELIMINAR -->
    <div class="modal fade modal-del" id="modalConfirm" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
        <div class="modal-content">
          <div class="modal-header">
            <h6 class="modal-title" style="color:var(--red-text);font-size:0.88rem;">
              <i class="bi bi-exclamation-triangle me-2"></i>Eliminar cotización
            </h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" style="font-size:0.85rem;color:var(--text-secondary);">
            ¿Eliminar la cotización <strong id="confirmCod" style="color:var(--text-primary);"></strong>?
            Esta acción no se puede deshacer.
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-danger btn-sm" onclick="eliminarCotizacion()">Sí, eliminar</button>
          </div>
        </div>
      </div>
    </div>

<?= $footer ?>