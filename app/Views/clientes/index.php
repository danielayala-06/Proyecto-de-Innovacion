<?= $header ?>
    <!-- MAIN -->
    <main id="main-content">
        <p class="section-label">Clientes</p>

        <!-- TOOLBAR -->
        <div class="toolbar">
            <div class="d-flex align-items-center gap-2 flex-wrap" style="flex:1;">
                <div class="search-wrap">
                    <input type="text" id="searchInput" placeholder="Buscar por nombre, DNI o teléfono...">
                    <button><i class="bi bi-search"></i></button>
                </div>

            </div>
            <button class="btn-add" onclick="abrirNuevo()">
                <i class="bi bi-person-plus"></i> Nuevo cliente
            </button>
        </div>

        <!-- STATS -->
        <div class="stats-bar">
            <div class="stat-box">
                <div class="sb-label">Total clientes</div>
                <div class="sb-val" id="statTotal">0</div>
            </div>
            <div class="stat-box">
                <div class="sb-label">Resultado búsqueda</div>
                <div class="sb-val" id="statFiltro" style="color:#7db8f0;">0</div>
            </div>
            <div class="stat-box">
                <div class="sb-label">Esta página</div>
                <div class="sb-val" id="statPagina">0</div>
            </div>
        </div>

        <!-- TABLA -->
        <div class="table-card-clientes">
            <table>
                <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th onclick="sortBy('nombre')">
                        Nombre <i class="bi bi-arrow-down-up sort-icon" id="sort-nombre"></i>
                    </th>
                    <th onclick="sortBy('apellido')">
                        Apellido <i class="bi bi-arrow-down-up sort-icon" id="sort-apellido"></i>
                    </th>
                    <th onclick="sortBy('dni')">
                        DNI <i class="bi bi-arrow-down-up sort-icon" id="sort-dni"></i>
                    </th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th style="width:90px;text-align:center;">Acciones</th>
                </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
            <div class="pagination-bar">
                <span id="paginaInfo"></span>
                <div class="pag-btns" id="paginaBtns"></div>
            </div>
        </div>
    </main>

    <!-- MODAL NUEVO / EDITAR -->
    <div class="modal fade" id="modalCliente" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:500px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="modalTitulo">Nuevo cliente</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cId">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label>Nombre</label>
                            <input type="text" class="form-control" id="cNombre" placeholder="Nombres">
                        </div>
                        <div class="col-6">
                            <label>Apellido</label>
                            <input type="text" class="form-control" id="cApellido" placeholder="Apellidos">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label>DNI</label>
                            <input type="text" class="form-control" id="cDni" placeholder="12345678" maxlength="8">
                        </div>
                        <div class="col-6">
                            <label>Teléfono</label>
                            <input type="text" class="form-control" id="cTelefono" placeholder="987654321" maxlength="12">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Correo electrónico</label>
                        <input type="email" class="form-control" id="cCorreo" placeholder="correo@ejemplo.com">
                    </div>
                    <div class="mb-1">
                        <label>Dirección <span style="color:#555;font-weight:400;">(opcional)</span></label>
                        <input type="text" class="form-control" id="cDireccion" placeholder="Av. ejemplo 123, Ica">
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button class="btn btn-outline-danger btn-sm" id="btnElimModal" style="display:none;" onclick="confirmarEliminar()">
                        <i class="bi bi-trash me-1"></i>Eliminar
                    </button>
                    <div class="ms-auto d-flex gap-2">
                        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary btn-sm" onclick="guardarCliente()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRMAR ELIMINAR -->
    <div class="modal fade modal-del" id="modalConfirm" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" style="color:#e07070;"><i class="bi bi-exclamation-triangle me-2"></i>Eliminar cliente</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="font-size:0.85rem;color:#aaa;">
                    ¿Eliminar a <strong id="confirmNombre" style="color:#ddd;"></strong>?
                    Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger btn-sm" onclick="eliminarCliente()">Sí, eliminar</button>
                </div>
            </div>
        </div>
    </div>
<script>
    const BASE_URL      = "<?= base_url('') ?>";
    const CLIENTES_DATA = <?= json_encode($clientes) ?>;
</script>
<script type="module" src="<?= base_url('js/modules/clientes/clientesIndexMain.js') ?>"></script>

<?= $footer ?>