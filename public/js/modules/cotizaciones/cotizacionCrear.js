/**
 * @file    cotizacionCrear.js
 * @module  modules/cotizaciones/cotizacionCrear
 *
 * Módulo autoejecutable para la vista de creación de cotizaciones (`/cotizaciones/crear`).
 * Orquesta todos los flujos de la pantalla: selección de cliente (existente o nuevo),
 * búsqueda RENIEC, selección de paquetes/servicios, resumen lateral, borrador en
 * localStorage y envío del formulario a la API.
 *
 * Variables globales requeridas (declaradas en la vista PHP):
 *  - `window.BASE_URL` : URL base de la aplicación.
 *
 * Flujo principal (función `init`):
 *  1. Carga en paralelo clientes y paquetes activos desde la API.
 *  2. Puebla el modal de paquetes agrupado por categoría y nivel.
 *  3. Restaura el borrador guardado en `localStorage` si existe.
 *  4. Registra todos los listeners de eventos (cliente, paquetes, servicios, submit).
 */

import { clienteApi }    from '../../api/cliente.api.js';
import { paqueteApi }    from '../../api/paquete.api.js';
import { cotizacionApi } from '../../api/cotizacion.api.js';
import { manager }       from './cotizacion.manager.js';
import { formatters }    from '../../utils/formatters.js';
import { alerts }        from '../../utils/alerts.js';

// ─────────────────────────────────────────────────────────────────────────────
// ESTADO LOCAL
// ─────────────────────────────────────────────────────────────────────────────

/** @type {string} Nivel de filtro activo en el modal de paquetes ('todos' o nombre de nivel). */
let _nivelFiltro = 'todos';

/**
 * Estado interno del formulario de creación.
 *
 * @type {Object}
 * @property {Object|null}   cliente             - Cliente existente seleccionado.
 * @property {boolean}       esNuevoCliente      - `true` si el cliente no existe en BD.
 * @property {Array<Object>} items               - Ítems añadidos (paquetes y servicios).
 * @property {Array<Object>} todosClientes       - Caché de todos los clientes activos.
 * @property {Array<Object>} todosPaquetes       - Caché de todos los paquetes activos.
 * @property {Object|null}   paqueteSeleccionado - Paquete actualmente marcado en el modal.
 */
const state = {
    cliente:               null,
    esNuevoCliente:        false,
    cliente2:              null,
    esNuevoCliente2:       false,
    items:                 [],
    todosClientes:         [],
    todosPaquetes:         [],
    paquetesSeleccionados: new Map(), // idRef (o nombre) → { idRef, nombre, precio, el }
    descuentoMonto:        0,         // Descuento en soles aplicado al total final (entero)
};

// ─────────────────────────────────────────────────────────────────────────────
// VALIDACIÓN DE DOCUMENTO Y TELÉFONO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Reglas de validación por tipo de documento.
 *
 * @type {Object<string, { regex: RegExp, hint: string, maxlen: number }>}
 */
const DOC_RULES = {
    DNI:       { regex: /^\d{8}$/,              hint: '8 dígitos numéricos',              maxlen: 8  },
    CE:        { regex: /^[A-Za-z0-9]{9}$/,     hint: '9 caracteres alfanuméricos',       maxlen: 9  },
    PASAPORTE: { regex: /^[A-Za-z0-9]{6,12}$/,  hint: '6 a 12 caracteres alfanuméricos', maxlen: 12 },
};

/**
 * Actualiza el placeholder e `maxLength` del campo de documento según el tipo seleccionado.
 * @returns {void}
 */
function _actualizarPlaceholderDoc() {
    const tipo = document.getElementById('tipoDocumento')?.value ?? 'DNI';
    const inp  = document.getElementById('dniCliente');
    if (!inp) return;
    inp.placeholder = DOC_RULES[tipo]?.hint ?? 'Número de documento';
    inp.maxLength   = DOC_RULES[tipo]?.maxlen ?? 12;
}

/** @type {RegExp} Regex para validar teléfonos peruanos (9 + 8 dígitos). */
const TEL_REGEX = /^9\d{8}$/;

/**
 * Muestra feedback visual en tiempo real sobre la validez del teléfono.
 * @returns {void}
 */
function _validarTel() {
    const val        = document.getElementById('telefonoCliente')?.value?.trim() ?? '';
    const feedbackEl = document.getElementById('telFeedback');
    if (!feedbackEl) return;
    if (!val) { feedbackEl.textContent = ''; return; }
    if (TEL_REGEX.test(val)) {
        feedbackEl.style.color = 'var(--green-text)';
        feedbackEl.textContent = '✓ Número válido';
    } else {
        feedbackEl.style.color = 'var(--red-text)';
        feedbackEl.textContent = val[0] !== '9'
            ? 'Debe empezar por 9'
            : 'Debe tener exactamente 9 dígitos';
    }
}

/**
 * Muestra feedback visual en tiempo real sobre la validez del documento.
 * @returns {void}
 */
function _validarDoc() {
    const tipo       = document.getElementById('tipoDocumento')?.value ?? 'DNI';
    const val        = document.getElementById('dniCliente')?.value?.trim() ?? '';
    const feedbackEl = document.getElementById('docFeedback');
    if (!feedbackEl) return;
    if (!val) { feedbackEl.textContent = ''; return; }
    const rule = DOC_RULES[tipo];
    if (rule?.regex.test(val)) {
        feedbackEl.style.color = 'var(--green-text)';
        feedbackEl.textContent = '✓ Formato válido';
    } else {
        feedbackEl.style.color = 'var(--red-text)';
        feedbackEl.textContent = `Formato esperado: ${rule?.hint ?? ''}`;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// SECCIÓN CLIENTE
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Aplica un cliente existente al formulario: llena los campos y los bloquea.
 *
 * @param {Object} c - Cliente obtenido de la API o del caché local.
 * @returns {void}
 */
function _setClienteExistente(c) {
    state.cliente        = c;
    state.esNuevoCliente = false;

    _llenarCamposCliente({
        contacto: `${c.nombres ?? ''} ${c.apellidos ?? ''}`.trim(),
        tipoDoc:  c.tipo_documento   ?? 'DNI',
        dni:      c.numero_documento ?? '',
        telefono: c.telefono         ?? '',
        correo:   c.correo           ?? '',
    });
    _setCamposClienteReadonly(true);

    document.getElementById('idCliente').value = c.id_cliente ?? '';

    const _nombreCompleto = `${c.nombres ?? ''} ${c.apellidos ?? ''}`.trim();
    _mostrarBadgeCliente('found', _nombreCompleto);
    _actualizarSidebarCliente(`<strong>${_nombreCompleto}</strong><br>
        <small style="color:#888;">${c.numero_documento ? c.numero_documento + ' · ' : ''}${c.telefono ?? ''}</small>`);
    _mostrarBtnCambiar(true);
    _saveDraft();
}

/**
 * Activa el modo "nuevo cliente": campos editables, badge naranja.
 * @returns {void}
 */
function _setModoNuevoCliente() {
    state.cliente        = null;
    state.esNuevoCliente = true;

    document.getElementById('idCliente').value = '';
    _setCamposClienteReadonly(false);

    _mostrarBadgeCliente('new', 'Cliente nuevo — los datos se registrarán al guardar.');
    _actualizarSidebarCliente(`<span style="color:#e65100;font-size:0.82rem;">⚠ Nuevo cliente</span>`);
    _mostrarBtnCambiar(true);
}

/**
 * Limpia toda la sección de cliente para comenzar de nuevo.
 * @returns {void}
 */
function _limpiarCliente() {
    state.cliente        = null;
    state.esNuevoCliente = false;

    _llenarCamposCliente({ contacto: '', tipoDoc: 'DNI', dni: '', telefono: '', correo: '' });
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

/**
 * Busca en el caché local por número de documento (coincidencia exacta).
 *
 * @param {string} dni - Número de documento a buscar.
 * @returns {Object|null} Cliente encontrado o `null`.
 */
function _buscarPorDni(dni) {
    if (!dni) return null;
    return state.todosClientes.find(c =>
        (c.numero_documento ?? '').toLowerCase() === dni.toLowerCase()
    ) ?? null;
}

// ─── Helpers de UI del cliente ────────────────────────────────────────────────

/**
 * Rellena los campos del formulario de cliente.
 *
 * @param {{ nombres: string, apellidos: string, tipoDoc: string, dni: string, telefono: string, correo: string }} campos
 * @returns {void}
 */
function _llenarCamposCliente({ contacto, tipoDoc = 'DNI', dni, telefono, correo }) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.value = v ?? ''; };
    set('contactoCliente', contacto);
    set('tipoDocumento',   tipoDoc);
    set('dniCliente',      dni);
    set('telefonoCliente', telefono);
    set('emailCliente',    correo);
    _actualizarPlaceholderDoc();
    _validarDoc();
}

/** @type {string[]} IDs de los campos del formulario de cliente. */
const CAMPOS_CLIENTE_IDS = ['contactoCliente', 'tipoDocumento', 'dniCliente', 'telefonoCliente', 'emailCliente'];

/**
 * Habilita o deshabilita los campos del formulario de cliente.
 *
 * @param {boolean} readonly - `true` para bloquear, `false` para habilitar.
 * @returns {void}
 */
function _setCamposClienteReadonly(readonly) {
    CAMPOS_CLIENTE_IDS.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (el.tagName === 'SELECT') el.disabled = readonly;
        else                         el.readOnly  = readonly;
    });
}

/** @type {HTMLElement|null} Elemento badge de estado del cliente (creado dinámicamente). */
let _badgeEl = null;

