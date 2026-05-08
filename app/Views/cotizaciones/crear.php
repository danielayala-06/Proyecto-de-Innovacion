<?= $header ?>

<main class="main-content" id="main-content">
<div class="container">
    <p class="section-label">Cotizaciones</p>

        <!-- FORMULARIO -->
        <form id="form-cotizacion" class="col-12 ">
            <input type="hidden" name="id_cliente" id="idCliente">
            <div class="d-flex justify-content-center gap-4">
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
                                <label for="dniCliente" class="form-label">DNI*</label>
                                <input type="text" class="form-control" id="dniCliente" name="dni">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="telefonoCliente" class="form-label">Teléfono*</label>
                                <input type="text" class="form-control" id="telefonoCliente" name="telefono">
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
                            <legend class="section-divider">Paquetes</legend>

                            <button type="button" class="btn-paquete mt-2" id="btn-modal-paquete">
                                <i class="bi bi-plus-circle me-1"></i> Agregar paquete
                            </button>
                            <div id="paquetesContainer" class="d-flex flex-column gap-2"></div>

                        </fieldset>

                        <!-- SERVICIOS -->
                        <fieldset class="col-12 col-md-7">
                            <legend class="section-divider">Servicios</legend>
                            <button type="button" class="btn-paquete mt-2" id="btn-modal-servicio">
                                <i class="bi bi-plus-circle me-1"></i> Agregar servicio
                            </button>
                            <div id="serviciosContainer" class="d-flex flex-column gap-2"></div>

                        </fieldset>
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
                            <div class="col-12 col-md-6">
                                <label for="nombreColegio" class="form-label" id="labelInstitucion">Nombre del colegio*</label>
                                <input type="text" class="form-control" id="nombreColegio"
                                       name="nombre_colegio" required minlength="3" maxlength="100"
                                       placeholder="Ej: I.E. San Marcos">
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

                            <!-- N.° de estudiantes / participantes (label y max cambian según tipo) -->
                            <div class="col-6 col-md-3">
                                <label for="numEstudiantes" class="form-label" id="labelEstudiantes">N.° estudiantes</label>
                                <input type="number" class="form-control" id="numEstudiantes"
                                       name="num_estudiantes" min="1" max="100" placeholder="0">
                                <div class="form-text" id="textoMaxEstudiantes">Máximo 100 personas.</div>
                            </div>

                            <!-- Grado (oculto para Grado Superior y Empresas) -->
                            <div class="col-6 col-md-4" id="wrap-grado">
                                <label for="gradoProm" class="form-label">Grado*</label>
                                <select class="form-select" id="gradoProm" name="grado">
                                    <option value="">Seleccionar grado...</option>
                                    <option value="5 añitos">5 añitos</option>
                                    <option value="6to primaria">6to primaria</option>
                                    <option value="5to secundaria">5to secundaria</option>
                                </select>
                            </div>

                            <!-- Sección (oculta para Grado Superior y Empresas) -->
                            <div class="col-6 col-md-3" id="wrap-seccion">
                                <label for="seccionProm" class="form-label">Sección</label>
                                <input type="text" class="form-control" id="seccionProm"
                                       name="seccion" maxlength="10"
                                       placeholder="Ej: A, B, C…">
                            </div>

                            <!-- Fecha y hora de la sesión -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">Fecha y hora de la sesión</label>
                                <div class="d-flex gap-2">
                                    <input type="date" class="form-control" id="fechaInicio-date">
                                    <select class="form-select" id="fechaInicio-time"
                                            style="flex:0 0 auto;width:115px;">
                                        <option value="">Hora</option>
                                    </select>
                                </div>
                                <input type="hidden" name="fechaInicio" id="fechaInicio">
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
                <div class="col-md-3">
                    <div class="resumen-card mb-3">
                        <div class="resumen-title">Resumen</div>
                        <div id="resumenItems">
                            <div class="resumen-row" style="color:#666;font-size:0.8rem;justify-content:center;">Sin ítems aún</div>
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
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
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
                <h6 class="modal-title">Seleccionar paquete</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="cat-tabs">
                    <button class="cat-tab active" onclick="cambiarCategoria('quinceaneros', this)">Paquetes</button>
                    <button class="cat-tab" onclick="cambiarCategoria('cuadros', this)">Cuadros</button>
                    <button class="cat-tab" onclick="cambiarCategoria('anuarios', this)">Anuarios</button>
                </div>

                <div class="cat-panel active overflow-auto" id="panel-quinceaneros" style="max-height:20rem;">
                    <!-- Cargado por JS desde la API -->
                </div>

                <div class="cat-panel overflow-auto" id="panel-cuadros" style="max-height:20rem;">
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Cuadro 30x40 cm','Impresión en canvas, 1 foto',80)">
                        <div class="po-left"><div class="po-name">Cuadro 30x40 cm</div><div class="po-desc">Impresión en canvas, 1 foto</div></div>
                        <span class="po-price">S/ 80.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Cuadro 50x70 cm','Impresión en canvas, 1 foto',130)">
                        <div class="po-left"><div class="po-name">Cuadro 50x70 cm</div><div class="po-desc">Impresión en canvas, 1 foto</div></div>
                        <span class="po-price">S/ 130.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Pack Cuadros x3','3 cuadros 20x30 a elección',200)">
                        <div class="po-left"><div class="po-name">Pack Cuadros x3</div><div class="po-desc">3 cuadros 20x30 a elección</div></div>
                        <span class="po-price">S/ 200.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                </div>

                <div class="cat-panel overflow-auto" id="panel-anuarios" style="max-height:20rem;">
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Anuario Básico','20 páginas, tapa blanda, 30 fotos',120)">
                        <div class="po-left"><div class="po-name">Anuario Básico</div><div class="po-desc">20 páginas, tapa blanda, 30 fotos</div></div>
                        <span class="po-price">S/ 120.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Anuario Estándar','40 páginas, tapa dura, 60 fotos',200)">
                        <div class="po-left"><div class="po-name">Anuario Estándar</div><div class="po-desc">40 páginas, tapa dura, 60 fotos</div></div>
                        <span class="po-price">S/ 200.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Anuario Premium','60 páginas, tapa dura, fotos ilimitadas + USB',320)">
                        <div class="po-left"><div class="po-name">Anuario Premium</div><div class="po-desc">60 páginas, tapa dura, fotos ilimitadas + USB</div></div>
                        <span class="po-price">S/ 320.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                </div>

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
/* ── Validación Fecha y Hora de la sesión ─────────────────── */
(function () {
    const inputFecha = document.getElementById('fechaInicio-date');
    const selHora    = document.getElementById('fechaInicio-time');

    function pad(n) { return String(n).padStart(2, '0'); }

    /* Fecha mínima = hoy */
    const hoy = new Date();
    const minFecha = `${hoy.getFullYear()}-${pad(hoy.getMonth() + 1)}-${pad(hoy.getDate())}`;
    inputFecha.min = minFecha;

    /* Genera opciones de hora (06:00 – 21:00 cada 30 min) */
    function generarHoras(soloFuturas) {
        const ahora    = new Date();
        const hActual  = ahora.getHours();
        const mActual  = ahora.getMinutes();
        const opciones = [];

        for (let h = 6; h <= 21; h++) {
            for (let m = 0; m < 60; m += 30) {
                if (soloFuturas && (h < hActual || (h === hActual && m <= mActual))) continue;
                opciones.push(`${pad(h)}:${pad(m)}`);
            }
        }
        return opciones;
    }

    function poblarHoras(soloFuturas) {
        const anterior = selHora.value;
        selHora.innerHTML = '<option value="">Hora</option>';
        generarHoras(soloFuturas).forEach(function (h) {
            const opt = document.createElement('option');
            opt.value = h;
            opt.textContent = h;
            selHora.appendChild(opt);
        });
        /* Mantener la hora elegida si sigue disponible */
        if ([...selHora.options].some(o => o.value === anterior)) selHora.value = anterior;
    }

    function esHoy(valor) {
        return valor === minFecha;
    }

    /* Poblar horas al cargar (sin fecha elegida = todas disponibles) */
    poblarHoras(false);

    /* Al cambiar la fecha filtrar horas si es hoy */
    inputFecha.addEventListener('change', function () {
        if (this.value && this.value < minFecha) {
            this.value = minFecha; /* fuerza al mínimo si el navegador lo permite */
        }
        poblarHoras(esHoy(this.value));
    });

    /* ── N.° estudiantes: solo dígitos, sin símbolos ni emojis ── */
    const inputAlumnos = document.getElementById('numEstudiantes');

    /* Bloquea teclas no numéricas antes de que lleguen al input */
    inputAlumnos.addEventListener('keydown', function (e) {
        const permitidas = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
        if (permitidas.includes(e.key)) return;
        if (!/^\d$/.test(e.key)) e.preventDefault();
    });

    /* Limpia lo que llega por paste o autocompletado */
    inputAlumnos.addEventListener('input', function () {
        const soloDigitos = this.value.replace(/\D/g, '');
        const numero = parseInt(soloDigitos, 10);
        if (!soloDigitos || isNaN(numero)) { this.value = ''; return; }
        this.value = Math.min(parseInt(this.max) || 100, Math.max(1, numero));
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

        document.getElementById('labelEstudiantes').textContent  = c.labelEstudiantes;
        document.getElementById('textoMaxEstudiantes').textContent = c.textoMax;
        const numInput = document.getElementById('numEstudiantes');
        numInput.max = c.max;
        if (numInput.value && parseInt(numInput.value) > c.max) numInput.value = c.max;

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
