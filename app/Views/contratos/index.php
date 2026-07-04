<?= $header ?>
    <!-- MAIN -->
    <main id="main-content">
        <div class="container">
            
            <p class="page-title">Contratos</p>

            <!-- STATS -->
            <div class="con-stats-bar">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-file-earmark-text-fill"></i></div>
                    <div><div class="stat-label">Total contratos</div><div class="stat-value" id="statTotal">0</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="bi bi-hourglass-split"></i></div>
                    <div><div class="stat-label">Vigentes</div><div class="stat-value" id="statVigentes" style="color:var(--amber-text);">0</div></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-check2-all"></i></div>
                    <div><div class="stat-label">Completados</div><div class="stat-value" id="statCompletados" style="color:var(--green-text);">0</div></div>
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
                    <button id="btnToggleArchivadas" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;display:flex;align-items:center;gap:5px;">
                        <i class="bi bi-archive"></i> Mostrar archivados
                    </button>
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
                        <th class="text-uppercase" onclick="sortBy('codigo')">Código <i class="bi bi-arrow-down-up sort-icon" id="sort-codigo"></i></th>
                        <th class="text-uppercase" onclick="sortBy('cotizacionCod')">Cotización <i class="bi bi-arrow-down-up sort-icon" id="sort-cotizacionCod"></i></th>
                        <th class="text-uppercase" onclick="sortBy('cliente')">Cliente <i class="bi bi-arrow-down-up sort-icon" id="sort-cliente"></i></th>
                        <th class="text-uppercase" onclick="sortBy('fechaEvento')">Primera Sesión <i class="bi bi-arrow-down-up sort-icon" id="sort-fechaEvento"></i></th>
                        <th class="text-uppercase" onclick="sortBy('total')">Total <i class="bi bi-arrow-down-up sort-icon" id="sort-total"></i></th>
                        <th class="text-uppercase" onclick="sortBy('estado')">Estado <i class="bi bi-arrow-down-up sort-icon" id="sort-estado"></i></th>
                        <th class="text-uppercase" onclick="sortBy('creado')">Generado <i class="bi bi-arrow-down-up sort-icon" id="sort-creado"></i></th>
                        <th class="text-uppercase" style="width:60px;text-align:center;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tablaBody"></tbody>
                </table>
                <div class="con-pagination">
                    <span id="paginaInfo" style="font-size:0.78rem;color:var(--text-muted);"></span>
                    <div class="pag-btns" id="paginaBtns"></div>
                </div>
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
                    <h6 class="modal-title" id="modal2Title">
                        <i class="bi bi-file-earmark-text me-2" style="color:var(--accent);"></i>
                        <span id="modal2TitleText">Generar contrato</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Resumen cotización seleccionada -->
                    <div class="con-cotizacion-resumen" id="cotResumen"></div>

                    <!-- Datos del contrato -->
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label>Fecha pago de adelanto</label>
                            <input type="date" class="form-control" id="contratoFechaFirma">
                        </div>
                        <div class="col-md-6">
                            <label>Adelanto / pago inicial (S/)</label>
                            <input type="number" class="form-control" id="contratoAdelanto" placeholder="0.00" min="0.01" step="0.01" onwheel="this.blur()"
                                   oninput="if(parseFloat(this.value)<0)this.value=''">
                            <p id="contratoAdelantoAviso" style="display:none;font-size:.75rem;color:var(--red-text,#dc3545);margin:4px 0 0;"></p>
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
                        <div class="col-12">
                            <label>Cláusulas / condiciones adicionales</label>
                            <textarea class="form-control" id="contratoClausulas" rows="3"
                                      placeholder="Ej: El fotógrafo se compromete a entregar las fotos en un plazo máximo de 30 días..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label>Nombre contacto adicional <span style="color:var(--text-muted);font-weight:400;font-size:.8rem;">(opcional)</span></label>
                            <input type="text" class="form-control" id="contacto2Nombre" placeholder="Nombre del contacto adicional">
                        </div>
                        <div class="col-md-6">
                            <label>Teléfono contacto adicional <span style="color:var(--text-muted);font-weight:400;font-size:.8rem;">(opcional)</span></label>
                            <input type="text" class="form-control" id="contacto2Telefono" placeholder="Ej: 987 654 321">
                        </div>
                        <div class="col-12">
                            <label>Observaciones internas</label>
                            <textarea class="form-control" id="contratoObservaciones" rows="2"
                                      placeholder="Notas internas (no aparecen en el contrato impreso)..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" id="btnVolverCot" onclick="volverACotizaciones()">
                        <i class="bi bi-arrow-left me-1"></i>Volver
                    </button>
                    <button class="btn btn-primary btn-sm" id="btnSubmitContrato" onclick="confirmarContrato()">
                        <i class="bi bi-check-circle me-1"></i><span id="btnSubmitText">Generar contrato</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: ADELANTO BAJO -->
    <div class="modal fade" id="modalAdelantoBajoIndex" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content" style="border-radius:14px;overflow:hidden;">
                <div class="modal-header" style="background:#b45309;border-bottom:none;padding:1rem 1.25rem;">
                    <h6 class="modal-title mb-0" style="color:#fff;font-size:.95rem;font-weight:700;letter-spacing:.3px;">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Adelanto inusualmente bajo
                    </h6>
                </div>
                <div class="modal-body" style="padding:1.35rem 1.25rem;background:var(--bg-surface,#FAFAF8);">
                    <p id="adelantoBajoMensajeIndex" style="font-size:.88rem;color:var(--text-primary);line-height:1.6;margin:0;"></p>
                    <p style="font-size:.8rem;color:var(--text-muted);margin-top:.75rem;margin-bottom:0;">
                        Si ya coordinó este monto con el cliente, puede continuar. De lo contrario, le recomendamos regresar y ajustar el monto.
                    </p>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border-color);padding:.85rem 1.25rem;gap:.5rem;background:var(--bg-surface,#FAFAF8);">
                    <button type="button" id="btnAdelantoBajoIndexVolver"
                            style="color:var(--text-muted);background:none;border:1px solid var(--border-color);border-radius:7px;padding:.35rem .85rem;font-size:.84rem;">
                        <i class="bi bi-arrow-left me-1"></i>Volver y corregir
                    </button>
                    <button type="button" id="btnAdelantoBajoIndexConfirmar"
                            style="background:#b45309;color:#fff;font-weight:600;border:none;border-radius:7px;padding:.4rem 1.1rem;font-size:.84rem;">
                        <i class="bi bi-check2 me-1"></i>Sí, deseo continuar
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
                <div class="modal-footer justify-content-between align-items-center">
                    <div id="detalleAccionesDanger"></div>
                    <div class="d-flex gap-2 align-items-center" id="detalleAcciones"></div>
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
                        <i class="bi bi-x-circle me-2"></i>Cancelar contrato
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="font-size:0.85rem;color:var(--text-secondary);">
                    ¿Estás seguro de que deseas cancelar el contrato <strong id="confirmCod" style="color:var(--text-primary);"></strong>?
                    El contrato quedará archivado y no se podrá reactivar.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Volver</button>
                    <button class="btn btn-danger btn-sm" onclick="eliminarContrato()">
                        <i class="bi bi-x-circle me-1"></i>Sí, cancelar contrato
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL 5: REGISTRAR PAGO -->
    <!-- Confirmación de pago -->
    <div class="modal fade" id="modalConfirmarPago" tabindex="-1" style="z-index:1060;" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
            <div class="modal-content">
                <div class="modal-header" style="background:var(--accent-light);border-bottom:1px solid var(--accent);">
                    <h6 class="modal-title"><i class="bi bi-shield-check me-2" style="color:var(--accent-text);"></i>Confirmar pago</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="font-size:.88rem;">
                    <p style="color:var(--text-muted);margin-bottom:12px;">Revisa los datos antes de registrar. Esta acción no se puede deshacer fácilmente.</p>
                    <table style="width:100%;border-collapse:collapse;">
                        <tr><td style="padding:5px 0;color:var(--text-muted);width:45%;">Monto</td>       <td id="cpMonto"  style="font-weight:700;color:var(--green-text);"></td></tr>
                        <tr><td style="padding:5px 0;color:var(--text-muted);">Forma de pago</td>         <td id="cpForma"  style="font-weight:600;"></td></tr>
                        <tr><td style="padding:5px 0;color:var(--text-muted);">Fecha</td>                 <td id="cpFecha"  style="font-weight:600;"></td></tr>
                        <tr><td style="padding:5px 0;color:var(--text-muted);">Voucher / ref.</td>        <td id="cpVoucher"style="color:var(--text-muted);font-style:italic;"></td></tr>
                        <tr><td style="padding:5px 0;color:var(--text-muted);">Saldo restante</td>        <td id="cpSaldo"  style="font-weight:700;color:var(--amber-text);"></td></tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Corregir</button>
                    <button class="btn btn-success btn-sm" id="btnEjecutarPago">
                        <i class="bi bi-check-circle me-1"></i>Confirmar y registrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPago" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="pagoTitle">
                        <i class="bi bi-cash-coin me-2" style="color:var(--green-text);"></i>
                        Registrar pago
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Resumen financiero -->
                    <div class="row g-0 mb-3" style="text-align:center;font-size:.82rem;background:var(--bg-hover);border-radius:8px;padding:10px 0;border:1px solid var(--border-color);">
                        <div class="col-4" style="border-right:1px solid var(--border-color);">
                            <div style="color:var(--text-muted);margin-bottom:2px;">Total</div>
                            <strong id="pagoTotal">—</strong>
                        </div>
                        <div class="col-4" style="border-right:1px solid var(--border-color);">
                            <div style="color:var(--text-muted);margin-bottom:2px;">Pagado</div>
                            <strong id="pagoPagado" style="color:var(--green-text);">—</strong>
                        </div>
                        <div class="col-4">
                            <div style="color:var(--text-muted);margin-bottom:2px;">Saldo</div>
                            <strong id="pagoSaldo" style="color:var(--amber-text);">—</strong>
                        </div>
                    </div>

                    <!-- Historial de pagos compacto -->
                    <div style="font-size:.73rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">
                        Historial de pagos
                    </div>
                    <div id="pagoHistorial"
                         style="max-height:140px;overflow-y:auto;margin-bottom:14px;border:1px solid var(--border-color);border-radius:6px;"></div>

                    <!-- Formulario nuevo pago -->
                    <hr style="border-color:var(--border-color);margin:0 0 12px;">
                    <div class="row g-2">
                        <div class="col-6">
                            <label style="font-size:.8rem;margin-bottom:3px;">Monto a pagar (S/)</label>
                            <input type="number" class="form-control form-control-sm" id="pagoMonto"
                                   placeholder="0.00" min="0.01" step="0.01" onwheel="this.blur()"
                                   oninput="if(parseFloat(this.value)<0)this.value=''">
                            <p id="pagoMontoAviso" style="display:none;font-size:.75rem;color:var(--red-text,#dc3545);margin:4px 0 0;"></p>
                        </div>
                        <div class="col-6">
                            <label style="font-size:.8rem;margin-bottom:3px;">Fecha de pago</label>
                            <input type="date" class="form-control form-control-sm" id="pagoFecha">
                        </div>
                        <div class="col-12">
                            <label style="font-size:.8rem;margin-bottom:3px;">Forma de pago</label>
                            <select class="form-select form-select-sm" id="pagoFormaPago">
                                <option value="">— Seleccionar —</option>
                            </select>
                            <input type="text" class="form-control form-control-sm mt-1" id="pagoFormaPagoOtro"
                                   placeholder="Ej: Tarjeta, Depósito BCP…"
                                   maxlength="60" style="display:none;">
                        </div>
                        <div class="col-12">
                            <label style="font-size:.8rem;margin-bottom:3px;">
                                Voucher / referencia
                                <span style="color:var(--text-muted);font-weight:400;">(opcional)</span>
                            </label>
                            <input type="text" class="form-control form-control-sm" id="pagoVoucher"
                                   placeholder="N° operación, referencia...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-success btn-sm" id="btnRegistrarPago" onclick="confirmarPago()">
                        <i class="bi bi-check-circle me-1"></i>Registrar pago
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
    const BASE_URL       = "<?= base_url('') ?>";
</script>
<script type="module" src="<?= base_url('js/modules/contratos/contratosIndexMain.js') . '?v=' . filemtime(FCPATH . 'js/modules/contratos/contratosIndexMain.js') ?>"></script>

<?= $footer ?>
