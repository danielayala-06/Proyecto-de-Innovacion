/**
 * paquetesManager.js
 * Estado centralizado y mutaciones de paquetes. Sin acceso al DOM.
 */

const _DEMO = [
    { id:1,  nombre:'Paquete Quinceañero Básico',   categoria:'Quinceañeros', desc:'Ideal para quinceañeros con sesión profesional y fotos editadas.',   precio:350,  estado:'activo',   duracion:'4 horas',  items:['Sesión fotográfica completa','50 fotos editadas en alta resolución','Galería digital privada'] },
    { id:2,  nombre:'Paquete Quinceañero Plus',      categoria:'Quinceañeros', desc:'Incluye álbum físico y sesión extendida.',                            precio:550,  estado:'activo',   duracion:'6 horas',  items:['Sesión fotográfica completa','100 fotos editadas','Álbum 20×30 tapa dura','Galería digital privada','USB con todas las fotos'] },
    { id:3,  nombre:'Paquete Quinceañero Premium',   categoria:'Quinceañeros', desc:'El paquete más completo con video resumen.',                          precio:850,  estado:'activo',   duracion:'8 horas',  items:['Sesión fotográfica completa','150 fotos editadas','Álbum 20×30 tapa dura','Video resumen 3 min','Toma con dron','USB con todas las fotos'] },
    { id:4,  nombre:'Cuadro 30×40 cm',              categoria:'Cuadros',      desc:'Impresión en canvas de alta calidad.',                                precio:80,   estado:'activo',   duracion:'',         items:['Impresión en canvas','Marco de madera','1 foto a elección','Entrega en 7 días hábiles'] },
    { id:5,  nombre:'Cuadro 50×70 cm',              categoria:'Cuadros',      desc:'Formato grande para retratos y ambientes.',                           precio:130,  estado:'activo',   duracion:'',         items:['Impresión en canvas premium','Marco de madera robusto','1 foto a elección','Entrega en 7 días hábiles'] },
    { id:6,  nombre:'Pack Cuadros ×3',              categoria:'Cuadros',      desc:'Tres cuadros 20×30 a elección del cliente.',                         precio:200,  estado:'activo',   duracion:'',         items:['3 cuadros 20×30 cm','Marcos de madera','Fotos a elección','Entrega en 10 días hábiles'] },
    { id:7,  nombre:'Anuario Básico',               categoria:'Anuarios',     desc:'Para instituciones educativas pequeñas.',                             precio:120,  estado:'activo',   duracion:'',         items:['20 páginas','Tapa blanda plastificada','30 fotos editadas','Diseño personalizado'] },
    { id:8,  nombre:'Anuario Estándar',             categoria:'Anuarios',     desc:'El más solicitado por colegios de la región.',                        precio:200,  estado:'activo',   duracion:'',         items:['40 páginas','Tapa dura','60 fotos editadas','Diseño personalizado','Sesión fotográfica incluida'] },
    { id:9,  nombre:'Anuario Premium',              categoria:'Anuarios',     desc:'Fotos ilimitadas y USB incluido.',                                    precio:320,  estado:'activo',   duracion:'',         items:['60 páginas','Tapa dura','Fotos ilimitadas editadas','Diseño personalizado','Sesión fotográfica','USB con fotos en alta resolución'] },
    { id:10, nombre:'Paquete Matrimonio Clásico',   categoria:'Matrimonios',  desc:'Cobertura completa del día de bodas.',                               precio:1200, estado:'activo',   duracion:'10 horas', items:['Cobertura completa ceremonia y recepción','200 fotos editadas','Álbum de bodas 30×40','Galería digital privada'] },
    { id:11, nombre:'Paquete Matrimonio Full',      categoria:'Matrimonios',  desc:'Con dron y video resumen.',                                           precio:1800, estado:'activo',   duracion:'12 horas', items:['Cobertura completa','300 fotos editadas','Álbum de bodas 30×40','Video resumen 5 min','Toma con dron','2 fotógrafos','USB con fotos'] },
    { id:12, nombre:'Sesión Corporativa',           categoria:'Corporativo',  desc:'Fotos profesionales para empresas y equipos.',                        precio:400,  estado:'inactivo', duracion:'3 horas',  items:['Hasta 10 personas','30 fotos editadas por persona','Fondo neutro o locación','Entrega en 5 días hábiles'] },
];

let _paquetes = [..._DEMO];
let _nextId   = 13;
let _editId   = null;
let _deleteId = null;

// ── Utilidades puras ───────────────────────────────────────────────────────────
export const catClass = (c) =>
    ({ Quinceañeros:'quinceaneros', Cuadros:'cuadros', Anuarios:'anuarios',
       Matrimonios:'matrimonios', Corporativo:'corporativo' }[c] || 'otro');

// ── Getters / setters de estado ────────────────────────────────────────────────
export const getPaquetes  = ()     => _paquetes;
export const getEditId    = ()     => _editId;
export const setEditId    = (id)   => { _editId   = id; };
export const getDeleteId  = ()     => _deleteId;
export const setDeleteId  = (id)   => { _deleteId = id; };

// ── Filtrado ───────────────────────────────────────────────────────────────────
export function filtrarPaquetes(q, cat, est) {
    const ql = (q || '').toLowerCase().trim();
    return _paquetes.filter(p =>
        (!ql  || p.nombre.toLowerCase().includes(ql) || p.desc.toLowerCase().includes(ql)) &&
        (!cat || p.categoria === cat) &&
        (!est || p.estado === est)
    );
}

// ── Stats ──────────────────────────────────────────────────────────────────────
export function getStats() {
    const precios = _paquetes.map(p => p.precio);
    return {
        total:   _paquetes.length,
        activos: _paquetes.filter(p => p.estado === 'activo').length,
        prom:    precios.length ? precios.reduce((a, b) => a + b, 0) / precios.length : 0,
        max:     precios.length ? Math.max(...precios) : 0,
    };
}

// ── Mutaciones ─────────────────────────────────────────────────────────────────
export function guardarPaquete(datos) {
    if (datos.id) {
        const idx = _paquetes.findIndex(x => x.id === datos.id);
        if (idx !== -1) _paquetes[idx] = datos;
    } else {
        _paquetes.push({ ...datos, id: _nextId++ });
    }
}

export function toggleEstadoPaquete(id) {
    const p = _paquetes.find(x => x.id === id);
    if (p) p.estado = p.estado === 'activo' ? 'inactivo' : 'activo';
}

export function eliminarPaquete() {
    _paquetes = _paquetes.filter(x => x.id !== _deleteId);
    _deleteId = null;
}
