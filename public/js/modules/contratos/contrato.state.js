/**
 * @file    contrato.state.js
 * @module  modules/contratos/state
 *
 * Estado global compartido del módulo de contratos y función de cálculo
 * de estadísticas de resumen (tarjetas superiores del index).
 *
 * El estado es un objeto mutable importado por referencia; todos los módulos
 * del dominio `contratos` comparten la misma instancia.
 */

/**
 * Estado global del módulo de contratos.
 *
 * @type {Object}
 * @property {Array<Object>}  filas                    - Lista completa de contratos recibida de la API.
 * @property {Array<Object>}  filtradas                - Lista tras aplicar búsqueda/filtro de estado.
 * @property {number}         pagina                   - Página activa en la tabla (base 1).
 * @property {number}         porPagina                - Número de filas por página.
 * @property {string|null}    sortKey                  - Columna actualmente ordenada, o `null`.
 * @property {'asc'|'desc'}   sortDir                  - Dirección del ordenamiento activo.
 * @property {Array<Object>}  todasCotizaciones        - Cotizaciones aprobadas sin contrato (caché para el modal 1).
 * @property {Object|null}    cotizacionSeleccionada   - Cotización elegida en el modal de selección.
 */
export const state = {
  filas:     [],
  filtradas: [],
  pagina:    1,
  porPagina: 10,
  sortKey:   null,
  sortDir:   'asc',

  todasCotizaciones:      [],
  cotizacionSeleccionada: null,
};

/**
 * Calcula los totales para las tarjetas de estadísticas del index de contratos.
 *
 * @param {Array<Object>} filas - Lista de contratos a analizar.
 * @returns {{ total: number, vigentes: number, completados: number, monto_total: number }}
 *   Resumen con conteos por estado y monto total de contratos.
 */
export function calcularStats(filas) {
  return filas.reduce(
    (acc, c) => {
      acc.total++;
      acc.monto_total += parseFloat(c.total) || 0;
      const e = c.estado?.toUpperCase();
      if (e === 'ACTIVO')     acc.vigentes++;
      if (e === 'COMPLETADO') acc.completados++;
      return acc;
    },
    { total: 0, vigentes: 0, completados: 0, monto_total: 0 }
  );
}
