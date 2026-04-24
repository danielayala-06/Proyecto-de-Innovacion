<?= $header ?>
    <!-- MAIN -->
    <main id="main-content">
        <p class="page-title"><i class="bi bi-file-earmark-text"></i> Contratos</p>

        <!-- STATS -->
        <div class="con-stats-bar">
            <div class="stat-card">
                <div class="stat-label">Total contratos</div>
                <div class="stat-value" id="statTotal">0</div>
            </div>
            <div class="stat-card" style="border-left-color:var(--amber-text);">
                <div class="stat-label">Vigentes</div>
                <div class="stat-value" id="statVigentes" style="color:var(--amber-text);">0</div>
            </div>
            <div class="stat-card" style="border-left-color:var(--green-text);">
                <div class="stat-label">Completados</div>
                <div class="stat-value" id="statCompletados" style="color:var(--green-text);">0</div>
            </div>
            <div class="stat-card" style="border-left-color:var(--accent);">
                <div class="stat-label">Monto total</div>
                <div class="stat-value" id="statMonto" style="color:var(--accent);font-size:1.15rem;">S/ 0</div>
            </div>
        </div>

        <!-- TOOLBAR -->
        <div class="toolbar mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap" style="flex:1;">
                <div class="search-wrap" style="max-width:300px;">
                    <input type="text" id="searchInput" placeholder="Buscar cliente, código o tipo...">
                    <button><i class="bi bi-search"></i></button>
                </div>
                <select class="filter-select" id="filterEstado">
                    <option value="">Todos los estados</option>
                    <option value="vigente">Vigente</option>
                    <option value="completado">Completado</option>
                    <option value="cancelado">Cancelado</option>
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
            <button class="btn-nuevo-paquete" onclick="abrirModalCotizaciones()">
                <i class="bi bi-plus-circle"></i> Generar contrato
            </button>
        </div>

        <!-- TABLA CONTRATOS -->
        <div class="con-table-card">
            <table>
                <thead>
                <tr>
                    <th onclick="sortBy('codigo')">Código <i class="bi bi-arrow-down-up sort-icon" id="sort-codigo"></i></th>
                    <th onclick="sortBy('cotizacionCod')">Cotización <i class="bi bi-arrow-down-up sort-icon" id="sort-cotizacionCod"></i></th>
                    <th onclick="sortBy('cliente')">Cliente <i class="bi bi-arrow-down-up sort-icon" id="sort-cliente"></i></th>
                    <th onclick="sortBy('tipoEvento')">Tipo evento <i class="bi bi-arrow-down-up sort-icon" id="sort-tipoEvento"></i></th>
                    <th onclick="sortBy('fechaEvento')">Fecha evento <i class="bi bi-arrow-down-up sort-icon" id="sort-fechaEvento"></i></th>
                    <th onclick="sortBy('total')">Total <i class="bi bi-arrow-down-up sort-icon" id="sort-total"></i></th>
                    <th onclick="sortBy('estado')">Estado <i class="bi bi-arrow-down-up sort-icon" id="sort-estado"></i></th>
                    <th onclick="sortBy('creado')">Generado <i class="bi bi-arrow-down-up sort-icon" id="sort-creado"></i></th>
                    <th style="width:100px;text-align:center;">Acciones</th>
                </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
            <div class="con-pagination">
                <span id="paginaInfo" style="font-size:0.78rem;color:var(--text-muted);"></span>
                <div class="pag-btns" id="paginaBtns"></div>
            </div>
        </div>
    </main>

    <!--  MODAL 1: SELECCIONAR COTIZACIÓN  -->
    <div class="modal fade" id="modalCotizaciones" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="bi bi-file-earmark-check me-2" style="color:var(--accent);"></i>
                        Seleccionar cotización para generar contrato
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;">
                        Solo se muestran cotizaciones <strong style="color:var(--green-text);">aprobadas</strong> que aún no tienen contrato generado.
                    </p>
                    <!-- Búsqueda dentro del modal -->
                    <div class="search-wrap mb-3" style="max-width:100%;">
                        <input type="text" id="searchCotModal" placeholder="Buscar por cliente o código...">
                        <button><i class="bi bi-search"></i></button>
                    </div>
                    <!-- Lista de cotizaciones disponibles -->
                    <div id="cotizacionesDisponibles"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: GENERAR CONTRATO -->
    <div class="modal fade" id="modalGenerarContrato" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="bi bi-file-earmark-text me-2" style="color:var(--accent);"></i>
                        Generar contrato
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Resumen cotización seleccionada -->
                    <div class="con-cotizacion-resumen" id="cotResumen"></div>

                    <!-- Datos del contrato -->
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label>Fecha de firma del contrato</label>
                            <input type="date" class="form-control" id="contratoFechaFirma">
                        </div>
                        <div class="col-md-6">
                            <label>Adelanto / pago inicial (S/)</label>
                            <input type="number" class="form-control" id="contratoAdelanto" placeholder="0.00" min="0">
                        </div>
                        <div class="col-md-6">
                            <label>Forma de pago del saldo</label>
                            <select class="form-select" id="contratoFormaPago">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia bancaria</option>
                                <option value="Yape/Plin">Yape / Plin</option>
                                <option value="Mixto">Mixto</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Fecha límite de pago del saldo</label>
                            <input type="date" class="form-control" id="contratoFechaSaldo">
                        </div>
                        <div class="col-12">
                            <label>Cláusulas / condiciones adicionales</label>
                            <textarea class="form-control" id="contratoClausulas" rows="3"
                                      placeholder="Ej: El fotógrafo se compromete a entregar las fotos en un plazo máximo de 30 días..."></textarea>
                        </div>
                        <div class="col-12">
                            <label>Observaciones internas</label>
                            <textarea class="form-control" id="contratoObservaciones" rows="2"
                                      placeholder="Notas internas (no aparecen en el contrato impreso)..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" onclick="volverACotizaciones()">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="confirmarContrato()">
                        <i class="bi bi-check-circle me-1"></i>Generar contrato
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: VER DETALLE CONTRATO  -->
    <div class="modal fade" id="modalDetalle" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="detalleTitle">Detalle del contrato</h6>
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
                        <i class="bi bi-exclamation-triangle me-2"></i>Eliminar contrato
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="font-size:0.85rem;color:var(--text-secondary);">
                    ¿Eliminar el contrato <strong id="confirmCod" style="color:var(--text-primary);"></strong>?
                    Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-danger btn-sm" onclick="eliminarContrato()">Sí, eliminar</button>
                </div>
            </div>
        </div>
    </div>
<?= $footer ?>