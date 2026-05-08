import { clienteApi }    from '../../api/cliente.api.js';
import { paqueteApi }    from '../../api/paquete.api.js';
import { cotizacionApi } from '../../api/cotizacion.api.js';
import { manager }       from './cotizacion.manager.js';
import { formatters }    from '../../utils/formatters.js';
import { alerts }        from '../../utils/alerts.js';

/* ── Estado en memoria ────────────────────────────────────────── */
const state = {
    cliente:             null,   // objeto cliente existente seleccionado
    esNuevoCliente:      false,  // true = no existe en BD, se creará al guardar
    items:               [],     // [{tipo, idRef, nombre, precio}]
    todosClientes:       [],
    todosPaquetes:       [],
    paqueteSeleccionado: null,
};

/* ═══════════════════════════════════════════════════════════════
   SECCIÓN CLIENTE
═══════════════════════════════════════════════════════════════ */

/** Aplica un cliente existente: llena campos y los bloquea */
function _setClienteExistente(c) {
    state.cliente        = c;
    state.esNuevoCliente = false;

    _llenarCamposCliente({
        nombres:   c.nombres           ?? '',
        apellidos: c.apellidos         ?? '',
        dni:       c.numero_documento  ?? '',
        telefono:  c.telefono          ?? '',
        correo:    c.correo            ?? '',
    });
    _setCamposClienteReadonly(true);

    document.getElementById('idCliente').value = c.id_cliente ?? '';

    _mostrarBadgeCliente('found', `${c.nombres} ${c.apellidos ?? ''}`.trim());
    _actualizarSidebarCliente(`<strong>${c.nombres} ${c.apellidos ?? ''}</strong><br>
        <small style="color:#888;">${c.numero_documento ?? ''} · ${c.telefono ?? ''}</small>`);
    _mostrarBtnCambiar(true);
    _saveDraft();
}

/** Activa modo "nuevo cliente": campos editables, badge naranja */
function _setModoNuevoCliente() {
    state.cliente        = null;
    state.esNuevoCliente = true;

    document.getElementById('idCliente').value = '';
    _setCamposClienteReadonly(false);

    _mostrarBadgeCliente('new', 'Cliente nuevo — los datos se registrarán al guardar.');
    _actualizarSidebarCliente(`<span style="color:#e65100;font-size:0.82rem;">⚠ Nuevo cliente</span>`);
    _mostrarBtnCambiar(true);
}

/** Limpia toda la sección de cliente (para empezar de nuevo) */
function _limpiarCliente() {
    state.cliente        = null;
    state.esNuevoCliente = false;

    _llenarCamposCliente({ nombres: '', apellidos: '', dni: '', telefono: '', correo: '' });
    _setCamposClienteReadonly(false);
    document.getElementById('idCliente').value = '';

    _mostrarBadgeCliente('', '');
    _actualizarSidebarCliente('Ningún cliente seleccionado');
    _mostrarBtnCambiar(false);

    const searchEl = document.getElementById('searchCliente');
    if (searchEl) searchEl.value = '';
    _ocultarDropdown();
    _saveDraft();
}

/** Busca en la lista local por número de documento (exacto, sin distinción de mayúsculas) */
function _buscarPorDni(dni) {
    if (!dni) return null;
    return state.todosClientes.find(c =>
        (c.numero_documento ?? '').toLowerCase() === dni.toLowerCase()
    ) ?? null;
}

/* ── Helpers de UI para el cliente ───────────────────────────── */
function _llenarCamposCliente({ nombres, apellidos, dni, telefono, correo }) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.value = v; };
    set('nombresCliente',   nombres);
    set('apellidosCliente', apellidos);
    set('dniCliente',       dni);
    set('telefonoCliente',  telefono);
    set('emailCliente',     correo);
}

const CAMPOS_CLIENTE_IDS = ['nombresCliente', 'apellidosCliente', 'dniCliente', 'telefonoCliente', 'emailCliente'];

