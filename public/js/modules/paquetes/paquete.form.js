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
const NIVELES_VALIDOS = ['inicial', 'primaria', 'secundaria', 'postgrado', 'otro'];

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
        _modoEdicion     = false;
        _idPaqueteActual = null;
        _renderItems();
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

        _modoEdicion     = true;
        _idPaqueteActual = p.id_paquete;

        _renderItems();
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

