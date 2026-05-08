import { clienteApi }    from '../../api/cliente.api.js';
import { paqueteApi }    from '../../api/paquete.api.js';
import { cotizacionApi } from '../../api/cotizacion.api.js';
import { manager }       from './cotizacion.manager.js';
import { formatters }    from '../../utils/formatters.js';
import { alerts }        from '../../utils/alerts.js';

/* ── Estado en memoria ────────────────────────────────────────── */
const state = {
    cliente:             null,  // objeto cliente seleccionado
    items:               [],    // [{tipo, idRef, nombre, precio}]
    todosClientes:       [],
    todosPaquetes:       [],
    paqueteSeleccionado: null,  // {idRef, nombre, precio} — sólo mientras el modal está abierto
};

/* ── Resumen lateral ──────────────────────────────────────────── */
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
        </div>
    `).join('');

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

/* ── Contenedores de paquetes y servicios ─────────────────────── */
function _renderContainers() {
    _renderContainer('paquetesContainer',  'PAQUETE');
    _renderContainer('serviciosContainer', 'PERSONALIZADO');
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
        </div>
    `).join('');

    el.querySelectorAll('.btn-item-del').forEach(btn => {
        btn.addEventListener('click', () => {
            state.items.splice(parseInt(btn.dataset.idx), 1);
            _renderContainers();
            _actualizarResumen();
            _saveDraft();
        });
    });
}

/* ── Cliente seleccionado ─────────────────────────────────────── */
function _mostrarCliente(c) {
    state.cliente = c;
    document.getElementById('idCliente').value        = c.id_cliente         ?? '';
    document.getElementById('nombresCliente').value   = c.nombres             ?? '';
    document.getElementById('apellidosCliente').value = c.apellidos           ?? '';
    document.getElementById('dniCliente').value       = c.numero_documento    ?? '';
    document.getElementById('telefonoCliente').value  = c.telefono            ?? '';
    document.getElementById('emailCliente').value     = c.correo              ?? '';

    const sidebar = document.getElementById('clienteSeleccionado');
    if (sidebar) {
        sidebar.innerHTML = `
            <strong>${c.nombres} ${c.apellidos || ''}</strong><br>
            <small style="color:#888;">${c.numero_documento || ''} · ${c.telefono || ''}</small>`;
    }
}

