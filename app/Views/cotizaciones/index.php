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
            <p class="page-title">Cotizaciones</p>

            <!-- STATS -->
            <div class="cot-stats-bar">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-file-earmark-text-fill"></i></div>
                    <div><div class="stat-label">Total</div><div class="stat-value" id="statTotal">0</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="bi bi-clock-history"></i></div>
                    <div><div class="stat-label">Pendientes</div><div class="stat-value" id="statPend" style="color:var(--amber-text);">0</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
                    <div><div class="stat-label">Aprobadas</div><div class="stat-value" id="statAprobadas" style="color:var(--green-text);">0</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="bi bi-x-circle-fill"></i></div>
                    <div><div class="stat-label">Rechazadas</div><div class="stat-value" id="statRechazadas" style="color:var(--red-text);">0</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:var(--bg-hover);"><i class="bi bi-hourglass-split" style="color:var(--text-muted);"></i></div>
                    <div><div class="stat-label">Expiradas</div><div class="stat-value" id="statExpiradas" style="color:var(--text-muted);">0</div></div>
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
                        <option value="expirada">Expirada</option>
                    </select>
                    <button id="btnToggleArchivadas" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;display:flex;align-items:center;gap:5px;">
                        <i class="bi bi-archive"></i> Mostrar archivadas
                    </button>
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
                        <th class="text-uppercase" onclick="sortBy('codigo')">Código <i class="bi bi-arrow-down-up sort-icon" id="sort-codigo"></i></th>
                        <th class="text-uppercase" onclick="sortBy('cliente')">Cliente <i class="bi bi-arrow-down-up sort-icon" id="sort-cliente"></i></th>
                        <th class="text-uppercase" onclick="sortBy('total')">Total <i class="bi bi-arrow-down-up sort-icon" id="sort-total"></i></th>
                        <th class="text-uppercase" onclick="sortBy('estado')">Estado <i class="bi bi-arrow-down-up sort-icon" id="sort-estado"></i></th>
                        <th class="text-uppercase" onclick="sortBy('creado')">Creado <i class="bi bi-arrow-down-up sort-icon" id="sort-creado"></i></th>
                        <th class="text-uppercase" style="width:90px;text-align:center;">Acciones</th>
                    </tr>
                    </thead>
                    <!-- CUERPO DE LA TABLA RENDERIZADO JS-->
                    <tbody id="tablaBody"></tbody>
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
                        <div class="modal-footer">
                            <div class="d-flex gap-2" id="detalleAcciones"></div>
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
        const BASE_URL = "<?= base_url('') ?>";
    </script>
    <script type="module" src="<?= base_url('js/modules/cotizaciones/cotizacionesIndex.js') . '?v=' . filemtime(FCPATH . 'js/modules/cotizaciones/cotizacionesIndex.js') ?>"></script>

<?= $footer ?>