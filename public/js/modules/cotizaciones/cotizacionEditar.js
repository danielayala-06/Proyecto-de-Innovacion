/**
 * @file    cotizacionEditar.js
 * @module  modules/cotizaciones/cotizacionEditar
 *
 * Módulo autoejecutable para la vista de edición de cotizaciones (`/cotizaciones/editar/{id}`).
 * Carga la cotización existente desde la API, permite modificar los ítems y las
 * observaciones, y envía `PUT /api/cotizaciones/{id}` al guardar.
 *
 * Solo cotizaciones en estado PENDIENTE son editables; si el estado es otro,
 * se muestra un aviso y se redirige al listado.
 *
 * Variables globales requeridas (declaradas en la vista PHP):
 *  - `window.BASE_URL` : URL base de la aplicación.
 *  - `window.COT_ID`   : ID de la cotización a editar.
 */

import { paqueteApi }    from '../../api/paquete.api.js';
import { cotizacionApi } from '../../api/cotizacion.api.js';
import { formatters }    from '../../utils/formatters.js';
import { alerts }        from '../../utils/alerts.js';

// ─────────────────────────────────────────────────────────────────────────────
// ESTADO LOCAL
// ─────────────────────────────────────────────────────────────────────────────

/** @type {string} Nivel de filtro activo en el modal de paquetes. */
let _nivelFiltro = 'todos';

/**
 * @type {{ items: Array<{tipo:string,idRef:number|null,nombre:string,precio:number,cantidad:number}>,
 *          todosPaquetes: Array<Object>,
 *          paqueteSeleccionado: Object|null }}
 */
const state = {
    items:               [],
    todosPaquetes:       [],
    paqueteSeleccionado: null,
    descuentoMonto:      0,
};

// ─────────────────────────────────────────────────────────────────────────────
// MODAL DE PAQUETES
// ─────────────────────────────────────────────────────────────────────────────

const NIVEL_LABEL = {
    inicial:    'Inicial',
    primaria:   'Primaria',
    secundaria: 'Secundaria',
    postgrado:  'Postgrado',
    otro:       'Otro',
};

const NIVEL_ORDER = ['inicial', 'primaria', 'secundaria', 'postgrado', 'otro'];
const NIVEL_STYLE = {
    inicial:    'background:#fff3e0;color:#e65100',
    primaria:   'background:#e3f2fd;color:#1565c0',
    secundaria: 'background:#f3e5f5;color:#6a1b9a',
    postgrado:  'background:#fce4ec;color:#c62828',
    otro:       'background:#fafafa;color:#616161',
};

/**
 * Construye el contenido del modal de paquetes agrupando por categoría y nivel.
 *
 * @param {Array<Object>} paquetes - Lista de paquetes activos.
 * @returns {void}
 */
