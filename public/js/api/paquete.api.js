/**
 * @file    paquete.api.js
 * @module  api/paqueteApi
 *
 * Capa de acceso a la API REST para el recurso Paquetes fotográficos.
 *
 * Endpoints consumidos:
 *  - GET   api/paquetes               → listar paquetes (soporta filtros: estado, nivel)
 *  - GET   api/paquetes/{id}          → detalle con productos y reglas
 *  - POST  api/paquetes               → crear un nuevo paquete
 *  - PUT   api/paquetes/{id}          → actualizar datos del paquete
 *  - PATCH api/paquetes/{id}/estado   → activar o desactivar (ACTIVO | INACTIVO)
 */

import { http } from '../utils/http.js';

/**
 * API de paquetes fotográficos.
 *
 * @namespace paqueteApi
 */
export const paqueteApi = {
    /** @param {Object} [params={}] - Filtros opcionales (`estado`, `nivel_disponible`). */
    listar:        (params = {}) => http.get('api/paquetes', params),
    /** @param {number} id - ID del paquete. */
    obtener:       (id)          => http.get(`api/paquetes/${id}`),
    /** @param {Object} data - `{ nombre_paquete, nivel_disponible, precio, descripcion?, categoria? }`. */
    crear:         (data)        => http.post('api/paquetes', data),
    /** @param {number} id - ID del paquete. @param {Object} data - Campos a actualizar. */
    actualizar:    (id, data)    => http.put(`api/paquetes/${id}`, data),
    /** @param {number} id - ID del paquete. @param {'ACTIVO'|'INACTIVO'} estado - Nuevo estado. */
    cambiarEstado: (id, estado)  => http.patch(`api/paquetes/${id}/estado`, { estado }),
};