/**
 * Muestra u oculta el badge de estado del cliente (encontrado / nuevo / buscando).
 *
 * @param {'found'|'new'|'searching'|''} tipo  - Tipo de badge.
 * @param {string}                        texto - Mensaje a mostrar (vacío para ocultar).
 * @returns {void}
 */
function _mostrarBadgeCliente(tipo, texto) {
    if (!_badgeEl) {
        _badgeEl = document.createElement('div');
        _badgeEl.style.cssText = 'font-size:0.81rem;margin-top:6px;padding:5px 10px;border-radius:5px;display:none;';
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

/** @type {HTMLButtonElement|null} Botón "Cambiar cliente" (creado dinámicamente). */
let _btnCambiarEl = null;

/**
 * Muestra u oculta el botón "Cambiar cliente".
 *
 * @param {boolean} visible
 * @returns {void}
 */
function _mostrarBtnCambiar(visible) {
    if (!_btnCambiarEl) {
        _btnCambiarEl = document.createElement('button');
        _btnCambiarEl.type = 'button';
        _btnCambiarEl.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Cambiar cliente';
        _btnCambiarEl.style.cssText = 'font-size:0.78rem;border:none;background:none;color:var(--text-muted);cursor:pointer;text-decoration:underline;padding:2px 0;';
        _btnCambiarEl.addEventListener('click', _limpiarCliente);
        _badgeEl?.after(_btnCambiarEl);
    }
    _btnCambiarEl.style.display = visible ? '' : 'none';
}

/**
 * Actualiza el texto del sidebar del cliente seleccionado.
 *
 * @param {string} html - HTML a insertar en `#clienteSeleccionado`.
 * @returns {void}
 */
function _actualizarSidebarCliente(html) {
    const el = document.getElementById('clienteSeleccionado');
    if (el) el.innerHTML = html;
}

// ─────────────────────────────────────────────────────────────────────────────
// DROPDOWN DE BÚSQUEDA DE CLIENTE
// ─────────────────────────────────────────────────────────────────────────────

/** @type {HTMLElement|null} Contenedor del dropdown de resultados (creado dinámicamente). */
let _dropdown = null;

/**
 * Muestra el dropdown de resultados de búsqueda de clientes.
 *
 * @param {Array<Object>} resultados - Clientes que coinciden con la búsqueda.
 * @returns {void}
 */
function _mostrarDropdown(resultados) {
    if (!_dropdown) {
        _dropdown = document.createElement('div');
        _dropdown.style.cssText = `
            position:absolute;top:calc(100% + 2px);left:0;right:0;z-index:1055;
            background:var(--bg-elevated);border:1px solid var(--border-color);border-radius:6px;
            color:var(--text-primary);
            max-height:220px;overflow-y:auto;
            box-shadow:0 4px 16px rgba(0,0,0,.25);`;
        const wrap = document.querySelector('.search-wrap');
        if (wrap) { wrap.style.position = 'relative'; wrap.appendChild(_dropdown); }
    }

    if (!resultados.length) {
        _dropdown.innerHTML = `<div style="padding:8px 14px;font-size:0.82rem;color:var(--text-muted);">Sin resultados.</div>`;
    } else {
        _dropdown.innerHTML = resultados.map(c => `
            <div class="dd-item" data-id="${c.id_cliente}"
                 style="padding:8px 14px;cursor:pointer;font-size:0.83rem;border-bottom:1px solid var(--border-color);">
                <strong style="color:var(--text-primary);">${c.nombres} ${c.apellidos ?? ''}</strong>
                <small class="d-block" style="color:var(--text-muted);">${c.numero_documento ?? ''} · ${c.telefono ?? ''}</small>
            </div>`).join('');

        _dropdown.querySelectorAll('.dd-item').forEach(el => {
            el.addEventListener('mouseenter', () => { el.style.background = 'var(--bg-hover)'; });
            el.addEventListener('mouseleave', () => { el.style.background = ''; });
            el.addEventListener('mousedown', (e) => {
                e.preventDefault();
                const cliente = state.todosClientes.find(c => Number(c.id_cliente) === parseInt(el.dataset.id));
                if (cliente) _setClienteExistente(cliente);
                _ocultarDropdown();
                const inp = document.getElementById('searchCliente');
                if (inp) inp.value = '';
            });
        });
    }

    _dropdown.style.display = 'block';
}

/** Oculta el dropdown de búsqueda. @returns {void} */
function _ocultarDropdown() {
    if (_dropdown) _dropdown.style.display = 'none';
}

/**
 * Filtra el caché local de clientes por nombre, documento o teléfono
 * y muestra el dropdown con los primeros 8 resultados.
 *
 * @param {string} q - Término de búsqueda.
 * @returns {void}
 */
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

// ─────────────────────────────────────────────────────────────────────────────
// DESCUENTO GLOBAL Y PRECIO FINAL
// ─────────────────────────────────────────────────────────────────────────────

/** Retorna el precio unitario del ítem (sin descuento por ítem; el descuento es global). */
function _precioFinal(item) {
    return item.precio;
}

/**
 * Actualiza las filas de subtotal, descuento y porcentaje en el sidebar.
 * @param {number} totalBruto - Suma de ítems antes del descuento.
 */
function _actualizarDescuentoUI(totalBruto) {
    const descMonto   = state.descuentoMonto ?? 0;
    const subtotalRow = document.getElementById('resumen-subtotal-row');
    const subtotalEl  = document.getElementById('resumenSubtotal');
    const descRow     = document.getElementById('resumen-desc-row');
    const descInfoEl  = document.getElementById('resumenDescInfo');

    if (descMonto > 0 && totalBruto > 0) {
        const pct = Math.round((descMonto / totalBruto) * 100);
        if (subtotalRow) subtotalRow.style.display = '';
        if (subtotalEl)  subtotalEl.textContent = formatters.moneda(totalBruto);
        if (descRow)     descRow.style.display = '';
        if (descInfoEl)  descInfoEl.textContent = `−${formatters.moneda(descMonto)} (${pct}%)`;
    } else {
        if (subtotalRow) subtotalRow.style.display = 'none';
        if (descRow)     descRow.style.display = 'none';
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// RESUMEN LATERAL DE ÍTEMS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Re-renderiza el resumen lateral de ítems seleccionados y el total.
 * @returns {void}
 */
function _actualizarResumen() {
    const el      = document.getElementById('resumenItems');
    const totalEl = document.getElementById('totalResumen');
    if (!el) return;

    if (!state.items.length) {
        el.innerHTML = `<div class="resumen-row" style="color:#666;font-size:0.8rem;justify-content:center;">Sin ítems aún</div>`;
        if (totalEl) totalEl.textContent = 'S/ 0.00';
        _actualizarDescuentoUI(0);
        return;
    }

    const totalBruto = state.items.reduce((s, i) => i.tipo === 'cortesia' ? s : s + i.precio * (i.cantidad ?? 1), 0);
    const descMonto  = Math.min(state.descuentoMonto ?? 0, totalBruto);
    const total      = totalBruto - descMonto;

    el.innerHTML = state.items.map((item, idx) => {
        const cant       = item.cantidad ?? 1;
        const esCortesia = item.tipo === 'cortesia';
        const subtotal   = esCortesia ? 0 : item.precio * cant;
        const cantLabel  = cant > 1 ? `<span style="color:#888;font-size:0.72rem;">×${cant} </span>` : '';

        let precioLabel;
        if (esCortesia) {
            precioLabel = `<span class="resumen-precio" style="font-size:.75rem;color:var(--green-text,#4caf50);font-weight:600;">Gratis</span>`;
        } else {
            precioLabel = `<span class="resumen-precio" style="font-size:0.78rem;">${cantLabel}${formatters.moneda(subtotal)}</span>`;
        }
        return `
        <div class="resumen-row">
            <span class="resumen-nombre" style="font-size:0.78rem;" title="${item.nombre}">${esCortesia ? '<i class="bi bi-gift" style="color:var(--green-text,#4caf50);margin-right:4px;flex-shrink:0;"></i>' : ''}${item.nombre}</span>
            ${precioLabel}
            <button type="button" data-idx="${idx}"
                    style="background:none;border:none;padding:0 2px;color:#e57373;cursor:pointer;font-size:0.78rem;line-height:1;"
                    class="btn-res-del" title="Quitar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>`;
    }).join('');

    if (totalEl) totalEl.textContent = formatters.moneda(total);
    _actualizarDescuentoUI(totalBruto);

    el.querySelectorAll('.btn-res-del').forEach(btn => {
        btn.addEventListener('click', () => {
            state.items.splice(parseInt(btn.dataset.idx), 1);
            _renderContainers();
            _actualizarResumen();
            _saveDraft();
        });
    });
}

// ─────────────────────────────────────────────────────────────────────────────
// SINCRONIZAR N.° ESTUDIANTES
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Actualiza el campo `#numEstudiantes` sumando las cantidades de los ítems
 * de tipo `paquete` (cada paquete corresponde a un grupo de estudiantes).
 *
 * @returns {void}
 */
function _sincronizarNumEstudiantes() {
    const el = document.getElementById('numEstudiantes');
    if (!el) return;
    const total = state.items
        .filter(i => i.tipo === 'paquete')
        .reduce((s, i) => s + (i.cantidad ?? 1), 0);
    el.value = total > 0 ? Math.min(parseInt(el.max) || 100, total) : '';
}

// ─────────────────────────────────────────────────────────────────────────────
// CONTENEDORES DE ÍTEMS (paquetes y servicios)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Re-renderiza ambos contenedores de ítems (paquetes y servicios personalizados).
 * @returns {void}
 */
function _renderContainers() {
    _renderContainer('paquetesContainer', 'paquete');
    _renderServiciosContainer();
    _renderCortesiasContainer();
}

/**
 * Renderiza el contenedor de ítems de un tipo específico.
 * Los paquetes incluyen un control +/− para editar la cantidad en sitio.
 *
 * @param {string} containerId - ID del contenedor DOM.
 * @param {'paquete'|'personalizado'} tipo - Tipo de ítems a renderizar.
 * @returns {void}
 */
function _renderContainer(containerId, tipo) {
    const el = document.getElementById(containerId);
    if (!el) return;

    const filtered = state.items
        .map((item, idx) => ({ item, idx }))
        .filter(({ item }) => item.tipo === tipo);

    if (!filtered.length) { el.innerHTML = ''; return; }

    el.innerHTML = filtered.map(({ item, idx }) => {
        const cant     = item.cantidad ?? 1;
        const subtotal = item.precio * cant;

        const cantControl = tipo === 'paquete'
            ? `<div class="po-qty" style="display:flex;">
                   <button type="button" class="po-qty-btn item-qty-dec" data-idx="${idx}">−</button>
                   <input  type="number" class="po-qty-input item-qty-input"
                           data-idx="${idx}" value="${cant}" min="1" max="999">
                   <button type="button" class="po-qty-btn item-qty-inc" data-idx="${idx}">+</button>
               </div>`
            : (cant > 1 ? `<span style="font-size:0.75rem;color:#888;white-space:nowrap;">×${cant}</span>` : '');

        const nombreEl = tipo === 'paquete' && item.idRef
            ? `<span class="item-nombre-link" data-idref="${item.idRef}"
                     style="flex:1;cursor:pointer;"
                     title="Ver en catálogo">${item.nombre}</span>`
            : `<span style="flex:1;">${item.nombre}</span>`;

        return `
        <div class="item-row d-flex justify-content-between align-items-center p-2 mt-1"
             style="border:1px solid var(--border,#dee2e6);border-radius:6px;font-size:0.82rem;gap:8px;">
            ${nombreEl}
            ${cantControl}
            <span class="item-subtotal" data-idx="${idx}"
                  style="font-weight:600;white-space:nowrap;min-width:62px;text-align:right;">
                ${formatters.moneda(subtotal)}
            </span>
            <button type="button" data-idx="${idx}"
                    style="background:none;border:none;padding:2px 4px;color:#e57373;cursor:pointer;font-size:0.85rem;"
                    class="btn-item-del" title="Quitar">
                <i class="bi bi-trash3"></i>
            </button>
        </div>`;
    }).join('');

    // ── Abrir modal al hacer clic en el nombre (solo paquetes) ──────────────
    el.querySelectorAll('.item-nombre-link').forEach(span => {
        span.addEventListener('click', () => _abrirModalPaquetes(parseInt(span.dataset.idref) || null));
    });

    // ── Eliminar ítem ────────────────────────────────────────────────────────
    el.querySelectorAll('.btn-item-del').forEach(btn => {
        btn.addEventListener('click', () => {
            state.items.splice(parseInt(btn.dataset.idx), 1);
            _renderContainers();
            _actualizarResumen();
            _saveDraft();
        });
    });

    // ── Editar cantidad (solo paquetes) ──────────────────────────────────────
    if (tipo !== 'paquete') return;

    const _aplicarCantidad = (idx, nuevaCant) => {
        nuevaCant = Math.min(999, Math.max(1, nuevaCant));
        state.items[idx].cantidad = nuevaCant;

        const inputEl    = el.querySelector(`.item-qty-input[data-idx="${idx}"]`);
        const subtotalEl = el.querySelector(`.item-subtotal[data-idx="${idx}"]`);
        if (inputEl)    inputEl.value       = nuevaCant;
        if (subtotalEl) subtotalEl.textContent = formatters.moneda(state.items[idx].precio * nuevaCant);

        _actualizarResumen();
        _saveDraft();
    };

    el.querySelectorAll('.item-qty-dec').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx = parseInt(btn.dataset.idx);
            _aplicarCantidad(idx, (state.items[idx].cantidad ?? 1) - 1);
        })
    );

    el.querySelectorAll('.item-qty-inc').forEach(btn =>
        btn.addEventListener('click', () => {
            const idx = parseInt(btn.dataset.idx);
            _aplicarCantidad(idx, (state.items[idx].cantidad ?? 1) + 1);
        })
    );

    el.querySelectorAll('.item-qty-input').forEach(input =>
        input.addEventListener('change', () => {
            const idx = parseInt(input.dataset.idx);
            _aplicarCantidad(idx, parseInt(input.value) || 1);
        })
    );
}

/** Renderiza los servicios adicionales (tipo = 'servicio', precio > 0). */
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
        <div class="item-row d-flex justify-content-between align-items-center p-2 mt-1"
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
        _saveDraft();
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
            _saveDraft();
        })
    );
}