function _poblarModalPaquetes(paquetes) {
    const nivelEl  = document.getElementById('nivelFiltrosContainer');
    const tabsEl   = document.getElementById('catTabsContainer');
    const panelsEl = document.getElementById('catPanelsContainer');
    if (!tabsEl || !panelsEl) return;

    const nivelesPresentes = [...new Set(
        paquetes.map(p => p.nivel_disponible || 'otro')
    )];
    const nivelesOrdenados = [
        ...NIVEL_ORDER.filter(n => nivelesPresentes.includes(n)),
        ...nivelesPresentes.filter(n => !NIVEL_ORDER.includes(n)),
    ];

    if (nivelEl) {
        nivelEl.innerHTML = [
            `<button class="nivel-filtro-btn active" onclick="filtrarPorNivel('todos',this)">Todos</button>`,
            ...nivelesOrdenados.map(n =>
                `<button class="nivel-filtro-btn" onclick="filtrarPorNivel('${n}',this)">${NIVEL_LABEL[n] ?? n}</button>`
            ),
        ].join('');
    }

    const CAT_LABEL = { Paquetes: 'Paquetes', Cuadros: 'Cuadros', Anuarios: 'Anuarios', otros: 'Otros' };
    const CAT_ORDER = ['Paquetes', 'Cuadros', 'Anuarios', 'otros'];

    const grupos = {};
    paquetes.forEach(p => {
        const cat = p.categoria || 'otros';
        (grupos[cat] = grupos[cat] || []).push(p);
    });

    const keys = [
        ...CAT_ORDER.filter(k => grupos[k]),
        ...Object.keys(grupos).filter(k => !CAT_ORDER.includes(k)),
    ];

    if (!keys.length) {
        tabsEl.innerHTML   = '';
        panelsEl.innerHTML = `<div style="padding:12px;font-size:0.82rem;color:#6c757d;text-align:center;">Sin paquetes disponibles.</div>`;
        return;
    }

    tabsEl.innerHTML = keys.map((cat, i) =>
        `<button class="cat-tab${i === 0 ? ' active' : ''}" onclick="cambiarCategoria('${cat}',this)">${CAT_LABEL[cat] ?? cat}</button>`
    ).join('');

    panelsEl.innerHTML = keys.map((cat, i) => {
        const rows = grupos[cat].map(p => {
            const nombre     = (p.nombre_paquete || '').replace(/'/g, "\\'");
            const nivel      = p.nivel_disponible || 'otro';
            const badgeStyle = NIVEL_STYLE[nivel] ?? NIVEL_STYLE.otro;
            const descRaw  = p.descripcion || '';
            const descHtml = descRaw.replace(/\n/g, '<br>');
            const descAttr = descRaw.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            return `
                <div class="paquete-option" data-nivel="${nivel}"
                     onclick="seleccionarOpcion(this,'${nombre}',${p.precio ?? 0},${p.id_paquete})">
                    <div class="po-left">
                        <div class="po-name">${p.nombre_paquete}</div>
                        ${descHtml ? `<div class="po-desc" title="${descAttr}">${descHtml}</div>` : ''}
                        <span class="nivel-badge" style="${badgeStyle}">${NIVEL_LABEL[nivel] ?? nivel}</span>
                    </div>
                    <span class="po-price">${formatters.moneda(p.precio ?? 0)}</span>
                    <i class="bi bi-check-circle-fill po-check"></i>
                </div>`;
        }).join('');
        return `<div class="cat-panel overflow-auto${i === 0 ? ' active' : ''}" id="cat-panel-${cat}" style="max-height:20rem;">${rows}</div>`;
    }).join('');
}

/**
 * Cambia la tab de categoría activa en el modal.
 *
 * @param {string}      cat   - Nombre de la categoría.
 * @param {HTMLElement} tabEl - Botón de tab clickeado.
 */
window.cambiarCategoria = function (cat, tabEl) {
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.cat-panel').forEach(p => p.classList.remove('active'));
    tabEl.classList.add('active');
    document.getElementById(`cat-panel-${cat}`)?.classList.add('active');
    state.paqueteSeleccionado = null;
};

/**
 * Filtra las opciones del modal por nivel educativo.
 *
 * @param {string}      nivel - Nivel a mostrar (`'todos'` o nombre de nivel).
 * @param {HTMLElement} btn   - Botón de filtro clickeado.
 */
window.filtrarPorNivel = function (nivel, btn) {
    _nivelFiltro = nivel;
    document.querySelectorAll('.nivel-filtro-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.paquete-option').forEach(opt => {
        opt.style.display = (nivel === 'todos' || (opt.dataset.nivel || 'otro') === nivel) ? '' : 'none';
    });
};

/**
 * Marca un paquete como seleccionado en el modal.
 *
 * @param {HTMLElement} el     - Elemento `.paquete-option` clickeado.
 * @param {string}      nombre - Nombre del paquete.
 * @param {number}      precio - Precio del paquete.
 * @param {number}      idRef  - ID del paquete en BD.
 */
window.seleccionarOpcion = function (el, nombre, precio, idRef) {
    el.closest('.cat-panel')?.querySelectorAll('.paquete-option')
        .forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    state.paqueteSeleccionado = {
        idRef:  idRef ? parseInt(idRef) : null,
        nombre,
        precio: parseFloat(precio) || 0,
    };
};

// ─────────────────────────────────────────────────────────────────────────────
// RENDERIZADO DE ÍTEMS
// ─────────────────────────────────────────────────────────────────────────────

/** Renderiza ítems de un tipo en un contenedor dado (solo botón eliminar). */
function _renderItemsSimple(containerId, tipo) {
    const el = document.getElementById(containerId);
    if (!el) return;

    const filtered = state.items
        .map((item, idx) => ({ item, idx }))
        .filter(({ item }) => item.tipo === tipo);

    if (!filtered.length) { el.innerHTML = ''; return; }

    const esCortesia = tipo === 'cortesia';

    el.innerHTML = filtered.map(({ item, idx }) => {
        const cant     = item.cantidad ?? 1;
        const subtotal = item.precio * cant;
        const cantTag  = cant > 1
            ? `<span style="font-size:.72rem;color:var(--text-muted);white-space:nowrap;">×${cant}</span>`
            : '';
        const precioLabel = esCortesia
            ? `<span style="font-size:.75rem;font-weight:600;color:var(--green-text,#4caf50);">Gratis</span>`
            : `<span style="font-weight:600;white-space:nowrap;">${formatters.moneda(subtotal)}</span>`;
        const iconHtml = esCortesia
            ? `<i class="bi bi-gift" style="color:var(--green-text,#4caf50);flex-shrink:0;"></i>`
            : '';
        return `
        <div class="d-flex justify-content-between align-items-center p-2 mt-1"
             style="border:1px solid ${esCortesia ? 'var(--green-text,#4caf50)' : 'var(--border,#dee2e6)'};
                    border-radius:6px;font-size:.82rem;gap:8px;
                    ${esCortesia ? 'background:rgba(76,175,80,.05);' : ''}">
            ${iconHtml}
            <span style="flex:1;">${item.nombre}</span>
            ${cantTag}
            ${precioLabel}
            <button type="button" data-idx="${idx}"
                    style="background:none;border:none;padding:2px 4px;color:#e57373;cursor:pointer;font-size:.85rem;"
                    class="btn-item-del" title="Quitar">
                <i class="bi bi-trash3"></i>
            </button>
        </div>`;
    }).join('');

    el.querySelectorAll('.btn-item-del').forEach(btn => {
        btn.addEventListener('click', () => {
            state.items.splice(parseInt(btn.dataset.idx), 1);
            _renderContainers();
            _actualizarResumen();
        });
    });
}

/** Renderiza los servicios adicionales (tipo='servicio') con controles de cantidad. */
function _renderServiciosContainer() {
    const el = document.getElementById('serviciosContainer');
    if (!el) return;

    const servicios = state.items
        .map((item, idx) => ({ item, idx }))
        .filter(({ item }) => item.tipo === 'servicio');

    if (!servicios.length) { el.innerHTML = ''; return; }

    el.innerHTML = servicios.map(({ item, idx }) => {
        const cant     = item.cantidad ?? 1;
        const subtotal = item.precio * cant;
        return `
        <div class="d-flex justify-content-between align-items-center p-2 mt-1"
             style="border:1px solid var(--border,#dee2e6);border-radius:6px;font-size:.82rem;gap:8px;">
            <span style="flex:1;" title="${item.nombre}">${item.nombre}</span>
            <span style="font-size:.7rem;color:var(--text-muted);white-space:nowrap;">${formatters.moneda(item.precio)}/u</span>
            <div style="display:flex;align-items:center;gap:2px;">
                <button type="button" class="po-qty-btn svc-qty-dec" data-idx="${idx}">−</button>
                <input type="number" class="svc-qty-input" data-idx="${idx}"
                       value="${cant}" min="1" max="9999"
                       style="width:46px;height:24px;text-align:center;border:1px solid var(--border);
                              border-radius:4px;font-size:.78rem;background:var(--bg-input);
                              color:var(--text-primary);padding:0 2px;">
                <button type="button" class="po-qty-btn svc-qty-inc" data-idx="${idx}">+</button>
            </div>
            <span class="svc-subtotal" data-idx="${idx}"
                  style="font-weight:600;white-space:nowrap;min-width:62px;text-align:right;">
                ${formatters.moneda(subtotal)}
            </span>
            <button type="button" data-idx="${idx}"
                    style="background:none;border:none;padding:2px 4px;color:#e57373;cursor:pointer;font-size:.85rem;"
                    class="btn-svc-del" title="Quitar">
                <i class="bi bi-trash3"></i>
            </button>
        </div>`;
    }).join('');

    const _aplicarCantSvc = (idx, nuevaCant) => {
        nuevaCant = Math.min(9999, Math.max(1, nuevaCant));
        state.items[idx].cantidad = nuevaCant;
        const inputEl    = el.querySelector(`.svc-qty-input[data-idx="${idx}"]`);
        const subtotalEl = el.querySelector(`.svc-subtotal[data-idx="${idx}"]`);
        if (inputEl)    inputEl.value          = nuevaCant;
        if (subtotalEl) subtotalEl.textContent = formatters.moneda(state.items[idx].precio * nuevaCant);
        _actualizarResumen();
    };

    el.querySelectorAll('.svc-qty-dec').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx = parseInt(btn.dataset.idx);
            _aplicarCantSvc(idx, (state.items[idx].cantidad ?? 1) - 1);
        })
    );
    el.querySelectorAll('.svc-qty-inc').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx = parseInt(btn.dataset.idx);
            _aplicarCantSvc(idx, (state.items[idx].cantidad ?? 1) + 1);
        })
    );
    el.querySelectorAll('.svc-qty-input').forEach(input =>
        input.addEventListener('change', () => {
            const idx = parseInt(input.dataset.idx);
            _aplicarCantSvc(idx, parseInt(input.value) || 1);
        })
    );
    el.querySelectorAll('.btn-svc-del').forEach(btn =>
        btn.addEventListener('click', () => {
            state.items.splice(parseInt(btn.dataset.idx), 1);
            _renderContainers();
            _actualizarResumen();
        })
    );
}

