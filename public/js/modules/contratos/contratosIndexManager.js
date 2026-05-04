/**
 * contratosIndexManager.js
 * Estado centralizado del listado de contratos. Sin acceso al DOM.
 * Consume window.CONTRATOS_DATA inyectado por PHP.
 */

function _mapContrato(c) {
    const estadoMap = { activo: 'vigente', completado: 'completado', cancelado: 'cancelado' };
    const estadoRaw = (c.estado || 'activo').toLowerCase();
    return {
        id:               c.id_contrato,
        codigo:           c.codigo || 'CON-' + String(c.id_contrato).padStart(3, '0'),
        cotizacionCod:    c.cotizacion_cod || ('COT-' + String(c.id_cotizacion).padStart(3, '0')),
        cotizacionNombre: c.nombre_cotizacion || '',
        cliente:          c.cliente   || '',
        telefono:         c.telefono  || '',
        tipoEvento:       '',
        fechaEvento:      (c.fecha_inicio || '').slice(0, 10),
        total:            parseFloat(c.total_final) || 0,
        adelanto:         parseFloat(c.adelanto)    || 0,
        estado:           estadoMap[estadoRaw] || estadoRaw,
        creado:           (c.fecha_contrato || '').slice(0, 10),
        observaciones:    c.observaciones || '',
    };
}

let _contratos = (typeof CONTRATOS_DATA !== 'undefined' && Array.isArray(CONTRATOS_DATA))
    ? CONTRATOS_DATA.map(_mapContrato)
    : [];

let _deleteId = null;
let _sortCol  = 'creado';
let _sortAsc  = false;
let _pagina   = 1;

export const POR_PAGINA = 8;

export const getContratos  = ()    => _contratos;
export const getDeleteId   = ()    => _deleteId;
export const setDeleteId   = (id)  => { _deleteId = id; };
export const getSortCol    = ()    => _sortCol;
export const setSortCol    = (col) => { _sortCol = col; };
export const getSortAsc    = ()    => _sortAsc;
export const setSortAsc    = (v)   => { _sortAsc = v; };
export const getPagina     = ()    => _pagina;
export const setPagina     = (p)   => { _pagina = p; };

export function filtrar(q, estado) {
    const ql = (q || '').toLowerCase().trim();
    return _contratos
        .filter(c =>
            (!ql     || `${c.cliente} ${c.codigo} ${c.cotizacionCod} ${c.cotizacionNombre}`.toLowerCase().includes(ql)) &&
            (!estado || c.estado === estado)
        )
        .sort((a, b) => {
            let va = a[_sortCol] ?? '', vb = b[_sortCol] ?? '';
            if (typeof va === 'string') { va = va.toLowerCase(); vb = vb.toLowerCase(); }
            return _sortAsc ? (va > vb ? 1 : -1) : (va < vb ? 1 : -1);
        });
}

export function formatFecha(s) {
    if (!s) return '—';
    const [y, m, d] = s.split('-');
    const mes = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    return `${d} ${mes[+m - 1]} ${y}`;
}

export function getContratoById(id) {
    return _contratos.find(c => c.id === id) || null;
}

export function agregarContrato(c) {
    _contratos.unshift(_mapContrato(c));
}

export function actualizarEstadoContrato(id, estado) {
    const c = _contratos.find(x => x.id === id);
    if (c) c.estado = estado;
}

export function eliminarContratoLocal() {
    _contratos = _contratos.filter(c => c.id !== _deleteId);
    _deleteId  = null;
}
