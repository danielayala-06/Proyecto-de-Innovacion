/**
 * @file    contrato.manager.js
 * @module  modules/contratos/manager
 *
 * Persistencia de preferencias del usuario del módulo de contratos en
 * `localStorage`. Almacena los filtros activos (búsqueda y estado) para
 * restaurarlos cuando el usuario regresa al listado.
 *
 * Las operaciones usan try/catch silencioso porque `localStorage` puede
 * no estar disponible (modo incógnito con cookies bloqueadas, cuota llena, etc.).
 */

/** @type {string} Clave de `localStorage` para los filtros del index. */
const FILTER_KEY = 'con_filtros_index';

/**
 * Gestiona la persistencia de filtros del módulo de contratos.
 *
 * @namespace manager
 */
export const manager = {
  /**
   * Serializa y guarda los filtros activos en `localStorage`.
   *
   * @param {{ search: string, estado: string }} data - Valores actuales de los filtros.
   * @returns {void}
   */
  guardarFiltros(data) {
    try { localStorage.setItem(FILTER_KEY, JSON.stringify(data)); } catch (_) {}
  },

  /**
   * Lee y deserializa los filtros guardados en `localStorage`.
   *
   * @returns {{ search: string, estado: string }|null} Filtros guardados, o `null` si no existen o hay error.
   */
  cargarFiltros() {
    try { return JSON.parse(localStorage.getItem(FILTER_KEY)); } catch (_) { return null; }
  },
};