/**
 * Re-renderiza todos los contenedores de ítems.
 * @returns {void}
 */
function _renderContainers() {
    _renderItemsSimple('paquetesContainer', 'paquete');
    _renderServiciosContainer();
    _renderItemsSimple('cortesiasContainer', 'cortesia');
}

/**
 * Re-renderiza el resumen lateral con los ítems y el total actual.
 * @returns {void}
 */
function _actualizarResumen() {
    const el      = document.getElementById('resumenItems');
    const totalEl = document.getElementById('totalResumen');
    if (!el) return;

    if (!state.items.length) {
        el.innerHTML = `<div class="resumen-row" style="color:#666;font-size:0.8rem;justify-content:center;">Sin ítems aún</div>`;
        if (totalEl) totalEl.textContent = 'S/ 0.00';
        return;
    }

    const total = state.items.reduce((s, i) => i.tipo === 'cortesia' ? s : s + i.precio * (i.cantidad ?? 1), 0);
    el.innerHTML = state.items.map((item, idx) => {
        const cant        = item.cantidad ?? 1;
        const esCortesia  = item.tipo === 'cortesia';
        const subtotal    = esCortesia ? 0 : item.precio * cant;
        const cantLabel   = cant > 1 ? `<span style="color:#888;font-size:0.72rem;">×${cant} </span>` : '';
        const precioLabel = esCortesia
            ? `<span style="font-size:.75rem;color:var(--green-text,#4caf50);font-weight:600;">Gratis</span>`
            : `<span style="white-space:nowrap;font-size:0.78rem;">${cantLabel}${formatters.moneda(subtotal)}</span>`;
        return `
        <div class="resumen-row" style="align-items:center;gap:6px;">
            <span style="flex:1;font-size:0.78rem;">${esCortesia ? '<i class="bi bi-gift" style="color:var(--green-text,#4caf50);margin-right:3px;"></i>' : ''}${item.nombre}</span>
            ${precioLabel}
            <button type="button" data-idx="${idx}"
                    style="background:none;border:none;padding:0 2px;color:#e57373;cursor:pointer;font-size:0.78rem;line-height:1;"
                    class="btn-res-del" title="Quitar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>`;
    }).join('');

    if (totalEl) totalEl.textContent = formatters.moneda(total);

    el.querySelectorAll('.btn-res-del').forEach(btn => {
        btn.addEventListener('click', () => {
            state.items.splice(parseInt(btn.dataset.idx), 1);
            _renderContainers();
            _actualizarResumen();
        });
    });
}

