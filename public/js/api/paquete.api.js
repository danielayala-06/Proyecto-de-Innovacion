/**
 * @file    paquete.api.js
 * @module  api/paqueteApi
 *
 * Capa de acceso a la API REST para el recurso Paquetes fotográficos.
 *
 * Endpoints consumidos:
 *  - GET    api/paquetes               → listar paquetes (soporta filtros: estado, nivel)
 *  - GET    api/paquetes/{id}          → detalle con productos y reglas
 *  - POST   api/paquetes               → crear un nuevo paquete
 *  - PUT    api/paquetes/{id}          → actualizar datos del paquete
 *  - PATCH  api/paquetes/{id}/estado   → activar o desactivar (ACTIVO | INACTIVO)
 *  - POST   api/paquetes/{id}/reglas   → crear regla de bonificación
 *  - DELETE api/paquetes/reglas/{rid}  → eliminar regla
 *  - POST   api/paquetes/{id}/imagen   → subir / reemplazar imagen (multipart)
 *  - DELETE api/paquetes/{id}/imagen   → eliminar imagen
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
    /** @param {number} id - ID del paquete. @param {Object} data - Datos de la regla. */
    crearRegla:    (id, data)    => http.post(`api/paquetes/${id}/reglas`, data),
    /** @param {number} rid - ID de la regla. */
    eliminarRegla: (rid)         => http.delete(`api/paquetes/reglas/${rid}`),

    /**
     * Sube o reemplaza la imagen de un paquete.
     * Usa FormData (multipart) en lugar de JSON.
     * @param {number} id - ID del paquete.
     * @param {File}   file - Archivo de imagen seleccionado.
     */
    subirImagen(id, file) {
        const fd = new FormData();
        fd.append('imagen', file);
        return fetch(`${BASE_URL}api/paquetes/${id}/imagen`, {
            method: 'POST',
            body:   fd,
            credentials: 'same-origin',
        }).then(r => r.json());
    },

    /** @param {number} id - ID del paquete. */
    eliminarImagen: (id) => http.delete(`api/paquetes/${id}/imagen`),
};
