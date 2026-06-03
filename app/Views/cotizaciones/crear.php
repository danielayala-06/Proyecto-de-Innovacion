<?= $header ?>

<main class="main-content" id="main-content">
<div class="container">
    <p class="section-label">Cotizaciones</p>

        <!-- FORMULARIO -->
        <form id="form-cotizacion" class="col-12 ">
            <input type="hidden" name="id_cliente" id="idCliente">
            <div class="d-flex justify-content-center gap-4 align-items-start">
                <div class="form-card">

                    <header class="card-heading">
                        <h2 class="h5 m-0">Nueva cotización</h2>
                    </header>

                    <!-- ================= CLIENTE ================= -->
                    <fieldset class="mb-4">
                        <legend class="section-divider">Datos del Cliente</legend>

                        <!-- Buscador -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-7">
                                <div class="input-group search-wrap">
                                    <label for="searchCliente" class="visually-hidden">Buscar cliente</label>
                                    <input type="text" class="form-control" id="searchCliente"
                                           placeholder="DNI, teléfono o nombre...">
                                    <button class="btn btn-outline-secondary" type="button" id="btnBuscar">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                                <div id="searchFeedback" class="form-text mt-1"></div>
                            </div>
                        </div>

                        <!-- Datos -->
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="nombresCliente" class="form-label">Nombres*</label>
                                <input type="text" class="form-control" id="nombresCliente" name="nombres_cliente">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="apellidosCliente" class="form-label">Apellidos*</label>
                                <input type="text" class="form-control" id="apellidosCliente" name="apellidos_cliente">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Documento*</label>
                                <div class="input-group">
                                    <select class="form-select" id="tipoDocumento" name="tipo_documento"
                                            style="max-width:175px;flex-shrink:0;">
                                        <option value="DNI">DNI</option>
                                        <option value="CE">Carnet de Extranjería</option>
                                        <option value="PASAPORTE">Pasaporte</option>
                                    </select>
                                    <input type="text" class="form-control" id="dniCliente"
                                           name="dni" placeholder="8 dígitos numéricos"
                                           maxlength="12">
                                </div>
                                <div id="docFeedback" class="form-text mt-1" style="font-size:.75rem;min-height:1rem;"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="telefonoCliente" class="form-label">Teléfono*</label>
                                <input type="text" class="form-control" id="telefonoCliente"
                                       name="telefono" placeholder="9XXXXXXXX" maxlength="9">
                                <div id="telFeedback" class="form-text mt-1" style="font-size:.75rem;min-height:1rem;"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="emailCliente" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="emailCliente" name="correo">
                            </div>
                        </div>
                    </fieldset>

                    <!-- ================= SERVICIOS Y PAQUETES ================= -->
                    <div class="row g-4">

                        <!-- PAQUETES -->
                        <fieldset class="col-12 col-md-5">
                            <legend class="section-divider">Productos</legend>

                            <button type="button" class="btn-paquete mt-2" id="btn-modal-paquete">
                                <i class="bi bi-plus-circle me-1"></i> Agregar producto
                            </button>
                            <div id="paquetesContainer" class="d-flex flex-column gap-2"></div>

                        </fieldset>

                        <!-- CORTESÍAS -->
                        <fieldset class="col-12 col-md-7">
                            <legend class="section-divider">
                                Productos de cortesía
                                <span style="font-size:.72rem;color:var(--text-muted);font-weight:400;"> — gratuitos</span>
                            </legend>

                            <div id="cortesiasContainer" class="d-flex flex-column gap-2 mb-1"></div>

                            <!-- Formulario inline para agregar -->
                            <div id="cortesia-add-wrap" style="display:none;background:var(--bg-input);border:1px solid var(--border);border-radius:8px;padding:.6rem;margin-bottom:.4rem;">
                                <div class="row g-2 align-items-end">
                                    <div class="col">
                                        <label style="font-size:.72rem;color:var(--text-muted);margin-bottom:3px;display:block;">Descripción</label>
                                        <input type="text" class="form-control form-control-sm" id="cortesiaProducto"
                                               placeholder="Ej: Cuadro laminado para el docente"
                                               autocomplete="off">
                                    </div>
                                    <div class="col-auto" style="min-width:76px;">
                                        <label style="font-size:.72rem;color:var(--text-muted);margin-bottom:3px;display:block;">Cantidad</label>
                                        <input type="number" class="form-control form-control-sm" id="cortesiaCant" value="1" min="1" max="99">
                                    </div>
                                    <div class="col-auto d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-primary" id="btn-confirmar-cortesia">
                                            <i class="bi bi-check2"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" id="btn-cancelar-cortesia">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn-paquete mt-1" id="btn-agregar-cortesia">
                                <i class="bi bi-gift me-1"></i> Agregar cortesía
                            </button>
                        </fieldset>

                        <!-- SERVICIOS -->
                        <!-- <fieldset class="col-12 col-md-7">
                            <legend class="section-divider">Servicios</legend>
                            <button type="button" class="btn-paquete mt-2" id="btn-modal-servicio">
                                <i class="bi bi-plus-circle me-1"></i> Agregar servicio
                            </button>
                            <div id="serviciosContainer" class="d-flex flex-column gap-2"></div>

                        </fieldset> -->
                    </div>

                    <!-- ================= COLEGIO ================= -->
                    <fieldset class="mt-4">
                        <legend class="section-divider">Institución</legend>

                        <!-- Tipo de institución -->
                        <div class="btn-group btn-group-sm mb-3" role="group" aria-label="Tipo de institución">
                            <input type="radio" class="btn-check" name="tipoInstitucion" id="tipo-colegio" value="colegio" checked>
                            <label class="btn btn-outline-secondary" for="tipo-colegio">Colegio</label>

                            <input type="radio" class="btn-check" name="tipoInstitucion" id="tipo-superior" value="superior">
                            <label class="btn btn-outline-secondary" for="tipo-superior">Grado Superior</label>

                            <input type="radio" class="btn-check" name="tipoInstitucion" id="tipo-empresa" value="empresa">
                            <label class="btn btn-outline-secondary" for="tipo-empresa">Empresas</label>
                        </div>

                        <div class="row g-3">
                            <!-- Nombre de la institución (label cambia según tipo) -->
                            <div class="col-12 col-md-5">
                                <label for="nombreColegio" class="form-label" id="labelInstitucion">Nombre del colegio*</label>
                                <input type="text" class="form-control" id="nombreColegio"
                                       name="nombre_colegio" required minlength="3" maxlength="100"
                                       placeholder="Ej: I.E. SAN MARCOS"
                                       oninput="this.value=this.value.toUpperCase()">
                            </div>

                            <!-- Provincia -->
                            <div class="col-6 col-md-3">
                                <label for="provinciaColegio" class="form-label">Provincia</label>
                                <select class="form-select" id="provinciaColegio" name="provincia_colegio">
                                    <option value="">Seleccionar...</option>
                                    <option value="Chincha">Chincha</option>
                                    <option value="Ica">Ica</option>
                                    <option value="Pisco">Pisco</option>
                                    <option value="Cañete">Cañete</option>
                                </select>
                            </div>

                            <!-- Distrito (cascada) -->
                            <div class="col-6 col-md-3">
                                <label for="distritoColegio" class="form-label">Distrito</label>
                                <select class="form-select" id="distritoColegio"
                                        name="distrito_colegio" disabled>
                                    <option value="">Selecciona provincia...</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <!-- ================= PROMOCIÓN Y SESIÓN ================= -->
                    <fieldset class="mt-4">
                        <legend class="section-divider">Promoción y sesión fotográfica</legend>

                        <div class="row g-3">
                            <!-- Nombre de la promoción / evento (label cambia según tipo) -->
                            <div class="col-12 col-md-5">
                                <label for="nombreProm" class="form-label" id="labelPromocion">Nombre de la promoción</label>
                                <input type="text" class="form-control" id="nombreProm"
                                       name="nombre_promocion" maxlength="100"
                                       placeholder="Ej: Promoción 2025">
                            </div>

                            <input type="hidden" id="numEstudiantes" name="num_estudiantes">

                            <!-- Nivel (oculto para Grado Superior y Empresas) -->
                            <div class="col-6 col-md-3" id="wrap-grado">
                                <label for="gradoProm" class="form-label">Nivel*</label>
                                <select class="form-select" id="gradoProm" name="grado">
                                    <option value="">Seleccionar nivel...</option>
                                    <option value="Inicial">Inicial</option>
                                    <option value="Jardin">Jardín</option>
                                    <option value="Primaria">Primaria</option>
                                    <option value="Secundaria">Secundaria</option>
                                </select>
                            </div>

                            <!-- Sección (oculta para Grado Superior y Empresas) -->
                            <div class="col-6 col-md-3" id="wrap-seccion">
                                <label for="seccionProm" class="form-label">Sección</label>
                                <input type="text" class="form-control" id="seccionProm"
                                       name="seccion" maxlength="10"
                                       placeholder="Ej: A, B, C…"
                                       oninput="this.value=this.value.toUpperCase()">
                            </div>

                            <!-- Observaciones -->
                            <div class="col-12">
                                <label for="notas" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="notas" name="observaciones"
                                          rows="3"
                                          placeholder="Notas o acuerdos adicionales sobre la sesión..."></textarea>
                            </div>
                        </div>
                    </fieldset>

                </div>

                <!-- RESUMEN -->
                <div class="col-md-3 resumen-sticky">
                    <div class="resumen-card mb-3">
                        <div class="resumen-title">Resumen</div>
                        <div id="resumenItems">
                            <div class="resumen-row" style="color:#666;font-size:0.8rem;justify-content:center;">Sin ítems aún</div>
                        </div>
                        <!-- Subtotal (visible solo cuando hay descuento) -->
                        <div id="resumen-subtotal-row" class="resumen-row mt-1" style="display:none;color:var(--text-muted);font-size:0.78rem;">
                            <span>Subtotal</span>
                            <span id="resumenSubtotal"></span>
                        </div>
                        <!-- Input de descuento -->
                        <div class="resumen-row mt-2" style="flex-direction:column;align-items:stretch;gap:4px;">
                            <label for="descuentoMonto" style="font-size:0.75rem;color:var(--text-muted);margin:0;">
                                <i class="bi bi-tag me-1"></i>Descuento (S/)
                            </label>
                            <input type="number" id="descuentoMonto" min="0" step="1" value=""
                                   placeholder="0"
                                   style="width:100%;border:1px solid var(--border);border-radius:4px;
                                          padding:3px 8px;font-size:0.82rem;background:var(--bg-input);
                                          color:var(--text-primary);text-align:right;">
                        </div>
                        <!-- Fila de descuento calculado (visible solo cuando hay descuento) -->
                        <div id="resumen-desc-row" class="resumen-row" style="display:none;font-size:0.78rem;color:var(--green-text,#2e7d32);">
                            <span>Ahorro</span>
                            <span id="resumenDescInfo"></span>
                        </div>
                        <div class="resumen-row total mt-2">
                            <span>Total</span>
                            <span id="totalResumen">S/ 0.00</span>
                        </div>
                    </div>
                    <div class="resumen-card mb-3">
                        <div class="resumen-title">Cliente seleccionado</div>
                        <div id="clienteSeleccionado" style="font-size:0.82rem;color:#666;">Ningún cliente seleccionado</div>
                    </div>
                    <button class="btn-guardar" type="submit">
                        <i class="bi bi-check-circle me-2"></i>Guardar cotización
                    </button>
                </div>
        <!--FIN DEL FORMULARIO-->
            </div>
        </form>

