/**
 * clientesIndexManager.js
 * Estado centralizado del listado de clientes. Sin acceso al DOM.
 * Consume window.CLIENTES_DATA inyectado por PHP.
 */

function _mapCliente(c) {
    return {
        id:        c.id_cliente,
        nombre:    c.nombres            || '',
        apellido:  c.apellidos          || '',
        dni:       c.numero_documento   || '',
        telefono:  c.telefono           || '',
        correo:    c.persona_correo     || '',
        metodoCom: c.metodo_comunicacion || '',
        redSocial: c.red_social         || '',
        estado:    (c.estado            || 'ACTIVO').toUpperCase(),
    };
}

let _clientes = (typeof CLIENTES_DATA !== 'undefined' && Array.isArray(CLIENTES_DATA))
    ? CLIENTES_DATA.map(_mapCliente)
    : [];

let _deleteId = null;
let _sortCol  = 'nombre';
let _sortAsc  = true;
let _pagina   = 1;

export const POR_PAGINA = 10;

export const getClientes = ()    => _clientes;
export const getDeleteId = ()    => _deleteId;
export const setDeleteId = (id)  => { _deleteId = id; };
export const getSortCol  = ()    => _sortCol;
export const setSortCol  = (col) => { _sortCol = col; };
export const getSortAsc  = ()    => _sortAsc;
export const setSortAsc  = (v)   => { _sortAsc = v; };
export const getPagina   = ()    => _pagina;
export const setPagina   = (p)   => { _pagina = p; };

export function filtrar(q) {
    const ql = (q || '').toLowerCase().trim();
    return _clientes
        .filter(c =>
            !ql || `${c.nombre} ${c.apellido} ${c.dni} ${c.telefono} ${c.correo}`.toLowerCase().includes(ql)
        )
        .sort((a, b) => {
            let va = a[_sortCol] ?? '', vb = b[_sortCol] ?? '';
            if (typeof va === 'string') { va = va.toLowerCase(); vb = vb.toLowerCase(); }
            return _sortAsc ? (va > vb ? 1 : -1) : (va < vb ? 1 : -1);
        });
}

export function getClienteById(id) {
    return _clientes.find(c => c.id === id) || null;
}

export function eliminarClienteLocal() {
    _clientes = _clientes.filter(c => c.id !== _deleteId);
    _deleteId = null;
}
