/**
 * calendarioManager.js
 * Estado, mutaciones y lógica de negocio del calendario.
 * No toca el DOM.
 */

export const MESES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                      'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
export const DIAS  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];

const _hoy = new Date();

// ── Estado privado ─────────────────────────────────────────────────
let _eventos  = [];
let _nextId   = 1;
let _curYear  = _hoy.getFullYear();
let _curMonth = _hoy.getMonth();
let _selDate  = null;
let _vista    = 'mes';
let _editId   = null;

// ── Getters ────────────────────────────────────────────────────────
export const getHoy      = ()  => _hoy;
export const getEventos  = ()  => [..._eventos];
export const getCurYear  = ()  => _curYear;
export const getCurMonth = ()  => _curMonth;
export const getSelDate  = ()  => _selDate;
export const getVista    = ()  => _vista;
export const getEditId   = ()  => _editId;

export function getEventoById(id) {
    return _eventos.find(e => e.id === id) ?? null;
}

export function getEventosMes() {
    return _eventos.filter(e => {
        const d = new Date(e.fecha + 'T00:00:00');
        return d.getFullYear() === _curYear && d.getMonth() === _curMonth;
    });
}

export function getEventosPendientes() {
    return _eventos.filter(e => e.estado === 'pendiente');
}

export function getLbl(tipo) {
    return { cotizacion: 'Cotización', contrato: 'Contrato', entrega: 'Entrega' }[tipo] ?? tipo;
}

// ── Setters / acciones ─────────────────────────────────────────────
export function setEventos(lista) {
    _eventos = lista;
    _nextId  = lista.length ? Math.max(...lista.map(e => e.id)) + 1 : 1;
}

export function setVista(v)    { _vista   = v; }
export function setSelDate(ds) { _selDate = ds; }
export function setEditId(id)  { _editId  = id; }

export function navegarMes(delta) {
    _curMonth += delta;
    if (_curMonth < 0)  { _curMonth = 11; _curYear--; }
    if (_curMonth > 11) { _curMonth = 0;  _curYear++; }
    _selDate = null;
}

export function irAHoy() {
    _curYear  = _hoy.getFullYear();
    _curMonth = _hoy.getMonth();
    _selDate  = null;
}

export function guardarEvento(datos) {
    if (datos.id) {
        const i = _eventos.findIndex(e => e.id === datos.id);
        if (i !== -1) _eventos[i] = { ..._eventos[i], ...datos };
    } else {
        _eventos.push({ ...datos, id: _nextId++ });
    }
}

export function eliminarEvento(id) {
    _eventos = _eventos.filter(e => e.id !== id);
}

// ── Datos demo ─────────────────────────────────────────────────────
// TODO: reemplazar con await fetchEventos() cuando el backend esté listo
const off = d => { const r = new Date(_hoy); r.setDate(r.getDate() + d); return r.toISOString().slice(0, 10); };

setEventos([
    { id:1,  titulo:'Cotización matrimonio García',  tipo:'cotizacion', estado:'pendiente',  fecha:off(1),  hora:'10:00', cliente:'Ana García',       monto:850  },
    { id:2,  titulo:'Firma contrato quinceañero',    tipo:'contrato',   estado:'pendiente',  fecha:off(2),  hora:'11:00', cliente:'Sofía Ríos',       monto:550  },
    { id:4,  titulo:'Entrega álbum promoción',       tipo:'entrega',    estado:'pendiente',  fecha:off(4),  hora:'16:00', cliente:'Colegio San José', monto:0    },
    { id:5,  titulo:'Cotización sesión corporativa', tipo:'cotizacion', estado:'confirmado', fecha:off(6),  hora:'14:00', cliente:'Empresa XYZ',      monto:400  },
    { id:6,  titulo:'Contrato fotos bebé',           tipo:'contrato',   estado:'confirmado', fecha:off(8),  hora:'10:30', cliente:'María López',      monto:300  },
    { id:8,  titulo:'Entrega fotos retrato',         tipo:'entrega',    estado:'completado', fecha:off(-5), hora:'17:00', cliente:'Carmen Ávila',     monto:0    },
    { id:9,  titulo:'Cotización anuario escolar',    tipo:'cotizacion', estado:'pendiente',  fecha:off(10), hora:'09:30', cliente:'UGEL Ica',         monto:1200 },
    { id:10, titulo:'Contrato dron matrimonio',      tipo:'contrato',   estado:'pendiente',  fecha:off(12), hora:'11:00', cliente:'Familia Rojas',    monto:700  },
]);
