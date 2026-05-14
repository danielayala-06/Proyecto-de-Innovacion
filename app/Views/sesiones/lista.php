<?= $header ?>
<main id="main-content">
    <div class="container">

        <p class="page-title">Sesiones fotográficas</p>

        <!-- STATS -->
        <div class="con-stats-bar">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-camera-fill"></i></div>
                <div>
                    <div class="stat-label">Total sesiones</div>
                    <div class="stat-value" id="statTotal">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="stat-label">Pendientes</div>
                    <div class="stat-value" id="statPendientes" style="color:var(--amber-text);">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-check2-all"></i></div>
                <div>
                    <div class="stat-label">Finalizadas</div>
                    <div class="stat-value" id="statFinalizadas" style="color:var(--green-text);">0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="bi bi-x-circle"></i></div>
                <div>
                    <div class="stat-label">Canceladas</div>
                    <div class="stat-value" id="statCanceladas" style="color:var(--red-text);">0</div>
                </div>
            </div>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap" style="flex:1;">
                <div class="search-wrap" style="max-width:280px;">
                    <input type="text" id="searchInput" placeholder="Buscar promoción o colegio...">
                    <button><i class="bi bi-search"></i></button>
                </div>
                <select class="filter-select" id="filterEstado">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="finalizado">Finalizado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
                <select class="filter-select" id="filterTipo">
                    <option value="">Todos los tipos</option>
                    <option value="colegio">Colegio</option>
                    <option value="exteriores">Exteriores</option>
                    <option value="estudio">Estudio</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
        </div>

        <!-- TABLA -->
        <div class="con-table-card">
            <table>
                <thead>
                    <tr>
                        <th class="text-uppercase">Tipo</th>
                        <th class="text-uppercase">Promoción</th>
                        <th class="text-uppercase">Colegio</th>
                        <th class="text-uppercase">Fecha y hora</th>
                        <th class="text-uppercase">Estado</th>
                        <th class="text-uppercase" style="width:90px;text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
            <div class="con-pagination">
                <span id="paginaInfo" style="font-size:0.78rem;color:var(--text-muted);"></span>
            </div>
        </div>

    </div>
</main>

<script>const BASE_URL = "<?= base_url('') ?>";</script>
<script type="module" src="<?= base_url('js/modules/sesiones/sesionesLista.js') ?>"></script>
<?= $footer ?>
