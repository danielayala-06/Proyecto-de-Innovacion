<?= $header ?>
<p class="section-label">Cotizaciones</p>
<div class="row g-4">

    <div class="col-12 col-xl-8">
        <div class="form-card">
            <div class="card-heading">Nueva cotización</div>

            <div class="section-divider">Datos del Cliente</div>
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-7">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchCliente" placeholder="Buscar cliente...">
                        <button class="btn btn-outline-secondary" type="button" id="btnBuscar">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <input type="text" class="form-control" id="nombreCliente" placeholder="Cliente">
                </div>
                <div class="col-12 col-md-6">
                    <input type="text" class="form-control" id="dniCliente" placeholder="DNI">
                </div>
                <div class="col-12 col-md-6">
                    <input type="text" class="form-control" id="telefonoCliente" placeholder="Teléfono">
                </div>
                <div class="col-12 col-md-6">
                    <input type="email" class="form-control" id="emailCliente" placeholder="Correo electrónico">
                </div>
            </div>

            <div class="row g-4">
                <!-- PAQUETES -->
                <div class="col-12 col-md-5">
                    <div class="section-divider">Paquetes</div>
                    <div id="paquetesContainer" class="d-flex flex-column gap-2"></div>
                    <button class="btn-paquete mt-2" onclick="abrirModalPaquete()">
                        <i class="bi bi-plus-circle me-1"></i> Agregar paquete
                    </button>
                </div>

                <!-- SERVICIOS -->
                <div class="col-12 col-md-7">
                    <div class="section-divider">Servicios</div>

                    <!-- Toggles predefinidos -->
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
                        <input class="foto-precio-input" id="fotoPrecioInput" type="number" value="" min="0"
                               placeholder="S/ 0.00" onclick="event.stopPropagation()" oninput="onFotoPrecioInput()">
                        <i class="bi bi-check-circle-fill sp-check"></i>
                    </div>

                    <!-- Lista con botón agregar servicio personalizado -->
                    <div class="servicios-list mt-3">
                        <div class="servicio-add" onclick="abrirModalServicio()">
                            <i class="bi bi-plus-circle"></i> Agregar servicio +
                        </div>
                        <div id="serviciosList">
                            <div class="servicios-empty" id="serviciosEmpty">Sin servicios adicionales</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-12 col-md-6">
                    <label>Fecha del evento</label>
                    <input type="date" class="form-control" id="fechaEvento">
                </div>
                <div class="col-12 col-md-6">
                    <label>Tipo de evento</label>
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
                    <label>Notas adicionales</label>
                    <textarea class="form-control" id="notas" rows="3" placeholder="Observaciones o detalles del contrato..."></textarea>
                </div>
            </div>
        </div>
    </div>

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
<?= $footer ?>
<script>
    document.getElementById('toggleSidebar').addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('hidden');
        document.getElementById('main-content').classList.toggle('expanded');
    });

    // Estado
    const presets = { dron: false, video: false, foto: false };
    let fotoPrecio = 0;
    let serviciosCustom = [];  // servicios añadidos manualmente
    let paquetes = [];
    let paqueteSeleccionado = null;

    // ---- SERVICIOS PRESET ----
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

    // ---- SERVICIOS CUSTOM (modal) ----
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

    // ---- PAQUETES ----
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
        <div style="background:#1e1e1e;border:1px solid #3a3a3a;border-radius:8px;padding:10px 14px;font-size:0.82rem;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <span style="color:#ddd;font-weight:500;">${p.nombre}</span>
            <button style="background:none;border:none;color:#666;cursor:pointer;" onclick="eliminarPaquete(${i})">
              <i class="bi bi-x"></i>
            </button>
          </div>
          ${p.desc ? `<div style="color:#888;font-size:0.78rem;margin-top:2px;">${p.desc}</div>` : ''}
          <div style="color:#4a9eff;margin-top:4px;">S/ ${p.precio.toFixed(2)}</div>
        </div>`).join('');
    }

    function eliminarPaquete(i) {
        paquetes.splice(i, 1);
        renderPaquetes();
        actualizarResumen();
    }

    // ---- RESUMEN ----
    function actualizarResumen() {
        const all = [...getServiciosPreset(), ...serviciosCustom, ...paquetes];
        const total = all.reduce((s, x) => s + x.precio, 0);
        const cont = document.getElementById('resumenItems');
        cont.innerHTML = all.length
            ? all.map(x => `<div class="resumen-row"><span>${x.nombre}</span><span>S/ ${x.precio.toFixed(2)}</span></div>`).join('')
            : '<div class="resumen-row" style="color:#666;font-size:0.8rem;justify-content:center;">Sin ítems aún</div>';
        document.getElementById('totalResumen').textContent = `S/ ${total.toFixed(2)}`;
    }

    // ---- BUSCAR CLIENTE ----
    document.getElementById('btnBuscar').addEventListener('click', () => {
        const q = document.getElementById('searchCliente').value.trim();
        if (!q) return;
        document.getElementById('nombreCliente').value = q;
        document.getElementById('dniCliente').value = '12345678';
        document.getElementById('telefonoCliente').value = '987654321';
        document.getElementById('emailCliente').value = q.toLowerCase().replace(/\s/g,'') + '@email.com';
        document.getElementById('clienteSeleccionado').innerHTML = `
        <div style="display:flex;align-items:center;gap:8px;">
          <div style="width:28px;height:28px;border-radius:50%;background:#4a6fa5;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;color:#fff;">
            ${q.charAt(0).toUpperCase()}
          </div>
          <div>
            <div style="color:#ddd;font-weight:500;">${q}</div>
            <div style="color:#888;font-size:0.78rem;">DNI: 12345678</div>
          </div>
        </div>`;
    });

    // ---- GUARDAR ----
    function guardarCotizacion() {
        const nombre = document.getElementById('nombreCliente').value;
        const all = [...getServiciosPreset(), ...serviciosCustom, ...paquetes];
        if (!nombre) { alert('Ingresa los datos del cliente'); return; }
        if (!all.length) { alert('Agrega al menos un servicio o paquete'); return; }
        const total = all.reduce((s, x) => s + x.precio, 0);
        alert(`Cotización guardada para ${nombre}\nTotal: S/ ${total.toFixed(2)}`);
    }
</script>