/** Renderiza los productos de cortesía (precio = 0, tipo = 'cortesia'). */
function _renderCortesiasContainer() {
    const el = document.getElementById('cortesiasContainer');
    if (!el) return;

    const cortesias = state.items
        .map((item, idx) => ({ item, idx }))
        .filter(({ item }) => item.tipo === 'cortesia');

    if (!cortesias.length) { el.innerHTML = ''; return; }

    el.innerHTML = cortesias.map(({ item, idx }) => {
        const cant = item.cantidad ?? 1;
        return `
        <div class="d-flex justify-content-between align-items-center p-2 mt-1"
             style="border:1px solid var(--green-text,#4caf50);border-radius:6px;font-size:.82rem;gap:8px;background:rgba(76,175,80,.05);">
            <i class="bi bi-gift" style="color:var(--green-text,#4caf50);flex-shrink:0;"></i>
            <span style="flex:1;">${item.nombre}</span>
            ${cant > 1 ? `<span style="font-size:.72rem;color:var(--text-muted);">×${cant}</span>` : ''}
            <span style="font-size:.75rem;font-weight:600;color:var(--green-text,#4caf50);white-space:nowrap;">Gratis</span>
            <button type="button" data-idx="${idx}"
                    style="background:none;border:none;padding:2px 4px;color:#e57373;cursor:pointer;font-size:.85rem;"
                    class="btn-cortesia-del" title="Quitar">
                <i class="bi bi-trash3"></i>
            </button>
        </div>`;
    }).join('');

    el.querySelectorAll('.btn-cortesia-del').forEach(btn =>
        btn.addEventListener('click', () => {
            state.items.splice(parseInt(btn.dataset.idx), 1);
            _renderContainers();
            _actualizarResumen();
            _saveDraft();
        })
    );
}

// ─────────────────────────────────────────────────────────────────────────────
// MODAL DE PAQUETES
// ─────────────────────────────────────────────────────────────────────────────

/** @type {Object<string,string>} Etiquetas de nivel para mostrar en el modal. */
const NIVEL_LABEL = {
    inicial:    'Inicial',
    primaria:   'Primaria',
    secundaria: 'Secundaria',
    postgrado:  'Postgrado',
    otro:       'Otro',
};

/** @type {string[]} Orden de visualización de niveles en el modal. */
const NIVEL_ORDER = ['inicial', 'primaria', 'secundaria', 'postgrado', 'otro'];

/** @type {Object<string,string>} Estilos CSS de los badges de nivel. */
const NIVEL_STYLE = {
    inicial:    'background:#fff3e0;color:#e65100',
    primaria:   'background:#e3f2fd;color:#1565c0',
    secundaria: 'background:#f3e5f5;color:#6a1b9a',
    postgrado:  'background:#fce4ec;color:#c62828',
    otro:       'background:#fafafa;color:#616161',
};

/**
 * Construye el contenido del modal de paquetes agrupando por categoría (tabs)
 * y añadiendo botones de filtro por nivel.
 *
 * @param {Array<Object>} paquetes - Lista de paquetes a mostrar en el modal.
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
                <div class="paquete-option" data-nivel="${nivel}" data-id="${p.id_paquete}"
                     onclick="seleccionarOpcion(this,'${nombre}',${p.precio ?? 0},${p.id_paquete})">
                    <div class="po-left">
                        <div class="po-name">${p.nombre_paquete}</div>
                        ${descHtml ? `<div class="po-desc" title="${descAttr}">${descHtml}</div>` : ''}
                        <span class="nivel-badge" style="${badgeStyle}">${NIVEL_LABEL[nivel] ?? nivel}</span>
                    </div>
                    <span class="po-price">${formatters.moneda(p.precio ?? 0)}</span>
                    <div class="po-qty" onclick="event.stopPropagation()">
                        <button type="button" class="po-qty-btn" onclick="poQtyStep(this,-1)">−</button>
                        <input type="number" class="po-qty-input" value="1" min="1" max="999">
                        <button type="button" class="po-qty-btn" onclick="poQtyStep(this,1)">+</button>
                    </div>
                    <i class="bi bi-check-circle-fill po-check"></i>
                </div>`;
        }).join('');
        return `<div class="cat-panel overflow-auto${i === 0 ? ' active' : ''}" id="cat-panel-${cat}" style="max-height:22rem;">${rows}</div>`;
    }).join('');
}

/**
 * Cambia la tab de categoría activa en el modal de paquetes.
 * Las selecciones previas se conservan al cambiar de tab.
 *
 * @param {string}      cat   - Nombre de la categoría.
 * @param {HTMLElement} tabEl - Botón de tab clickeado.
 */