// ─────────────────────────────────────────────────────────────────────────────
// DATOS DEL CLIENTE
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Renderiza la información del cliente en modo de solo lectura.
 *
 * @param {Object} cliente - Objeto cliente con nombre_completo, tipo_documento, etc.
 * @returns {void}
 */
function _renderClienteInfo(cliente) {
    const el = document.getElementById('clienteInfo');
    if (!el) return;
    el.innerHTML = `
        <div class="row g-2" style="font-size:.87rem;">
            <div class="col-12 col-md-6">
                <span style="color:var(--text-muted);font-size:.78rem;">Nombre</span><br>
                <strong>${cliente.nombre_completo ?? '—'}</strong>
            </div>
            <div class="col-12 col-md-6">
                <span style="color:var(--text-muted);font-size:.78rem;">Documento</span><br>
                <span>${cliente.tipo_documento ?? ''} ${cliente.numero_documento ?? '—'}</span>
            </div>
            <div class="col-12 col-md-6">
                <span style="color:var(--text-muted);font-size:.78rem;">Teléfono</span><br>
                <span>${cliente.telefono ?? '—'}</span>
            </div>
            ${cliente.correo ? `
            <div class="col-12 col-md-6">
                <span style="color:var(--text-muted);font-size:.78rem;">Correo</span><br>
                <span>${cliente.correo}</span>
            </div>` : ''}
        </div>`;
}

