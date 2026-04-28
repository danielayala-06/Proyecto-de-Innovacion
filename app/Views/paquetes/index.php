<?= $header ?>
<!-- MAIN -->
<main id="main-content">
    <p class="section-label">Paquetes</p>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div class="d-flex align-items-center gap-2 flex-wrap" style="flex:1;">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar paquete...">
                <button class="search-btn"><i class="bi bi-search"></i></button>
            </div>
            <select class="filter-select" id="filterCat">
                <option value="">Todas las categorías</option>
                <option value="Quinceañeros">Quinceañeros</option>
                <option value="Cuadros">Cuadros</option>
                <option value="Anuarios">Anuarios</option>
                <option value="Matrimonios">Matrimonios</option>
                <option value="Corporativo">Corporativo</option>
                <option value="Otro">Otro</option>
            </select>
            <select class="filter-select" id="filterEstado">
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="inactivo">Inactivos</option>
            </select>
        </div>
        <button class="btn-nuevo-paquete" onclick="abrirNuevo()">
            <i class="bi bi-plus-circle"></i> Nuevo paquete
        </button>
    </div>

    <!-- STATS -->
    <div class="stats-bar">
        <div class="stat-box">
            <div class="sb-label">Total paquetes</div>
            <div class="sb-val" id="statTotal">0</div>
        </div>
        <div class="stat-box">
            <div class="sb-label">Activos</div>
            <div class="sb-val" id="statActivos" style="color:#4caf82;">0</div>
        </div>
        <div class="stat-box">
            <div class="sb-label">Precio promedio</div>
            <div class="sb-val" id="statPromedio" style="color:#7db8f0;font-size:1.1rem;">S/ 0</div>
        </div>
        <div class="stat-box">
            <div class="sb-label">Precio más alto</div>
            <div class="sb-val" id="statMax" style="color:#d4a017;font-size:1.1rem;">S/ 0</div>
        </div>
    </div>

    <!-- GRID -->
    <div class="paquetes-grid" id="paquetesGrid"></div>
</main>

<!-- MODAL NUEVO / EDITAR -->
<div class="modal fade" id="modalPaquete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modalTitulo">Nuevo paquete</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pId">
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-8">
                        <label>Nombre del paquete</label>
                        <input type="text" class="form-control" id="pNombre" placeholder="Ej: Paquete Quinceañero Premium">
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Categoría</label>
                        <select class="form-select" id="pCategoria">
                            <option value="Quinceañeros">Quinceañeros</option>
                            <option value="Cuadros">Cuadros</option>
                            <option value="Anuarios">Anuarios</option>
                            <option value="Matrimonios">Matrimonios</option>
                            <option value="Corporativo">Corporativo</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Descripción</label>
                    <textarea class="form-control" id="pDesc" rows="2" placeholder="Descripción breve del paquete..."></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-4">
                        <label>Precio (S/)</label>
                        <input type="number" class="form-control" id="pPrecio" placeholder="0.00" min="0" step="0.01">
                    </div>
                    <div class="col-6 col-md-4">
                        <label>Estado</label>
                        <select class="form-select" id="pEstado">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label>Duración estimada</label>
                        <input type="text" class="form-control" id="pDuracion" placeholder="Ej: 3 horas">
                    </div>
                </div>

                <!-- Items del paquete -->
                <div class="mb-1">
                    <label style="margin-bottom:8px;">Incluye</label>
                    <div id="itemsContainer"></div>
                    <button class="btn-add-item" onclick="agregarItemModal()">
                        <i class="bi bi-plus"></i> Agregar ítem
                    </button>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button class="btn btn-outline-danger btn-sm" id="btnEliminarModal" style="display:none;" onclick="confirmarEliminar()">
                    <i class="bi bi-trash me-1"></i>Eliminar
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary btn-sm" onclick="guardarPaquete()">Guardar paquete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINAR -->
<div class="modal fade modal-delete" id="modalConfirm" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" style="color:#e07070;"><i class="bi bi-exclamation-triangle me-2"></i>Eliminar paquete</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size:0.85rem;color:#aaa;">
                ¿Estás seguro de que deseas eliminar el paquete <strong id="confirmNombre" style="color:#ddd;"></strong>? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-danger btn-sm" onclick="eliminarPaquete()">Sí, eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>const BASE_URL = "<?= base_url('') ?>"</script>
<script type="module" src="<?= base_url('js/modules/paquetes/paquetesMain.js') ?>"></script>

<?= $footer ?>