window.cambiarCategoria = function (cat, tabEl) {
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.cat-panel').forEach(p => p.classList.remove('active'));
    tabEl.classList.add('active');
    document.getElementById(`cat-panel-${cat}`)?.classList.add('active');
};

/**
 * Filtra las opciones del modal de paquetes por nivel educativo.
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

/** Devuelve el n.° de estudiantes ingresado en el modal de paquetes (0 si vacío). */
function _getModalNumEst() {
    return parseInt(document.getElementById('modalNumEstudiantes')?.value) || 0;
}

/** Suma las cantidades de todos los paquetes actualmente seleccionados en el modal. */
function _calcularTotalModalQty() {
    let total = 0;
    state.paquetesSeleccionados.forEach(({ el }) => {
        total += parseInt(el?.querySelector('.po-qty-input')?.value) || 0;
    });
    return total;
}

/**
 * Actualiza el botón de confirmar y el hint con el contador en vivo.
 * El botón se habilita solo cuando el total de cantidades >= numEst.
 */
function _actualizarBtnConfirmar() {
    const btn  = document.getElementById('btn-confirmar-paquetes');
    const hint = document.getElementById('paq-hint');
    if (!btn) return;

    const numEst   = _getModalNumEst();
    const n        = state.paquetesSeleccionados.size;
    const totalQty = _calcularTotalModalQty();

    if (numEst <= 0) {
        btn.disabled    = true;
        btn.textContent = 'Primero ingresa el n.° de estudiantes';
        if (hint) hint.textContent = 'Ingresa el n.° de estudiantes para continuar.';
        return;
    }

    if (n === 0) {
        btn.disabled    = true;
        btn.textContent = 'Selecciona un paquete';
        if (hint) hint.textContent = 'Haz clic en un paquete para seleccionarlo · puedes elegir varios';
        return;
    }

    const alcanza = totalQty >= numEst;
    btn.disabled    = !alcanza;
    btn.textContent = alcanza
        ? `Agregar ${n} paquete${n > 1 ? 's' : ''}`
        : `Faltan ${numEst - totalQty} para cubrir a todos`;

    if (hint) {
        hint.innerHTML = alcanza
            ? `Total: <strong style="color:#198754">${totalQty}</strong> / ${numEst} — <i class="bi bi-check-circle-fill" style="color:#198754;"></i>`
            : `Total: <strong style="color:#dc3545">${totalQty}</strong> / ${numEst} estudiantes — selecciona más o aumenta la cantidad`;
    }
}


/**
 * Alterna la selección de un paquete en el modal (multi-select).
 * Si ya está seleccionado lo deselecciona; si no, lo agrega al mapa.
 *
 * @param {HTMLElement} el     - Elemento `.paquete-option` clickeado.
 * @param {string}      nombre - Nombre del paquete.
 * @param {number}      precio - Precio del paquete.
 * @param {number}      idRef  - ID del paquete en BD.
 */
window.seleccionarOpcion = function (el, nombre, precio, idRef) {
    const key = idRef ?? nombre;

    if (el.classList.contains('selected')) {
        el.classList.remove('selected');
        state.paquetesSeleccionados.delete(key);
    } else {
        el.classList.add('selected');
        const numEst   = _getModalNumEst();
        const qtyInput = el.querySelector('.po-qty-input');
        if (qtyInput) {
            qtyInput.min   = 1;
            qtyInput.value = numEst > 0 ? numEst : 1;
        }
        state.paquetesSeleccionados.set(key, {
            idRef:  idRef ? parseInt(idRef) : null,
            nombre,
            precio: parseFloat(precio) || 0,
            el,
        });
    }

    _actualizarBtnConfirmar();
};

/**
 * Incrementa o decrementa la cantidad de un paquete seleccionado.
 *
 * @param {HTMLElement} btn  - Botón +/− presionado.
 * @param {number}      step - +1 o -1.
 */
window.poQtyStep = function (btn, step) {
    const input = step > 0 ? btn.previousElementSibling : btn.nextElementSibling;
    const val   = parseInt(input?.value) || 1;
    const next  = Math.max(1, val + step);
    if (input) input.value = next;
    _actualizarBtnConfirmar();
};

// ─────────────────────────────────────────────────────────────────────────────
// MODAL DE SERVICIOS
// ─────────────────────────────────────────────────────────────────────────────


// ─────────────────────────────────────────────────────────────────────────────
// BORRADOR EN LOCALSTORAGE
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Serializa y guarda el estado actual del formulario como borrador.
 * @returns {void}
 */
function _saveDraft() {
    manager.guardar({
        cliente:        state.cliente,
        esNuevoCliente: state.esNuevoCliente,
        camposCliente: state.esNuevoCliente ? {
            contacto: document.getElementById('contactoCliente')?.value  ?? '',
            tipoDoc:  document.getElementById('tipoDocumento')?.value    ?? 'DNI',
            dni:      document.getElementById('dniCliente')?.value       ?? '',
            telefono: document.getElementById('telefonoCliente')?.value  ?? '',
            correo:   document.getElementById('emailCliente')?.value     ?? '',
        } : null,
        tipoInstitucion:  document.querySelector('input[name="tipoInstitucion"]:checked')?.value ?? 'colegio',
        numEstudiantes:   parseInt(document.getElementById('numEstudiantes')?.value) || 0,
        items:          state.items,
        descuentoMonto: state.descuentoMonto ?? 0,
        notas: document.getElementById('notas')?.value ?? '',
        colegio: {
            nombre:    document.getElementById('nombreColegio')?.value    ?? '',
            provincia: document.getElementById('provinciaColegio')?.value ?? '',
            distrito:  document.getElementById('distritoColegio')?.value  ?? '',
        },
        promocion: {
            nombre:  document.getElementById('nombreProm')?.value?.trim()  ?? '',
            grado:   document.getElementById('gradoProm')?.value           ?? '',
            seccion: document.getElementById('seccionProm')?.value?.trim() ?? '',
        },
    });
}

/**
 * Restaura el estado del formulario desde un borrador guardado en `localStorage`.
 *
 * @param {Object|null} borrador - Datos del borrador, o `null` si no existe.
 * @returns {void}
 */
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
        const contacto = c.contacto ?? `${c.nombres ?? ''} ${c.apellidos ?? ''}`.trim();
        _llenarCamposCliente({ contacto, tipoDoc: c.tipoDoc ?? 'DNI', dni: c.dni, telefono: c.telefono, correo: c.correo });
        _setModoNuevoCliente();
    }

    if (borrador.descuentoMonto > 0) {
        state.descuentoMonto = borrador.descuentoMonto;
        const descEl = document.getElementById('descuentoMonto');
        if (descEl) descEl.value = borrador.descuentoMonto;
    }

    if (borrador.items?.length) {
        state.items = borrador.items;
        _renderContainers();
        _actualizarResumen();
    }

    const notasEl = document.getElementById('notas');
    if (notasEl && borrador.notas) notasEl.value = borrador.notas;

    if (borrador.numEstudiantes > 0) {
        const numEstEl = document.getElementById('numEstudiantes');
        if (numEstEl) numEstEl.value = borrador.numEstudiantes;
    }

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

    if (borrador.promocion) {
        const np = document.getElementById('nombreProm');
        const gp = document.getElementById('gradoProm');
        const sp = document.getElementById('seccionProm');
        if (np && borrador.promocion.nombre)  np.value = borrador.promocion.nombre;
        if (gp && borrador.promocion.grado)   gp.value = borrador.promocion.grado;
        if (sp && borrador.promocion.seccion) sp.value = borrador.promocion.seccion;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// VALIDACIÓN DEL FORMULARIO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Valida todos los campos del formulario antes del envío.
 *
 * @returns {string|null} Mensaje de error, o `null` si el formulario es válido.
 */
function _validar() {
    if (!state.cliente && !state.esNuevoCliente) {
        return 'Busca un cliente existente o ingresa sus datos para registrar uno nuevo.';
    }

    if (state.esNuevoCliente) {
        const contacto = document.getElementById('contactoCliente')?.value?.trim();
        const tipoDoc  = document.getElementById('tipoDocumento')?.value ?? 'DNI';
        const dni      = document.getElementById('dniCliente')?.value?.trim();
        const telefono = document.getElementById('telefonoCliente')?.value?.trim();
        if (!contacto) return 'El nombre de contacto es obligatorio.';
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/.test(contacto))
            return 'El nombre de contacto solo puede contener letras y tildes.';
        if (!telefono) return 'El teléfono del cliente es obligatorio.';
        if (!TEL_REGEX.test(telefono)) return 'El teléfono debe tener 9 dígitos y comenzar con 9.';
        if (dni) {
            const rule = DOC_RULES[tipoDoc];
            if (rule && !rule.regex.test(dni))
                return `Documento inválido para ${tipoDoc}: se esperan ${rule.hint}.`;
        }
        const correo = document.getElementById('emailCliente')?.value?.trim();
        if (correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo))
            return 'El correo electrónico no tiene un formato válido.';
    }

    const itemsPagados = state.items.filter(i => i.tipo !== 'cortesia');
    if (!itemsPagados.length) return 'Agrega al menos un paquete o servicio a la cotización.';

    const numEst = parseInt(document.getElementById('numEstudiantes')?.value) || 0;
    if (numEst <= 0) {
        return 'El n.° de estudiantes es obligatorio. Agrégalo al seleccionar paquetes desde el modal.';
    }
    if (numEst > 1000) {
        return 'El n.° de estudiantes no puede superar 1000.';
    }

    const nombreColegio = document.getElementById('nombreColegio')?.value?.trim() ?? '';
    if (!nombreColegio) return 'El nombre de la institución es obligatorio.';
    if (nombreColegio.length < 3) return 'El nombre de la institución debe tener al menos 3 caracteres.';

    const wrapGrado = document.getElementById('wrap-grado');
    if (wrapGrado?.style.display !== 'none' && !document.getElementById('gradoProm')?.value) {
        return 'Selecciona el grado de la promoción.';
    }

    return null;
}

