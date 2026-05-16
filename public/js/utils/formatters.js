/**
 * @file    formatters.js
 * @module  utils/formatters
 *
 * Funciones de formato para presentación de datos en la interfaz:
 * moneda, fechas, estados de entidades y códigos correlativos.
 *
 * @example
 * import { formatters } from '../utils/formatters.js';
 * formatters.moneda(1250);        // → 'S/ 1250.00'
 * formatters.fecha('2025-01-15'); // → '15 ene 2025'
 * formatters.estado('APROBADA'); // → 'Aprobada'
 * formatters.codigo(7);          // → '#0007'
 */

/** @type {string[]} Nombres cortos de los meses en español. */
const MESES = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];

/**
 * Colección de funciones de formato para la UI.
 *
 * @namespace formatters
 */
export const formatters = {
  /**
   * Formatea un número como moneda con símbolo y dos decimales.
   *
   * @param {number|string} valor  - Valor numérico a formatear.
   * @param {'PEN'|'USD'|'EUR'} [moneda='PEN'] - Código de moneda ISO 4217.
   * @returns {string} Valor formateado, p.ej. `'S/ 1 250.00'`.
   */
  moneda(valor, moneda = 'PEN') {
    const simbolos = { PEN: 'S/', USD: '$', EUR: '€' };
    const s = simbolos[moneda] || moneda;
    return `${s} ${parseFloat(valor || 0).toFixed(2)}`;
  },

  /**
   * Formatea una cadena de fecha ISO en formato legible (`dd mmm aaaa`).
   * Maneja fechas con y sin componente de hora (`T`). Si el valor es nulo,
   * vacío o no parseable, retorna `'—'` o el valor original respectivamente.
   *
   * @param {string|null} str - Fecha en formato ISO 8601 (`YYYY-MM-DD` o `YYYY-MM-DDTHH:mm:ss`).
   * @returns {string} Fecha formateada, p.ej. `'15 ene 2025'`, o `'—'` si es nula.
   */
  fecha(str) {
    if (!str) return '—';
    const d = new Date(str + (str.includes('T') ? '' : 'T00:00:00'));
    if (isNaN(d)) return str;
    return `${String(d.getDate()).padStart(2,'0')} ${MESES[d.getMonth()]} ${d.getFullYear()}`;
  },

  /**
   * Convierte un estado de base de datos en su etiqueta legible en español.
   * Soporta los estados de cotizaciones. Si el estado no está mapeado,
   * retorna el valor original en mayúsculas o `'—'` si es nulo.
   *
   * @param {string|null} e - Estado de la cotización (insensible a mayúsculas).
   * @returns {string} Etiqueta legible.
   */
  estado(e) {
    const map = {
      PENDIENTE:         'Pendiente',
      APROBADA:          'Aprobada',
      RECHAZADA:         'Rechazada',
      EXPIRADA:          'Expirada',
      CONTRATO_GENERADO: 'Contrato generado',
    };
    return map[e?.toUpperCase()] ?? (e || '—');
  },

  /**
   * Formatea un ID numérico como código correlativo de 4 dígitos con prefijo `#`.
   *
   * @param {number} id - ID de la entidad.
   * @returns {string} Código formateado, p.ej. `'#0007'`.
   */
  codigo(id) {
    return '#' + String(id).padStart(4, '0');
  },
};
