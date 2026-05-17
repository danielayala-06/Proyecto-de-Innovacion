<?= $header ?>

<main class="main-content" id="main-content">
<div class="container">
    <p class="section-label">Cotizaciones</p>

    <a href="<?= base_url('cotizaciones') ?>" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="bi bi-arrow-left me-1"></i> Volver al listado
    </a>

    <!-- FORMULARIO -->
    <form id="form-cotizacion" class="col-12">
        <div class="d-flex justify-content-center gap-4 align-items-start">
            <div class="form-card bg-body-tertiary">

                <header class="card-heading">
                    <h2 class="h5 m-0">
                        Editar cotización
                        <code id="codigoCotizacion" style="font-size:.75rem;opacity:.7;"></code>
                    </h2>
                </header>

                <!-- ================= CLIENTE (solo lectura) ================= -->
                <fieldset class="mb-4">
                    <legend class="section-divider">Datos del Cliente</legend>
                    <div id="clienteInfo" style="font-size:.87rem;color:var(--text-muted);">
                        <div class="placeholder-glow">
                            <span class="placeholder col-6"></span><br>
                            <span class="placeholder col-4 mt-1"></span>
                        </div>
                    </div>
                </fieldset>

                <!-- ================= PAQUETES ================= -->
                <div class="row g-4">
                    <fieldset class="col-12">
                        <legend class="section-divider">Paquetes y servicios</legend>

                        <button type="button" class="btn-paquete mt-2" id="btn-modal-paquete">
                            <i class="bi bi-plus-circle me-1"></i> Agregar paquete
                        </button>
                        <div id="paquetesContainer" class="d-flex flex-column gap-2"></div>
                    </fieldset>
                </div>

                <!-- ================= INSTITUCIÓN ================= -->
                <fieldset class="mt-4">
                    <legend class="section-divider">Institución</legend>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="nombreColegio" class="form-label">Nombre del colegio</label>
                            <input type="text" class="form-control" id="nombreColegio"
                                   name="nombre_colegio" maxlength="100"
                                   placeholder="Ej: I.E. San Marcos">
                        </div>
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
                        <div class="col-6 col-md-3">
                            <label for="distritoColegio" class="form-label">Distrito</label>
                            <select class="form-select" id="distritoColegio"
                                    name="distrito_colegio" disabled>
                                <option value="">Selecciona provincia...</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <!-- ================= PROMOCIÓN ================= -->
                <fieldset class="mt-4">
                    <legend class="section-divider">Promoción</legend>
                    <div class="row g-3">
                        <div class="col-12 col-md-7">
                            <label for="nombreProm" class="form-label">Nombre de la promoción</label>
                            <input type="text" class="form-control" id="nombreProm"
                                   name="nombre_promocion" maxlength="100"
                                   placeholder="Ej: Promoción 2025">
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="numEstudiantes" class="form-label">N.° estudiantes</label>
                            <input type="number" class="form-control" id="numEstudiantes"
                                   name="num_estudiantes" min="1" max="500" placeholder="0">
                        </div>
                    </div>
                </fieldset>

                <!-- ================= OBSERVACIONES ================= -->
                <fieldset class="mt-4">
                    <legend class="section-divider">Observaciones</legend>
                    <div class="row g-3">
                        <div class="col-12">
                            <textarea class="form-control" id="notas" name="observaciones"
                                      rows="3"
                                      placeholder="Notas o acuerdos adicionales..."></textarea>
                        </div>
                    </div>
                </fieldset>

            </div>

            <!-- RESUMEN -->
            <div class="col-md-3 resumen-sticky">
                <div class="resumen-card mb-3 bg-body-tertiary">
                    <div class="resumen-title">Resumen</div>
                    <div id="resumenItems">
                        <div class="resumen-row" style="color:#666;font-size:0.8rem;justify-content:center;">Sin ítems aún</div>
                    </div>
                    <div class="resumen-row total mt-2">
                        <span>Total</span>
                        <span id="totalResumen">S/ 0.00</span>
                    </div>
                </div>
                <button class="btn-guardar" type="submit">
                    <i class="bi bi-check-circle me-2"></i>Guardar cambios
                </button>
            </div>

        </div>
    </form>

</div>
</main>

<!-- MODAL PAQUETES POR CATEGORÍA -->
<div class="modal fade" id="modalPaquete" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Seleccionar paquete</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="nivel-filtro-wrap" id="nivelFiltrosContainer"></div>
                <div class="cat-tabs" id="catTabsContainer"></div>
                <div id="catPanelsContainer"></div>

                <!-- Cantidad -->
                <div class="d-flex align-items-center gap-3 mt-3 pt-3 border-top">
                    <label for="paqueteCantidad" class="form-label mb-0"
                           style="font-size:0.85rem;font-weight:500;white-space:nowrap;">Cantidad:</label>
                    <input type="number" class="form-control form-control-sm" id="paqueteCantidad"
                           value="1" min="1" max="999" style="width:75px;">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" id="btn-confirmar-paquetes">Agregar paquete</button>
            </div>
        </div>
    </div>
</div>

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
            opt.value = d; opt.textContent = d;
            selDist.appendChild(opt);
        });
        selDist.disabled = distritos.length === 0;
    });
})();
</script>
<script>const BASE_URL = "<?= base_url('') ?>"</script>
<script>var COT_ID = <?= (int) $id_cotizacion ?>;</script>
<script type="module" src="<?= base_url('js/modules/cotizaciones/cotizacionEditar.js') ?>"></script>

<?= $footer ?>
