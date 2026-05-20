/**
 * @file    paquete.form.js
 * @module  modules/paquetes/form
 *
 * Gestiona el formulario de creación/edición de paquetes (modal Bootstrap).
 *
 * Responsabilidades:
 *  - Limpiar y poblar los campos del formulario modal.
 *  - Mantener la lista de ítems incluidos (`_items`) en memoria.
 *  - Renderizar dinámicamente los inputs de ítems con botones de quitar.
 *  - Validar los campos requeridos antes de guardar.
 *  - Construir los payloads para `POST /api/paquetes` y `PUT /api/paquetes/:id`.
 *
 * Expone en `window.*` dos helpers invocados desde HTML generado dinámicamente:
 *  - `window.__paqUpdateItem(i, v)` — actualiza el ítem en posición `i`.
 *  - `window.__paqRemoveItem(i)`    — elimina el ítem en posición `i` y re-renderiza.
 */

import { categoriaDesdNombre } from './paquete.state.js';

// ─────────────────────────────────────────────────────────────────────────────
// ESTADO INTERNO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Lista de ítems incluidos en el paquete que se está editando/creando.
 * Cada elemento corresponde a una línea del campo `descripcion` (líneas 2+).
 *
 * @type {string[]}
 */
let _items = [];

/**
 * Reglas del paquete en edición (cargadas desde la API al abrir en modo edit).
 * En modo creación se mantiene vacío; las reglas nuevas van a `_reglasPendientes`.
 *
 * @type {Array<{id_regla:number, tipo_condicion:string, valor_condicion:number,
 *              tipo_beneficio:string, valor_beneficio:string, descripcion:string}>}
 */
let _reglas = [];

/**
 * Reglas añadidas durante la creación de un nuevo paquete.
 * Se envían a la API una a una después de que el paquete es creado.
 *
 * @type {Array<{tipo_condicion:string, valor_condicion:number,
 *              tipo_beneficio:string, valor_beneficio:string, descripcion:string}>}
 */
let _reglasPendientes = [];

/** true cuando el modal está en modo edición (paquete ya existe en BD). */
let _modoEdicion = false;

/** ID del paquete en edición; null si es creación. */
let _idPaqueteActual = null;

// ─────────────────────────────────────────────────────────────────────────────
// CONSTANTES
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Valores válidos para el campo `nivel_disponible`.
 * Deben coincidir con el ENUM de la BD.
 *
 * @type {string[]}
 */
const NIVELES_VALIDOS = ['inicial-primaria', 'secundaria', 'postgrado', 'otro'];

/**
 * Mapeo de valor de select UI → valor ENUM de la BD para `categoria`.
 * Solo cubre las categorías con correspondencia 1-a-1; los demás caen en `'otros'`.
 *
 * @type {Object<string, string>}
 */
const CAT_DB_MAP = {
    'Cuadros':  'Cuadros',
    'Anuarios': 'Anuarios',
    'Paquetes': 'Paquetes',
};

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS PRIVADOS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Resuelve el valor a mostrar en el select de categoría UI a partir del paquete de la BD.
 * Para categorías sin mapeo 1-a-1 infiere la categoría desde el nombre del paquete.
 *
 * @param {Object} p              - Objeto paquete de la API.
 * @param {string} [p.categoria] - Campo `categoria` de la BD.
 * @param {string} p.nombre_paquete
 * @returns {string} Valor a asignar al select `#pCategoria`.
 */
function _uiCatFromPaquete(p) {
    if (p.categoria === 'Cuadros')  return 'Cuadros';
    if (p.categoria === 'Anuarios') return 'Anuarios';
    return categoriaDesdNombre(p.nombre_paquete);
}

/** Asigna `v` al `value` del elemento con `id`. */
const _set = (id, v) => { const el = document.getElementById(id); if (el) el.value = v ?? ''; };

/** Devuelve el `value` recortado del elemento con `id`, o `''` si no existe. */
const _get = (id)    => document.getElementById(id)?.value?.trim() ?? '';

const TIPO_CONDICION_LABEL = { CANTIDAD_MIN: '≥ cantidad', CANTIDAD_MAX: 'máx. cantidad' };
const TIPO_BENEFICIO_LABEL  = { producto_gratis: 'Producto gratis', sesion_unica: 'Sesión extra', otro: 'Otro' };