// ─────────────────────────────────────────────────────────────────────────────
// PAYLOAD PARA LA API
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Pre-popula los campos de institución y promoción con los datos cargados de la API.
 *
 * @param {{ nombre?: string, provincia?: string, distrito?: string }|null} colegio
 * @param {{ nombre?: string, num_estudiantes?: number }|null}              promocion
 * @returns {void}
 */
function _poblarInstitucionPromocion(colegio, promocion) {
    if (colegio) {
        const nc = document.getElementById('nombreColegio');
        const pc = document.getElementById('provinciaColegio');
        if (nc && colegio.nombre)   nc.value = colegio.nombre;
        if (pc && colegio.provincia) {
            pc.value = colegio.provincia;
            pc.dispatchEvent(new Event('change'));
            setTimeout(() => {
                const dc = document.getElementById('distritoColegio');
                if (dc && colegio.distrito) dc.value = colegio.distrito;
            }, 60);
        }
    }
    if (promocion) {
        const np = document.getElementById('nombreProm');
        const ne = document.getElementById('numEstudiantes');
        if (np && promocion.nombre)          np.value = promocion.nombre;
        if (ne && promocion.num_estudiantes) ne.value = promocion.num_estudiantes;
    }
}

/**
 * Construye el payload para `PUT /api/cotizaciones/{id}`.
 *
 * @returns {{ detalles: Array<Object>, observaciones: string|null, promocion: Object|null, colegio: Object|null }}
 */
function _buildPayload() {
    const nombreColegio = document.getElementById('nombreColegio')?.value?.trim() || null;
    const provincia     = document.getElementById('provinciaColegio')?.value || null;
    const distrito      = document.getElementById('distritoColegio')?.value || null;
    const nombreProm    = document.getElementById('nombreProm')?.value?.trim() || null;
    const numEst        = parseInt(document.getElementById('numEstudiantes')?.value) || null;

    return {
        detalles: state.items.map(item => ({
            tipo_item:       item.tipo === 'paquete' ? 'paquete' : 'personalizado',
            id_referencia:   item.idRef ?? null,
            descripcion:     item.tipo === 'cortesia' ? `[Cortesía] ${item.nombre}` : item.nombre,
            cantidad:        item.cantidad ?? 1,
            precio_unitario: item.tipo === 'cortesia' ? 0 : item.precio,
            subtotal:        item.tipo === 'cortesia' ? 0 : item.precio * (item.cantidad ?? 1),
        })),
        descuento_monto: state.descuentoMonto > 0 ? state.descuentoMonto : null,
        observaciones:   document.getElementById('notas')?.value?.trim() || null,
        promocion: (nombreProm || numEst) ? { nombre: nombreProm, num_estudiantes: numEst } : null,
        colegio:   nombreColegio          ? { nombre: nombreColegio, provincia, distrito }  : null,
    };
}

// ─────────────────────────────────────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────────────────────────────────────

/** @type {bootstrap.Modal|null} Modal de selección de paquetes. */
let _modalPaquete = null;

/**
 * Función principal de inicialización del módulo.
 *
 * @returns {Promise<void>}
 */
