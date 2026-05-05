/**
 * cotizacionesIndexManager.js
 * Estado centralizado y mutaciones del listado de cotizaciones. Sin acceso al DOM.
 * Consume window.COTIZACIONES_DATA inyectado por PHP (vista_cotizaciones_resumen).
 */

import { updateEstadoCotizacion } from '../../api/cotizacionesApi.js';

// ── Mapeo de campos PHP → JS ───────────────────────────────────────────────────
// Vista devuelve: id_cotizacion, cotizacion, cliente, telefono,
//                 fecha_evento, fecha_creado, total, estado
function _mapCotizacion(c) {
    return {
        id:         c.id_cotizacion,
        codigo:     'COT-' + String(c.id_cotizacion).padStart(3, '0'),
        cotizacion: c.cotizacion                   || '',
        cliente:    c.cliente                      || '',
        telefono:   c.telefono                     || '',
        tipoEvento: '',                               // no disponible en la vista actual
        fecha:      (c.fecha_evento  || '').slice(0, 10),
        total:      parseFloat(c.total)            || 0,
        estado:     (c.estado || 'pendiente').toLowerCase(),
        creado:     (c.fecha_creado  || '').slice(0, 10),
        items:      [],
        notas:      '',
    };
}

// ── Datos de fallback (demo) ───────────────────────────────────────────────────
const _DEMO = [
    { id:1,  codigo:'COT-001', cotizacion:'Matrimonio García', cliente:'Ana García',      telefono:'987654321', tipoEvento:'Matrimonio',  fecha:'2026-05-16', total:1200, estado:'aprobada',   creado:'2026-04-01', items:['Sesión completa','Álbum 30×40','Video resumen 3 min'], notas:'Ceremonia en Hacienda Las Flores.' },
    { id:2,  codigo:'COT-002', cotizacion:'Quinceañero Sofía', cliente:'Sofía Ríos',      telefono:'912456789', tipoEvento:'Quinceañero', fecha:'2026-05-22', total:850,  estado:'pendiente',  creado:'2026-04-03', items:['Paquete Premium','Toma con Dron'], notas:'' },
    { id:3,  codigo:'COT-003', cotizacion:'Retrato Luis',      cliente:'Luis Pérez',      telefono:'956321478', tipoEvento:'Retrato',     fecha:'2026-04-18', total:350,  estado:'completada', creado:'2026-03-20', items:['Sesión retrato','50 fotos editadas'], notas:'Incluye maquillaje.' },
    { id:4,  codigo:'COT-004', cotizacion:'Corp Mendoza',      cliente:'Carlos Mendoza',  telefono:'934567890', tipoEvento:'Corporativo', fecha:'2026-06-10', total:600,  estado:'pendiente',  creado:'2026-04-05', items:['Sesión corporativa 10 personas','30 fotos por persona'], notas:'' },
    { id:5,  codigo:'COT-005', cotizacion:'Quinceañero María', cliente:'María López',     telefono:'945678901', tipoEvento:'Quinceañero', fecha:'2026-07-05', total:550,  estado:'aprobada',   creado:'2026-04-08', items:['Paquete Plus','Video resumen'], notas:'' },
    { id:6,  codigo:'COT-006', cotizacion:'Boda Ramos',        cliente:'Pedro Ramos',     telefono:'967890123', tipoEvento:'Matrimonio',  fecha:'2026-08-20', total:1800, estado:'pendiente',  creado:'2026-04-10', items:['Paquete Full Matrimonio','Dron','2 fotógrafos'], notas:'Boda en la playa.' },
    { id:7,  codigo:'COT-007', cotizacion:'Retrato Ávila',     cliente:'Carmen Ávila',    telefono:'923456781', tipoEvento:'Retrato',     fecha:'2026-04-25', total:280,  estado:'rechazada',  creado:'2026-04-02', items:['Sesión retrato básica'], notas:'Cliente declinó por presupuesto.' },
    { id:8,  codigo:'COT-008', cotizacion:'Corp Vargas',       cliente:'Jorge Vargas',    telefono:'956789012', tipoEvento:'Corporativo', fecha:'2026-05-30', total:400,  estado:'aprobada',   creado:'2026-04-12', items:['Sesión corporativa 5 personas'], notas:'' },
    { id:9,  codigo:'COT-009', cotizacion:'Anuario UGEL',      cliente:'UGEL Ica',        telefono:'056234567', tipoEvento:'Otro',        fecha:'2026-09-15', total:1200, estado:'pendiente',  creado:'2026-04-14', items:['Anuario Premium','Sesión fotográfica colegios'], notas:'Para 3 instituciones.' },
    { id:10, codigo:'COT-010', cotizacion:'Quinceañero Paty',  cliente:'Patricia Flores', telefono:'978901234', tipoEvento:'Quinceañero', fecha:'2026-06-28', total:350,  estado:'pendiente',  creado:'2026-04-15', items:['Paquete Básico'], notas:'' },
    { id:11, codigo:'COT-011', cotizacion:'Boda Torres',       cliente:'Roberto Torres',  telefono:'912345670', tipoEvento:'Matrimonio',  fecha:'2026-10-04', total:1200, estado:'pendiente',  creado:'2026-04-16', items:['Paquete Clásico bodas'], notas:'' },
    { id:12, codigo:'COT-012', cotizacion:'Retrato Diana',     cliente:'Diana Castillo',  telefono:'934560123', tipoEvento:'Retrato',     fecha:'2026-05-03', total:200,  estado:'completada', creado:'2026-03-28', items:['Sesión rápida 1h','20 fotos'], notas:'' },
];