// ─────────────────────────────────────────────────────────────────────────────
// PAYLOAD PARA LA API
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Construye el payload completo para `POST /api/cotizaciones`.
 *
 * @param {number} idCliente - ID del cliente (existente o recién creado).
 * @returns {Object} Payload con `id_cliente`, `detalles[]`, `colegio`, `sesion`, etc.
 */
function _buildPayload(idCliente) {
    const totalBruto = state.items.reduce((s, i) => i.tipo === 'cortesia' ? s : s + i.precio * (i.cantidad ?? 1), 0);
    const descMonto  = Math.min(state.descuentoMonto ?? 0, totalBruto);
    const total      = totalBruto - descMonto;
    const detalles = state.items.map(item => {
        const esCortesia = item.tipo === 'cortesia';
        const cant       = item.cantidad ?? 1;
        const pUnit      = esCortesia ? 0 : item.precio;
        const desc       = esCortesia ? `[Cortesía] ${item.nombre}` : item.nombre;
        return {
            tipo_item:       item.tipo === 'paquete' ? 'paquete' : 'personalizado',
            id_referencia:   item.idRef ?? null,
            descripcion:     desc,
            cantidad:        cant,
            precio_unitario: pUnit,
            subtotal:        pUnit * cant,
        };
    });

    const fecha = null;

    const idCliente2Raw = document.getElementById('idCliente2')?.value;
    const idCliente2    = idCliente2Raw ? parseInt(idCliente2Raw) : null;

    return {
        id_cliente:      idCliente,
        id_cliente2:     idCliente2 || null,
        observaciones:   document.getElementById('notas')?.value?.trim() ?? null,
        total_estimado:  total,
        descuento_monto: descMonto > 0 ? descMonto : null,
        detalles,
        colegio: {
            nombre:    document.getElementById('nombreColegio')?.value?.trim()    ?? null,
            provincia: document.getElementById('provinciaColegio')?.value         ?? null,
            distrito:  document.getElementById('distritoColegio')?.value          ?? null,
        },
        sesion: {
            tipo_institucion: document.querySelector('input[name="tipoInstitucion"]:checked')?.value ?? 'colegio',
            nombre_promocion: document.getElementById('nombreProm')?.value?.trim()      ?? null,
            num_estudiantes:  parseInt(document.getElementById('numEstudiantes')?.value) || null,
            grado:            document.getElementById('gradoProm')?.value               || null,
            seccion:          document.getElementById('seccionProm')?.value?.trim()     || null,
            fecha,
        },
    };
}

// ─────────────────────────────────────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────────────────────────────────────

/** @type {bootstrap.Modal|null} Modal de selección de paquetes. */
let _modalPaquete      = null;
/** @type {bootstrap.Modal|null} Modal de confirmación antes de guardar. */
let _modalConfirmacion = null;

// ─────────────────────────────────────────────────────────────────────────────
// MODAL DE CONFIRMACIÓN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Puebla el modal de confirmación con el resumen de la cotización actual
 * y lo muestra al usuario.
 *
 * Datos destacados: n.° de estudiantes, paquetes con cantidad y precio, total.
 */
function _mostrarModalConfirmacion() {
    const numEst     = parseInt(document.getElementById('numEstudiantes')?.value) || 0;
    const notas      = document.getElementById('notas')?.value?.trim() ?? '';
    const totalBruto = state.items.reduce((s, i) => i.tipo === 'cortesia' ? s : s + _precioFinal(i) * (i.cantidad ?? 1), 0);
    const descMonto  = Math.min(state.descuentoMonto ?? 0, totalBruto);
    const total      = totalBruto - descMonto;

    // ── Bloques de resumen ──────────────────────────────────────────────────
    const numEstEl = document.getElementById('conf-num-estudiantes');
    if (numEstEl) {
        numEstEl.textContent = numEst > 0 ? numEst : '—';
        const bloqueEst = document.getElementById('conf-bloque-estudiantes');
        if (bloqueEst) {
            bloqueEst.style.borderColor = numEst > 0
                ? 'var(--accent,#B49040)'
                : 'var(--border,#D6D0C8)';
        }
    }

    const totalEl = document.getElementById('conf-total');
    if (totalEl) totalEl.textContent = formatters.moneda(total);

    // ── Tabla de ítems ──────────────────────────────────────────────────────
    const tablaEl = document.getElementById('conf-tabla-items');
    if (tablaEl) {
        const paquetes     = state.items.filter(i => i.tipo === 'paquete');
        const cortesias    = state.items.filter(i => i.tipo === 'cortesia');
        const otros        = state.items.filter(i => i.tipo !== 'paquete' && i.tipo !== 'cortesia');
        const COLS = '1fr 55px 105px 110px';

        const filaHeader = `
            <div style="display:grid;grid-template-columns:${COLS};
                        gap:0;background:var(--sidebar-bg,#1A1814);color:var(--sidebar-link,#C8BCA8);
                        font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;">
                <div style="padding:.45rem 1rem;">Descripción</div>
                <div style="padding:.45rem .5rem;text-align:center;">Cant.</div>
                <div style="padding:.45rem .75rem;text-align:right;">Precio u.</div>
                <div style="padding:.45rem 1rem;text-align:right;">Subtotal</div>
            </div>`;

        const filaItem = (item, esDestacado) => {
            const cant     = item.cantidad ?? 1;
            const descPct  = item.descuento ?? 0;
            const pFinal   = _precioFinal(item);
            const subtotal = pFinal * cant;
            const bg       = esDestacado ? 'background:var(--accent-light,#F4EAD0);' : '';
            return `
            <div style="display:grid;grid-template-columns:${COLS};
                        gap:0;${bg}border-bottom:1px solid var(--border,#D6D0C8);
                        align-items:center;">
                <div style="padding:.55rem 1rem;">
                    ${esDestacado
                        ? `<i class="bi bi-box-seam me-1" style="color:var(--accent,#B49040);font-size:.8rem;"></i>`
                        : `<i class="bi bi-camera2 me-1" style="color:var(--text-muted,#7C7468);font-size:.8rem;"></i>`}
                    <span style="font-size:.82rem;font-weight:${esDestacado ? '600' : '400'};
                                 color:var(--text-primary,#1C1916);">${item.nombre}</span>
                </div>
                <div style="padding:.55rem .5rem;text-align:center;font-size:.82rem;
                            font-weight:${esDestacado ? '700' : '400'};
                            color:${esDestacado ? 'var(--accent-text,#6A5018)' : 'var(--text-secondary,#4E4840)'};">
                    ${cant}
                </div>
                <div style="padding:.55rem .75rem;text-align:right;font-size:.8rem;color:var(--text-muted,#7C7468);">
                    ${descPct > 0
                        ? `<span style="text-decoration:line-through;">${formatters.moneda(item.precio)}</span>
                           <br><span style="color:var(--green-text,#1A5E2E);">${formatters.moneda(pFinal)} <small>(−${descPct}%)</small></span>`
                        : formatters.moneda(item.precio)}
                </div>
                <div style="padding:.55rem 1rem;text-align:right;font-size:.82rem;font-weight:600;
                            color:${esDestacado ? 'var(--green-text,#1A5E2E)' : 'var(--text-primary,#1C1916)'};">
                    ${formatters.moneda(subtotal)}
                </div>
            </div>`;
        };

        const descPct     = (descMonto > 0 && totalBruto > 0) ? Math.round((descMonto / totalBruto) * 100) : 0;
        const filaDescuento = descMonto > 0 ? `
            <div style="display:grid;grid-template-columns:${COLS};gap:0;
                        background:var(--bg-input,#ECEAE4);border-top:1px solid var(--border,#D6D0C8);">
                <div style="padding:.5rem 1rem;font-size:.78rem;font-weight:600;
                            color:var(--text-secondary,#4E4840);text-align:right;
                            grid-column:1/4;">SUBTOTAL</div>
                <div style="padding:.5rem 1rem;text-align:right;font-size:.85rem;
                            font-weight:600;color:var(--text-secondary,#4E4840);">
                    ${formatters.moneda(totalBruto)}
                </div>
            </div>
            <div style="display:grid;grid-template-columns:${COLS};gap:0;
                        background:var(--bg-input,#ECEAE4);">
                <div style="padding:.5rem 1rem;font-size:.78rem;font-weight:600;
                            color:var(--green-text,#1A5E2E);text-align:right;
                            grid-column:1/4;">DESCUENTO (${descPct}%)</div>
                <div style="padding:.5rem 1rem;text-align:right;font-size:.85rem;
                            font-weight:600;color:var(--green-text,#1A5E2E);">
                    −${formatters.moneda(descMonto)}
                </div>
            </div>` : '';

        const filaTotal = `
            ${filaDescuento}
            <div style="display:grid;grid-template-columns:${COLS};gap:0;
                        background:var(--bg-input,#ECEAE4);border-top:1px solid var(--border,#D6D0C8);">
                <div style="padding:.65rem 1rem;font-size:.8rem;font-weight:700;
                            color:var(--text-secondary,#4E4840);text-align:right;
                            grid-column:1/4;">TOTAL ESTIMADO</div>
                <div style="padding:.65rem 1rem;text-align:right;font-size:.97rem;
                            font-weight:800;color:var(--green-text,#1A5E2E);white-space:nowrap;">
                    ${formatters.moneda(total)}
                </div>
            </div>`;

        const sinItems = `
            <div style="padding:1.2rem;text-align:center;font-size:.82rem;color:var(--text-muted,#7C7468);">
                Sin ítems registrados.
            </div>`;

        // Fila especial para cortesías
        const filaCortesia = (item) => {
            const cant = item.cantidad ?? 1;
            return `
            <div style="display:grid;grid-template-columns:${COLS};gap:0;
                        border-bottom:1px solid var(--border,#D6D0C8);align-items:center;
                        background:rgba(76,175,80,.05);">
                <div style="padding:.55rem 1rem;">
                    <i class="bi bi-gift me-1" style="color:var(--green-text,#1A5E2E);font-size:.8rem;"></i>
                    <span style="font-size:.82rem;color:var(--green-text,#1A5E2E);">${item.nombre}</span>
                </div>
                <div style="padding:.55rem .5rem;text-align:center;font-size:.82rem;">${cant}</div>
                <div style="padding:.55rem .75rem;text-align:right;font-size:.82rem;color:var(--green-text,#1A5E2E);">Gratis</div>
                <div style="padding:.55rem 1rem;text-align:right;font-size:.82rem;font-weight:600;color:var(--green-text,#1A5E2E);">S/ 0.00</div>
            </div>`;
        };

        tablaEl.innerHTML = filaHeader
            + (paquetes.length || otros.length || cortesias.length
                ? [...paquetes.map(i => filaItem(i, true)),
                   ...otros.map(i => filaItem(i, false)),
                   ...cortesias.map(i => filaCortesia(i))].join('')
                : sinItems)
            + filaTotal;
    }

    // ── Notas ───────────────────────────────────────────────────────────────
    const wrapNotas = document.getElementById('conf-wrap-notas');
    const notasEl   = document.getElementById('conf-notas');
    if (wrapNotas && notasEl) {
        if (notas) {
            notasEl.textContent  = notas;
            wrapNotas.style.display = '';
        } else {
            wrapNotas.style.display = 'none';
        }
    }

    _modalConfirmacion?.show();
}

