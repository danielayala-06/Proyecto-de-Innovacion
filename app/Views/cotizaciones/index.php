<?php
// ── Helpers de presentación ────────────────────────────────────────────────────
function badgeEstadoCot(string $e): string {
    $e = strtolower($e);
    $m = [
        'pendiente'  => ['badge-pendiente',  'Pendiente'],
        'aprobada'   => ['badge-aprobada',   'Aprobada'],
        'rechazada'  => ['badge-rechazada',  'Rechazada'],
        'completada' => ['badge-completada', 'Completada'],
    ];
    [$cls, $lb] = $m[$e] ?? ['badge-inactivo', ucfirst($e)];
    return "<span class=\"{$cls}\">{$lb}</span>";
}

function fmtFechaCot(?string $f): string {
    if (!$f) return '—';
    $meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    $d = new DateTime($f);
    return $d->format('d') . ' ' . $meses[(int)$d->format('n') - 1] . ' ' . $d->format('Y');
}
?>
<?= $header ?>
    <main class="main-content" id="main-content">
        <div class="container">
        <p class="page-title"><i class="bi bi-list-ul"></i> Cotizaciones</p>

            <!-- STATS -->
            <div class="cot-stats-bar">
              <div class="stat-card">
                <div class="stat-label">Total</div>
                <div class="stat-value" id="statTotal"><?= $resumenes['total_cotizaciones'] ?></div>
              </div>
              <div class="stat-card" style="border-left-color:var(--amber-text);">
                <div class="stat-label">Pendientes</div>
                <div class="stat-value" id="statPend" style="color:var(--amber-text);"><?= $resumenes['pendientes'] ?></div>
              </div>
              <div class="stat-card" style="border-left-color:var(--green-text);">
                <div class="stat-label">Aprobadas</div>
                <div class="stat-value" id="statAprobadas" style="color:var(--green-text);"><?= $resumenes['aprobadas'] ?></div>
              </div>
              <div class="stat-card" style="border-left-color:var(--red-text);">
                <div class="stat-label">Rechazadas</div>
                <div class="stat-value" id="statRechazadas" style="color:var(--red-text);"><?= $resumenes['rechazadas'] ?></div>
              </div>
              <div class="stat-card" style="border-left-color:var(--accent);">
                <div class="stat-label">Monto total</div>
                <div class="stat-value" id="statMonto" style="color:var(--accent);font-size:1.2rem;">S/ <?= number_format($resumenes['total_estimado'] ?? 0, 2, '.', ',') ?></div>
              </div>
            </div>

            <!-- TOOLBAR -->
            <div class="toolbar mb-3">
              <div class="d-flex align-items-center gap-2 flex-wrap" style="flex:1;">
                <div class="search-wrap" style="max-width:300px;">
                  <input type="text" id="searchInput" placeholder="Buscar cliente o código...">
                  <button><i class="bi bi-search"></i></button>
                </div>
                <select class="filter-select" id="filterEstado">
                  <option value="">Todos los estados</option>
                  <option value="pendiente">Pendiente</option>
                  <option value="aprobada">Aprobada</option>
                  <option value="rechazada">Rechazada</option>
                  <option value="completada">Completada</option>
                </select>
              </div>
              <a href="<?= base_url('cotizaciones/crear') ?>" class="btn-nuevo-paquete" style="text-decoration:none;">
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
                    <th onclick="sortBy('fecha')">Fecha evento <i class="bi bi-arrow-down-up sort-icon" id="sort-fecha"></i></th>
                    <th onclick="sortBy('total')">Total <i class="bi bi-arrow-down-up sort-icon" id="sort-total"></i></th>
                    <th onclick="sortBy('estado')">Estado <i class="bi bi-arrow-down-up sort-icon" id="sort-estado"></i></th>
                    <th onclick="sortBy('creado')">Creado <i class="bi bi-arrow-down-up sort-icon" id="sort-creado"></i></th>
                    <th style="width:90px;text-align:center;">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tablaBody">
                  <?php foreach ($cotizaciones as $c): ?>
                  <tr onclick="verDetalle(<?= $c['id_cotizacion'] ?>)" style="cursor:pointer;">
                    <td>
                      <span class="cot-codigo"><?= sprintf('COT-%03d', $c['id_cotizacion']) ?></span>
                      <?php if (!empty($c['cotizacion'])): ?>
                        <div style="font-size:0.71rem;color:var(--text-muted);margin-top:2px;"><?= esc($c['cotizacion']) ?></div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="text-uppercase" style="font-weight:600;color:var(--text-primary);"><?= esc($c['cliente']) ?></div>
                      <div style="font-size:0.72rem;color:var(--text-muted);"><?= esc($c['telefono'] ?? '') ?></div>
                    </td>
                    <td style="color:var(--text-secondary);white-space:nowrap;">
                      <?= fmtFechaCot($c['fecha_evento']) ?>
                    </td>
                    <td style="font-weight:700;color:var(--accent);">
                      S/ <?= number_format($c['total'] ?? 0, 2, '.', ',') ?>
                    </td>
                    <td><?= badgeEstadoCot($c['estado']) ?></td>
                    <td style="color:var(--text-muted);font-size:0.78rem;white-space:nowrap;">
                      <?= fmtFechaCot($c['fecha_creado']) ?>
                    </td>
                    <td>
                      <div class="cot-actions" onclick="event.stopPropagation()">
                        <button class="btn-icon" title="Ver" onclick="verDetalle(<?= $c['id_cotizacion'] ?>)">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn-icon" title="Editar" onclick="editarCotizacion(<?= $c['id_cotizacion'] ?>)">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn-icon danger" title="Eliminar" onclick="confirmarEliminar(<?= $c['id_cotizacion'] ?>)">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
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

        </div>
    </main>

<script>
    const BASE_URL          = "<?= base_url('') ?>";
    const COTIZACIONES_DATA = <?= json_encode($cotizaciones) ?>;
    const RESUMENES_DATA    = <?= json_encode($resumenes) ?>;
</script>
<script type="module" src="<?= base_url('js/modules/cotizaciones/cotizacionesIndexMain.js') ?>"></script>

<?= $footer ?>
