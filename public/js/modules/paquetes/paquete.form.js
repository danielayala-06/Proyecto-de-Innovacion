import { categoriaDesdNombre } from './paquete.state.js';

let _items = [];

const NIVEL_MAP = {
    'Quinceañeros': 'secundaria',
    'Cuadros':      'otro',
    'Anuarios':     'primaria',
    'Matrimonios':  'otro',
    'Corporativo':  'otro',
    'Otro':         'otro',
};

// Maps UI select value → DB ENUM value
const CAT_DB_MAP = {
    'Cuadros':  'Cuadros',
    'Anuarios': 'Anuarios',
};

// Maps DB ENUM → UI select value (only for known 1-to-1; 'otros' falls back to name inference)
function _uiCatFromPaquete(p) {
    if (p.categoria === 'Cuadros')  return 'Cuadros';
    if (p.categoria === 'Anuarios') return 'Anuarios';
    return categoriaDesdNombre(p.nombre_paquete);
}

const _set = (id, v) => { const el = document.getElementById(id); if (el) el.value = v ?? ''; };
const _get = (id)    => document.getElementById(id)?.value?.trim() ?? '';

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

export const form = {
    limpiar() {
        ['pId', 'pNombre', 'pDesc', 'pPrecio', 'pDuracion'].forEach(id => _set(id, ''));
        const cat = document.getElementById('pCategoria');
        if (cat) cat.value = 'Quinceañeros';
        const est = document.getElementById('pEstado');
        if (est) est.value = 'activo';
        _items = [];
        _renderItems();
    },

    poblar(p) {
        _set('pId',    p.id_paquete);
        _set('pNombre', p.nombre_paquete);
        _set('pPrecio', p.precio);
        _set('pEstado', (p.estado ?? 'ACTIVO').toLowerCase());

        const cat = document.getElementById('pCategoria');
        if (cat) cat.value = _uiCatFromPaquete(p);

        const lineas = (p.descripcion || '').split('\n').filter(Boolean);
        _set('pDesc', lineas[0] ?? '');
        _items = [...lineas.slice(1)];
        _renderItems();
    },

    validar() {
        if (!_get('pNombre'))         return 'El nombre del paquete es obligatorio.';
        const precio = parseFloat(_get('pPrecio'));
        if (!precio || precio <= 0)   return 'El precio debe ser mayor a 0.';
        return null;
    },

    datosCrear() {
        const cat    = document.getElementById('pCategoria')?.value ?? 'Otro';
        const desc   = _get('pDesc');
        const lineas = [desc, ..._items].filter(Boolean);
        return {
            nombre_paquete:   _get('pNombre'),
            nivel_disponible: NIVEL_MAP[cat] ?? 'otro',
            descripcion:      lineas.join('\n') || null,
            precio:           parseFloat(_get('pPrecio')),
            categoria:        CAT_DB_MAP[cat] ?? 'otros',
        };
    },

    datosActualizar() {
        const cat    = document.getElementById('pCategoria')?.value ?? 'Otro';
        const desc   = _get('pDesc');
        const lineas = [desc, ..._items].filter(Boolean);
        return {
            datos: {
                nombre_paquete:   _get('pNombre'),
                nivel_disponible: NIVEL_MAP[cat] ?? 'otro',
                descripcion:      lineas.join('\n') || null,
                precio:           parseFloat(_get('pPrecio')),
                categoria:        CAT_DB_MAP[cat] ?? 'otros',
            },
            estado: (_get('pEstado') || 'activo').toUpperCase(),
        };
    },

    agregarItem() {
        _items.push('');
        _renderItems();
        // Focus en el último input generado
        setTimeout(() => {
            const inputs = document.querySelectorAll('#itemsContainer input[type="text"]');
            inputs[inputs.length - 1]?.focus();
        }, 30);
    },
};

// Helpers llamados desde HTML generado dinámicamente
window.__paqUpdateItem = (i, v) => { _items[i] = v; };
window.__paqRemoveItem = (i)    => { _items.splice(i, 1); _renderItems(); };
