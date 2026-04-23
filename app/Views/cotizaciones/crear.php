<?= $header ?>

<main class="main-content">
<div class="container">
    <p class="section-label">Cotizaciones</p>
    <div class="row g-4">

        <!-- FORMULARIO -->
        <form id="form-cotizacion" class="col-12 col-xl-8">
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
                            <div class="input-group">
                                <label for="searchCliente" class="visually-hidden">Buscar cliente</label>
                                <input type="text" class="form-control" id="searchCliente" placeholder="Buscar cliente...">
                                <button class="btn btn-outline-secondary" type="button" id="btnBuscar">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Datos -->
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="nombreCliente" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombreCliente">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="dniCliente" class="form-label">DNI</label>
                            <input type="text" class="form-control" id="dniCliente">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="telefonoCliente" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefonoCliente">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="emailCliente" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="emailCliente">
                        </div>
                    </div>
                </fieldset>

                <!-- ================= SERVICIOS Y PAQUETES ================= -->
                <div class="row g-4">

                    <!-- PAQUETES -->
                    <fieldset class="col-12 col-md-5">
                        <legend class="section-divider">Paquetes</legend>

                        <div id="paquetesContainer" class="d-flex flex-column gap-2"></div>

                        <button type="button" class="btn-paquete mt-2" onclick="abrirModalPaquete()">
                            <i class="bi bi-plus-circle me-1"></i> Agregar paquete
                        </button>
                    </fieldset>

                    <!-- SERVICIOS -->
                    <fieldset class="col-12 col-md-7">
                        <legend class="section-divider">Servicios</legend>

                        <!-- Presets -->
                        <div role="group" aria-label="Servicios predefinidos">

                            <div class="servicio-preset-btn" id="btn-dron" onclick="togglePreset('dron')">
                                <div class="sp-left">
                                    <div class="sp-icon"><i class="bi bi-joystick"></i></div>
                                    <span class="sp-name">Toma con Dron</span>
                                </div>
                                <span class="sp-price">S/ 150.00</span>
                                <i class="bi bi-check-circle-fill sp-check"></i>
                            </div>

                            <div class="servicio-preset-btn" id="btn-video" onclick="togglePreset('video')">
                                <div class="sp-left">
                                    <div class="sp-icon"><i class="bi bi-camera-video"></i></div>
                                    <span class="sp-name">Video resumen (3 min)</span>
                                </div>
                                <span class="sp-price">S/ 100.00</span>
                                <i class="bi bi-check-circle-fill sp-check"></i>
                            </div>

                            <div class="servicio-preset-btn" id="btn-foto" onclick="togglePreset('foto')">
                                <div class="sp-left">
                                    <div class="sp-icon"><i class="bi bi-image"></i></div>
                                    <span class="sp-name">Foto</span>
                                </div>

                                <label for="fotoPrecioInput" class="visually-hidden">Precio de foto</label>
                                <input
                                        class="foto-precio-input"
                                        id="fotoPrecioInput"
                                        type="number"
                                        min="0"
                                        placeholder="S/ 0.00"
                                        onclick="event.stopPropagation()"
                                        oninput="onFotoPrecioInput()"
                                >

                                <i class="bi bi-check-circle-fill sp-check"></i>
                            </div>

                        </div>

                        <!-- Lista dinámica -->
                        <div class="servicios-list mt-3">
                            <button type="button" class="btn btn-light servicio-add w-100" onclick="abrirModalServicio()">
                                <i class="bi bi-plus-circle"></i> Agregar servicio +
                            </button>

                            <div id="serviciosList">
                                <div class="servicios-empty">Sin servicios adicionales</div>
                            </div>
                        </div>

                    </fieldset>
                </div>

                <!-- ================= EVENTO ================= -->
                <fieldset class="mt-4">
                    <legend class="section-divider">Detalles del Evento</legend>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="fechaEvento" class="form-label">Fecha del evento</label>
                            <input type="date" class="form-control" id="fechaEvento">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="tipoEvento" class="form-label">Tipo de evento</label>
                            <select class="form-select" id="tipoEvento">
                                <option value="">Seleccionar...</option>
                                <option>Matrimonio</option>
                                <option>Quinceañero</option>
                                <option>Retrato</option>
                                <option>Corporativo</option>
                                <option>Otro</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="notas" class="form-label">Notas adicionales</label>
                            <textarea
                                    class="form-control"
                                    id="notas"
                                    rows="3"
                                    placeholder="Observaciones o detalles del contrato..."
                            ></textarea>
                        </div>
                    </div>
                </fieldset>

            </div>
        </form>
        <!-- RESUMEN -->
        <div class="col-12 col-xl-4">
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
            <button class="btn-guardar" onclick="guardarCotizacion()">
                <i class="bi bi-check-circle me-2"></i>Guardar cotización
            </button>
        </div>
    </div>
</div>
</main>

