<?= $header ?>
<!-- MAIN -->
<main id="main-content">
    <div class="container">

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
                    <option value="Cuadros">Cuadros</option>
                    <option value="Anuarios">Anuarios</option>
                    <option value="Paquetes">Paquetes</option>
                    <option value="Corporativo">Corporativo</option>
                    <option value="Otro">Otro</option>
                </select>
                <select class="filter-select" id="filterNivel">
                    <option value="">Todos los niveles</option>
                    <option value="inicial">Inicial</option>
                    <option value="primaria">Primaria</option>
                    <option value="secundaria">Secundaria</option>
                    <option value="postgrado">Postgrado</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <button class="btn-nuevo-paquete" onclick="abrirNuevo()">
                <i class="bi bi-plus-circle"></i> Nuevo paquete
            </button>
        </div>

        <!-- STATS -->
        <div class="stats-bar">
            <div class="stat-box">
                <div class="stat-icon blue"><i class="bi bi-box-seam-fill"></i></div>
                <div><div class="sb-label">Encontrados</div><div class="sb-val" id="statTotal">0</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon green"><i class="bi bi-toggle-on"></i></div>
                <div><div class="sb-label">Activos</div><div class="sb-val" id="statActivos" style="color:var(--green-text);">0</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon red"><i class="bi bi-toggle-off"></i></div>
                <div><div class="sb-label">Inactivos</div><div class="sb-val" id="statInactivos" style="color:var(--red-text);">0</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon amber"><i class="bi bi-tag-fill"></i></div>
                <div><div class="sb-label">Precio promedio</div><div class="sb-val" id="statPromedio" style="color:var(--amber-text);font-size:1.1rem;">S/ 0</div></div>
            </div>
            <div class="stat-box">
                <div class="stat-icon gold"><i class="bi bi-graph-up"></i></div>
                <div><div class="sb-label">Precio más alto</div><div class="sb-val" id="statMax" style="color:var(--accent);font-size:1.1rem;">S/ 0</div></div>
            </div>
        </div>

        <!-- GRID -->
        <div class="paquetes-grid" id="paquetesGrid"></div>
    </div>
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
                            <option value="Cuadros">Cuadros</option>
                            <option value="Anuarios">Anuarios</option>
                            <option value="Paquetes">Paquetes</option>
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
                    <div class="col-6 col-md-8">
                        <label>Nivel disponible *</label>
                        <select class="form-select" id="pNivel">
                            <option value="">Seleccionar nivel...</option>
                            <option value="inicial">Inicial</option>
                            <option value="primaria">Primaria</option>
                            <option value="secundaria">Secundaria</option>
                            <option value="postgrado">Postgrado</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>

                <!-- Imagen del paquete (solo edición) -->
                <div id="pImagenSection" class="mb-3" style="display:none;">
                    <label style="margin-bottom:8px;">Imagen del paquete</label>
                    <div id="pImagenZone" class="img-upload-zone" onclick="document.getElementById('pImagenInput').click()">
                        <img id="pImagenPreview" src="" alt="" loading="lazy" decoding="async"
                             style="display:none;width:100%;height:100%;object-fit:cover;border-radius:8px;">
                        <div id="pImagenPlaceholder">
                            <i class="bi bi-cloud-upload" style="font-size:1.5rem;display:block;margin-bottom:.25rem;"></i>
                            <span style="font-size:.8rem;">Haz clic para subir imagen (JPG, PNG · máx. 5 MB)</span>
                        </div>
                    </div>
                    <input type="file" id="pImagenInput" accept="image/jpeg,image/png,image/webp" style="display:none;">
                    <div id="pImagenAcciones" style="display:none;margin-top:.4rem;gap:.5rem;" class="d-flex">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                onclick="document.getElementById('pImagenInput').click()">
                            <i class="bi bi-arrow-repeat me-1"></i>Cambiar
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm"
                                onclick="quitarImagenPaquete()">
                            <i class="bi bi-trash me-1"></i>Quitar
                        </button>
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

                <!-- Sesiones fotográficas (solo creación) -->
                <div id="sesionesSection" class="mb-1" style="display:none;">
                    <hr style="border-color:var(--border);margin:.75rem 0;">
                    <label style="margin-bottom:8px;">Sesiones fotográficas incluidas</label>
                    <div id="sesionesContainer"></div>
                    <button class="btn-add-item" onclick="agregarSesionModal()">
                        <i class="bi bi-plus"></i> Agregar tipo de sesión
                    </button>
                </div>

                <!-- Reglas de bonificación -->
                <hr style="border-color:var(--border);margin:1rem 0 .75rem;">
                <label style="margin-bottom:8px;">Reglas y condiciones</label>
                <div id="reglasContainer" style="margin-bottom:.75rem;"></div>

                <!-- Mini-formulario para agregar regla -->
                <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:8px;padding:.75rem;">
                    <div class="row g-2 mb-2">
                        <div class="col-6 col-md-4">
                            <label style="font-size:.75rem;">¿Qué tipo de regla es?</label>
                            <select class="form-select form-select-sm" id="rTipoCondicion"
                                    onchange="window.__paqOnCondicionChange(this.value)">
                                <option value="">— Seleccionar —</option>
                                <option value="ELEGIBILIDAD_MIN">Mínimo para contratar el paquete</option>
                                <option value="CANTIDAD_MIN">Bonificación por cantidad de alumnos</option>
                                <option value="CANTIDAD_MAX">Reducción de sesiones por grupo pequeño</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label style="font-size:.75rem;" id="rValorCondicionLabel">N.° de alumnos</label>
                            <input type="number" class="form-control form-control-sm" id="rValorCondicion"
                                   placeholder="Ej: 15" min="1" step="1">
                        </div>
                        <div class="col-12 col-md-6" id="wrapRTipoBeneficio">
                            <label style="font-size:.75rem;">¿Qué se entrega?</label>
                            <select class="form-select form-select-sm" id="rTipoBeneficio">
                                <option value="">— Seleccionar —</option>
                                <option value="producto_gratis">Un producto del catálogo (gratis)</option>
                                <option value="sesion_unica">Una sesión fotográfica extra</option>
                                <option value="otro">Otro beneficio (texto libre)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-5" id="wrapRValorBeneficio">
                            <label style="font-size:.75rem;" id="rValorBeneficioLabel">Detalle del beneficio</label>
                            <input type="text" class="form-control form-control-sm" id="rValorBeneficio"
                                   placeholder="Ej: Cuadro laminado para el docente">
                        </div>
                        <div class="col-12 col-md-5 d-none" id="wrapRProductoBeneficio">
                            <label style="font-size:.75rem;">Producto que se entrega</label>
                            <select class="form-select form-select-sm" id="rIdProductoBeneficio">
                                <option value="">Seleccionar producto...</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-7">
                            <label style="font-size:.75rem;">Mensaje que verá el cliente</label>
                            <input type="text" class="form-control form-control-sm" id="rDescripcion"
                                   placeholder="Ej: Con 15 o más alumnos, el docente recibe un cuadro laminado.">
                        </div>
                    </div>
                    <button class="btn-add-item" onclick="agregarReglaModal()">
                        <i class="bi bi-plus"></i> Agregar regla
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
<script type="module" src="<?= base_url('js/modules/paquetes/paquetesMain.js') . '?v=' . filemtime(FCPATH . 'js/modules/paquetes/paquetesMain.js') ?>"></script>

<?= $footer ?>