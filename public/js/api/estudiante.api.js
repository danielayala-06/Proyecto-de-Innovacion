/**
 * @file    estudiante.api.js
 * @module  api/estudianteApi
 *
 * Capa de acceso a la API REST para el recurso Estudiantes.
 * Los estudiantes siempre pertenecen a una promoción escolar;
 * el `id_promocion` es obligatorio en el listado.
 *
 * Endpoints consumidos:
 *  - GET    api/estudiantes?id_promocion={id} → listar estudiantes de una promoción
 *  - POST   api/estudiantes                   → registrar un estudiante (con apoderado)
 *  - PUT    api/estudiantes/{id}              → actualizar datos del estudiante
 *  - DELETE api/estudiantes/{id}              → eliminar estudiante de la promoción
 */

import { http } from '../utils/http.js';

/**
 * API de estudiantes.
 *
 * @namespace estudianteApi
 */
export const estudianteApi = {
    /**
     * @param {number} idPromocion - ID de la promoción escolar.
     * @returns {Promise<Object>} Lista de estudiantes con datos de apoderado aplanados.
     */
    listar:    (idPromocion) => http.get('api/estudiantes', { id_promocion: idPromocion }),
    /**
     * @param {number} id - ID del estudiante.
     * @returns {Promise<Object>} Perfil completo con productos y historial de sesiones.
     */
    obtener:   (id)          => http.get(`api/estudiantes/${id}`),
    /**
     * @param {Object} data - `{ id_promocion, estudiante: {...}, apoderado: {...} }`.
     */
    crear:     (data)        => http.post('api/estudiantes', data),
    /** @param {number} id - ID del estudiante. @param {Object} data - Campos a actualizar. */
    actualizar:(id, data)    => http.put(`api/estudiantes/${id}`, data),
    /** @param {number} id - ID del estudiante. */
    eliminar:  (id)          => http.delete(`api/estudiantes/${id}`),
};