/* ── Búsqueda de clientes ─────────────────────────────────────── */
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
                <strong>${c.nombres} ${c.apellidos || ''}</strong>
                <small class="d-block" style="color:#888;">${c.numero_documento || ''} · ${c.telefono || ''}</small>
            </div>`).join('');

        _dropdown.querySelectorAll('.dd-item').forEach(el => {
            el.addEventListener('mouseenter', () => { el.style.background = '#f8f9fa'; });
            el.addEventListener('mouseleave', () => { el.style.background = ''; });
            el.addEventListener('click', () => {
                const cliente = state.todosClientes.find(c => c.id_cliente === parseInt(el.dataset.id));
                if (cliente) _mostrarCliente(cliente);
                _ocultarDropdown();
                const inp = document.getElementById('searchCliente');
                if (inp) inp.value = '';
                const fb = document.getElementById('searchFeedback');
                if (fb) fb.textContent = '';
            });
        });
    }

    _dropdown.style.display = 'block';
}

function _ocultarDropdown() {
    if (_dropdown) _dropdown.style.display = 'none';
}

function _buscar(q) {
    q = (q || '').toLowerCase().trim();
    if (!q) { _ocultarDropdown(); return; }

    const res = state.todosClientes.filter(c => {
        const nombre = `${c.nombres || ''} ${c.apellidos || ''}`.toLowerCase();
        return nombre.includes(q)
            || (c.numero_documento || '').includes(q)
            || (c.telefono        || '').includes(q);
    }).slice(0, 8);

    _mostrarDropdown(res);
}

/* ── Modal paquetes ───────────────────────────────────────────── */
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

/* ── Modal servicios ──────────────────────────────────────────── */
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

/* ── Borrador (localStorage) ──────────────────────────────────── */
function _saveDraft() {
    manager.guardar({
        cliente: state.cliente,
        items:   state.items,
        notas:   document.getElementById('notas')?.value ?? '',
        colegio: {
            nombre:    document.getElementById('nombreColegio')?.value    ?? '',
            provincia: document.getElementById('provinciaColegio')?.value ?? '',
            distrito:  document.getElementById('distritoColegio')?.value  ?? '',
        },
    });
}

function _restoreDraft(borrador) {
    if (!borrador) return;

    if (borrador.cliente) _mostrarCliente(borrador.cliente);

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
        if (nc && borrador.colegio.nombre)    nc.value = borrador.colegio.nombre;
        if (pc && borrador.colegio.provincia) {
            pc.value = borrador.colegio.provincia;
            pc.dispatchEvent(new Event('change')); // activa la cascada de distritos
            setTimeout(() => {
                const dc = document.getElementById('distritoColegio');
                if (dc && borrador.colegio.distrito) dc.value = borrador.colegio.distrito;
            }, 60);
        }
    }
}

/* ── Validación ───────────────────────────────────────────────── */
function _validar() {
    if (!state.cliente)        return 'Selecciona un cliente para continuar.';
    if (!state.items.length)   return 'Agrega al menos un paquete o servicio a la cotización.';
    if (!document.getElementById('gradoProm')?.value)
                               return 'Selecciona el grado de la promoción.';
    return null;
}

/* ── Payload para la API ──────────────────────────────────────── */
function _buildPayload() {
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
        id_cliente:     state.cliente.id_cliente,
        id_usuario:     window.CURRENT_USER_ID ?? 1,
        observaciones:  document.getElementById('notas')?.value?.trim() ?? null,
        total_estimado: total,
        detalles,
        // TODO: el backend aún no persiste colegio ni sesión
        colegio: {
            nombre:    document.getElementById('nombreColegio')?.value?.trim()    ?? null,
            provincia: document.getElementById('provinciaColegio')?.value         ?? null,
            distrito:  document.getElementById('distritoColegio')?.value          ?? null,
        },
        sesion: {
            nombre_promocion: document.getElementById('nombreProm')?.value?.trim()      ?? null,
            num_estudiantes:  parseInt(document.getElementById('numEstudiantes')?.value) || null,
            grado:            document.getElementById('gradoProm')?.value               ?? null,
            seccion:          document.getElementById('seccionProm')?.value?.trim()     ?? null,
            tipo_sesion:      document.getElementById('tipoSesion')?.value              ?? null,
            fecha,
        },
    };
}

/* ── Bootstrap modals ─────────────────────────────────────────── */
let _modalPaquete  = null;
let _modalServicio = null;

/* ── Inicialización ───────────────────────────────────────────── */
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

    /* 2. Poblar panel Quinceañeros con paquetes de la API */
    _poblarQuinceaneros(state.todosPaquetes);

    /* 3. Inicializar panel de servicios en el modal */
    _inicializarModalServicio();

    /* 4. Restaurar borrador guardado */
    _restoreDraft(manager.cargar());

    /* 5. Instanciar modales Bootstrap */
    const paqEl = document.getElementById('modalPaquete');
    const srvEl = document.getElementById('modalServicio');
    if (paqEl) _modalPaquete  = new bootstrap.Modal(paqEl);
    if (srvEl) _modalServicio = new bootstrap.Modal(srvEl);

    /* 6. Abrir modal de paquetes */
    document.getElementById('btn-modal-paquete')?.addEventListener('click', () => {
        state.paqueteSeleccionado = null;
        document.querySelectorAll('.paquete-option').forEach(o => o.classList.remove('selected'));
        _modalPaquete?.show();
    });

    /* 7. Abrir modal de servicios */
    document.getElementById('btn-modal-servicio')?.addEventListener('click', () => {
        const n = document.getElementById('servicioModalNombre');
        const p = document.getElementById('servicioModalPrecio');
        if (n) n.value = '';
        if (p) p.value = '';
        _modalServicio?.show();
    });

    /* 8. Confirmar paquete seleccionado */
    document.getElementById('btn-confirmar-paquetes')?.addEventListener('click', () => {
        if (!state.paqueteSeleccionado) {
            alerts.warning('Selecciona un paquete de la lista.');
            return;
        }
        const { idRef, nombre, precio } = state.paqueteSeleccionado;
        state.items.push({
            tipo:  idRef ? 'PAQUETE' : 'PERSONALIZADO',
            idRef: idRef ?? null,
            nombre,
            precio,
        });
        _renderContainers();
        _actualizarResumen();
        _saveDraft();
        _modalPaquete?.hide();
    });

    /* 9. Confirmar servicio personalizado */
    document.getElementById('btn-confirmar-servicio')?.addEventListener('click', () => {
        const nombre = document.getElementById('servicioModalNombre')?.value?.trim();
        const precio = parseFloat(document.getElementById('servicioModalPrecio')?.value || 0);
        if (!nombre) { alerts.warning('Ingresa el nombre del servicio.'); return; }
        if (precio <= 0) { alerts.warning('El precio debe ser mayor a 0.'); return; }
        state.items.push({ tipo: 'PERSONALIZADO', idRef: null, nombre, precio });
        _renderContainers();
        _actualizarResumen();
        _saveDraft();
        _modalServicio?.hide();
    });

    /* 10. Búsqueda de cliente */
    const searchInput = document.getElementById('searchCliente');
    const searchBtn   = document.getElementById('btnBuscar');

    searchBtn?.addEventListener('click', () => _buscar(searchInput?.value));
    searchInput?.addEventListener('input', () => _buscar(searchInput.value));
    searchInput?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); _buscar(searchInput.value); }
    });
    document.addEventListener('click', e => {
        if (!e.target.closest('.search-wrap')) _ocultarDropdown();
    });

    /* 11. Auto-guardar borrador al editar campos */
    ['notas', 'nombreColegio'].forEach(id =>
        document.getElementById(id)?.addEventListener('input', _saveDraft));
    document.getElementById('provinciaColegio')?.addEventListener('change', _saveDraft);
    document.getElementById('distritoColegio')?.addEventListener('change', _saveDraft);

    /* 12. Envío del formulario */
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
            await cotizacionApi.crear(_buildPayload());
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