/**
 * Re-renderiza el contenedor de reglas `#reglasContainer`.
 * Muestra tanto las reglas guardadas (con botón de borrar) como las pendientes.
 */
function _renderReglas() {
    const container = document.getElementById('reglasContainer');
    if (!container) return;

    const todasLasReglas = [
        ..._reglas.map(r => ({ ...r, guardada: true })),
        ..._reglasPendientes.map((r, i) => ({ ...r, guardada: false, _idx: i })),
    ];

    if (!todasLasReglas.length) {
        container.innerHTML = '<p class="text-muted" style="font-size:.8rem;margin:0;">Sin reglas definidas.</p>';
        return;
    }

    container.innerHTML = todasLasReglas.map(r => {
        const label    = `${TIPO_CONDICION_LABEL[r.tipo_condicion] ?? r.tipo_condicion} ${r.valor_condicion}`;
        const benefTipo = TIPO_BENEFICIO_LABEL[r.tipo_beneficio] ?? r.tipo_beneficio;
        const benefDetalle = r.tipo_beneficio === 'producto_gratis'
            ? (r.nombre_producto_beneficio ?? r.valor_beneficio ?? '—')
            : (r.valor_beneficio ?? '—');
        const eliminar = r.guardada
            ? `onclick="window.__paqEliminarRegla(${r.id_regla})"`
            : `onclick="window.__paqQuitarReglaPendiente(${r._idx})"`;
        return `
        <div class="d-flex align-items-start gap-2 mb-2" style="font-size:.8rem;">
            <span class="badge" style="background:var(--accent);color:#fff;white-space:nowrap;flex-shrink:0;">${label}</span>
            <span style="color:var(--text-secondary);flex:1;">${r.descripcion}</span>
            <span class="badge bg-secondary" style="white-space:nowrap;flex-shrink:0;">${benefTipo}: ${benefDetalle}</span>
            <button type="button" ${eliminar}
                    style="background:none;border:none;color:var(--red-text,#e57373);cursor:pointer;padding:0 2px;flex-shrink:0;">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>`;
    }).join('');
}

/**
 * Re-renderiza el contenedor de ítems `#itemsContainer` a partir del array `_items`.
 * Cada ítem produce un input de texto con botón de eliminar.
 *
 * @returns {void}
 */
function _renderItems() {
    const container = document.getElementById('itemsContainer');
    if (!container) return;

    if (!_items.length) { container.innerHTML = ''; return; }

    container.innerHTML = _items.map((item, i) => `
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-dot" style="color:var(--accent);flex-shrink:0;font-size:1.2rem;"></i>
            <input type="text" class="form-control form-control-sm"
                   value="${item.replace(/"/g, '&quot;')}"
                   placeholder="Ej: 2 con toga"
                   oninput="window.__paqUpdateItem(${i}, this.value)">
            <button type="button" onclick="window.__paqRemoveItem(${i})"
                    style="background:none;border:none;color:var(--red-text,#e57373);
                           cursor:pointer;padding:0 4px;font-size:.9rem;flex-shrink:0;">
                <i class="bi bi-x-circle"></i>
            </button>
        </div>`).join('');
}

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS PRIVADOS ADICIONALES
// ─────────────────────────────────────────────────────────────────────────────

/** Limpia el mini-formulario de agregar regla y restaura visibilidad. */
function _limpiarFormRegla() {
    ['rTipoCondicion', 'rValorCondicion', 'rTipoBeneficio', 'rValorBeneficio', 'rIdProductoBeneficio', 'rDescripcion']
        .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
    _toggleBeneficioUI('');
}

/**
 * Muestra el selector de producto o el input de texto según el tipo de beneficio.
 * @param {string} tipoBeneficio
 */
