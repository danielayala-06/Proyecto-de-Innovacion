/**
 * @file    promocion.api.js
 * @module  api/promocionApi
 *
 * Capa de acceso a la API REST para el recurso Promociones Escolares.
 * Las promociones son de solo lectura desde el frontend; se crean
 * automáticamente al crear una cotización.
 *
 * Endpoints consumidos:
 *  - GET api/promociones        → listar promociones (soporta ?id_cotizacion, ?colegio, etc.)
 *  - GET api/promociones/{id}   → detalle completo con estudiantes y sesiones
 */

import { http } from '../utils/http.js';

/**
 * API de promociones escolares (solo lectura).
 *
 * @namespace promocionApi
 */
export const promocionApi = {
  /** @param {Object} [params={}] - Filtros opcionales (`id_cotizacion`, `colegio`, `anio`, `activa`). */
  listar:  (params = {}) => http.get('api/promociones', params),
  /** @param {number} id - ID de la promoción. */
  obtener: (id)          => http.get(`api/promociones/${id}`),
};
