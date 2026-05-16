/**
 * @file    cotizacion.api.js
 * @module  api/cotizacionApi
 *
 * Capa de acceso a la API REST para el recurso Cotizaciones.
 *
 * Endpoints consumidos:
 *  - GET    api/cotizaciones              → listar cotizaciones (soporta ?sin_contrato=1)
 *  - GET    api/cotizaciones/{id}         → detalle completo con cliente, usuario y detalles
 *  - POST   api/cotizaciones              → crear una nueva cotización
 *  - PUT    api/cotizaciones/{id}         → actualizar cotización existente
 *  - PATCH  api/cotizaciones/{id}/estado  → cambiar estado (APROBADA | RECHAZADA | EXPIRADA)
 *  - DELETE api/cotizaciones/{id}         → eliminar cotización en estado PENDIENTE
 */

import { http } from '../utils/http.js';

/**
 * API de cotizaciones.
 *
 * @namespace cotizacionApi
 */
export const cotizacionApi = {
  /** @param {Object} [params={}] - Filtros opcionales (`sin_contrato`, `estado`, etc.). */
  listar:        (params = {}) => http.get('api/cotizaciones', params),
  /** @param {number} id - ID de la cotización. */
  obtener:       (id)          => http.get(`api/cotizaciones/${id}`),
  /** @param {Object} data - Payload con `id_cliente`, `id_usuario`, `detalles[]`, etc. */
  crear:         (data)        => http.post('api/cotizaciones', data),
  /** @param {number} id - ID de la cotización. @param {Object} data - Campos a actualizar. */
  actualizar:    (id, data)    => http.put(`api/cotizaciones/${id}`, data),
  /** @param {number} id - ID de la cotización. @param {string} estado - Nuevo estado. */
  cambiarEstado: (id, estado)  => http.patch(`api/cotizaciones/${id}/estado`, { estado }),
  /** @param {number} id - ID de la cotización (solo en estado PENDIENTE). */
  eliminar:      (id)          => http.delete(`api/cotizaciones/${id}`),
};
