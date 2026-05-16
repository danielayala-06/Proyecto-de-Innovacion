/**
 * @file    cotizacion.manager.js
 * @module  modules/cotizaciones/manager
 *
 * Persistencia de preferencias del usuario del módulo de cotizaciones en
 * `localStorage`. Gestiona dos tipos de datos:
 *
 *  - **Borrador del formulario**: datos ingresados en la vista de creación de
 *    cotización, para restaurarlos si el usuario recarga o regresa a la página.
 *  - **Filtros del index**: búsqueda y filtro de estado activos en el listado,
 *    para restaurarlos al regresar al index.
 *
 * Todas las operaciones usan try/catch silencioso porque `localStorage` puede
 * no estar disponible (modo incógnito con cookies bloqueadas, cuota llena, etc.).
 */

/** @type {string} Clave de `localStorage` para el borrador del formulario crear. */
const DRAFT_KEY  = 'cot_borrador_crear';
/** @type {string} Clave de `localStorage` para los filtros del index. */
const FILTER_KEY = 'cot_filtros_index';

/**
 * Gestiona la persistencia de borradores y filtros del módulo de cotizaciones.
 *
 * @namespace manager
 */
export const manager = {
  // ─── Borrador del formulario crear ─────────────────────────────────────────

  /**
   * Guarda el borrador del formulario de creación en `localStorage`.
   *
   * @param {Object} data - Estado del formulario a persistir.
   * @returns {void}
   */
  guardar(data) {
    try { localStorage.setItem(DRAFT_KEY, JSON.stringify(data)); } catch (_) {}
  },

  /**
   * Lee y deserializa el borrador guardado.
   *
   * @returns {Object|null} Datos del borrador, o `null` si no existe o hay error.
   */
  cargar() {
    try { return JSON.parse(localStorage.getItem(DRAFT_KEY)); } catch (_) { return null; }
  },

  /**
   * Elimina el borrador del `localStorage` (se llama tras guardar exitosamente).
   *
   * @returns {void}
   */
  limpiar() {
    localStorage.removeItem(DRAFT_KEY);
  },

  // ─── Preferencias de filtro del index ──────────────────────────────────────

  /**
   * Guarda los filtros activos del index en `localStorage`.
   *
   * @param {{ search: string, estado: string }} data - Valores actuales de los filtros.
   * @returns {void}
   */
  guardarFiltros(data) {
    try { localStorage.setItem(FILTER_KEY, JSON.stringify(data)); } catch (_) {}
  },

  /**
   * Lee y deserializa los filtros guardados del index.
   *
   * @returns {{ search: string, estado: string }|null} Filtros guardados, o `null`.
   */
  cargarFiltros() {
    try { return JSON.parse(localStorage.getItem(FILTER_KEY)); } catch (_) { return null; }
  },
};
