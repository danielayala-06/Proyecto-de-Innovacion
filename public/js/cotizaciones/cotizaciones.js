document.addEventListener("DOMContentLoaded", () => {
    // referencia al formulario de registro de cotizaciones
    const form = document.getElementById("form-cotizacion");

    // Capturamos los datos del formulario:
    form.addEventListener("submit", (event) => {
        // Capturamos el envio del formulario
        event.preventDefault();

        // Creamos un objeto FormData para acceder a los datos del form
        const formData = new FormData(form);

        const nombreEvento = formData.get("nombre");
        const fechaInicio = formData.get("fechaInicio");
        const fechaFin = formData.get("fechaFin");
        const direccionEvento = formData.get("direccion");
        const referenciaEvento = formData.get("referencia");
        const observaciones = formData.get("observaciones");

        // Validaciones

        // Probamos que los datos esten llegando correctamente
         console.log("DATOS RECIVIDOS:")
         console.log(fechaInicio)
         console.log(fechaFin)
         console.log(direccionEvento)
         console.log(nombreEvento)
         console.log(referenciaEvento)
         console.log(observaciones)
         console.log("=======================")

        // CREAMOS EL OBJETO PARA ENVIAR LOS DATOS
        const cotizacion_json = {
            "id_cliente": 4,
            "id_usuario": 1,
            "nombre_cotizacion": nombreEvento,
            "fecha_hora_inicio": fechaInicio,
            "fecha_hora_fin": fechaFin,
            "direccion": direccionEvento,
            "referencia": referenciaEvento,
            "observaciones": observaciones,
            "total_estimado": 2500.20,
        }

        createCotizacion(cotizacion_json);
    })


    async function createCotizacion(data_object) {

        if (!data_object) {
            console.error("No hay datos para enviar");
            return;
        }

        try {
            const res = await fetch(`${BASE_URL}/cotizaciones/insertar`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
                body: JSON.stringify(data_object),
            });

        const result = await res.json();

        if (res.status !== 201) {
            alert('No se logró insertar');
            console.log(data_object);
            console.log(res.status);
            console.log(result);
            return;
        }

        alert('Se insertó la cotización en la BD');
        return res.status;

        } catch (e) {
            console.error("Error en la petición:", e);
            return null;
        }
    }

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