function _setCamposClienteReadonly(readonly) {
    CAMPOS_CLIENTE_IDS.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.readOnly = readonly;
    });
}

/* ── Badge de estado del cliente ──────────────────────────────── */
let _badgeEl = null;

function _mostrarBadgeCliente(tipo, texto) {
    if (!_badgeEl) {
        _badgeEl = document.createElement('div');
        _badgeEl.style.cssText = 'font-size:0.81rem;margin-top:6px;padding:5px 10px;border-radius:5px;display:none;';
        // Insertarlo después del row de campos (dentro del fieldset)
        const fieldset = document.querySelector('fieldset.mb-4');
        fieldset?.appendChild(_badgeEl);
    }

    if (!texto) { _badgeEl.style.display = 'none'; return; }

    _badgeEl.style.display = '';
    if (tipo === 'found') {
        Object.assign(_badgeEl.style, { background: '#e8f5e9', color: '#2e7d32', border: '1px solid #a5d6a7' });
        _badgeEl.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>${texto}`;
    } else if (tipo === 'new') {
        Object.assign(_badgeEl.style, { background: '#fff3e0', color: '#e65100', border: '1px solid #ffcc80' });
        _badgeEl.innerHTML = `<i class="bi bi-person-plus-fill me-1"></i>${texto}`;
    } else if (tipo === 'searching') {
        Object.assign(_badgeEl.style, { background: '#f5f5f5', color: '#555', border: '1px solid #ddd' });
        _badgeEl.innerHTML = `<i class="bi bi-hourglass-split me-1"></i>${texto}`;
    }
}

/* ── Botón "Cambiar cliente" ──────────────────────────────────── */
let _btnCambiarEl = null;

function _mostrarBtnCambiar(visible) {
    if (!_btnCambiarEl) {
        _btnCambiarEl = document.createElement('button');
        _btnCambiarEl.type = 'button';
        _btnCambiarEl.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Cambiar cliente';
        _btnCambiarEl.style.cssText = 'font-size:0.78rem;border:none;background:none;color:#6c757d;cursor:pointer;text-decoration:underline;padding:2px 0;';
        _btnCambiarEl.addEventListener('click', _limpiarCliente);
        _badgeEl?.after(_btnCambiarEl);
    }
    _btnCambiarEl.style.display = visible ? '' : 'none';
}

function _actualizarSidebarCliente(html) {
    const el = document.getElementById('clienteSeleccionado');
    if (el) el.innerHTML = html;
}

/* ═══════════════════════════════════════════════════════════════
   BÚSQUEDA DROPDOWN (barra general)
═══════════════════════════════════════════════════════════════ */
let _dropdown = null;

function _mostrarDropdown(resultados) {
    if (!_dropdown) {
        _dropdown = document.createElement('div');
        _dropdown.style.cssText = `
            position:absolute;top:calc(100% + 2px);left:0;right:0;z-index:1055;
            background:#fff;border:1px solid #dee2e6;border-radius:6px;
            max-height:220px;overflow-y:auto;
            box-shadow:0 4px 12px rgba(0,0,0,.12);`;
        const wrap = document.querySelector('.search-wrap');
        if (wrap) { wrap.style.position = 'relative'; wrap.appendChild(_dropdown); }
    }

    if (!resultados.length) {
        _dropdown.innerHTML = `<div style="padding:8px 14px;font-size:0.82rem;color:#6c757d;">Sin resultados.</div>`;
    } else {
        _dropdown.innerHTML = resultados.map(c => `
            <div class="dd-item" data-id="${c.id_cliente}"
                 style="padding:8px 14px;cursor:pointer;font-size:0.83rem;border-bottom:1px solid #f5f5f5;">
                <strong>${c.nombres} ${c.apellidos ?? ''}</strong>
                <small class="d-block" style="color:#888;">${c.numero_documento ?? ''} · ${c.telefono ?? ''}</small>
            </div>`).join('');

        _dropdown.querySelectorAll('.dd-item').forEach(el => {
            el.addEventListener('mouseenter', () => { el.style.background = '#f8f9fa'; });
            el.addEventListener('mouseleave', () => { el.style.background = ''; });
            el.addEventListener('click', () => {
                const cliente = state.todosClientes.find(c => c.id_cliente === parseInt(el.dataset.id));
                if (cliente) _setClienteExistente(cliente);
                _ocultarDropdown();
                const inp = document.getElementById('searchCliente');
                if (inp) inp.value = '';
            });
        });
    }

    _dropdown.style.display = 'block';
}

function _ocultarDropdown() {
    if (_dropdown) _dropdown.style.display = 'none';
}

function _filtrarClientes(q) {
    q = (q || '').toLowerCase().trim();
    if (!q) { _ocultarDropdown(); return; }
    const res = state.todosClientes.filter(c => {
        const nombre = `${c.nombres ?? ''} ${c.apellidos ?? ''}`.toLowerCase();
        return nombre.includes(q)
            || (c.numero_documento ?? '').toLowerCase().includes(q)
            || (c.telefono ?? '').includes(q);
    }).slice(0, 8);
    _mostrarDropdown(res);
}

/* ═══════════════════════════════════════════════════════════════
   RESUMEN LATERAL
═══════════════════════════════════════════════════════════════ */
function _actualizarResumen() {
    const el      = document.getElementById('resumenItems');
    const totalEl = document.getElementById('totalResumen');
    if (!el) return;

    if (!state.items.length) {
        el.innerHTML = `<div class="resumen-row" style="color:#666;font-size:0.8rem;justify-content:center;">Sin ítems aún</div>`;
        if (totalEl) totalEl.textContent = 'S/ 0.00';
        return;
    }

    const total = state.items.reduce((s, i) => s + i.precio, 0);
    el.innerHTML = state.items.map((item, idx) => `
        <div class="resumen-row" style="align-items:center;gap:6px;">
            <span style="flex:1;font-size:0.78rem;">${item.nombre}</span>
            <span style="white-space:nowrap;font-size:0.78rem;">${formatters.moneda(item.precio)}</span>
            <button type="button" data-idx="${idx}"
                    style="background:none;border:none;padding:0 2px;color:#e57373;cursor:pointer;font-size:0.78rem;line-height:1;"
                    class="btn-res-del" title="Quitar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>`).join('');

    if (totalEl) totalEl.textContent = formatters.moneda(total);

    el.querySelectorAll('.btn-res-del').forEach(btn => {
        btn.addEventListener('click', () => {
            state.items.splice(parseInt(btn.dataset.idx), 1);
            _renderContainers();
            _actualizarResumen();
            _saveDraft();
        });
    });
}

/* ═══════════════════════════════════════════════════════════════
   CONTENEDORES DE ÍTEMS
═══════════════════════════════════════════════════════════════ */
function _renderContainers() {
    _renderContainer('paquetesContainer',  'paquete');
    _renderContainer('serviciosContainer', 'personalizado');
}

function _renderContainer(containerId, tipo) {
    const el = document.getElementById(containerId);
    if (!el) return;

    const filtered = state.items
        .map((item, idx) => ({ item, idx }))
        .filter(({ item }) => item.tipo === tipo);

    if (!filtered.length) { el.innerHTML = ''; return; }

    el.innerHTML = filtered.map(({ item, idx }) => `
        <div class="d-flex justify-content-between align-items-center p-2 mt-1"
             style="border:1px solid var(--border,#dee2e6);border-radius:6px;font-size:0.82rem;gap:8px;">
            <span style="flex:1;">${item.nombre}</span>
            <span style="font-weight:600;white-space:nowrap;">${formatters.moneda(item.precio)}</span>
            <button type="button" data-idx="${idx}"
                    style="background:none;border:none;padding:2px 4px;color:#e57373;cursor:pointer;font-size:0.85rem;"
                    class="btn-item-del" title="Quitar">
                <i class="bi bi-trash3"></i>
            </button>
        </div>`).join('');

    el.querySelectorAll('.btn-item-del').forEach(btn => {
        btn.addEventListener('click', () => {
            state.items.splice(parseInt(btn.dataset.idx), 1);
            _renderContainers();
            _actualizarResumen();
            _saveDraft();
        });
    });
}

/* ═══════════════════════════════════════════════════════════════
   MODAL PAQUETES
═══════════════════════════════════════════════════════════════ */
function _poblarQuinceaneros(paquetes) {
    const panel = document.getElementById('panel-quinceaneros');
    if (!panel) return;
    if (!paquetes.length) {
        panel.innerHTML = `<div style="padding:12px;font-size:0.82rem;color:#6c757d;text-align:center;">Sin paquetes disponibles.</div>`;
        return;
    }
    panel.innerHTML = paquetes.map(p => {
        const nombre = (p.nombre_paquete || '').replace(/'/g, "\\'");
        const desc   = (p.descripcion    || '').replace(/'/g, "\\'");
        return `
            <div class="paquete-option"
                 onclick="seleccionarOpcion(this,'${nombre}','${desc}',${p.precio ?? 0},${p.id_paquete})">
                <div class="po-left">
                    <div class="po-name">${p.nombre_paquete}</div>
                    <div class="po-desc">${p.descripcion || ''}</div>
                </div>
                <span class="po-price">${formatters.moneda(p.precio ?? 0)}</span>
                <i class="bi bi-check-circle-fill po-check"></i>
            </div>`;
    }).join('');
}

window.cambiarCategoria = function (cat, tabEl) {
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.cat-panel').forEach(p => p.classList.remove('active'));
    tabEl.classList.add('active');
    document.getElementById(`panel-${cat}`)?.classList.add('active');
    state.paqueteSeleccionado = null;
};

window.seleccionarOpcion = function (el, nombre, desc, precio, idRef) {
    el.closest('.cat-panel')?.querySelectorAll('.paquete-option')
        .forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    state.paqueteSeleccionado = {
        idRef:  idRef ? parseInt(idRef) : null,
        nombre,
        precio: parseFloat(precio) || 0,
    };
};

/* ═══════════════════════════════════════════════════════════════
   MODAL SERVICIOS
═══════════════════════════════════════════════════════════════ */
function _inicializarModalServicio() {
    const panel = document.getElementById('panel-servicios');
    if (!panel) return;
    panel.innerHTML = `
        <div class="mb-2">
            <label class="form-label" style="font-size:0.85rem;font-weight:500;">
                Nombre del servicio*
            </label>
            <input type="text" class="form-control" id="servicioModalNombre"
                   placeholder="Ej: Álbum digital, Sesión grupal, CD con fotos...">
        </div>`;
}

/* ═══════════════════════════════════════════════════════════════
   BORRADOR (localStorage)
═══════════════════════════════════════════════════════════════ */
function _saveDraft() {
    manager.guardar({
        cliente:        state.cliente,
        esNuevoCliente: state.esNuevoCliente,
        camposCliente: state.esNuevoCliente ? {
            nombres:   document.getElementById('nombresCliente')?.value   ?? '',
            apellidos: document.getElementById('apellidosCliente')?.value ?? '',
            dni:       document.getElementById('dniCliente')?.value       ?? '',
            telefono:  document.getElementById('telefonoCliente')?.value  ?? '',
            correo:    document.getElementById('emailCliente')?.value     ?? '',
        } : null,
        tipoInstitucion: document.querySelector('input[name="tipoInstitucion"]:checked')?.value ?? 'colegio',
        items: state.items,
        notas: document.getElementById('notas')?.value ?? '',
        colegio: {
            nombre:    document.getElementById('nombreColegio')?.value    ?? '',
            provincia: document.getElementById('provinciaColegio')?.value ?? '',
            distrito:  document.getElementById('distritoColegio')?.value  ?? '',
        },
    });
}

function _restoreDraft(borrador) {
    if (!borrador) return;

    if (borrador.tipoInstitucion) {
        const radio = document.getElementById(`tipo-${borrador.tipoInstitucion}`);
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        }
    }

    if (borrador.cliente) {
        _setClienteExistente(borrador.cliente);
    } else if (borrador.esNuevoCliente && borrador.camposCliente) {
        const c = borrador.camposCliente;
        _llenarCamposCliente({ nombres: c.nombres, apellidos: c.apellidos, dni: c.dni, telefono: c.telefono, correo: c.correo });
        _setModoNuevoCliente();
    }

    if (borrador.items?.length) {
        state.items = borrador.items;
        _renderContainers();
        _actualizarResumen();
    }

    const notasEl = document.getElementById('notas');
    if (notasEl && borrador.notas) notasEl.value = borrador.notas;

    if (borrador.colegio) {
        const nc = document.getElementById('nombreColegio');
        const pc = document.getElementById('provinciaColegio');
        if (nc && borrador.colegio.nombre) nc.value = borrador.colegio.nombre;
        if (pc && borrador.colegio.provincia) {
            pc.value = borrador.colegio.provincia;
            pc.dispatchEvent(new Event('change'));
            setTimeout(() => {
                const dc = document.getElementById('distritoColegio');
                if (dc && borrador.colegio.distrito) dc.value = borrador.colegio.distrito;
            }, 60);
        }
    }
}

/* ═══════════════════════════════════════════════════════════════
   VALIDACIÓN
═══════════════════════════════════════════════════════════════ */
function _validar() {
    if (!state.cliente && !state.esNuevoCliente) {
        return 'Ingresa el DNI del cliente y presiona Buscar.';
    }

    if (state.esNuevoCliente) {
        const nombres  = document.getElementById('nombresCliente')?.value?.trim();
        const dni      = document.getElementById('dniCliente')?.value?.trim();
        const telefono = document.getElementById('telefonoCliente')?.value?.trim();
        if (!nombres)  return 'El nombre del cliente es obligatorio.';
        if (!dni)      return 'El DNI/documento del cliente es obligatorio.';
        if (!telefono) return 'El teléfono del cliente es obligatorio.';
        if (!/^\d{9}$/.test(telefono)) return 'El teléfono debe tener exactamente 9 dígitos.';
    }

    if (!state.items.length)  return 'Agrega al menos un paquete o servicio a la cotización.';
    const wrapGrado = document.getElementById('wrap-grado');
    if (wrapGrado?.style.display !== 'none' && !document.getElementById('gradoProm')?.value) {
        return 'Selecciona el grado de la promoción.';
    }

    return null;
}

/* ═══════════════════════════════════════════════════════════════
   PAYLOAD
═══════════════════════════════════════════════════════════════ */
function _buildPayload(idCliente) {
    const total    = state.items.reduce((s, i) => s + i.precio, 0);
    const detalles = state.items.map(item => ({
        tipo_item:       item.tipo,
        id_referencia:   item.idRef ?? null,
        descripcion:     item.nombre,
        cantidad:        1,
        precio_unitario: item.precio,
        subtotal:        item.precio,
    }));

    const fechaDate = document.getElementById('fechaInicio-date')?.value ?? '';
    const fechaHora = document.getElementById('fechaInicio-time')?.value ?? '';
    const fecha     = fechaDate && fechaHora ? `${fechaDate} ${fechaHora}:00` : (fechaDate || null);

    return {
        id_cliente:     idCliente,
        id_usuario:     window.CURRENT_USER_ID ?? 1,
        observaciones:  document.getElementById('notas')?.value?.trim() ?? null,
        total_estimado: total,
        detalles,
        // TODO: backend pendiente para guardar colegio y sesión
        colegio: {
            nombre:    document.getElementById('nombreColegio')?.value?.trim()    ?? null,
            provincia: document.getElementById('provinciaColegio')?.value         ?? null,
            distrito:  document.getElementById('distritoColegio')?.value          ?? null,
        },
        sesion: {
            tipo_institucion: document.querySelector('input[name="tipoInstitucion"]:checked')?.value ?? 'colegio',
            nombre_promocion: document.getElementById('nombreProm')?.value?.trim()      ?? null,
            num_estudiantes:  parseInt(document.getElementById('numEstudiantes')?.value) || null,
            grado:            document.getElementById('gradoProm')?.value               ?? null,
            seccion:          document.getElementById('seccionProm')?.value?.trim()     ?? null,
            fecha,
        },
    };
}

/* ═══════════════════════════════════════════════════════════════
   MODALES BOOTSTRAP
═══════════════════════════════════════════════════════════════ */
let _modalPaquete  = null;
let _modalServicio = null;

/* ═══════════════════════════════════════════════════════════════
   INICIALIZACIÓN
═══════════════════════════════════════════════════════════════ */
async function init() {
    /* 1. Cargar clientes y paquetes en paralelo */
    try {
        const [resC, resP] = await Promise.all([
            clienteApi.listar(),
            paqueteApi.listar({ estado: 'ACTIVO' }),
        ]);
        state.todosClientes = resC.data ?? [];
        state.todosPaquetes = resP.data ?? [];
    } catch {
        alerts.error('Error al cargar datos iniciales. Recarga la página.');
    }

    /* 2. Poblar panel Quinceañeros */
    _poblarQuinceaneros(state.todosPaquetes);

    /* 3. Inicializar modal de servicios */
    _inicializarModalServicio();

    /* 4. Restaurar borrador */
    _restoreDraft(manager.cargar());

    /* 5. Bootstrap modals */
    const paqEl = document.getElementById('modalPaquete');
    const srvEl = document.getElementById('modalServicio');
    if (paqEl) _modalPaquete  = new bootstrap.Modal(paqEl);
    if (srvEl) _modalServicio = new bootstrap.Modal(srvEl);

    /* ── SECCIÓN CLIENTE ── */

    /* 6a. Campo DNI: buscar cuando tiene 6+ chars y se pierde el foco */
    const dniInput = document.getElementById('dniCliente');
    dniInput?.addEventListener('blur', () => {
        if (state.cliente || state.esNuevoCliente) return; // ya hay selección
        const dni = dniInput.value.trim();
        if (!dni) return;
        _mostrarBadgeCliente('searching', 'Buscando...');
        const encontrado = _buscarPorDni(dni);
        if (encontrado) {
            _setClienteExistente(encontrado);
        } else {
            _setModoNuevoCliente();
        }
    });

    /* 6b. Si el DNI ya seleccionado se edita, resetear la selección */
    dniInput?.addEventListener('input', () => {
        if (state.cliente || state.esNuevoCliente) {
            const valorActual = dniInput.value;
            _limpiarCliente();
            dniInput.value = valorActual;
        }
    });

    /* 7. Barra de búsqueda general */
    const searchInput = document.getElementById('searchCliente');
    const searchBtn   = document.getElementById('btnBuscar');

    const _doSearch = () => {
        const q = searchInput?.value?.trim();
        if (!q) { _ocultarDropdown(); return; }
        // Búsqueda exacta por DNI primero
        const exactoDni = _buscarPorDni(q);
        if (exactoDni) {
            _setClienteExistente(exactoDni);
            searchInput.value = '';
            _ocultarDropdown();
            return;
        }
        // Búsqueda general (nombre/teléfono)
        _filtrarClientes(q);
    };

    searchBtn?.addEventListener('click', _doSearch);
    searchInput?.addEventListener('input', () => _filtrarClientes(searchInput.value));
    searchInput?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); _doSearch(); }
    });
    document.addEventListener('click', e => {
        if (!e.target.closest('.search-wrap')) _ocultarDropdown();
    });

    /* 8. Abrir modal paquete */
    document.getElementById('btn-modal-paquete')?.addEventListener('click', () => {
        state.paqueteSeleccionado = null;
        document.querySelectorAll('.paquete-option').forEach(o => o.classList.remove('selected'));
        _modalPaquete?.show();
    });

    /* 9. Abrir modal servicio */
    document.getElementById('btn-modal-servicio')?.addEventListener('click', () => {
        const n = document.getElementById('servicioModalNombre');
        const p = document.getElementById('servicioModalPrecio');
        if (n) n.value = '';
        if (p) p.value = '';
        _modalServicio?.show();
    });

    /* 10. Confirmar paquete */
    document.getElementById('btn-confirmar-paquetes')?.addEventListener('click', () => {
        if (!state.paqueteSeleccionado) { alerts.warning('Selecciona un paquete de la lista.'); return; }
        const { idRef, nombre, precio } = state.paqueteSeleccionado;
        state.items.push({ tipo: idRef ? 'paquete' : 'personalizado', idRef: idRef ?? null, nombre, precio });
        _renderContainers();
        _actualizarResumen();
        _saveDraft();
        _modalPaquete?.hide();
    });

    /* 11. Confirmar servicio */
    document.getElementById('btn-confirmar-servicio')?.addEventListener('click', () => {
        const nombre = document.getElementById('servicioModalNombre')?.value?.trim();
        const precio = parseFloat(document.getElementById('servicioModalPrecio')?.value || 0);
        if (!nombre) { alerts.warning('Ingresa el nombre del servicio.'); return; }
        if (precio <= 0) { alerts.warning('El precio debe ser mayor a 0.'); return; }
        state.items.push({ tipo: 'personalizado', idRef: null, nombre, precio });
        _renderContainers();
        _actualizarResumen();
        _saveDraft();
        _modalServicio?.hide();
    });

    /* 12. Auto-guardar borrador en campos de sesión/colegio */
    ['notas', 'nombreColegio'].forEach(id =>
        document.getElementById(id)?.addEventListener('input', _saveDraft));
    document.getElementById('provinciaColegio')?.addEventListener('change', _saveDraft);
    document.getElementById('distritoColegio')?.addEventListener('change', _saveDraft);

    /* Auto-guardar cuando el usuario edita campos de nuevo cliente */
    CAMPOS_CLIENTE_IDS.forEach(id =>
        document.getElementById(id)?.addEventListener('input', () => {
            if (state.esNuevoCliente) _saveDraft();
        })
    );

    /* 13. Envío del formulario */
    document.getElementById('form-cotizacion')?.addEventListener('submit', async e => {
        e.preventDefault();

        const error = _validar();
        if (error) { alerts.error(error); return; }

        const btnGuardar = document.querySelector('.btn-guardar');
        if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Guardando...';
        }

        try {
            let idCliente;

            /* Si es cliente nuevo, primero lo creamos en la BD */
            if (state.esNuevoCliente) {
                _mostrarBadgeCliente('searching', 'Registrando cliente...');
                const resCliente = await clienteApi.crear({
                    nombres:              document.getElementById('nombresCliente')?.value?.trim(),
                    apellidos:            document.getElementById('apellidosCliente')?.value?.trim() || null,
                    numero_documento:     document.getElementById('dniCliente')?.value?.trim(),
                    tipo_documento:       'DNI',
                    telefono:             document.getElementById('telefonoCliente')?.value?.trim(),
                    correo:               document.getElementById('emailCliente')?.value?.trim() || null,
                    metodo_comunicacion:  'whatsapp',
                    acepta_promociones:   false,
                });
                idCliente = resCliente.id_cliente;
                _mostrarBadgeCliente('found', 'Cliente registrado correctamente.');
            } else {
                idCliente = state.cliente.id_cliente;
            }

            await cotizacionApi.crear(_buildPayload(idCliente));
            manager.limpiar();
            alerts.ok('Cotización creada correctamente.');
            setTimeout(() => {
                window.location.href = (window.BASE_URL || '/') + 'cotizaciones';
            }, 1100);

        } catch (err) {
            alerts.error(err.message || 'Error al crear la cotización.');
            if (btnGuardar) {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="bi bi-check-circle me-2"></i>Guardar cotización';
            }
        }
    });
}

init();
