<?= $header ?>
<main id="main-content">
    <div class="container">

        <p class="page-title">Promociones Escolares</p>

        <!-- STATS -->
        <div class="con-stats-bar">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-mortarboard-fill"></i></div>
                <div>
                    <div class="stat-label">Total promociones</div>
                    <div class="stat-value" id="statTotal">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
                <div>
                    <div class="stat-label">Activas</div>
                    <div class="stat-value" id="statActivas" style="color:var(--green-text);">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-building"></i></div>
                <div>
                    <div class="stat-label">Colegios</div>
                    <div class="stat-value" id="statColegios" style="color:var(--amber-text);">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple" style="background:rgba(139,92,246,.15);color:#7c3aed;"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="stat-label">Estudiantes</div>
                    <div class="stat-value" id="statEstudiantes" style="color:#7c3aed;">0</div>
                </div>
            </div>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap" style="flex:1;">
                <div class="search-wrap" style="max-width:300px;">
                    <input type="text" id="searchInput" placeholder="Buscar colegio o promoción...">
                    <button><i class="bi bi-search"></i></button>
                </div>
                <select class="filter-select" id="filterGrado">
                    <option value="">Todos los grados</option>
                    <option value="Inicial">Inicial</option>
                    <option value="Jardin">Jardín</option>
                    <option value="Primaria">Primaria</option>
                    <option value="Secundaria">Secundaria</option>
                    <option value="Superior">Superior</option>
                    <option value="Otro">Otro</option>
                </select>
                <select class="filter-select" id="filterAnio">
                    <option value="">Todos los años</option>
                </select>
            </div>
        </div>

        <!-- TABLA -->
        <div class="con-table-card">
            <table>
                <thead>
                    <tr>
                        <th class="text-uppercase">Grado</th>
                        <th class="text-uppercase">Colegio</th>
                        <th class="text-uppercase">Promoción</th>
                        <th class="text-uppercase" style="text-align:center;">Estudiantes</th>
                        <th class="text-uppercase" style="text-align:center;">Año</th>
                        <th class="text-uppercase" style="width:130px;text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaBody">
                    <tr>
                        <td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">
                            <i class="bi bi-arrow-repeat" style="font-size:1.2rem;"></i> Cargando...
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="con-pagination">
                <span id="paginaInfo" style="font-size:0.78rem;color:var(--text-muted);"></span>
                <div class="pag-btns" id="paginaBtns"></div>
            </div>
        </div>

    </div>
</main>

<!-- MODAL: Ver sesiones disponibles -->
<div class="modal fade" id="modalSesiones" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">
                    <i class="bi bi-camera-fill me-2" style="color:var(--accent);"></i>
                    <span id="modalSesionesTitulo">Sesiones de la promoción</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalSesionesBody">
                <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                    <i class="bi bi-arrow-repeat"></i> Cargando...
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                <a id="btnIrSesiones" href="#" class="btn btn-outline-primary btn-sm" style="display:none;">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Ver página completa
                </a>
            </div>
        </div>
    </div>
</div>

<script>const BASE_URL = "<?= base_url('') ?>";</script>
<script type="module" src="<?= base_url('js/modules/promociones/promocionesMain.js') ?>"></script>
<?= $footer ?>