// ─────────────────────────────────────────────────────────────────────────────
// SEGUNDO CLIENTE
// ─────────────────────────────────────────────────────────────────────────────

/** @type {HTMLElement|null} Dropdown del segundo cliente (creado dinámicamente). */
let _dropdown2 = null;

function _setCliente2Existente(c) {
    state.cliente2        = c;
    state.esNuevoCliente2 = false;

    const set = (id, v) => { const el = document.getElementById(id); if (el) el.value = v ?? ''; };
    set('contactoCliente2',  `${c.nombres ?? ''} ${c.apellidos ?? ''}`.trim());
    set('tipoDocumento2',    c.tipo_documento   ?? 'DNI');
    set('dniCliente2',       c.numero_documento ?? '');
    set('telefonoCliente2',  c.telefono         ?? '');
    set('emailCliente2',     c.correo           ?? '');
    set('idCliente2',        c.id_cliente       ?? '');

    ['contactoCliente2','tipoDocumento2','dniCliente2','telefonoCliente2','emailCliente2'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (el.tagName === 'SELECT') el.disabled = true; else el.readOnly = true;
    });

    const fb = document.getElementById('searchFeedback2');
    if (fb) {
        fb.style.color = 'var(--green-text,#2e7d32)';
        fb.innerHTML   = `<i class="bi bi-check-circle-fill me-1"></i>${c.nombres} ${c.apellidos ?? ''} — seleccionado`;
    }
    _ocultarDropdown2();
}

function _setModoNuevoCliente2() {
    state.cliente2        = null;
    state.esNuevoCliente2 = true;
    document.getElementById('idCliente2').value = '';
    ['contactoCliente2','tipoDocumento2','dniCliente2','telefonoCliente2','emailCliente2'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (el.tagName === 'SELECT') el.disabled = false; else el.readOnly = false;
    });
    const fb = document.getElementById('searchFeedback2');
    if (fb) { fb.style.color = 'var(--text-muted)'; fb.textContent = 'Cliente nuevo — se registrará al guardar'; }
}

function _limpiarCliente2() {
    state.cliente2        = null;
    state.esNuevoCliente2 = false;
    ['contactoCliente2','dniCliente2','telefonoCliente2','emailCliente2','idCliente2'].forEach(id => {
        const el = document.getElementById(id); if (el) el.value = '';
    });
    const td2 = document.getElementById('tipoDocumento2');
    if (td2) { td2.value = 'DNI'; td2.disabled = false; }
    const fb = document.getElementById('searchFeedback2');
    if (fb) fb.textContent = '';
    document.getElementById('searchCliente2').value = '';
    _ocultarDropdown2();
}

function _ocultarDropdown2() { if (_dropdown2) _dropdown2.style.display = 'none'; }

function _mostrarDropdown2(resultados) {
    const wrap = document.querySelector('#cliente2-fields .search-wrap');
    if (!wrap) return;
    if (!_dropdown2) {
        _dropdown2 = document.createElement('div');
        _dropdown2.style.cssText = `
            position:absolute;top:calc(100% + 2px);left:0;right:0;z-index:1055;
            background:var(--bg-elevated);border:1px solid var(--border-color);border-radius:6px;
            color:var(--text-primary);max-height:180px;overflow-y:auto;
            box-shadow:0 4px 16px rgba(0,0,0,.25);`;
        wrap.style.position = 'relative';
        wrap.appendChild(_dropdown2);
    }

    if (!resultados.length) {
        _dropdown2.innerHTML = `<div style="padding:8px 14px;font-size:.82rem;color:var(--text-muted);">Sin resultados.</div>`;
    } else {
        _dropdown2.innerHTML = resultados.map(c => `
            <div class="dd2-item" data-id="${c.id_cliente}"
                 style="padding:8px 14px;cursor:pointer;font-size:.83rem;border-bottom:1px solid var(--border-color);">
                <strong style="color:var(--text-primary);">${c.nombres} ${c.apellidos ?? ''}</strong>
                <small class="d-block" style="color:var(--text-muted);">${c.numero_documento ?? ''} · ${c.telefono ?? ''}</small>
            </div>`).join('');

        _dropdown2.querySelectorAll('.dd2-item').forEach(el => {
            el.addEventListener('mouseenter', () => { el.style.background = 'var(--bg-hover)'; });
            el.addEventListener('mouseleave', () => { el.style.background = ''; });
            el.addEventListener('mousedown', e => {
                e.preventDefault();
                const c = state.todosClientes.find(x => Number(x.id_cliente) === parseInt(el.dataset.id));
                if (c) _setCliente2Existente(c);
                _ocultarDropdown2();
                document.getElementById('searchCliente2').value = '';
            });
        });
    }
    _dropdown2.style.display = 'block';
}

function _initSegundoClienteListeners() {
    const btnToggle  = document.getElementById('btn-toggle-cliente2');
    const btnQuitar  = document.getElementById('btn-quitar-cliente2');
    const fields     = document.getElementById('cliente2-fields');
    const iconToggle = document.getElementById('icon-toggle-cliente2');
    const lblToggle  = document.getElementById('label-toggle-cliente2');

    const _setToggleActivo = (activo) => {
        fields.style.display  = activo ? '' : 'none';
        iconToggle.className  = activo ? 'bi bi-person-check' : 'bi bi-person-plus';
        lblToggle.textContent = activo ? '✓ 2.º responsable' : '+ 2.º responsable';
        btnToggle.style.background   = activo ? 'var(--accent)'       : 'var(--accent-light)';
        btnToggle.style.color        = activo ? '#fff'                 : 'var(--accent-text)';
        btnToggle.style.borderColor  = activo ? 'var(--accent)'       : 'var(--accent)';
    };

    btnToggle?.addEventListener('click', () => {
        const visible = fields.style.display !== 'none';
        _setToggleActivo(!visible);
        if (visible) _limpiarCliente2();
    });

    btnQuitar?.addEventListener('click', () => {
        _setToggleActivo(false);
        _limpiarCliente2();
    });

    const searchInput2 = document.getElementById('searchCliente2');
    const searchBtn2   = document.getElementById('btnBuscar2');

    const _filtrar2 = q => {
        q = (q || '').toLowerCase().trim();
        if (!q) { _ocultarDropdown2(); return; }
        const res = state.todosClientes.filter(c => {
            const nombre = `${c.nombres ?? ''} ${c.apellidos ?? ''}`.toLowerCase();
            return nombre.includes(q)
                || (c.numero_documento ?? '').toLowerCase().includes(q)
                || (c.telefono ?? '').includes(q);
        }).slice(0, 8);
        _mostrarDropdown2(res);
    };

    const _doSearch2 = async () => {
        const q = searchInput2?.value?.trim();
        if (!q) { _ocultarDropdown2(); return; }
        const exacto = state.todosClientes.find(c => (c.numero_documento ?? '').toLowerCase() === q.toLowerCase());
        if (exacto) { _setCliente2Existente(exacto); searchInput2.value = ''; _ocultarDropdown2(); return; }

        if (/^\d{8}$/.test(q)) {
            const fb = document.getElementById('searchFeedback2');
            if (fb) fb.textContent = 'Consultando RENIEC...';
            try {
                const res = await clienteApi.reniecDni(q);
                const d   = res.data;
                ['contactoCliente2','dniCliente2','telefonoCliente2','emailCliente2'].forEach(id => {
                    const el = document.getElementById(id); if (el) el.value = '';
                });
                const el2 = document.getElementById('contactoCliente2');
                if (el2) el2.value = `${d.nombres ?? ''} ${d.apellidos ?? ''}`.trim();
                document.getElementById('dniCliente2').value = q;
                _setModoNuevoCliente2();
                if (fb) fb.innerHTML = '<i class="bi bi-info-circle me-1"></i>Datos del RENIEC — completa los faltantes';
                searchInput2.value = '';
            } catch {
                _setModoNuevoCliente2();
                document.getElementById('dniCliente2').value = q;
                searchInput2.value = '';
            }
            return;
        }
        _filtrar2(q);
    };

    searchBtn2?.addEventListener('click', _doSearch2);
    searchInput2?.addEventListener('input', () => _filtrar2(searchInput2.value));
    searchInput2?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); _doSearch2(); } });
    document.addEventListener('click', e => { if (!e.target.closest('#cliente2-fields .search-wrap')) _ocultarDropdown2(); });

    document.getElementById('telefonoCliente2')?.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 9);
    });
}