async function init() {
    const cotId = window.COT_ID;

    /* 1. Cargar cotización y paquetes en paralelo */
    let cotizacion;
    try {
        const [resCot, resP] = await Promise.all([
            cotizacionApi.obtener(cotId),
            paqueteApi.listar({ estado: 'ACTIVO' }),
        ]);
        cotizacion            = resCot.data ?? resCot;
        state.todosPaquetes   = resP.data ?? [];
    } catch {
        alerts.error('Error al cargar la cotización. Recarga la página.');
        return;
    }

    /* 2. Verificar que la cotización sea editable */
    if ((cotizacion.estado ?? '').toUpperCase() !== 'PENDIENTE') {
        alerts.warning('Solo se pueden editar cotizaciones en estado Pendiente.');
        setTimeout(() => { window.location.href = (window.BASE_URL || '/') + 'cotizaciones'; }, 1500);
        return;
    }

    /* 3. Mostrar código de la cotización en el título */
    const codigoEl = document.getElementById('codigoCotizacion');
    if (codigoEl && cotizacion.id) {
        const pad = String(cotizacion.id).padStart(4, '0');
        codigoEl.textContent = `#${pad}`;
    }

    /* 4. Renderizar info del cliente (solo lectura) */
    _renderClienteInfo(cotizacion.cliente ?? {});

    /* 5. Cargar ítems y descuento existentes en el estado interno */
    state.descuentoMonto = parseFloat(cotizacion.descuento_monto) || 0;
    const detalles = cotizacion.detalles ?? cotizacion.items ?? [];
    state.items = detalles.map(d => {
        const tipoItem = d.tipo_item ?? 'paquete';
        const precio   = parseFloat(d.precio_unitario) || 0;
        const desc     = d.descripcion ?? d.referencia_nombre ?? '—';
        let tipo;
        if (tipoItem === 'paquete') {
            tipo = 'paquete';
        } else if (precio === 0 || desc.startsWith('[Cortesía]')) {
            tipo = 'cortesia';
        } else {
            tipo = 'servicio';
        }
        return {
            tipo,
            idRef:    d.id_referencia ?? null,
            nombre:   desc.replace(/^\[Cortesía\]\s*/, ''),
            precio,
            cantidad: d.cantidad ?? 1,
        };
    });

    /* 6. Poblar modal de paquetes */
    _poblarModalPaquetes(state.todosPaquetes);

    /* 7. Renderizar contenedores + resumen */
    _renderContainers();
    _actualizarResumen();

    /* 8. Poblar observaciones, institución y promoción */
    const notasEl = document.getElementById('notas');
    if (notasEl && cotizacion.observaciones) notasEl.value = cotizacion.observaciones;

    _poblarInstitucionPromocion(cotizacion.colegio ?? null, cotizacion.promocion ?? null);

    /* 9. Instanciar modal Bootstrap */
    const paqEl = document.getElementById('modalPaquete');
    if (paqEl) _modalPaquete = new bootstrap.Modal(paqEl);

    /* 10. Abrir modal de paquete */
    document.getElementById('btn-modal-paquete')?.addEventListener('click', () => {
        state.paqueteSeleccionado = null;
        _nivelFiltro = 'todos';
        document.querySelectorAll('.nivel-filtro-btn').forEach((b, i) => b.classList.toggle('active', i === 0));
        document.querySelectorAll('.paquete-option').forEach(o => {
            o.classList.remove('selected');
            o.style.display = '';
        });
        const cantEl = document.getElementById('paqueteCantidad');
        if (cantEl) cantEl.value = '1';
        _modalPaquete?.show();
    });

    /* 11. Confirmar selección de paquete */
    document.getElementById('btn-confirmar-paquetes')?.addEventListener('click', () => {
        if (!state.paqueteSeleccionado) { alerts.warning('Selecciona un paquete de la lista.'); return; }
        const { idRef, nombre, precio } = state.paqueteSeleccionado;
        const cantidad = Math.max(1, parseInt(document.getElementById('paqueteCantidad')?.value) || 1);
        state.items.push({ tipo: idRef ? 'paquete' : 'personalizado', idRef: idRef ?? null, nombre, precio, cantidad });
        _renderContainers();
        _actualizarResumen();
        _modalPaquete?.hide();
    });

    /* 11b. Servicios adicionales */
    const servicioWrap    = document.getElementById('servicio-add-wrap');
    const servicioNombre  = document.getElementById('servicioNombre');
    const servicioPrecio  = document.getElementById('servicioPrecio');
    const servicioCant    = document.getElementById('servicioCant');
    const servicioPreview = document.getElementById('servicioSubtotalPreview');

    const _actualizarSubtotalPreview = () => {
        const precio = parseFloat(servicioPrecio?.value) || 0;
        const cant   = parseInt(servicioCant?.value) || 1;
        if (servicioPreview) servicioPreview.textContent = `= ${formatters.moneda(precio * cant)}`;
    };

    const _limpiarServicioForm = () => {
        if (servicioNombre)  servicioNombre.value  = '';
        if (servicioPrecio)  servicioPrecio.value  = '';
        if (servicioCant)    servicioCant.value    = '1';
        if (servicioPreview) servicioPreview.textContent = '= S/ 0.00';
        if (servicioWrap)    servicioWrap.style.display = 'none';
    };

    const _confirmarServicio = () => {
        const nombre = servicioNombre?.value?.trim();
        const precio = parseFloat(servicioPrecio?.value);
        const cant   = Math.max(1, parseInt(servicioCant?.value) || 1);
        if (!nombre) { alerts.warning('Escribe la descripción del servicio.'); servicioNombre?.focus(); return; }
        if (!precio || precio <= 0) { alerts.warning('El precio unitario debe ser mayor a 0.'); servicioPrecio?.focus(); return; }
        state.items.push({ tipo: 'servicio', idRef: null, nombre, precio: Math.round(precio * 100) / 100, cantidad: cant });
        _limpiarServicioForm();
        _renderContainers();
        _actualizarResumen();
    };

    document.getElementById('btn-agregar-servicio')?.addEventListener('click', () => {
        if (servicioWrap) servicioWrap.style.display = '';
        servicioNombre?.focus();
    });
    document.getElementById('btn-cancelar-servicio')?.addEventListener('click', _limpiarServicioForm);
    document.getElementById('btn-confirmar-servicio')?.addEventListener('click', _confirmarServicio);
    servicioPrecio?.addEventListener('input', _actualizarSubtotalPreview);
    servicioCant?.addEventListener('input',   _actualizarSubtotalPreview);
    servicioNombre?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); servicioPrecio?.focus(); } });
    servicioPrecio?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); servicioCant?.focus(); } });
    servicioCant?.addEventListener('keydown',   e => { if (e.key === 'Enter') { e.preventDefault(); _confirmarServicio(); } });

    /* 11c. Cortesías */
    const cortesiaWrap  = document.getElementById('cortesia-add-wrap');
    const cortesiaInput = document.getElementById('cortesiaProducto');
    const cortesiaCant  = document.getElementById('cortesiaCant');

    const _limpiarCortesiaForm = () => {
        if (cortesiaInput) cortesiaInput.value = '';
        if (cortesiaCant)  cortesiaCant.value  = '1';
        if (cortesiaWrap)  cortesiaWrap.style.display = 'none';
    };

    const _confirmarCortesia = () => {
        const nombre = cortesiaInput?.value?.trim();
        const cant   = Math.max(1, parseInt(cortesiaCant?.value) || 1);
        if (!nombre) { alerts.warning('Escribe el nombre del producto de cortesía.'); cortesiaInput?.focus(); return; }
        state.items.push({ tipo: 'cortesia', idRef: null, nombre, precio: 0, cantidad: cant });
        _limpiarCortesiaForm();
        _renderContainers();
        _actualizarResumen();
    };

    document.getElementById('btn-agregar-cortesia')?.addEventListener('click', () => {
        if (cortesiaWrap) cortesiaWrap.style.display = '';
        cortesiaInput?.focus();
    });
    document.getElementById('btn-cancelar-cortesia')?.addEventListener('click', _limpiarCortesiaForm);
    document.getElementById('btn-confirmar-cortesia')?.addEventListener('click', _confirmarCortesia);
    cortesiaInput?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); _confirmarCortesia(); } });

    /* 12. Envío del formulario */
    document.getElementById('form-cotizacion')?.addEventListener('submit', async e => {
        e.preventDefault();

        if (!state.items.length) {
            alerts.error('La cotización debe tener al menos un ítem.');
            return;
        }

        const btnGuardar = document.querySelector('.btn-guardar');
        if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Guardando...';
        }

        try {
            await cotizacionApi.actualizar(cotId, _buildPayload());
            alerts.ok('Cotización actualizada correctamente.');
            setTimeout(() => {
                window.location.href = (window.BASE_URL || '/') + 'cotizaciones';
            }, 1100);
        } catch (err) {
            alerts.error(err.message || 'Error al actualizar la cotización.');
            if (btnGuardar) {
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = '<i class="bi bi-check-circle me-2"></i>Guardar cambios';
            }
        }
    });
}

init();
