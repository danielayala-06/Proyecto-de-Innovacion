/**
 * @file    paquete.state.js
 * @module  modules/paquetes/state
 *
 * Estado compartido y lógica pura del módulo de paquetes.
 * No realiza llamadas a la API ni manipula el DOM.
 *
 * Responsabilidades:
 *  - Mantener la colección completa y filtrada de paquetes.
 *  - Definir el orden y las etiquetas de niveles disponibles.
 *  - Inferir la categoría visual de un paquete desde su nombre o campo DB.
 *  - Calcular estadísticas de resumen (total, activos, precios).
 *  - Filtrar y ordenar paquetes según criterios de búsqueda.
 *  - Agrupar paquetes por `nivel_disponible` en un `Map` ordenado.
 */

// ─────────────────────────────────────────────────────────────────────────────
// ESTADO GLOBAL
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Estado mutable del módulo de paquetes.
 *
 * @type {{
 *   todos:     Array<Object>,
 *   filtrados: Array<Object>
 * }}
 * @property {Array<Object>} todos     - Todos los paquetes cargados desde la API.
 * @property {Array<Object>} filtrados - Subconjunto resultante de aplicar filtros.
 */
export const state = {
    todos:     [],
    filtrados: [],
};

// ─────────────────────────────────────────────────────────────────────────────
// CATEGORÍAS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Mapa de nombre de categoría → subcadena clave para inferencia desde el nombre del paquete.
 * Se recorre en orden de declaración; la primera coincidencia gana.
 *
 * @type {Object<string, string>}
 */
const CAT_KEYS = {
    'Quinceañeros': 'quinceañ',
    'Cuadros':      'cuadro',
    'Anuarios':     'anuario',
    'Matrimonios':  'matrimon',
    'Corporativo':  'corporat',
};

// ─────────────────────────────────────────────────────────────────────────────
// NIVELES
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Orden de visualización de los niveles disponibles.
 * Debe mantenerse sincronizado con el ENUM `paquetes.nivel_disponible` en la BD.
 *
 * @type {string[]}
 */
export const NIVEL_ORDER = ['inicial', 'primaria', 'secundaria', 'postgrado', 'otro'];

/**
 * Etiquetas de interfaz para cada valor del ENUM `nivel_disponible`.
 *
 * @type {Object<string, string>}
 */
export const NIVEL_LABEL = {
    'inicial':    'Inicial',
    'primaria':   'Primaria',
    'secundaria': 'Secundaria',
    'postgrado':  'Postgrado',
    'otro':       'Otro',
};

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS DE CATEGORÍA
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Infiere la categoría visual de un paquete a partir de su nombre.
 * Útil cuando el campo `categoria` de la BD no está disponible.
 *
 * @param {string} nombre - Nombre del paquete.
 * @returns {string} Categoría inferida, o `'Otro'` si no hay coincidencia.
 */
export function categoriaDesdNombre(nombre) {
    const n = (nombre || '').toLowerCase();
    for (const [cat, key] of Object.entries(CAT_KEYS)) {
        if (n.includes(key)) return cat;
    }
    return 'Otro';
}

/**
 * Resuelve la categoría de visualización de un paquete.
 * Prioriza los valores conocidos del campo `categoria` de la BD;
 * para los demás valores cae en inferencia por nombre.
 *
 * @param {Object} p              - Objeto paquete de la API.
 * @param {string} [p.categoria] - Campo `categoria` almacenado en la BD.
 * @param {string} p.nombre_paquete
 * @returns {string} Categoría normalizada para la UI.
 */
export function categoriaDesPaquete(p) {
    if (p.categoria === 'Cuadros')  return 'Cuadros';
    if (p.categoria === 'Anuarios') return 'Anuarios';
    if (p.categoria === 'Paquetes') return 'Paquetes';
    return categoriaDesdNombre(p.nombre_paquete);
}

// ─────────────────────────────────────────────────────────────────────────────
// ESTADÍSTICAS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Calcula estadísticas de resumen para un conjunto de paquetes.
 *
 * @param {Array<Object>} paquetes - Lista de paquetes a analizar.
 * @returns {{
 *   total:     number,
 *   activos:   number,
 *   inactivos: number,
 *   promedio:  number,
 *   maximo:    number
 * }} Objeto con las métricas calculadas.
 */
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

// ─────────────────────────────────────────────────────────────────────────────
// FILTRADO Y ORDENAMIENTO
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Filtra y ordena paquetes según los criterios indicados.
 *
 * Ordenamiento aplicado:
 *  1. Por `nivel_disponible` según {@link NIVEL_ORDER}.
 *  2. Dentro de cada nivel: ACTIVO antes que INACTIVO.
 *
 * @param {Array<Object>} todos                       - Lista completa de paquetes.
 * @param {Object}        [opts={}]                   - Criterios de filtrado.
 * @param {string}        [opts.busqueda='']          - Texto a buscar en `nombre_paquete`.
 * @param {string}        [opts.categoria='']         - Categoría exacta a filtrar.
 * @param {string}        [opts.nivelFiltro='']       - Valor de `nivel_disponible` a filtrar.
 * @returns {Array<Object>} Paquetes filtrados y ordenados.
 */
export function filtrar(todos, { busqueda = '', categoria = '', nivelFiltro = '' }) {
    const resultado = todos.filter(p => {
        const nombre = (p.nombre_paquete || '').toLowerCase();
        if (busqueda && !nombre.includes(busqueda.toLowerCase())) return false;
        if (categoria && categoriaDesPaquete(p) !== categoria) return false;
        if (nivelFiltro && (p.nivel_disponible ?? 'otro') !== nivelFiltro) return false;
        return true;
    });

    return resultado.sort((a, b) => {
        const nivelA = NIVEL_ORDER.indexOf(a.nivel_disponible ?? 'otro');
        const nivelB = NIVEL_ORDER.indexOf(b.nivel_disponible ?? 'otro');
        if (nivelA !== nivelB) return nivelA - nivelB;
        return (a.estado === 'ACTIVO' ? 0 : 1) - (b.estado === 'ACTIVO' ? 0 : 1);
    });
}

// ─────────────────────────────────────────────────────────────────────────────
// AGRUPACIÓN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Agrupa un array de paquetes por `nivel_disponible` en un `Map` ordenado
 * según {@link NIVEL_ORDER}. Los grupos vacíos son eliminados del resultado.
 *
 * @param {Array<Object>} paquetes - Lista de paquetes (ya filtrados/ordenados).
 * @returns {Map<string, Array<Object>>} Mapa nivel → lista de paquetes.
 */
export function agruparPorNivel(paquetes) {
    const grupos = new Map();
    for (const nivel of NIVEL_ORDER) grupos.set(nivel, []);
    for (const p of paquetes) {
        const nivel = p.nivel_disponible ?? 'otro';
        if (!grupos.has(nivel)) grupos.set(nivel, []);
        grupos.get(nivel).push(p);
    }
    for (const [k, v] of grupos) if (!v.length) grupos.delete(k);
    return grupos;
}