function _toggleBeneficioUI(tipoBeneficio) {
    const wrapText    = document.getElementById('wrapRValorBeneficio');
    const wrapSelect  = document.getElementById('wrapRProductoBeneficio');
    if (!wrapText || !wrapSelect) return;
    if (tipoBeneficio === 'producto_gratis') {
        wrapText.classList.add('d-none');
        wrapSelect.classList.remove('d-none');
    } else {
        wrapText.classList.remove('d-none');
        wrapSelect.classList.add('d-none');
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// API PÚBLICA
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Colección de operaciones del formulario de paquetes.
 *
 * @namespace form
 */
export const form = {

    /**
     * Limpia todos los campos del modal y reinicia la lista de ítems y reglas.
     * Deja el select de categoría en `'Quinceañeros'` y el de nivel vacío.
     *
     * @returns {void}
     */
    limpiar() {
        ['pId', 'pNombre', 'pDesc', 'pPrecio'].forEach(id => _set(id, ''));
        const cat = document.getElementById('pCategoria');
        if (cat) cat.value = 'Quinceañeros';
        const niv = document.getElementById('pNivel');
        if (niv) niv.value = '';
        _items = [];
        _reglas = [];
        _reglasPendientes = [];
        _modoEdicion = false;
        _idPaqueteActual = null;
        _renderItems();
        _renderReglas();
        _limpiarFormRegla();
    },

    /**
     * Pobla el formulario con los datos de un paquete existente para edición.
     * La primera línea de `descripcion` va al campo `#pDesc`;
     * las líneas siguientes se convierten en ítems individuales.
     *
     * @param {Object} p                    - Objeto paquete de la API.
     * @param {number} p.id_paquete
     * @param {string} p.nombre_paquete
     * @param {number|string} p.precio
     * @param {string} [p.categoria]
     * @param {string} [p.nivel_disponible]
     * @param {string} [p.descripcion]      - Líneas separadas por `\n`.
     * @returns {void}
     */
    poblar(p) {
        _set('pId',     p.id_paquete);
        _set('pNombre', p.nombre_paquete);
        _set('pPrecio', p.precio);

        const cat = document.getElementById('pCategoria');
        if (cat) cat.value = _uiCatFromPaquete(p);

        const niv = document.getElementById('pNivel');
        if (niv) niv.value = NIVELES_VALIDOS.includes(p.nivel_disponible) ? p.nivel_disponible : '';

        const lineas = (p.descripcion || '').split('\n').filter(Boolean);
        _set('pDesc', lineas[0] ?? '');
        _items = [...lineas.slice(1)];

        _reglas = Array.isArray(p.reglas) ? p.reglas : [];
        _reglasPendientes = [];
        _modoEdicion = true;
        _idPaqueteActual = p.id_paquete;

        _renderItems();
        _renderReglas();
        _limpiarFormRegla();
    },

    /**
     * Valida los campos requeridos del formulario.
     *
     * @returns {string|null} Mensaje de error si hay validación fallida, o `null` si es válido.
     */
    validar() {
        if (!_get('pNombre'))                          return 'El nombre del paquete es obligatorio.';
        const precio = parseFloat(_get('pPrecio'));
        if (!precio || precio <= 0)                    return 'El precio debe ser mayor a 0.';
        if (!NIVELES_VALIDOS.includes(_get('pNivel'))) return 'Selecciona el nivel disponible del paquete.';
        return null;
    },

    /**
     * Construye el payload para crear un nuevo paquete (`POST /api/paquetes`).
     * La `descripcion` se forma uniendo la descripción corta con los ítems,
     * separados por `\n`.
     *
     * @returns {{
     *   nombre_paquete:   string,
     *   nivel_disponible: string,
     *   descripcion:      string|null,
     *   precio:           number,
     *   categoria:        string
     * }}
     */
    datosCrear() {
        const cat    = document.getElementById('pCategoria')?.value ?? 'Otro';
        const desc   = _get('pDesc');
        const lineas = [desc, ..._items].filter(Boolean);
        return {
            nombre_paquete:   _get('pNombre'),
            nivel_disponible: _get('pNivel'),
            descripcion:      lineas.join('\n') || null,
            precio:           parseFloat(_get('pPrecio')),
            categoria:        CAT_DB_MAP[cat] ?? 'otros',
        };
    },

    /**
     * Construye el payload para actualizar un paquete existente (`PUT /api/paquetes/:id`).
     * Idéntico a {@link form.datosCrear} ya que ambas operaciones comparten los mismos campos.
     *
     * @returns {{
     *   nombre_paquete:   string,
     *   nivel_disponible: string,
     *   descripcion:      string|null,
     *   precio:           number,
     *   categoria:        string
     * }}
     */
    datosActualizar() {
        const cat    = document.getElementById('pCategoria')?.value ?? 'Otro';
        const desc   = _get('pDesc');
        const lineas = [desc, ..._items].filter(Boolean);
        return {
            nombre_paquete:   _get('pNombre'),
            nivel_disponible: _get('pNivel'),
            descripcion:      lineas.join('\n') || null,
            precio:           parseFloat(_get('pPrecio')),
            categoria:        CAT_DB_MAP[cat] ?? 'otros',
        };
    },

    /**
     * Agrega un ítem vacío a la lista y enfoca el nuevo input tras un breve retardo.
     *
     * @returns {void}
     */
    agregarItem() {
        _items.push('');
        _renderItems();
        setTimeout(() => {
            const inputs = document.querySelectorAll('#itemsContainer input[type="text"]');
            inputs[inputs.length - 1]?.focus();
        }, 30);
    },

    /**
     * Lee el mini-formulario de regla y agrega la regla a `_reglasPendientes`
     * (modo creación) o devuelve los datos para que el caller la envíe a la API
     * (modo edición). Retorna null si hay error de validación.
     *
     * @returns {{tipo_condicion:string, valor_condicion:number,
     *           tipo_beneficio:string, valor_beneficio:string,
     *           descripcion:string}|null}
     */
    leerReglaForm() {
        const tipo_condicion  = document.getElementById('rTipoCondicion')?.value  ?? '';
        const valor_condicion = parseFloat(document.getElementById('rValorCondicion')?.value ?? '');
        const tipo_beneficio  = document.getElementById('rTipoBeneficio')?.value  ?? '';
        const descripcion     = (document.getElementById('rDescripcion')?.value   ?? '').trim();

        if (!tipo_condicion || !tipo_beneficio) return null;
        if (!valor_condicion || valor_condicion <= 0) return null;
        if (!descripcion) return null;

        if (tipo_beneficio === 'producto_gratis') {
            const sel = document.getElementById('rIdProductoBeneficio');
            const id_producto_beneficio = parseInt(sel?.value ?? '0', 10);
            if (!id_producto_beneficio) return null;
            const nombre_producto_beneficio = sel?.options[sel.selectedIndex]?.text ?? '';
            return { tipo_condicion, valor_condicion, tipo_beneficio, id_producto_beneficio, nombre_producto_beneficio, descripcion };
        }

        const valor_beneficio = (document.getElementById('rValorBeneficio')?.value ?? '').trim();
        if (!valor_beneficio) return null;

        return { tipo_condicion, valor_condicion, tipo_beneficio, valor_beneficio, descripcion };
    },

    /**
     * Agrega una regla a la lista de pendientes (modo creación) y re-renderiza.
     */
    agregarReglaPendiente(regla) {
        _reglasPendientes.push(regla);
        _renderReglas();
        _limpiarFormRegla();
    },

    /**
     * Agrega una regla ya guardada a `_reglas` y re-renderiza (modo edición).
     */
    agregarReglaGuardada(regla) {
        _reglas.push(regla);
        _renderReglas();
        _limpiarFormRegla();
    },

    /** Devuelve las reglas pendientes de guardar (solo en modo creación). */
    getReglasPendientes() { return [..._reglasPendientes]; },

    /** Muestra u oculta el selector de producto según el tipo de beneficio. */
    toggleBeneficioUI: _toggleBeneficioUI,

    /** true si el modal está en modo edición. */
    get modoEdicion() { return _modoEdicion; },

    /** ID del paquete en edición. */
    get idPaqueteActual() { return _idPaqueteActual; },
};

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS GLOBALES (llamados desde HTML generado dinámicamente)
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Actualiza el texto del ítem en la posición indicada.
 * Invocado por el atributo `oninput` de los inputs generados por `_renderItems`.
 *
 * @param {number} i - Índice del ítem en `_items`.
 * @param {string} v - Nuevo valor.
 */
window.__paqUpdateItem = (i, v) => { _items[i] = v; };

/**
 * Elimina el ítem en la posición indicada y re-renderiza el contenedor.
 * Invocado por el `onclick` de los botones de quitar generados por `_renderItems`.
 *
 * @param {number} i - Índice del ítem a eliminar.
 */
window.__paqRemoveItem = (i) => { _items.splice(i, 1); _renderItems(); };

/**
 * Quita una regla pendiente (modo creación) por índice.
 * @param {number} i
 */
window.__paqQuitarReglaPendiente = (i) => { _reglasPendientes.splice(i, 1); _renderReglas(); };

/**
 * Quita una regla guardada del array local (la eliminación real vía API la maneja paquetesMain).
 * @param {number} idRegla
 */
window.__paqEliminarRegla = (idRegla) => {
    if (window.__paqEliminarReglaApi) window.__paqEliminarReglaApi(idRegla);
};
