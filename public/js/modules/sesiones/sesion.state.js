/**
 * @file    sesion.state.js
 * @module  modules/sesiones/state
 *
 * Estado compartido y constantes del módulo de sesiones fotográficas.
 * No realiza llamadas a la API ni manipula el DOM.
 *
 * El módulo de sesiones funciona en el contexto de un contrato específico;
 * los datos se organizan por promoción (pestaña activa), con carga bajo demanda.
 */

// ─────────────────────────────────────────────────────────────────────────────
// ESTADO GLOBAL
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Estado mutable del módulo de sesiones.
 *
 * @type {{
 *   idContrato:    number|null,
 *   promociones:   Array<Object>,
 *   sesiones:      Object,
 *   estudiantes:   Object,
 *   limites:       Object,
 *   sesionActiva:  Object|null
 * }}
 * @property {number|null}   idContrato   - ID del contrato activo (inyectado desde PHP como `ID_CONTRATO`).
 * @property {Array<Object>} promociones  - Lista de promociones del contrato (inyectadas como `PROMOCIONES`).
 * @property {Object}        sesiones     - Map: `id_promocion` → `sesion[]`; cargado bajo demanda.
 * @property {Object}        estudiantes  - Map: `id_promocion` → `estudiante[]`; cargado bajo demanda.
 * @property {Object}        limites      - Map: `id_promocion` → `{tipo: {permitidas, usadas, puede_crear}}`
 * @property {Object|null}   sesionActiva - Sesión completa con asistencia para el offcanvas activo.
 */
export const state = {
    idContrato:    null,
    promociones:   [],
    sesiones:      {},
    estudiantes:   {},
    limites:       {},
    sesionActiva:  null,
};

// ─────────────────────────────────────────────────────────────────────────────
// ETIQUETAS E ICONOS DE TIPO DE SESIÓN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Etiquetas de interfaz para cada tipo de sesión.
 * Valores coinciden con el campo `tipo` de la tabla `sesiones`.
 *
 * @type {Object<string, string>}
 */
export const TIPO_LABEL = {
    exteriores: 'Exteriores',
    colegio:    'Colegio',
    estudio:    'Estudio',
    otro:       'Otro',
};

/**
 * Clases de icono Bootstrap Icons para cada tipo de sesión.
 *
 * @type {Object<string, string>}
 */
export const TIPO_ICON = {
    exteriores: 'bi-tree',
    colegio:    'bi-building',
    estudio:    'bi-camera',
    otro:       'bi-geo-alt',
};

// ─────────────────────────────────────────────────────────────────────────────
// ETIQUETAS Y CLASES DE ESTADO DE SESIÓN
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Etiquetas de interfaz para cada estado de sesión.
 * Valores coinciden con el campo `estado` de la tabla `sesiones`.
 *
 * @type {Object<string, string>}
 */
export const ESTADO_LABEL = {
    pendiente:  'Pendiente',
    finalizado: 'Finalizado',
    cancelado:  'Cancelado',
};

/**
 * Clases CSS del badge de estado para cada valor de `estado`.
 *
 * @type {Object<string, string>}
 */
export const ESTADO_CLASS = {
    pendiente:  'badge-pendiente',
    finalizado: 'badge-aprobada',
    cancelado:  'badge-rechazada',
};

// ─────────────────────────────────────────────────────────────────────────────
// ESTADÍSTICAS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Calcula un resumen de estados para las sesiones de una promoción.
 *
 * @param {Array<Object>} sesiones - Lista de sesiones de una promoción.
 * @returns {{
 *   total:       number,
 *   pendientes:  number,
 *   finalizadas: number
 * }}
 */
export function calcularStatsPromocion(sesiones) {
    return {
        total:       sesiones.length,
        pendientes:  sesiones.filter(s => s.estado === 'pendiente').length,
        finalizadas: sesiones.filter(s => s.estado === 'finalizado').length,
    };
}
