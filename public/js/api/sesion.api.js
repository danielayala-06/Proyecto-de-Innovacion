/**
 * @file    sesion.api.js
 * @module  api/sesionApi
 *
 * Capa de acceso a la API REST para el recurso Sesiones Fotográficas
 * y la gestión de asistencia.
 *
 * Endpoints consumidos:
 *  - GET    api/sesiones                                      → listar sesiones (filtros: id_promocion, estado)
 *  - GET    api/sesiones/{id}                                 → detalle con lista de asistencia
 *  - POST   api/sesiones                                      → crear sesión
 *  - PUT    api/sesiones/{id}                                 → actualizar sesión
 *  - PATCH  api/sesiones/{id}/estado                          → cambiar estado (pendiente|finalizado|cancelado)
 *  - GET    api/sesiones/limite/{idPromocion}?tipo={tipo}     → verificar límites por tipo
 *  - POST   api/sesiones/{id}/asistencia                      → agregar estudiante a la sesión
 *  - DELETE api/sesiones/{id}/asistencia/{idEstudiante}       → quitar estudiante de la sesión
 *  - PATCH  api/sesiones/{id}/asistencia/{idEstudiante}       → marcar asistencia (1=asistió, 0=ausente)
 */

import { http } from '../utils/http.js';

/**
 * API de sesiones fotográficas y asistencia.
 *
 * @namespace sesionApi
 */
export const sesionApi = {
    /** @param {Object} [params={}] - Filtros opcionales (`id_promocion`, `estado`, `tipo`). */
    listar:            (params = {})       => http.get('api/sesiones', params),
    /** @param {number} id - ID de la sesión (incluye asistencia en la respuesta). */
    obtener:           (id)                => http.get(`api/sesiones/${id}`),
    /** @param {Object} data - `{ id_promocion, tipo, fecha_hora_sesion, observaciones? }`. */
    crear:             (data)              => http.post('api/sesiones', data),
    /** @param {number} id - ID de la sesión. @param {Object} data - Campos a actualizar. */
    actualizar:        (id, data)          => http.put(`api/sesiones/${id}`, data),
    /** @param {number} id - ID de la sesión. @param {string} estado - Nuevo estado. */
    cambiarEstado:     (id, estado)        => http.patch(`api/sesiones/${id}/estado`, { estado }),
    /**
     * Consulta cuántas sesiones de un tipo tiene permitidas / usadas una promoción.
     *
     * @param {number} idPromocion - ID de la promoción escolar.
     * @param {string} tipo        - Tipo de sesión (exteriores|colegio|estudio|otro).
     * @returns {Promise<Object>} `{ data: { permitidas, usadas, puede_crear } }`.
     */
    limite:            (idPromocion, tipo) => http.get(`api/sesiones/limite/${idPromocion}`, { tipo }),
    /** @param {number} id - ID de la sesión. @param {number} idEstudiante - ID del estudiante. */
    agregarEstudiante: (id, idEstudiante)  => http.post(`api/sesiones/${id}/asistencia`, { id_estudiante: idEstudiante }),
    /** @param {number} id - ID de la sesión. @param {number} idEstudiante - ID del estudiante. */
    quitarEstudiante:  (id, idEstudiante)  => http.delete(`api/sesiones/${id}/asistencia/${idEstudiante}`),
    /**
     * @param {number} id          - ID de la sesión.
     * @param {number} idEstudiante - ID del estudiante.
     * @param {0|1}   asistio      - 1 = asistió, 0 = ausente.
     */
    marcarAsistencia:  (id, idEstudiante, asistio) =>
        http.patch(`api/sesiones/${id}/asistencia/${idEstudiante}`, { asistio }),
};
