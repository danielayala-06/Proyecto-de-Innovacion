/**
 * @file    contrato.api.js
 * @module  api/contratoApi
 *
 * Capa de acceso a la API REST para el recurso Contratos y Pagos.
 * Encapsula todas las llamadas HTTP relacionadas con contratos,
 * pagos y formas de pago.
 *
 * Endpoints consumidos:
 *  - GET   api/contratos              → listar contratos (con filtros opcionales)
 *  - GET   api/contratos/{id}         → detalle de un contrato (incluye pagos y saldo)
 *  - POST  api/contratos              → crear un contrato desde una cotización aprobada
 *  - PATCH api/contratos/{id}         → actualizar adelanto / fecha / observaciones
 *  - PATCH api/contratos/{id}/estado  → cambiar estado (COMPLETADO | CANCELADO)
 *  - GET   api/pagos                  → listar pagos (filtro opcional: ?contrato={id})
 *  - POST  api/pagos                  → registrar un pago
 *  - GET   api/formas-pago            → catálogo de formas de pago
 */

import { http } from '../utils/http.js';

/**
 * API de contratos, pagos y formas de pago.
 *
 * @namespace contratoApi
 */
export const contratoApi = {
  /** @param {Object} [params={}] - Filtros opcionales (estado, etc.). */
  listar:        (params = {}) => http.get('api/contratos', params),
  /** @param {number} id - ID del contrato. */
  obtener:       (id)          => http.get(`api/contratos/${id}`),
  /** @param {Object} data - `{ id_cotizacion, adelanto, fecha_emision?, observaciones? }`. */
  crear:         (data)        => http.post('api/contratos', data),
  /** @param {number} id - ID del contrato. @param {Object} data - Campos a actualizar. */
  actualizar:    (id, data)    => http.patch(`api/contratos/${id}`, data),
  /** @param {number} id - ID del contrato. @param {string} estado - Nuevo estado (COMPLETADO | CANCELADO). */
  cambiarEstado: (id, estado)  => http.patch(`api/contratos/${id}/estado`, { estado }),
  /** @param {number} contratoId - ID del contrato para filtrar. */
  listarPagos:   (contratoId)  => http.get('api/pagos', { contrato: contratoId }),
  /** @param {Object} data - `{ id_contrato, id_form_pago, monto, fecha, moneda, voucher? }`. */
  registrarPago: (data)        => http.post('api/pagos', data),
  /** @returns {Promise<Object>} Catálogo de formas de pago disponibles. */
  formasPago:    ()            => http.get('api/formas-pago'),
};