/**
 * Prepara y abre el modal de paquetes.
 * Si se pasa scrollToIdRef, cambia a la tab correspondiente y hace scroll al producto.
 *
 * @param {number|null} scrollToIdRef - ID del paquete al que debe desplazarse el modal.
 */
function _abrirModalPaquetes(scrollToIdRef = null) {
    state.paquetesSeleccionados = new Map();
    _nivelFiltro = 'todos';

    const formNumEst    = parseInt(document.getElementById('numEstudiantes')?.value) || 0;
    const modalNumEstEl = document.getElementById('modalNumEstudiantes');
    if (modalNumEstEl) modalNumEstEl.value = formNumEst > 0 ? formNumEst : '';

    const defaultQty = formNumEst > 0 ? formNumEst : 1;
    document.querySelectorAll('.nivel-filtro-btn').forEach((b, i) => b.classList.toggle('active', i === 0));
    document.querySelectorAll('.paquete-option').forEach(o => {
        o.classList.remove('selected');
        o.style.display = '';
        const qi = o.querySelector('.po-qty-input');
        const id = parseInt(o.dataset.id);
        const ya = state.items.find(i => i.tipo === 'paquete' && i.idRef === id);
        if (ya) {
            o.classList.add('selected');
            if (qi) { qi.value = ya.cantidad ?? 1; qi.min = 1; qi.max = ''; }
            state.paquetesSeleccionados.set(id, { idRef: id, nombre: ya.nombre, precio: ya.precio, el: o });
        } else {
            if (qi) { qi.value = defaultQty; qi.min = 1; qi.max = ''; }
        }
    });
    _actualizarBtnConfirmar();

    if (scrollToIdRef) {
        const paqEl   = document.getElementById('modalPaquete');
        const onShown = () => {
            paqEl?.removeEventListener('shown.bs.modal', onShown);
            const target = document.querySelector(`.paquete-option[data-id="${scrollToIdRef}"]`);
            if (!target) return;
            // Activar la tab correcta si el producto está en otra categoría
            const panel = target.closest('.cat-panel');
            if (panel && !panel.classList.contains('active')) {
                const catId = panel.id.replace('cat-panel-', '');
                const tab   = document.querySelector(`.cat-tab[onclick*="'${catId}'"]`);
                if (tab) window.cambiarCategoria(catId, tab);
            }
            target.scrollIntoView({ block: 'nearest' });
            // Pulso visual para identificar el producto
            target.style.outline       = '2px solid var(--accent, #B49040)';
            target.style.outlineOffset = '-2px';
            setTimeout(() => { target.style.outline = ''; target.style.outlineOffset = ''; }, 1500);
        };
        paqEl?.addEventListener('shown.bs.modal', onShown);
    }

    _modalPaquete?.show();
}

/**
 * Función principal de inicialización del módulo.
 * Carga datos, puebla los modales, restaura el borrador y registra todos los listeners.
 *
 * @returns {Promise<void>}
 */
