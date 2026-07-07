<?= $header ?>
<main id="main-content">
<div class="container">

    <p class="section-label">Colegios</p>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div class="d-flex align-items-center gap-2 flex-wrap" style="flex:1;">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar colegio, distrito...">
                <button class="search-btn"><i class="bi bi-search"></i></button>
            </div>
            <select class="filter-select" id="filterEstado">
                <option value="">Todos los estados</option>
                <option value="ACTIVO">Activo</option>
                <option value="INACTIVO">Inactivo</option>
            </select>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats-bar">
        <div class="stat-box">
            <div class="stat-icon blue"><i class="bi bi-building-fill"></i></div>
            <div><div class="sb-label">Colegios</div><div class="sb-val" id="statTotal">0</div></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon green"><i class="bi bi-toggle-on"></i></div>
            <div><div class="sb-label">Activos</div><div class="sb-val" id="statActivos" style="color:var(--green-text);">0</div></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon amber"><i class="bi bi-mortarboard-fill"></i></div>
            <div><div class="sb-label">Promociones</div><div class="sb-val" id="statPromos" style="color:var(--amber-text);">0</div></div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table-main">
                <thead>
                    <tr>
                        <th onclick="sortBy('nombre_colegio')" style="cursor:pointer;">
                            Colegio <i class="bi bi-arrow-down-up ms-1 sort-icon" id="sort-nombre_colegio"></i>
                        </th>
                        <th>Ubicación</th>
                        <th>Última promoción</th>
                        <th>Contacto</th>
                        <th onclick="sortBy('total_promociones')" style="cursor:pointer;text-align:center;">
                            Promos <i class="bi bi-arrow-down-up ms-1 sort-icon" id="sort-total_promociones"></i>
                        </th>
                        <th style="text-align:center;">Estado</th>
                        <th style="width:50px;"></th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-secondary);">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>Cargando...
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</main>

<!-- MODAL DETALLE COLEGIO -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width:680px;">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title mb-0" id="detNombre">—</h6>
                    <small id="detUbicacion" style="color:var(--text-secondary);font-size:.78rem;"></small>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <span id="detEstadoBadge" class="badge"></span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>

            <!-- Tabs -->
            <div style="border-bottom:1px solid var(--border);padding:0 1.25rem;">
                <ul class="nav nav-tabs border-0" id="detTabs">
                    <li class="nav-item">
                        <button class="nav-link active" id="tab-promo-btn" onclick="_tabActivo('promo')">
                            <i class="bi bi-mortarboard me-1"></i>Promociones
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="tab-prod-btn" onclick="_tabActivo('prod')">
                            <i class="bi bi-box-seam me-1"></i>Productos
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="tab-contacto-btn" onclick="_tabActivo('contacto')">
                            <i class="bi bi-person me-1"></i>Contacto
                        </button>
                    </li>
                </ul>
            </div>

            <div class="modal-body" style="padding:1.25rem;min-height:280px;max-height:420px;overflow-y:auto;">
                <!-- Tab promociones -->
                <div id="tab-promo">
                    <div id="detPromociones">
                        <div style="text-align:center;padding:2rem;color:var(--text-secondary);">
                            <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                        </div>
                    </div>
                </div>
                <!-- Tab productos -->
                <div id="tab-prod" style="display:none;">
                    <div id="detProductos">
                        <div style="text-align:center;padding:2rem;color:var(--text-secondary);">
                            <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                        </div>
                    </div>
                </div>
                <!-- Tab contacto -->
                <div id="tab-contacto" style="display:none;">
                    <div id="detContacto"></div>
                </div>
            </div>

            <div class="modal-footer" style="justify-content:flex-start;gap:.5rem;">
                <button class="btn btn-outline-secondary btn-sm" id="detBtnEditar" onclick="_abrirEditar()">
                    <i class="bi bi-pencil me-1"></i>Editar datos
                </button>
                <button class="btn btn-secondary btn-sm ms-auto" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR COLEGIO -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Editar colegio</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="eId">
                <div class="mb-3">
                    <label class="form-label" style="font-size:.82rem;">Nombre del colegio</label>
                    <input type="text" class="form-control" id="eNombre" maxlength="100">
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label" style="font-size:.82rem;">Distrito</label>
                        <input type="text" class="form-control" id="eDistrito" maxlength="100">
                    </div>
                    <div class="col-6">
                        <label class="form-label" style="font-size:.82rem;">Provincia</label>
                        <input type="text" class="form-control" id="eProvincia" maxlength="100">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" onclick="guardarEdicion()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>const BASE_URL = "<?= base_url('') ?>";</script>
<script type="module" src="<?= base_url('js/modules/clientes/clientesIndexMain.js') . '?v=' . filemtime(FCPATH . 'js/modules/clientes/clientesIndexMain.js') ?>"></script>

<?= $footer ?>
