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
    return {
        total,
        activos:  paquetes.filter(p => p.estado === 'ACTIVO').length,
        promedio: total ? precios.reduce((a, b) => a + b, 0) / total : 0,
        maximo:   total ? Math.max(...precios) : 0,
    };
}

export function filtrar(todos, { busqueda = '', categoria = '', estadoFiltro = '' }) {
    return todos.filter(p => {
        const nombre = (p.nombre_paquete || '').toLowerCase();

        if (busqueda && !nombre.includes(busqueda.toLowerCase())) return false;

        if (categoria) {
            if (categoriaDesPaquete(p) !== categoria) return false;
        }

        if (estadoFiltro && p.estado?.toLowerCase() !== estadoFiltro.toLowerCase()) return false;

        return true;
    });
}