async function init() {
    /* 1. Cargar clientes y paquetes en paralelo */
    try {
        const [resC, resP] = await Promise.all([
            clienteApi.listar(),
            paqueteApi.listar({ estado: 'ACTIVO', con_reglas: 1 }),
        ]);
        state.todosClientes = resC.data ?? [];
        state.todosPaquetes = resP.data ?? [];
    } catch {
        alerts.error('Error al cargar datos iniciales. Recarga la página.');
    }

    /* 2. Poblar modal de paquetes agrupado por categoría */
    _poblarModalPaquetes(state.todosPaquetes);

    /* 3. Restaurar borrador */
    _restoreDraft(manager.cargar());

    /* 4. Instanciar modales Bootstrap */
    const paqEl  = document.getElementById('modalPaquete');
    const confEl = document.getElementById('modalConfirmacion');
    if (paqEl)  _modalPaquete      = new bootstrap.Modal(paqEl);
    if (confEl) _modalConfirmacion = new bootstrap.Modal(confEl);

    /* 6a. Contacto: solo letras y tildes; activa modo nuevo si el usuario escribe directo */
    document.getElementById('contactoCliente')?.addEventListener('input', function () {
        this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]/g, '');
        const fb = document.getElementById('contactoFeedback');
        if (fb) {
            if (this.value.trim().length > 0) {
                fb.style.color   = 'var(--green-text)';
                fb.textContent   = '✓';
            } else {
                fb.textContent = '';
            }
        }
        if (!state.cliente && !state.esNuevoCliente && this.value.trim()) {
            _setModoNuevoCliente();
        }
        if (state.esNuevoCliente) _saveDraft();
    });

    /* 6b. Teléfono: solo dígitos; activa modo nuevo si el usuario escribe directo */
    document.getElementById('telefonoCliente')?.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 9);
        _validarTel();
        if (!state.cliente && !state.esNuevoCliente && this.value.trim()) {
            _setModoNuevoCliente();
        }
    });

    /* 6b. Tipo de documento: actualizar placeholder y limpiar campo */
    _actualizarPlaceholderDoc();
    document.getElementById('tipoDocumento')?.addEventListener('change', () => {
        _actualizarPlaceholderDoc();
        _validarDoc();
        document.getElementById('dniCliente').value = '';
        document.getElementById('docFeedback').textContent = '';
    });

    /* 6c. Campo documento: filtrar caracteres y auto-buscar al perder foco */
    const dniInput = document.getElementById('dniCliente');
    dniInput?.addEventListener('input', function () {
        const tipo   = document.getElementById('tipoDocumento')?.value ?? 'DNI';
        const maxlen = DOC_RULES[tipo]?.maxlen ?? 12;
        this.value   = tipo === 'DNI'
            ? this.value.replace(/\D/g, '').slice(0, maxlen)
            : this.value.replace(/[^A-Za-z0-9]/g, '').slice(0, maxlen);
        _validarDoc();

        if (state.cliente) {
            // Cliente confirmado editando su DNI (campo debería ser readonly, pero por si acaso)
            const valorActual = this.value;
            _limpiarCliente();
            this.value = valorActual;
            _validarDoc();
        } else if (state.esNuevoCliente && !this.value.trim()) {
            // DNI vaciado en modo nuevo-cliente → resetear para permitir buscar de nuevo
            _limpiarCliente();
        }
    });
    dniInput?.addEventListener('blur', () => {
        if (state.cliente) return;
        const dni = dniInput.value.trim();
        if (!dni) return;

        _mostrarBadgeCliente('searching', 'Buscando en registros...');
        const encontrado = _buscarPorDni(dni);
        if (encontrado) {
            _setClienteExistente(encontrado);
        } else if (!state.esNuevoCliente) {
            _setModoNuevoCliente();
        }
    });

    /* 7. Barra de búsqueda general (nombre, DNI, teléfono + RENIEC) */
    const searchInput = document.getElementById('searchCliente');
    const searchBtn   = document.getElementById('btnBuscar');

    const _setSearchFeedback = (msg, color = 'var(--text-muted)') => {
        const el = document.getElementById('searchFeedback');
        if (el) { el.textContent = msg; el.style.color = color; }
    };

    const _doSearch = async () => {
        const q = searchInput?.value?.trim();
        if (!q) { _ocultarDropdown(); return; }

        // 1. Coincidencia exacta por número de documento en caché local
        const exactoDni = _buscarPorDni(q);
        if (exactoDni) {
            _setClienteExistente(exactoDni);
            searchInput.value = '';
            _ocultarDropdown();
            _setSearchFeedback('');
            return;
        }

        // 2. DNI de 8 dígitos → consultar RENIEC vía proxy Decolecta
        if (/^\d{8}$/.test(q)) {
            _ocultarDropdown();
            _setSearchFeedback('Consultando RENIEC...', 'var(--text-muted)');
            try {
                const res = await clienteApi.reniecDni(q);
                const d   = res.data;
                _llenarCamposCliente({
                    contacto: `${d.nombres ?? ''} ${d.apellidos ?? ''}`.trim(),
                    tipoDoc:  'DNI',
                    dni:      q,
                    telefono: '',
                    correo:   '',
                });
                _setModoNuevoCliente();
                _mostrarBadgeCliente('new', 'Datos obtenidos del RENIEC — completa los campos restantes.');
                _setSearchFeedback('');
                searchInput.value = '';
            } catch {
                _setSearchFeedback('No se encontró el DNI en RENIEC.', 'var(--red-text, #e57373)');
                _llenarCamposCliente({ contacto: '', tipoDoc: 'DNI', dni: q, telefono: '', correo: '' });
                _setModoNuevoCliente();
                searchInput.value = '';
            }
            return;
        }

        // 3. Búsqueda general → dropdown
        _setSearchFeedback('');
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
    document.getElementById('btn-modal-paquete')?.addEventListener('click', () => _abrirModalPaquetes());

    /* 8b. Listener del input de estudiantes en el modal */
    document.getElementById('modalNumEstudiantes')?.addEventListener('input', () => {
        const numEst = _getModalNumEst();
        document.querySelectorAll('.paquete-option .po-qty-input').forEach(inp => {
            inp.min = 1;
            inp.max = '';
            if (numEst > 0) inp.value = numEst;
        });
        _actualizarBtnConfirmar();
    });

    /* 8c. Event delegation: recalcular total al escribir en cualquier qty del modal */
    document.getElementById('catPanelsContainer')?.addEventListener('input', e => {
        if (e.target.classList.contains('po-qty-input')) {
            _actualizarBtnConfirmar();
        }
    });

    /* 8d. Seleccionar todo el texto al hacer clic/focus en un qty input del modal */
    document.getElementById('catPanelsContainer')?.addEventListener('focusin', e => {
        if (e.target.classList.contains('po-qty-input')) e.target.select();
    });

    /* 9. Confirmar selección de paquetes (múltiple) */

    /** Reemplaza los paquetes en state.items con la selección actual del modal y cierra. */
    function _ejecutarAgregarPaquetes() {
        const numEst = _getModalNumEst();
        // Mantener ítems que no son paquetes (servicios, cortesías) y reemplazar los paquetes
        state.items = state.items.filter(i => i.tipo !== 'paquete');
        state.paquetesSeleccionados.forEach(({ idRef, nombre, precio, el }) => {
            const cantidad = Math.max(1, parseInt(el.querySelector('.po-qty-input')?.value) || 1);
            state.items.push({ tipo: idRef ? 'paquete' : 'personalizado', idRef: idRef ?? null, nombre, precio, cantidad, descuento: 0 });
        });
        const numEstEl = document.getElementById('numEstudiantes');
        if (numEstEl) numEstEl.value = numEst;
        _renderContainers();
        _actualizarResumen();
        _saveDraft();
        _modalPaquete?.hide();
    }

    document.getElementById('btn-confirmar-paquetes')?.addEventListener('click', () => {
        const numEst = _getModalNumEst();
        if (numEst <= 0) {
            alerts.warning('Ingresa la cantidad de estudiantes antes de agregar paquetes.');
            return;
        }
        if (state.paquetesSeleccionados.size === 0) {
            alerts.warning('Selecciona al menos un paquete de la lista.');
            return;
        }
        const totalQty = _calcularTotalModalQty();
        if (totalQty < numEst) {
            alerts.warning(`La cantidad total seleccionada (${totalQty}) no cubre a todos los estudiantes (${numEst}). Aumenta la cantidad o selecciona más paquetes.`);
            return;
        }

        _ejecutarAgregarPaquetes();
    });

    /* 10. Auto-guardar borrador en campos de sesión, colegio y promoción */
    ['notas', 'nombreColegio', 'nombreProm', 'seccionProm'].forEach(id =>
        document.getElementById(id)?.addEventListener('input', _saveDraft));
    document.getElementById('provinciaColegio')?.addEventListener('change', _saveDraft);
    document.getElementById('distritoColegio')?.addEventListener('change', _saveDraft);
    document.getElementById('gradoProm')?.addEventListener('change', _saveDraft);

    CAMPOS_CLIENTE_IDS.forEach(id =>
        document.getElementById(id)?.addEventListener('input', () => {
            if (state.esNuevoCliente) _saveDraft();
        })
    );

    /* 13a. Descuento global: solo enteros, no mayor al total bruto */
    document.getElementById('descuentoMonto')?.addEventListener('input', function () {
        let val = Math.floor(Math.abs(parseFloat(this.value) || 0));
        this.value = val > 0 ? val : '';
        state.descuentoMonto = val;
        _actualizarResumen();
        _saveDraft();
    });

    /* 13. Submit → validar y abrir modal de confirmación */
    document.getElementById('form-cotizacion')?.addEventListener('submit', e => {
        e.preventDefault();
        const error = _validar();
        if (error) { alerts.error(error); return; }
        _mostrarModalConfirmacion();
    });

    /* 14. Botón confirmar del modal → llamada real a la API */
    document.getElementById('btn-confirmar-cotizacion')?.addEventListener('click', async () => {
        const btnConf    = document.getElementById('btn-confirmar-cotizacion');
        const btnGuardar = document.querySelector('.btn-guardar');

        if (btnConf) {
            btnConf.disabled    = true;
            btnConf.innerHTML   = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Guardando...';
        }

        try {
            let idCliente;

            if (state.esNuevoCliente) {
                _mostrarBadgeCliente('searching', 'Registrando cliente...');
                const _dni = document.getElementById('dniCliente')?.value?.trim();
                const resCliente = await clienteApi.crear({
                    nombres:             document.getElementById('contactoCliente')?.value?.trim(),
                    apellidos:           null,
                    numero_documento:    _dni || null,
                    tipo_documento:      _dni ? (document.getElementById('tipoDocumento')?.value ?? 'DNI') : null,
                    telefono:            document.getElementById('telefonoCliente')?.value?.trim(),
                    correo:              document.getElementById('emailCliente')?.value?.trim() || null,
                    metodo_comunicacion: 'whatsapp',
                    acepta_promociones:  false,
                });
                idCliente = resCliente.id_cliente;
                _mostrarBadgeCliente('found', 'Cliente registrado correctamente.');
            } else {
                idCliente = state.cliente.id_cliente;
            }

            // Registrar segundo cliente nuevo si aplica
            if (state.esNuevoCliente2) {
                const contacto2  = document.getElementById('contactoCliente2')?.value?.trim();
                const telefono2  = document.getElementById('telefonoCliente2')?.value?.trim();
                const dni2       = document.getElementById('dniCliente2')?.value?.trim();
                if (!contacto2)  throw new Error('El nombre del 2.º responsable es obligatorio.');
                if (!telefono2)  throw new Error('El teléfono del 2.º responsable es obligatorio.');
                try {
                    const resC2 = await clienteApi.crear({
                        nombres:             contacto2,
                        apellidos:           null,
                        numero_documento:    dni2 || null,
                        tipo_documento:      dni2 ? (document.getElementById('tipoDocumento2')?.value ?? 'DNI') : null,
                        telefono:            telefono2,
                        correo:              document.getElementById('emailCliente2')?.value?.trim() || null,
                        metodo_comunicacion: 'whatsapp',
                        acepta_promociones:  false,
                    });
                    document.getElementById('idCliente2').value = resC2.id_cliente;
                } catch (e) {
                    throw new Error('Error al registrar el 2.º responsable: ' + (e.message || 'verifica los datos.'));
                }
            }

            const res = await cotizacionApi.crear(_buildPayload(idCliente));
            manager.limpiar();
            _modalConfirmacion?.hide();
            alerts.ok('Cotización creada correctamente.');

            setTimeout(() => {
                window.location.href = (window.BASE_URL || '/') + 'cotizaciones';
            }, 1100);

        } catch (err) {
            _modalConfirmacion?.hide();
            alerts.error(err.message || 'Error al crear la cotización.');
            if (btnConf) {
                btnConf.disabled  = false;
                btnConf.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirmar y guardar';
            }
            if (btnGuardar) {
                btnGuardar.disabled  = false;
                btnGuardar.innerHTML = '<i class="bi bi-check-circle me-2"></i>Guardar cotización';
            }
        }
    });

    /* Segundo cliente */
    _initSegundoClienteListeners();

    // ── Sección cortesías ─────────────────────────────────────────────────────
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

        if (!nombre) {
            alerts.warning('Escribe el nombre del producto de cortesía.');
            cortesiaInput?.focus();
            return;
        }

        state.items.push({ tipo: 'cortesia', idRef: null, nombre, precio: 0, cantidad: cant });
        _limpiarCortesiaForm();
        _renderContainers();
        _actualizarResumen();
        _saveDraft();
    };

    document.getElementById('btn-agregar-cortesia')?.addEventListener('click', () => {
        if (cortesiaWrap) cortesiaWrap.style.display = '';
        cortesiaInput?.focus();
    });

    document.getElementById('btn-cancelar-cortesia')?.addEventListener('click', _limpiarCortesiaForm);
    document.getElementById('btn-confirmar-cortesia')?.addEventListener('click', _confirmarCortesia);

    // Confirmar también con Enter en el input
    cortesiaInput?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); _confirmarCortesia(); }
    });

    // ── Sección servicios adicionales ─────────────────────────────────────────
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
        _saveDraft();
    };

    document.getElementById('btn-agregar-servicio')?.addEventListener('click', () => {
        if (servicioWrap) servicioWrap.style.display = '';
        servicioNombre?.focus();
    });
    document.getElementById('btn-cancelar-servicio')?.addEventListener('click', _limpiarServicioForm);
    document.getElementById('btn-confirmar-servicio')?.addEventListener('click', _confirmarServicio);

    servicioPrecio?.addEventListener('input', _actualizarSubtotalPreview);
    servicioCant?.addEventListener('input',   _actualizarSubtotalPreview);

    servicioNombre?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); servicioPrecio?.focus(); }
    });
    servicioPrecio?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); servicioCant?.focus(); }
    });
    servicioCant?.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); _confirmarServicio(); }
    });
}

init();
