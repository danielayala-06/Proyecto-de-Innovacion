export const state = {
    todos:     [],
    filtrados: [],
};

const CAT_KEYS = {
    'Quinceañeros': 'quinceañ',
    'Cuadros':      'cuadro',
    'Anuarios':     'anuario',
    'Matrimonios':  'matrimon',
    'Corporativo':  'corporat',
};

export const NIVEL_ORDER = ['inicial-primaria', 'secundaria', 'postgrado', 'otro'];

export const NIVEL_LABEL = {
    'inicial-primaria': 'Inicial / Primaria',
    'secundaria':       'Secundaria',
    'postgrado':        'Postgrado',
    'otro':             'Otro',
};

/** Infiere la categoría visual desde el nombre del paquete */
export function categoriaDesdNombre(nombre) {
    const n = (nombre || '').toLowerCase();
    for (const [cat, key] of Object.entries(CAT_KEYS)) {
        if (n.includes(key)) return cat;
    }
    return 'Otro';
}

/** Resuelve la categoría de display desde el paquete; usa campo DB cuando está disponible */
export function categoriaDesPaquete(p) {
    if (p.categoria === 'Cuadros')  return 'Cuadros';
    if (p.categoria === 'Anuarios') return 'Anuarios';
    if (p.categoria === 'Paquetes') return 'Paquetes';
    return categoriaDesdNombre(p.nombre_paquete);
}

export function calcularStats(paquetes) {
    const precios = paquetes.map(p => parseFloat(p.precio) || 0);
    const total   = paquetes.length;
    const activos = paquetes.filter(p => p.estado === 'ACTIVO').length;
    return {
        total,
        activos,
        inactivos: total - activos,
        promedio:  total ? precios.reduce((a, b) => a + b, 0) / total : 0,
        maximo:    total ? Math.max(...precios) : 0,
    };
}

/**
 * Filtra por búsqueda, categoría y nivel.
 * Devuelve los paquetes agrupados por nivel_disponible (en orden definido),
 * con los inactivos siempre al final dentro de cada grupo.
 */
export function filtrar(todos, { busqueda = '', categoria = '', nivelFiltro = '' }) {
    const resultado = todos.filter(p => {
        const nombre = (p.nombre_paquete || '').toLowerCase();
        if (busqueda && !nombre.includes(busqueda.toLowerCase())) return false;
        if (categoria && categoriaDesPaquete(p) !== categoria) return false;
        if (nivelFiltro && (p.nivel_disponible ?? 'otro') !== nivelFiltro) return false;
        return true;
    });

    // Ordenar: primero por nivel (según NIVEL_ORDER), luego activos antes de inactivos
    return resultado.sort((a, b) => {
        const nivelA = NIVEL_ORDER.indexOf(a.nivel_disponible ?? 'otro');
        const nivelB = NIVEL_ORDER.indexOf(b.nivel_disponible ?? 'otro');
        if (nivelA !== nivelB) return nivelA - nivelB;
        return (a.estado === 'ACTIVO' ? 0 : 1) - (b.estado === 'ACTIVO' ? 0 : 1);
    });
}

/**
 * Agrupa un array ya filtrado/ordenado por nivel_disponible.
 * Devuelve Map<nivel, paquete[]> respetando NIVEL_ORDER.
 */
export function agruparPorNivel(paquetes) {
    const grupos = new Map();
    for (const nivel of NIVEL_ORDER) grupos.set(nivel, []);
    for (const p of paquetes) {
        const nivel = p.nivel_disponible ?? 'otro';
        if (!grupos.has(nivel)) grupos.set(nivel, []);
        grupos.get(nivel).push(p);
    }
    // Eliminar grupos vacíos
    for (const [k, v] of grupos) if (!v.length) grupos.delete(k);
    return grupos;
}