<!-- MODAL SERVICIO PERSONALIZADO -->
<div class="modal fade" id="modalServicio" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Agregar servicio</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Nombre del servicio</label>
                    <input type="text" class="form-control" id="servicioNombre" placeholder="Ej: Sesión de fotos 2h">
                </div>
                <div class="mb-3">
                    <label>Precio (S/)</label>
                    <input type="number" class="form-control" id="servicioPrecio" placeholder="0.00">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" onclick="agregarServicioCustom()">Agregar</button>
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
                    <button class="cat-tab active" onclick="cambiarCategoria('quinceaneros', this)">Quinceañeros</button>
                    <button class="cat-tab" onclick="cambiarCategoria('cuadros', this)">Cuadros</button>
                    <button class="cat-tab" onclick="cambiarCategoria('anuarios', this)">Anuarios</button>
                </div>

                <div class="cat-panel active" id="panel-quinceaneros">
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Paquete Quinceañero Básico','Sesión + 50 fotos editadas',350)">
                        <div class="po-left"><div class="po-name">Paquete Básico</div><div class="po-desc">Sesión + 50 fotos editadas</div></div>
                        <span class="po-price">S/ 350.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Paquete Quinceañero Plus','Sesión + 100 fotos + álbum 20×30',550)">
                        <div class="po-left"><div class="po-name">Paquete Plus</div><div class="po-desc">Sesión + 100 fotos + álbum 20×30</div></div>
                        <span class="po-price">S/ 550.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Paquete Quinceañero Premium','Sesión + 150 fotos + álbum + video',850)">
                        <div class="po-left"><div class="po-name">Paquete Premium</div><div class="po-desc">Sesión + 150 fotos + álbum + video</div></div>
                        <span class="po-price">S/ 850.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                </div>

                <div class="cat-panel" id="panel-cuadros">
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Cuadro 30×40 cm','Impresión en canvas, 1 foto',80)">
                        <div class="po-left"><div class="po-name">Cuadro 30×40 cm</div><div class="po-desc">Impresión en canvas, 1 foto</div></div>
                        <span class="po-price">S/ 80.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Cuadro 50×70 cm','Impresión en canvas, 1 foto',130)">
                        <div class="po-left"><div class="po-name">Cuadro 50×70 cm</div><div class="po-desc">Impresión en canvas, 1 foto</div></div>
                        <span class="po-price">S/ 130.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                    <div class="paquete-option" onclick="seleccionarOpcion(this,'Pack Cuadros ×3','3 cuadros 20×30 a elección',200)">
                        <div class="po-left"><div class="po-name">Pack Cuadros ×3</div><div class="po-desc">3 cuadros 20×30 a elección</div></div>
                        <span class="po-price">S/ 200.00</span><i class="bi bi-check-circle-fill po-check"></i>
                    </div>
                </div>

                <div class="cat-panel" id="panel-anuarios">
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
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-sm" onclick="confirmarPaquete()">Agregar paquete</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // referencia al formulario de registro de cotizaciones
        const form = document.getElementById("form-cotizacion");

        // Capturamos los datos del formulario:
        form.addEventListener("submit", (event) => {
            event.preventDefault();
            const formData = new FormData(form);
            const nombre = formData.get("nombre");
        })

        document.getElementById('toggleSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.getElementById('main-content').classList.toggle('expanded');
        });

        const presets = { dron: false, video: false, foto: false };
        let fotoPrecio = 0;
        let serviciosCustom = [];
        let paquetes = [];
        let paqueteSeleccionado = null;

        function togglePreset(key) {
            presets[key] = !presets[key];
            document.getElementById('btn-' + key).classList.toggle('selected', presets[key]);
            actualizarResumen();
        }

        function onFotoPrecioInput() {
            fotoPrecio = parseFloat(document.getElementById('fotoPrecioInput').value) || 0;
            if (presets.foto) actualizarResumen();
        }

        function getServiciosPreset() {
            const r = [];
            if (presets.dron)  r.push({ nombre: 'Toma con Dron', precio: 150 });
            if (presets.video) r.push({ nombre: 'Video resumen (3 min)', precio: 100 });
            if (presets.foto)  r.push({ nombre: 'Foto', precio: fotoPrecio });
            return r;
        }

        function abrirModalServicio() {
            new bootstrap.Modal(document.getElementById('modalServicio')).show();
        }

        function agregarServicioCustom() {
            const nombre = document.getElementById('servicioNombre').value.trim();
            const precio = parseFloat(document.getElementById('servicioPrecio').value) || 0;
            if (!nombre) return;
            serviciosCustom.push({ nombre, precio });
            renderServiciosCustom();
            actualizarResumen();
            document.getElementById('servicioNombre').value = '';
            document.getElementById('servicioPrecio').value = '';
            bootstrap.Modal.getInstance(document.getElementById('modalServicio')).hide();
        }

        function eliminarServicioCustom(i) {
            serviciosCustom.splice(i, 1);
            renderServiciosCustom();
            actualizarResumen();
        }

        function renderServiciosCustom() {
            const cont = document.getElementById('serviciosList');
            if (!serviciosCustom.length) {
                cont.innerHTML = '<div class="servicios-empty">Sin servicios adicionales</div>';
                return;
            }
            cont.innerHTML = serviciosCustom.map((s, i) => `
            <div class="servicio-item">
              <span class="servicio-name">${s.nombre}</span>
              <span class="servicio-price">S/ ${s.precio.toFixed(2)}</span>
              <button class="btn-remove" onclick="eliminarServicioCustom(${i})"><i class="bi bi-x"></i></button>
            </div>`).join('');
        }

        function abrirModalPaquete() {
            paqueteSeleccionado = null;
            document.querySelectorAll('.paquete-option').forEach(el => el.classList.remove('selected'));
            new bootstrap.Modal(document.getElementById('modalPaquete')).show();
        }

        function cambiarCategoria(cat, tabEl) {
            document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.cat-panel').forEach(p => p.classList.remove('active'));
            tabEl.classList.add('active');
            document.getElementById('panel-' + cat).classList.add('active');
            paqueteSeleccionado = null;
            document.querySelectorAll('.paquete-option').forEach(el => el.classList.remove('selected'));
        }

        function seleccionarOpcion(el, nombre, desc, precio) {
            document.querySelectorAll('.paquete-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            paqueteSeleccionado = { nombre, desc, precio };
        }

        function confirmarPaquete() {
            if (!paqueteSeleccionado) return;
            paquetes.push({ ...paqueteSeleccionado });
            renderPaquetes();
            actualizarResumen();
            bootstrap.Modal.getInstance(document.getElementById('modalPaquete')).hide();
        }

        function renderPaquetes() {
            const cont = document.getElementById('paquetesContainer');
            cont.innerHTML = paquetes.map((p, i) => `
            <div style="background:#1a1a1a;border:1px solid #2e2e2e;border-radius:8px;padding:10px 13px;font-size:0.81rem;">
              <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="color:#ccc;font-weight:500;">${p.nombre}</span>
                <button style="background:none;border:none;color:#555;cursor:pointer;" onclick="eliminarPaquete(${i})">
                  <i class="bi bi-x"></i>
                </button>
              </div>
              ${p.desc ? `<div style="color:#555;font-size:0.74rem;margin-top:2px;">${p.desc}</div>` : ''}
              <div style="color:#5a9eff;margin-top:4px;font-size:0.82rem;">S/ ${p.precio.toFixed(2)}</div>
            </div>`).join('');
        }

        function eliminarPaquete(i) {
            paquetes.splice(i, 1);
            renderPaquetes();
            actualizarResumen();
        }

        function actualizarResumen() {
            const all = [...getServiciosPreset(), ...serviciosCustom, ...paquetes];
            const total = all.reduce((s, x) => s + x.precio, 0);
            const cont = document.getElementById('resumenItems');
            cont.innerHTML = all.length
                ? all.map(x => `<div class="resumen-row"><span>${x.nombre}</span><span>S/ ${x.precio.toFixed(2)}</span></div>`).join('')
                : '<div class="resumen-row" style="color:#555;font-size:0.79rem;justify-content:center;">Sin ítems aún</div>';
            document.getElementById('totalResumen').textContent = `S/ ${total.toFixed(2)}`;
        }

        document.getElementById('btnBuscar').addEventListener('click', () => {
            const q = document.getElementById('searchCliente').value.trim();
            if (!q) return;
            document.getElementById('nombreCliente').value = q;
            document.getElementById('dniCliente').value = '12345678';
            document.getElementById('telefonoCliente').value = '987654321';
            document.getElementById('emailCliente').value = q.toLowerCase().replace(/\s/g,'') + '@email.com';
            document.getElementById('clienteSeleccionado').innerHTML = `
            <div style="display:flex;align-items:center;gap:8px;">
              <div style="width:26px;height:26px;border-radius:50%;background:#254870;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#7db8f0;">
                ${q.charAt(0).toUpperCase()}
              </div>
              <div>
                <div style="color:#ccc;font-weight:500;font-size:0.82rem;">${q}</div>
                <div style="color:#555;font-size:0.74rem;">DNI: 12345678</div>
              </div>
            </div>`;
        });

        function guardarCotizacion() {
            const nombre = document.getElementById('nombreCliente').value;
            const all = [...getServiciosPreset(), ...serviciosCustom, ...paquetes];
            if (!nombre) { alert('Ingresa los datos del cliente'); return; }
            if (!all.length) { alert('Agrega al menos un servicio o paquete'); return; }
            const total = all.reduce((s, x) => s + x.precio, 0);
            alert(`Cotización guardada para ${nombre}\nTotal: S/ ${total.toFixed(2)}`);
        }
    })


</script>

<?= $footer ?>