// ── Inicialización: usa datos reales si PHP los inyectó ────────────────────────
let _cotizaciones = (typeof COTIZACIONES_DATA !== 'undefined' && Array.isArray(COTIZACIONES_DATA))
    ? COTIZACIONES_DATA.map(_mapCotizacion)
    : [..._DEMO];

let _deleteId = null;
let _sortCol  = 'creado';
let _sortAsc  = false;
let _pagina   = 1;

export const POR_PAGINA = 8;

// ── Getters / setters ──────────────────────────────────────────────────────────
export const getCotizaciones = ()    => _cotizaciones;
export const getDeleteId     = ()    => _deleteId;
export const setDeleteId     = (id)  => { _deleteId = id; };
export const getSortCol      = ()    => _sortCol;
export const setSortCol      = (col) => { _sortCol = col; };
export const getSortAsc      = ()    => _sortAsc;
export const setSortAsc      = (v)   => { _sortAsc = v; };
export const getPagina       = ()    => _pagina;
export const setPagina       = (p)   => { _pagina = p; };

// ── Filtrado + ordenamiento ────────────────────────────────────────────────────
export function filtrar(q, est, tp) {
    const ql = (q || '').toLowerCase().trim();
    return _cotizaciones
        .filter(c =>
            (!ql  || `${c.cliente} ${c.codigo} ${c.cotizacion} ${c.tipoEvento}`.toLowerCase().includes(ql)) &&
            (!est || c.estado === est) &&
            (!tp  || c.tipoEvento === tp)
        )
        .sort((a, b) => {
            let va = a[_sortCol], vb = b[_sortCol];
            if (typeof va === 'string') { va = va.toLowerCase(); vb = vb.toLowerCase(); }
            return _sortAsc ? (va > vb ? 1 : -1) : (va < vb ? 1 : -1);
        });
}

// ── Utilidades puras ───────────────────────────────────────────────────────────
export function formatFecha(s) {
    if (!s) return '—';
    const [y, m, d] = s.split('-');
    const mes = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    return `${d} ${mes[+m - 1]} ${y}`;
}

// ── Mutaciones ─────────────────────────────────────────────────────────────────
function _actualizarEstadoLocal(id, estado) {
    const c = _cotizaciones.find(x => x.id === id);
    if (c) c.estado = estado.toLowerCase();
}

export async function cambiarEstadoCotizacion(id, estado) {
    const ok = await updateEstadoCotizacion(id, estado);
    if (ok) _actualizarEstadoLocal(id, estado);
    return ok;
}

export function eliminarCotizacion() {
    _cotizaciones = _cotizaciones.filter(x => x.id !== _deleteId);
    _deleteId = null;
}
