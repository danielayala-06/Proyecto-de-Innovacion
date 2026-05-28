/**
 * @file    cotizacion.state.js
 * @module  modules/cotizaciones/state
 *
 * Estado global compartido del módulo de cotizaciones y función de cálculo
 * de resúmenes estadísticos (tarjetas superiores del index).
 *
 * El estado es un objeto mutable compartido por referencia entre todos los
 * módulos del dominio `cotizaciones`.
 */

/**
 * Estado global del módulo de cotizaciones.
 *
 * @type {Object}
 * @property {Array<Object>} filas     - Lista completa de cotizaciones recibida de la API.
 * @property {Array<Object>} filtradas - Lista tras aplicar búsqueda/filtro de estado.
 * @property {number}        pagina    - Página activa en la tabla (base 1).
 * @property {number}        porPagina - Número de filas por página.
 * @property {string|null}   sortKey   - Columna actualmente ordenada, o `null`.
 * @property {'asc'|'desc'}  sortDir   - Dirección del ordenamiento activo.
 */
export const state = {
  filas:             [],
  filtradas:         [],
  pagina:            1,
  porPagina:         10,
  sortKey:           null,
  sortDir:           'asc',
  mostrarArchivadas: false,
};

export const ESTADOS_ARCHIVADOS_COT = new Set(['RECHAZADA', 'EXPIRADA']);

/**
 * Calcula los totales para las tarjetas de estadísticas del index de cotizaciones.
 *
 * @param {Array<Object>} filas - Lista de cotizaciones a analizar.
 * @returns {{
 *   total: number,
 *   borrador: number,
 *   aprobadas: number,
 *   rechazadas: number,
 *   expiradas: number,
 *   monto_total: number
 * }} Resumen de conteos por estado y suma de totales estimados.
 */
const _ESTADO_ORDER_COT = { PENDIENTE: 0, APROBADA: 1 };

export function ordenarPorEstado(filas) {
  return [...filas].sort((a, b) => {
    const oa = _ESTADO_ORDER_COT[a.estado?.toUpperCase()] ?? 2;
    const ob = _ESTADO_ORDER_COT[b.estado?.toUpperCase()] ?? 2;
    return oa - ob;
  });
}

export function calcularResumenes(filas) {
  return filas.reduce(
    (acc, c) => {
      acc.total++;
      acc.monto_total += parseFloat(c.total) || 0;
      const e = c.estado?.toUpperCase();
      if (e === 'PENDIENTE')      acc.borrador++;
      else if (e === 'APROBADA')  acc.aprobadas++;
      else if (e === 'RECHAZADA') acc.rechazadas++;
      else if (e === 'EXPIRADA')  acc.expiradas++;
      return acc;
    },
    { total: 0, borrador: 0, aprobadas: 0, rechazadas: 0, expiradas: 0, monto_total: 0 }
  );
}