</div>
</main>

<!-- MODAL SERVICIOS -->
<div class="modal fade" id="modalServicio" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Agregar servicio</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="panel-servicios">
                    <!-- Cargado por JS -->
                </div>
                <div class="mt-3">
                    <label for="servicioModalPrecio" class="form-label">Precio (S/)</label>
                    <input type="number" class="form-control" id="servicioModalPrecio" placeholder="0.00" min="0" step="0.01">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn->sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" id="btn-confirmar-servicio">Agregar servicio</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PAQUETES POR CATEGORÍA -->
<div class="modal fade" id="modalPaquete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Seleccionar productos</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- N.° de estudiantes -->
                <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.9rem;
                            padding-bottom:.8rem;border-bottom:1px solid var(--border-color,#dde3ee);">
                    <div style="flex:1;">
                        <label for="modalNumEstudiantes"
                               style="font-size:.78rem;font-weight:700;color:var(--text-primary,#333);
                                      margin-bottom:.3rem;display:block;">
                            <i class="bi bi-people-fill me-1" style="color:var(--sidebar-bg,#1b2d6b);"></i>
                            N.° de estudiantes
                        </label>
                        <input type="number" id="modalNumEstudiantes" class="form-control form-control-sm"
                               min="1" max="1000" placeholder="Ej: 30"
                               style="max-width:140px;">
                    </div>
                    <div id="modal-est-hint"
                         style="font-size:.72rem;color:var(--text-muted,#888);line-height:1.4;max-width:200px;padding-top:.25rem;">
                        La cantidad máxima por paquete se limita a este número.
                    </div>
                </div>
                <div class="nivel-filtro-wrap" id="nivelFiltrosContainer"></div>
                <div class="cat-tabs" id="catTabsContainer"></div>
                <div id="catPanelsContainer"></div>
                <div id="paq-hint" style="font-size:.75rem;color:var(--text-muted);margin-top:.4rem;text-align:center;">
                    Haz clic en un paquete para seleccionarlo · puedes elegir varios
                </div>
            </div>
            <div class="modal-footer" style="flex-direction:column;align-items:stretch;gap:.5rem;padding:.85rem 1.25rem;">
                <!-- Footer normal -->
                <div id="paq-footer-normal" style="display:flex;justify-content:flex-end;gap:.5rem;width:100%;">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary btn-sm" id="btn-confirmar-paquetes" disabled>
                        Selecciona un paquete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMACIÓN DE COTIZACIÓN -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width:660px;">
        <div class="modal-content">

            <div class="modal-header" style="background:var(--sidebar-bg,#1A1814);border-bottom:none;padding:1rem 1.25rem;">
                <div>
                    <h6 class="modal-title mb-0" style="color:var(--sidebar-active-text,#F2E4BC);font-size:1rem;font-weight:700;letter-spacing:.3px;">
                        <i class="bi bi-file-earmark-check me-2"></i>Resumen de cotización
                    </h6>
                    <div style="color:var(--sidebar-link,#C8BCA8);font-size:.75rem;margin-top:2px;opacity:.75;">
                        Revisa los datos antes de confirmar
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding:1.25rem;background:var(--bg-surface,#FAFAF8);">

                <!-- Bloque destacado: estudiantes + total -->
                <div style="display:flex;gap:.75rem;margin-bottom:1.1rem;">
                    <div id="conf-bloque-estudiantes"
                         style="flex:1;background:var(--bg-elevated,#fff);border-radius:10px;
                                padding:.85rem 1rem;text-align:center;border:1.5px solid var(--border,#D6D0C8);">
                        <div style="font-size:1.9rem;font-weight:800;color:var(--accent,#B49040);line-height:1;"
                             id="conf-num-estudiantes">—</div>
                        <div style="font-size:.72rem;color:var(--text-muted,#7C7468);margin-top:3px;text-transform:uppercase;letter-spacing:.5px;">
                            Estudiantes
                        </div>
                    </div>
                    <div style="flex:1;background:var(--bg-elevated,#fff);border-radius:10px;
                                padding:.85rem 1rem;text-align:center;border:1.5px solid var(--border,#D6D0C8);">
                        <div style="font-size:1.9rem;font-weight:800;color:var(--green-text,#1A5E2E);line-height:1;"
                             id="conf-total">S/ 0.00</div>
                        <div style="font-size:.72rem;color:var(--text-muted,#7C7468);margin-top:3px;text-transform:uppercase;letter-spacing:.5px;">
                            Total estimado
                        </div>
                    </div>
                </div>

                <!-- Tabla de paquetes -->
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                            letter-spacing:.6px;color:var(--text-muted,#7C7468);margin-bottom:.5rem;">
                    Paquetes y servicios
                </div>
                <div id="conf-tabla-items"
                     style="border:1.5px solid var(--border,#D6D0C8);border-radius:8px;overflow:hidden;">
                    <!-- Poblado por JS -->
                </div>

                <!-- Nota/observaciones (si existen) -->
                <div id="conf-wrap-notas" style="display:none;margin-top:.9rem;">
                    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;
                                letter-spacing:.6px;color:var(--text-muted,#7C7468);margin-bottom:.35rem;">Notas</div>
                    <div id="conf-notas"
                         style="font-size:.82rem;color:var(--text-primary,#1C1916);
                                background:var(--bg-elevated,#fff);border-radius:7px;
                                padding:.6rem .85rem;border:1px solid var(--border,#D6D0C8);"></div>
                </div>

            </div>

            <div class="modal-footer" style="border-top:1px solid var(--border,#D6D0C8);padding:.9rem 1.25rem;gap:.5rem;background:var(--bg-surface,#FAFAF8);">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-pencil me-1"></i>Revisar
                </button>
                <button type="button" class="btn btn-sm" id="btn-confirmar-cotizacion"
                        style="background:var(--accent,#B49040);color:#fff;font-weight:600;padding:.45rem 1.2rem;border:none;">
                    <i class="bi bi-check-circle me-1"></i>Confirmar y guardar
                </button>
            </div>

        </div>
    </div>
</div>

<script>const BASE_URL = "<?= base_url('') ?>"</script>

<script>
/* ── Cascada Provincia → Distrito ─────────────────────────── */
(function () {
    const DISTRITOS = {
        'Chincha': [
            'Alto Larán', 'Chavin', 'Chincha Alta', 'Chincha Baja',
            'El Carmen', 'Grocio Prado', 'Pueblo Nuevo',
            'San Juan de Yanac', 'San Pedro de Huacarpana',
            'Sunampe', 'Tambo de Mora'
        ],
        'Pisco': [
            'Huancano', 'Humay', 'Independencia', 'Paracas',
            'Pisco', 'San Andrés', 'San Clemente', 'Túpac Amaru Inca'
        ],
        'Ica': [
            'Ica', 'La Tinguiña', 'Los Aquijes', 'Ocucaje', 'Pachacutec',
            'Parcona', 'Pueblo Nuevo', 'Salas', 'San José de Los Molinos',
            'San Juan Bautista', 'Santiago', 'Subtanjalla', 'Tate', 'Yauca del Rosario'
        ],
        'Cañete': [
            'Asia', 'Calango', 'Cerro Azul', 'Chilca', 'Coayllo',
            'Imperial', 'Lunahuaná', 'Mala', 'Nuevo Imperial',
            'Quilmaná', 'San Antonio', 'San Luis',
            'San Vicente de Cañete', 'Santa Cruz de Flores', 'Zúñiga'
        ]
    };

    const selProv = document.getElementById('provinciaColegio');
    const selDist = document.getElementById('distritoColegio');

    selProv.addEventListener('change', function () {
        const distritos = DISTRITOS[this.value] || [];

        selDist.innerHTML = '<option value="">Seleccionar...</option>';
        distritos.forEach(function (d) {
            const opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            selDist.appendChild(opt);
        });

        selDist.disabled = distritos.length === 0;
    });
})();
</script>


<script>
/* ── Selector de tipo de institución ──────────────────────── */
(function () {
    const TIPOS = {
        colegio: {
            labelInstitucion: 'Nombre del colegio*',
            placeholder:      'Ej: I.E. San Marcos',
            labelPromocion:   'Nombre de la promoción',
            placeholderProm:  'Ej: Promoción 2025',
            labelEstudiantes: 'N.° estudiantes',
            max:              100,
            textoMax:         'Máximo 100 personas.',
            showGrado:        true,
            showSeccion:      true,
        },
        superior: {
            labelInstitucion: 'Nombre de la institución*',
            placeholder:      'Ej: Universidad San Marcos',
            labelPromocion:   'Nombre de la promoción',
            placeholderProm:  'Ej: Promoción 2025',
            labelEstudiantes: 'N.° estudiantes',
            max:              40,
            textoMax:         'Máximo 40 personas.',
            showGrado:        false,
            showSeccion:      false,
        },
        empresa: {
            labelInstitucion: 'Nombre de la empresa*',
            placeholder:      'Ej: Empresa XYZ S.A.C.',
            labelPromocion:   'Nombre del evento',
            placeholderProm:  'Ej: Conferencia anual 2025',
            labelEstudiantes: 'N.° de participantes',
            max:              200,
            textoMax:         'Máximo 200 participantes.',
            showGrado:        false,
            showSeccion:      false,
        },
    };

    function aplicarTipo(tipo) {
        const c = TIPOS[tipo] || TIPOS.colegio;

        document.getElementById('labelInstitucion').textContent  = c.labelInstitucion;
        document.getElementById('nombreColegio').placeholder     = c.placeholder;

        document.getElementById('labelPromocion').textContent    = c.labelPromocion;
        document.getElementById('nombreProm').placeholder        = c.placeholderProm;

        // Actualizar el max del input de estudiantes en el modal de paquetes
        const modalNumInput = document.getElementById('modalNumEstudiantes');
        if (modalNumInput) {
            modalNumInput.max = c.max;
            if (modalNumInput.value && parseInt(modalNumInput.value) > c.max)
                modalNumInput.value = c.max;
        }
        // Actualizar el campo oculto si excede el nuevo máximo
        const hiddenNumEst = document.getElementById('numEstudiantes');
        if (hiddenNumEst && hiddenNumEst.value && parseInt(hiddenNumEst.value) > c.max)
            hiddenNumEst.value = c.max;

        const show = (id, visible) => {
            document.getElementById(id).style.display = visible ? '' : 'none';
        };
        show('wrap-grado',   c.showGrado);
        show('wrap-seccion', c.showSeccion);

        window.TIPO_INSTITUCION = tipo;
    }

    document.querySelectorAll('input[name="tipoInstitucion"]').forEach(radio => {
        radio.addEventListener('change', () => aplicarTipo(radio.value));
    });

    aplicarTipo('colegio');
})();
</script>

<script type="module" src="<?= base_url('js/main.js')?>"></script>

<?= $footer ?>
