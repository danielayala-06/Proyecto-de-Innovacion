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
            const key = CAT_KEYS[categoria];
            if (key) {
                if (!nombre.includes(key)) return false;
            } else {
                // "Otro" → solo los que no encajan en ninguna categoría conocida
                if (categoriaDesdNombre(p.nombre_paquete) !== 'Otro') return false;
            }
        }

        if (estadoFiltro && p.estado?.toLowerCase() !== estadoFiltro.toLowerCase()) return false;

        return true;
    });
}
